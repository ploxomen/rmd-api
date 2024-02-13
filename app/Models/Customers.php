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
        'customer_email',
        'customer_phone',
        'customer_cell_phone',
        'customer_address',
        'customer_district',
        'customer_status'
    ];
    public function contacts()
    {
        return $this->hasMany(Contacts::class,'customer_id');
    }
}
