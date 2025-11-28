<?php

namespace App\Models;

use App\Observers\ProductFinalyObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinaly extends Model
{
    use HasFactory;
    protected $table = "product_finalies";
    protected $fillable = ['product_id','product_finaly_stock','product_finaly_status'];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(ProductFinalyObserver::class);
    }
    public function scopeProductsActive($query,$search){
        return $query->joinProducts()->productExist()->where(function($subquery)use($search){
            $subquery->where('product_name','like','%'.$search.'%')
            ->orWhere('product_finaly_stock','like','%'.$search.'%')
            ->orWhere('product_code','like','%'.$search.'%');
        });
    }
    public function scopeJoinProducts($query){
        return $query->join("products","products.id","=","product_id");
    }
    public function imported(){
        return $this->hasMany(ProductFinalyImported::class,"product_finaly_id");
    }
    public function assembled() {
        return $this->hasMany(ProductFinalyAssembled::class,"product_finaly_id");
    }
    public function products(){
        return $this->belongsTo(Products::class,'product_id');
    }
    public function scopeProductExist($query){
        return $query->where("product_finaly_status",'>',0)->where('product_status','>',0);
    } 
}
