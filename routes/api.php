<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChangeMoneyController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\ModulesController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductFinaliesController;
use App\Http\Controllers\ProductProgressController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\QuotationsController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::post('register',[AuthController::class,'register']);
    Route::get('home/info',[AuthController::class,'dataHome']);
    Route::prefix('user')->group(function () {
        Route::delete('logout',[AuthController::class,'logout']);
        Route::put('change-password',[AuthController::class,'changePassword']);
        Route::get('modules-roles',[UserController::class,'userModules']);
        Route::get('change-role/{role}',[AuthController::class,'changeRole']);
    });
    Route::get('role-module/{role}',[RolesController::class,'getModules']);
    Route::get('my-business',[UserController::class,'getBusiness']);
    Route::put('my-business',[UserController::class,'updateBusiness']);
    Route::put('role-module/{role}',[RolesController::class,'updateModules']);
    Route::put('role-module/{role}',[RolesController::class,'updateModules']);
    Route::apiResource('role',RolesController::class);
    Route::get('money/change',[ChangeMoneyController::class,'index']);
    Route::post('money/change',[ChangeMoneyController::class,'store']);
    Route::apiResource('order',OrdersController::class);
    Route::apiResource('store',StoresController::class);
    Route::apiResource('module',ModulesController::class);
    Route::apiResource('raw-material',RawMaterialController::class);
    Route::apiResource('products-finaly',ProductFinaliesController::class)->except(['update']);
    Route::get('module-role/{module}',[ModulesController::class,'getRoles']);
    Route::get('product-store',[StoresController::class,'getStoresAndSubStoresSelect']);
    Route::put('module-role/{module}',[ModulesController::class,'updateRoles']);
    Route::get('type-documents',[UserController::class,'getTypeDocuments']);
    Route::get('my-info',[UserController::class,'getInfoUser']);
    Route::put('my-account',[UserController::class,'updateInfoUser']);
    Route::apiResource('users',UserController::class);
    Route::apiResource('product',ProductsController::class);
    Route::apiResource('product-progress',ProductProgressController::class);
    Route::get("product-extra/raw-process",[ProductsController::class,"getProductRawMaterialAndProcess"]);
    Route::get('product-progress-extra/raw-materials',[ProductProgressController::class,'getRawMaterialActive']);
    Route::get('product-progress-extra/history/{productProgress}',[ProductProgressController::class,'historyProductProgress']);
    Route::get('product-progress/history-list/{productProgress}',[ProductProgressController::class,'listHistory']);
    Route::get('product-progress/history-one/{historyProgress}',[ProductProgressController::class,'oneHistory']);
    Route::put('product-progress/history-one/{historyProgress}',[ProductProgressController::class,'updateHistory']);
    Route::delete('product-progress/history-list/{historyProgress}',[ProductProgressController::class,'deleteHistory']);
    Route::prefix('/product-finaly-extra/history')->group(function () {
        Route::get('/verif/{productFinaly}',[ProductFinaliesController::class,'historiesProductVerif']);
        Route::put('/assembled/{assembled}',[ProductFinaliesController::class,'updateAssembled']);
        Route::put('/imported/{imported}',[ProductFinaliesController::class,'updateImported']);
        Route::get('/assembled/{assembled}',[ProductFinaliesController::class,'getAssembledHistory']);
        Route::get('/imported/{imported}',[ProductFinaliesController::class,'getImportedHistory']);
        Route::delete('/assembled/{assembled}',[ProductFinaliesController::class,'deleteAssembled']);
        Route::delete('/imported/{imported}',[ProductFinaliesController::class,'deleteImported']);
        
    });
    Route::apiResource('quotation',QuotationsController::class);
    Route::get('quotation-extra/products',[QuotationsController::class,'getProductsActive']);
    Route::get('/raw-material/valid-product/{numberBill}',[RawMaterialController::class,'disabledProduct']);

    Route::get('/raw-material/history/{material}',[RawMaterialController::class,'historyRawMaterial']);
    Route::get('/raw-material/history-list/{historyMaterial}',[RawMaterialController::class,'listHistory']);
    Route::get('/raw-material/history-one/{historyMaterial}',[RawMaterialController::class,'oneHistory']);
    Route::put('/raw-material/history-one/{historyMaterial}',[RawMaterialController::class,'updateHistory']);

    Route::delete('/raw-material/history-list/{historyMaterial}',[RawMaterialController::class,'deleteHistory']);
    Route::get('/raw-material/providers/list',[RawMaterialController::class,'gepProvider']);

    

    Route::get('/raw-material/product-add/{product}',[RawMaterialController::class,'addProduct']);

    Route::get('quotation-extra/products-details/{product}',[QuotationsController::class,'getProductDescription']);
    Route::get('quotation-extra/report',[QuotationsController::class,'getDataExport']);
    Route::get('user/password/admin',[UserController::class,'getPasswordAdmin']);
    Route::put('user/password/admin',[UserController::class,'changePasswordAdmin']);

    Route::get('quotation-extra/config',[QuotationsController::class,'getInformationConfig']);
    Route::get('quotation-extra/download/{quotation}',[QuotationsController::class,'getReportPdf']);
    Route::get('order-extra/download/{order}',[OrdersController::class,'getReportPdf']);
    Route::get('order-extra/download-os/{order}',[OrdersController::class,'downloadOS']);
    Route::post('quotation-extra/preview',[QuotationsController::class,'getPreview']);
    Route::get('quotation-extra/customers',[QuotationsController::class,'getCustomerActive']);
    Route::get('order-extra/quotations',[OrdersController::class,'getQuotations']);
    Route::put('order-extra/add-quotation/{quotation}',[OrdersController::class,'addQuotation']);
    Route::put('order-extra/delete-quotation/{quotation}',[OrdersController::class,'deleteQuotation']);
    Route::get('order-extra/reload-quotations',[OrdersController::class,'reloadQuotations']);

    Route::get('quotation-extra/users',[QuotationsController::class,'getUsers']);
    Route::get('quotation-extra/pdf/{quotation}',[QuotationsController::class,'getReportPdf']);
    Route::get('order-extra/pdf/{order}',[OrdersController::class,'getReportPdf']);

    Route::get('quotation/contacts/{customer}',[QuotationsController::class,'getContactsActive']);
    Route::get('product-categorie',[ProductsController::class,'categorie']);
    Route::get('product-export',[ProductsController::class,'exportProductsExcel']);
    Route::get('product-subcategorie/{categorie}',[ProductsController::class,'subcategorie']);
    Route::put('users-reset/{user}',[AuthController::class,'resetPassword']);
    Route::apiResource('customer',CustomersController::class);
    Route::apiResource('provider',ProviderController::class);
    Route::apiResource('categorie',CategoriesController::class);
    Route::delete('categorie-subcategorie/{subcategorie}',[CategoriesController::class,'deleteSubcategorie']);
    Route::delete('customer-contact/{contact}',[CustomersController::class,'deleteContact']);
    Route::delete('provider-contact/{contact}',[ProviderController::class,'deleteContact']);
    Route::get('departaments',[UserController::class,'getDepartamentsUbigeo']);
    Route::get('provinces/{departament}',[UserController::class,'getProvincesUbigeo']);
    Route::get('districts/{province}',[UserController::class,'getDistrictsUbigeo']);
    Route::get('contries',[UserController::class,'getContries']);
});
Route::get('login',[AuthController::class,'login']);