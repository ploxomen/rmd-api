<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\ModulesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\QuotationsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('register',[AuthController::class,'register']);
    Route::prefix('user')->group(function () {
        Route::get('modules-roles',[UserController::class,'userModules']);
    });
    Route::get('role-module/{role}',[RolesController::class,'getModules']);
    Route::put('role-module/{role}',[RolesController::class,'updateModules']);
    Route::put('role-module/{role}',[RolesController::class,'updateModules']);
    Route::apiResource('role',RolesController::class);
    Route::apiResource('module',ModulesController::class);
    Route::get('module-role/{module}',[ModulesController::class,'getRoles']);
    Route::put('module-role/{module}',[ModulesController::class,'updateRoles']);
    Route::get('type-documents',[UserController::class,'getTypeDocuments']);
    Route::apiResource('users',UserController::class);
    Route::apiResource('product',ProductsController::class);
    Route::apiResource('quotation',QuotationsController::class);
    Route::get('quotation-extra/products',[QuotationsController::class,'getProductsActive']);
    Route::get('quotation-extra/customers',[QuotationsController::class,'getCustomerActive']);
    Route::get('quotation-extra/users',[QuotationsController::class,'getUsers']);
    Route::get('quotation-extra/pdf/{quotation}',[QuotationsController::class,'getReportPdf']);
    Route::get('quotation/contacts/{customer}',[QuotationsController::class,'getContactsActive']);
    Route::get('product-categorie',[ProductsController::class,'categorie']);
    Route::get('product-export',[ProductsController::class,'exportProductsExcel']);
    Route::get('product-subcategorie/{categorie}',[ProductsController::class,'subcategorie']);
    Route::put('users-reset/{user}',[AuthController::class,'resetPassword']);
    Route::apiResource('customer',CustomersController::class);
    Route::apiResource('categorie',CategoriesController::class);
    Route::delete('categorie-subcategorie/{subcategorie}',[CategoriesController::class,'deleteSubcategorie']);
    Route::delete('customer-contact/{contact}',[CustomersController::class,'deleteContact']);
    Route::get('departaments',[UserController::class,'getDepartamentsUbigeo']);
    Route::get('provinces/{departament}',[UserController::class,'getProvincesUbigeo']);
    Route::get('districts/{province}',[UserController::class,'getDistrictsUbigeo']);
    Route::get('contries',[UserController::class,'getContries']);
});

Route::get('login',[AuthController::class,'login']);
