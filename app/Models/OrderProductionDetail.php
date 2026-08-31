<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductionDetail extends Model
{
    protected $table = "order_productions_details";
    protected $fillable = [
        'order_production_id',
        'quotation_detail_id',
        'product_label_id',
        'amount',
        'product_label_hr',
        'product_label_total'
    ];
}
