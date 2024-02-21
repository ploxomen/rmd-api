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
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Acceso denegado'
        ]);
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
        $orders = Orders::getOrders();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Pedidos obtenidos correctamente',
            'totalOrders' => $orders->count(),
            'data' => $orders->skip($skip)->take($show)->orderBy("id","desc")->get()
        ]);
    }
}
