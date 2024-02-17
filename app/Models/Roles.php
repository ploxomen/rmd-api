<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable = [
        'rol_name',
    ];
    protected $hidden = [
        'create_at',
        'update_at',
    ];

    public function modules()
    {
        return $this->belongsToMany(Modules::class, 'roles_modules', 'role', 'module')->withTimestamps();
    }
    public function users()
    {
        return $this->belongsToMany(User::class,'users_roles','role','user');
    }
}
