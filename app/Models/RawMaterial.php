<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $fillable = ['product_id','raw_material_stock','raw_material_price_buy','raw_material_status','raw_material_money'];
    public function history(): HasMany
    {
        return $this->hasMany(RawMaterialHistory::class,'raw_material_id');
    }
    public static function getRawMasterials($subStore,$search) {
        $rawMaterial = RawMaterial::select('product_id','product_name','raw_materials.id','raw_material_stock','raw_material_price_buy','store_sub_name','raw_material_money')
        ->join('products','product_id','=','products.id')
        ->leftJoin('stores_sub','products.id','=','product_substore')
        ->where('raw_material_status',1)
        ->where(function($query)use($search){
            $query->where('raw_material_stock','like','%'.$search.'%')
            ->orWhere('raw_material_price_buy','like','%'.$search.'%')
            ->orWhere('raw_material_price_buy','like','%'.$search.'%')
            ->orWhere('product_name','like','%'.$search.'%');
        });
        if(!is_null($subStore)){
            $rawMaterial = $rawMaterial->where('product_substore',$subStore);
        }
        return $rawMaterial;
    }
}
