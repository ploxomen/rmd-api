<?php

namespace App\Http\Controllers;

use App\Models\Configurations;
use App\Models\Contries;
use App\Models\Departaments;
use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Roles;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $urlModule = '/users';
    private $urlConfiguration = '/configurations';
    private $listColumnsUser = ["user_name","user_last_name","user_cell_phone","user_status","user_email","users.id"];
    public function updateBusiness(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlConfiguration);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => false, 
            ]);
        }
        $data = $request->all();
        foreach ($data as $key => $data) {
            Configurations::where('description',$key)->update(['value' => $data]);
        }
        return response()->json([
            'redirect' => null,
            'error' => false, 
            'message' => 'Datos actualizados correctamente',
        ]);
    }
    public function getBusiness(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlConfiguration);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => false, 
            ]);
        }
        $configurations = Configurations::all();
        return response()->json([
            'redirect' => null,
            'error' => false, 
            'message' => 'Datos obtenidos correctamente',
            'data' => $configurations 
        ]);
    }
    public function updateInfoUser(Request $request) {
        $data = $request->except("_method","avatar","delete_img");
        $validator = Validator::make($data,[
            'user_name' => 'required|string|max:250',
            'user_last_name' => 'required|string|max:250',
            'user_birthdate' => 'nullable|date',
            'user_gender' => 'nullable|string',
            'user_cell_phone' => 'nullable|string',
        ]);
        $validator->setAttributeNames([
            'user_name' => 'nombre',
            'user_last_name' => 'apellidos',
            'user_birthdate' => 'fecha de nacimiento',
            'user_gender' => 'género',
            'user_cell_phone' => 'celular',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all()]);
        }
        $user = User::find($request->user()->id);
        if($request->has('delete_img') && File::exists($user->user_avatar)){
            File::delete($user->user_avatar);
            $data['user_avatar'] = null;
        }
        if($request->has('avatar')){
            $file = $request->file('avatar');
            $fileName = time() . "_" . $file->getClientOriginalName();
            $file->move(public_path('storage/users'),$fileName);
            $data['user_avatar'] = 'storage/users/'.$fileName;
        }
        $user->update($data);
        return response()->json([
            'redirect' => null,
            'error' => false, 
            'message' => 'Datos actualizado correctamente', 
        ]);
    }
    public function index(Request $request) {
        $users = new User();
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        if($request->role != 'all'){
            $users = Roles::find($request->role)->users()->where('user_status','>',0);
        }else{
            $users = User::where('user_status','>',0);
        }
        $users = $users->where(function($query)use($search){
            $query->where('user_email','like','%'.$search.'%')
            ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')",[$search]);
        });
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'totalUsers' => $users->count(),
            'message' => 'Usuarios obtenidos correctamente',
            'data' => $users->select($this->listColumnsUser)->skip($skip)->take($show)->get()
        ]);
    }
    public function getInfoUser(Request $request) {
        $user = User::find($request->user()->id,["user_type_document","user_number_document","user_name","user_last_name","user_email","user_cell_phone","user_birthdate","user_gender","user_avatar"]);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Datos del usuario obtenidos correctamente',
            'data' => $user
        ]);
    }
    public function getContries() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Usuarios obtenidos correctamente',
            'data' => Contries::select('id','contrie')->get()
        ]);
    }
    public function userModules(Request $request) {
        $user = $request->user();
        if(!$user->roles()->where('users_roles.active',1)->count()){
            $role = $user->roles()->first();
            $user->roles()->where('role','=',$role->id)->update(['active' => 1]);
        }else{
            $role = $user->roles()->where('active',1)->first();
        }
        return response()->json([
            'error' => false,
            'redirect' => (new AuthController)->userRestrict($user,$request->url),
            'modules' => Roles::find($role->id)->modules()->where('module_status',1)->orderBy("module_order")->get(),
            'roles' => $user->roles()->get(),
            'user' => User::find($request->user()->id,["user_name","user_last_name","user_avatar"])
        ]);
    }
    public function getDepartamentsUbigeo() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Departamentos obtenidos correctamente',
            'data' => Departaments::all()
        ]);
    }
    public function getProvincesUbigeo($departament) {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Departamentos obtenidos correctamente',
            'data' => Provinces::select("id","province_name")->where('province_departament',$departament)->get()
        ]);
    }
    public function getDistrictsUbigeo($province) {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Departamentos obtenidos correctamente',
            'data' => Districts::select("id","district_name")->where('district_province',$province)->get()
        ]);
    }
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'user_name' => 'required|string|max:250',
            'user_last_name' => 'required|string|max:250',
            'user_email' => 'required|string|max:250|unique:users,user_email',
            'user_password' => 'required|string|max:255',
            'user_birthdate' => 'nullable|date',
            'user_address' => 'nullable|string',
            'user_gender' => 'nullable|string',
            'user_cell_phone' => 'nullable|string',
            'user_phone' => 'nullable|string'
        ]);
        $validator->setAttributeNames([
            'user_name' => 'nombre',
            'user_last_name' => 'apellidos',
            'user_email' => 'correo',
            'user_password' => 'contraseña',
            'user_birthdate' => 'fecha de nacimiento',
            'user_address' => 'dirección',
            'user_gender' => 'género',
            'user_cell_phone' => 'celular',
            'user_phone' => 'teléfono'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $user = User::create([
            'user_type_document' => $request->user_type_document,
            'user_number_document' => $request->user_number_document,
            'user_name' => $request->user_name,
            'user_last_name' => $request->user_last_name,
            'user_email' => $request->user_email,
            'password' => Hash::make($request->user_password),
            'user_phone' => $request->user_phone,
            'user_cell_phone' => $request->user_cell_phone,
            'user_birthdate' => $request->user_birthdate,
            'user_gender' => $request->user_gender,
            'user_address' => $request->user_address,
            'user_status' => 2
        ]);
        if($request->has('user_avatar')){
            $file = $request->file('user_avatar');
            $fileName = time() . "_" . $file->getClientOriginalName();
            $file->move(public_path('storage/users'),$fileName);
            $user->update(['user_avatar' => 'storage/users/'. $fileName ]);
        }
        $user->roles()->attach(json_decode($request->roles,true));
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Usuario creado correctamente', 
            'data' => User::select($this->listColumnsUser)->find($user->id),
        ]);
    }
    public function update(Request $request) {
        $validator = Validator::make($request->all(),[
            'user_type_document' => 'nullable|numeric',
            'user_number_document' => 'nullable|string',
            'user_name' => 'required|string|max:250',
            'user_last_name' => 'required|string|max:250',
            'user_email' => 'required|string|max:250|unique:users,user_email,'.$request->user,
            'user_birthdate' => 'nullable|date',
            'user_address' => 'nullable|string',
            'user_gender' => 'nullable|string',
            'user_cell_phone' => 'nullable|string',
            'user_phone' => 'nullable|string'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $user = User::find($request->user);
        if($request->has('delete_img') && File::exists($user->user_avatar)){
            File::delete($user->user_avatar);
            $user->update(['user_avatar' => null ]);
        }
        if($request->has('user_avatar')){
            $file = $request->file('user_avatar');
            $fileName = time() . "_" . $file->getClientOriginalName();
            $file->move(public_path('storage/users'),$fileName);
            $user->update(['user_avatar' => 'storage/users/'. $fileName ]);
        }
        $user->update([
            'user_type_document' => $request->user_type_document,
            'user_number_document' => $request->user_number_document,
            'user_name' => $request->user_name,
            'user_last_name' => $request->user_last_name,
            'user_email' => $request->user_email,
            'user_phone' => $request->user_phone,
            'user_cell_phone' => $request->user_cell_phone,
            'user_birthdate' => $request->user_birthdate,
            'user_gender' => $request->user_gender,
            'user_address' => $request->user_address,
        ]);
        $user->roles()->sync(json_decode($request->roles));
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Usuario modificado correctamente', 
            'data' => User::select($this->listColumnsUser)->find($request->user),
        ]);
    }
    public function getPasswordAdmin(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => Configurations::select('value')->where(['description'=>'password_admin_text'])->first()
        ]);
    }
    public function changePasswordAdmin(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(empty($redirect)){
            Configurations::where(['description'=>'password_admin_encrypt'])->update([
                'value' => Hash::make($request->password)
            ]);
            Configurations::where(['description'=>'password_admin_text'])->update([
                'value' => $request->password
            ]);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Usuario eliminado correctamente',
        ]);
    }
    public function getTypeDocuments() {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Tipo de documentos obtenidos correctamnente',
            'data' => TypeDocument::select("id","document_name","document_length","document_minimun")->where('document_status',1)->get()
        ]);
    }
    public function destroy(Request $request) {
        User::find($request->user)->update(['user_status' => 0]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Usuario eliminado correctamente',
        ]);
    }
    public function show(Request $request) {
        $user = User::find($request->user);
        $user->user_avatar = $user->user_avatar ? $request->root() . '/' . $user->user_avatar : null;
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Usuarios obtenidos correctamente',
            'data' => ['user' => $user->makeHidden(['created_at','user_status','updated_at']),'roles' => $user->roles()->select('rol_name','roles.id')->get()],
        ]);
    }
}
