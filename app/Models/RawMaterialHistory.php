<?php

namespace App\Models;

use App\Observers\RawMaterialHistoryObserver;
use Illuminate\Database\Eloquent\Model;

class RawMaterialHistory extends Model
{
    protected $table = 'raw_materials_history';
    protected $fillable = ['raw_material_id','raw_provider','product_id','material_hist_bill','material_hist_guide','material_hist_amount','material_hist_price_buy','material_hist_igv','material_hist_money','material_hist_total_buy_pen','material_user','material_hist_total_buy_usd','material_hist_total_type_change','material_hist_total_include_type_change','product_final_assem_id','quotation_detail_id','product_progres_hist_id'];
    protected $casts = [
        'material_hist_total_include_type_change' => 'boolean',
    ];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(RawMaterialHistoryObserver::class);
    }
    public static function getHistory(int $idRawMaterial, $search) {
        return RawMaterialHistory::select('raw_materials_history.id','material_hist_bill','material_hist_guide','material_hist_amount','material_hist_price_buy','material_hist_money','material_hist_total_buy_pen','material_hist_total_buy_usd','material_hist_igv','material_hist_total_type_change','material_hist_total_include_type_change','product_final_assem_id','quotation_detail_id','product_progres_hist_id')
        ->selectRaw('(material_hist_total_buy_pen - material_hist_price_buy) AS material_price_igv, CONCAT(user_name," ", user_last_name) AS user_name')
        ->leftJoin('users','material_user','=','users.id')
        ->where(function($query)use($search){
            $query->where('material_hist_bill','like','%'.$search.'%')
            ->orWhere('material_hist_guide','like','%'.$search.'%')
            ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')",[$search]);
        })
        ->where('raw_material_id',$idRawMaterial)->orderBy('id','desc');
    }
}
