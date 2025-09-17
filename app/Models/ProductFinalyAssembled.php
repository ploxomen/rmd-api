<?php

namespace App\Models;

use App\Observers\ProductFinalyAssembledObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinalyAssembled extends Model
{
    protected $table = "product_finaly_assembleds";
    protected $fillable = ['product_finaly_amount','product_finaly_id','guide_refer_id','product_finaly_created','prod_fina_type_change','product_finaly_description','product_finaly_user','product_finaly_total'];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(ProductFinalyAssembledObserver::class);
    }
    public function product(){
        return $this->belongsToMany(Products::class,'product_finaly_assem_deta','product_assembled_id','product_id')->using(ProductFinalAssemDeta::class)->withPivot('product_finaly_stock','product_finaly_subtotal','product_finaly_price_unit','product_finaly_type','id')->withTimestamps();
    }
    public function scopeGetActive($query,$productFinalyId) {
        return $query->select("product_finaly_assembleds.id","guide_refer_id","product_unit_measurement","product_finaly_amount","product_finaly_description","product_finaly_total")
        ->selectRaw("DATE_FORMAT(product_finaly_created, '%d/%m/%Y') as product_finaly_created, CONCAT(user_name,' ',user_last_name) as user_name")
        ->join("product_finalies","product_finalies.id","=",'product_finaly_id')
        ->join("products","products.id","=",'product_id')
        ->leftJoin('users','users.id','=','product_finaly_user')
        ->where("product_finaly_id",$productFinalyId);
     }
     public function scopeSearchHistory($query,$search) {
        return $query->where(function($query)use($search){
           $query->where('product_finaly_amount','LIKE','%'.$search.'%')
           ->orWhere("product_finaly_description",'LIKE','%'.$search.'%')
           ->orWhere("product_unit_measurement",'LIKE','%'.$search.'%')
           ->orWhereRaw(" CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')",[$search]);
       });
     }
     public function productFinaly(){
        return $this->belongsTo(ProductFinaly::class,'product_finaly_id');
     }
}
