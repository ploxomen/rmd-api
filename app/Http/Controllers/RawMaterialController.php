<?php

namespace App\Http\Controllers;

use App\Models\ChangeMoney;
use App\Models\Products;
use App\Models\Provider;
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
            'total' => $rawMaterials->get()->count(),
            'data' => $rawMaterials->skip($skip)->take($show)->get()
        ]);
    }
    public function updateHistory(Request $request, RawMaterialHistory $historyMaterial) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $total = $request->material_hist_amount * ($request->material_hist_igv ? 1.18 : 1) * $request->material_hist_price_buy;
        $historyMaterial->update([
            'raw_provider' => $request->raw_provider,
            'material_hist_bill' => $request->material_hist_bill,
            'material_hist_date' => $request->material_hist_date,
            'material_hist_guide' => $request->material_hist_guide,
            'material_hist_amount' => $request->material_hist_amount,
            'material_hist_price_buy' => $request->material_hist_price_buy,
            'material_hist_igv' => $request->material_hist_igv ? $request->material_hist_amount * $request->material_hist_price_buy * 0.18 : 0,
            'material_hist_money' => $request->material_hist_money,
            'material_hist_total_buy_pen' => $request->material_hist_money === 'PEN' ? $total : $total * $historyMaterial->material_hist_total_type_change,
            'material_hist_total_buy_usd' => $request->material_hist_money === 'PEN' ? $total / $historyMaterial->material_hist_total_type_change : $total,
            'material_user' => $request->user()->id
        ]);
        $raw_material = RawMaterial::find($historyMaterial->raw_material_id);
        $this->calculateRawMaterial($raw_material);
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
            'data' => RawMaterialHistory::find($historyMaterial,["material_hist_bill",'raw_provider','product_id',"material_hist_guide","material_hist_amount","material_hist_price_buy","id AS history_id","material_hist_igv","","material_hist_money","material_hist_total_buy_pen","material_hist_total_buy_usd","material_hist_total_type_change",'material_hist_date'])
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
            'total' => $rawMaterials->get()->count(),
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
                'measurementProduct' => $material->product->product_unit_measurement,
                'idProduct' => $material->product_id
            ]
        ]);
    }
    public function gepProvider(Request $request) {
        return response()->json( [
            'redirect' => null,
            'error' => false,
            'providers' => Provider::select('provider_name AS label','id AS value', 'provider_number_document')->where('provider_status',1)->get()
        ]);
    }
    public function addProduct(Request $request, $product) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $moneyChange = ChangeMoney::select('change_soles')->where('change_day',date('Y-m-d'))->first();
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => array_merge(Products::find($product,['id','product_unit_measurement','product_name AS material_hist_name_product'])->toArray(),['material_hist_total_type_change' => empty($moneyChange) ? null : $moneyChange->change_soles])
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
        $raw_material->history()->each(function($history){
            $history->delete();
        });
        $raw_material->update([
            'raw_hist_bala_amou' => 0,
            'raw_hist_bala_cost' => 0,
            'raw_hist_prom_weig' => 0
        ]);
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
        $money = ChangeMoney::select('change_soles')->where('change_day',date('Y-m-d'))->first();
        if(empty($money)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'No se ha establecido un tipo de cambio para el dia ' . date('d/m/Y'), 
            ]);
        }
        $rawMaterial = RawMaterial::updateOrCreate(
            ['product_id' => $request->product_id,'raw_material_status' => 1],
            ['raw_material_money' => 'PEN']
        );
        $total = $request->material_hist_amount * ($request->material_hist_igv ? 1.18 : 1) * $request->material_hist_price_buy;
        RawMaterialHistory::create([
            'raw_material_id' => $rawMaterial->id,
            'product_id' => $request->product_id,
            'material_hist_date' => $request->material_hist_date,
            'raw_provider' => $request->raw_provider,
            'material_hist_bill' => $request->material_hist_bill,
            'raw_hist_type' => 'ENTRADA',
            'material_hist_guide' => $request->material_hist_guide,
            'material_hist_amount' => $request->material_hist_amount,
            'material_hist_price_buy' => $request->material_hist_price_buy,
            'material_hist_igv' => $request->material_hist_igv ? $request->material_hist_amount * $request->material_hist_price_buy * 0.18 : 0,
            'material_hist_money' => $request->material_hist_money,
            'material_hist_total_buy_pen' => $request->material_hist_money === 'PEN' ? $total : $total * $money->change_soles,
            'material_hist_total_buy_usd' => $request->material_hist_money === 'PEN' ? $total / $money->change_soles : $total,
            'material_hist_total_type_change' => $money->change_soles,
            'material_user' => $request->user()->id
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
    public function calculateRawMaterial($rawMaterial) {
        $rawMaterial->update([
            'raw_material_stock' => $this->stockTotal($rawMaterial->id)['stock'],
            'raw_material_price_buy' => $this->stockTotal($rawMaterial->id)['total'],
        ]);
    }
    public function stockTotal($idRawMaterial) {
        $history = RawMaterialHistory::where('raw_material_id',$idRawMaterial);
        return [ 'stock' => $history->sum('material_hist_amount'),'total' => $history->sum('material_hist_total_buy_pen')];
    }
}
