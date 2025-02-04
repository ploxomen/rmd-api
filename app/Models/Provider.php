<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $table = 'provider';
    protected $fillable = [
        'provider_type_document',
        'provider_contrie',
        'provider_number_document',
        'provider_name',
        'provider_address',
        'provider_district',
        'user_create',
        'provider_status',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function contacts()
    {
        return $this->hasMany(ProviderContacts::class,'provider_id');
    }
}
