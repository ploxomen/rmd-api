<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\Products;

class ProductFinalyObserver
{
    /**
     * Handle the ProductFinaly "created" event.
     *
     * @param  \App\Models\ProductFinaly  $productFinaly
     * @return void
     */
    public function created(ProductFinaly $productFinaly)
    {
        $products = Products::find($productFinaly->product_id);
        $priceUnitPEN = $products->type_money_initial == 'PEN' ? $products->product_buy : round($products->product_buy * $products->type_money_initial, 2);
        $totalBuyPEN = $priceUnitPEN * $products->stock_initial;
        $productFinaly->assembled()->create([
            'product_finaly_user' => auth()->user()->id,
            'product_finaly_created' => today()->toDateString(),
            'product_finaly_amount' => $products->stock_initial,
            'prod_fina_type_change' => $products->type_change_initial,
            'type_motion' => 'INVENTARIO INICIAL',
            'product_finaly_total' => $totalBuyPEN,
        ]);
    }
}
