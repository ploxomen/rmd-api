<?php

namespace App\Observers;

use App\Models\Commodity;
use App\Models\CommodityHistory;

class CommodityHistoriesObserver
{
    public function totalCommodities(int $id)
    {
        $commodity = Commodity::find($id);
        $totalStock = $commodity->history()->sum('commodi_hist_amount');
        $totalBuy = $commodity->history()->sum('commodi_hist_total_buy');
        $commodity->commodi_stock = $totalStock;
        $commodity->commodi_price_buy = $totalBuy;
        $commodity->save();
    }
    public function created(CommodityHistory $commodityHistory)
    {
        $idCommodity = $commodityHistory->commodi_id;
        $this->totalCommodities($idCommodity);
        Commodity::calculationAvgHistory($idCommodity, $commodityHistory->commodi_hist_amount, $commodityHistory->commodi_hist_total_buy, $commodityHistory);
    }

    public function updated(CommodityHistory $commodityHistory)
    {
        if ($commodityHistory->wasChanged("commodi_hist_amount") || $commodityHistory->wasChanged("commodi_hist_total_buy")) {
            $this->totalCommodities($commodityHistory->commodi_id);
        }
    }
    public function deleting(CommodityHistory $commodityHistory)
    {
        Commodity::calculationAvgHistory($commodityHistory->commodi_id,$commodityHistory->commodi_hist_amount, $commodityHistory->commodi_hist_total_buy, $commodityHistory,'resta');
    }
    public function deleted(CommodityHistory $commodityHistory)
    {
        $this->totalCommodities($commodityHistory->commodi_id);
    }
}
