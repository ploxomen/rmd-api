<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use App\Models\Customers;
use App\Models\Products;
use Illuminate\Http\Request;

class QuotationsController extends Controller
{
    private $urlModule = '/quotation/new';

    public function getCustomerActive() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => Customers::select('id AS value','customer_name AS label')->where('customer_status',1)->get()
        ]);
    }
    public function getContactsActive($customer) {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => ['contacts' => Contacts::select('id','contact_name')->where(['contact_status'=> 1,'customer_id' => $customer])->get(),'address' => Customers::find($customer)->customer_address]
        ]);
    }
    public function getProductsActive(Request $request) {
        $search = $request->has('search') ? $request->search : '';
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => Products::select('id AS value','product_name AS label','product_sale')->where(['product_status'=> 1])->where('product_name','like','%'.$search.'%')->get()
        ]);
    }

}

