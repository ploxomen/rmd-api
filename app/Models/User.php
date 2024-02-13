<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $table = 'users';

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_type_document',
        'user_number_document',
        'user_name',
        'user_last_name',
        'user_email',
        'password',
        'user_phone',
        'user_cell_phone',
        'user_birthdate',
        'user_status',
        'user_gender',
        'user_address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Roles::class,'users_roles','user','role')->withPivot('active')->withTimestamps();
    }
}
