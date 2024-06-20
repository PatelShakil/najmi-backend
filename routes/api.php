<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ColorController;
use App\Http\Controllers\API\ManageStockController;
use App\Http\Middleware\AdminRouteProtect;
use App\Http\Middleware\WorkerRouteProtect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => "api"], (function () {
    Route::prefix('/admin')->group(function () {
        Route::post('/login', [AuthController::class, 'adminLogin']);
        Route::post('/createworker', [AuthController::class, 'createWorker']);
        Route::post('/addbrand',[BrandController::class, 'addBrand']);
        Route::get('/getbrands', [BrandController::class, 'getBrands']);
        Route::get('/getbrandsdetails', [BrandController::class, 'getBrandsPage']);
        Route::get('/getcolors', [ColorController::class, 'getColors']);
        Route::post('/createcategory',[CategoryController::class, 'createCategory']);
        Route::post('/addcolor',[ColorController::class, 'addColor']);
        Route::post('/getbarcodelist',[ManageStockController::class, 'getBarcodeList']);
        Route::get('/getcategories/{id}',[CategoryController::class, 'getCategoriesById']);
        Route::get('/getcategories',[CategoryController::class,'getCategories']);
        Route::get('/getworkers',[AuthController::class, 'getWorkers']);
        Route::get('/getallcolors',[ColorController::class,'getAllColors']);
        Route::post('/updatebrand/{id}',[BrandController::class,'updateBrand']);
        Route::get('/deletebrand/{id}',[BrandController::class,'deleteBrand']);
        Route::post('/updatecolor/{id}',[ColorController::class,'updateColor']);
        Route::post('/updatecategory/{id}',[CategoryController::class,'updateCategory']);
        Route::post('/updateworker/{id}',[AuthController::class,'updateWorker']);
        Route::get('/returnproduct/{br}',[ManageStockController::class,'returnProduct']);
        Route::get('/getadmins',[AuthController::class,'getAdmins']);
    })->withoutMiddleware(WorkerRouteProtect::class);

    Route::prefix('/worker')->group(function () {
        Route::post('/login', [AuthController::class, 'workerLogin']);
        Route::get('/getstock/{br}',[ManageStockController::class, 'getStockDetails']);
        Route::get('/salestock/{br}',[ManageStockController::class,'saleStock']);
        Route::get('/getstocklist/{c_id}',[ManageStockController::class,'getStockList']);
    })->withoutMiddleware(AdminRouteProtect::class);

}));
