<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::prefix('suratijin')->group(function() {
    Route::prefix('terlambat')->group(function() {
        Route::get('/', 'TerlambatController@index'); 
    });

    Route::prefix('lupaabsen')->group(function() {
        Route::get('/', 'LupaAbsenController@index'); 
    });
});
