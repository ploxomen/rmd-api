<?php

namespace App\Observers;

use App\Models\ProductProgress;
use App\Models\ProductProgressHistory;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;

class ProductProgresDetaObserver
{
    public static bool $disable = false;
    public function totalProductProgress(int $id){
        $productProgres = ProductProgress::find($id);
        $totalStock = $productProgres->history()->sum('product_progress_history_stock');
        $productProgres->product_progress_stock = $totalStock;
        $productProgres->save();
    }
    public function created(ProductProgressHistory $productProgressHistory)
    {
        if(!self::$disable){
            $rawMaterial = RawMaterial::where(['product_id' => $productProgressHistory->product_id, 'raw_material_status' => 1])->first();
            if(!empty($rawMaterial)) {
                $amount = $productProgressHistory->product_progress_history_stock * -1;
                $priceUnit = $rawMaterial->raw_hist_prom_weig;
                $subtotal = $amount * $priceUnit;
                $rawMaterial->history()->create([
                    'product_id' => $productProgressHistory->product_id,
                    'material_hist_amount' => $amount,
                    'material_hist_total_buy_pen' => $subtotal,
                    'material_hist_price_buy' => $priceUnit,
                    'raw_hist_type' => 'SALIDA',
                    'product_progres_hist_id' => $productProgressHistory->id,
                    'material_user' => auth()->user()->id,
                ]);
            }
        }
        $idProductProgress = $productProgressHistory->product_progress_id;
        $productProgress = ProductProgress::find($idProductProgress);
        $amount = $productProgressHistory->product_progress_history_stock + $productProgress->prod_prog_bala_amou;
        $total = $productProgressHistory->product_progress_history_total + $productProgress->prod_prog_bala_cost;
        $avg = round($total / $amount,2);
        $productProgressHistory->prod_prog_hist_bala_amou = $amount;
        $productProgressHistory->prod_prog_hist_bala_cost = $total;
        $productProgressHistory->prod_prog_hist_prom_weig = $avg;
        $productProgressHistory->saveQuietly();
        $productProgress->prod_prog_bala_amou = $amount;
        $productProgress->prod_prog_bala_cost = $total;
        $productProgress->prod_prog_prom_weig = $avg;
        $productProgress->save();
        $this->totalProductProgress($idProductProgress);
    }
    public function updated(ProductProgressHistory $productProgressHistory)
    {
        RawMaterialHistory::where('product_progres_hist_id',$productProgressHistory->id)->get()->each(function($item) use ($productProgressHistory) {
            $item->material_hist_amount = $productProgressHistory->product_progress_history_stock * -1;
            $item->save();
        });
        $this->totalProductProgress($productProgressHistory->product_progress_id);
    }
    public function deleting(ProductProgressHistory $productProgressHistory){
        $productProgress = ProductProgress::find($productProgressHistory->product_progress_id);
        $amount = $productProgress->prod_prog_bala_amou - $productProgressHistory->product_progress_history_stock;
        $total = $productProgress->prod_prog_bala_cost - $productProgressHistory->product_progress_history_total;
        $avg = $amount <= 0 ? 0 : round($total / $amount,2);
        $productProgress->prod_prog_bala_amou = $amount;
        $productProgress->prod_prog_bala_cost = $total;
        $productProgress->prod_prog_prom_weig = $avg;
        $productProgress->save();
        RawMaterialHistory::where('product_progres_hist_id',$productProgressHistory->id)->get()->each(function($item) {
            $item->delete();
        });
    }
    public function deleted(ProductProgressHistory $productProgressHistory)
    {
        $this->totalProductProgress($productProgressHistory->product_progress_id);   
    }
}
