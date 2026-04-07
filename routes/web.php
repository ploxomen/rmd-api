<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\QuotationsController;
use App\Http\Controllers\ReportTransactionController;
use App\Http\Controllers\StoresController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('customer/export/{typeExport}',[CustomersController::class,'export']);
Route::get('quotation/{quotation}',[QuotationsController::class,'getReportPdf']);
Route::get('entry-exit',[ReportTransactionController::class,'reportExit']);
Route::get('modules-roles',[StoresController::class,'getStoresAndSubStoresSelect']);
Route::post('login',[AuthController::class,'login']);
Route::get('/ordenes/{order}', [OrdersController::class,'getReportPdf']);
Route::post('/user/logout',[AuthController::class,'logout']);
