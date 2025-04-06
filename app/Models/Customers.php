<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $fillable = [
        'customer_type_document',
        'customer_contrie',
        'customer_number_document',
        'customer_name',
        'customer_address',
        'customer_district',
        'user_create',
        'customer_status',
        'customer_retaining'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function contacts()
    {
        return $this->hasMany(Contacts::class,'customer_id');
    }
}
