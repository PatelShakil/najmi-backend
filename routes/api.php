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
        Route::get('/getcolors', [ColorController::class, 'getColors']);
        Route::post('/createcategory',[CategoryController::class, 'createCategory']);
        Route::post('/addcolor',[ColorController::class, 'addColor']);
        Route::post('/getbarcodelist',[ManageStockController::class, 'getBarcodeList']);
        Route::get('/getcategories/{id}',[CategoryController::class, 'getCategories']);
    })->withoutMiddleware(WorkerRouteProtect::class);

    Route::prefix('/worker')->group(function () {
        Route::post('/login', [AuthController::class, 'workerLogin']);
    })->withoutMiddleware(AdminRouteProtect::class);

}));
