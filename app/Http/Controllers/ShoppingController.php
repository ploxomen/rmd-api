<?php

namespace App\Http\Controllers;

use App\Exports\ShoppingExport;
use App\Helpers\ProductHelper;
use App\Models\ChangeMoney;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\ShoppingImported;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ShoppingController extends Controller
{
    private $urlModule = '/store/shopping/general';

    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $shopping = Shopping::select("shopping.id", "buy_date", "provider_name", "buy_total_usd", "buy_number_invoice", "buy_number_guide", "buy_type", "buy_total", "buy_type_money")->providers()->list($search)->orderBy('buy_date', 'DESC');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Compras obtenidas correctamente',
            'total' => $shopping->get()->count(),
            'data' => $shopping->skip($skip)->take($show)->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shopping_details' => 'required|array',
            'buy_provider' => 'required'
        ], [], ['shopping_details' => 'detalle de producto', 'buy_provider' => 'proveedor']);
        $priceChange = 0;
        $money = ChangeMoney::select('change_soles')->where('change_day', $request->buy_date_invoice)->first();
        if (empty($money)) {
            return response()->json([
                'error' => true,
                'message' => 'No se ha establecido un tipo de cambio para el dia ' . $request->buy_date_invoice,
            ], 422);
        }
        $priceChange = $money->change_soles;
        DB::beginTransaction();
        try {
            $shopping = new Shopping();
            $shopping->fill($request->only(['buy_date', 'buy_date_invoice', 'buy_provider', 'buy_number_invoice', 'buy_details', 'buy_number_guide', 'buy_type', 'buy_type_money']));
            $shopping->buy_type_change = $priceChange;
            $shopping->buy_user = $request->user()->id;
            $shopping->created_at = $request->buy_date . " " . date("H:i:s");
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
                    'imported_coefficient' => ($request->imported_expenses_cost + $request->imported_flete_cost + $request->imported_insurance_cost + $request->imported_destination_cost) / $totals['USD']
                ]);
            }
            foreach ($detailsProducts as $detail) {
                $shopping->products()->attach($detail['product_id'], [
                    'shopping_deta_store' => $detail['detail_store'],
                    'created_at' => $shopping->created_at,
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
    public function destroy(Shopping $store_shopping)
    {
        ShoppingDetail::where('shopping_id', $store_shopping->id)->get()->each(fn($row) => $row->delete());
        if ($store_shopping->imported()->exists()) {
            $store_shopping->imported()->delete();
        }
        $store_shopping->delete();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Compra eliminada correctamente'
        ]);
    }
    public function show($store_shopping, Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $shopping = Shopping::select('buy_date', 'shopping.id', 'buy_details', 'buy_date_invoice', 'buy_provider', 'buy_number_invoice', 'buy_number_guide', 'buy_type', 'buy_type_money', 'imported_nro_dam', 'imported_expenses_cost', 'imported_flete_cost', 'imported_insurance_cost', 'imported_destination_cost')->typeImported()->where('shopping.id', $store_shopping)->first();
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'compras obtenidos correctamente',
            'compra' => $shopping,
            'compra_details' => ShoppingDetail::select(['shopping_deta_store AS detail_store', 'shopping_product AS detail_product_id', 'shopping_deta_ammount AS detail_stock', 'shopping_details.id AS detail_id'])->selectRaw("'old' AS detail_type, IF(? = 'PEN', shopping_deta_price, shopping_deta_price_usd) AS datail_price_unit", [$shopping->buy_type_money])->where('shopping_id', $store_shopping)->get()
        ]);
    }
    public function mapDetailsShopping(array $item)
    {
        return [
            $item['product_id'] => [
                'shopping_deta_store' => $item['detail_store'],
                'shopping_deta_ammount' => $item['stock'],
                'shopping_deta_price' => $item['shopping_deta_price'],
                'shopping_deta_price_usd' => $item['shopping_deta_price_usd'],
                'shopping_deta_subtotal' => $item['shopping_deta_subtotal'],
                'shopping_deta_subtotal_usd' => $item['shopping_deta_subtotal_usd']
            ]
        ];
    }
    public function update(Shopping $store_shopping, Request $request)
    {
        DB::beginTransaction();
        $request->validate([
            'shopping_details' => 'required|array',
            'buy_provider' => 'required'
        ], [], ['shopping_details' => 'detalle de producto', 'buy_provider' => 'proveedor']);
        try {
            $priceChange = $store_shopping->buy_type_change ?? 1;
            $store_shopping->fill($request->only(['buy_date', 'buy_date_invoice', 'buy_details', 'buy_provider', 'buy_number_invoice', 'buy_number_guide', 'buy_type', 'buy_type_money']));
            $store_shopping->created_at = $request->buy_date . " " . date("H:i:s");
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
                $detailsProducts[$keyDetail]['created_at'] = $store_shopping->created_at;
                $detailsProducts[$keyDetail]['shopping_deta_subtotal'] = $priceUnitPEN * $detail['stock'];
                $detailsProducts[$keyDetail]['shopping_deta_subtotal_usd'] = $priceUnitUSD * $detail['stock'];
            }
            if ($store_shopping->imported()->exists() && $request->buy_type === "NACIONAL") {
                $store_shopping->imported()->delete();
            }
            if ($request->buy_type === "IMPORTADO") {
                $shoppinImportedValues = [
                    'imported_nro_dam' => $request->imported_nro_dam,
                    'imported_expenses_cost' => $request->imported_expenses_cost,
                    'imported_flete_cost' => $request->imported_flete_cost,
                    'imported_insurance_cost' => $request->imported_insurance_cost,
                    'imported_destination_cost' => $request->imported_destination_cost,
                    'imported_coefficient' => ($request->imported_expenses_cost + $request->imported_flete_cost + $request->imported_insurance_cost + $request->imported_destination_cost) / $totals['USD']
                ];
                $store_shopping->imported()->exists() ? $store_shopping->imported()->update($shoppinImportedValues) : $store_shopping->imported()->create($shoppinImportedValues);
            }
            $sync = collect($detailsProducts)->filter(fn($row) => $row['detail_type'] == 'old')->mapWithKeys([$this, 'mapDetailsShopping']);
            $attach = collect($detailsProducts)->filter(fn($row) => $row['detail_type'] == 'new')->mapWithKeys([$this, 'mapDetailsShopping']);
            $store_shopping->products()->sync($sync);
            $store_shopping->products()->attach($attach);
            $store_shopping->buy_total = $totals['PEN'];
            $store_shopping->buy_total_usd = $totals['USD'];
            $store_shopping->save();
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Compra actualizada correctamente'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Error al actualizar la compra ' . $th->getMessage()
            ], 422);
        }
    }
}
