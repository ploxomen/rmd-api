<?php

namespace App\Observers;

use App\Models\ProductFinalAssemDeta;
use App\Models\ProductProgress;
use App\Models\ProductProgressHistory;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use App\Observers\ProductProgresDetaObserver;
class ProductFinAssemObserver
{
    public function created(ProductFinalAssemDeta $productFinalAssemDeta)
    {
        if($productFinalAssemDeta->product_finaly_type == "PRODUCTO CURSO"){
            $productProgres = ProductProgress::where(['product_id' => $productFinalAssemDeta->product_id,'product_progress_status' => 1])->first();
            if(!empty($productProgres)){
                ProductProgresDetaObserver::$disable = true;
                $productProgres->history()->create([
                    'product_id' => $productFinalAssemDeta->product_id,
                    'product_progress_history_date' => now()->toDateString(),
                    'product_progress_history_stock' => $productFinalAssemDeta->product_finaly_stock * -1,
                    'product_final_assem_id' => $productFinalAssemDeta->id
                ]);
                ProductProgresDetaObserver::$disable = false;
            }
        }else if($productFinalAssemDeta->product_finaly_type == "MATERIA PRIMA"){
            $rawMaterial = RawMaterial::where(['product_id' => $productFinalAssemDeta->product_id, 'raw_material_status' => 1])->first();
            if(!empty($rawMaterial)){
                $rawMaterial->history()->create([
                    'product_id' => $productFinalAssemDeta->product_id,
                    'material_hist_amount' => $productFinalAssemDeta->product_finaly_stock * -1,
                    'product_final_assem_id' => $productFinalAssemDeta->id,
                    'material_user' => auth()->user()->id,
                ]);
            }
        }
    }
    public function deleting(ProductFinalAssemDeta $productFinalAssemDeta){
        if(!isset($productFinalAssemDeta->id) || empty($productFinalAssemDeta->id)){
            return null;
        }
        RawMaterialHistory::where(['product_final_assem_id' => $productFinalAssemDeta->id])->get()->each(function($history){
            $history->delete();
        });
        ProductProgressHistory::where(['product_final_assem_id' => $productFinalAssemDeta->id])->get()->each(function($history){
            $history->delete();
        });
    }
    
}
