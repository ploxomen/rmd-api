<?php

namespace App\Http\Controllers;

use App\Helpers\ProductHelper;
use App\Models\ChangeMoney;
use App\Models\Shopping;
use App\Models\ShoppingImported;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $money = ChangeMoney::select('change_soles')->where('change_day', $request->buy_date_invoice)->first();
        if (empty($money)) {
            return response()->json([
                'error' => true,
                'message' => 'No se ha establecido un tipo de cambio para el dia ' . $request->buy_date_invoice,
            ]);
        }
        $priceChange = $money->change_soles;
        DB::beginTransaction();
        try {
            $shopping = new Shopping();
            $shopping->fill($request->only(['buy_date', 'buy_date_invoice', 'buy_provider', 'buy_number_invoice', 'buy_number_guide', 'buy_type', 'buy_type_money']));
            $shopping->buy_type_change = $priceChange;
            $shopping->save();
            $detailsProducts = ProductHelper::unionOfProducts($request->shopping_details);
            $totals = [
                'USD' => 0,
                'PEN' => 0,
            ];
            foreach ($detailsProducts as $keyDetail => $detail) {
                $priceUnitPEN = $request->buy_type_money === 'USD' ? $priceChange * $detail['datail_price_unit'] : $detail['datail_price_unit'];
                $priceUnitUSD = $request->buy_type_money === 'USD' ? $detail['datail_price_unit'] : $detail['datail_price_unit'] / $priceChange;
                $totals['PEN'] += $priceUnitPEN * $detail['stock'];
                $totals['USD'] += $priceUnitUSD * $detail['stock'];
                $detailsProducts[$keyDetail]['shopping_deta_price'] = $priceUnitPEN;
                $detailsProducts[$keyDetail]['shopping_deta_price_usd'] = $priceUnitUSD;
                $detailsProducts[$keyDetail]['shopping_deta_subtotal'] = $priceUnitPEN * $detail['stock'];
                $detailsProducts[$keyDetail]['shopping_deta_subtotal_usd'] = $priceUnitUSD * $detail['stock'];
            }
            if ($request->buy_type === "IMPORTADO") {
                ShoppingImported::create([
                    'imported_nro_dam' => $request->imported_nro_dam,
                    'shopping_id' => $shopping->id,
                    'imported_expenses_cost' => $request->imported_expenses_cost,
                    'imported_flete_cost' => $request->imported_flete_cost,
                    'imported_insurance_cost' => $request->imported_insurance_cost,
                    'imported_destination_cost' => $request->imported_destination_cost,
                    'imported_coefficient' => ($request->imported_expenses_cost + $request->imported_flete_cost + $request->imported_insurance_cost + $request->imported_destination_cost) / $totals['PEN']
                ]);
            }
            foreach ($detailsProducts as $detail) {
                $shopping->products()->attach($detail['product_id'], [
                    'shopping_deta_store' => $detail['detail_store'],
                    'shopping_deta_ammount' => $detail['stock'],
                    'shopping_deta_price' => $detail['shopping_deta_price'],
                    'shopping_deta_price_usd' => $detail['shopping_deta_price_usd'],
                    'shopping_deta_subtotal' => $detail['shopping_deta_subtotal'],
                    'shopping_deta_subtotal_usd' => $detail['shopping_deta_subtotal_usd']
                ]);
            }
            $shopping->buy_total = $totals['PEN'];
            $shopping->buy_total_usd = $totals['USD'];
            $shopping->save();
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Compra registrada correctamente'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Error al registrar la compra ' . $th->getMessage()
            ]);
        }
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
            'compra' => $shopping->only(['buy_date', 'buy_date_invoice', 'buy_provider', 'buy_number_invoice', 'buy_number_guide', 'buy_type', 'buy_type_money']),
            'compra_details' => $shopping->products()
        ]);
    }
}
