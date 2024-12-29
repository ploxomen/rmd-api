<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    use HasFactory;

    public function roles()
    {
        return $this->belongsToMany(Roles::class,'roles_modules','module','role')->withTimestamps();
    }
    public function moduleGroup()
    {
        return $this->belongsTo(ModuleGroup::class,'id_module_group');
    }
}
