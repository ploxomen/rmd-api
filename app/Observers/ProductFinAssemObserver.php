<?php

namespace App\Observers;

use App\Models\ProductFinalAssemDeta;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductProgress;
use App\Models\RawMaterial;
use App\Observers\ProductProgresDetaObserver;
class ProductFinAssemObserver
{
    /**
     * Handle the ProductFinalAssemDeta "created" event.
     *
     * @param  \App\Models\ProductFinalAssemDeta  $productFinalAssemDeta
     * @return void
     */
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
        $productAssembled = ProductFinalyAssembled::find($productFinalAssemDeta->product_assembled_id);
        $productAssembled->productFinaly()->update([
            'product_finaly_stock' => ProductFinalyAssembled::where('product_finaly_id',$productAssembled->product_finaly_id)->sum('product_finaly_amount')
        ]);
    }

    /**
     * Handle the ProductFinalAssemDeta "updated" event.
     *
     * @param  \App\Models\ProductFinalAssemDeta  $productFinalAssemDeta
     * @return void
     */
    public function updated(ProductFinalAssemDeta $productFinalAssemDeta)
    {
        //
    }

    /**
     * Handle the ProductFinalAssemDeta "deleted" event.
     *
     * @param  \App\Models\ProductFinalAssemDeta  $productFinalAssemDeta
     * @return void
     */
    public function deleted(ProductFinalAssemDeta $productFinalAssemDeta)
    {
        //
    }

    /**
     * Handle the ProductFinalAssemDeta "restored" event.
     *
     * @param  \App\Models\ProductFinalAssemDeta  $productFinalAssemDeta
     * @return void
     */
    public function restored(ProductFinalAssemDeta $productFinalAssemDeta)
    {
        //
    }

    /**
     * Handle the ProductFinalAssemDeta "force deleted" event.
     *
     * @param  \App\Models\ProductFinalAssemDeta  $productFinalAssemDeta
     * @return void
     */
    public function forceDeleted(ProductFinalAssemDeta $productFinalAssemDeta)
    {
        //
    }
}
