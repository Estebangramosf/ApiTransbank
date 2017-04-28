<?php
Route::get('test', function(){

   $fecha = date("Y-m-d");



   //$yesterday = date("Y-m-d 00:00:00", strtotime("$fecha -3 day"));
   //$yesterday1 =date("Y-m-d 00:00:00", strtotime("$fecha this week wednesday"));

   $today = date("Y-m-d 00:00:00", strtotime("$fecha this day"));
   $monday =date("Y-m-d 00:00:00", strtotime("$fecha this week monday"));
   $thursday =date("Y-m-d 00:00:00", strtotime("$fecha this week thursday"));



   echo '+------------------------------------------+<br>';
   echo $today.' -> HOY <br>';
   echo '+------------------------------------------+<br>';
   echo $monday.' -> LUNES <br>';
   echo '+------------------------------------------+<br>';
   echo $thursday.' -> JUEVES <br>';
   echo '+------------------------------------------+<br>';

   if ($today === $monday) {
      echo 'Hoy es Lunes, Se debe realizar el desbloqueo<br><br>';
      echo 'El rango es entre: <br>';
      echo '+------------------------------------------+<br>';
      echo $today.'<br>';
      echo '+------------------------------------------+<br>';
      echo date("Y-m-d 00:00:00", strtotime("$fecha -4 day")).' - ';
      echo date("Y-m-d 23:59:59", strtotime("$fecha -1 day")).'<br>';

      echo '+------------------------------------------+<br>';
   }else if ($today === $thursday) {
      echo 'Hoy es Jueves, Se debe realizar el desbloqueo<br><br>';
      echo 'El rango es entre: <br>';
      echo '+------------------------------------------+<br>';
      echo $today.'<br>';
      echo '+------------------------------------------+<br>';
      echo date("Y-m-d 00:00:00", strtotime("$fecha -3 day")).' - ';
      echo date("Y-m-d 23:59:59", strtotime("$fecha -1 day")).'<br>';

      echo '+------------------------------------------+<br>';
   }else{
      echo 'No ocurre nada<br>';
      echo '+------------------------------------------+<br>';
   }

});



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
/*
Route::any('{all}', function(){
    return 'It Works';
})->where('all', '.*');
*/

//Route::get('/test', 'SoapController@index');

Route::post('validateCorpbancaCard', 'WebserviceController@validateCorpbancaCard');

Route::get('testAjax', function () {
   return view('testAjax');
});
Route::get('vt', 'WebserviceController@validaTarjetaCorpbanca');


Route::get('ws', 'WebserviceController@index');
Route::get('config', 'ConfigController@index');


//Routes for statistics
Route::get('users', 'ReportController@users');
Route::get('transactions', 'ReportController@transactions');
Route::get('payments', 'ReportController@payments');

//Route::get('webpaynormal', 'WebpayController@index');

Route::post('getResult', 'WebpayController@getResult');

Route::post('end', 'WebpayController@end');

Route::post('getShoppingCart', 'CelmediaPagoController@getShoppingCart');
Route::post('celmediaPagoInit', 'CelmediaPagoController@celmediaPagoInit');

//Route for Prestashop WebServices
Route::get('prestashopGetProductsDetails', 'PrestashopController@prestashopGetProductsDetails');

Route::get('/', function () {
   return view('welcome');
});
