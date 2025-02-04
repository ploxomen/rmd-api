<?php

namespace App\Http\Controllers;

use App\Models\ChangeMoney;
use Illuminate\Http\Request;

class ChangeMoneyController extends Controller
{
    private $urlModule = '/raw-material';

    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'value' => ChangeMoney::select('change_soles','change_attempts')->where('change_day',date('Y-m-d'))->first()
        ]);
    }
    public function store(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $money = ChangeMoney::select('change_soles','change_attempts')->where('change_day',date('Y-m-d'))->first();
        if(!empty($money) && $money->change_attempts === 2){
            return response()->json([
                'redirect' => $redirect,
                'error' => false,
                'alert' => 'Solo se adminten 2 cambios como máximo'
            ]);
        }
        $money = ChangeMoney::updateOrCreate([
            'change_day' => date('Y-m-d')
        ],[
            'change_soles' => $request->money,
            'change_user' => $request->user()->id,
            'change_attempts' => empty($money) ? 1 : 2
        ]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Tipo de cambios actualizado correctamente',
            'data' => $money
        ]);
    }
}
