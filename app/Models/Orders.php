<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $fillable = [
        'customer_id',
        'order_date_issue',
        'order_details',
        'user_id',
        'order_igv',
        'order_money',
        'order_mount',
        'order_mount_igv',
        'order_total',
        'order_status',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class,'order_id');
    }
    public static function getOrder($id) {
        return  Orders::select("orders.id","order_igv","order_money","order_status","customer_name AS order_customer","order_details")
        ->selectRaw('DATE_FORMAT(order_date_issue,"%d/%m/%Y") AS order_date_issue')
        ->join('customers','customers.id','=','customer_id')
        ->where(['orders.id' => $id])->first();
    }
    public static function getOrders($search,$filters){
        $query = Orders::select("orders.id","order_igv","order_money","order_status","order_mount","order_mount_igv","order_total","customer_name")
        ->selectRaw('DATE_FORMAT(order_date_issue,"%d/%m/%Y") AS date_issue')
        ->join('customers','customers.id','=','customer_id')
        ->where(function($query)use($search){
            $query->where('customer_name','LIKE','%'.$search.'%')
            ->orWhereRaw("LPAD(orders.id,5,'0') LIKE CONCAT('%',?,'%')",[$search]);
        });
        foreach ($filters as $filter) {
            $query = $query->where($filter['column'],$filter['sign'],$filter['value']);
        }
        return $query;
    }
}
