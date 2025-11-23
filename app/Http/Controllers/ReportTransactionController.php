<?php

namespace App\Http\Controllers;

use App\Models\CommodityHistory;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductProgressHistory;
use App\Models\ProductStockInitial;
use App\Models\RawMaterialHistory;
use Illuminate\Http\Request;

class ReportTransactionController extends Controller
{
    public function reportEntry(Request $request)
    {
        $dateInitial = $request->input('dateInitial',today()->toDateString());
        $dateFinaly = $request->input('dateFinaly',today()->toDateString());
        $stockInitialReport = ProductStockInitial::reportEntry($dateInitial,$dateFinaly);
        $rawMaterialReport = RawMaterialHistory::reportEntry($dateInitial,$dateFinaly);
        $productProgressReport = ProductProgressHistory::reportEntry($dateInitial,$dateFinaly);
        $productFinalyReport = ProductFinalyAssembled::reportEntry($dateInitial,$dateFinaly);
        $commodityReport = CommodityHistory::reportEntry($dateInitial,$dateFinaly);
        $finalReport = $stockInitialReport->unionAll($rawMaterialReport)
        ->unionAll($productProgressReport)
        ->unionAll($productFinalyReport)
        ->unionAll($commodityReport)
        ->orderBy('date','DESC')
        ->get();
        dd($finalReport);
    }
    public function reportExit(Request $request)
    {
        // code...
    }
}
