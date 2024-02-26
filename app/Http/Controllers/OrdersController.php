<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\Orders;
use App\Models\Quotation;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    private $urlModule = "/order/new";
    private $urlModuleAll = "/order/all";
    public function getQuotations(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $customer = Customers::find($request->customer);
        $includeIgv = $customer->customer_contrie == 173 ? 1 : 0;
        return response()->json([
            'redirect' => null,
            'error' => false,
            'data' => Quotation::getQuotationsForOrders($request->money,$includeIgv,$customer->id),
            'includeIgv' => $includeIgv,
            'message' => 'Cotizaciones obtenidas correctamente'
        ]);
    }
    public function store(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $order = Orders::create([
            'order_date_issue' => date('Y-m-d'),
            'customer_id' => $request->customer,
            'order_details' => $request->orderDetails,
            'order_igv' => $request->includeIgv,
            'order_money' => $request->money,
            'order_status' => 1,
            'user_id' => $request->user()->id
        ]);
        foreach ($request->quotations as $quotation) {
            Quotation::where(['id' => $quotation['id'],'quotation_status' => 1])->whereNull('order_id')->update(['order_id' => $order->id,'quotation_status' => 2]);
        }
        $order->update($this->calculateMount($order->id));
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Acceso denegado'
        ]);
    }
    public function calculateMount($idOrder) {
        $quotations = Quotation::where(['order_id' => $idOrder])->where('quotation_status','!=',0)->get();
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
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        if(!is_null($redirect)){
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
        $columns = ['customer' => 'customer_id','status' => 'order_status'];
        foreach ($request->all() as $key => $filter) {
            if(in_array($key,['status','customer']) && !is_null($filter)){
                $filters[] = [
                    'column' => $columns[$key],
                    'sign' => '=',
                    'value' => $filter
                ];
            }
        }
        $orders = Orders::getOrders($search,$filters);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos obtenidos correctamente',
            'totalOrders' => $orders->count(),
            'data' => $orders->skip($skip)->take($show)->orderBy("id","desc")->get()
        ]);
    }
    public function show(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $order = Orders::getOrder($request->order);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos obtenidos correctamente',
            'data' => [
                'order' => $order,
                'quotations' => Quotation::select("id","quotation_total")->selectRaw("DATE_FORMAT(quotation_date_issue,'%d/%m/%Y') AS date_issue,0 AS close")->where(['order_id' => $request->order,'quotation_status' => 2])->get()
            ]
        ]);
    }
    public function update(Request $request){
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $order = Orders::find($request->order);
        $order->update(['order_details' => $request->order_details]);
        foreach ($request->quotations as $quotation) {
            if($quotation['close'] === 1){
                Quotation::where(['id' => $quotation['id']])->update(['order_id' => null,'quotation_status' => 1]);
            }
        }
        $order->update($this->calculateMount($order->id));
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos actualizados correctamente',
        ]);
    }
    public function destroy(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        $order = Orders::find($request->order);
        $order->quotations()->update(['quotation_status' => 1, 'order_id' => null]);
        $order->update(['order_status' => 0]);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos eliminados correctamente',
        ]);
    }
}
