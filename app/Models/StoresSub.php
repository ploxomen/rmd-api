<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoresSub extends Model
{
    protected $table = 'stores_sub';
    protected $fillable = ['store_id','store_sub_name','store_sub_status'];

    public function products(): HasMany
    {
        return $this->hasMany(Products::class,'product_substore');
    }
}
