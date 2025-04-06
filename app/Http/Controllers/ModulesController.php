<?php

namespace App\Http\Controllers;

use App\Models\Modules;
use App\Models\Roles;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    private $urlModule = '/modules';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Roles obtenidos', 
            'data' => Modules::select('id','module_title','module_description')->where('module_status',1)->orderBy('module_order')->get()
        ]);
    }
    public function getRoles(Request $request)  {
        $roles = Roles::select('id','rol_name')->get();
        foreach ($roles as $role) {
            $role->checked = $role->modules()->where('module',$request->module)->count();
        }
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Roles obtenidos', 
            'data' => $roles
        ]);
    }
    public function updateRoles(Request $request) {
        $modules = Modules::find($request->module);
        $roles = [];
        foreach ($request->roles as $role) {
            if($role['checked']){
                $roles[] = $role['id'];
            }
        }
        $modules->roles()->sync($roles);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Datos modificados correctamente', 
        ]);
    }
}
