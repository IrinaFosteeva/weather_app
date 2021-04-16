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



Route::get('/update', 'App\Http\Controllers\WeatherController@updateWeather');


Route::get('/get_weather_with_dates/{city_id}/from/{from}/to/{to}', 'App\Http\Controllers\WeatherController@getWeatherDatesByCityId');
Route::get('/get_weather/{city_id}', 'App\Http\Controllers\WeatherController@getWeatherByCityId');
Route::get('/create_city/{city_name}', 'App\Http\Controllers\WeatherController@createCity');
Route::get('/delete_city/{city_id}', 'App\Http\Controllers\WeatherController@deleteCity');
