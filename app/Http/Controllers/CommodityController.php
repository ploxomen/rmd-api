<?php

namespace App\Http\Controllers;

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
        $commodity = Commodity::select("commodi_stock","commodi_money","commodi_price_buy","commodities.id","product_code","product_name","product_unit_measurement","product_label_2")->products()->enabled()->where('product_name','like','%'.$search.'%');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos de almacenamiento mercadería obtenidos correctamente',
            'total' => $commodity->get()->count(),
            'data' => $commodity->skip($skip)->take($show)->get()
        ]);
    }
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
    }
    public function validateDuplicateProduct($numberBill,$idProduct) {
        $materialDuplicate = CommodityHistory::where(['product_id' => $idProduct, 'commodi_hist_bill' => $numberBill])->first();
        return !empty($materialDuplicate);
    }
}
