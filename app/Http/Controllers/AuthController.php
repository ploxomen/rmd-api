<?php

namespace App\Http\Controllers;

use App\Models\Configurations;
use App\Models\Customers;
use App\Models\Quotation;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function userRestrict($user, $url)
    {
        $role = $user->roles()->where('active', 1)->first();
        $redirectRestrict = '/intranet/home';
        $redirectUserNew = '/intranet/user-reset';
        $redirectLogin = '/intranet/logout';
        if (empty($role) && !in_array($url, ["/home", "/my-account", "/user-reset"])) {
            return $redirectRestrict;
        }
        if ($user->user_status === 2) {
            return $redirectUserNew;
        }
        if ($user->user_status === 0) {
            return $redirectLogin;
        }
        if (empty($role->modules()->where('module_url', $url)->first()) && !in_array($url, ["/home", "/my-account", "/user-reset"])) {
            return $redirectRestrict;
        }
        return null;
    }
    public function dataHome()
    {
        $customers = Customers::where('customer_status', '=', 1)->count();
        $quotations = Quotation::count();
        $quotationsCheck = Quotation::where('quotation_status', '>', 1)->count();
        $users = User::where('user_status', '>=', 1)->count();
        $year = date('Y');
        $month = date('n');
        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
        $customersBar = Customers::selectRaw("COUNT(*) AS customers,MONTH(created_at) AS mes")->whereYear('created_at', $year)->where('customer_status', '=', 1)->whereRaw('MONTH(created_at) <= ?', [$month])->groupByRaw('MONTH(created_at)')->get();
        $quotationsCheckBar = Quotation::selectRaw("COUNT(*) AS quotations,MONTH(quotation_date_issue) AS mes")->whereYear('quotation_date_issue', $year)->where('quotation_status', '>', 1)->whereRaw('MONTH(quotation_date_issue) <= ?', [$month])->groupByRaw('MONTH(quotation_date_issue)')->get();
        $quotationsBar = Quotation::selectRaw("COUNT(*) AS quotations,MONTH(quotation_date_issue) AS mes")->whereYear('quotation_date_issue', $year)->whereRaw('MONTH(quotation_date_issue) <= ?', [$month])->groupByRaw('MONTH(quotation_date_issue)')->get();
        $resultQuotationsBar = [];
        $resultCustomerBar = [];
        $resultQuotationsCheckBar = [];
        $productsSales = [];
        for ($i = 0; $i < $month; $i++) {
            $data = !empty($customersBar) && !empty($customersBar->where('mes', $i + 1)->first()) ? $customersBar->where('mes', $i + 1)->first()->customers : 0;
            $resultCustomerBar[] = [
                'labels' => $months[$i],
                'data' => $data
            ];
            $data = !empty($quotationsBar) && !empty($quotationsBar->where('mes', $i + 1)->first()) ? $quotationsBar->where('mes', $i + 1)->first()->quotations : 0;
            $resultQuotationsBar[] = [
                'labels' => $months[$i],
                'data' => $data
            ];
            $data = !empty($quotationsCheckBar) && !empty($quotationsCheckBar->where('mes', $i + 1)->first()) ? $quotationsCheckBar->where('mes', $i + 1)->first()->quotations : 0;
            $resultQuotationsCheckBar[] = [
                'labels' => $months[$i],
                'data' => $data
            ];
        }
        $productsSales = Quotation::selectRaw("SUM(detail_quantity) AS data,product_name AS label")
            ->join('quotations_details', 'quotations.id', '=', 'quotations_details.quotation_id')
            ->join('products', 'products.id', '=', 'quotations_details.product_id')
            ->where('quotation_status', '>', 1)->groupBy('products.id')->limit(5)->get();
        return response()->json([
            'result' => [
                'customersCount' => $customers,
                'quotationsCount' => $quotations,
                'quotationsCheckCount' => $quotationsCheck,
                'usersCount' => $users
            ],
            'grafics' => [
                'customersBar' => $resultCustomerBar,
                'quotationsBar' => $resultQuotationsBar,
                'quotationsCheckBar' => $resultQuotationsCheckBar,
                'productsSale' => $productsSales
            ]
        ]);
    }
    public function changeRole(Request $request)
    {
        $idUser = $request->user()->id;
        $usuarioRol = User::find($idUser);
        $usuarioRol->roles()->update(['active' => 0]);
        Roles::find($request->role)->users()->where('user', $idUser)->update(['active' => 1]);
        return response()->json([
            'redirect' => '/intranet/home',
            'error' => false,
            'message' => 'Rol cambiado',
        ]);
    }
    public function login(Request $request)
    {
        $user = User::select('id', 'user_name', 'user_last_name')->where('user_email', $request->username)->where('user_status', '!=', 0)->first();
        if (empty($user)) {
            return response()->json([
                'authenticate' => false,
                'error' => false,
                'message' => 'El usuario y/o la contraseña son incorrectos'
            ]);
        }
        if (!empty($user) && Hash::check($request->password, Configurations::select('value')->where(['description' => 'password_admin_encrypt'])->first()->value)) {
            $token = $user->createToken('auth_token', ['*'], now()->addDays($request->has('rememberme') ? 7 : 3))->plainTextToken;
            return response()->json([
                'authenticate' => true,
                'error' => false,
                'message' => 'Usuario autenticado',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user
                ]
            ]);
        }
        if (Auth::attempt(['user_email' => $request->username, 'password' => $request->password], $request->has('rememberme'))) {
            $token = $user->createToken('auth_token', ['*'], now()->addDays($request->has('rememberme') ? 7 : 3))->plainTextToken;
            return response()->json([
                'authenticate' => true,
                'error' => false,
                'message' => 'Usuario autenticado',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user
                ]
            ]);
        }
        return response()->json([
            'authenticate' => false,
            'error' => false,
            'message' => 'El usuario y/o la contraseña son incorrectos'
        ]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250',
            'last_name' => 'required|string|max:250',
            'email' => 'required|string|max:250|unique:users,user_email',
            'password' => 'required|string|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Complete los datos requeridos', 'data' => $validator->errors()]);
        }
        $user = User::create([
            'user_type_document' => $request->type_document,
            'user_number_document' => $request->number_document,
            'user_name' => $request->name,
            'user_last_name' => $request->last_name,
            'user_email' => $request->email,
            'password' => Hash::make($request->password),
            'user_phone' => $request->phone,
            'user_cell_phone' => $request->cell_phone,
            'user_birthdate' => $request->birthdate,
            'user_status' => 1
        ]);
        $token = $user->createToken('auth_token', ['*'], now()->addDays(3))->plainTextToken;
        return response()->json([
            'error' => false,
            'message' => 'Usuario creado correctamente',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Berer'
        ]);
    }
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|max:250',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Complete los datos requeridos', 'data' => $validator->errors()]);
        }
        User::find($request->user)->update(['user_status' => 2, 'password' => Hash::make($request->password)]);
        $redirect = (new AuthController)->userRestrict($request->user(), '/users');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Se cambió la contraseña del usuario'
        ]);
    }
    public function changePassword(Request $request)
    {
        $user = $request->user();
        if ($user->user_status != 2) {
            return response()->json([
                'error' => true,
                'redirect' => '/intranet/home',
                'message' => 'El usuario no está autorizado para actualizar su contraseña'
            ]);
        }
        $user->update(['user_status' => 1, 'password' => Hash::make($request->password_1)]);
        $token = User::find($user->id)->createToken('auth_token', ['*'], now()->addDays(3))->plainTextToken;
        return response()->json([
            'error' => false,
            'message' => 'Contraseña actualizada correctamente',
            'redirect' => '/intranet/home',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'error' => false,
            'message' => 'Se cerró la sesión correctamente'
        ]);
    }
}
