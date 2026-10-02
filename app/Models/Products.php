<?php

namespace App\Models;

use App\Observers\ProductsObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Products extends Model
{
    protected $fillable = [
        'product_name',
        'product_description',
        'product_service',
        'product_buy',
        'product_public_customer',
        'product_distributor',
        'sub_categorie',
        'product_img',
        'product_status',
        'product_store',
        'product_label',
        'product_unit_measurement',
        'product_code',
        'type_change_initial',
        'type_money_initial',
        'stock_initial',
        "product_label_2",
        'created_at'
    ];
    protected static function boot()
    {
        parent::boot();
        static::observe(ProductsObserver::class);
    }
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductLabel::class,
            'product_product_labels',
            'product_id',
            'product_label_id'
        )->withPivot([
            'work_time_hours',
            'group_work_number',
            'group_work_time_hours',
        ])->withTimestamps();
    }
    public function listOfLabels()
    {
        return ProductLabel::query()->leftJoin('product_product_labels', function ($join) {
            $join->on('product_product_labels.product_label_id', '=', 'product_labels.id')
                ->where('product_product_labels.product_id', '=', $this->id);
        })->select(
            'product_labels.id',
            'product_labels.name',
            DB::raw('COALESCE(product_product_labels.work_time_hours, "") as work_time_hours'),
            DB::raw('COALESCE(product_product_labels.group_work_number, "") as group_work_number'),
            DB::raw('COALESCE(product_product_labels.group_work_time_hours, "") as group_work_time_hours')
        )->get();
    }
    public static function getCodeProductNew()
    {
        $product = Products::select('product_code')->orderBy('product_code', 'desc')->first();
        if (empty($product)) {
            return 1;
        }
        return $product->product_code + 1;
    }
    public const STATUS_ACTIVE_RELATION = 1;
    public function productRawMaterial()
    {
        return $this->hasOne(RawMaterial::class, 'product_id')->where('raw_material_status', self::STATUS_ACTIVE_RELATION);
    }
    public function productProgress()
    {
        return $this->hasOne(ProductProgress::class, 'product_id')->where('product_progress_status', self::STATUS_ACTIVE_RELATION);
    }
    public function productFinaly()
    {
        return $this->hasOne(ProductFinaly::class, 'product_id')->where('product_finaly_status', self::STATUS_ACTIVE_RELATION);
    }
    public static function getProducts($search)
    {
        return Products::select("products.id", "product_code", "product_buy", "product_label", "product_store", "product_public_customer", "product_distributor", "sub_categorie_name", "categorie_name", "product_name")
            ->join('sub_categories', 'products.sub_categorie', '=', 'sub_categories.id')
            ->join('categories', 'sub_categories.categorie_id', '=', 'categories.id')
            ->where('product_status', '>', 0)->where(function ($query) use ($search) {
                $query->where('product_name', 'like', '%' . $search . '%')
                    ->orWhere('product_description', 'like', '%' . $search . '%')
                    ->orWhere('sub_categorie_name', 'like', '%' . $search . '%')
                    ->orWhere('categorie_name', 'like', '%' . $search . '%');
            });
    }
    public function subcategorie()
    {
        return $this->belongsTo(SubCategories::class, 'sub_categorie');
    }
    public static function reportExcel()
    {
        return Products::with(['subcategorie.categorie'])
            ->where('product_status', '!=', 0)
            ->get()
            ->sortBy(function ($product) {
                return $product->subcategorie->categorie->categorie_name . ' ' . $product->subcategorie->sub_categorie_name;
            })->map(function ($product) {
                return [
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'category_name' => $product->subcategorie->categorie->categorie_name,
                    'subcategory_name' => $product->subcategorie->sub_categorie_name,
                    'product_buy' => $product->product_buy,
                    'product_public_customer' => $product->product_public_customer,
                    'product_distributor' => $product->product_distributor,
                ];
            });
    }
}
