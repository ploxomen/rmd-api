<?php

namespace App\Http\Controllers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductFinalyImported;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductFinaliesController extends Controller
{
    private $urlModule = '/store/products-finaly';
    public function historiesProductVerif(ProductFinaly $productFinaly)
    {
        $result = $productFinaly->products()->whereIn('product_label', ['IMPORTADO', 'ENSAMBLADO'])->first();
        return response()->json([
            'data' => [
                'nameProduct' => $result?->product_name,
                'idProductFinaly' => $productFinaly->id,
                'typeProductFinaly' => $result->product_label,
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
            'details' => $assembled->product()->select("product_finaly_assem_deta.id AS detail_id", "product_finaly_stock as detail_stock", "product_finaly_type as detail_store", "product_id as detail_product_id")->selectRaw("'old' AS detail_type")->get()->makeHidden('pivot')
        ]);
    }
    public function getImportedHistory(ProductFinalyImported $imported)
    {
        $data = $imported->only('product_finaly_id', 'id', 'product_finaly_hist_bill', 'product_finaly_hist_guide', 'product_finaly_provider', 'product_finaly_money', 'product_finaly_type_change', 'product_finaly_amount', 'product_finaly_price_buy', 'product_finaly_total_buy');
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
        $productsFinaly = ProductFinaly::select("product_name", "products.id", "product_code", "product_label", "product_finaly_stock", "product_unit_measurement", "product_public_customer", "product_finalies.id AS product_finaly_id")->productsActive($search);
        if ($request->filled('tipo')) {
            $productsFinaly->where('product_label', $request->tipo);
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
        if ($productFinaly->products->product_label === "IMPORTADO") {
            $request->validate([
                'product_finaly_provider' => 'required'
            ], [], ['product_finaly_provider' => 'proveedor']);
        } else if ($productFinaly->products->product_label === "ENSAMBLADO") {
            $request->validate([
                'details' => 'required|array'
            ], [], ['details' => 'detalles']);
        }
        DB::beginTransaction();
        try {
            if ($productFinaly->products->product_label === "IMPORTADO") {
                $productImported = new ProductFinalyImported();
                $productImported->product_finaly_id = $productFinaly->id;
                $productImported->product_finaly_created = now()->toDateString();
                $productImported->fill($request->except('product_finaly_id', 'product_name', 'product_id'));
                $productImported->product_finaly_user = $request->user()->id;
                $productImported->save();
            } else if ($productFinaly->products->product_label === "ENSAMBLADO") {
                $productAssembled = new ProductFinalyAssembled();
                $productAssembled->product_finaly_id = $productFinaly->id;
                $productAssembled->product_finaly_created = now()->toDateString();
                $productAssembled->product_finaly_amount = $request->product_finaly_amount;
                $productAssembled->product_finaly_description = $request->product_finaly_description;
                $productAssembled->product_finaly_user = $request->user()->id;
                $productAssembled->save();
                foreach ($request->details as $product) {
                    $productAssembled->product()->attach($product['detail_product_id'], ['product_finaly_stock' => $product['detail_stock'], 'product_finaly_type' => $product['detail_store']]);
                }
            }
            DB::commit();
            return response()->json(['redirect' => null, 'error' => false, 'message' => 'Historial generado correctamente']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage(), 'error' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function totalStock(ProductFinaly $productFinaly) {}
    public function show($id, Request $request)
    {
        $productFinaly = ProductFinaly::findOrFail($id);
        $product = $productFinaly->products;
        $result = [];
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        if ($product->product_label === "IMPORTADO") {
            $result = ProductFinalyImported::getActive($productFinaly->id)->searchHistory($search)->orderBy('product_finaly_created', 'desc');
        } else if ($product->product_label === 'ENSAMBLADO') {
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
            $syncs = [];
            $attach = [];
            foreach ($request->details as $product) {
                if ($product['detail_type'] === "old") {
                    $syncs[$product['detail_product_id']] = ['product_finaly_stock' => $product['detail_stock'], 'product_finaly_type' => $product['detail_store']];
                    continue;
                }
                $attach[$product['detail_product_id']] = ['product_finaly_stock' => $product['detail_stock'], 'product_finaly_type' => $product['detail_store']];
            }
            $assembled->product()->sync($syncs);
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
    public function deleteAssembled(ProductFinalyAssembled $assembled)
    {
        $assembled->product()->detach();
        $assembled->delete();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Historial eliminado correctamente'
        ]);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
