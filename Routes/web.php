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
        Route::post('/terlambat/{access_token}/reject', 'TerlambatController@reject')->name('terlambat.reject');
        Route::post('/terlambat/get-info-tanggal', 'TerlambatController@getHariDanKeterlambatan')->name('terlambat.getInfoTanggal');
    });

    Route::prefix('lupaabsen')->group(function() {
        Route::get('/', 'LupaAbsenController@index')->name('lupa.index');  
        Route::get('/create', 'LupaAbsenController@create')->name('lupa.create'); 
        Route::post('/store', 'LupaAbsenController@store')->name('lupa.store'); 
        Route::get('/edit/{access_token}', 'LupaAbsenController@edit')->name('lupa.edit');
        Route::put('/update/{access_token}', 'LupaAbsenController@update')->name('lupa.update');
        Route::post('/lupa/{access_token}/approve', 'LupaAbsenController@approve')->name('lupa.approve');
        Route::post('/lupa/{access_token}/approve-kepegawaian', 'LupaAbsenController@approvedByKepegawaian')->name('lupa.approve-kepegawaian');
        Route::get('/print/{access_token}', 'LupaAbsenController@print')->name('lupa.print');
        Route::post('/lupa/{access_token}/reject', 'LupaAbsenController@reject')->name('lupa.reject');
        Route::get('/lupa/get-lupa', 'LupaAbsenController@getHariDanKeterlambatan')->name('lupa.getLupa');

    });

});

Route::get('/scan/{access_token}', 'TerlambatController@scan')->name('terlambat.scan');
Route::get('/scan_lupa_absen/{access_token}', 'LupaAbsenController@scan')->name('lupa.scan');
