<?php

namespace App\Observers;

use App\Models\Commodity;
use App\Models\Products;

class CommodityObserver
{
    /**
     * Handle the Commodity "created" event.
     *
     * @param  \App\Models\Commodity  $commodity
     * @return void
     */
    public function created(Commodity $commodity)
    {
        $products = Products::find($commodity->product_id);
        $priceUnitPEN = $products->type_money_initial == 'PEN' ? $products->product_buy : round($products->product_buy * $products->type_money_initial, 2);
        $totalBuyPEN = $priceUnitPEN * $products->stock_initial;
        $totalBuyUSD = $products->type_change_initial > 0 ? round($totalBuyPEN / $products->type_change_initial, 2) : 0;
        $commodity->history()->create([
            'product_id' => $products->id,
            'commodi_hist_user' => auth()->user()->id,
            'commodi_hist_date' => today()->toDateString(),
            'commodi_hist_amount' => $products->stock_initial,
            'commodi_hist_type' => 'ENTRADA',
            'commodi_hist_money' => $products->type_money_initial,
            'commodi_hist_type_change' => $products->type_change_initial,
            'type_motion' => 'INVENTARIO INICIAL',
            'commodi_hist_price_buy' => $priceUnitPEN,
            'commodi_hist_total_buy' => $totalBuyPEN,
            'commodi_hist_total_buy_usd' => $totalBuyUSD
        ]);
    }
}
