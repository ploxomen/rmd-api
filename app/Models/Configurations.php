<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configurations extends Model
{
    // protected $primaryKey = 'description';
    protected $fillable = [
        'description',
        'value'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
