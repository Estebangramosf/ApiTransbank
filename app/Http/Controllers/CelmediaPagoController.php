<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
use App\TransactionValidation;
use App\WebpayPago;
use DateTime;
use Exception;
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

    public function getShoppingCart(Request $request){
      try{

         $request->TBK_ORDEN_COMPRA = "182";

         $WebpayPago = WebpayPago::where('ord_compra', $request->TBK_ORDEN_COMPRA)->get();

         //Filtro cuando se ingresa una transacción ya registrada
         if(count($WebpayPago)>0){
            $WebpayPago = json_decode(json_encode($WebpayPago[0]));
            if($WebpayPago->estado_transaccion != 'ApprovedTransaction'){

               $TV = TransactionValidation::where('TBK_ORDEN_COMPRA', '=',$WebpayPago->ord_compra)->get();

               if(count($TV)>0){
                  $TV[0]->delete();
               }

               $TransactionValidation = new TransactionValidation();

               $TransactionValidation->TBK_MONTO = $request->TBK_MONTO;
               $TransactionValidation->TBK_TIPO_TRANSACCION = $request->TBK_TIPO_TRANSACCION;
               $TransactionValidation->TBK_ORDEN_COMPRA = $request->TBK_ORDEN_COMPRA;
               $TransactionValidation->TBK_ID_SESION = $request->TBK_ID_SESION;
               $TransactionValidation->TBK_RUT = $request->TBK_RUT;
               $TransactionValidation->TBK_CORPBANCA = $request->TBK_CORPBANCA;
               $TransactionValidation->TBK_OTPC_WEB = $request->TBK_OTPC_WEB;
               $TransactionValidation->TRANSACTION_STATUS = $WebpayPago->estado_transaccion;
               $TransactionValidation->save();

               return view('webpay.webpayValidations.AuthTransaction', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA]);
            }else{
               return view('webpay.webpayValidations.TransactionAlreadyApproved');
            }

         }else{
            return $this->celmediaPagoPostInit($request);
         }



      }catch(Exception $e){
        //dd($e);
        //Excepcion que reacciona cuando ocurre un error al comprobar los certificados
        return view('webpay.webpayResponseErrors.invalidWebpayCert');
      }

    }//End function getShoppingCart()





   public function celmediaPagoInit(Request $request){
      try{

         //Buscar el en tabla de paso TransactionValidations               OK
         //Hacer funcionar lo demás con los datos traídos                  OK
         //Eliminar el registro anterior y crear una nueva transacción
         //Verificar tambien que la transaccion esta aprobada o no

         $request = TransactionValidation::where('TBK_ORDEN_COMPRA', '=',$request->TBK_ORDEN_COMPRA)->get();

         if(count($request)>0){
            $request = json_decode(json_encode($request[0]));
         }

         if($request->TRANSACTION_STATUS == 'ApprovedTransaction'){
            return view('webpay.webpayValidations.TransactionAlreadyApproved');
         }

         $WebpayPagoOld = WebpayPago::where('ord_compra', $request->TBK_ORDEN_COMPRA)->get();
         if(count($WebpayPagoOld)>0){
            $WebpayPagoOld[0]->delete();
         }


         return $this->celmediaPagoPostInit($request);



      }catch(Exception $e){
         return view('webpay.webpayResponseErrors.invalidWebpayCert');
      }
   }

   public function celmediaPagoPostInit($request){
      try{
         //Se crea el objeto $userResult como resultado de la verificación del usuario
         $userResult = $this->verifyRUTExistanceAndGetUser($request);



         //$request->TBK_MONTO=str_replace(".","",$request->TBK_MONTO);
         $request->TBK_MONTO=round($request->TBK_MONTO,0);




         if( $userResult->pts >= $request->TBK_MONTO){

            //Se genera el canje y solicitud de canje
            $this->generateSwap($request->TBK_RUT,$request->TBK_MONTO,$request->TBK_OTPC_WEB,0,$request->TBK_ORDEN_COMPRA);


            $historial = HistorialCanje::where('estado','encanje')->where('ordenCompraCarrito',$request->TBK_ORDEN_COMPRA)->get();

            if(count($historial)>0){

               $historial = json_decode(json_encode($historial[0]));
               return view('webpay.responseCanjeNoTransbank', ['historial'=>$historial]);

            }

            $historial = HistorialCanje::where('ordenCompraCarrito',$request->TBK_ORDEN_COMPRA)->get();

            //si viene vacío es por que no se generó la compra, por ende puede que esté en estado en canje
            if(count($historial)==0){
               return view('webpay.canjePendiente');
            }



         }else{

            $total = ($userResult->pts - $request->TBK_MONTO);



            $this->generateSwap($request->TBK_RUT,$userResult->pts,$request->TBK_OTPC_WEB,($total*-3),$request->TBK_ORDEN_COMPRA) ;



            $historial = HistorialCanje::where('estado','encanje')->where('ordenCompraCarrito',$request->TBK_ORDEN_COMPRA)->get();



            $result = '';

            if(count($historial)>0){
               $result = $this->WebpayController->initTransaction($total*-3,$request->TBK_ORDEN_COMPRA,$request->TBK_ID_SESION);
            }

            $historial = HistorialCanje::where('ordenCompraCarrito',$request->TBK_ORDEN_COMPRA)->get();



            //si viene vacío es por que no se generó la compra, por ende puede que esté en estado en canje
            if(count($historial)==0){
               return view('webpay.canjePendiente');
            }

            return view('webpay.index', ['result'=>$result]);
         }
      }catch(Exception $e){
         return view('webpay.webpayResponseErrors.invalidWebpayCert');
      }

   }


    //Funcion que verifica si el rut del usuario existe mediante request y devuelve al usuario como objeto de la clase
    //En caso que el usuario no exista, redirecciona al usuario
    public function verifyRUTExistanceAndGetUser($request){
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
    }//End function verifyRUTExistanceAndGetUser()

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
    }//End function ConsultaPuntosWSCLOTPC()

    public function generateSwap($rut,$monto,$otpc,$copago,$ordenCompraCarrito){
      //Se asegura en caso de caidas
      try {
        //Se instancia un nuevo comunicador de webservice con SoapWrapper
        SoapWrapper::add(function ($service) {
          $service
            ->name('ConfirmaCanje')
            ->wsdl('http://190.196.23.184/clop_otpc_web_prestashop/wscl/wsclotpc_server_ps.php?wsdl')
            ->trace(true);
        });

        $data = [
          'usuario'=>'celmediapago',
          'password'=>'0x552A6798E1F1BCF715EFDB1E1DDC0874',
          'idproveedor'=>'9',
          //'rut'=>'171058902',//$rut,
          //'rut'=>'180025553',//$rut,
          'rut'=>$rut,//$rut,
          'origen'=>'PRUEBAS_JCH',
          'monto'=>$monto,
          'copago'=>$copago,
          'uni_canje'=>'0',
          'descripcion'=>'Canje de Prueba JCH',
          'cod_prod_prov'=>'COD001',
          'id_grupo'=>'10',
          'id_categoria'=>'36',
          'id_subcategoria'=>'187',
          'hash_otpc'=>$otpc,
          'tdv_otpc'=>'31',
        ];

        // Se usa el nuevo webservice creado
        SoapWrapper::service('ConfirmaCanje', function ($service) use ($data,$ordenCompraCarrito) {


          $Result = $service->call('ConfirmaCanjePSWSCLOTPC', [$data]);
          if($Result->RC == '227'){

            return view('webpay.canjePendiente');

          }

          if(count(HistorialCanje::where('ordenCompraCarrito', $ordenCompraCarrito)->get())==0){
            HistorialCanje::create([
              'user_rut'=>$Result->rut,
              'rc'=>$Result->RC,
              'fecha_canje'=>$Result->fecha_canje,
              'id_transaccion'=>$Result->id_transaccion,
              'saldo_final'=>$Result->saldo_final,
              'puntos'=>$Result->puntos,
              'copago'=>$data['copago'],
              'ordenCompraCarrito'=>$ordenCompraCarrito,
              'estado'=>'encanje',
            ])->save();
            return true;
          }else{
            return true;
          }
        });

      } catch(Exception $e) {

      }
    }//End function generateSwap()


}
