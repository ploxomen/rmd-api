<?php

namespace App\Observers;

use App\Models\GuidesReferral;
use App\Models\GuidesReferralDetails;
use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductFinalyImported;
use App\Models\RawMaterial;
use App\Models\RawMaterialHistory;

class GuidesReferralDetailsObserver
{
    public function newDetail(GuidesReferralDetails $guideDetail){
        if($guideDetail->guide_product_type == "PRODUCTO TERMINADO"){
            $productProgres = ProductFinaly::where(['product_id' => $guideDetail->guide_product_id,'product_finaly_status' => 1])->first();
            if(!empty($productProgres)){
                $insert = [
                    'product_finaly_id' => $productProgres->id,
                    'guide_refer_id' => $guideDetail->id,
                    'product_finaly_amount' => $guideDetail->guide_product_quantity * -1,
                    'product_finaly_user' => auth()->user()->id
                ];
                if($productProgres->imported()->exists()){
                    $productProgres->imported()->create($insert);
                }else if($productProgres->assembled()->exists()){
                    $productProgres->assembled()->create($insert);
                }
            }
        }else if($guideDetail->guide_product_type == "MATERIA PRIMA"){
            $rawMaterial = RawMaterial::where(['product_id' => $guideDetail->guide_product_id, 'raw_material_status' => 1])->first();
            if(!empty($rawMaterial)){
                $rawMaterial->history()->create([
                    'product_id' => $guideDetail->guide_product_id,
                    'material_hist_amount' => $guideDetail->guide_product_quantity * -1,
                    'guide_refer_id' => $guideDetail->id,
                    'material_user' => auth()->user()->id,
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
                    $value->material_hist_amount = $guideDetail->guide_product_quantity * -1;
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
    }
}
