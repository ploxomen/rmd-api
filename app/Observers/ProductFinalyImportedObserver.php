<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyImported;

class ProductFinalyImportedObserver
{
    public function created(ProductFinalyImported $productFinalyImported)
    {
        $productFinaly = ProductFinaly::find($productFinalyImported->product_finaly_id);
        $totalStock = $productFinaly->imported()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }
    public function deleted(ProductFinalyImported $productFinalyImported)
    {
        $productFinaly = ProductFinaly::find($productFinalyImported->product_finaly_id);
        $totalStock = $productFinaly->imported()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }
}
