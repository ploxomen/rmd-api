<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'product_name',
        'product_description',
        'product_service',
        'product_buy',
        'product_sale',
        'sub_categorie',
        'product_img',
        'product_status'
    ];

    public static function getProducts($search){
        return Products::select("products.id","product_buy","product_sale","sub_categorie_name","categorie_name","product_name")
        ->join('sub_categories','products.sub_categorie','=','sub_categories.id')
        ->join('categories','sub_categories.categorie_id','=','categories.id')
        ->where('product_status','>',0)->where(function($query)use($search){
            $query->where('product_name','like','%'.$search.'%')
            ->orWhere('product_description','like','%'.$search.'%')
            ->orWhere('sub_categorie_name','like','%'.$search.'%')
            ->orWhere('categorie_name','like','%'.$search.'%');
        });
    }
    public function subcategorie()
    {
        return $this->belongsTo(SubCategories::class,'sub_categorie');
    }
}
