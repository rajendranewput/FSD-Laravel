<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    $data = DB::table('wn_costcenter')->select('team_name')->get();
    print_r($data);
    die;
});

Route::get('user', function () {
    echo 'hello';
    //return view('welcome');
});