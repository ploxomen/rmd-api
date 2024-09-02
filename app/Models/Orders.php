<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $fillable = [
        'customer_id',
        'order_date_issue',
        'user_id',
        'order_igv',
        'order_money',
        'order_mount',
        'order_mount_igv',
        'order_total',
        'order_status',
        'order_district',
        'order_conditions_pay',
        'order_conditions_delivery',
        'order_address',
        'order_project',
        'order_contact_email',
        'order_contact_telephone',
        'order_contact_name',
        'order_file_name',
        'order_file_url',
        'order_number',
        'order_code'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function customer()
    {
        return $this->belongsTo(Customers::class,'customer_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function quotations()
    {
        return $this->hasMany(Quotation::class,'order_id');
    }
    public static function quotationsNew($contactName,$contactNumber,$contactEmail,$nameProyect,$clientId,$igv,$money){
        return Quotation::select('quotations.id AS value','quotation_code AS label')
        ->join('contacts','quotation_customer_contact','=','contacts.id')
        ->where([
            'contact_name' => $contactName,
            'contact_number' => $contactNumber,
            'contact_email' => $contactEmail,
            'contact_status' => 1,
            'quotation_project' => $nameProyect,
            'quotation_status' => 1,
            'quotation_customer' => $clientId,
            'quotation_type_money' => $money,
            'quotation_include_igv' => $igv
        ])->get();
    }
    public static function getOrder($id) {
        return  Orders::select("orders.id","order_date_issue","order_igv","order_money","order_status","customer_id","order_conditions_pay","order_conditions_delivery","order_address","order_project","order_contact_email","order_contact_telephone","order_contact_name","order_file_name","order_district","district_province AS order_province","district_departament AS order_departament")
        ->selectRaw("COUNT(quotations.order_id) AS quotations_total")
        ->leftJoin("quotations","quotations.order_id","=","orders.id")
        ->join("districts","districts.id","=","orders.order_district")
        ->where(['orders.id' => $id])->groupBy("orders.id")->first();
    }
    public static function getOrders($search,$filters){
        $query = Orders::select("orders.id","order_code","order_igv","order_money","order_status","order_mount","order_mount_igv","order_total","customer_name","order_file_name")
        ->selectRaw('DATE_FORMAT(order_date_issue,"%d/%m/%Y") AS date_issue,(SELECT sub_categorie_name FROM 
        quotations
        INNER JOIN quotations_details ON quotations_details.quotation_id = quotations.id
        INNER JOIN products ON products.id = product_id 
        INNER JOIN sub_categories ON sub_categories.id = products.sub_categorie 
        WHERE quotations.order_id = orders.id ORDER BY detail_total DESC LIMIT 1) AS sub_categorie_name')
        ->join('customers','customers.id','=','customer_id')
        ->leftJoin('users','users.id','=','user_id')
        ->where(function($query)use($search){
            $query->where('customer_name','LIKE','%'.$search.'%')
            ->orWhere("order_code",'LIKE','%'.$search.'%')
            ->orWhereRaw("CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')",[$search]);
        });
        foreach ($filters as $filter) {
            $query = $query->where($filter['column'],$filter['sign'],$filter['value']);
        }
        return $query;
    }
    public function district()
    {
        return $this->belongsTo(Districts::class,'order_district');
    }
}
