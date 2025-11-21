<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockInitial extends Model
{
    use HasFactory;
    protected $table = 'product_stock_initial';
    protected $fillable = ['product_id','stock_initial','type_money','type_change_money'];

}
