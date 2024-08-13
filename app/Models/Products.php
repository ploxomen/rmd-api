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
        'product_public_customer',
        'product_distributor',
        'sub_categorie',
        'product_img',
        'product_status'
    ];

    public static function getProducts($search){
        return Products::select("products.id","product_buy","product_public_customer","product_distributor","sub_categorie_name","categorie_name","product_name")
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
    public static function reportExcel()
    {
        return Products::with(['subcategorie.categorie'])
        ->get()
        ->sortBy(function($product){
            return $product->subcategorie->categorie->categorie_name . ' ' . $product->subcategorie->sub_categorie_name;
        })->map(function($product){
            return [
                'product_name' => $product->product_name,
                'category_name' => $product->subcategorie->categorie->categorie_name,
                'subcategory_name' => $product->subcategorie->sub_categorie_name,
                'product_buy' => $product->product_buy,
                'product_public_customer' => $product->product_public_customer,
                'product_distributor' => $product->product_distributor,
            ];
        });
    }
}
