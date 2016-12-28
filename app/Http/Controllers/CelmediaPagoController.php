<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
use App\TransactionValidation;
use App\WebpayPago;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use App\User;

class CelmediaPagoController extends Controller
{

   private $WebserviceController;
   private $WebpayController;
   private $ConfigController;

   public function __construct()
   {
      $this->WebserviceController = new WebserviceController();
      $this->WebpayController = new WebpayController();
      $this->ConfigController = new ConfigController();
   }

   public function getShoppingCart(Request $request)
   {
      try {
         $WebpayPago = WebpayPago::where('ord_compra', $request->TBK_ORDEN_COMPRA)->get();
         //Filtro cuando se ingresa una transacción ya registrada
         if (count($WebpayPago) > 0) {
            $WebpayPago = json_decode(json_encode($WebpayPago[0]));
            if ($WebpayPago->estado_transaccion != 'ApprovedTransaction') {
               /*
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

               return view('webpay.webpayValidations.AuthTransaction', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlApi' => $this->ConfigController->urlApi]);
               */
               return view('webpay.webpayValidations.TransactionAlreadyProcessed', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'ecommerceHomeUrl'=>$this->ConfigController->ecommerceHomeUrl]);
            } else {
               return view('webpay.webpayValidations.TransactionAlreadyApproved', ['ecommerceHomeUrl'=>$this->ConfigController->ecommerceHomeUrl]);
            }
         } else {
            return $this->celmediaPagoPostInit($request);
         }
      } catch (Exception $e) {
         //Excepcion que reacciona cuando ocurre un error al comprobar los certificados
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }

   }//End function getShoppingCart()
   public function celmediaPagoInit(Request $request)
   {
      try {
         $request = TransactionValidation::where('TBK_ORDEN_COMPRA', '=', $request->TBK_ORDEN_COMPRA)->get();
         if (count($request) > 0) {
            $request = json_decode(json_encode($request[0]));
         }
         if ($request->TRANSACTION_STATUS == 'ApprovedTransaction') {
            return view('webpay.webpayValidations.TransactionAlreadyApproved',['ecommerceHomeUrl'=>$this->ConfigController->ecommerceHomeUrl]);
         }
         $WebpayPagoOld = WebpayPago::where('ord_compra', $request->TBK_ORDEN_COMPRA)->get();
         if (count($WebpayPagoOld) > 0) {
            $WebpayPagoOld[0]->delete();
         }
         return $this->celmediaPagoPostInit($request);
      } catch (Exception $e) {
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function celmediaPagoPostInit($request)
   {
      try {
         //Se crea el objeto $userResult como resultado de la verificación del usuario
         $userResult = $this->verifyRUTExistanceAndGetUser($request);
         //$request->TBK_MONTO=str_replace(".","",$request->TBK_MONTO);
         $request->TBK_MONTO = round($request->TBK_MONTO, 0);
         if ($userResult->pts >= $request->TBK_MONTO) {
            //Se genera el canje y solicitud de canje
            $this->generateSwap($request->TBK_RUT, $request->TBK_MONTO, $request->TBK_OTPC_WEB, 0, $request->TBK_ORDEN_COMPRA);
            $historial = HistorialCanje::where('estado', 'encanje')->where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->get();
            if (count($historial) > 0) {
               $historial = json_decode(json_encode($historial[0]));
               return view('webpay.responseCanjeNoTransbank', ['historial' => $historial, 'urlExito'=>$this->ConfigController->urlExito]);
            }
            $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->get();
            //si viene vacío es por que no se generó la compra, por ende puede que esté en estado en canje
            if (count($historial) == 0) {
               return view('webpay.canjePendiente',['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
            }
         } else {
            $total = ($request->TBK_MONTO - $userResult->pts);
            $this->generateSwap($request->TBK_RUT, $userResult->pts, $request->TBK_OTPC_WEB, ($total * 3), $request->TBK_ORDEN_COMPRA);
            $historial = HistorialCanje::where('estado', 'encanje')->where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->get();
            $result = '';
            if (count($historial) > 0) {
               $result = $this->WebpayController->initTransaction($total * 3, $request->TBK_ORDEN_COMPRA, $request->TBK_ID_SESION);
            }
            $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->get();
            //si viene vacío es por que no se generó la compra, por ende puede que esté en estado en canje
            if (count($historial) == 0) {
               return view('webpay.canjePendiente',['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
            }
            return $result;
         }
      } catch (Exception $e) {
         //Aca se cae en la primera validación de error de certificados
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }


   //Funcion que verifica si el rut del usuario existe mediante request y devuelve al usuario como objeto de la clase
   //En caso que el usuario no exista, redirecciona al usuario
   public function verifyRUTExistanceAndGetUser($request)
   {
      try {
         if ($request->TBK_RUT && isset($request->TBK_RUT)) {
            //Obtiene la informacion del cliente, si no existe lo registra, si existe, actualiza los puntos desde WS
            $this->ConsultaPuntosWSCLOTPC($request->TBK_RUT);
            //Se busca al usuario en la base de datos local
            $user = User::where('rut', (int)$request->TBK_RUT)->get();
            //se verifica si existe y lo guarda en una veriable
            if (isset($user[0])) {
               return json_decode(json_encode($user[0]));
            } else {
               return Redirect::to($this->ConfigController->ecommerceHomeUrl);
            }
         }
      } catch (Exception $e) {
      }
   }//End function verifyRUTExistanceAndGetUser()

   //Funcion que verifica los puntos de un cliente y registra al usuario en caso que no exista en la base CelmediaPago
   public function ConsultaPuntosWSCLOTPC($rut)
   {
      //Se asegura en caso de caidas
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
            'rut' => $rut,
            'usuario' => $this->ConfigController->WebServiceUserCelPago,
            'origen' => $this->ConfigController->WebServiceOrigenCelPago,
            'password' => $this->ConfigController->WebServicePasswordCelPago,
            'idproveedor' => $this->ConfigController->WebServiceIdProveedorCelPago
            /*
            DEPRECATED BY REPLACE
            'rut' => $rut,
            'usuario' => $this->ConfigController->WebServiceUser,
            'origen' => $this->ConfigController->WebServiceOrigen,
            'password' => $this->ConfigController->WebServicePassword,
            'idproveedor' => $this->ConfigController->WebServiceIdProveedorl
            */
         ];


         // Se usa el nuevo webservice creado
         SoapWrapper::service('currency', function ($service) use ($data) {
            $ClientData = $service->call('ConsultaPuntosWSCLOTPC', [$data]);
            //Se busca al usuario en la base de datos local
            $user = User::where('rut', (int)$ClientData->rut)->get();
            //se verifica si existe y lo guarda en una veriable
            if (isset($user[0])) {
               $user = json_decode(json_encode($user[0]));
            }
            //Se verifica si el tiene rut válido
            if (isset($user->rut)) {
               //Se busca y actualizan los puntos del usuario, luego se guarda
               $user = User::findOrFail($user->id);
               $user->pts = $ClientData->SaldoPuntos;//30000;//
               $user->save();
               return true;
            } else {
               //En caso que el usuario no exista en base se registra y se guarda
               User::create([
                  'name' => $ClientData->Nombre,
                  'rut' => $ClientData->rut,
                  'pts' => $ClientData->SaldoPuntos
               ])->save();
               return true;
            }
            //dd($service->call('ConsultaPuntosWSCLOTPC', [$data]));
            //var_dump($service->call('Otherfunction'));
         });
      } catch (Exception $e) {
      }
   }//End function ConsultaPuntosWSCLOTPC()

   public function generateSwap($rut, $monto, $otpc, $copago, $ordenCompraCarrito)
   {
      //Se asegura en caso de caidas
      try {
         //Se instancia un nuevo comunicador de webservice con SoapWrapper
         SoapWrapper::add(function ($service) {
            $service
               ->name('ConfirmaCanje')
               ->wsdl($this->ConfigController->WebServiceServer)
               ->trace(true);
         });
         $psc = new PrestashopController();
         $result = $psc->prestashopGetProductsDetails($ordenCompraCarrito);
         $data = [
            'usuario' => $this->ConfigController->WebServiceUserCelPago,
            'password' => $this->ConfigController->WebServicePasswordCelPago,
            'idproveedor' => $this->ConfigController->WebServiceIdProveedorCelPago,
            //'rut'=>'171058902',//$rut,
            //'rut'=>'180025553',//$rut,
            'rut' => $rut,//$rut,
            'origen' => $this->ConfigController->WebServiceOrigenCelPago,
            'monto' => $monto, //acá van los montos concatenados
            //'monto'=>$result->prices, //acá van los montos concatenados
            'copago' => $copago,
            'uni_canje' => $this->ConfigController->WebServiceUniCanjeCelPago,
            //'descripcion'=>'Canje de Prueba JCH', //acá van los nombres concatenados
            'descripcion' => $result->names, //acá van los nombres concatenados
            //'cod_prod_prov'=>'COD001', //acá van los códigos concatenados
            'cod_prod_prov' => $result->references, //acá van los códigos concatenados
            'id_grupo' => $this->ConfigController->WebServiceIdGrupoCelPago,
            'id_categoria' => $this->ConfigController->WebServiceIdCategoriaCelPago,
            'id_subcategoria' => $this->ConfigController->WebServiceIdSubCategoriaCelPago,
            'hash_otpc' => $otpc,
            'tdv_otpc' => $this->ConfigController->WebServiceTdvOtpcCelPago,
         ];
         // Se usa el nuevo webservice creado
         SoapWrapper::service('ConfirmaCanje', function ($service) use ($data, $ordenCompraCarrito) {
            $Result = $service->call('ConfirmaCanjePSWSCLOTPC', [$data]);
            //dd($Result);
            if ($Result->RC == '227') {
               return view('webpay.canjePendiente',['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
            }
            if (count(HistorialCanje::where('ordenCompraCarrito', $ordenCompraCarrito)->get()) == 0) {
               HistorialCanje::create([
                  'user_rut' => $Result->rut,
                  'rc' => $Result->RC,
                  'fecha_canje' => $Result->fecha_canje,
                  'id_transaccion' => $Result->id_transaccion,
                  'saldo_final' => $Result->saldo_final,
                  'puntos' => $Result->puntos,
                  'copago' => $data['copago'],
                  'ordenCompraCarrito' => $ordenCompraCarrito,
                  'estado' => 'encanje',
               ])->save();
               return true;
            } else {
               return true;
            }
         });
      } catch (Exception $e) {
      }
   }//End function generateSwap()
}
