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
        'order_produc_date_issue',
        'order_produc_date_delive',
        'order_produc_address',
        'order_production_customer',
        'order_produc_total'
    ];
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'order_production_customer');
    }
    public function details(): HasMany
    {
        return $this->hasMany(OrderProductionDetail::class, 'order_production_id');
    }
    public function numberOrders()
    {
        $orders = $this->details()
            ->join('orders', 'orders.id', '=', 'order_productions_details.order_id')
            ->select(
                'orders.order_code',
                'orders.order_contact_telephone',
                'orders.order_contact_name',
                'orders.order_project'
            )
            ->groupBy(
                'orders.id',
            )
            ->get();
        return [
            'order_code' => $orders->pluck('order_code')->filter()->unique()->implode(' - '),
            'order_contact_telephone' => $orders->pluck('order_contact_telephone')->filter()->unique()->implode(' - '),
            'order_contact_name' => $orders->pluck('order_contact_name')->filter()->unique()->implode(' - '),
            'order_project' => $orders->pluck('order_project')->filter()->unique()->implode(' - '),
        ];
    }
}
