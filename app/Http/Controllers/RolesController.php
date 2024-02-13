<?php

namespace App\Http\Controllers;

use App\Models\Modules;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
    private $urlModule = '/roles';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Roles obtenidos', 
            'data' => Roles::select('id','rol_name')->get()
        ]);
    }
    public function update(Request $request) {
        $validator = Validator::make($request->all(),[
            'rol_name' => 'required|string|max:250|unique:roles,rol_name',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Complete los datos requeridos','data' => $validator->errors()]);
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Rol modificado', 
            'data' => Roles::find($request->id)->update(['rol_name' => $request->rol_name])
        ]);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'rol_name' => 'required|string|max:250|unique:roles,rol_name',
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Complete los datos requeridos','data' => $validator->errors()]);
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Rol creado', 
            'data' => Roles::create(['rol_name' => $request->rol_name])
        ]);
    }
    public function getModules(Request $request)  {
        $modules = Modules::select('id','module_title')->where('module_status',1)->get();
        foreach ($modules as $module) {
            $module->checked = $module->roles()->where('role',$request->role)->count();
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Modulos obtenidos', 
            'data' => $modules
        ]);
    }
    public function updateModules(Request $request) {
        $role = Roles::find($request->role);
        $modules = [];
        foreach ($request->modules as $module) {
            if($module['checked']){
                $modules[] = $module['id'];
            }
        }
        $role->modules()->sync($modules);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Datos modificados correctamente', 
        ]);
    }
    public function destroy(Request $request) {
        $role = Roles::find($request->role);
        $response = [
            'error' => false,
            'message' => 'Role eliminado correctamente'   
        ];
        if($role->users()->count() > 0){
            $response['error'] = true;
            $response['message'] = 'Para eliminar el rol debes de eliminar los usuarios asociados a ellos';
        }else{
            $role->modules()->detach();
            $role->delete();
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $response['redirect'] = $redirect;
        return response()->json($response);
    }
}
