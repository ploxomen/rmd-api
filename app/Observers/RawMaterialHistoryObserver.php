<?php

namespace App\Observers;

use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Illuminate\Support\Facades\Log;

class RawMaterialHistoryObserver
{
    public function created(RawMaterialHistory $rawMaterialHistory)
    {
        $rawMaterial = RawMaterial::find($rawMaterialHistory->raw_material_id);
        $totalStock = $rawMaterial->history()->sum('material_hist_amount');
        $totalBuy = $rawMaterial->history()->sum('material_hist_total_buy_pen');
        $rawMaterial->raw_material_stock = $totalStock;
        $rawMaterial->raw_material_price_buy = $totalBuy;
        $rawMaterial->save();
    }
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
        $totalBuy = $rawMaterial->history()->sum('material_hist_total_buy_pen');
        $rawMaterial->raw_material_stock = $totalStock;
        $rawMaterial->raw_material_price_buy = $totalBuy;
        $rawMaterial->save();
    }

}
