<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialHistory extends Model
{
    protected $table = 'raw_materials_history';
    protected $fillable = ['raw_material_id','product_id','material_hist_bill','material_hist_guide','material_hist_amount','material_hist_price_buy','material_hist_igv','material_hist_money','material_hist_total_buy','material_hist_status'];
}
