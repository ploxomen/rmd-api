<?php

namespace App\Models;

use App\Observers\GuidesReferralDetailsObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GuidesReferralDetails extends Pivot
{
    protected $table = 'guides_referral_details';
    protected $fillable = [
        'guide_referral_id',
        'guide_product_quantity',
        'guide_product_id',
        'guide_product_type'
    ];
    public $incrementing = true; // Indica que tiene una clave primaria auto-incremental
    protected $primaryKey = "id";
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(GuidesReferralDetailsObserver::class);
    }
    public function guideReferral()
    {
        return $this->belongsTo(GuidesReferral::class,'guide_referral_id');
    }
}
