<?php

namespace App\Models;

use App\Observers\RawMaterialHistoryObserver;
use Illuminate\Database\Eloquent\Model;

class RawMaterialHistory extends Model
{
    protected $table = 'raw_materials_history';
    protected $fillable = ['raw_material_id', 'raw_provider', 'product_id', 'material_hist_bill', 'material_hist_guide', 'material_hist_amount', 'material_hist_price_buy', 'material_hist_igv', 'material_hist_money', 'material_hist_total_buy_pen', 'material_user', 'material_hist_total_buy_usd', 'material_hist_total_type_change', 'product_final_assem_id', 'guide_refer_id', 'product_progres_hist_id', 'material_hist_date', 'raw_hist_type', 'raw_hist_bala_amou', 'raw_hist_bala_cost', 'raw_hist_prom_weig', 'shopping_detail_id', 'type_motion'];

    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(RawMaterialHistoryObserver::class);
    }
    public static function getHistory(int $idRawMaterial, $search)
    {
        return RawMaterialHistory::select('raw_materials_history.id', 'material_hist_date', 'material_hist_bill', 'material_hist_amount', 'material_hist_price_buy', 'material_hist_money', 'raw_hist_type', 'raw_hist_bala_amou', 'raw_hist_bala_cost', 'raw_hist_prom_weig', 'material_hist_total_buy_pen', 'material_hist_total_buy_usd', 'material_hist_igv', 'material_hist_total_type_change', 'raw_materials_history.product_final_assem_id', 'raw_materials_history.guide_refer_id')
            ->selectRaw('(material_hist_total_buy_pen - material_hist_price_buy) AS material_price_igv, CONCAT(user_name," ", user_last_name) AS user_name, COALESCE(product_finaly_assembleds.product_finaly_description,shopping.buy_details,product_progress_history_description) AS justification, COALESCE(shopping.buy_number_guide) AS material_hist_guide')
            ->leftJoin('users', 'material_user', '=', 'users.id')
            ->leftJoin('product_finaly_assem_deta', function ($join) {
                $join->on('product_finaly_assem_deta.id', '=', 'raw_materials_history.product_final_assem_id')
                    ->join('product_finaly_assembleds', function ($subJoin) {
                        $subJoin->on('product_finaly_assembleds.id', '=', 'product_finaly_assem_deta.product_assembled_id');
                    });
            })
            ->leftJoin('shopping_details', function ($join) {
                $join->on('shopping_details.id', '=', 'shopping_detail_id')
                    ->join('shopping', function ($subJoin) {
                        $subJoin->on('shopping.id', '=', 'shopping_details.shopping_id');
                    });
            })
            ->leftJoin('product_progress_history', 'product_progress_history.id', '=', 'raw_materials_history.product_progres_hist_id')
            ->where(function ($query) use ($search) {
                $query->where('material_hist_bill', 'like', '%' . $search . '%')
                    ->orWhere('material_hist_guide', 'like', '%' . $search . '%')
                    ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')", [$search]);
            })
            ->where('raw_material_id', $idRawMaterial)->orderBy('id', 'desc');
    }
    public static function reportEntry(string $dateInitial, string $dateFinaly)
    {
        return RawMaterialHistory::select('product_name', 'product_code', 'material_hist_amount AS stock', 'material_hist_money AS type_money', 'material_hist_total_type_change AS type_change_money', 'material_hist_price_buy AS price_unit_pen')
            ->selectRaw('material_hist_date AS date, "MATERIA PRIMA" AS store, raw_materials_history.type_motion AS type_mov, provider_number_document AS number_doc_provider, provider_name as provider, material_hist_guide AS number_guide, material_hist_total_buy_pen AS cost_total_pen, IF(imported_coefficient IS NOT NULL, material_hist_price_buy * imported_coefficient, material_hist_price_buy) AS valorization_unit,IF(imported_coefficient IS NOT NULL, material_hist_price_buy * material_hist_amount * imported_coefficient, material_hist_total_buy_pen) AS valorization_total')
            ->leftJoin('products', 'products.id', '=', 'product_id')
            ->leftJoin('provider', 'raw_provider', '=', 'provider.id')
            ->leftJoin('shopping_details', function ($join) {
                $join->on('shopping_details.id', '=', 'shopping_detail_id')
                    ->leftJoin('shopping_imported', function ($subJoin) {
                        $subJoin->on('shopping_imported.shopping_id', '=', 'shopping_details.shopping_id');
                    });
            })
            ->whereBetween('material_hist_date', [$dateInitial, $dateFinaly])->where('raw_hist_type', 'ENTRADA');
    }
    public static function reportExit(string $dateInitial, string $dateFinaly)
    {
        return RawMaterialHistory::select('product_name', 'product_code', 'material_hist_amount AS stock', 'material_hist_money AS type_money', 'material_hist_total_type_change AS type_change_money', 'material_hist_price_buy AS price_unit_pen')
            ->selectRaw('material_hist_date AS date, "MATERIA PRIMA" AS store,COALESCE(guide_type_motion,raw_materials_history.type_motion) AS type_mov, COALESCE(customer_number_document, "-") AS number_doc_provider, COALESCE(customer_name, "RMD") as provider, IF(raw_materials_history.product_final_assem_id IS NOT NULL OR product_progres_hist_id IS NOT NULL, COALESCE(product_progres_hist_id,raw_materials_history.product_final_assem_id), guide_issue_number) AS number_guide, material_hist_total_buy_pen AS cost_total_pen, material_hist_price_buy AS valorization_unit, material_hist_total_buy_pen AS valorization_total')
            ->leftJoin('products', 'products.id', '=', 'product_id')
            ->leftJoin('product_progress_history', 'product_progress_history.id', '=', 'product_progres_hist_id')
            ->leftJoin('product_finaly_assem_deta', 'product_finaly_assem_deta.id', '=', 'raw_materials_history.product_final_assem_id')
            ->leftJoin('guides_referral_details', function ($join) {
                $join->on('guides_referral_details.id', '=', 'guide_refer_id')
                    ->leftJoin('guides_referral', function ($subJoin) {
                        $subJoin->on('guides_referral.id', '=', 'guides_referral_details.guide_referral_id')
                            ->leftJoin('customers', 'customers.id', '=', 'guides_referral.guide_customer_id');;
                    });
            })
            ->whereBetween('material_hist_date', [$dateInitial, $dateFinaly])->where('raw_hist_type', 'SALIDA');
    }
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
