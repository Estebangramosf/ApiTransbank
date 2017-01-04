<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
use App\User;
use App\WebpayPago;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Libraries\libwebpay\webpay;
use App\Libraries\libwebpay\configuration;
use Illuminate\Support\Facades\Redirect;

class WebpayController extends Controller
{
   private $webpay;
   private $webpay_config;
   private $webpay_certificate;
   private $ConfigController;
   private $LogController;

   public function __construct()
   {
      $this->ConfigController = new ConfigController();
      $this->LogController = new LogController();
   }

   public function initTransaction($a, $bO, $sId)
   {
      try {
         $wp = $this->setParametersForTransbankTransactions();
         /** Monto de la transacción */
         $amount = $a;
         /** Orden de compra de la tienda */
         $buyOrder = $bO;
         /** Código comercio de la tienda entregado por Transbank */
         $sessionId = $sId;
         /** URL de retorno */
         $urlReturn = $this->ConfigController->urlReturn;
         /** URL Final */
         $urlFinal = $this->ConfigController->urlFinal;
         $request = array(
            "amount" => $amount,
            "buyOrder" => $buyOrder,
            "sessionId" => $sessionId,
            "urlReturn" => $urlReturn,
            "urlFinal" => $urlFinal,
         );
         /** Iniciamos Transaccion */

         $our = Carbon::now()->second.Carbon::now()->minute.Carbon::now()->hour;
         $day = Carbon::now()->day.Carbon::now()->month.Carbon::now()->year;
         //dd($request);

         \Storage::disk('local')->put('Transbank_'.$day.'_Transaction.log', json_encode($request));
         $result = $wp->getNormalTransaction()->initTransaction($amount, $buyOrder, $sessionId, $urlReturn, $urlFinal);
         \Storage::disk('local')->put('Transbank_'.$day.'_Transaction.log', json_encode($result));
         //\Storage::disk('local')->put('Transbank_'.$our.'_'.$day.'_InitTransactionResult.log', json_encode($result));
         //dd($result);
         // Write the contents of a file

         /*
         Acá está el código para guardar ficheros, en caso que transabank solicite los logs,
         lo que falta es que se pueda agregar al mismo archivo el resto de los casos,
         una forma de manejarlo es guardar la ruta del archivo en un campo y llamar la ruta
         para sobreescribir la información.
         */




         /* Para sobre escribir o agregar al archivo */
         /*
         $bytesWritten = File::append($filename, $content);
         if ($bytesWritten === false)
         {
             die("Couldn't write to the file.");
         }
         */


         //Guardamos el token para despues actualizar con el resto de la información
         $WebpayPago = new WebpayPago();
         $WebpayPago->pago_id = $bO;
         $WebpayPago->ord_compra = $bO;
         $WebpayPago->id_sesion = $bO;
         $WebpayPago->token_ws = $result->token;
         $WebpayPago->estado_transaccion = 'initTransaction';
         $WebpayPago->save();
         //Enviamos a la vista el objeto resultante con la informacion para ser POSTeada hacia el ecommerce
         return view('webpay.index', ['result' => $result]);
      } catch (Exception $e) {
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $bO, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function setParametersForTransbankTransactions()
   {
      //Iniciamos un objeto de la clase configuracion
      $wp_config = new configuration();
      //Llamamos a la funcion que solicita la informacion de certificados y ambientes
      $wp_certificate = $this->cert_normal();
      //Asignamos a la configuración los parametros solicitados desde el resultado de la funcion llamada anteriormente
      $wp_config->setEnvironment($wp_certificate['environment']);
      $wp_config->setCommerceCode($wp_certificate['commerce_code']);
      $wp_config->setPrivateKey($wp_certificate['private_key']);
      $wp_config->setPublicCert($wp_certificate['public_cert']);
      $wp_config->setWebpayCert($wp_certificate['webpay_cert']);
      return new webpay($wp_config);
   }

   public function getResult(Request $request)
   {
      try {
         $wp = $this->setParametersForTransbankTransactions();
         //dd($request);
         $our = Carbon::now()->second.Carbon::now()->minute.Carbon::now()->hour;
         $day = Carbon::now()->day.Carbon::now()->month.Carbon::now()->year;
         \Storage::disk('local')->put('Transbank_'.$day.'_Transaction.log', json_encode($request));
         $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
         \Storage::disk('local')->put('Transbank_'.$day.'_Transaction.log', json_encode($result));
         //dd($result);
         //Desde acá filtrar el response code
         switch ($result->detailOutput->responseCode) {
            case '0':
               //echo "Transacción Aprobada";
               $this->saveTransactionResult($request->token_ws, $result, 'ApprovedTransaction');/*'getTransactionResult'*/
               break;
            case '-1':
               //echo "Transacción Rechazada";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionDeclined');
               break;
            case '-2':
               //echo "Transacción debe Reintentarse";
               $this->saveTransactionResult($request->token_ws, $result, 'RetryTransaction');
               break;
            case '-3':
               //echo "Error en Transacción";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionError');
               break;
            case '-4':
               //echo "Rechazo de Transacción";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionRejected');
               break;
            case '-5':
               //echo "Rechazo por error de Tasa";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionRejectedByErrorRate');
               break;
            case '-6':
               //echo "Excede cupo máximo Mensual";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionExceedsMonthlyMaximumQuota');
               break;
            case '-7':
               //echo "Excede límite diario por Transacción";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionExceedsDailyLimit');
               break;
            case '-8':
               //echo "Rubro no Autorizado";
               $this->saveTransactionResult($request->token_ws, $result, 'TransactionUnauthorizedItem');
               break;
         }
         //traer los datos del carro $result->buyOrder
         $historial = HistorialCanje::where('ordenCompraCarrito', $result->buyOrder)->get();
         if (count($historial) == 1) {
            return view('webpay.voucher', ['urlRedirection' => $result->urlRedirection, 'token' => $request->token_ws]);
         } else {
            return view('webpay.canjePendiente', ['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
         }
      } catch (Exception $e) {
         //Excepcion que reacciona cuando ocurre un error al comprobar los certificados
         $WebpayPago = WebpayPago::select('ord_compra')->where('token_ws', $request->token_ws)->get();
         $WebpayPago = json_decode(json_encode($WebpayPago[0]));
         $this->procesarTransaccionNoAprobada($WebpayPago->ord_compra);
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $WebpayPago->ord_compra, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function saveTransactionResult($token_ws, $result, $transactionStatus)
   {
      try {
         $WebpayPago = WebpayPago::where('token_ws', $token_ws)->get();
         $WebpayPago = $WebpayPago[0];
         //dd($result);
         $WebpayPago->accounting_date = $result->accountingDate;
         $WebpayPago->ord_compra = $result->buyOrder;
         $WebpayPago->id_sesion = $result->sessionId;
         $WebpayPago->fh_transaccion = date('Y-m-d H:i:s');
         $WebpayPago->card_number = $result->cardDetail->cardNumber;
         $WebpayPago->card_expiration_date = $result->cardDetail->cardExpirationDate;
         $WebpayPago->authorization_code = $result->detailOutput->authorizationCode;
         $WebpayPago->payment_type_code = $result->detailOutput->paymentTypeCode;
         $WebpayPago->response_code = $result->detailOutput->responseCode;
         $WebpayPago->shares_number = $result->detailOutput->sharesNumber;
         $WebpayPago->shares_amount = $result->detailOutput->sharesAmount;
         $WebpayPago->monto_dinero = $result->detailOutput->amount;
         $WebpayPago->commerce_code = $result->detailOutput->commerceCode;
         $WebpayPago->transaction_date = $result->transactionDate;
         $WebpayPago->vci = $result->VCI;
         $WebpayPago->tp_transaction = $this->ConfigController->WebServiceTpTransactionCelPago;
         $WebpayPago->tpago = date('Y-m-d H:i:s');
         $WebpayPago->hora_pago = date('Y-m-d H:i:s');
         $WebpayPago->estado_transaccion = $transactionStatus; //'getTransactionResult';
         $WebpayPago->save();
         //Retornamos el objeto guardado
         return $WebpayPago;
      } catch (Exception $e) {
      }
   }

   public function end(Request $request)
   {
      try {
         //Se llama la funcion que asigna los parametros necesarios de certificado y ambiente
         $wp = $this->setParametersForTransbankTransactions();

         //dd($request);
         //Se hace la inicializacion de la transaccion por transbank
         $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
         //dd($result);

         //Valida que el resultado sea arreglo
         if (is_array($result)) {
            //Transformamos el arreglo a objeto
            $result = json_decode(json_encode($result));
            //Se valida el código de respuesta del resultado
            if (strpos($result->detail, '274', 15)) {
               //"Transacción anulada";
               //Acá es cuando el cliente anula desde módulo de webpay transbank
               //Se procesa este caso
               $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
               //Se redirecciona a la pagina de rechazo
               return view('webpay.end', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
            } elseif (strpos($result->detail, '272', 15)) {
               //"Error de transaccion por Timeout";
               //Se aprobecha este error para verificar que la transacción no fue anulada
               //Se busca la información guardada en la base local mediante el token
               $WebpayPago = WebpayPago::where('token_ws', $request->token_ws)->get();
               $WebpayPago = $WebpayPago[0];
               //Se verifica si la transacción ya ha sido aprobada y se actualizan los demas parametros
               if ($WebpayPago->estado_transaccion == 'ApprovedTransaction') {
                  $historial = HistorialCanje::where('ordenCompraCarrito', $WebpayPago->ord_compra)->get();
                  $historial = json_decode(json_encode($historial[0]));

                  //dd($WebpayPago);
                  /*
                    #attributes: array:28 [▼
                      "id" => 2
                      "pago_id" => 442
                      "monto_puntos" => 0
                      "monto_dinero" => 40779
                      "diferencia" => 0
                      "estado_pago" => 0
                      "ord_compra" => "442"
                      "id_sesion" => "442"
                      "fh_transaccion" => "2017-01-04"
                      "token_ws" => "e27dfb5abf4f6c20c7efa09f9d48958a247cec1c9a94484a20cf22f672b7bafb"
                      "accounting_date" => "0104"
                      "card_detail" => ""
                      "card_number" => "6623"
                      "card_expiration_date" => ""
                      "authorization_code" => "1213"
                      "payment_type_code" => "VN"
                      "response_code" => "0"
                      "shares_amount" => ""
                      "shares_number" => "0"
                      "commerce_code" => "597020000541"
                      "transaction_date" => "2017-01-04T09:43:24.127-03:00"
                      "vci" => "TSY"
                      "tp_transaction" => "TR_NORMAL_WS"
                      "tpago" => "2017-01-04"
                      "hora_pago" => "2017-01-04"
                      "estado_transaccion" => "ApprovedTransaction"
                      "created_at" => "2017-01-04 12:44:55"
                      "updated_at" => "2017-01-04 12:45:29"
                    ]
                  */

                  //dd($historial);
                  /*
                    +"id": 1
                    +"user_rut": "180025553"
                    +"rc": ""
                    +"fecha_canje": "2017-01-03 22:09:58"
                    +"id_transaccion": ""
                    +"saldo_final": ""
                    +"puntos": ""
                    +"copago": ""
                    +"ordenCompraCarrito": "440"
                    +"estado": "encanje"
                    +"created_at": "2017-01-03 22:09:58"
                    +"updated_at": "2017-01-03 22:09:58"
                  */

                  $user = User::where('rut', $historial->user_rut)->first();
                  $total = $historial->puntos - $user->pts;

                  $this->generateSwap($user->rut, $user->pts, $user->otpc, ($total * 3), $WebpayPago->ord_compra);

                  $historial->authorization_code = $WebpayPago->authorization_code;
                  $historial->payment_type_code = $WebpayPago->payment_type_code;
                  $historial->shares_number = $WebpayPago->shares_number;
                  return view('webpay.responseCanjeSiTransbank', ['historial' => $historial, 'urlExito'=>$this->ConfigController->urlExito]);
               } else {
                  //Se deja nuevamente al cliente en estado activo para realizar un nuevo canje
                  $this->procesarTransaccionNoAprobada($WebpayPago->ord_compra);
                  //Cuando el estado no fue aprobado, lo redicrecciona a su vista correspondiente
                  return view('webpay.webpayResponseErrors.' . $WebpayPago->estado_transaccion,
                     ['TBK_ORDEN_COMPRA' => $WebpayPago->ord_compra, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
               }
            } else {
               $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
               return view('webpay.end', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
            }
         } else {
            $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
            return view('webpay.end', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
         }
      } catch (Exception $e) {
         $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
         return view('webpay.end', ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function generateSwap($rut, $monto, $otpc, $copago, $ordenCompraCarrito){
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
            /*
             * Aqui despues se reemplazan con los campos faltantes */
            if (count( $historial = HistorialCanje::where('ordenCompraCarrito', $ordenCompraCarrito)->get()) > 0) {

               dd($Result);
               $historial[0]->user_rut = $Result->rut;
               $historial[0]->rc = $Result->RC;
               $historial[0]->fecha_canje = $Result->fecha_canje;
               $historial[0]->id_transaccion = $Result->id_transaccion;
               $historial[0]->saldo_final = $Result->saldo_final;
               $historial[0]->puntos = $Result->puntos;
               $historial[0]->copago = $data['copago'];
               $historial[0]->ordenCompraCarrito = $ordenCompraCarrito;
               $historial[0]->estado = 'canjeado';
               $historial[0]->save();
               /*
               HistorialCanje::create([
                  'user_rut' => $Result->rut,
                  'rc' => $Result->RC,
                  'fecha_canje' => $Result->fecha_canje,
                  'id_transaccion' => $Result->id_transaccion,
                  'saldo_final' => $Result->saldo_final,
                  'puntos' => $Result->puntos,
                  'copago' => $data['copago'],
                  'ordenCompraCarrito' => $ordenCompraCarrito,
                  'estado' => 'canjeado',
               ])->save();
               */

               return true;
            } else {
               return true;
            }
         });
      } catch (Exception $e) {
      }
   }

   public function procesarTransaccionNoAprobada($TBK_ORDEN_COMPRA)
   {
      try {
         $historial = HistorialCanje::select('user_rut')->where('ordenCompraCarrito', $TBK_ORDEN_COMPRA)->get();
         $historial = $historial[0];
         $this->CambioEstadoPorAnulacionWSCLOTPC($historial->user_rut);
      } catch (Exception $e) {
         $this->procesarTransaccionNoAprobada($TBK_ORDEN_COMPRA);
         return view('webpay.end', ['TBK_ORDEN_COMPRA' => $TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function CambioEstadoPorAnulacionWSCLOTPC($user_rut)
   {
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
            'usuario' => $this->ConfigController->WebServiceUserCelPago,
            'password' => $this->ConfigController->WebServicePasswordCelPago,
            'rut' => $user_rut,
         ];
         // Se usa el nuevo webservice creado
         SoapWrapper::service('currency', function ($service) use ($data) {
            $service->call('CambioEstadoPorAnulacionWSCLOTPC', [$data]);
            return true;
         });
      } catch (Exception $e) {
      }
   }

   public function cert_normal()
   {
      return $certificate = array(
         /** Ambiente */
         "environment" => $this->ConfigController->TransbankEnvironment,
         /** Llave Privada */
         "private_key" => $this->ConfigController->TransbankPrivateKey,
         /** Certificado Publico */
         "public_cert" => $this->ConfigController->TransbankPublicCert,
         /** Certificado Server */
         "webpay_cert" => $this->ConfigController->TransbankWebpayCert,
         /** Codigo Comercio */
         "commerce_code" => $this->ConfigController->TransbankCommerceCode,
      );
   }
}