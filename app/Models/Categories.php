<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $fillable = [
        'categorie_name',
        'categorie_status',
    ];
    public function subcategories()
    {
        return $this->hasMany(SubCategories::class,'categorie_id');
    }
    // public static function getAllCategories() {
    //     return Categories::select()->
    // }
}
