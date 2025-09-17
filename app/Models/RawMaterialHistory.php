<?php

namespace App\Models;

use App\Observers\RawMaterialHistoryObserver;
use Illuminate\Database\Eloquent\Model;

class RawMaterialHistory extends Model
{
    protected $table = 'raw_materials_history';
    protected $fillable = ['raw_material_id', 'raw_provider', 'product_id', 'material_hist_bill', 'material_hist_guide', 'material_hist_amount', 'material_hist_price_buy', 'material_hist_igv', 'material_hist_money', 'material_hist_total_buy_pen', 'material_user', 'material_hist_total_buy_usd', 'material_hist_total_type_change', 'product_final_assem_id', 'guide_refer_id', 'product_progres_hist_id', 'material_hist_date', 'raw_hist_type', 'raw_hist_bala_amou', 'raw_hist_bala_cost', 'raw_hist_prom_weig', 'shopping_detail_id'];

    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(RawMaterialHistoryObserver::class);
    }
    public static function getHistory(int $idRawMaterial, $search)
    {
        return RawMaterialHistory::select('raw_materials_history.id', 'material_hist_date', 'material_hist_bill', 'material_hist_guide', 'material_hist_amount', 'material_hist_price_buy', 'material_hist_money', 'raw_hist_type', 'raw_hist_bala_amou', 'raw_hist_bala_cost', 'raw_hist_prom_weig', 'material_hist_total_buy_pen', 'material_hist_total_buy_usd', 'material_hist_igv', 'material_hist_total_type_change', 'product_final_assem_id', 'raw_materials_history.guide_refer_id', 'product_progres_hist_id',"product_finaly_assembleds.product_finaly_description")
            ->selectRaw('(material_hist_total_buy_pen - material_hist_price_buy) AS material_price_igv, CONCAT(user_name," ", user_last_name) AS user_name')
            ->leftJoin('users', 'material_user', '=', 'users.id')
            ->leftJoin('product_finaly_assem_deta', function ($join) {
                $join->on('product_finaly_assem_deta.id', '=', 'raw_materials_history.product_final_assem_id')
                    ->join('product_finaly_assembleds', function ($subJoin) {
                        $subJoin->on('product_finaly_assembleds.id', '=', 'product_finaly_assem_deta.product_assembled_id');
                    });
            })
            ->where(function ($query) use ($search) {
                $query->where('material_hist_bill', 'like', '%' . $search . '%')
                    ->orWhere('material_hist_guide', 'like', '%' . $search . '%')
                    ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')", [$search]);
            })
            ->where('raw_material_id', $idRawMaterial)->orderBy('id', 'desc');
    }
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
