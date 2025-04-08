<?php

namespace App\Observers;

use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Illuminate\Support\Facades\Log;

class RawMaterialHistoryObserver
{
    public function totalRawMaterial(int $id){
        $rawMaterial = RawMaterial::find($id);
        $totalStock = $rawMaterial->history()->sum('material_hist_amount');
        $totalBuy = $rawMaterial->history()->sum('material_hist_total_buy_pen');
        $rawMaterial->raw_material_stock = $totalStock;
        $rawMaterial->raw_material_price_buy = $totalBuy;
        $rawMaterial->save();
    }
    public function created(RawMaterialHistory $rawMaterialHistory)
    {
        $this->totalRawMaterial($rawMaterialHistory->raw_material_id);
    }
    public function updated(RawMaterialHistory $rawMaterialHistory)
    {
        if($rawMaterialHistory->wasChanged("material_hist_amount")||$rawMaterialHistory->wasChanged("material_hist_total_buy_pen")){
            $this->totalRawMaterial($rawMaterialHistory->raw_material_id);
        }
    }
    public function deleted(RawMaterialHistory $rawMaterialHistory)
    {
        $this->totalRawMaterial($rawMaterialHistory->raw_material_id);
    }

}
