<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    private $urlModule = '/raw-material';
    public function index(Request $request){
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $subStore = $request->has('subStore') ? $request->subStore : null; 
        $skip = ($request->page - 1) * $request->show;
        $rawMaterials = RawMaterial::getRawMasterials($subStore,$search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Datos obtenidos correctamente',
            'total' => $rawMaterials->count(),
            'data' => $rawMaterials->skip($skip)->take($show)->get()
        ]);
    }
    public function updateHistory(Request $request, RawMaterialHistory $historyMaterial) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $historyMaterial->update([
            'material_hist_bill' => $request->material_hist_bill,
            'material_hist_guide' => $request->material_hist_guide,
            'material_hist_amount' => $request->material_hist_amount,
            'material_hist_price_buy' => $request->material_hist_price_buy,
            'material_hist_igv' => $request->material_hist_igv,
            'material_hist_money' => $request->material_hist_money,
            'material_hist_total_buy' => $request->material_hist_total_buy,
            'material_user' => $request->user()->id
        ]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Registro actualzado correctamente', 
        ]);
    }
    public function oneHistory(Request $request, $historyMaterial) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'El registro se eliminó correctamente',
            'data' => RawMaterialHistory::find($historyMaterial,["material_hist_bill",'product_id',"material_hist_guide","material_hist_amount","material_hist_price_buy","id AS history_id","material_hist_igv","material_hist_money","material_hist_total_buy"])
        ]);
    }
    public function deleteHistory(Request $request, RawMaterialHistory $historyMaterial) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $historyMaterial->delete();
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'El registro se eliminó correctamente',
        ]);
    }
    public function listHistory(Request $request, $historyMaterial) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $rawMaterials = RawMaterialHistory::getHistory($historyMaterial,$search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Datos obtenidos correctamente',
            'total' => $rawMaterials->count(),
            'data' => $rawMaterials->skip($skip)->take($show)->get()
        ]);
    }
    public function historyRawMaterial(Request $request, RawMaterial $material) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => [
                'nameMaterial' => $material->product->product_name,
                'idMaterial' => $material->id,
                'measurementProduct' => $material->product->product_unit_measurement
            ]
        ]);
    }
    public function addProduct(Request $request, $product) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => Products::find($product,['id','product_unit_measurement'])
        ]);
    }
    public function disabledProduct(Request $request, $numberBill) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => RawMaterialHistory::select('product_id')->where('material_hist_bill',$numberBill)->get()
        ]);
    }
    public function destroy(Request $request, RawMaterial $raw_material) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $raw_material->history()->delete();
        $raw_material->delete();
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Los datos se eliminaron correctamente', 
        ]);
    }
    public function store(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if($this->validateDuplicateProduct($request->material_hist_bill,$request->product_id)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'El producto no puede ser añadido debido a que ya se encuentra registrado con el mismo numero de factura', 
            ]);
        }
        $rawMaterial = RawMaterial::updateOrCreate(
            ['product_id' => $request->product_id],
            ['raw_material_price_buy' => $request->material_hist_total_buy,'raw_material_money' => $request->material_hist_money]
        );
        RawMaterialHistory::create([
            'raw_material_id' => $rawMaterial->id,
            'product_id' => $request->product_id,
            'material_hist_bill' => $request->material_hist_bill,
            'material_hist_guide' => $request->material_hist_guide,
            'material_hist_amount' => $request->material_hist_amount,
            'material_hist_price_buy' => $request->material_hist_price_buy,
            'material_hist_igv' => $request->material_hist_igv,
            'material_hist_money' => $request->material_hist_money,
            'material_hist_total_buy' => $request->material_hist_total_buy,
            'material_user' => $request->user()->id
        ]);
        $rawMaterial->update([
            'raw_material_stock' => $this->stockTotal($rawMaterial->id),
        ]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Registro creado correctamente', 
        ]);
    }
    public function validateDuplicateProduct($numberBill,$idProduct) {
        $materialDuplicate = RawMaterialHistory::where(['product_id' => $idProduct, 'material_hist_bill' => $numberBill])->first();
        return !empty($materialDuplicate);
    }
    public function stockTotal($idRawMaterial) {
        return RawMaterialHistory::where('raw_material_id',$idRawMaterial)->sum('material_hist_amount');
    }
}
