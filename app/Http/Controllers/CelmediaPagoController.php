<?php

namespace App\Http\Controllers;

use Illuminate\Console\Parser;
use Illuminate\Http\Request;

use App\Http\Requests;
use SimpleXMLElement;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use App\User;

class CelmediaPagoController extends Controller
{

    private $WebserviceController;
    private $WebpayController;
    private $ClientData;

    public function __construct()
    {
      $this->WebserviceController = new WebserviceController();
      $this->WebpayController = new WebpayController();
    }

  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    public function getShoppingCart(Request $request){
      try{
        //Check if Rut Exist
        //
        if ($_SERVER['REMOTE_ADDR'] == '172.16.4.100'){
          return view('webpay.celmediaPago',['request'=>$request->all()]);
        }


        if($request->TBK_RUT && isset($request->TBK_RUT)){

          //Obtain data from client
          $this->ClientData = $this->ConsultaPuntosWSCLOTPC($request->TBK_RUT);




          /*
          if($request->TBK_MONTO && $request->TBK_MONTO ){
            dd(1);
          }
          */

          //Return data at view to send form

        }
      }catch(Exception $e){

      }
    }

    public function ConsultaPuntosWSCLOTPC($rut){
      try {
        SoapWrapper::add(function ($service) {
          $service
            ->name('currency')
            ->wsdl('http://190.196.23.184/clop_otpc_web_prestashop/wscl/wsclotpc_server_ps.php?wsdl')
            ->trace(true);
        });

        $data = [
          'rut'         => $rut,
          'usuario'     => 'tienda_ps',
          'origen'      => 'Login WEB OTPC',
          'password'    => '0x552A6798E1F1BCF715EFDB1E1DDC0874',
          'idproveedor' => '8'
        ];


        // Using the added service
        SoapWrapper::service('currency', function ($service) use ($data) {
          $ClientData = $service->call('ConsultaPuntosWSCLOTPC', [$data]);
          
          $user = User::where('rut', (int)$ClientData->rut)->get();
          if(isset($user[0])){
            $user = json_decode(json_encode($user[0]));
          }
          if(isset($user->rut)){

            $user=User::findOrFail($user->id);
            $user->pts = $ClientData->SaldoPuntos;
            $user->save();
            return true;
          }else{
            User::create([
              'name'=>$ClientData->Nombre,
              'rut'=>$ClientData->rut,
              'pts'=>$ClientData->SaldoPuntos
            ])->save();
            return true;
          }

          //dd($service->call('ConsultaPuntosWSCLOTPC', [$data]));
          //var_dump($service->call('Otherfunction'));
        });

      } catch(Exception $e) {

      }
    }



}
