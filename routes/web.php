<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('index');
});

Route::get('/KakaocertService/{APIName}','KakaocertController@RouteHandelerFunc');
Route::get('/NavercertService/{APIName}','NavercertController@RouteHandelerFunc');
Route::get('/PasscertService/{APIName}','PasscertController@RouteHandelerFunc');