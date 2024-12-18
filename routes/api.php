<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\CorController;
use App\Http\Controllers\CookedLeakageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FarmtoforkController;
use App\Http\Controllers\FiscalPeriodController;
use App\Http\Controllers\FlavorFirstController;
use App\Http\Controllers\BeefPerMealController;
use App\Http\Controllers\ShareImageController;
use App\Http\Controllers\TrimmingTransportationController;
use App\Http\Controllers\SapHierarchyController;
use App\Http\Controllers\GoogleAnalyticsController;
use App\Http\Controllers\DecreasingDeforestationController;
use App\Http\Controllers\EmphasizePlantController;
use App\Http\Controllers\WellnessPlateController;
use App\Http\Controllers\WBIController;


Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/farm-fork-spend-data', [FarmtoforkController::class, 'farmForkSpendData']);
Route::post('/farm-to-fork-gl-code-graph', [FarmtoforkController::class, 'farmToForkGLCodeData']);
Route::post('/cooked-leakage-data', [CookedLeakageController::class, 'cookedLeakageData']);
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
Route::get('/download-csv-report', [GoogleAnalyticsController::class, 'downloadCsvReport']);
Route::post('/decreasing-deforestation', [DecreasingDeforestationController::class, 'decreasingDeforestation']);
Route::post('/emphasize-plant-proteins', [EmphasizePlantController::class, 'emphasizePlant']);
Route::post('/wellness-plate', [WellnessPlateController::class, 'wellnessPlate']);
Route::post('/wbi', [WBIController::class, 'wbiData']);
