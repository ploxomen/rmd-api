<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderProduction extends Model
{
    protected $fillable = [
        'order_production_detail',
        'order_production_code',
        'order_production_number',
        'order_produc_date_issue',
        'order_produc_date_delive',
        'order_produc_address',
        'order_production_customer'
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->order_produc_date_issue)) {
                $model->order_produc_date_issue = now()->format('Y-m-d');
            }
            $date = Carbon::parse($model->order_produc_date_issue);
            $fullYear = $date->format('Y');
            $shortYear = $date->format('y');
            $latestOrder = static::whereYear('order_produc_date_issue', $fullYear)
                ->orderBy('order_production_number', 'desc')
                ->first();
            $nextNumber = $latestOrder ? $latestOrder->order_production_number + 1 : 1;
            $model->order_production_number = $nextNumber;
            $paddedNumber = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            $model->order_production_code = "OP-PV{$paddedNumber}{$shortYear}";
        });
    }
    public function details() : HasMany
    {
        return $this->hasMany(OrderProductionDetail::class, 'order_production_id');
    }
}
