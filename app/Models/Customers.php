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
        'customer_address',
        'customer_district',
        'user_create',
        'customer_status',
        'customer_retaining'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function scopeSearch($query, $search)
    {
        $query->where('customer_name', 'like', '%' . $search . '%')
            ->orWhere('customer_number_document', 'like', '%' . $search . '%');
    }
    /** 
     * $seach es el valor a buscar por distrito
     * Debe estar with district
     */
    public function scopeSearchDistrict($query, $search)
    {
        $query->whereHas('district', function ($q) use ($search) {
            $q->orWhere('district_name', 'like', '%' . $search . '%');
        });
    }
    public function contacts()
    {
        return $this->hasMany(Contacts::class, 'customer_id')->where('contact_status', Contacts::STATUS_ACTIVE);
    }
    public function scopeActive($query)
    {
        return $query->where('customer_status', 1);
    }
    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'customer_type_document');
    }
    public function district()
    {
        return $this->belongsTo(Districts::class, 'customer_district');
    }
}
