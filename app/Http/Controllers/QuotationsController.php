<?php

namespace App\Http\Controllers;

use App\Exports\QuotationsExport;
use App\Models\Configurations;
use App\Models\Contacts;
use App\Models\Customers;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Quotation;
use App\Models\SubCategories;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class QuotationsController extends Controller
{
    private $urlModule = '/quotation/new';
    private $urlModuleAll = '/quotation/all';
    private $urlModuleExcel = '/quotation/report';
    private $idContrieIgv = 173; 
    public function getCustomerActive() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => Customers::select('id AS value','customer_name AS label')->where('customer_status',1)->orderBy('customer_name','asc')->get()
        ]);
    }
    public function getDataExport(Request $request){
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleExcel);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        switch ($request->typeInformation) {
            case 'excel':
                return Excel::download(new QuotationsExport(Quotation::getQuotationsReport($request->startDate,$request->finalDate)->get(),'reports.quotation'),'cotizizaciones.xlsx');
            break;
            default:
                $show = $request->show;
                $skip = ($request->page - 1) * $show;
                $quotations = Quotation::getQuotationsReport($request->startDate,$request->finalDate);
                return response()->json([
                    'redirect' => null,
                    'error' => false,
                    'message' => 'Datos obtenidos correctamente',
                    'data' => $quotations->skip($skip)->take($show)->get(),
                    'totalQuotations' => $quotations->count()
                ]);
            break;
        }
    }
    public function getInformationConfig() {
        $configurations = Configurations::select("description","value")->whereIn('description',['quotation_conditions','quotation_observations','quotation_warranty_1','quotation_warranty_2'])->get();
        return response()->json([
            'redirect' => null,
            'error' => false, 
            'message' => 'Datos obtenidos correctamente',
            'data' => $configurations 
        ]);
    }
    public function getPreview(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ],403);
        }
        $detailsQuotation = [];
        foreach ($request->products as $detail) {
            $product = Products::find($detail['id']);
            $subCategorie = SubCategories::with('categorie')->find($product->sub_categorie)->toArray();
            $filterCategorie = array_filter($detailsQuotation,function($value)use($subCategorie){
                return $value['categorie_id'] === $subCategorie['categorie_id'];
            });
            if(empty($filterCategorie)){
             $detailsQuotation[] = [
                'categorie_id' => $subCategorie['categorie_id'],
                'categorie_name' => $subCategorie['categorie']['categorie_name'],
                'subcategories' => [
                    [
                        'subcategorie_id' => $subCategorie['id'],
                        'subcategorie_name' => $subCategorie['sub_categorie_name'],
                        'products' => [$detail]
                    ]
                ]
             ];
            }else{
                $filterSubCategorie = array_filter($detailsQuotation[key($filterCategorie)]['subcategories'],function($value)use($subCategorie){
                    return $value['subcategorie_id'] === $subCategorie['id'];
                });
                if(empty($filterSubCategorie)){
                    $detailsQuotation[key($filterCategorie)]['subcategories'][] = [
                        'subcategorie_id' => $subCategorie['id'],
                        'subcategorie_name' => $subCategorie['sub_categorie_name'],
                        'products' => [$detail]
                    ];
                }else{
                    $detailsQuotation[key($filterCategorie)]['subcategories'][key($filterSubCategorie)]['products'][] = $detail;
                }
            }
        }
        $quotation = $request->all();
        $nameCategorie = array_column($detailsQuotation, 'categorie_name');
        array_multisort($nameCategorie, SORT_ASC, $detailsQuotation);
        $configuration = Configurations::all();
        return Pdf::loadView('reports.quotationpdfv3preview',compact('quotation','configuration','detailsQuotation'))->stream("asas.pdf");
    }
    public function getReportPdf(Request $request,Quotation $quotation) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        $redirect2 = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(!is_null($redirect) && !is_null($redirect2)){
            return response('Acceso denegado',403);
        }
        $detailsQuotation = [];
        foreach ($quotation->products as $detail) {
            $subCategorie = SubCategories::with('categorie')->find($detail->sub_categorie)->toArray();
            $filterCategorie = array_filter($detailsQuotation,function($value)use($subCategorie){
                return $value['categorie_id'] === $subCategorie['categorie_id'];
            });
            if(empty($filterCategorie)){
             $detailsQuotation[] = [
                'categorie_id' => $subCategorie['categorie_id'],
                'categorie_name' => $subCategorie['categorie']['categorie_name'],
                'subcategories' => [
                    [
                        'subcategorie_id' => $subCategorie['id'],
                        'subcategorie_name' => $subCategorie['sub_categorie_name'],
                        'products' => [$detail]
                    ]
                ]
             ];
            }else{
                $filterSubCategorie = array_filter($detailsQuotation[key($filterCategorie)]['subcategories'],function($value)use($subCategorie){
                    return $value['subcategorie_id'] === $subCategorie['id'];
                });
                if(empty($filterSubCategorie)){
                    $detailsQuotation[key($filterCategorie)]['subcategories'][] = [
                        'subcategorie_id' => $subCategorie['id'],
                        'subcategorie_name' => $subCategorie['sub_categorie_name'],
                        'products' => [$detail]
                    ];
                }else{
                    $detailsQuotation[key($filterCategorie)]['subcategories'][key($filterSubCategorie)]['products'][] = $detail;
                }
            }
        }
        $nameCategorie = array_column($detailsQuotation, 'categorie_name');
        array_multisort($nameCategorie, SORT_ASC, $detailsQuotation);
        $configuration = Configurations::all();
        return Pdf::loadView('reports.' . $quotation->quotation_view_pdf ,compact('quotation','configuration','detailsQuotation'))->stream("asas.pdf");
    }
    public function getUsers() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Usuarios obtenidos correctamente',
            'data' => User::select('id','user_name','user_last_name')->where('user_status','!=',0)->orderBy('user_name','asc')->orderBy('user_last_name','asc')->get()
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
    public function getProductDescription(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $redirect2 = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        if(!is_null($redirect) && !is_null($redirect2)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true,
                'message' => 'Acceso denegado'
            ]);
        }
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Descripción obtenida correctamente',
            'data' => Products::find($request->product)->product_description
        ]);
    }
    public function getProductsActive(Request $request) {
        $search = $request->has('search') ? $request->search : '';
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'data' => Products::select('id AS value','product_name AS label','product_public_customer','product_distributor','product_service')->where(['product_status'=> 1])->where('product_name','like','%'.$search.'%')->orderBy('product_name','asc')->get()
        ]);
    }
    public function getCodeQuotation() {
        $year = date('Y');
        $selectNumber = Quotation::select("quotation_number")->whereYear('quotation_date_issue',$year)->orderBy('quotation_number','desc')->first();
        $number = empty($selectNumber) ? 0 : $selectNumber->quotation_number;
        $number++;
        return ['number' => $number,'code' => str_pad($number,3,'0',STR_PAD_LEFT) . '/'.substr($year,-2)];
    }
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'quotation_date_issue' => 'required|date',
            'quotation_type_money' => 'required|string|max:5',
            'quotation_type_change' => 'nullable|string|decimal:0,2',
            'quotation_include_igv' => 'required|boolean',
            'quotation_project' => 'required|string',
            'quotation_customer' => 'required|numeric',
            'quotation_contact' => 'required|numeric',
            'quotation_way_to_pay' => 'required|string'
        ]);
        $validator->setAttributeNames([
            'quotation_date_issue' => 'fecha de emisión',
            'quotation_type_money' => 'tipo de moneda',
            'quotation_type_change' => 'tipo de cambio',
            'quotation_project' => 'proyecto',
            'quotation_include_igv' => 'incluir IGV',
            'quotation_customer' => 'cliente',
            'quotation_contact' => 'contacto',
            'quotation_way_to_pay' => 'forma de pago'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all(),'redirect' => null]);
        }
        $quotationCode = $this->getCodeQuotation();
        $quotation = Quotation::create([
            'quotation_include_igv' => $request->quotation_include_igv,
            'quotation_customer' => $request->quotation_customer,
            'quotation_customer_contact' => $request->quotation_contact,
            'quotation_project' => $request->quotation_project,
            'quotation_number' => $quotationCode['number'],
            'quotation_code' => $quotationCode['code'],
            'quotation_date_issue' => $request->quotation_date_issue,
            'quotation_type_money' => $request->quotation_type_money,
            'quotation_change_money' => $request->quotation_type_change,
            'quotation_customer_address' => $request->quotation_address,
            'quotation_quoter' => $request->user()->id,
            'quotation_observations' => $request->quotation_observations,
            'quotation_conditions' => $request->quotation_conditions,
            'quotation_status' => 1,
            'quotation_way_to_pay' => $request->quotation_way_to_pay,
            'quotation_warranty_1' => $request->quotation_warranty_1,
            'quotation_warranty_2' => $request->quotation_warranty_2,
            'quotation_view_pdf' => 'quotationpdfv3'
        ]);
        $amount = 0;
        foreach ($request->products as $detail) {
            $calcAmount = round(($detail['price_unit'] + $detail['price_aditional']) * $detail['quantity'],2);
            $amount += $calcAmount;
            $quotation->products()->attach($detail['id'],[
                'detail_price_buy' => Products::find($detail['id'])->product_buy,
                'detail_quantity' => $detail['quantity'],
                'detail_price_unit' => $detail['price_unit'],
                'detail_type_price' => $detail['type_ammount'],
                'detail_price_additional' => $detail['price_aditional'],
                'detail_total' => $calcAmount,
                'detail_status' => 1,
                'quotation_description' => $detail['details']
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
            'id' => $quotation->id,
            'fileName' => 'FP_' . str_replace('/','_',$quotation->quotation_code) . '_' . str_replace(' ','_',$quotation->customer->customer_name) . '.pdf'
        ]);
    }
    public function update(Request $request){
        $validator = Validator::make($request->all(),[
            'quotation_date_issue' => 'required|date',
            'quotation_type_money' => 'required|string|max:5',
            'quotation_type_change' => 'nullable|string|decimal:0,2',
            'quotation_include_igv' => 'required|boolean',
            'quotation_customer' => 'required|numeric',
            'quotation_contact' => 'required|numeric',
            'quotation_way_to_pay' => 'required|string'
        ]);
        $validator->setAttributeNames([
            'quotation_date_issue' => 'fecha de emisión',
            'quotation_type_money' => 'tipo de moneda',
            'quotation_type_change' => 'tipo de cambio',
            'quotation_include_igv' => 'incluir IGV',
            'quotation_customer' => 'cliente',
            'quotation_contact' => 'contacto',
            'quotation_way_to_pay' => 'forma de pago'
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
            'quotation_project' => $request->quotation_project,
            'quotation_change_money' => $request->quotation_type_change,
            'quotation_customer_address' => $request->quotation_address,
            'quotation_observations' => $request->quotation_observations,
            'quotation_conditions' => $request->quotation_conditions,
            'quotation_way_to_pay' => $request->quotation_way_to_pay,
            'quotation_warranty_1' => $request->quotation_warranty_1,
            'quotation_warranty_2' => $request->quotation_warranty_2,
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
                    'detail_type_price' => $detail['type_ammount'],
                    'detail_status' => 1,
                    'quotation_description' => $detail['details'],
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
        if(!is_null($quotation->order_id)){
            $controllerOrder = new OrdersController();
            $totalOrder = $controllerOrder->calculateMount($quotation->order_id);
            Orders::find($quotation->order_id)->update($totalOrder);
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cotización actualizada correctamente', 
        ]);
    }
    public function show(Request $request) {
        $quotation = Quotation::find($request->quotation,["quotation_customer","quotation_project","quotations.id","quotation_include_igv","quotation_warranty_1","quotation_warranty_2","quotation_discount","quotation_customer_contact AS quotation_contact","quotation_date_issue","quotation_type_money",'quotation_warranty',"quotation_way_to_pay","quotation_change_money AS quotation_type_change","quotation_customer_address AS quotation_address","quotation_observations","quotation_conditions","order_id"]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cotizaciones obtenida correctamente',
            'data' => [
                'quotation' => $quotation,
                'contacs' => Contacts::select("id","contact_name")->where(["contact_status" => 1,'customer_id' => $quotation->quotation_customer])->get(),
                'products' => $quotation->products()->select("quotation_description AS details","products.id","products.product_service AS is_service","products.product_name AS description","quotations_details.detail_quantity AS quantity","detail_type_price AS type_ammount","quotations_details.detail_price_unit AS price_unit","quotations_details.detail_price_additional AS price_aditional","product_public_customer AS price_public_customer","product_distributor AS price_distributor")->orderBy("quotations_details.id")->get()
            ]
        ]);
    }
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
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
            'data' => $quotations->skip($skip)->take($show)->orderBy("id","desc")->groupBy("id")->get()
        ]);
    }
    public function destroy(Request $request) {
        $quotation = Quotation::find($request->quotation);
        if(!empty($quotation->order_id)){
            return response()->json([
                'error' => true,
                'redirect' => null,
                'message' => 'No se puede eliminar la cotización porque pertenece al pedido N° ' . str_pad($quotation->order_id,5,'0',STR_PAD_LEFT)
            ]);
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModuleAll);
        $quotation->update(['quotation_status' => 0]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cotización eliminada correctamente',
        ]);
    }
}

