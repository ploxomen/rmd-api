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
        $idRawMaterial = $rawMaterialHistory->raw_material_id;
        $this->totalRawMaterial($idRawMaterial);
        $rawMaterial = RawMaterial::find($idRawMaterial);
        $amount = $rawMaterialHistory->material_hist_amount + $rawMaterial->raw_hist_bala_amou;
        $total = $rawMaterialHistory->material_hist_total_buy_pen + $rawMaterial->raw_hist_bala_cost;
        $avg = round($total / $amount,2);
        $rawMaterialHistory->raw_hist_bala_amou = $amount;
        $rawMaterialHistory->raw_hist_bala_cost = $total;
        $rawMaterialHistory->raw_hist_prom_weig = $avg;
        $rawMaterialHistory->saveQuietly();
        $rawMaterial->raw_hist_bala_amou = $amount;
        $rawMaterial->raw_hist_bala_cost = $total;
        $rawMaterial->raw_hist_prom_weig = $avg;
        $rawMaterial->save();
    }
    public function updated(RawMaterialHistory $rawMaterialHistory)
    {
        if($rawMaterialHistory->wasChanged("material_hist_amount")||$rawMaterialHistory->wasChanged("material_hist_total_buy_pen")){
            $this->totalRawMaterial($rawMaterialHistory->raw_material_id);
        }
    }
    public function deleting(RawMaterialHistory $rawMaterialHistory)
    {
        $rawMaterial = RawMaterial::find($rawMaterialHistory->raw_material_id);
        $amount = $rawMaterial->raw_hist_bala_amou - $rawMaterialHistory->material_hist_amount;
        $total = $rawMaterial->raw_hist_bala_cost - $rawMaterialHistory->material_hist_total_buy_pen;
        $avg = $amount <= 0 ? 0 : round($total / $amount,2);
        $rawMaterial->raw_hist_bala_amou = $amount;
        $rawMaterial->raw_hist_bala_cost = $total;
        $rawMaterial->raw_hist_prom_weig = $avg;
        $rawMaterial->save();
    }
    public function deleted(RawMaterialHistory $rawMaterialHistory)
    {
        $this->totalRawMaterial($rawMaterialHistory->raw_material_id);
    }

}
