<?php

namespace App\Http\Controllers;

use App\Models\Configurations;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    private $urlModule = "/calculator/configuration";
    public function getConfiguration(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $data = Configurations::whereIn('description',['calculator_price_supervition','calculator_price_opertor','calculator_price_operating_margin','calculator_price_km_desplacement','calculator_price_site_visit','calculator_price_overnight_voucher','calculator_price_food_diary','calculator_price_medical_exam','calculator_price_supervition_extra','calculator_price_opertor_extra','calculator_price_hour_daily'])->get()->map(function ($item) {
            return [ $item->description => $item->value ];
        })->values();
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Configuración obtenida correctamente',
            'configuration' => $data
        ]);
    }

    public function updateConfiguration(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $request->validate([
            'calculator_price_supervition' => 'required|numeric',
            'calculator_price_opertor' => 'required|numeric',
            'calculator_price_operating_margin' => 'required|numeric',
            'calculator_price_km_desplacement' => 'required|numeric',
            'calculator_price_site_visit' => 'required|numeric',
            'calculator_price_overnight_voucher' => 'required|numeric',
            'calculator_price_food_diary' => 'required|numeric',
            'calculator_price_medical_exam' => 'required|numeric',
            'calculator_price_hour_daily' => 'required|numeric',
            'calculator_price_supervition_extra' => 'required|numeric',
            'calculator_price_opertor_extra' => 'required|numeric',
        ]);
        foreach ($request->all() as $key => $value) {
            Configurations::where('description', $key)->update(['value' => $value]);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Configuración actualizada correctamente'
        ]);
    }
}
