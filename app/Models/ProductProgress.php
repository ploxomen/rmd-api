<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductProgress extends Model
{
    protected $table = 'product_progress';
    protected $fillable = ['product_id','product_progress_stock','product_progress_status'];
    public function scopeProducts($query){
        return $query->join("products","products.id","=","product_id")->where('product_status','>',0);
    }
    public function scopeActive($query) {
        return $query->where('product_progress_status','>',0);
    }
    public static function getProductProgress($dateInitial,$dateFinal,$search) {
        $productProgress =ProductProgressHistory::select('product_progress_history.id','product_code','product_id','product_progress_history_stock AS product_progress_stock','product_unit_measurement','product_name','product_progress_history_description')
        ->selectRaw("DATE_FORMAT(product_progress_history_date,'%d/%m/%Y') AS product_progress_history_date")
        ->join('products','product_id','=','products.id')
        ->where(function($query)use($search){
            $query->where('product_name','like','%'.$search.'%')
            ->orWhere('product_progress_history_stock','like','%'.$search.'%')
            ->orWhere("product_progress_history_date", "LIKE", "%" . $search ."%")
            ->orWhere('product_code','like','%'.$search.'%');
        });
        if(!empty($dateInitial) && !empty($dateFinal)){
            $productProgress = $productProgress->whereBetween("product_progress_history_date",[$dateInitial,$dateFinal]);
        }
        return $productProgress->orderBy('id','desc');
    }
    public static function getProductsProgessAgroup($dateInitial,$dateFinal,$search){
        $productProgress = ProductProgress::select("product_id","product_code","product_progress_stock","product_name","product_progress.id","product_unit_measurement")
        ->selectRaw("DATE_FORMAT(product_progress.created_at,'%d/%m/%Y') AS product_progress_history_date")
        ->join('products','product_id','=','products.id')
        ->where('product_progress_status','=',1)->where(function($query)use($search){
            $query->where('product_name','like','%'.$search.'%')
            ->orWhere('product_progress_stock','like','%'.$search.'%')
            ->orWhereRaw("DATE_FORMAT(product_progress.created_at,'%Y-%m-%d') LIKE CONCAT('%',?,'%')",[$search])
            ->orWhere('product_code','like','%'.$search.'%');
        });
        if(!empty($dateInitial) && !empty($dateFinal)){
            $productProgress = $productProgress->whereRaw("DATE_FORMAT(product_progress.created_at,'%Y-%m-%d') BETWEEN ? AND ?",[$dateInitial,$dateFinal]);
        }
        return $productProgress;
    }
    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
    public function history()
    {
        return $this->hasMany(ProductProgressHistory::class,'product_progress_id');
    }
}
