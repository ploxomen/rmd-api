<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    protected $fillable = [
        'customer_id',
        'contact_position',
        'contact_name',
        'contact_email',
        'contact_number',
        'contact_status'
    ];
}
