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
        'detail_type_price',
        'detail_total',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public static function getQuotationDetailOld(int $orderId)
    {
        return Quotation::query()->select(["quotations_details.id as quota_deta_id", "product_label_id", "product_label_hr", "amount", "product_label_total"])
            ->leftJoin('quotations_details', 'quotations_details.quotation_id', '=', 'quotations.id')
            ->leftJoin('order_productions_details', 'order_productions_details.quotation_detail_id', '=', 'quotations_details.id')
            ->where('order_id', $orderId)
            ->get();
    }
    public static function getQuotationDetail(int $orderId)
    {
        return Quotation::query()->select(["orders.id as order_id","quotations_details.id as quota_deta_id", "product_name", "order_code", "products.id as product_id"])->selectRaw(" quotations_details.detail_quantity as 'amount'")
            ->leftJoin('orders', 'orders.id', '=', 'quotations.order_id')
            ->leftJoin('quotations_details', 'quotations_details.quotation_id', '=', 'quotations.id')
            ->leftJoin('products', 'products.id', '=', 'quotations_details.product_id')
            ->where('order_id', $orderId)
            ->get();
    }
}
