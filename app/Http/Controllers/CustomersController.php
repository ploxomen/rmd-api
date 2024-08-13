<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use App\Models\Customers;
use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    private $urlModule = '/customers';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $customers = Customers::select("customers.id","customer_number_document","customer_name","document_name","departament_name")
        ->selectRaw("CONCAT(users.user_name,' ',users.user_last_name) AS user_creator")
        ->join('type_documents','customer_type_document','=','type_documents.id')
        ->leftJoin('districts', 'customers.customer_district', '=', 'districts.id')
        ->leftJoin('departaments', 'districts.district_departament', '=', 'departaments.id')
        ->leftJoin('users','users.id','=','user_create')
        ->where('customer_status','>',0)->where(function($query)use($search){
            $query->where('customer_name','like','%'.$search.'%')
            ->orWhere('customer_number_document','like','%'.$search.'%')
            ->orWhere('departament_name','like','%'.$search.'%');
        });
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'totalCustomers' => $customers->count(),
            'data' => $customers->skip($skip)->take($show)->get()
        ]);
    }
    public function deleteContact(Request $request) {
        if(Quotation::where('quotation_customer_contact',$request->contact)->count() > 0){
            return response()->json([
                'error' => true,
                'message' => 'No se puede eliminar el contacto porque está presente en una o varias cotizaciones',
            ]);
        }
        Contacts::find($request->contact)->delete();
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Contacto eliminado correctamente'
        ]);
    }
    public function show(Request $request) {
        $customer = Customers::find($request->customer)->makeHidden(['created_at','updated_at','customer_status']);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $provinces = [];
        $districts = [];
        if(!empty($customer->customer_district)){
            $district = Districts::find($customer->customer_district);
            $provinces = Provinces::where('province_departament',$district->district_departament)->get();
            $districts = Districts::where('district_province',$district->district_province)->get();
            $customer->customer_departament = $district->district_departament;
            $customer->customer_province = $district->district_province;
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente obtenido correctamente', 
            'data' => [
                'customer' => $customer,
                'districts' => $districts,
                'provinces' => $provinces,
                'contacts' => $customer->contacts()->select("id","contact_name","contact_number","contact_email","contact_position")->selectRaw("'old' AS type")->get()
            ]
        ]);
    }
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'customer_name' => 'required|string|max:250|unique:customers,customer_name',
            'customer_email' => 'nullable|string|max:250',
            'customer_phone' => 'nullable|string',
            'customer_cell_phone' => 'nullable|string',
            'customer_address' => 'nullable|string',
        ]);
        $validator->setAttributeNames([
            'customer_name' => 'razón social o nombres completos',
            'customer_email' => 'correo',
            'customer_phone' => 'teléfono',
            'customer_cell_phone' => 'celular',
            'customer_address' => 'dirección',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $customer = Customers::create([
            'customer_type_document' => $request->customer_type_document,
            'customer_contrie' => $request->customer_contrie,
            'customer_number_document' => $request->customer_number_document,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_cell_phone' => $request->customer_cell_phone,
            'customer_address' => $request->customer_address,
            'user_create' => $request->user()->id,
            'customer_district' => $request->customer_district,
            'customer_status' => 1
        ]);
        $contacts = [];
        foreach ($request->contacts as $contact) {
            $contacts[] = [
                'contact_name' => $contact['contact_name'],
                'contact_email' => $contact['contact_email'],
                'contact_position' => $contact['contact_position'],
                'contact_number' => $contact['contact_number'],
                'contact_status' => 1
            ];
        }
        $customer->contacts()->createMany($contacts);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente creado correctamente', 
            'data' => $customer,
        ]);
    }
    public function update(Request $request) {        
        $validator = Validator::make($request->all(),[
            'customer_name' => 'required|string|max:250|unique:customers,customer_name,'.$request->customer,
            'customer_email' => 'nullable|string|max:250',
            'customer_phone' => 'nullable|string',
            'customer_cell_phone' => 'nullable|string',
            'customer_address' => 'nullable|string',
        ]);
        $validator->setAttributeNames([
            'customer_name' => 'razón social o nombres completos',
            'customer_email' => 'correo',
            'customer_phone' => 'teléfono',
            'customer_cell_phone' => 'celular',
            'customer_address' => 'dirección',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $customer = Customers::find($request->customer);
        $customer->update([
            'customer_contrie' => $request->customer_contrie,
            'customer_type_document' => $request->customer_type_document,
            'customer_number_document' => $request->customer_number_document,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_cell_phone' => $request->customer_cell_phone,
            'customer_address' => $request->customer_address,
            'customer_district' => $request->customer_district,
        ]);
        $contactsCreate = [];
        foreach ($request->contacts as $contact) {
            $contactNew = [
                'contact_name' => $contact['contact_name'],
                'contact_number' => $contact['contact_number'],
                'contact_email' => $contact['contact_email'],
                'contact_position' => $contact['contact_position']
            ];
            if($contact['type'] == 'new'){
                $contactNew['contact_status'] = 1;
                $contactsCreate[] = $contactNew;
            }else{
                Contacts::where(['customer_id' => $request->customer,'id' => $contact['id']])->update($contactNew);
            }
        }
        $customer->contacts()->createMany($contactsCreate);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente modificado correctamente', 
            'data' => $customer,
        ]);
    }
    public function destroy(Request $request) {
        Customers::find($request->customer)->update(['customer_status' => 0]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cliente eliminado correctamente',
        ]);
    }
}
