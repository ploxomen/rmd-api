<?php

namespace App\Observers;

use App\Models\Commodity;
use App\Models\CommodityHistory;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use Illuminate\Support\Facades\Log;

class ShoppinDetailObserver
{

    public function created(ShoppingDetail $shoppingDetail)
    {
        $shopping = Shopping::find($shoppingDetail->shopping_id);
        if ($shoppingDetail->shopping_deta_store == "MATERIA PRIMA") {
            $rawMaterial = RawMaterial::where(['product_id' => $shoppingDetail->shopping_product, 'raw_material_status' => 1])->first();
            if(empty($rawMaterial)){
                return null;
            }
            $rawMaterial->history()->create([
                'product_id' => $shoppingDetail->shopping_product,
                'material_hist_money' => $shopping->buy_type_money,
                'material_hist_amount' => $shoppingDetail->shopping_deta_ammount,
                'material_hist_total_buy_pen' => $shoppingDetail->shopping_deta_subtotal,
                'material_hist_total_buy_usd' => $shoppingDetail->shopping_deta_subtotal_usd,
                'material_hist_total_type_change' => $shopping->buy_type_change,
                'material_hist_price_buy' => $shoppingDetail->shopping_deta_price,
                'raw_hist_type' => 'ENTRADA',
                'shopping_detail_id' => $shoppingDetail->id,
                'material_user' => auth()->user()->id,
            ]);
        }else if($shoppingDetail->shopping_deta_store == "MERCADERIA"){
            $commodity = Commodity::where(['product_id' => $shoppingDetail->shopping_product, 'commodi_status' => 1])->first();
            if(empty($commodity)){
                return null;
            }
            $commodity->history()->create([
                'product_id' => $shoppingDetail->shopping_product,
                'commodi_hist_money' => $shopping->buy_type_money,
                'commodi_hist_amount' => $shoppingDetail->shopping_deta_ammount,
                'commodi_hist_total_buy' => $shoppingDetail->shopping_deta_subtotal,
                'commodi_hist_total_buy_usd' => $shoppingDetail->shopping_deta_subtotal_usd,
                'commodi_hist_type_change' => $shopping->buy_type_change,
                'commodi_hist_price_buy' => $shoppingDetail->shopping_deta_price,
                'commodi_hist_type' => 'ENTRADA',
                'shopping_detail_id' => $shoppingDetail->id,
                'commodi_hist_user' => auth()->user()->id,
            ]);
        }
    }
    public function updated(ShoppingDetail $shoppingDetail)
    {
        
    }
    public function deleting(ShoppingDetail $shoppingDetail)
    {
        Log::info('observer',['pase por aqui']);
        RawMaterialHistory::where(['shopping_detail_id' => $shoppingDetail->id])->get()->each(function($history){
            $history->delete();
        });
        CommodityHistory::where(['shopping_detail_id' => $shoppingDetail->id])->get()->each(function($history){
            $history->delete();
        });
    }
}
