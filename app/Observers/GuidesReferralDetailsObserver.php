<?php

namespace App\Observers;

use App\Models\Commodity;
use App\Models\CommodityHistory;
use App\Models\GuidesReferral;
use App\Models\GuidesReferralDetails;
use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductFinalyImported;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;
use Illuminate\Support\Facades\Log;

class GuidesReferralDetailsObserver
{
    public function newDetail(GuidesReferralDetails $guideDetail){
        if($guideDetail->guide_product_type == "PRODUCTO TERMINADO"){
            $productFinaly = ProductFinaly::where(['product_id' => $guideDetail->guide_product_id,'product_finaly_status' => 1])->first();
            if(!empty($productFinaly)){
                $insert = [
                    'product_finaly_created' => date('Y-m-d'),
                    'product_finaly_id' => $productFinaly->id,
                    'prod_fina_type_change' => $guideDetail->guideReferral->guide_type_change,
                    'guide_refer_id' => $guideDetail->id,
                    'type_motion' => $guideDetail->guideReferral->guide_type_motion,
                    'product_finaly_amount' => $guideDetail->guide_product_quantity * -1,
                    'created_at' => $guideDetail->created_at,
                    'product_finaly_user' => auth()->user()->id
                ];
                $assembled = new ProductFinalyAssembled($insert);
                $assembled->saveQuietly();
            }
        }else if($guideDetail->guide_product_type == "MATERIA PRIMA"){
            $rawMaterial = RawMaterial::where(['product_id' => $guideDetail->guide_product_id, 'raw_material_status' => 1])->first();
            if(!empty($rawMaterial)){
                $guides = GuidesReferral::find($guideDetail->guide_referral_id);
                $amount = $guideDetail->guide_product_quantity * -1;
                $priceUnit = $rawMaterial->raw_hist_prom_weig;
                $subtotal = $amount * $priceUnit;
                $rawMaterial->history()->create([
                    'product_id' => $guideDetail->guide_product_id,
                    'material_hist_total_type_change' => $guides->guide_type_change,
                    'material_hist_money' => 'PEN',
                    'type_motion' => $guideDetail->guideReferral->guide_type_motion,
                    'material_hist_date' => $guides->guide_issue_date,
                    'material_hist_amount' => $amount,
                    'material_hist_total_buy_pen' => $subtotal,
                    'material_hist_price_buy' => $priceUnit,
                    'created_at' => $guideDetail->created_at,
                    'raw_hist_type' => 'SALIDA',
                    'guide_refer_id' => $guideDetail->id,
                    'material_user' => auth()->user()->id,
                ]);
            }
        }else if($guideDetail->guide_product_type == "MERCADERIA"){
            $commodity = Commodity::where(['product_id' => $guideDetail->guide_product_id, 'commodi_status' => 1])->first();
            if(!empty($commodity)){
                $guides = GuidesReferral::find($guideDetail->guide_referral_id);
                $amount = $guideDetail->guide_product_quantity * -1;
                $priceUnit = $commodity->commodi_prom_weig;
                $subtotal = $amount * $priceUnit;
                $commodity->history()->create([
                    'product_id' => $guideDetail->guide_product_id,
                    'commodi_hist_date' => $guides->guide_issue_date,
                    'commodi_hist_bill' => "",
                    'type_motion' => $guideDetail->guideReferral->guide_type_motion,
                    'commodi_hist_guide' => $guides->guide_issue_number,
                    'commodi_hist_type_change' => $guides->guide_type_change,
                    'commodi_hist_money' => 'PEN',
                    'commodi_hist_type' => 'SALIDA',
                    'created_at' => $guideDetail->created_at,
                    'commodi_hist_amount' => $amount,
                    'commodi_hist_price_buy' => $priceUnit,
                    'commodi_hist_total_buy' => $subtotal,
                    'commodi_hist_total_buy_usd' => $subtotal * $guides->guide_type_change,
                    'guide_refer_id' => $guideDetail->id,
                    'commodi_hist_user' => auth()->user()->id,
                ]);
            }
        }
    }
    public function deleteDetail(GuidesReferralDetails $guideDetail){
        $guides = GuidesReferral::find($guideDetail->guide_referral_id);
        $totalStock = GuidesReferralDetails::where('guide_referral_id',$guideDetail->guide_referral_id)->sum('guide_product_quantity');
        $guides->guite_total = $totalStock;
        $guides->save();
    }
    public function created(GuidesReferralDetails $guideDetail)
    {
        $this->newDetail($guideDetail);
    }
    public function updated(GuidesReferralDetails $guideDetail){
        if($guideDetail->wasChanged('guide_product_type') || $guideDetail->wasChanged('guide_product_id')){
            $oldType = $guideDetail->wasChanged('guide_product_type') ? $guideDetail->getOriginal('guide_product_type') : $guideDetail->guide_product_type;
            if($oldType === "PRODUCTO TERMINADO"){
                ProductFinalyImported::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
                    $history->delete();
                });
                ProductFinalyAssembled::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
                    $history->delete();
                });
            }else if($oldType === 'MATERIA PRIMA'){
                RawMaterialHistory::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
                    $history->delete();
                });
            }else if($oldType === 'MERCADERIA'){
                CommodityHistory::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
                    $history->delete();
                });
            }
            $this->newDetail($guideDetail);
        }
        if(!$guideDetail->wasChanged('guide_product_type') && !$guideDetail->wasChanged('guide_product_id') && $guideDetail->wasChanged('guide_product_quantity')){
            if($guideDetail->product_finaly_type == "PRODUCTO TERMINADO"){
                ProductFinalyImported::where(['guide_refer_id' => $guideDetail->id])->get()->each(function ($value) use ($guideDetail){
                    $value->product_finaly_amount = $guideDetail->guide_product_quantity * -1;
                    $value->save();
                });
                ProductFinalyAssembled::where(['guide_refer_id' => $guideDetail->id])->get()->each(function ($value) use ($guideDetail){
                    $value->product_finaly_amount = $guideDetail->guide_product_quantity * -1;
                    $value->save();
                });
            }else if($guideDetail->product_finaly_type == "MATERIA PRIMA"){
                RawMaterialHistory::where(['guide_refer_id' => $guideDetail->id])->get()->each(function ($value) use ($guideDetail){
                    $amount = $guideDetail->guide_product_quantity * -1;
                    $value->material_hist_amount = $amount;
                    $value->material_hist_total_buy_pen = $amount * $value->material_hist_price_buy;
                    $value->save();
                });
            }else if($guideDetail->product_finaly_type == "MERCADERIA"){
                CommodityHistory::where(['guide_refer_id' => $guideDetail->id])->get()->each(function ($value) use ($guideDetail){
                    $amount = $guideDetail->guide_product_quantity * -1;
                    $value->commodi_hist_amount = $amount;
                    $value->commodi_hist_total_buy = $amount * $value->commodi_hist_price_buy;
                    $value->save();
                });
            }
        }
    }
    public function saved(GuidesReferralDetails $guideDetail){
        $guides = GuidesReferral::find($guideDetail->guide_referral_id);
        $totalStock = GuidesReferralDetails::where('guide_referral_id',$guideDetail->guide_referral_id)->sum('guide_product_quantity');
        $guides->guite_total = $totalStock;
        $guides->save();
    }
    public function deleted(GuidesReferralDetails $guideDetail){
        $this->deleteDetail($guideDetail);
    }
    public function deleting(GuidesReferralDetails $guideDetail){
        if(!isset($guideDetail->id) || empty($guideDetail->id)){
            return null;
        }
        ProductFinalyImported::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
            $history->delete();
        });
        ProductFinalyAssembled::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
            $history->delete();
        });
        RawMaterialHistory::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
            $history->delete();
        });
        CommodityHistory::where(['guide_refer_id' => $guideDetail->id])->get()->each(function($history){
            $history->delete();
        });
    }
}
