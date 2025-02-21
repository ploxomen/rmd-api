<?php

namespace App\Http\Controllers;

use App\Models\Districts;
use App\Models\Provider;
use App\Models\ProviderContacts;
use App\Models\Provinces;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    private $urlModule = '/provider/all';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $provider = Provider::select("provider.id","provider_number_document","provider_name","document_name","departament_name")
        ->selectRaw("CONCAT(users.user_name,' ',users.user_last_name) AS user_creator")
        ->join('type_documents','provider_type_document','=','type_documents.id')
        ->leftJoin('districts', 'provider.provider_district', '=', 'districts.id')
        ->leftJoin('departaments', 'districts.district_departament', '=', 'departaments.id')
        ->leftJoin('users','users.id','=','user_create')
        ->where('provider_status','>',0)->where(function($query)use($search){
            $query->where('provider_name','like','%'.$search.'%')
            ->orWhere('provider_number_document','like','%'.$search.'%')
            ->orWhere('departament_name','like','%'.$search.'%');
        });
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Clientes obtenidos correctamente',
            'totalCustomers' => $provider->count(),
            'data' => $provider->skip($skip)->take($show)->get()
        ]);
    }
    public function deleteContact(Request $request) {
        ProviderContacts::find($request->contact)->delete();
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Contacto eliminado correctamente'
        ]);
    }
    public function show(Request $request) {
        $provider = Provider::find($request->provider)->makeHidden(['created_at','updated_at','provider_status']);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $provinces = [];
        $districts = [];
        if(!empty($provider->provider_district)){
            $district = Districts::find($provider->provider_district);
            $provinces = Provinces::where('province_departament',$district->district_departament)->get();
            $districts = Districts::where('district_province',$district->district_province)->get();
            $provider->provider_departament = $district->district_departament;
            $provider->provider_province = $district->district_province;
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente obtenido correctamente', 
            'data' => [
                'customer' => $provider,
                'districts' => $districts,
                'provinces' => $provinces,
                'contacts' => $provider->contacts()->select("id","provider_name","provider_number","provider_email","provider_position")->selectRaw("'old' AS type")->get()
            ]
        ]);
    }
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'provider_name' => 'required|string|max:250|unique:provider,provider_name',
            'provider_email' => 'nullable|string|max:250',
            'provider_phone' => 'nullable|string',
            'provider_cell_phone' => 'nullable|string',
            'provider_address' => 'nullable|string',
        ]);
        $validator->setAttributeNames([
            'provider_name' => 'razón social o nombres completos',
            'provider_email' => 'correo',
            'provider_phone' => 'teléfono',
            'provider_cell_phone' => 'celular',
            'provider_address' => 'dirección',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $provider = Provider::create([
            'provider_type_document' => $request->provider_type_document,
            'provider_contrie' => $request->provider_contrie,
            'provider_number_document' => $request->provider_number_document,
            'provider_name' => $request->provider_name,
            'provider_email' => $request->provider_email,
            'provider_phone' => $request->provider_phone,
            'provider_cell_phone' => $request->provider_cell_phone,
            'provider_address' => $request->provider_address,
            'user_create' => $request->user()->id,
            'provider_district' => $request->provider_district,
            'provider_status' => 1,
        ]);
        $contacts = [];
        foreach ($request->contacts as $contact) {
            $contacts[] = [
                'provider_name' => $contact['provider_name'],
                'provider_email' => $contact['provider_email'],
                'provider_position' => $contact['provider_position'],
                'provider_number' => $contact['provider_number'],
                'provider_status' => 1
            ];
        }
        $provider->contacts()->createMany($contacts);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente creado correctamente', 
            'data' => $provider,
        ]);
    }
    public function update(Request $request) {        
        $validator = Validator::make($request->all(),[
            'provider_name' => 'required|string|max:250|unique:provider,provider_name,'.$request->provider,
            'provider_email' => 'nullable|string|max:250',
            'provider_phone' => 'nullable|string',
            'provider_cell_phone' => 'nullable|string',
            'provider_address' => 'nullable|string',
        ]);
        $validator->setAttributeNames([
            'provider_name' => 'razón social o nombres completos',
            'provider_email' => 'correo',
            'provider_phone' => 'teléfono',
            'provider_cell_phone' => 'celular',
            'provideraddress' => 'dirección',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $provider = Provider::find($request->provider);
        $provider->update([
            'provider_contrie' => $request->provider_contrie,
            'provider_type_document' => $request->provider_type_document,
            'provider_number_document' => $request->provider_number_document,
            'provider_name' => $request->provider_name,
            'provider_email' => $request->provider_email,
            'provider_phone' => $request->provider_phone,
            'provider_cell_phone' => $request->provider_cell_phone,
            'provider_address' => $request->provider_address,
            'provider_district' => $request->provider_district,
        ]);
        $contactsCreate = [];
        foreach ($request->contacts as $contact) {
            $contactNew = [
                'provider_name' => $contact['provider_name'],
                'provider_number' => $contact['provider_number'],
                'provider_email' => $contact['provider_email'],
                'provider_position' => $contact['provider_position']
            ];
            if($contact['type'] == 'new'){
                $contactNew['contact_status'] = 1;
                $contactsCreate[] = $contactNew;
            }else{
                ProviderContacts::where(['provider_id' => $request->provider,'id' => $contact['id']])->update($contactNew);
            }
        }
        $provider->contacts()->createMany($contactsCreate);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente modificado correctamente', 
            'data' => $provider,
        ]);
    }
    public function destroy(Request $request) {
        Provider::find($request->provider)->update(['provider_status' => 0]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Cliente eliminado correctamente',
        ]);
    }
}
