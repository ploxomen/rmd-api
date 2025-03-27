<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinalyAssembled extends Model
{
    protected $table = "product_finaly_assembleds";
    public function product(){
        return $this->belongsToMany(Products::class,'product_finaly_assem_deta','product_assembled_id','product_id')->withPivot('product_finaly_stock','product_finaly_type')->withTimestamps();
    }

}
