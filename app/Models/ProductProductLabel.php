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
        'group_work_time_hours',
        'work_time_hours',
        'group_work_number',
    ];

    protected $casts = [
        'work_time_hours' => 'decimal:3',
        'group_work_number' => 'integer',
        'group_work_time_hours' => 'decimal:3',
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
