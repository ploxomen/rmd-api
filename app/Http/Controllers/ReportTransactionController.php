<?php

namespace App\Http\Controllers;

use App\Exports\ShoppingExport;
use App\Exports\TransactionExport;
use App\Models\CommodityHistory;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductProgressHistory;
use App\Models\ProductStockInitial;
use App\Models\RawMaterialHistory;
use App\Models\Shopping;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportTransactionController extends Controller
{
    public function reportEntry(Request $request)
    {
        $dateInitial = $request->input('date_initial',today()->toDateString());
        $dateFinaly = $request->input('date_finaly',today()->toDateString());
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
        return Excel::download(new TransactionExport($finalReport, 'REPORTE DE ENTRADAS'),'reporte_entradas.xlsx');
    }
    public function reportShopping(Request $request)
    {
        $search = $request->has('search') ? $request->search : '';
        $shopping = Shopping::select("buy_date_invoice", "provider_name", "provider_number_document", "buy_number_invoice", "buy_type_change","imported_expenses_cost","buy_total","imported_flete_cost","imported_insurance_cost","imported_destination_cost","user_name","user_last_name")->providers()->typeImported()->users()->list($search)->get();
        return Excel::download(new ShoppingExport($shopping),'compras.xlsx');
    }
    public function reportExit(Request $request)
    {
        $dateInitial = $request->input('date_initial',today()->toDateString());
        $dateFinaly = $request->input('date_finaly',today()->toDateString());
        $rawMaterialReport = RawMaterialHistory::reportExit($dateInitial,$dateFinaly);
        $productProgressReport = ProductProgressHistory::reportExit($dateInitial,$dateFinaly);
        $productFinalyReport = ProductFinalyAssembled::reportExit($dateInitial,$dateFinaly);
        $commodityReport = CommodityHistory::reportExit($dateInitial,$dateFinaly);
        $finalReport = $rawMaterialReport->unionAll($productProgressReport)
        ->unionAll($productFinalyReport)
        ->unionAll($commodityReport)
        ->orderBy('date','DESC')
        ->get();
        return Excel::download(new TransactionExport($finalReport, 'REPORTE DE SALIDAS'),'reporte_salidas.xlsx');
    }
}
