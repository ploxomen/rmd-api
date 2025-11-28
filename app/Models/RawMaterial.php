<?php

namespace App\Models;

use App\Observers\RawMaterialObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $fillable = ['product_id', 'raw_material_stock', 'raw_material_price_buy', 'raw_material_status', 'raw_material_money', 'raw_hist_bala_amou', 'raw_hist_bala_cost', 'raw_hist_prom_weig'];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(RawMaterialObserver::class);
    }
    public function history(): HasMany
    {
        return $this->hasMany(RawMaterialHistory::class, 'raw_material_id');
    }
    public static function getRawMasterials($subStore, $search)
    {
        $rawMaterial = RawMaterial::select('product_id', 'product_code', 'product_name', 'raw_materials.id', 'raw_material_stock', 'raw_material_price_buy', 'product_label', 'product_store', 'raw_material_money', 'product_unit_measurement')
            ->join('products', 'product_id', '=', 'products.id')
            ->where('raw_material_status', 1)
            ->where(function ($query) use ($search) {
                $query->where('raw_material_stock', 'like', '%' . $search . '%')
                    ->orWhere('raw_material_price_buy', 'like', '%' . $search . '%')
                    ->orWhere('raw_material_price_buy', 'like', '%' . $search . '%')
                    ->orWhere('product_name', 'like', '%' . $search . '%');
            })->groupBy('product_id');
        if (!is_null($subStore)) {
            $rawMaterial = $rawMaterial->where('product_label', $subStore);
        }
        return $rawMaterial;
    }
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
    public function scopeProducts($query)
    {
        return $query->join("products", "products.id", "=", "product_id")->where('product_status', '>', 0);
    }
    public function scopeActive($query)
    {
        return $query->where('raw_material_status', '>', 0);
    }
}
