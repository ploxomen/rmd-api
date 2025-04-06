<?php

namespace App\Http\Controllers;

use App\Models\ChangeMoney;
use Illuminate\Http\Request;

class ChangeMoneyController extends Controller
{

    public function index(Request $request) {
        return response()->json([
            'redirect' => null,
            'error' => false,
            'value' => ChangeMoney::select('change_soles','change_attempts')->where('change_day',date('Y-m-d'))->first()
        ]);
    }
    public function store(Request $request) {
        $money = ChangeMoney::select('change_soles','change_attempts')->where('change_day',date('Y-m-d'))->first();
        if(!empty($money) && $money->change_attempts === 2){
            return response()->json([
                'redirect' => null,
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
            'redirect' => null,
            'error' => false,
            'message' => 'Tipo de cambios actualizado correctamente',
            'data' => $money
        ]);
    }
}
