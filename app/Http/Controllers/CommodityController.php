<?php

namespace App\Http\Controllers;

use App\Models\ChangeMoney;
use App\Models\Commodity;
use App\Models\CommodityHistory;
use Illuminate\Http\Request;

class CommodityController extends Controller
{
    private $urlModule = '/store/commodity/general';
    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $commodity = Commodity::select("commodi_stock","commodi_money","commodi_price_buy","commodities.id","products.id AS product_id","product_code","product_name","product_unit_measurement","product_label_2")->products()->enabled()->where('product_name','like','%'.$search.'%');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos de almacenamiento mercadería obtenidos correctamente',
            'total' => $commodity->get()->count(),
            'data' => $commodity->skip($skip)->take($show)->get()
        ]);
    }
    //AGREGAR HISTORIAL
    public function store(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if($this->validateDuplicateProduct($request->commodi_hist_bill,$request->product_id)){
            return response()->json([
                'redirect' => $redirect,
                'error' => true, 
                'message' => 'El historial no puede ser añadido debido a que ya se encuentra registrado con el mismo numero de factura', 
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
        $commodity = Commodity::updateOrCreate(
            ['product_id' => $request->product_id,'commodi_status' => 1],
            ['commodi_money' => 'PEN']
        );
        $total = $request->commodi_hist_price_buy * $request->commodi_hist_amount;
        CommodityHistory::create([
            'commodi_id' => $commodity->id,
            'product_id' => $request->product_id,
            'commodi_hist_date' => $request->commodi_hist_date,
            'commodity_provider' => $request->commodity_provider,
            'commodi_hist_bill' => $request->commodi_hist_bill,
            'commodi_hist_type' => 'ENTRADA',
            'commodi_hist_guide' => $request->commodi_hist_guide,
            'commodi_hist_amount' => $request->commodi_hist_amount,
            'commodi_hist_price_buy' => $request->commodi_hist_price_buy,
            'commodi_hist_money' => $request->commodi_hist_money,
            'commodi_hist_total_buy' => $request->commodi_hist_money === 'PEN' ? $total : $total * $money->change_soles,
            'commodi_hist_total_buy_usd' => $request->commodi_hist_money === 'PEN' ? $total / $money->change_soles : $total,
            'commodi_hist_type_change' => $money->change_soles,
            'commodi_hist_user' => $request->user()->id
        ]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Registro creado correctamente', 
        ]);
    }
    public function validateDuplicateProduct($numberBill,$idProduct) {
        $materialDuplicate = CommodityHistory::where(['product_id' => $idProduct, 'commodi_hist_bill' => $numberBill])->first();
        return !empty($materialDuplicate);
    }
    public function historiesCommodities(Request $request, $commodity)
    {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $commodities = CommodityHistory::getHistory($commodity,$search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Datos obtenidos correctamente',
            'total' => $commodities->get()->count(),
            'data' => $commodities->skip($skip)->take($show)->get()
        ]);
    }
    public function historyCommodity(Request $request, CommodityHistory $commodityHistory)
    {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'El registro se obtuvo correctamente',
            'name_product' => $commodityHistory->commodity->product->product_name,
            'measurement_product' => $commodityHistory->commodity->product->product_unit_measurement,
            'data' => $commodityHistory->only(["commodi_hist_bill",'commodity_provider','product_id',"commodi_hist_guide","commodi_hist_amount","commodi_hist_price_buy","id","commodi_hist_money","commodi_hist_total_buy","commodi_hist_total_buy_usd","commodi_hist_type_change",'commodi_hist_date'])
        ]);
    }
    //ACTUALIZAR HISTORIAL
    public function update(Request $request, CommodityHistory $storeCommodity)
    {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $total = $request->commodi_hist_price_buy * $request->commodi_hist_amount;
        $storeCommodity->update([
            'commodi_hist_date' => $request->commodi_hist_date,
            'commodity_provider' => $request->commodity_provider,
            'commodi_hist_bill' => $request->commodi_hist_bill,
            'commodi_hist_guide' => $request->commodi_hist_guide,
            'commodi_hist_amount' => $request->commodi_hist_amount,
            'commodi_hist_price_buy' => $request->commodi_hist_price_buy,
            'commodi_hist_money' => $request->commodi_hist_money,
            'commodi_hist_total_buy' => $request->commodi_hist_money === 'PEN' ? $total : $total * $storeCommodity->commodi_hist_type_change,
            'commodi_hist_total_buy_usd' => $request->commodi_hist_money === 'PEN' ? $total / $storeCommodity->commodi_hist_type_change : $total,
        ]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Registro actualizado correctamente', 
        ]);
    }
    //VER HISTORIAL
    public function show(Request $request, Commodity $storeCommodity) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'data' => [
                'nameProduct' => $storeCommodity->product->product_name,
                'idCommodity' => $storeCommodity->id,
                'measurementProduct' => $storeCommodity->product->product_unit_measurement,
                'idProduct' => $storeCommodity->product_id
            ]
        ]);
    }
}
