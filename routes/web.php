<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo 'hello';
   //return view('welcome');
});

Route::get('user', function () {
    echo 'hello';
    //return view('welcome');
});