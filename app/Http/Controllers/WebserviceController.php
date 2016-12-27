<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use Mockery\CountValidator\Exception;

class WebserviceController extends Controller
{
    private $result;
    private $ConfigController;
    public function __construct()
    {
        $this->ConfigController = new ConfigController();
    }

    public function validateCorpbancaCard(Request $request){
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
               'usuario'=>$this->ConfigController->WebServiceUserCelPago,
               'password'=>$this->ConfigController->WebServicePasswordCelPago,
               'cardnumber'=>$request->digito,//172161
            ];
            // Se usa el nuevo webservice creado
            SoapWrapper::service('currency', function ($service) use ($data) {
                $this->result = $service->call('ConsultaValidaTarjetaCorpbancaWSCLOTPC', [$data]);
            });
            return response()->json(['RC'=>$this->result->RC,'RD'=>$this->result->RD]);
        } catch(Exception $e) {
            dd($e);
        }
    }

    public function validaTarjetaCorpbanca(){
        return false;
        /*
        DEPRECATED BECAUSE THAT METHOD IS USED FOR TESTS
        try {

            //Se instancia un nuevo comunicador de webservice con SoapWrapper
            SoapWrapper::add(function ($service) {
                $service
                   ->name('currency')
                   ->wsdl('http://190.196.23.184/clop_otpc_web_prestashop_desa/wscl/wsclotpc_server_ps.php?wsdl')
                   ->trace(true);
            });

            //Se definen los parametros que consume el webservice
            $data = [
               'usuario'=>'celmediapago',
               'password'=>'0x552A6798E1F1BCF715EFDB1E1DDC0874',
               'cardnumber'=>'172280',
               //'cardnumber'=>'123432423',
            ];

            // Se usa el nuevo webservice creado
            SoapWrapper::service('currency', function ($service) use ($data) {
                $this->result = $service->call('ConsultaValidaTarjetaCorpbancaWSCLOTPC', [$data]);

                //dd($result);
                //return true;
            });

            return $this->result->RC;

        } catch(Exception $e) {
            dd($e);
        }
        */
    }
}
