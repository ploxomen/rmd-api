<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProductLabel extends Model
{
    protected $table = 'product_product_labels';

    protected $fillable = [
        'product_id',
        'product_label_id',
        'time_origin_minute',
        'time_origin_hours',
    ];

    protected $casts = [
        'time_origin_minute' => 'integer',
        'time_origin_hours' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
    public function productLabel(): BelongsTo
    {
        return $this->belongsTo(ProductLabel::class);
    }
}
