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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/weather_with_dates/{city_id}/from/{from}/to/{to}', 'App\Http\Controllers\WeatherController@getWeatherDatesByCityId');
Route::get('/weather/{city_id}', 'App\Http\Controllers\WeatherController@getWeatherByCityId');
Route::post('/city', 'App\Http\Controllers\WeatherController@createCity');
Route::delete('/city', 'App\Http\Controllers\WeatherController@deleteCity');
