<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Districts extends Model
{
    public function province()
    {
        return $this->belongsTo(Provinces::class,'district_province');
    }
    public function departament()
    {
        return $this->belongsTo(Departaments::class,'district_departament');
    }
}
