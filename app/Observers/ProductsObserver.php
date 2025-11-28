<?php

namespace App\Observers;

use App\Models\Commodity;
use App\Models\CommodityHistory;
use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\Products;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Illuminate\Support\Facades\Log;

class ProductsObserver
{
    public function updated(Products $products)
    {
        if ($products->wasChanged('stock_initial') || $products->wasChanged('product_buy') || $products->wasChanged('type_money_initial')) {
            $store = $products->wasChanged('product_store') ? $products->getOriginal('product_store') : $products->product_store;
            $priceUnitPEN = $products->type_money_initial == 'PEN' ? $products->product_buy : round($products->product_buy * $products->type_money_initial, 2);
            $totalBuyPEN = $priceUnitPEN * $products->stock_initial;
            $totalBuyUSD = round($totalBuyPEN / $products->type_change_initial, 2);
            if ($store === "PRODUCTO MERCADERIA") {
                $commodity = Commodity::where('product_id', $products->id)->active()->first();
                if ($commodity) {
                    CommodityHistory::where(['type_motion' => 'INVENTARIO INICIAL', 'commodi_id' => $commodity->id])->update([
                        'commodi_hist_amount' => $products->stock_initial,
                        'commodi_hist_price_buy' => $priceUnitPEN,
                        'commodi_hist_total_buy' => $totalBuyPEN,
                        'commodi_hist_total_buy_usd' => $totalBuyUSD,
                        'material_hist_money' => $products->type_money_initial,
                        'material_hist_total_type_change' => $products->type_change_initial,
                    ]);
                }
            } else if ($store === "MATERIA PRIMA") {
                $rawMaterial = RawMaterial::where('product_id', $products->id)->active()->first();
                if ($rawMaterial) {
                    RawMaterialHistory::where(['type_motion' => 'INVENTARIO INICIAL', 'raw_material_id' => $rawMaterial->id])->update([
                        'material_hist_amount' => $products->stock_initial,
                        'material_hist_price_buy' => $priceUnitPEN,
                        'material_hist_total_buy_pen' => $totalBuyPEN,
                        'material_hist_total_buy_usd' => $totalBuyUSD,
                        'material_hist_money' => $products->type_money_initial,
                        'material_hist_total_type_change' => $products->type_change_initial,
                    ]);
                }
            } else if ($store === "PRODUCTO TERMINADO") {
                $productFinaly = ProductFinaly::where('product_id', $products->id)->productExist()->first();
                if ($productFinaly) {
                    ProductFinalyAssembled::where(['type_motion' => 'INVENTARIO INICIAL', 'product_finaly_id' => $productFinaly->id])->update([
                        'product_finaly_amount' => $products->stock_initial,
                        'prod_fina_type_change' => $products->type_change_initial,
                        'product_finaly_total' => $totalBuyPEN,
                    ]);
                }
            }
        } else if ($products->wasChanged('product_store')) {
            $originalStore = $products->getOriginal('product_store');
            if ($originalStore === "PRODUCTO MERCADERIA") {
                CommodityHistory::where(['type_motion' => 'INVENTARIO INICIAL', 'product_id' => $products->id])->get()->each(fn ($row) => $row->delete());
            } else if ($originalStore === "MATERIA PRIMA") {
                RawMaterialHistory::where(['type_motion' => 'INVENTARIO INICIAL', 'product_id' => $products->id])->get()->each(fn ($row) => $row->delete());
            } else if ($originalStore === "PRODUCTO TERMINADO") {
                $productFinaly = ProductFinaly::where('product_id', $products->id)->productExist()->first();
                ProductFinalyAssembled::where(['type_motion' => 'INVENTARIO INICIAL', 'product_finaly_id' => $productFinaly->id])->get()->each(fn ($row) => $row->delete());
            }
        }
    }
}
