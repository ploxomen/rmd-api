<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationDetails extends Model
{
    protected $table = 'quotations_details';
    protected $fillable = [
        'quotation_id',
        'product_id',
        'detail_price_buy',
        'detail_quantity',
        'detail_price_unit',
        'detail_price_additional',
        'detail_total',
        'detail_status'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
