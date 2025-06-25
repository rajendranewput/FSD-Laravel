<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\CorController;
use App\Http\Controllers\CookedLeakageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FarmToForkController;
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
use App\Http\Controllers\WholeFoodChartController;
use App\Http\Controllers\TrendGraphController;
use App\Http\Controllers\TicksController;
use App\Http\Controllers\AnimalProteinsPerMealController;
use App\Http\Controllers\FsdHierarchyController;
use App\Http\controllers\Popup\PurchasingPopup;
use App\Http\Controllers\GlcodeController;
use App\Http\Controllers\Popup\FarmToForkPopup;
use App\Http\Controllers\Popup\CfsPopupController;
use App\Http\Controllers\Popup\LeakagePopupController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogoutController;


//Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('/farm-fork-spend-data', [FarmToForkController::class, 'farmToForkPurchasingData']);
    Route::get('/farm-to-fork-gl-code-graph', [FarmToForkController::class, 'farmToForkGLCodeData']);
    Route::get('/cooked-leakage-data', [CookedLeakageController::class, 'cookedLeakageData']);
    Route::get('/cor-data', [CorController::class, 'CorData']);
    Route::get('/set-costcenter', [LoginController::class, 'setCostCentersToRedis']);
    Route::get('/get-fiscal-year', [FiscalPeriodController::class, 'getFiscalYear']);
    Route::get('/get-fiscal-period', [FiscalPeriodController::class, 'getFiscalPeriod']);
    //Route::get('/download-flavor-first-report', [FlavorFirstController::class, 'downloadFlavorFirstReport']);
    Route::get('/download-flavor-first-report', [FlavorFirstController::class, 'export']);
    Route::get('/beef-meal', [BeefPerMealController::class, 'beefPerMeal']);
    Route::get('/image-share', [ShareImageController::class, 'shareImage']);
    Route::get('/trimming-transportation', [TrimmingTransportationController::class, 'trimmingTransportation']);
    Route::get('/sap-hierarchy', [SapHierarchyController::class, 'sapHierarchy']);
    Route::get('/download-csv-report', [GoogleAnalyticsController::class, 'downloadCsvReport']);
    Route::get('/decreasing-deforestation', [DecreasingDeforestationController::class, 'decreasingDeforestation']);
    Route::get('/emphasize-plant-proteins', [EmphasizePlantController::class, 'emphasizePlant']);
    Route::get('/wellness-plate', [WellnessPlateController::class, 'wellnessPlate']);
    Route::get('/wbi', [WBIController::class, 'wbiData']);
    Route::get('/whole-food-bar-chart', [WholeFoodChartController::class, 'wholeFood']);
    Route::get('/trend-purchasing', [TrendGraphController::class, 'purcahasingTrendGraph']);
    Route::get('/ticks', [TicksController::class, 'ticks']);
    Route::get('/animal-proteins-per-meal', [AnimalProteinsPerMealController::class, 'animalProteinsPerMeal']);
    Route::get('/check-for-popups', [FiscalPeriodController::class, 'checkForPopups']);
    Route::get('/sector-drop-down-data', [FsdHierarchyController::class, 'sectorData']);
    Route::get('/sector-hierarchy-data', [FsdHierarchyController::class, 'sectorHierarchyData']);
    Route::get('/account-hierarchy-data', [FsdHierarchyController::class, 'accountHierarchy']);
    Route::get('/latest-date', [FiscalPeriodController::class, 'getLatestPeriod']);
    Route::get('/cor-total-popup', [PurchasingPopup::class, 'getPopup']);
    Route::get('/get-cor-line-item-popup', [PurchasingPopup::class, 'getLineItem']);
    Route::get('/get-gl-graph', [GlcodeController::class, 'getGlcodeData']);
    Route::get('/get-gl-graph-popup', [GlcodeController::class, 'getGlcodePopup']);
    Route::get('/farm-to-fork-popup', [FarmToForkPopup::class, 'index']);
    Route::get('/radis-clear', [FarmToForkPopup::class, 'radisClear']);
    Route::get('/cfs-popup', [CfsPopupController::class, 'index']);
    Route::get('/leakage-popup', [LeakagePopupController::class, 'index']);
    Route::get('/cfs-noncompliant-popup', [CfsPopupController::class, 'cfsNonCompliantPopup']);
    Route::get('/leakage-noncompliant-popup', [LeakagePopupController::class, 'leakageNonCompliantPopup']);
    Route::get('/get-account-cor-item', [PurchasingPopup::class, 'getAccountCORItem']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('/get-cfs-line-item', [CfsPopupController::class, 'cfsLineItems']);
    Route::get('/get-cfs-line-item-details', [CfsPopupController::class, 'cfsLineItemsDetails']);
    Route::get('/get-leakage-line-item', [LeakagePopupController::class, 'leakageLineItems']);
    Route::get('/get-leakage-line-item-details', [LeakagePopupController::class, 'leakageLineItemsDetails']);
    Route::get('/logout', [LogoutController::class, 'index']);

//});


