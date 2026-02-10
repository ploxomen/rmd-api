<?php

namespace App\Models;

use App\Observers\ProductProgresDetaObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProgressHistory extends Model
{
    protected $table = 'product_progress_history';
    protected $fillable = ['product_id','product_progress_id','product_progress_history_date','product_progress_history_stock','product_progress_history_description','product_final_assem_id','product_progress_history_total','product_progress_history_pu','prod_prog_hist_type','prod_prog_hist_bala_amou','prod_prog_hist_bala_cost','prod_prog_hist_prom_weig','prod_prog_type_change','type_motion','created_at'];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(ProductProgresDetaObserver::class);
    }
    public static function reportEntry(string $dateInitial,string $dateFinaly)
    {
        return ProductProgressHistory::select('product_name','product_code','product_progress_history_stock AS stock')
        ->selectRaw('"PEN" AS type_money, prod_prog_type_change AS type_change_money')
        ->addSelect('product_progress_history_pu AS price_unit_pen')
        ->selectRaw('product_progress_history_date AS date, "PRODUCTO EN CURSO" AS store, COALESCE(product_progress_history.type_motion, "ENSAMBLE") AS type_mov, "-" AS number_doc_provider, "RMD" as provider, "-" AS number_guide, product_progress_history_total AS cost_total_pen, product_progress_history_pu AS valorization_unit, product_progress_history_total AS valorization_total')
        ->leftJoin('products','products.id','=','product_id')
        ->whereBetween('product_progress_history_date',[$dateInitial,$dateFinaly])->where('prod_prog_hist_type','ENTRADA');
    }
    public static function reportExit(string $dateInitial,string $dateFinaly)
    {
        return ProductProgressHistory::select('product_name','product_code','product_progress_history_stock AS stock')
        ->selectRaw('"PEN" AS type_money, prod_prog_type_change AS type_change_money')
        ->addSelect('product_progress_history_pu AS price_unit_pen')
        ->selectRaw('product_progress_history_date AS date, "PRODUCTO EN CURSO" AS store, COALESCE(product_progress_history.type_motion, "ENSAMBLE") AS type_mov, "-" AS number_doc_provider, "RMD" as provider, "-" AS number_guide, product_progress_history_total AS cost_total_pen, product_progress_history_pu AS valorization_unit, product_progress_history_total AS valorization_total')
        ->leftJoin('products','products.id','=','product_id')
        ->whereBetween('product_progress_history_date',[$dateInitial,$dateFinaly])->where('prod_prog_hist_type','SALIDA');
    }
    public static function getHistory(int $idProductProgress, $search) {
        return ProductProgressHistory::select('product_progress_history.id',"product_final_assem_id",'product_progress_id','product_id','product_progress_history_stock','product_name','product_progress_history_description')
        ->selectRaw("DATE_FORMAT(product_progress_history_date,'%d/%m/%Y') AS product_progress_history_date")
        ->leftJoin('products','products.id','=','product_id')
        ->where(function($query)use($search){
            $query->where('product_name','like','%'.$search.'%')
            ->orWhere('product_progress_history_description','like','%'.$search.'%')
            ->orWhereRaw("DATE_FORMAT(product_progress_history_date,'%d/%m/%Y') LIKE CONCAT('%',?,'%')",[$search])
            ->orWhere('product_progress_history_stock','like','%'.$search.'%');
        })
        ->where('product_progress_id',$idProductProgress)->orderBy('product_progress_history_date','desc');
    }
    public function productProgress()
    {
        return $this->belongsTo(ProductProgress::class,'product_progress_id');
    }
    
}
