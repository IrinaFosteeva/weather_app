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
    return view('welcome');
});

Route::get('/weather_with_dates/{city_id}/from/{from}/to/{to}', 'App\Http\Controllers\WeatherController@getWeatherDatesByCityId');
Route::get('/weather/{city_id}', 'App\Http\Controllers\WeatherController@getWeatherByCityId');
Route::post('/city', 'App\Http\Controllers\WeatherController@createCity');
Route::delete('/city', 'App\Http\Controllers\WeatherController@deleteCity');
