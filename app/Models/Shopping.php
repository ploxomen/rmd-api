<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shopping extends Model
{
    protected $fillable = [
        'buy_date',
        'buy_date_invoice',
        'buy_provider',
        'buy_number_invoice',
        'buy_number_guide',
        'buy_type',
        'buy_type_money',
        'buy_type_change',
        'buy_total'
    ];
    protected $table = 'shopping';
    public function scopeProviders($query)
    {
        return $query->leftJoin('provider', 'provider.id', '=', 'buy_provider');
    }
    public function products()
    {
        return $this->belongsToMany(Products::class, 'shopping_details', 'shopping_id', 'shopping_product')->using(ShoppingDetail::class)->withPivot(['shopping_deta_store', 'shopping_deta_ammount', 'shopping_deta_price', 'shopping_deta_subtotal'])->withTimestamps();
    }
    public function scopeList($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('provider_name', 'like', '%' . $search . '%')
                ->orWhere('buy_number_invoice', 'like', '%' . $search . '%')
                ->orWhere('buy_number_guide', 'like', '%' . $search . '%');
        });
    }
}
