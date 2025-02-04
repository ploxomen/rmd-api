<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeMoney extends Model
{
    protected $table = 'change_money';
    protected $fillable = ['change_day','change_soles','change_attempts','change_user'];
    
}
