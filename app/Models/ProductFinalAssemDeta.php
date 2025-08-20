<?php

namespace App\Models;

use App\Observers\ProductFinAssemObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductFinalAssemDeta extends Pivot
{
    public $incrementing = true; // Indica que tiene una clave primaria auto-incremental
    protected $primaryKey = "id";
    protected $table = "product_finaly_assem_deta";
    protected $fillable = [
        'product_finaly_stock','product_id','product_finaly_type', 'product_finaly_price_unit', 'product_finaly_subtotal'
    ];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(ProductFinAssemObserver::class);
    }
    public function productFinalyAssem(){
        return $this->belongsTo(ProductFinalyAssembled::class,'product_assembled_id');
    }
}
