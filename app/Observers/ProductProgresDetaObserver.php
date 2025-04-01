<?php

namespace App\Observers;

use App\Models\ProductProgress;
use App\Models\ProductProgressHistory;
use App\Models\RawMaterial;

class ProductProgresDetaObserver
{
    public static bool $disable = false;
    /**
     * Handle the ProductProgressHistory "created" event.
     *
     * @param  \App\Models\ProductProgressHistory  $productProgressHistory
     * @return void
     */
    public function created(ProductProgressHistory $productProgressHistory)
    {
        if(!self::$disable){
            $rawMaterial = RawMaterial::where(['product_id' => $productProgressHistory->product_id, 'raw_material_status' => 1])->first();
            if(!empty($rawMaterial)) {
                $rawMaterial->history()->create([
                    'product_id' => $productProgressHistory->product_id,
                    'material_hist_amount' => $productProgressHistory->product_progress_history_stock * -1,
                    'product_progres_hist_id' => $productProgressHistory->id,
                    'material_user' => auth()->user()->id,
                ]);
            }
        }
        $productProgres = ProductProgress::find($productProgressHistory->product_progress_id);
        $totalStock = $productProgres->history()->sum('product_progress_history_stock');
        $productProgres->product_progress_stock = $totalStock;
        $productProgres->save();
    }

    /**
     * Handle the ProductProgressHistory "updated" event.
     *
     * @param  \App\Models\ProductProgressHistory  $productProgressHistory
     * @return void
     */
    public function updated(ProductProgressHistory $productProgressHistory)
    {
        //
    }

    /**
     * Handle the ProductProgressHistory "deleted" event.
     *
     * @param  \App\Models\ProductProgressHistory  $productProgressHistory
     * @return void
     */
    public function deleted(ProductProgressHistory $productProgressHistory)
    {
        $productProgres = ProductProgress::find($productProgressHistory);
        $totalStock = $productProgres->history()->sum('product_progress_history_stock');
        $productProgres->product_progress_stock = $totalStock;
        $productProgres->save();
    }

    /**
     * Handle the ProductProgressHistory "restored" event.
     *
     * @param  \App\Models\ProductProgressHistory  $productProgressHistory
     * @return void
     */
    
}
