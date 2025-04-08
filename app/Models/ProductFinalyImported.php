<?php

namespace App\Models;

use App\Observers\ProductFinalyImportedObserver;
use Illuminate\Database\Eloquent\Model;

class ProductFinalyImported extends Model
{
   protected $table = "product_finaly_imported";
   protected $fillable = ["product_finaly_id","quotation_detail_id","product_finaly_created","product_finaly_provider","product_finaly_money","product_finaly_hist_bill","product_finaly_hist_guide","product_finaly_type_change","product_finaly_amount","product_finaly_price_buy","product_finaly_total_buy","product_finaly_user"];
   protected static function boot()
   {
       parent::boot();
       // Registrar el observer aquí
       static::observe(ProductFinalyImportedObserver::class);
   }
   public function scopeGetActive($query,$productFinalyId) {
      return $query->select("provider_name",'quotation_detail_id',"product_finaly_amount","product_finaly_imported.id","product_unit_measurement","product_finaly_money","product_finaly_hist_bill","product_finaly_hist_guide","product_finaly_price_buy","product_finaly_total_buy")
      ->selectRaw("DATE_FORMAT(product_finaly_created, '%d/%m/%Y') as product_finaly_created, CONCAT(user_name,' ',user_last_name) as user_name")
      ->join("product_finalies","product_finalies.id","=",'product_finaly_id')
      ->leftJoin('provider','provider.id','=','product_finaly_provider')
      ->join("products","products.id","=",'product_id')
      ->leftJoin('users','users.id','=','product_finaly_user')
      ->where("product_finaly_id",$productFinalyId);
   }
   public function scopeSearchHistory($query,$search) {
      return $query->where(function($query)use($search){
         $query->where('provider_name','LIKE','%'.$search.'%')
         ->orWhere("product_finaly_hist_guide",'LIKE','%'.$search.'%')
         ->orWhere("product_finaly_amount",'LIKE','%'.$search.'%')
         ->orWhere("product_finaly_hist_bill",'LIKE','%'.$search.'%')
         ->orWhere("product_unit_measurement",'LIKE','%'.$search.'%')
         ->orWhereRaw(" CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')",[$search]);   
     });
   }
   public function productFinaly(){
      return $this->belongsTo(ProductFinaly::class,'product_finaly_id');
   }
}
