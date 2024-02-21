<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $fillable = [
        'customer_id',
        'order_details',
        'user_id',
        'order_igv',
        'order_money',
        'order_status',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public static function getOrders(){
        return Orders::select("orders.id","order_igv","order_money","order_status","customer_name")
        ->join('customers','customers.id','=','customer_id');
    }
}
