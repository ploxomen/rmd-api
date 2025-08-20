<?php

namespace App\Http\Controllers;

use App\Models\ProductFinalAssemDeta;
use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductFinalyImported;
use App\Models\ProductProgress;
use App\Models\ProductProgressHistory;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductFinaliesController extends Controller
{
    private $urlModule = '/store/products-finaly';
    public function historiesProductVerif(ProductFinaly $productFinaly)
    {
        $result = $productFinaly->products()->whereIn('product_label_2', ['IMPORTADO', 'ENSAMBLADO'])->first();
        return response()->json([
            'data' => [
                'nameProduct' => $result?->product_name,
                'idProductFinaly' => $productFinaly->id,
                'typeProductFinaly' => $result->product_label_2,
            ]
        ]);
    }
    public function getAssembledHistory(ProductFinalyAssembled $assembled)
    {
        $data = $assembled->only('product_finaly_id', 'id', 'product_finaly_created', 'product_finaly_description', 'product_finaly_amount');
        $data['product_id'] = $assembled->productFinaly->products->id;
        $data['product_name'] = $assembled->productFinaly->products->product_name;
        $data['product_price_client'] = $assembled->productFinaly->products->product_public_customer;
        return response()->json([
            'redirect' => null,
            'error' => false,
            'data' => $data,
            'details' => $assembled->product()->select("product_finaly_assem_deta.id AS detail_id", "product_finaly_stock as detail_stock", "product_finaly_price_unit AS detail_price_unit", "product_finaly_type as detail_store", "product_id as detail_product_id", "product_id as detail_product_id_old")->selectRaw("'old' AS detail_type")->get()->makeHidden('pivot')
        ]);
    }
    public function getImportedHistory(ProductFinalyImported $imported)
    {
        $data = $imported->only('product_finaly_id', 'id', 'guide_refer_id', 'product_finaly_hist_bill', 'product_finaly_hist_guide', 'product_finaly_provider', 'product_finaly_money', 'product_finaly_type_change', 'product_finaly_amount', 'product_finaly_price_buy', 'product_finaly_total_buy');
        $data['product_id'] = $imported->productFinaly->products->id;
        $data['product_name'] = $imported->productFinaly->products->product_name;
        $data['product_finaly_unit_measurement'] = $imported->productFinaly->products->product_unit_measurement;
        return response()->json([
            'redirect' => null,
            'error' => false,
            'data' => $data
        ]);
    }
    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $productsFinaly = ProductFinaly::select("product_name", "products.id", "product_code", "product_label_2", "product_finaly_stock", "product_unit_measurement", "product_public_customer", "product_finalies.id AS product_finaly_id")->productsActive($search);
        if ($request->filled('tipo')) {
            $productsFinaly->where('product_label_2', $request->tipo);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos terminados obtenidos correctamente',
            'total' => $productsFinaly->get()->count(),
            'data' => $productsFinaly->skip($skip)->take($show)->get()
        ]);
    }
    public function store(Request $request)
    {
        $productFinaly = ProductFinaly::select("product_finalies.id", "product_id")->joinProducts()->productExist()->where('product_id', $request->product_id)->first();
        if (empty($productFinaly)) {
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'No se encontró el producto final'
            ], 422);
        }
        if ($productFinaly->products->product_store !== "PRODUCTO TERMINADO") {
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'El producto seleccionado no es un producto terminado'
            ], 422);
        }
        if ($productFinaly->products->product_label_2 === "IMPORTADO") {
            $request->validate([
                'product_finaly_provider' => 'required'
            ], [], ['product_finaly_provider' => 'proveedor']);
        } else if ($productFinaly->products->product_label_2 === "ENSAMBLADO") {
            $request->validate([
                'details' => 'required|array'
            ], [], ['details' => 'detalles']);
            $totalDetails = [];
            foreach ($request->details as $product) {
                $productExist = array_filter($totalDetails, function ($value) use ($product) {
                    return $value['product_id'] === $product['detail_product_id'] && $value['type'] === $product['detail_store'];
                });
                if (count($productExist) === 0) {
                    $totalDetails[] = [
                        'type' => $product['detail_store'],
                        'product_id' => $product['detail_product_id'],
                        'stock' => $product['detail_stock']
                    ];
                    continue;
                }
                $totalDetails[key($productExist)]['stock'] += $product['detail_stock'];
            }
            foreach ($totalDetails as $detail) {
                if ($detail['type'] === "MATERIA PRIMA") {
                    $rawMaterial = RawMaterial::where(['product_id' => $detail['product_id'], 'raw_material_status' => 1])->first();
                    if (empty($rawMaterial)) {
                        return response()->json([
                            'redirect' => null,
                            'error' => true,
                            'message' => 'No existe el producto de MATERIA PRIMA elegido'
                        ], 422);
                    }
                    if ($rawMaterial->raw_material_stock < $detail['stock']) {
                        return response()->json([
                            'redirect' => null,
                            'error' => true,
                            'message' => "El producto {$rawMaterial->product->product_name} de MATERIA PRIMA no cuenta con stock suficiente, actualmente hay {$rawMaterial->raw_material_stock}, se está tratando de ingresar {$detail['stock']}"
                        ], 422);
                    }
                    continue;
                }
                $productPorgres = ProductProgress::where(["product_id" => $detail["product_id"], 'product_progress_status' => 1])->first();
                if (empty($productPorgres)) {
                    return response()->json([
                        'redirect' => null,
                        'error' => true,
                        'message' => 'No existe el producto de PRODUCTO EN CURSO elegido'
                    ], 422);
                }
                if ($productPorgres->product_progress_stock < $detail['stock']) {
                    return response()->json([
                        'redirect' => null,
                        'error' => true,
                        'message' => "El producto {$productPorgres->product->product_name} de PRODUCTO EN CURSO no cuenta con stock suficiente, actualmente hay {$productPorgres->product_progress_stock}, se está tratando de ingresar {$detail['stock']}"
                    ], 422);
                }
            }
        }
        DB::beginTransaction();
        try {
            if ($productFinaly->products->product_label_2 === "IMPORTADO") {
                $productImported = new ProductFinalyImported();
                $productImported->product_finaly_id = $productFinaly->id;
                $productImported->product_finaly_created = now()->toDateString();
                $productImported->fill($request->except('product_finaly_id', 'product_name', 'product_id'));
                $productImported->product_finaly_user = $request->user()->id;
                $productImported->save();
            } else if ($productFinaly->products->product_label_2 === "ENSAMBLADO") {
                $productAssembled = new ProductFinalyAssembled();
                $productAssembled->product_finaly_id = $productFinaly->id;
                $productAssembled->product_finaly_created = now()->toDateString();
                $productAssembled->product_finaly_amount = $request->product_finaly_amount;
                $productAssembled->product_finaly_description = $request->product_finaly_description;
                $productAssembled->product_finaly_user = $request->user()->id;
                $productAssembled->save();
                foreach ($request->details as $product) {
                    $productAssembled->product()->attach($product['detail_product_id'], [
                        'product_finaly_stock' => $product['detail_stock'], 
                        'product_finaly_type' => $product['detail_store'],
                    ]);
                }
            }
            DB::commit();
            return response()->json(['redirect' => null, 'error' => false, 'message' => 'Historial generado correctamente']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage(), 'error' => true]);
        }
    }
    public function show($id, Request $request)
    {
        $productFinaly = ProductFinaly::findOrFail($id);
        $product = $productFinaly->products;
        $result = [];
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        if ($product->product_label_2 === "IMPORTADO") {
            $result = ProductFinalyImported::getActive($productFinaly->id)->searchHistory($search)->orderBy('product_finaly_created', 'desc');
        } else if ($product->product_label_2 === 'ENSAMBLADO') {
            $result = ProductFinalyAssembled::getActive($productFinaly->id)->searchHistory($search)->orderBy('product_finaly_created', 'desc');
        }
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Historiales obtenidos correctamente',
            'data' => $result->skip($skip)->take($show)->get(),
            'total' => $result->get()->count()
        ]);
    }

    public function updateAssembled(Request $request, ProductFinalyAssembled $assembled)
    {
        $request->validate([
            'details' => 'required|array'
        ], [], ['details' => 'detalles']);
        DB::beginTransaction();
        try {
            $assembled->fill($request->only('product_finaly_description', 'product_finaly_amount'));
            $assembled->product_finaly_user = $request->user()->id;
            $assembled->save();
            $idsUpdates = [];
            $attach = [];
            foreach ($request->details as $product) {
                if ($product['detail_type'] === "old") {
                    ProductFinalAssemDeta::find($product['detail_id'])->update([
                        'product_finaly_stock' => $product['detail_stock'],
                        'product_id' => $product['detail_product_id'],
                        'product_finaly_type' => $product['detail_store']
                    ]);
                    $idsUpdates[] = $product['detail_id'];
                    continue;
                }
                $attach[$product['detail_product_id']] = ['product_finaly_stock' => $product['detail_stock'], 'product_finaly_type' => $product['detail_store']];
            }
            ProductFinalAssemDeta::whereNotIn('id', $idsUpdates)->where('product_assembled_id', $request->id)->get()->each(function ($detail) {
                $detail->delete();
            });
            $assembled->product()->attach($attach);
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Historial actualizado correctamente'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => $th->getMessage()
            ]);
        }
    }
    public function deleteImported(ProductFinalyImported $imported)
    {
        $imported->delete();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Historial eliminado correctamente'
        ]);
    }
    public function deleteProducts(ProductFinalyAssembled $assembled)
    {
        foreach ($assembled->product as $product) {
            ProductProgressHistory::where(['product_final_assem_id' => $product->pivot->id])->get()->each(function ($history) {
                $history->delete();
            });
            RawMaterialHistory::where(['product_final_assem_id' => $product->pivot->id])->get()->each(function ($history) {
                $history->delete();
            });
        }
    }
    public function deleteAssembled(ProductFinalyAssembled $assembled)
    {
        DB::beginTransaction();
        try {
            ProductFinalAssemDeta::where('product_assembled_id', $assembled->id)->get()->each(function ($value) {
                $value->delete();
            });
            $assembled->delete();
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Historial eliminado correctamente'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => $th->getMessage()
            ]);
        }
    }
    public function updateImported(Request $request, ProductFinalyImported $imported)
    {
        $imported->fill($request->except('id', 'product_finaly_id', 'product_id', 'product_name'));
        $imported->product_finaly_user = $request->user()->id;
        $imported->save();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Historial actualizado correctamente'
        ]);
    }
    public function destroy(ProductFinaly $products_finaly)
    {
        DB::beginTransaction();
        try {
            if ($products_finaly->imported()->exists()) {
                $products_finaly->imported()->delete();
            }
            if ($products_finaly->assembled()->exists()) {
                $products_finaly->assembled()->get()->each(function ($assembled) {
                    ProductFinalAssemDeta::where('product_assembled_id', $assembled->id)->get()->each(function ($value) {
                        $value->delete();
                    });
                    $assembled->delete();
                });
            }
            $products_finaly->update(['product_finaly_stock' => 0]);
            DB::commit();
            return response()->json([
                'error' => false,
                'success' => 'Se eliminaron los historiales correctamente'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => $th->getMessage()
            ]);
        }
    }
}
