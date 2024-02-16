<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategories extends Model
{
    protected $fillable = [
        'categorie_id',
        'sub_categorie_name',
        'sub_categorie_status'
    ];
    public function categorie()
    {
        return $this->belongsTo(Categories::class,'categorie_id');
    }
    public function products()
    {
        return $this->hasMany(Products::class,'sub_categorie');
    }
}
