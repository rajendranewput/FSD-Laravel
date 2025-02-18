<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    $data = DB::connection('mysql_second')->table('wn_costcenter')->get();
    print_r($data);
    die;
});

Route::get('user', function () {
    echo 'hello';
    //return view('welcome');
});