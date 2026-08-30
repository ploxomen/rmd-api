<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductLabel extends Model
{
    protected $fillable = [
        'nombre',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Products::class,
            'product_product_labels'
        )->withPivot([
            'time_origin_minute',
            'time_origin_hours',
        ])->withTimestamps();
    }
}
