<?php

namespace App\Http\Controllers;

use Illuminate\Console\Parser;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
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

        //Se crea el objeto $userResult como resultado de la verificación del usuario
        $userResult = $this->verifyRUTExistanceAndGetUser($request);

        $request->TBK_MONTO=str_replace(".","",$request->TBK_MONTO);
        if( $userResult->pts > $request->TBK_MONTO ){
          //En esta parte se debiese hacer el canje normal sin problemas
          echo "Los puntos le alcanzan . <br>".
            'Pts Usuario : '.($userResult->pts .' | Costo : '. $request->TBK_MONTO).'<br>'.
            'Total restante después del canje : '.($userResult->pts - $request->TBK_MONTO);

        }else{
          //En esta parte se debiese guardar los datos y generar el pago por transbank para el usuario
          //Tomando la diferencia de los puntos y generar el cobro en base a los puntos

          echo "Los puntos no le alcanzan . <br>".
            'Pts Usuario : '.($userResult->pts .' | Costo : '. $request->TBK_MONTO).'<br>'.
            'Total restante después del canje : '.($userResult->pts - $request->TBK_MONTO).'<br>'.
            'Se debe generar el pago mediante transbank por los puntos restantes.';
        }


      }catch(Exception $e){

      }



    }

    //Funcion que verifica si el rut del usuario existe mediante request y devuelve al usuario como objeto de la clase
    //En caso que el usuario no exista, redirecciona al usuario
    public function verifyRUTExistanceAndGetUser(Request $request){
      try{
        if($request->TBK_RUT && isset($request->TBK_RUT)){
          //Obtiene la informacion del cliente, si no existe lo registra, si existe, actualiza los puntos desde WS
          $this->ConsultaPuntosWSCLOTPC($request->TBK_RUT);
          //Se busca al usuario en la base de datos local
          $user = User::where('rut', (int)$request->TBK_RUT)->get();
          //se verifica si existe y lo guarda en una veriable
          if(isset($user[0])){
            return json_decode(json_encode($user[0]));
          }else{
            return Redirect::to('http://ecorpbancadesa.celmedia.cl/');
          }
        }
      }catch(Exception $e){}
    }

    //Funcion que verifica los puntos de un cliente y registra al usuario en caso que no exista en la base CelmediaPago
    public function ConsultaPuntosWSCLOTPC($rut){
      //Se asegura en caso de caidas
      try {
        //Se instancia un nuevo comunicador de webservice con SoapWrapper
        SoapWrapper::add(function ($service) {
          $service
            ->name('currency')
            ->wsdl('http://190.196.23.184/clop_otpc_web_prestashop/wscl/wsclotpc_server_ps.php?wsdl')
            ->trace(true);
        });
        //Se definen los parametros que consume el webservice
        $data = [
          'rut'         => $rut,
          'usuario'     => 'tienda_ps',
          'origen'      => 'Login WEB OTPC',
          'password'    => '0x552A6798E1F1BCF715EFDB1E1DDC0874',
          'idproveedor' => '8'
        ];


        // Se usa el nuevo webservice creado
        SoapWrapper::service('currency', function ($service) use ($data) {
          $ClientData = $service->call('ConsultaPuntosWSCLOTPC', [$data]);

          //Se busca al usuario en la base de datos local
          $user = User::where('rut', (int)$ClientData->rut)->get();
          //se verifica si existe y lo guarda en una veriable
          if(isset($user[0])){
            $user = json_decode(json_encode($user[0]));
          }
          //Se verifica si el tiene rut válido
          if(isset($user->rut)){
            //Se busca y actualizan los puntos del usuario, luego se guarda
            $user=User::findOrFail($user->id);
            $user->pts = $ClientData->SaldoPuntos;//30000;//
            $user->save();
            return true;
          }else{
            //En caso que el usuario no exista en base se registra y se guarda
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
