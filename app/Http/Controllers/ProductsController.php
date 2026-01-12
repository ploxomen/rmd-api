<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Models\Categories;
use App\Models\ChangeMoney;
use App\Models\Commodity;
use App\Models\ProductFinaly;
use App\Models\ProductProgress;
use App\Models\Products;
use App\Models\RawMaterial;
use App\Models\SubCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    private $urlModule = '/products';
    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $products = Products::getProducts($search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos obtenidos correctamente',
            'totalProducts' => $products->count(),
            'data' => $products->skip($skip)->take($show)->get()
        ]);
    }
    public function getProductRawMaterialAndProcess(Request $request)
    {
        $rawsMaterial = RawMaterial::select("product_id", "product_name", "product_unit_measurement", "raw_hist_prom_weig AS cost_unit")->selectRaw("'MATERIA PRIMA' AS tipo")->products()->active();
        $productProgress = ProductProgress::select("product_id", "product_name", "product_unit_measurement", "prod_prog_prom_weig AS cost_unit")->selectRaw("'PRODUCTO CURSO' AS tipo")->products()->active()->union($rawsMaterial)->get();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Productos obtenidos correctamente',
            'products' => $productProgress
        ]);
    }
    public function getProductRawMaterialAndFinaly(Request $request)
    {
        $rawsMaterial = RawMaterial::select("product_id", "product_name", "product_unit_measurement")->selectRaw("'MATERIA PRIMA' AS tipo")->products()->active();
        $productFinaly = ProductFinaly::select("product_id", "product_name", "product_unit_measurement")->selectRaw("'PRODUCTO TERMINADO' AS tipo")->joinProducts()->productExist();
        $commodity = Commodity::select("product_id", "product_name", "product_unit_measurement")->selectRaw("'MERCADERIA' AS tipo")->products()->active();
        $products = $commodity->union($rawsMaterial)->union($productFinaly)->get();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Productos obtenidos correctamente',
            'products' => $products
        ]);
    }
    public function exportProductsExcel(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        if (!is_null($redirect)) {
            return response('Acceso no autorizado', 403);
        }
        $products = Products::reportExcel();
        return Excel::download(new ProductsExport($products, 'reports.productsv2'), 'products.xlsx');
    }
    public function categorie()
    {
        $categories = Categories::where(['categorie_status' => 1])->orderBy('categorie_name')->get()->map(function ($categorie) {
            return [
                'label' => $categorie->categorie_name,
                'options' => $categorie->subcategories()->select('id AS value', 'sub_categorie_name AS label')
                    ->where('sub_categorie_status', 1)->orderBy('sub_categorie_name')->get()->toArray()
            ];
        })->toArray();
        return response()->json([
            'error' => false,
            'message' => 'Categorias obtenidas correctamente',
            'data' => $categories
        ]);
    }
    public function updateStockInitial(Products $product)
    {
        return !(
            $product->productRawMaterial()->where('raw_material_stock', '>', 0)->exists() ||
            $product->productProgress()->where('product_progress_stock', '>', 0)->exists() ||
            $product->productFinaly()->where('product_finaly_stock', '>', 0)->exists()
        );
    }
    public function subcategorie($categorie)
    {
        return response()->json([
            'error' => false,
            'message' => 'Categorias obtenidas correctamente',
            'data' => SubCategories::select('id', 'sub_categorie_name')->where(['categorie_id' => $categorie, 'sub_categorie_status' => 1])->orderBy('sub_categorie_name')->get()
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => [
                'required',
                'string',
                'max:250',
                Rule::unique('products')->where(function ($query) {
                    $query->where('product_status', '!=', 0);
                })
            ],
            'product_description' => 'nullable|string|max:2000',
            'product_buy' => 'nullable|decimal:0,2|min:0',
            'product_public_customer' => 'nullable|decimal:0,2|min:0',
            'product_distributor' => 'nullable|decimal:0,2|min:0',
            'sub_categorie' => 'required|numeric',
            'product_img' => 'nullable|image'
        ]);
        $validator->setAttributeNames([
            'product_name' => 'nombre del producto',
            'product_description' => 'descripción del producto',
            'product_buy' => 'precio de producción',
            'product_public_customer' => 'precio publico del cliente',
            'product_distributor' => 'precio del distribuidor',
            'sub_categorie' => 'subcategoría',
            'product_img' => 'imagen del producto'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Los campos no estan llenados correctamentes', 'data' => $validator->errors()->all(), 'redirect' => null]);
        }
        try {
            DB::beginTransaction();
            $money = ChangeMoney::select('change_soles')->where('change_day', date('Y-m-d'))->first();
            if (!$request->has('product_service') && empty($money)) {
                return response()->json([
                    'redirect' => null,
                    'error' => true,
                    'message' => 'No se ha establecido un tipo de cambio para el dia ' . date('d/m/Y'),
                ]);
            }
            $dataProduct = $request->except('product_service');
            $dataProduct['type_change_initial'] = $money->change_soles;
            if ($request->has('product_img')) {
                $file = $request->file('product_img');
                $fileName = time() . "_" . $file->getClientOriginalName();
                $filePath = $file->move(public_path('storage/products'), $fileName);
                $dataProduct['product_img'] = 'storage/products/' . $fileName;
            }
            $dataProduct['product_service'] = 0;
            if ($request->product_service == "true") {
                $dataProduct['product_service'] = 1;
                $dataProduct['product_buy'] = 0;
                $dataProduct['product_public_customer'] = 0;
                $dataProduct['product_distributor'] = 0;
            }
            $dataProduct['product_status'] = 1;
            $dataProduct['product_code'] = Products::getCodeProductNew();
            $product = Products::create($dataProduct);
            if ($request->product_store === 'MATERIA PRIMA') {
                RawMaterial::create([
                    'product_id' => $product->id,
                    'raw_material_stock' => 0,
                    'raw_material_price_buy' => 0,
                    'raw_material_status' => 1,
                    'raw_material_money' => 'PEN'
                ]);
            } else if ($request->product_store === 'PRODUCTO TERMINADO') {
                ProductFinaly::create([
                    'product_id' => $product->id,
                    'product_finaly_stock' => 0,
                    'product_finaly_price' => 0,
                ]);
            } else if ($request->product_store === 'PRODUCTO MERCADERIA') {
                Commodity::create([
                    'product_id' => $product->id,
                    'commodi_stock' => 0,
                    'commodi_money' => 'PEN',
                    'commodi_price_buy' => 0
                ]);
            }
            DB::commit();
            $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
            return response()->json([
                'redirect' => $redirect,
                'error' => false,
                'message' => 'Producto creado correctamente',
                'data' => $product,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'line' => $th->getLine(),
                'error' => true,
                'message' => $th->getMessage(),
                'data' => [$th->getMessage()]
            ], 500);
        }
    }
    public function update(Request $request, $product)
    {
        $dataProduct = $request->except("id", "product_img", "product_categorie");
        $validator = Validator::make($dataProduct, [
            'product_name' => [
                'required',
                'string',
                'max:250',
                Rule::unique('products')->where(function ($query) use ($product) {
                    $query->where('product_status', '!=', 0)->where('id', '!=', $product);
                })
            ],
            'product_code' => [
                'required',
                'integer',
                Rule::unique('products')->where(function ($query) use ($product) {
                    $query->where('product_status', '!=', 0)->where('id', '!=', $product);
                })
            ],
            'product_description' => 'nullable|string|max:2000',
            'product_buy' => 'nullable|decimal:0,2|min:0',
            'product_public_customer' => 'required|decimal:0,2|min:0',
            'product_distributor' => 'nullable|decimal:0,2|min:0',
            'sub_categorie' => 'required|numeric',
            'product_img' => 'nullable|image'
        ]);
        $validator->setAttributeNames([
            'product_name' => 'nombre del producto',
            'product_description' => 'descripción del producto',
            'product_buy' => 'precio de producción',
            'product_public_customer' => 'precio de venta',
            'product_distributor' => 'precio del distribuidor',
            'sub_categorie' => 'subcategoría',
            'product_img' => 'imagen del producto',
            'product_code' => 'código del producto'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Los campos no estan llenados correctamentes', 'data' => $validator->errors()->all(), 'redirect' => null]);
        }
        try {
            $product = Products::find($product);
            if ($this->updateStockInitial($product)) {
                $money = ChangeMoney::select('change_soles')->where('change_day', date('Y-m-d'))->first();
                if (empty($money)) {
                    return response()->json([
                        'redirect' => null,
                        'error' => true,
                        'message' => 'No se ha establecido un tipo de cambio para el dia ' . date('d/m/Y'),
                    ]);
                }
                 $dataProduct['type_change_initial'] = $money->change_soles;
            }
            if ($request->has('delete_img') && File::exists($product->product_img)) {
                File::delete($product->product_img);
                $dataProduct['product_img'] = null;
            }
            $dataProduct['product_service'] = 0;
            if ($request->product_service == "true") {
                $dataProduct['product_service'] = 1;
                $dataProduct['product_buy'] = 0;
                $dataProduct['product_public_customer'] = 0;
                $dataProduct['product_distributor'] = 0;
            }
            if ($request->has('product_img')) {
                $file = $request->file('product_img');
                $fileName = time() . "_" . $file->getClientOriginalName();
                $file->move(public_path('storage/products'), $fileName);
                $dataProduct['product_img'] = 'storage/products/' . $fileName;
            }
            if ($product->product_store === 'MATERIA PRIMA' && $request->product_store !== 'MATERIA PRIMA') {
                RawMaterial::where('product_id', $product->id)->update(['raw_material_status' => 0]);
            } else if ($product->product_store === 'PRODUCTO TERMINADO' && $request->product_store !== 'PRODUCTO TERMINADO') {
                ProductFinaly::where('product_id', $product->id)->update(['product_finaly_status' => 0]);
            } else if ($product->product_store === 'PRODUCTO MERCADERIA' && $request->product_store !== 'PRODUCTO MERCADERIA') {
                Commodity::where('product_id', $product->id)->update(['commodi_status' => 0]);
            }
            $product->update($dataProduct);
            if ($request->product_store === 'MATERIA PRIMA') {
                RawMaterial::firstOrCreate(
                    ['product_id' => $product->id, 'raw_material_status' => 1],
                    [
                        'product_id' => $product->id,
                        'raw_material_stock' => 0,
                        'raw_material_price_buy' => 0,
                        'raw_material_status' => 1,
                        'raw_material_money' => 'PEN'
                    ]
                );
            } else if ($request->product_store === 'PRODUCTO TERMINADO') {
                ProductFinaly::firstOrCreate(
                    ['product_id' => $product->id, 'product_finaly_status' => 1],
                    [
                        'product_id' => $product->id,
                        'commodi_stock' => 0,
                        'product_finaly_price' => 0,
                    ]
                );
            } else if ($request->product_store === 'PRODUCTO MERCADERIA') {
                Commodity::firstOrCreate(
                    ['product_id' => $product->id, 'commodi_status' => 1],
                    [
                        'product_id' => $product->id,
                        'commodi_stock' => 0,
                        'commodi_money' => 'PEN',
                        'commodi_price_buy' => 0
                    ]
                );
            }
            $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
            return response()->json([
                'redirect' => $redirect,
                'error' => false,
                'message' => 'Producto actualizado correctamente',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => $th->getMessage(),
                'data' => [$th->getMessage()]
            ], 500);
        }
    }
    public function show(Request $request)
    {
        $product = Products::select(
            'products.id',
            'product_name',
            'product_description',
            'product_service',
            'product_buy',
            'product_public_customer',
            'product_distributor',
            'sub_categorie',
            'product_img',
            'product_store',
            'product_label',
            'product_unit_measurement',
            'product_code',
            'stock_initial',
            'type_money_initial',
            "product_label_2"
        )->where('products.id', $request->product)->first();
        $productCategorie = $product->subcategorie->categorie_id;
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Producto obtenido correctamente',
            'data' => [
                'url' => $request->root(),
                'product' => $product,
                'updateStockInitial' => $this->updateStockInitial($product),
                'categorieId' => $productCategorie,
            ]
        ]);
    }
    public function destroy(Request $request, $product)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        if (!is_null($redirect)) {
            return response()->json([
                'redirect' => $redirect,
                'error' => false,
            ], 403);
        }
        $productModel =  Products::find($product);
        if (!$this->updateStockInitial($productModel)) {
            return response()->json([
                'error' => true,
                'message' => 'No se puede eliminar el producto ya que contiene movimentos de entrada y/o salida',
                'redirect' => null
            ]);
        }
        $productModel->update(['product_status' => 0]);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Producto eliminado correctamente'
        ]);
    }
}
