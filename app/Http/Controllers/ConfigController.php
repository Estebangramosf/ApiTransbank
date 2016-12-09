<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;



class ConfigController extends Controller
{

    var $db;
    var $Sendws;
    var $CanjeWs;
    var $OpUrlWs;

    var $WSCLOTPC_Tiempo_OTPC;

    var $WSDL_SEND_ABOUT;
    var $WSDL_SEND_FROM;
    var $WSDL_SEND_HOST;

    var $WSDL_SEND_USER;
    var $WSDL_SEND_PASS;
    var $WSDL_SEND_MAIL;

    var $WSDL_SEND_USER_PS;
    var $WSDL_SEND_PASS_PS;
    var $WSDL_SEND_MAIL_PS;

    var $tasa_conv_CI;


    public function __construct()
    {

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
