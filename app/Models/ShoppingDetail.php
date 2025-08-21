<?php

namespace App\Models;

use App\Observers\ShoppinDetailObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ShoppingDetail extends Pivot
{
    protected $table = 'shopping_details';
    protected $fillable = ['shopping_deta_store', 'shopping_id', 'shopping_product', 'shopping_deta_ammount', 'shopping_deta_price', 'shopping_deta_price_usd', 'shopping_deta_subtotal', 'shopping_deta_subtotal_usd'];
    protected static function boot()
    {
        parent::boot();
        static::observe(ShoppinDetailObserver::class);
    }
}
