<?php

namespace App\Http\Controllers;

use App\Models\ProductProgress;
use App\Models\ProductProgressHistory;
use App\Models\RawMaterial;
use Illuminate\Http\Request;

class ProductProgressController extends Controller
{
    private $urlModule = '/store/product-progress';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $productProgress = ProductProgress::getProductProgress($request->filter_initial,$request->filter_final,$search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos en progreso obtenidos correctamente',
            'total' => $productProgress->get()->count(),
            'data' => $productProgress->skip($skip)->take($show)->get()
        ]);
    }
    public function deleteHistory(Request $request, ProductProgressHistory $historyProgress) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $productProgress = ProductProgress::find($historyProgress->product_progress_id);
        $stockDelete = $historyProgress->product_progress_history_stock;
        $rawMaterial = RawMaterial::where(['product_id' => $productProgress->product_id,'raw_material_status' => 1])->first();
        if(empty($rawMaterial)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'El producto no existe como materia prima', 
            ]);
        }
        $historyProgress->delete();
        $this->calculateProductProgress($productProgress);
        $this->calculateRawMaterial($rawMaterial,$stockDelete,"sumar");
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'El registro se eliminó correctamente',
        ]);
    }
    public function updateHistory(Request $request, ProductProgressHistory $historyProgress) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $productProgress = ProductProgress::find($historyProgress->product_progress_id);
        $rawMaterial = RawMaterial::where(['product_id' => $productProgress->product_id,'raw_material_status' => 1])->first();
        if(empty($rawMaterial)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'El producto no existe como materia prima', 
            ]);
        }
        $stockLast = $historyProgress->product_progress_history_stock;
        $productProgress->update(['product_progress_stock' => $productProgress->product_progress_stock + $stockLast]);
        $rawMaterial->update(['raw_material_stock' => $rawMaterial->raw_material_stock + $stockLast]);
        if($rawMaterial->raw_material_stock < $request->stock){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'La cantidad ingresada supera el stock del producto', 
            ]);
        }
        $historyProgress->update([
            'product_progress_history_date' => $request->date,
            'product_progress_history_stock' => $request->stock,
            'product_progress_history_description' => $request->details
        ]);
        $this->calculateProductProgress($productProgress);
        $this->calculateRawMaterial($rawMaterial,$request->stock);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Registro actualizado correctamente', 
        ]);
    }
    public function oneHistory(Request $request, $historyProgress) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'El registro se eliminó correctamente',
            'data' => ProductProgressHistory::select(["product_id",'product_progress_history_date AS date','product_progress_history_stock AS stock',"product_progress_history_description AS details", "product_unit_measurement AS unit_measurement"])->join('products','products.id','=','product_id')->where(['product_progress_history.id' => $historyProgress])->first()
        ]);
    }
    public function listHistory(Request $request, $productProgress) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $historyProductProgress = ProductProgressHistory::getHistory($productProgress,$search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Datos obtenidos correctamente',
            'total' => $historyProductProgress->get()->count(),
            'data' => $historyProductProgress->skip($skip)->take($show)->get()
        ]);
    }
    public function historyProductProgress(Request $request, ProductProgress $productProgress) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => [
                'nameProduct' => $productProgress->product->product_name,
                'idProductProgress' => $productProgress->id,
                'idProduct' => $productProgress->product_id
            ]
        ]);
    }
    public function store(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $rawMaterial = RawMaterial::where(['product_id' => $request->product_id,'raw_material_status' => 1])->first();
        if(empty($rawMaterial)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'El producto seleccionado no está relacionado con materia prima', 
            ]);
        }
        if($rawMaterial->raw_material_stock < $request->stock){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'La cantidad ingresada supera el stock del producto', 
            ]);
        }
        $productProgress = ProductProgress::updateOrCreate(
            ['product_id' => $request->product_id,'product_progress_status' => 1],
            ['product_progress_stock' => 0]
        );
        ProductProgressHistory::create([
            'product_id' => $request->product_id,
            'product_progress_id' => $productProgress->id,
            'product_progress_history_date' => $request->date,
            'product_progress_history_stock' => $request->stock,
            'product_progress_history_description' => $request->details,
            'product_progress_history_status' => 1,
        ]);
        $this->calculateProductProgress($productProgress);
        $this->calculateRawMaterial($rawMaterial,$request->stock);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Registro creado correctamente', 
        ]);
    }
    public function calculateRawMaterial($rawMaterial,$stock, $action = "restar") {
        $rawMaterial->update([
            'raw_material_stock' => $action === 'restar' ? $rawMaterial->raw_material_stock - $stock : $rawMaterial->raw_material_stock + $stock,
        ]);
    }
    public function calculateProductProgress($productProgress) {
        $productProgress->update([
            'product_progress_stock' => $this->stockTotal($productProgress->id)['stock'],
        ]);
    }
    public function stockTotal($idProductProgress) {
        $history = ProductProgressHistory::where('product_progress_id',$idProductProgress);
        return [ 'stock' => $history->sum('product_progress_history_stock')];
    }
    public function getRawMaterialActive(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $rawMaterials = RawMaterial::getRawMasterials(null,'');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Materia prima obtenidos correctamente',
            'data' => $rawMaterials->get()
        ]);
    }
    public function destroy(Request $request,ProductProgress $product_progress) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $rawMaterial = RawMaterial::where(['product_id' => $product_progress->product_id,'raw_material_status' => 1])->first();
        if(empty($rawMaterial)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'El producto seleccionado no está relacionado con materia prima', 
            ]);
        }
        $stockTotal = $this->stockTotal($product_progress->id)['stock'];
        $product_progress->history()->delete();
        $product_progress->delete();
        $this->calculateRawMaterial($rawMaterial,$stockTotal,"sumar");
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Producto eliminado correctamente',
        ]);
    }
}
