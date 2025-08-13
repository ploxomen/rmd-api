<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = [
        'product_id',
        'commodi_stock',
        'commodi_money',
        'commodi_price_buy',
        'commodi_bala_cost',
        'commodi_prom_weig',
        'commodi_status'
    ];
    public function scopeProducts($query)
    {
        return $query->join('products','products.id','=','product_id')->where('product_status',1);
    }
    public function scopeEnabled($query)
    {
        return $query->where('commodi_status',1);
    }
}
