<?php

namespace App\Helpers;

class ProductHelper
{
    public static function unionOfProducts(array $products)
    {
        $newProducts = [];
        foreach ($products as $product) {
            $productExist = array_filter($newProducts, function ($value) use ($product) {
                return $value['product_id'] === $product['detail_product_id'] && $value['type'] === $product['detail_store'];
            });
            if (count($productExist) === 0) {
                $newProducts[] = array_merge($product, [
                    'type' => $product['detail_store'],
                    'product_id' => $product['detail_product_id'],
                    'stock' => $product['detail_stock']
                ]);
                continue;
            }
            $newProducts[key($productExist)]['stock'] += $product['detail_stock'];
        }
        return $newProducts;
    }
}
