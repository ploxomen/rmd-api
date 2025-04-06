<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderContacts extends Model
{
    protected $table = 'provider_contacts';

    protected $fillable = [
        'provider_id',
        'provider_position',
        'provider_name',
        'provider_email',
        'provider_number',
        'provider_status'
    ];
}
