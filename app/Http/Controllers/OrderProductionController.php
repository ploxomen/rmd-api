<?php

namespace App\Http\Controllers;

use App\Exports\OrderProductionExport;
use App\Models\OrderProduction;
use App\Models\OrderProductionDetail;
use App\Models\Orders;
use App\Models\ProductLabel;
use App\Models\QuotationDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class OrderProductionController extends Controller
{
    private $urlModule = "/order/production/list";
    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $customer = $request->input("customer", "");
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $orderProductions = OrderProduction::select([
            "order_productions.id",
            "order_production_code",
            "order_produc_date_issue",
            "order_produc_total",
            "order_produc_date_delive",
            "customer_name",
            DB::raw("GROUP_CONCAT(DISTINCT(order_code) SEPARATOR ',') as orders_code")
        ])
            ->leftJoin('customers', 'customers.id', '=', 'order_production_customer')
            ->leftJoin('order_productions_details', 'order_productions.id', '=', 'order_productions_details.order_production_id')
            ->leftJoin('orders', 'order_productions_details.order_id', '=', 'orders.id');
        if (!empty($customer)) {
            $orderProductions->where('order_production_customer', $customer);
        }
        if (!empty($search)) {
            $orderProductions->where(function ($query) use ($search) {
                $query->where('order_productions.order_production_code', 'like', '%' . $search . '%')
                    ->orWhere('customers.customer_name', 'like', '%' . $search . '%')
                    ->orWhere('order_productions.product_name', 'like', '%' . $search . '%');
            });
        }
        $orderProductions->groupBy(
            "order_productions.id",
        );
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Orden de produccion obtenidos correctamente',
            'total' => $orderProductions->get()->count(),
            'data' => $orderProductions->skip($skip)->take($show)->get()
        ]);
    }
    public function destroy(OrderProduction $orderProduction)
    {
        $orderProduction->details()->delete();
        $orderProduction->delete();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Orden de produccion eliminado correctamente',
            'success' => true
        ]);
    }
    public function viewReportExcel(int $orderId)
    {
        $order = OrderProduction::with(['customer'])->where('id', $orderId)->first();
        $details = QuotationDetails::getQuotationDetailOld($orderId);
        foreach ($details as $detail) {
            $labels = OrderProductionDetail::query()->select(["product_label_id as id", "product_label_hr as time_origin_hours", "product_labels.name as product_label_name"])
                ->leftJoin('product_labels', 'product_labels.id', '=', 'product_label_id')->where([
                    'order_production_id' => $orderId,
                    'quotation_detail_id' => $detail->quota_deta_id,
                    'order_id' => $detail->order_id
                ])->whereNotNull('product_label_id')->get();
            $detail->list_labels = $labels;
            $detail->subtotal = $labels->sum(function ($item) use ($detail) {
                return $detail->amount * $item->time_origin_hours;
            });
        }
        return Excel::download(new OrderProductionExport($order, $details), 'orden-produccion-' . $order->order_production_code . '.xlsx');
    }
    function formatOrderCodes(array $ordersCollection): string
    {
        $codes = collect($ordersCollection)
            ->pluck('order_code')
            ->filter()
            ->unique()
            ->values();
        if ($codes->isEmpty()) {
            return '';
        }
        $numbers = [];
        $years = [];
        $prefix = 'PV'; // Prefijo por defecto
        foreach ($codes as $code) {
            $prefix = substr($code, 0, 2);
            $year   = substr($code, -2);
            $middle = substr($code, 2, -2);
            $numbers[] = $middle;
            $years[]   = $year;
        }
        sort($numbers, SORT_STRING);
        $minYear = min($years);
        $joinedNumbers = implode('-', $numbers);
        return "OP-{$prefix}{$joinedNumbers}{$minYear}";
    }
    public function getShortages()
    {
        return response()->json([
            'data' => Orders::listOrdersProduction()
        ]);
    }
    public function store(Request $request)
    {
        $details = json_decode($request->input('details', '[]'), true);
        $orderCode = $this->formatOrderCodes($details);
        if (OrderProduction::where('order_production_code', $orderCode)->exists()) {
            return response()->json([
                'message' => "El código {$orderCode} ya se encuentra registrado"
            ], 422);
        }
        $orderProduction = OrderProduction::create([
            'order_production_code' => $orderCode,
            'order_production_detail' => $request->observations,
            'order_produc_date_issue' => $request->date_issue,
            'order_produc_date_delive' => $request->date_delivery,
            'order_produc_address' => $request->address,
            'order_production_customer' => $request->cod_client,
        ]);
        $totalHr = 0;
        foreach ($details as $detail) {
            $detailFillable = [
                'quotation_detail_id' => $detail['quota_deta_id'],
                'order_id' => $detail['order_id'],
                'product_label_id' => null,
                'amount' => $detail['amount'],
                'product_label_hr' => 0,
                'product_label_total' => 0
            ];
            if (empty($detail['list_labels'])) {
                $orderProduction->details()->create($detailFillable);
                continue;
            }
            foreach ($detail['list_labels'] as $label) {
                $subtotalHr = round($detail['amount'] * $label['time_origin_hours'], 2);
                $detailFillable['product_label_id'] = $label['id'];
                $detailFillable['product_label_hr'] = $label['time_origin_hours'];
                $detailFillable['product_label_total'] = $subtotalHr;
                $orderProduction->details()->create($detailFillable);
                $totalHr += $subtotalHr;
            }
        }
        $orderProduction->update(['order_produc_total' => $totalHr]);
        return response()->json([
            'message' => 'Orden de producción generada correctamente',
            'success' => true,
            'redirect' => null
        ]);
    }
    public function show(int $orderProduction)
    {
        $orderProductionModel = OrderProduction::with('customer')->where('id', $orderProduction)->first();
        $products = QuotationDetails::getQuotationDetailOld($orderProduction);
        foreach ($products as $product) {
            $product->list_labels = OrderProductionDetail::query()->select(["product_label_id as id", "product_label_hr as time_origin_hours"])->where([
                'order_production_id' => $orderProduction,
                'quotation_detail_id' => $product->quota_deta_id,
                'order_id' => $product->order_id
            ])->whereNotNull('product_label_id')->get();
        }
        return response()->json([
            'data' => $orderProductionModel,
            'products' => $products
        ]);
    }
    public function viewReportPdf(int $orderId)
    {
        $order = OrderProduction::with(['customer'])->where('id', $orderId)->first();
        $details = QuotationDetails::getQuotationDetailOld($orderId);
        foreach ($details as $detail) {
            $labels = OrderProductionDetail::query()->select(["product_label_id as id", "product_label_hr as time_origin_hours", "product_labels.name as product_label_name"])
                ->leftJoin('product_labels', 'product_labels.id', '=', 'product_label_id')->where([
                    'order_production_id' => $orderId,
                    'quotation_detail_id' => $detail->quota_deta_id,
                    'order_id' => $detail->order_id
                ])->whereNotNull('product_label_id')->get();
            $detail->list_labels = $labels;
            $detail->subtotal = $labels->sum(function ($item) use ($detail) {
                return $detail->amount * $item->time_origin_hours;
            });
        }
        return Pdf::loadView('reports.order-production', compact('order', 'details'))->stream("order.pdf");
    }
    public function getQuotationForOrderId(int $orderId)
    {
        $productNotLabel = QuotationDetails::productNotLabel($orderId);
        if ($productNotLabel->isNotEmpty()) {
            return response()->json([
                'data' => $productNotLabel,
                'alert' => 'Los siguientes productos no cuentan con los datos de producción:'
            ]);
        }
        $products = QuotationDetails::getQuotationDetail($orderId);
        $details = Orders::query()->select(['order_details', 'order_code', 'id as order_id', 'order_date_issue', 'order_address'])->selectRaw("DATE_FORMAT(orders.created_at, '%Y-%m-%d') as order_date_created")->where('id', $orderId)->first();
        foreach ($products as $product) {
            $product->list_labels = ProductLabel::query()->select(["product_labels.id", "time_origin_hours"])->leftJoin('product_product_labels', 'product_product_labels.product_label_id', '=', 'product_labels.id')->where('product_id', $product->product_id)->get();
        }
        return response()->json([
            'data' => $products,
            'details' => $details
        ]);
    }
    public function update(OrderProduction $orderProduction, Request $request)
    {
        $details = json_decode($request->input('details', '[]'), true);
        $orderCode = $this->formatOrderCodes($details);
        if (OrderProduction::where('order_production_code', $orderCode)->where('id', '!=', $orderProduction->id)->exists()) {
            return response()->json([
                'message' => "El código {$orderCode} ya se encuentra registrado"
            ], 422);
        }
        $orderProduction->update([
            'order_production_code' => $orderCode,
            'order_production_detail' => $request->observations,
            'order_produc_date_issue' => $request->date_issue,
            'order_produc_date_delive' => $request->date_delivery,
            'order_produc_address' => $request->address,
            'order_production_customer' => $request->cod_client,
        ]);
        $idDetails = array_column($details, 'quotation_detail_id');
        $orderProduction->details()->whereNotIn('quotation_detail_id', $idDetails)->delete();
        $totalHr = 0;
        foreach ($details as $detail) {
            $columnDiferent = [
                'order_production_id' => $orderProduction->id,
                'order_id' => $detail['order_id'],
                'quotation_detail_id' => $detail['quota_deta_id'],
            ];
            if (empty($detail['list_labels'])) {
                OrderProductionDetail::updateOrCreate(
                    $columnDiferent,
                    [
                        'amount' => $detail['amount'],
                        'product_label_hr' => 0,
                        'product_label_total' => 0
                    ]
                );
                continue;
            }
            foreach ($detail['list_labels'] as $label) {
                $columnDiferent['product_label_id'] = $label['id'];
                $subtotalHr = round($detail['amount'] * $label['time_origin_hours'], 2);
                OrderProductionDetail::updateOrCreate(
                    $columnDiferent,
                    [
                        'amount' => $detail['amount'],
                        'product_label_hr' => $label['time_origin_hours'],
                        'product_label_total' => $subtotalHr
                    ]
                );
                $totalHr += $subtotalHr;
            }
        }
        $orderProduction->update(['order_produc_total' => $totalHr]);
        return response()->json([
            'message' => 'Orden de producción actualizada correctamente',
            'success' => true,
            'redirect' => null
        ]);
    }
}
