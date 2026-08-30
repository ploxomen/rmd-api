<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\ProductLabel;
use App\Models\QuotationDetails;
use Illuminate\Http\Request;

class OrderProductionController extends Controller
{
    public function getShortages()
    {
        return response()->json([
            'data' => Orders::listOrdersProduction()
        ]);
    }
    public function getQuotationForOrderId($orderId)
    {
        $products = QuotationDetails::getQuotationDetail($orderId);
        foreach ($products as $product) {
            $product->list_labels = ProductLabel::query()->select(["product_labels.id", "time_origin_hours"])->leftJoin('product_product_labels','product_product_labels.product_label_id', '=', 'product_labels.id')->where('product_id', $product->product_id)->get();
        }
        return response()->json([
            'data' => $products
        ]);
    }
}
