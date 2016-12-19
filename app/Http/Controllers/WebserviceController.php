<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use Mockery\CountValidator\Exception;

class WebserviceController extends Controller
{
    private $result;

    public function validateCorpbancaCard(Request $request){
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
               'cardnumber'=>$request->digito,
                //'cardnumber'=>'123432423',
            ];

            // Se usa el nuevo webservice creado
            SoapWrapper::service('currency', function ($service) use ($data) {
                $this->result = $service->call('ConsultaValidaTarjetaCorpbancaWSCLOTPC', [$data]);
            });

            return $this->result->RC;

        } catch(Exception $e) {
            dd($e);
        }
    }

    public function validaTarjetaCorpbanca(){
        return false;
        /*
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


    public function __construct()
    {


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {




    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



}
