<?php

namespace App\Observers;

use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;

class RawMaterialHistoryObserver
{
    /**
     * Handle the RawMaterialHistory "created" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function created(RawMaterialHistory $rawMaterialHistory)
    {
        $rawMaterial = RawMaterial::find($rawMaterialHistory->raw_material_id);
        $totalStock = $rawMaterial->history()->sum('material_hist_amount');
        $rawMaterial->raw_material_stock = $totalStock;
        $rawMaterial->save();
    }

    /**
     * Handle the RawMaterialHistory "updated" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function updated(RawMaterialHistory $rawMaterialHistory)
    {
        //
    }

    /**
     * Handle the RawMaterialHistory "deleted" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function deleted(RawMaterialHistory $rawMaterialHistory)
    {
        $rawMaterial = RawMaterial::find($rawMaterialHistory->raw_material_id);
        $totalStock = $rawMaterial->history()->sum('material_hist_amount');
        $rawMaterial->raw_material_stock = $totalStock;
        $rawMaterial->save();
    }

    /**
     * Handle the RawMaterialHistory "restored" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function restored(RawMaterialHistory $rawMaterialHistory)
    {
        //
    }

    /**
     * Handle the RawMaterialHistory "force deleted" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function forceDeleted(RawMaterialHistory $rawMaterialHistory)
    {
        //
    }
}
