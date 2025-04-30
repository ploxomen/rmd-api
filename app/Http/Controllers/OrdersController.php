<?php

namespace App\Http\Controllers;

use App\Models\Configurations;
use App\Models\Customers;
use App\Models\Districts;
use App\Models\Orders;
use App\Models\Provinces;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrdersController extends Controller
{
    private $urlModule = "/order/new";
    private $urlModuleAll = "/order/all";
    public function getQuotations(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $customer = Customers::find($request->customer);
        $includeIgv = $request->includeIgv;
        return response()->json([
            'redirect' => null,
            'error' => false,
            'data' => Quotation::getQuotationsForOrders($request->money, $includeIgv, $customer->id),
            'includeIgv' => $includeIgv,
            'message' => 'Cotizaciones obtenidas correctamente'
        ]);
    }
    public function getReportPdf(Request $request, Orders $order)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        $redirect2 = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        if (!is_null($redirect) && !is_null($redirect2)) {
            return response('Acceso denegado', 403);
        }
        $configuration = Configurations::all();
        $emails = $configuration->where('description', '=', 'business_email')->first()->value;
        $listEmails = explode('/', $emails);
        $moneyOrder = $order->order_money == 'PEN' ? 'S/' : '$';
        return Pdf::loadView('reports.order', compact('order', 'moneyOrder', 'listEmails'))->stream("order.pdf");
    }
    public function downloadOS(Orders $order)
    {
        if (!Storage::exists($order->order_file_url)) {
            return response()->json(['error' => 'File not found.'], 404);
        }
        return Storage::download($order->order_file_url);
    }
    public function store(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $orderCode = $this->getCodeOrder();
        DB::beginTransaction();
        try {
            $order = Orders::create([
                'order_date_issue' => $request->order_date_issue,
                'order_district' => $request->order_district,
                'customer_id' => $request->customer_id,
                'order_conditions_pay' => $request->order_conditions_pay,
                'order_igv' => $request->order_igv,
                'order_number' => $orderCode['number'],
                'order_code' => $orderCode['code'],
                'order_money' => $request->order_money,
                'order_conditions_delivery' => $request->order_conditions_delivery,
                'order_address' => $request->order_address,
                'order_project' => $request->order_project,
                'order_contact_email' => $request->order_contact_email,
                'order_contact_telephone' => $request->order_contact_telephone,
                'order_contact_name' => $request->order_contact_name,
                'order_status' => 1,
                'order_retaining_customer' => $request->order_retaining_customer,
                'user_id' => $request->user()->id
            ]);
            Customers::find($request->customer_id)->update(['customer_retaining' => $request->order_retaining_customer]);
            list($filePath, $fileName) = [null, null];
            if ($request->has('order_os')) {
                $file = $request->file("order_os");
                $fileName = $file->getClientOriginalName();
                $fileNameDB = time() . '_' . $fileName;
                $filePath = $file->storeAs('documents_os', $fileNameDB);
            }
            foreach (json_decode($request->quotations) as $quotation) {
                Quotation::where([
                    'id' => $quotation->id,
                    'quotation_status' => 1
                ])->whereNull('order_id')->first()->update([
                    'order_id' => $order->id,
                    'quotation_status' => 2
                ]);
            }
            $order->update(array_merge($this->calculateMount($order->id), [
                'order_file_url' => $filePath,
                'order_file_name' => $fileName
            ]));
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Pedido generado correctamente'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => $th->getMessage()
            ]);
        }
    }
    public function getCodeOrder()
    {
        $year = date('Y');
        $selectNumber = Orders::select("order_number")->whereYear('created_at', $year)->orderBy('order_number', 'desc')->first();
        $number = empty($selectNumber) ? 0 : $selectNumber->order_number;
        $number++;
        return ['number' => $number, 'code' => 'PV' . str_pad($number, 2, '0', STR_PAD_LEFT) . '' . substr($year, -2)];
    }
    public function calculateMount($idOrder)
    {
        $quotations = Quotation::where(['order_id' => $idOrder])->where('quotation_status', '!=', 0)->get();
        $amount = [
            'order_mount' => 0,
            'order_mount_igv' => 0,
            'order_total' => 0
        ];
        foreach ($quotations as $quotation) {
            $amount['order_mount'] += $quotation->quotation_amount - $quotation->quotation_discount;
            $amount['order_mount_igv'] += $quotation->quotation_igv;
            $amount['order_total'] += $quotation->quotation_total;
        }
        return $amount;
    }
    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $filters = [];
        $columns = [
            'customer' => 'customer_id',
            'status' => 'order_status',
        ];
        foreach ($request->all() as $key => $filter) {
            if (in_array($key, ['status', 'customer']) && !is_null($filter)) {
                $filters[] = [
                    'column' => $columns[$key],
                    'sign' => '=',
                    'value' => $filter
                ];
            }
        }
        $totalOrders = Orders::getOrdersCount($search, $filters);
        $orders = Orders::getOrders($search, $filters);
        if (!empty($request->date_ini) && !empty($request->date_fin)) {
            $orders = $orders->whereRaw('DATE_FORMAT(orders.created_at, "%Y-%m-%d") BETWEEN ? AND ?', [$request->date_ini, $request->date_fin]);
            $totalOrders = $totalOrders->whereRaw('DATE_FORMAT(orders.created_at, "%Y-%m-%d") BETWEEN ? AND ?', [$request->date_ini, $request->date_fin]);
        }
        $responseData = $orders->skip($skip)->take($show)->orderBy("id", "desc")->get();
        $calculator = $orders->join('quotations', 'quotations.order_id', '=', 'orders.id');
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos obtenidos correctamente',
            'igv' =>  $calculator->selectRaw('SUM(IF(quotations.quotation_type_money = "USD",quotation_change_money * quotation_igv,quotation_igv)) AS order_igv')->value('order_igv'),
            'amount' =>  $calculator->selectRaw('SUM(IF(quotations.quotation_type_money = "USD",quotation_change_money * quotation_amount,quotation_amount)) AS order_mount')->value('order_mount'),
            'total' =>  $calculator->selectRaw('SUM(IF(quotations.quotation_type_money = "USD",quotation_change_money * quotation_total,quotation_total)) AS order_total')->value('order_total'),
            'totalOrders' => $totalOrders->get()->count(),
            'data' => $responseData
        ]);
    }
    public function addQuotation(Request $request, Quotation $quotation)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ], 403);
        }
        $countQuotation = Quotation::join('contacts', 'quotation_customer_contact', '=', 'contacts.id')
            ->where([
                'contact_name' => $request->order_contact_name,
                'contact_number' => $request->order_contact_telephone,
                'contact_email' => $request->order_contact_email,
                'contact_status' => 1,
                'quotations.id' => $quotation->id,
                'quotation_project' => $request->order_project,
                'quotation_status' => 1,
                'quotation_customer' => $request->customer_id,
                'quotation_type_money' => $request->order_money,
                'quotation_include_igv' => $request->order_igv
            ])->count();
        if (!$countQuotation) {
            return response()->json([
                'message' => 'La cotización ' . $quotation->quotation_code . ' no puede ser agregado a este pedido debido a que no cumple con las codiciones requeridas',
                'alert' => true
            ]);
        }
        return response()->json([
            'message' => 'Cotización agregada',
            'data' => [
                'id' => $quotation->id,
                'quotation_total' => $quotation->quotation_total,
                'quotation_code' => $quotation->quotation_code,
                'close' => 0,
                'date_issue' => date('d/m/Y', strtotime($quotation->quotation_date_issue))
            ]
        ]);
    }
    public function deleteQuotation(Request $request, Quotation $quotation)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ], 403);
        }
        return response()->json([
            'message' => 'Cotización eliminada',
            'data' => [
                'value' => $quotation->id,
                'label' => $quotation->quotation_code
            ]
        ]);
    }
    public function reloadQuotations(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ], 403);
        }
        return response()->json([
            'data' => Orders::quotationsNew(
                $request->order_contact_name,
                $request->order_contact_telephone,
                $request->order_contact_email,
                $request->order_project,
                $request->customer_id,
                $request->order_igv,
                $request->order_money
            )
        ]);
    }
    public function show(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ], 403);
        }
        $order = Orders::getOrder($request->order);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos obtenidos correctamente',
            'data' => [
                'order' => $order,
                'quotationsNew' => Orders::quotationsNew($order->order_contact_name, $order->order_contact_telephone, $order->order_contact_email, $order->order_project, $order->customer_id, $order->order_igv, $order->order_money),
                'quotations' => Quotation::select("id", "quotation_total", "quotation_code")->selectRaw("DATE_FORMAT(quotation_date_issue,'%d/%m/%Y') AS date_issue,0 AS close")->where(['order_id' => $request->order, 'quotation_status' => 2])->get(),
                'provinces' => Provinces::select("id", "province_name")->where("province_departament", $order->order_departament)->get(),
                'districs' => Districts::select("id", "district_name")->where("district_province", $order->order_province)->get()
            ]
        ]);
    }
    public function update(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ], 403);
        }
        DB::beginTransaction();
        try {
            $order = Orders::find($request->order);
            $order->update([
                'order_date_issue' => $request->order_date_issue,
                'order_district' => $request->order_district,
                'order_conditions_pay' => $request->order_conditions_pay,
                'order_conditions_delivery' => $request->order_conditions_delivery,
                'order_address' => $request->order_address,
                'customer_id' => $request->customer_id,
                'order_igv' => $request->order_igv,
                'order_money' => $request->order_money,
                'order_project' => $request->order_project,
                'order_contact_email' => $request->order_contact_email,
                'order_contact_telephone' => $request->order_contact_telephone,
                'order_contact_name' => $request->order_contact_name,
                'order_retaining_customer' => $request->order_retaining_customer
            ]);
            if ($request->has('order_file_update')) {
                if (Storage::exists($order->order_file_url)) {
                    Storage::delete($order->order_file_url);
                }
                $file = $request->file("order_file_update");
                $fileName = $file->getClientOriginalName();
                $fileNameDB = time() . '_' . $fileName;
                $filePath = $file->storeAs('documents_os', $fileNameDB);
                $order->update([
                    'order_file_url' => $filePath,
                    'order_file_name' => $fileName
                ]);
            }
            $quotationsRequest = collect(json_decode($request->quotations, true));
            $order->quotations()->whereIn("quotations.id", $quotationsRequest->pluck("id"))->get()->each(function ($quotation) {
                $quotation->quotation_status = 1;
                $quotation->order_id = null;
                $quotation->save();
            });
            $order->update($this->calculateMount($order->id));
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Pedidos actualizados correctamente',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => $th->getMessage(),
            ]);
        }
    }
    public function destroy(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModuleAll);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        DB::beginTransaction();
        try {
            $order = Orders::find($request->order);
            $order->quotations()->get()->each(function ($quotation) {
                $quotation->quotation_status = 1;
                $quotation->order_id = null;
                $quotation->save();
            });
            $order->update(['order_status' => 0, 'order_mount' => 0, 'order_mount_igv' => 0, 'order_total' => 0]);
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Pedidos eliminados correctamente',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
