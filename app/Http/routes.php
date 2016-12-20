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


Route::post('validateCorpbancaCard', 'WebserviceController@validateCorpbancaCard');

Route::get('testAjax',function(){
    return view('testAjax');
});
Route::get('vt', 'WebserviceController@validaTarjetaCorpbanca');




Route::get('ws', 'WebserviceController@index');
Route::get('config', 'ConfigController@index');


//Routes for statistics
Route::get('users','ReportController@users');
Route::get('transactions','ReportController@transactions');
Route::get('payments','ReportController@payments');

//Route::get('webpaynormal', 'WebpayController@index');

Route::post('getResult', 'WebpayController@getResult');

Route::post('end', 'WebpayController@end');

Route::post('getShoppingCart','CelmediaPagoController@getShoppingCart');
Route::post('celmediaPagoInit','CelmediaPagoController@celmediaPagoInit');

//Route for Prestashop WebServices
Route::get('prestashop','PrestashopController@test');

Route::get('/', function () {
    return view('welcome');
});
