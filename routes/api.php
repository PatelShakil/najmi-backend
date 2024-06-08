<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\AdminRouteProtect;
use App\Http\Middleware\WorkerRouteProtect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => "api"], (function () {
    Route::prefix('/admin')->group(function () {
        Route::post('/login', [AuthController::class, 'adminLogin']);
        Route::post('/createworker', [AuthController::class, 'createWorker']);
    })->withoutMiddleware(WorkerRouteProtect::class);

    Route::prefix('/worker')->group(function () {
        Route::post('/login', [AuthController::class, 'workerLogin']);
    })->withoutMiddleware(AdminRouteProtect::class);

}));
