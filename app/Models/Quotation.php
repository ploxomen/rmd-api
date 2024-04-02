<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'quotations';
    protected $fillable = [
        'order_id',
        'quotation_number',
        'quotation_code',
        'quotation_customer',
        'quotation_project',
        'quotation_customer_contact',
        'quotation_date_issue',
        'quotation_include_igv',
        'quotation_type_money',
        'quotation_change_money',
        'quotation_customer_address',
        'quotation_amount',
        'quotation_discount',
        'quotation_igv',
        'quotation_total',
        'quotation_quoter',
        'quotation_observations',
        'quotation_conditions',
        'quotation_status'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function products()
    {
        return $this->belongsToMany(Products::class,'quotations_details','quotation_id','product_id')
        ->withPivot('detail_quantity','detail_price_unit','detail_price_buy','detail_price_additional','detail_total','detail_status','quotation_description')
        ->withTimestamps();
    }
    public static function getQuotations($search,$filters = []) {
        $query = Quotation::select("quotations.id","quotation_code","quotation_total","quotation_type_money","quotation_status","customer_name")
        ->selectRaw("DATE_FORMAT(quotation_date_issue,'%d/%m/%Y') AS date_issue,CONCAT(user_name,' ',user_last_name) AS name_quoter")
        ->join('customers','quotation_customer','=','customers.id')
        ->join('users','quotation_quoter','=','users.id')
        ->where(function($query)use($search){
            $query->where('customer_name','LIKE','%'.$search.'%')
            ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%') OR quotation_code LIKE CONCAT('%',?,'%')",[$search,$search]);
        });
        foreach ($filters as $filter) {
            $query = $query->where($filter['column'],$filter['sign'],$filter['value']);
        }
        return $query;
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class,'quotation_customer');
    }
    public function contact()
    {
        return $this->belongsTo(Contacts::class,'quotation_customer_contact');
    }
    public static function getQuotationsForOrders($typeMoney,$includeIgv,$customer) {
        return Quotation::select('id','quotation_total','quotation_code')
        ->selectRaw("DATE_FORMAT(quotation_date_issue,'%d/%m/%Y') AS date_issue, 1 AS checked")
        ->where(['quotation_status' => 1, 'quotation_type_money' => $typeMoney,'quotation_include_igv' => $includeIgv, 'quotation_customer' => $customer])->whereNull('order_id')->get();
    }
    public static function getQuotationsReport($startDate,$finalDate) {
        return Quotation::select("quotations.id","quotation_code","quotation_date_issue","customer_name","user_name","user_last_name","contrie","departament_name","quotation_status","quotation_type_money","quotation_change_money")
        ->join('users','users.id','=','quotation_quoter')
        ->join('customers','customers.id','=','quotation_customer')
        ->join('contries','contries.id','=','customer_contrie')
        ->leftJoin('districts','districts.id','=','customer_district')
        ->leftJoin('departaments','departaments.id','=','district_departament')
        ->whereBetween('quotation_date_issue',[$startDate,$finalDate]);
    }
}
