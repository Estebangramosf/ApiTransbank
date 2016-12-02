<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('ws', 'WebserviceController@index');
Route::get('config', 'ConfigController@index');



Route::get('webpaynormal', 'WebpayController@index');

Route::post('getResult', 'WebpayController@getResult');

Route::post('end', 'WebpayController@end');

Route::post('getShoppingCart','WebpayController@getShoppingCart');



Route::get('/', function () {
    return view('welcome');
});
