<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;



class ConfigController extends Controller
{

    private $db;
    private $Sendws;
    private $CanjeWs;
    private $OpUrlWs;

    private $WSCLOTPC_Tiempo_OTPC;

    private $WSDL_SEND_ABOUT;
    private $WSDL_SEND_FROM;
    private $WSDL_SEND_HOST;

    private $WSDL_SEND_USER;
    private $WSDL_SEND_PASS;
    private $WSDL_SEND_MAIL;

    private $WSDL_SEND_USER_PS;
    private $WSDL_SEND_PASS_PS;
    private $WSDL_SEND_MAIL_PS;

    private $tasa_conv_CI;


    public function __construct()
    {


        SoapWrapper::add(function ($service) {
            $service
              ->name('currency')
              ->wsdl('http://190.196.23.184/clop_otpc_web_prestashop/wscl/wsclotpc_server_ps.php?wsdl')
              ->trace(true);
        });

        $data = [
          'rut'         => '167417906',
          'usuario'     => 'tienda_ps',
          'origen'      => 'Login WEB OTPC',
          'password'    => '0x552A6798E1F1BCF715EFDB1E1DDC0874',
          'idproveedor' => '8'
        ];

// Using the added service
        SoapWrapper::service('currency', function ($service) use ($data) {
            dd($service->call('ConsultaPuntosWSCLOTPC', [$data]));
            var_dump($service->call('Otherfunction'));
        });


        $this->Sendws = 'http://celmediainfo.cl/celmedia_chile/2138_WSDLCLOPCorpbanca/WS_MensajeriaCLOPCorpbanca.php?wsdl';
        $this->CanjeWs = 'http://190.196.23.186/clop/ws/server.php?wsdl';
        $this->OpUrlWs = 'http://200.68.3.74/toolsbkp/WSDLGetOper/wsdl.php?wsdl';
        $this->WSCLOTPC_Tiempo_OTPC = 30;

        $this->WSDL_SEND_ABOUT = 'Clave acceso canje on-line CORPUNTOS.';
        $this->WSDL_SEND_FROM = 'Canje on-line CORPUNTOS';
        $this->WSDL_SEND_HOST = 'mail.misp.cl';

        $this->WSDL_SEND_USER = 'corteingles@canjeonline.cl';
        $this->WSDL_SEND_PASS = 'p$qg5*/';
        $this->WSDL_SEND_MAIL = 'corteingles@canjeonline.cl';

        $this->WSDL_SEND_USER_PS = 'promoservice@canjeonline.cl';
        $this->WSDL_SEND_PASS_PS = '8a4_27j';
        $this->WSDL_SEND_MAIL_PS = 'promoservice@canjeonline.cl';

        $this->tasa_conv_CI = '3';




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
