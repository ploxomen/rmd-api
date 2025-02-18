<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProgressHistory extends Model
{
    protected $table = 'product_progress_history';
    protected $fillable = ['product_id','product_progress_id','product_progress_history_date','product_progress_history_stock','product_progress_history_status','product_progress_history_description'];

    public static function getHistory(int $idProductProgress, $search) {
        return ProductProgressHistory::select('product_progress_history.id','product_progress_id','product_id','product_progress_history_stock','product_name','product_progress_history_description')
        ->selectRaw("DATE_FORMAT(product_progress_history_date,'%d/%m/%Y') AS product_progress_history_date")
        ->leftJoin('products','products.id','=','product_id')
        ->where(function($query)use($search){
            $query->where('product_name','like','%'.$search.'%')
            ->orWhere('product_progress_history_description','like','%'.$search.'%')
            ->orWhereRaw("DATE_FORMAT(product_progress_history_date,'%d/%m/%Y') LIKE CONCAT('%',?,'%')",[$search])
            ->orWhere('product_progress_history_stock','like','%'.$search.'%');
        })
        ->where('product_progress_id',$idProductProgress)->orderBy('product_progress_history_date','desc');
    }
}
