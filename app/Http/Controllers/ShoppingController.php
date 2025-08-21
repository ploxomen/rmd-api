<?php

namespace App\Http\Controllers;

use App\Helpers\ProductHelper;
use App\Models\ChangeMoney;
use App\Models\Shopping;
use Illuminate\Http\Request;

class ShoppingController extends Controller
{
    private $urlModule = '/store/shopping/general';

    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $shopping = Shopping::select("shopping.id", "buy_date", "provider_name", "buy_number_invoice", "buy_number_guide", "buy_type", "buy_total", "buy_type_money")->providers()->list($search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Compras obtenidas correctamente',
            'totalProducts' => $shopping->get()->count(),
            'data' => $shopping->skip($skip)->take($show)->get()
        ]);
    }
    public function store(Request $request)
    {
        $priceChange = 0;
        if ($request->buy_type_money === "USD") {
            $money = ChangeMoney::select('change_soles')->where('change_day', date('Y-m-d'))->first();
            if (empty($money)) {
                return response()->json([
                    'error' => true,
                    'message' => 'No se ha establecido un tipo de cambio para el dia ' . date('d/m/Y'),
                ]);
            }
            $priceChange = $money->change_soles;
        }
        $shopping = new Shopping();
        $shopping->fill($request->except('shopping_details'));
        $shopping->save();
        $detailsProducts = ProductHelper::unionOfProducts($request->shopping_details);
        foreach ($detailsProducts as $detail) {
            $priceUnitPEN = $request->buy_type_money === 'USD' ? $priceChange * $detail['shopping_deta_price'] : $detail['shopping_deta_price'];
            $priceUnitUSD = $request->buy_type_money === 'USD' ? $detail['shopping_deta_price'] : $detail['shopping_deta_price'] / $priceChange;
            $shopping->products()->attach($detail['product_id'],[
                'shopping_deta_store' => $detail['type'],
                'shopping_deta_ammount' => $detail['stock'],
                'shopping_deta_price' => $priceUnitPEN,
                'shopping_deta_price_usd' => $priceUnitUSD,
                'shopping_deta_subtotal' => $priceUnitPEN * $detail['stock'],
                'shopping_deta_subtotal_usd' => $priceUnitUSD * $detail['stock']
            ]);
        }
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Compra registrada correctamente'
        ]);
    }
    public function destroy(Shopping $shopping)
    {
        $shopping->products()->get()->each(fn($row) => $row->delete());
        $shopping->delete();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Compra eliminada correctamente'
        ]);
    }
    public function show(Shopping $shopping, Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Producto obtenidos correctamente',
            'compra' => $shopping->only(['buy_date','buy_date_invoice','buy_provider','buy_number_invoice','buy_number_guide','buy_type','buy_type_money']),
            'compra_details' => $shopping->products()
        ]);
    }
}
