<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stores extends Model
{
    protected $fillable = ['store_name','store_status','store_description'];
    public function subStories(): HasMany
    {
        return $this->hasMany(StoresSub::class,'store_id');
    }
    public static function getStoresAndSubStores($search)  {
        return Stores::select('stores.id','store_name','store_description')
        ->selectRaw("CONCAT('[',GROUP_CONCAT(JSON_OBJECT('id',stores_sub.id,'name',store_sub_name)),']') AS substores")
        ->join('stores_sub','store_id','=','stores.id')
        ->where(['store_status' => 1, 'store_sub_status' => 1])
        ->where(function($query)use($search){
            $query->where('store_name','like','%'.$search.'%')
            ->orWhere('store_sub_name','like','%'.$search.'%')
            ->orWhere('store_description','like','%'.$search.'%');
        })->groupBy('stores.id');
    }
}
