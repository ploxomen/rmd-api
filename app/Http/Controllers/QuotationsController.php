<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use App\Models\Customers;
use App\Models\Products;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuotationsController extends Controller
{
    private $urlModule = '/quotation/new';
    private $idContrieIgv = 173; 
    public function getCustomerActive() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => Customers::select('id AS value','customer_name AS label')->where('customer_status',1)->get()
        ]);
    }
    public function getUsers() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Usuarios obtenidos correctamente',
            'data' => User::select('id','user_name','user_last_name')->where('user_status','!=',0)->get()
        ]);
    }
    public function getContactsActive($customer) {
        $customerModule = Customers::find($customer);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => ['contacts' => Contacts::select('id','contact_name')->where(['contact_status'=> 1,'customer_id' => $customer])->get(),'address' => $customerModule->customer_address,'disabledIgv' => $customerModule->customer_contrie == $this->idContrieIgv]
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
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'quotation_date_issue' => 'required|date',
            'quotation_type_money' => 'required|string|max:5',
            'quotation_type_change' => 'nullable|string|decimal:0,2',
            'quotation_include_igv' => 'required|boolean',
            'quotation_customer' => 'required|numeric',
            'quotation_contact' => 'nullable|numeric',
            'quotation_address' => 'required|string|max:255',
        ]);
        $validator->setAttributeNames([
            'quotation_date_issue' => 'fecha de emisión',
            'quotation_type_money' => 'tipo de moneda',
            'quotation_type_change' => 'tipo de cambio',
            'quotation_include_igv' => 'incluir IGV',
            'quotation_customer' => 'cliente',
            'quotation_contact' => 'contacto',
            'quotation_address' => 'direccion',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all(),'redirect' => null]);
        }
        $quotation = Quotation::create([
            'quotation_include_igv' => $request->quotation_include_igv,
            'quotation_customer' => $request->quotation_customer,
            'quotation_customer_contact' => $request->quotation_contact,
            'quotation_date_issue' => $request->quotation_date_issue,
            'quotation_type_money' => $request->quotation_type_money,
            'quotation_change_money' => $request->quotation_type_change,
            'quotation_customer_address' => $request->quotation_address,
            'quotation_quoter' => $request->user()->id,
            'quotation_observations' => $request->quotation_observations,
            'quotation_conditions' => $request->quotation_conditions,
            'quotation_description_products' => $request->quotation_description_products,
            'quotation_status' => 1
        ]);
        $amount = 0;
        foreach ($request->products as $detail) {
            $calcAmount = round(($detail['price_unit'] + $detail['price_aditional']) * $detail['quantity'],2);
            $amount += $calcAmount;
            $quotation->products()->attach($detail['id'],[
                'detail_price_buy' => Products::find($detail['id'])->product_buy,
                'detail_quantity' => $detail['quantity'],
                'detail_price_unit' => $detail['price_unit'],
                'detail_price_additional' => $detail['price_aditional'],
                'detail_total' => $calcAmount,
                'detail_status' => 1
            ]);
        }
        $subtotal = $amount - $request->quotation_discount;
        $igv = $request->quotation_include_igv ? $subtotal * 0.18 : 0;
        $total = $subtotal + $igv;
        $quotation->update([
            'quotation_amount' => $amount,
            'quotation_discount' => $request->quotation_discount,
            'quotation_igv' => $igv,
            'quotation_total' => $total
        ]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cotización generada correctamente', 
        ]);
    }
    public function update(Request $request){
        $validator = Validator::make($request->all(),[
            'quotation_date_issue' => 'required|date',
            'quotation_type_money' => 'required|string|max:5',
            'quotation_type_change' => 'nullable|string|decimal:0,2',
            'quotation_include_igv' => 'required|boolean',
            'quotation_customer' => 'required|numeric',
            'quotation_contact' => 'nullable|numeric',
            'quotation_address' => 'required|string|max:255',
        ]);
        $validator->setAttributeNames([
            'quotation_date_issue' => 'fecha de emisión',
            'quotation_type_money' => 'tipo de moneda',
            'quotation_type_change' => 'tipo de cambio',
            'quotation_include_igv' => 'incluir IGV',
            'quotation_customer' => 'cliente',
            'quotation_contact' => 'contacto',
            'quotation_address' => 'direccion',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all(),'redirect' => null]);
        }
        $quotation = Quotation::find($request->quotation);
        $quotation->update([
            'quotation_include_igv' => $request->quotation_include_igv,
            'quotation_customer' => $request->quotation_customer,
            'quotation_customer_contact' => $request->quotation_contact,
            'quotation_date_issue' => $request->quotation_date_issue,
            'quotation_type_money' => $request->quotation_type_money,
            'quotation_change_money' => $request->quotation_type_change,
            'quotation_customer_address' => $request->quotation_address,
            'quotation_observations' => $request->quotation_observations,
            'quotation_conditions' => $request->quotation_conditions,
            'quotation_description_products' => $request->quotation_description_products,
        ]);
        $details = [];
        $amount = 0;
        foreach ($request->products as $detail) {
            $calcAmount = round(($detail['price_unit'] + $detail['price_aditional']) * $detail['quantity'],2);
            $amount += $calcAmount;
            $details[$detail['id']] =  [
                    'detail_quantity' => $detail['quantity'],
                    'detail_price_unit' => $detail['price_unit'],
                    'detail_price_additional' => $detail['price_aditional'],
                    'detail_status' => 1,
                    'detail_total' => $calcAmount
            ];
        }
        $quotation->products()->sync($details);
        $subtotal = $amount - $request->quotation_discount;
        $igv = $request->quotation_include_igv ? $subtotal * 0.18 : 0;
        $total = $subtotal + $igv;
        $quotation->update([
            'quotation_amount' => $amount,
            'quotation_discount' => $request->quotation_discount,
            'quotation_igv' => $igv,
            'quotation_total' => $total
        ]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cotización actualizada correctamente', 
        ]);
    }
    public function show(Request $request) {
        $quotation = Quotation::find($request->quotation,["quotation_customer","quotations.id","quotation_include_igv","quotation_discount","quotation_customer_contact AS quotation_contact","quotation_date_issue","quotation_type_money","quotation_change_money AS quotation_type_change","quotation_customer_address AS quotation_address","quotation_observations","quotation_conditions","quotation_description_products"]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cotizaciones obtenida correctamente',
            'data' => [
                'quotation' => $quotation,
                'contacs' => Contacts::select("id","contact_name")->where("contact_status",1)->get(),
                'products' => $quotation->products()->select("products.id","products.product_name AS description","quotations_details.detail_quantity AS quantity","quotations_details.detail_price_unit AS price_unit","quotations_details.detail_price_additional AS price_aditional")->get()
            ]
        ]);
    }
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $filters = [];
        $columns = ['customer' => 'quotation_customer','status' => 'quotation_status','quoter' => 'quotation_quoter'];
        foreach ($request->all() as $key => $filter) {
            if(in_array($key,['status','quoter','customer']) && !is_null($filter)){
                $filters[] = [
                    'column' => $columns[$key],
                    'sign' => '=',
                    'value' => $filter
                ];
            }
        }
        $quotations = Quotation::getQuotations($search,$filters);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cotizaciones obtenidas correctamente',
            'totalQuotations' => $quotations->count(),
            'data' => $quotations->skip($skip)->take($show)->orderBy("id","desc")->get()
        ]);
    }
    public function destroy(Request $request) {
        Quotation::find($request->quotation)->update(['quotation_status' => 0]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cotización eliminada correctamente',
        ]);
    }
}

