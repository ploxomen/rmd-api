<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotations';
    protected $fillable = [
        'quotation_customer',
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
        'quotation_description_products',
        'quotation_status'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function products()
    {
        return $this->belongsToMany(Products::class,'quotations_details','quotation_id','product_id')
        ->withPivot('detail_quantity','detail_price_unit','detail_price_buy','detail_price_additional','detail_total','detail_status')
        ->withTimestamps();
    }
    public static function getQuotations($search,$filters = []) {
        $query = Quotation::select("quotations.id","quotation_total","quotation_type_money","quotation_status","customer_name")
        ->selectRaw("LPAD(quotations.id,5,'0') AS nro_quotation,DATE_FORMAT(quotation_date_issue,'%d/%m/%Y') AS date_issue,CONCAT(user_name,' ',user_last_name) AS name_quoter")
        ->join('customers','quotation_customer','=','customers.id')
        ->join('users','quotation_quoter','=','users.id')
        ->where(function($query)use($search){
            $query->where('customer_name','LIKE','%'.$search.'%')
            ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%') OR LPAD(quotations.id,5,'0') LIKE CONCAT('%',?,'%')",[$search,$search]);
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
}
