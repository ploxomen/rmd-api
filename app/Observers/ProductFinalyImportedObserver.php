<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyImported;

class ProductFinalyImportedObserver
{
    /**
     * Handle the ProductFinalyImported "created" event.
     *
     * @param  \App\Models\ProductFinalyImported  $productFinalyImported
     * @return void
     */
    public function created(ProductFinalyImported $productFinalyImported)
    {
        $productFinaly = ProductFinaly::find($productFinalyImported->product_finaly_id);
        $totalStock = $productFinaly->imported()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }

    public function updated(ProductFinalyImported $productFinalyImported)
    {
        //
    }

    /**
     * Handle the ProductFinalyImported "deleted" event.
     *
     * @param  \App\Models\ProductFinalyImported  $productFinalyImported
     * @return void
     */
    public function deleted(ProductFinalyImported $productFinalyImported)
    {
        $productFinaly = ProductFinaly::find($productFinalyImported->product_finaly_id);
        $totalStock = $productFinaly->imported()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }
}
