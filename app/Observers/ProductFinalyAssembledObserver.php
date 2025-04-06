<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;

class ProductFinalyAssembledObserver
{
    /**
     * Handle the RawMaterialHistory "created" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function created(ProductFinalyAssembled $productFinalyAssembled)
    {
        $productFinaly = ProductFinaly::find($productFinalyAssembled->product_finaly_id);
        $totalStock = $productFinaly->assembled()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }

    /**
     * Handle the RawMaterialHistory "updated" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function updated(ProductFinalyAssembled $productFinalyAssembled)
    {
        //
    }

    /**
     * Handle the RawMaterialHistory "deleted" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function deleted(ProductFinalyAssembled $productFinalyAssembled)
    {
        $productFinaly = ProductFinaly::find($productFinalyAssembled->product_finaly_id);
        $totalStock = $productFinaly->assembled()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }

    /**
     * Handle the RawMaterialHistory "restored" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function restored(ProductFinalyAssembled $productFinalyAssembled)
    {
        //
    }

    /**
     * Handle the RawMaterialHistory "force deleted" event.
     *
     * @param  \App\Models\RawMaterialHistory  $rawMaterialHistory
     * @return void
     */
    public function forceDeleted(ProductFinalyAssembled $productFinalyAssembled)
    {
        //
    }
}
