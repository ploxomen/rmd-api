<?php

namespace App\Http\Controllers;

use App\Models\OrderProduction;
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
    public function store(Request $request)
    {
        $orderProduction = OrderProduction::create([
            'order_production_detail' => $request->observations,
            'order_produc_date_issue' => $request->date_issue,
            'order_produc_date_delive' => $request->date_delivery,
            'order_produc_address' => $request->address,
            'order_production_customer' => $request->cod_client,
        ]);
        $details = json_decode($request->input('details', '[]'), true);
        foreach ($details as $detail) {
            $detailFillable = [
                'quotation_detail_id' => $detail['quota_deta_id'],
                'product_label_id' => null,
                'amount' => $detail['amount'],
                'product_label_hr' => 0,
                'product_label_total' => 0
            ];
            if (empty($detail['list_labels'])) {
                $orderProduction->details()->create($detailFillable);
                continue;
            }
            foreach ($detail['list_labels'] as $label) {
                $detailFillable['product_label_id'] = $label['id'];
                $detailFillable['product_label_hr'] = $label['time_origin_hours'];
                $detailFillable['product_label_total'] = round($detail['amount'] * $label['time_origin_hours'], 2);
                $orderProduction->details()->create($detailFillable);
            }
        }
        return response()->json([
            'message' => 'Orden de producción generada correctamente',
            'success' => true,
            'redirect' => null
        ]);
    }
    public function getQuotationForOrderId($orderId)
    {
        $products = QuotationDetails::getQuotationDetail($orderId);
        $details = Orders::query()->select(['order_details', 'order_code', 'id as order_id'])->where('id', $orderId)->first();
        foreach ($products as $product) {
            $product->list_labels = ProductLabel::query()->select(["product_labels.id", "time_origin_hours"])->leftJoin('product_product_labels', 'product_product_labels.product_label_id', '=', 'product_labels.id')->where('product_id', $product->product_id)->get();
        }
        return response()->json([
            'data' => $products,
            'details' => $details
        ]);
    }
}
