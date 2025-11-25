<?php

namespace App\Models;

use App\Observers\ProductFinalyAssembledObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinalyAssembled extends Model
{
    protected $table = "product_finaly_assembleds";
    protected $fillable = ['product_finaly_amount', 'product_finaly_id', 'guide_refer_id', 'product_finaly_created', 'prod_fina_type_change', 'product_finaly_description', 'product_finaly_user', 'product_finaly_total'];
    protected static function boot()
    {
        parent::boot();
        // Registrar el observer aquí
        static::observe(ProductFinalyAssembledObserver::class);
    }
    public function product()
    {
        return $this->belongsToMany(Products::class, 'product_finaly_assem_deta', 'product_assembled_id', 'product_id')->using(ProductFinalAssemDeta::class)->withPivot('product_finaly_stock', 'product_finaly_subtotal', 'product_finaly_price_unit', 'product_finaly_type', 'id')->withTimestamps();
    }
    public static function reportEntry(string $dateInitial, string $dateFinaly)
    {
        return ProductFinalyAssembled::select('product_name', 'product_code', 'product_finaly_amount AS stock')
            ->selectRaw('"PEN" AS type_money, prod_fina_type_change AS type_change_money')
            ->addSelect('product_finaly_total AS price_unit_pen')
            ->selectRaw('product_finaly_created AS date, "PRODUCTO TERMINADO" AS store, "ENSAMBLE" AS type_mov, "-" AS number_doc_provider, "RMD" as provider, "-" AS number_guide, (product_finaly_total * product_finaly_amount) AS cost_total_pen, (product_finaly_total * product_finaly_amount) AS valorization')
            ->leftJoin('products', 'products.id', '=', 'product_finaly_id')
            ->where('product_finaly_amount', '>=', 0)
            // ->whereBetween('product_finaly_created',[$dateInitial,$dateFinaly])->where('prod_prog_hist_type','ENTRADA');
        ;
    }
    public static function reportExit(string $dateInitial, string $dateFinaly)
    {
        return ProductFinalyAssembled::select('product_name', 'product_code', 'product_finaly_amount AS stock')
            ->selectRaw('"PEN" AS type_money, prod_fina_type_change AS type_change_money')
            ->addSelect('product_finaly_total AS price_unit_pen')
            ->selectRaw('product_finaly_created AS date, "PRODUCTO TERMINADO" AS store, guide_type_motion AS type_mov, COALESCE(customer_number_document, "-") AS number_doc_provider, COALESCE(customer_name, "RMD") as provider, guide_issue_number AS number_guide, (product_finaly_total * product_finaly_amount) AS cost_total_pen, (product_finaly_total * product_finaly_amount) AS valorization')
            ->leftJoin('products', 'products.id', '=', 'product_finaly_id')
            ->leftJoin('guides_referral_details', function ($join) {
                $join->on('guides_referral_details.id', '=', 'guide_refer_id')
                    ->leftJoin('guides_referral', function ($subJoin) {
                        $subJoin->on('guides_referral.id', '=', 'guides_referral_details.guide_referral_id')
                            ->leftJoin('customers', 'customers.id', '=', 'guides_referral.guide_customer_id');;
                    });
            })
            ->where('product_finaly_amount', '<', 0)
            // ->whereBetween('product_finaly_created',[$dateInitial,$dateFinaly])->where('prod_prog_hist_type','ENTRADA');
        ;
    }
    public function scopeGetActive($query, $productFinalyId)
    {
        return $query->select("product_finaly_assembleds.id", "guide_refer_id", "product_unit_measurement", "product_finaly_amount", "product_finaly_description", "product_finaly_total")
            ->selectRaw("DATE_FORMAT(product_finaly_created, '%d/%m/%Y') as product_finaly_created, CONCAT(user_name,' ',user_last_name) as user_name")
            ->join("product_finalies", "product_finalies.id", "=", 'product_finaly_id')
            ->join("products", "products.id", "=", 'product_id')
            ->leftJoin('users', 'users.id', '=', 'product_finaly_user')
            ->where("product_finaly_id", $productFinalyId);
    }
    public function scopeSearchHistory($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('product_finaly_amount', 'LIKE', '%' . $search . '%')
                ->orWhere("product_finaly_description", 'LIKE', '%' . $search . '%')
                ->orWhere("product_unit_measurement", 'LIKE', '%' . $search . '%')
                ->orWhereRaw(" CONCAT(user_name,' ',user_last_name) LIKE CONCAT('%',?,'%')", [$search]);
        });
    }
    public function productFinaly()
    {
        return $this->belongsTo(ProductFinaly::class, 'product_finaly_id');
    }
}
