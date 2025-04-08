<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductFinalyImported;
use App\Models\Quotation;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Exception;
use Illuminate\Support\Facades\Log;

class QuotationObserver
{
    public function updated(Quotation $quotation)
    {
        if ($quotation->wasChanged("order_id") && empty($quotation->order_id)) {
            foreach ($quotation->products as $product) {
                RawMaterialHistory::where('quotation_detail_id', $product->pivot->id)->get()->each(function ($query) {
                    $query->delete();
                });
                ProductFinalyAssembled::where('quotation_detail_id', $product->pivot->id)->get()->each(function ($query) {
                    $query->delete();
                });
                ProductFinalyImported::where('quotation_detail_id', $product->pivot->id)->get()->each(function ($query) {
                    $query->delete();
                });
            }
        } else if ($quotation->wasChanged("order_id") && !empty($quotation->order_id)) {
            foreach ($quotation->products as $product) {
                if ($product->product_store === "MATERIA PRIMA") {
                    $rawMaterial = RawMaterial::where(['product_id' => $product->id, 'raw_material_status' => 1])->first();
                    if (empty($rawMaterial)) {
                        throw new Exception("El producto {$product->product_name}, que corresponde a la cotización {$quotation->quotation_code} no se encuentra registrado en la tabla de MATERIA PRIMA");
                    }
                    if ($rawMaterial->raw_material_stock < $product->pivot->detail_quantity) {
                        throw new Exception("El producto {$product->product_name} del almacen MATERIA PRIMA, que corresponde a la cotización {$quotation->quotation_code} no tiene suficiente STOCK");
                    }
                    $rawMaterial->history()->create([
                        'product_id' => $product->id,
                        'material_hist_amount' => $product->pivot->detail_quantity * -1,
                        'quotation_detail_id' => $product->pivot->id,
                        'material_user' => auth()->user()->id,
                    ]);
                } else if ($product->product_store === "PRODUCTO TERMINADO") {
                    $productFinal = ProductFinaly::where(['product_id' => $product->id, 'product_finaly_status' => 1])->first();
                    if (empty($productFinal)) {
                        throw new Exception("El producto {$product->product_name}, que corresponde a la cotización {$quotation->quotation_code} no se encuentra registrado en la tabla de PRODUCTO TERMINADO");
                    }
                    if ($productFinal->product_finaly_stock < $product->pivot->detail_quantity) {
                        throw new Exception("El producto {$product->product_name} del almacen PRODUCTO TERMINADO, que corresponde a la cotización {$quotation->quotation_code} no tiene suficiente STOCK");
                    }
                    if ($product->product_label === "ENSAMBLADO") {
                        $productFinal->assembled()->create([
                            'product_finaly_created' => now()->toDateString(),
                            'product_finaly_amount' => $product->pivot->detail_quantity * -1,
                            'product_finaly_user' => auth()->user()->id,
                            'quotation_detail_id' => $product->pivot->id,
                        ]);
                    } else if ($product->product_label === "IMPORTADO") {
                        $productFinal->imported()->create([
                            'quotation_detail_id' => $product->pivot->id,
                            'product_finaly_created' => now()->toDateString(),
                            'product_finaly_amount' => $product->pivot->detail_quantity * -1,
                            'product_finaly_user' => auth()->user()->id,
                        ]);
                    }
                }
            }
        }
    }
}
