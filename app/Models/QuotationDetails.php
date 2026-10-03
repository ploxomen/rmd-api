<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    public static function getQuotationDetailOld(int $orderProdDetail)
    {
        return OrderProductionDetail::query()->select([
            "order_productions_details.id as id_detail",
            "order_productions_details.order_id", 
            "quotation_detail_id as quota_deta_id",
            "product_name",
            "order_code",
            "products.id as product_id", 
            "order_productions_details.amount"
        ])
            ->leftJoin('orders', 'orders.id', '=', 'order_productions_details.order_id')
            ->leftJoin('quotations_details', 'quotations_details.id', '=', 'quotation_detail_id')
            ->leftJoin('products', 'products.id', '=', 'quotations_details.product_id')
            ->where('order_production_id', $orderProdDetail)
            ->groupBy('order_id', 'quotation_detail_id')
            ->get();
    }
    public static function productNotLabel(int $orderId)
    {
        return Quotation::query()->select(["product_name", "products.id as product_id"])
        ->join('quotations_details', 'quotations_details.quotation_id', '=', 'quotations.id')
        ->leftJoin('product_product_labels', 'product_product_labels.product_id', '=', 'quotations_details.product_id')
        ->leftJoin('products', 'products.id', '=', 'quotations_details.product_id')
        ->where('order_id', $orderId)
        ->whereNull('product_product_labels.id')
        ->groupBy('products.id')
        ->get();
    }
    public static function getQuotationDetail(int $orderId)
    {
        return Quotation::query()->select(["orders.id as order_id", "quotations_details.id as quota_deta_id", "product_img", "product_name", "order_code", "products.id as product_id"])->selectRaw(" quotations_details.detail_quantity as 'amount'")
            ->leftJoin('orders', 'orders.id', '=', 'quotations.order_id')
            ->leftJoin('quotations_details', 'quotations_details.quotation_id', '=', 'quotations.id')
            ->leftJoin('products', 'products.id', '=', 'quotations_details.product_id')
            ->where('order_id', $orderId)
            ->get();
    }
}
