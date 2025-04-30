<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;

class ProductFinalyAssembledObserver
{
    public function created(ProductFinalyAssembled $productFinalyAssembled)
    {
        $productFinaly = ProductFinaly::find($productFinalyAssembled->product_finaly_id);
        $totalStock = $productFinaly->assembled()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }
    public function deleted(ProductFinalyAssembled $productFinalyAssembled)
    {
        $productFinaly = ProductFinaly::find($productFinalyAssembled->product_finaly_id);
        $totalStock = $productFinaly->assembled()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }
    
}
