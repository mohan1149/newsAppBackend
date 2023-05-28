<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api'], function () {
	Route::post('/register', 'App\Http\Controllers\APIController@registerUser');
	Route::post('/login', 'App\Http\Controllers\APIController@login');
	Route::get('/profile/{uid}', 'App\Http\Controllers\APIController@profile');
	Route::post('/news','App\Http\Controllers\APIController@getNewsFromServices');
	Route::post('/add-to-preferences','App\Http\Controllers\APIController@addToPreferences');
	Route::post('/update/account','App\Http\Controllers\APIController@updateAccount');

	

	
});

