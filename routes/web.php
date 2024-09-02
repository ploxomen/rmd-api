<?php

use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\QuotationsController;
use App\Http\Controllers\UserController;
use App\Models\Products;
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
Route::get('quotation/{quotation}',[QuotationsController::class,'getReportPdf']);
Route::get('modules-roles',[UserController::class,'userModules']);

Route::get('/ordenes/{order}', [OrdersController::class,'getReportPdf']);
