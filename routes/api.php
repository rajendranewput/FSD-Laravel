<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\CorController;
use App\Http\Controllers\LoginController;



Route::get('/user', function (Request $request) {
    echo 'hello';
    //return $request->user();
});

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/farm-fork-spend-data', [PurchasingController::class, 'farmForkSpendData']);
Route::post('/cooked-leakage-data', [PurchasingController::class, 'purchasingCookedLeakageData']);
Route::post('/cor-data', [CorController::class, 'CorData']);
Route::post('/set-costcenter', [LoginController::class, 'setCostCentersToRedis']);
