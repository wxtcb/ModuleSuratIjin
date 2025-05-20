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
        Route::get('/', 'TerlambatController@index')->name('terlambat.index'); 
        Route::get('/create', 'TerlambatController@create')->name('terlambat.create'); 
        Route::post('/store', 'TerlambatController@store')->name('terlambat.store'); 
        Route::get('/edit/{access_token}', 'TerlambatController@edit')->name('terlambat.edit');
        Route::put('/update/{access_token}', 'TerlambatController@update')->name('terlambat.update');
        Route::post('/terlambat/{access_token}/approve', 'TerlambatController@approve')->name('terlambat.approve');
        Route::post('/terlambat/{access_token}/approve-kepegawaian', 'TerlambatController@approvedByKepegawaian')->name('terlambat.approve-kepegawaian');
        Route::get('/print/{access_token}', 'TerlambatController@print')->name('terlambat.print');
    });

    Route::prefix('lupaabsen')->group(function() {
        Route::get('/', 'LupaAbsenController@index'); 
    });
});

Route::get('/scan/{access_token}', 'TerlambatController@scan')->name('terlambat.scan');
