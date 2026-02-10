<?php

namespace App\Observers;

use App\Models\Commodity;
use App\Models\CommodityHistory;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use App\Models\Shopping;
use App\Models\ShoppingDetail;

class ShoppinDetailObserver
{

    public function customCreatedRawMaterial(ShoppingDetail $shoppingDetail, Shopping $shopping)
    {
        $rawMaterial = RawMaterial::where(['product_id' => $shoppingDetail->shopping_product, 'raw_material_status' => 1])->first();
        if (empty($rawMaterial)) {
            return null;
        }
        return $rawMaterial->history()->create([
            'product_id' => $shoppingDetail->shopping_product,
            'material_hist_date' => $shopping->buy_date,
            'material_hist_guide' => $shopping->buy_number_guide,
            'raw_provider' => $shopping->buy_provider,
            'material_hist_money' => $shopping->buy_type_money,
            'material_hist_amount' => $shoppingDetail->shopping_deta_ammount,
            'material_hist_total_buy_pen' => $shoppingDetail->shopping_deta_subtotal,
            'created_at' => $shoppingDetail->created_at,
            'material_hist_total_buy_usd' => $shoppingDetail->shopping_deta_subtotal_usd,
            'material_hist_total_type_change' => $shopping->buy_type_change,
            'material_hist_price_buy' => $shoppingDetail->shopping_deta_price,
            'raw_hist_type' => 'ENTRADA',
            'type_motion' => 'COMPRA',
            'shopping_detail_id' => $shoppingDetail->id,
            'material_user' => auth()->user()->id,
        ]);
    }
    public function customCreatedCommodity(ShoppingDetail $shoppingDetail, Shopping $shopping)
    {
        $commodity = Commodity::where(['product_id' => $shoppingDetail->shopping_product, 'commodi_status' => 1])->first();
        if (empty($commodity)) {
            return null;
        }
        return $commodity->history()->create([
            'product_id' => $shoppingDetail->shopping_product,
            'commodi_hist_date' => $shopping->buy_date,
            'commodi_hist_bill' => $shopping->buy_number_invoice,
            'commodi_hist_guide' => $shopping->buy_number_guide,
            'commodi_hist_money' => $shopping->buy_type_money,
            'commodity_provider' => $shopping->buy_provider,
            'commodi_hist_amount' => $shoppingDetail->shopping_deta_ammount,
            'commodi_hist_total_buy' => $shoppingDetail->shopping_deta_subtotal,
            'commodi_hist_total_buy_usd' => $shoppingDetail->shopping_deta_subtotal_usd,
            'commodi_hist_type_change' => $shopping->buy_type_change,
            'commodi_hist_price_buy' => $shoppingDetail->shopping_deta_price,
            'commodi_hist_type' => 'ENTRADA',
            'type_motion' => 'COMPRA',
            'created_at' => $shoppingDetail->created_at,
            'shopping_detail_id' => $shoppingDetail->id,
            'commodi_hist_user' => auth()->user()->id,
        ]);
    }
    public function created(ShoppingDetail $shoppingDetail)
    {
        $shopping = Shopping::find($shoppingDetail->shopping_id);
        if ($shoppingDetail->shopping_deta_store == "MATERIA PRIMA") {
            $this->customCreatedRawMaterial($shoppingDetail, $shopping);
        } else if ($shoppingDetail->shopping_deta_store == "MERCADERIA") {
            $this->customCreatedCommodity($shoppingDetail, $shopping);
        }
    }
    public function updating(ShoppingDetail $shoppingDetail)
    {
        $dirty = $shoppingDetail->getDirty();
        if (isset($dirty['shopping_product']) && $dirty['shopping_product']  != $shoppingDetail->shopping_product) {
            $shopping = Shopping::find($shoppingDetail->shopping_id);
            $shoppindDetailOld = isset($dirty['shopping_deta_store']) ? $dirty['shopping_deta_store'] : $shoppingDetail->shopping_deta_store;
            if ($shoppindDetailOld == 'MATERIA PRIMA') {
                RawMaterialHistory::where(['shopping_detail_id' => $dirty['shopping_product']])->get()->each(function ($history) {
                    $history->delete();
                });
            } else if ($shoppindDetailOld == 'MERCADERIA') {
                CommodityHistory::where(['shopping_detail_id' => $dirty['shopping_product']])->get()->each(function ($history) {
                    $history->delete();
                });
            }
            if ($shoppingDetail->shopping_deta_store == 'MATERIA PRIMA') {
                $this->customCreatedRawMaterial($shoppingDetail, $shopping);
            } else if ($shoppingDetail->shopping_deta_store == 'MERCADERIA') {
                $this->customCreatedCommodity($shoppingDetail, $shopping);
            }
        }
    }
    public function updated(ShoppingDetail $shoppingDetail)
    {
        if (!$shoppingDetail->wasChanged('shopping_product') && ($shoppingDetail->wasChanged('shopping_deta_ammount') || $shoppingDetail->wasChanged('shopping_deta_price'))) {
            if ($shoppingDetail->shopping_deta_store == 'MATERIA PRIMA') {
                RawMaterialHistory::where(['shopping_detail_id' => $shoppingDetail->id])->update([
                    'material_hist_amount' => $shoppingDetail->shopping_deta_ammount,
                    'material_hist_price_buy' => $shoppingDetail->shopping_deta_price,
                    'material_hist_total_buy_pen' => $shoppingDetail->shopping_deta_subtotal,
                    'material_hist_total_buy_usd' => $shoppingDetail->shopping_deta_subtotal_usd,
                ]);
                return true;
            }
            CommodityHistory::where(['shopping_detail_id' => $shoppingDetail->id])->update([
                'commodi_hist_amount' => $shoppingDetail->shopping_deta_ammount,
                'commodi_hist_price_buy' => $shoppingDetail->shopping_deta_price,
                'commodi_hist_total_buy' => $shoppingDetail->shopping_deta_subtotal,
                'commodi_hist_total_buy_usd' => $shoppingDetail->shopping_deta_subtotal_usd,
            ]);
        }
    }
    public function deleting(ShoppingDetail $shoppingDetail)
    {
        RawMaterialHistory::where(['shopping_detail_id' => $shoppingDetail->id])->get()->each(function ($history) {
            $history->delete();
        });
        CommodityHistory::where(['shopping_detail_id' => $shoppingDetail->id])->get()->each(function ($history) {
            $history->delete();
        });
    }
}
