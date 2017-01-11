<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use Mockery\CountValidator\Exception;


class SoapController extends Controller
{

   public function __construct()
   {
      $this->ConfigController=new ConfigController();
   }

   public function index(){
      try {
         //Se instancia un nuevo comunicador de webservice con SoapWrapper
         SoapWrapper::add(function ($service) {
            $service
               ->name('currency')
               ->wsdl($this->ConfigController->WebServiceServer)
               ->trace(true);
         });
         //Se definen los parametros que consume el webservice
         $data = [
            'usuario'=>'CONSORCIO_SOAP',
            'password'=>'0xb613a38fa4f3a40e329123ef2c1ef6c4',
            'idproveedor'=>'6',
            'origen'=>'WSCL',
            'detalle'=>''
         ];
         // Se usa el nuevo webservice creado
         SoapWrapper::service('currency', function ($service) use ($data) {
            $this->result = $service->call('RespuestaConsorcioWSCLSOAP', [$data]);
            dd($this->result);
         });
         return response()->json(['RC'=>$this->result->RC,'RD'=>$this->result->RD]);
      } catch(Exception $e) {
         dd($e);
      }


   }
}
