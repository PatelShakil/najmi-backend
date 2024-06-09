<?php

use App\Http\Controllers\API\ManageStockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdf-generate',[ManageStockController::class,'generatePdfWithBarcodes']);
