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
        'shopping_detail_id',
        'type_motion'
    ];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(CommodityHistoriesObserver::class);
    }
    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodi_id');
    }
    public static function reportEntry(string $dateInitial, string $dateFinaly)
    {
        return CommodityHistory::select('product_name', 'product_code', 'commodi_hist_amount AS stock', 'commodi_hist_money AS type_money', 'commodi_hist_type_change AS type_change_money', 'commodi_hist_price_buy AS price_unit_pen')
            ->selectRaw('commodi_hist_date AS date, "ALMACEN MERCADERIA" AS store, "COMPRA" AS type_mov, provider_number_document AS number_doc_provider, provider_name as provider, commodi_hist_guide AS number_guide, commodi_hist_total_buy AS cost_total_pen, IF(imported_coefficient IS NOT NULL, commodi_hist_price_buy * imported_coefficient, commodi_hist_price_buy) AS valorization_unit,IF(imported_coefficient IS NOT NULL, commodi_hist_price_buy * commodi_hist_amount * imported_coefficient, commodi_hist_total_buy) AS valorization_total')
            ->leftJoin('products', 'products.id', '=', 'product_id')
            ->leftJoin('provider', 'commodity_provider', '=', 'provider.id')
            ->leftJoin('shopping_details', function ($join) {
                $join->on('shopping_details.id', '=', 'shopping_detail_id')
                    ->leftJoin('shopping_imported', function ($subJoin) {
                        $subJoin->on('shopping_imported.shopping_id', '=', 'shopping_details.shopping_id');
                    });
            })
            ->whereBetween('commodi_hist_date', [$dateInitial, $dateFinaly])->where('commodi_hist_type', 'ENTRADA');;
    }
    public static function reportExit(string $dateInitial, string $dateFinaly)
    {
        return CommodityHistory::select('product_name', 'product_code', 'commodi_hist_amount AS stock', 'commodi_hist_money AS type_money', 'commodi_hist_type_change AS type_change_money', 'commodi_hist_price_buy AS price_unit_pen')
            ->selectRaw('commodi_hist_date AS date, "ALMACEN MERCADERIA" AS store, guide_type_motion AS type_mov, COALESCE(customer_number_document, "-") AS number_doc_provider, COALESCE(customer_name, "RMD") as provider, guide_issue_number AS number_guide, commodi_hist_total_buy AS cost_total_pen, commodi_hist_price_buy AS valorization_unit,commodi_hist_total_buy AS valorization_total')
            ->leftJoin('products', 'products.id', '=', 'product_id')
            ->leftJoin('guides_referral_details', function ($join) {
                $join->on('guides_referral_details.id', '=', 'guide_refer_id')
                    ->leftJoin('guides_referral', function ($subJoin) {
                        $subJoin->on('guides_referral.id', '=', 'guides_referral_details.guide_referral_id')
                            ->leftJoin('customers', 'customers.id', '=', 'guides_referral.guide_customer_id');;
                    });
            })
            ->whereBetween('commodi_hist_date', [$dateInitial, $dateFinaly])->where('commodi_hist_type', 'SALIDA');
    }

    public static function getHistory(int $commodity, string $search)
    {
        return CommodityHistory::select('commodity_histories.id', 'commodi_hist_date', 'commodi_hist_bill', 'commodi_hist_amount', 'commodi_hist_price_buy', 'commodi_hist_money', 'commodi_hist_type', 'commodi_hist_bala_amou', 'commodi_hist_bala_cost', 'commodi_hist_prom_weig', 'commodi_hist_total_buy', 'commodi_hist_total_buy_usd', 'commodi_hist_type_change', 'guide_refer_id')
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
