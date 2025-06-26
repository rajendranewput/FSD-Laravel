<?php

use App\Http\Controllers\CafeManager\DayPartController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    $data = DB::table('wn_costcenter')->select('team_name')->get();
    // print_r($data);
    die;
});

Route::get('user', function () {
    echo 'hello';
    return view('welcome');
});

Route::prefix('cafemanager')->group(function () {
    Route::prefix('cafes/{cafeId}')->group(function () {
        Route::get('/day-parts', [DayPartController::class, 'index']);
        Route::post('/day-part', [DayPartController::class, 'store']);
        Route::get('/day-part/edit', [DayPartController::class, 'edit']);
        Route::delete('/day-part/delete', [DayPartController::class, 'destroy']);
    });
});
