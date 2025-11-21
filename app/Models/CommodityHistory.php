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
        'commodi_hist_amount',
        'commodi_hist_price_buy',
        'commodi_hist_money',
        'commodi_hist_total_buy',
        'guide_refer_id',
        'commodi_hist_total_buy_usd',
        'commodi_hist_type',
        'commodity_provider',
        'commodi_hist_type_change',
        'commodi_hist_bala_amou',
        'commodi_hist_bala_cost',
        'commodi_hist_date',
        'commodi_hist_prom_weig',
        'commodi_hist_status',
        'commodi_hist_user',
        'shopping_detail_id'
    ];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(CommodityHistoriesObserver::class);
    }
    public function commodity()
    {
        return $this->belongsTo(Commodity::class,'commodi_id');
    }
    public static function getHistory(int $commodity, string $search)
    {
        return CommodityHistory::select('commodity_histories.id','commodi_hist_date', 'commodi_hist_bill', 'commodi_hist_amount', 'commodi_hist_price_buy', 'commodi_hist_money','commodi_hist_type','commodi_hist_bala_amou','commodi_hist_bala_cost','commodi_hist_prom_weig', 'commodi_hist_total_buy', 'commodi_hist_total_buy_usd', 'commodi_hist_type_change', 'guide_refer_id')
            ->selectRaw('CONCAT(user_name," ", user_last_name) AS user_name, COALESCE(shopping.buy_details) AS justification, COALESCE(shopping.buy_number_guide) AS commodi_hist_guide')
            ->leftJoin('users', 'commodi_hist_user', '=', 'users.id')
            ->leftJoin('shopping_details', function ($join) {
                $join->on('shopping_details.id', '=', 'shopping_detail_id')
                    ->join('shopping', function ($subJoin) {
                        $subJoin->on('shopping.id', '=', 'shopping_details.shopping_id');
                    });
            })
            ->where(function ($query) use ($search) {
                $query->where('commodi_hist_bill', 'like', '%' . $search . '%')
                    ->orWhere('commodi_hist_guide', 'like', '%' . $search . '%')
                    ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')", [$search]);
            })
            ->where('commodi_id', $commodity)->orderBy('id', 'desc');
    }
}
