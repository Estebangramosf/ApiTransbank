<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Artisaninweb\SoapWrapper\Facades\SoapWrapper;

class WebserviceController extends Controller
{

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
