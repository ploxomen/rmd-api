<?php

namespace App\Models;

use App\Observers\CommodityHistoriesObserver;
use Illuminate\Database\Eloquent\Model;

class CommodityHistory extends Model
{
    protected $fillable = [
        'commodi_id',
        'product_id',
        'commodi_hist_bill',
        'commodi_hist_guide',
        'commodi_hist_amount',
        'commodi_hist_price_buy',
        'commodi_hist_money',
        'commodi_hist_total_buy',
        'commodi_hist_total_buy_usd',
        'commodi_hist_type',
        'commodity_provider',
        'commodi_hist_type_change',
        'commodi_hist_bala_amou',
        'commodi_hist_bala_cost',
        'commodi_hist_date',
        'commodi_hist_prom_weig',
        'commodi_hist_status',
        'commodi_hist_user'
    ];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(CommodityHistoriesObserver::class);
    }
}
