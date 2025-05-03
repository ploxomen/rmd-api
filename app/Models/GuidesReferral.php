<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidesReferral extends Model
{
    protected $table = 'guides_referral';
    protected $fillable = [
        'guide_issue_date',
        'guide_customer_id',
        'guide_issue_number',
        'guide_address_destination',
        'guide_justification',
        'guide_user_id',
        'guite_total'
    ];
    protected function casts() : array {
        return [
            'guide_issue_date' => 'date:d/m/Y',
        ];
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'guide_customer_id');
    }
    public function scopeWithCustomer(){
        return $this->leftJoin('customers', 'guide_customer_id', '=', 'customers.id');
    }
    public function scopeWithDetails(){
        return $this->leftJoin('guides_referral_details', 'guides_referral.id', '=', 'guide_referral_id');
    }
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'like', "%$search%")
                ->orWhere('guide_justification', 'like', "%$search%")
                ->orWhere('guide_address_destination', 'like', "%$search%")
                ->orWhere('guide_issue_number', 'like', "%$search%");
        });
    }
    public function product(){
        return $this->belongsToMany(Products::class,'guides_referral_details','guide_referral_id','guide_product_id')->using(GuidesReferralDetails::class)->withPivot('guide_product_quantity','guide_product_type','id')->withTimestamps();
    }
    // public function numberGuide(int $year){
    //     $number = GuidesReferral::where('guide_issue_year', $year)->max('guide_issue_number');
    //     return $number ? $number + 1 : 1;
    // }
}
