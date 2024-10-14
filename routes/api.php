<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\CorController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FarmtoforkController;
use App\Http\Controllers\FiscalPeriodController;
use App\Http\Controllers\FlavorFirstController;
use App\Http\Controllers\BeefPerMealController;
use App\Http\Controllers\ShareImageController;
use App\Http\Controllers\TrimmingTransportationController;
use App\Http\Controllers\SapHierarchyController;


Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/farm-fork-spend-data', [PurchasingController::class, 'farmForkSpendData']);
Route::post('/cooked-leakage-data', [PurchasingController::class, 'purchasingCookedLeakageData']);
Route::post('/farm-to-fork-gl-code-graph', [FarmtoforkController::class, 'farmToForkGLCodeData']);
Route::post('/cor-data', [CorController::class, 'CorData']);
Route::post('/set-costcenter', [LoginController::class, 'setCostCentersToRedis']);
Route::get('/get-fiscal-year', [FiscalPeriodController::class, 'getFiscalYear']);
Route::post('/get-fiscal-period', [FiscalPeriodController::class, 'getFiscalPeriod']);
//Route::post('/download-flavor-first-report', [FlavorFirstController::class, 'downloadFlavorFirstReport']);
Route::post('/download-flavor-first-report', [FlavorFirstController::class, 'export']);
Route::post('/beef-meal', [BeefPerMealController::class, 'beefPerMeal']);
Route::post('/image-share', [ShareImageController::class, 'shareImage']);
Route::post('/trimming-transportation', [TrimmingTransportationController::class, 'trimmingTransportation']);
Route::post('/sap-hierarchy', [SapHierarchyController::class, 'sapHierarchy']);


