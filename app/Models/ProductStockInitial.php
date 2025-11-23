<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockInitial extends Model
{
    use HasFactory;
    protected $table = 'product_stock_initial';
    protected $fillable = ['product_id','stock_initial','type_money','type_change_money','price_unit_pen','price_unit_usd'];
    public static function reportEntry(string $dateInitial, string $dateFinaly)
    {
        return ProductStockInitial::select('product_name','product_code','stock_initial AS stock', 'type_money', 'type_change_money','price_unit_pen')
        ->selectRaw('DATE(product_stock_initial.updated_at) AS date, "ALMACEN" AS store, "INVENTARIO INICIAL" AS type_mov, "-" AS number_doc_provider, "RMD" as provider, "-" AS number_guide, (stock_initial * price_unit_pen) AS cost_total_pen, (stock_initial * price_unit_pen) AS valorization')
        ->leftJoin('products','products.id','=','product_id')
        // ->whereRaw('DATE(product_stock_initial.updated_at) BETWEEN ? AND ?',[$dateInitial,$dateFinaly])->get();
        ;
    }

}
