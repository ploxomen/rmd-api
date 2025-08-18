<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    protected $fillable = [
        'product_id',
        'commodi_stock',
        'commodi_money',
        'commodi_price_buy',
        'commodi_bala_amou',
        'commodi_bala_cost',
        'commodi_prom_weig',
        'commodi_status'
    ];
    public function scopeProducts($query)
    {
        return $query->join('products','products.id','=','product_id')->where('product_status',1);
    }
    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
    public static function calculationAvgHistory(int $idCommodity, float $amountHistory, float $costHistory, CommodityHistory $history, string $type = 'suma')
    {
        $commodity = Commodity::find($idCommodity);
        $amount = $type == 'suma' ? $amountHistory + $commodity->commodi_bala_amou : $commodity->commodi_bala_amou - $amountHistory;
        $cost = $type == 'suma' ? $costHistory + $commodity->commodi_bala_cost : $commodity->commodi_bala_cost - $costHistory;
        $average = $cost <= 0 ? 0 : round($amount / $cost,2);
        $commodity->update([
            'commodi_bala_amou' => $amount,
            'commodi_bala_cost' => $cost,
            'commodi_prom_weig' => $average
        ]);
        $history->updateQuietly([
            'commodi_hist_bala_amou' => $amount,
            'commodi_hist_bala_cost' => $cost,
            'commodi_hist_prom_weig' => $average,
        ]);
    }
    public function history(): HasMany
    {
        return $this->hasMany(CommodityHistory::class,'commodi_id');
    }
    public function scopeEnabled($query)
    {
        return $query->where('commodi_status',1);
    }
}
