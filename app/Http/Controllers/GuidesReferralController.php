<?php

namespace App\Http\Controllers;

use App\Models\ChangeMoney;
use App\Models\Commodity;
use App\Models\GuidesReferral;
use App\Models\GuidesReferralDetails;
use App\Models\ProductFinaly;
use App\Models\RawMaterial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuidesReferralController extends Controller
{
    private $urlModule = "/billing/referral-guide";
    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(), $this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $customer = $request->input('customer', '');
        $skip = ($request->page - 1) * $request->show;
        $guidesReferral = GuidesReferral::withCustomer()->search($search)->orderBy('guides_referral.created_at', 'desc');
        if (!empty($customer)) {
            $guidesReferral->where('guide_customer_id', $customer);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Guias de remisión obtenidas correctamente',
            'total' => $guidesReferral->get()->count(),
            'data' => $guidesReferral->select("guides_referral.id", "guide_issue_number", "guide_issue_date", "guite_total", "customer_name", "guide_address_destination", "guide_justification")->skip($skip)->take($show)->get()
        ]);
    }
    public function store(Request $request)
    {
        if (GuidesReferral::where('guide_issue_number', $request->guide_issue_number)->exists()) {
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'Ya existe una guía de remisión con el mismo número'
            ], 422);
        }
        $money = ChangeMoney::select('change_soles')->where('change_day', $request->guide_issue_date)->first();
        if (empty($money)) {
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'No se ha establecido un tipo de cambio para el dia ' . $request->guide_issue_date,
            ]);
        }
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
            if ($detail['type'] === "PRODUCTO TERMINADO") {
                $productFinaly = ProductFinaly::where(["product_id" => $detail["product_id"], 'product_finaly_status' => 1])->first();
                if (empty($productFinaly)) {
                    return response()->json([
                        'redirect' => null,
                        'error' => true,
                        'message' => 'No existe el producto de PRODUCTO TERMINADO elegido'
                    ], 422);
                }
                if ($productFinaly->product_finaly_stock < $detail['stock']) {
                    return response()->json([
                        'redirect' => null,
                        'error' => true,
                        'message' => "El producto {$productFinaly->products->product_name} de PRODUCTO TERMINADO no cuenta con stock suficiente, actualmente hay {$productFinaly->product_finaly_stock}, se está tratando de ingresar {$detail['stock']}"
                    ], 422);
                }
                continue;
            }
            $commodity = Commodity::where(["product_id" => $detail["product_id"], 'commodi_status' => 1])->first();
            if (empty($commodity)) {
                return response()->json([
                    'redirect' => null,
                    'error' => true,
                    'message' => 'No existe el producto MERCADERIA elegido'
                ], 422);
            }
            if ($commodity->commodi_stock < $detail['stock']) {
                return response()->json([
                    'redirect' => null,
                    'error' => true,
                    'message' => "El producto {$commodity->product->product_name} de PRODUCTO MERCADERIA no cuenta con stock suficiente, actualmente hay {$commodity->commodi_stock}, se está tratando de ingresar {$detail['stock']}"
                ], 422);
            }
        }
        DB::beginTransaction();
        try {
            $guideReferral = new GuidesReferral();
            $guideReferral->fill($request->except(['details']));
            $guideReferral->guide_user_id = $request->user()->id;
            $guideReferral->guide_type_change = $money->change_soles;
            $guideReferral->created_at = $request->guide_issue_date . " " . date("H:i:s");
            $guideReferral->save();
            foreach ($request->details as $product) {
                $guideReferral->product()->attach($product['detail_product_id'], ['guide_product_quantity' => $product['detail_stock'], 'created_at' => $guideReferral->created_at, 'guide_product_type' => $product['detail_store']]);
            }
            DB::commit();
            return response()->json(['redirect' => null, 'error' => false, 'message' => 'Historial generado correctamente']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage(), 'error' => true]);
        }
    }
    public function show(GuidesReferral $guide_referral)
    {
        $data = $guide_referral->only('guide_customer_id', 'id', 'guide_issue_date', 'guide_issue_number', 'guide_address_destination', 'guide_justification');
        return response()->json([
            'redirect' => null,
            'error' => false,
            'data' => $data,
            'details' => $guide_referral->product()->select("guides_referral_details.id AS detail_id", "guide_product_quantity as detail_stock", "guide_product_type as detail_store", "guide_product_id as detail_product_id")->selectRaw("'old' AS detail_type")->get()->makeHidden('pivot')
        ]);
    }
    public function update(Request $request, GuidesReferral $guide_referral)
    {
        $request->validate([
            'details' => 'required|array',
            'guide_issue_number' => 'required'
        ], [], ['details' => 'detalles']);
        if (GuidesReferral::where('guide_issue_number', $request->guide_issue_number)->where('id', '!=', $guide_referral->id)->exists()) {
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'Ya existe una guía de remisión con el mismo número'
            ], 422);
        }
        DB::beginTransaction();
        try {
            $guide_referral->fill($request->except(['details']));
            $guide_referral->created_at = $request->guide_issue_date . " " . date("H:i:s");
            $guide_referral->save();
            $idsUpdates = [];
            $attach = [];
            foreach ($request->details as $product) {
                if ($product['detail_type'] === "old") {
                    GuidesReferralDetails::find($product['detail_id'])->update([
                        'guide_product_quantity' => $product['detail_stock'],
                        'guide_product_id' => $product['detail_product_id'],
                        'guide_product_type' => $product['detail_store'],
                        'created_at' => $guide_referral->created_at
                    ]);
                    $idsUpdates[] = $product['detail_id'];
                    continue;
                }
                $attach[$product['detail_product_id']] = ['guide_product_quantity' => $product['detail_stock'], 'guide_product_type' => $product['detail_store']];
            }
            GuidesReferralDetails::whereNotIn('id', $idsUpdates)->where('guide_referral_id', $guide_referral->id)->get()->each(function ($detail) {
                $detail->delete();
            });
            $guide_referral->product()->attach($attach);
            DB::commit();
            return response()->json([
                'redirect' => null,
                'error' => false,
                'message' => 'Guia de remisión actualizada correctamente'
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
    public function destroy(GuidesReferral $guide_referral)
    {
        DB::beginTransaction();
        try {
            GuidesReferralDetails::where('guide_referral_id', $guide_referral->id)->get()->each(function ($detail) {
                $detail->delete();
            });
            $guide_referral->delete();
            DB::commit();
            return response()->json([
                'error' => false,
                'message' => 'La guía de remisión fue eliminada correctamente'
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
