<?php

namespace App\Observers;

use App\Models\Products;
use App\Models\RawMaterial;

class RawMaterialObserver
{
    public function created(RawMaterial $rawMaterial)
    {
        $products = Products::find($rawMaterial->product_id);
        $priceUnitPEN = $products->type_money_initial == 'PEN' ? $products->product_buy : round($products->product_buy * $products->type_change_initial, 2);
        $totalBuyPEN = $priceUnitPEN * $products->stock_initial;
        $totalBuyUSD = $products->type_change_initial > 0 && $totalBuyPEN > 0 ? round($totalBuyPEN / $products->type_change_initial, 2) : 0;
        $rawMaterial->history()->create([
            'product_id' => $products->id,
            'material_user' => auth()->user()->id,
            'material_hist_date' => today()->toDateString(),
            'material_hist_amount' => $products->stock_initial,
            'raw_hist_type' => 'ENTRADA',
            'material_hist_money' => $products->type_money_initial,
            'material_hist_total_type_change' => $products->type_change_initial,
            'type_motion' => 'INVENTARIO INICIAL',
            'material_hist_price_buy' => $priceUnitPEN,
            'material_hist_total_buy_pen' => $totalBuyPEN,
            'material_hist_total_buy_usd' => $totalBuyUSD
        ]);
    }
}
