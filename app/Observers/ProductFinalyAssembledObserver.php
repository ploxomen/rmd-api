<?php

namespace App\Observers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;

class ProductFinalyAssembledObserver
{
    public function countTotalStockProductFinaly(int $productFinalyId)
    {
        $productFinaly = ProductFinaly::find($productFinalyId);
        $totalStock = $productFinaly->assembled()->sum('product_finaly_amount');
        $productFinaly->product_finaly_stock = $totalStock;
        $productFinaly->save();
    }
    public function created(ProductFinalyAssembled $productFinalyAssembled)
    {
        $this->countTotalStockProductFinaly($productFinalyAssembled->product_finaly_id);
    }
    public function updated(ProductFinalyAssembled $productFinalyAssembled)
    {
        if ($productFinalyAssembled->wasChanged('product_finaly_amount')) {
            $this->countTotalStockProductFinaly($productFinalyAssembled->product_finaly_id);
        }
    }
    public function deleted(ProductFinalyAssembled $productFinalyAssembled)
    {
        $this->countTotalStockProductFinaly($productFinalyAssembled->product_finaly_id);
    }
}
