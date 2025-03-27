<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinalyImported extends Model
{
   protected $table = "product_finaly_imported";
   protected $fillable = ["product_finaly_id","product_finaly_provider","product_finaly_money","product_finaly_hist_bill","product_finaly_hist_guide","product_finaly_type_change","product_finaly_amount","product_finaly_price_buy","product_finaly_total_buy","product_finaly_user"];
}
