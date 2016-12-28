<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
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

         //dd($request);
         $result = $wp->getNormalTransaction()->initTransaction($amount, $buyOrder, $sessionId, $urlReturn, $urlFinal);
         //dd($result);
         // Write the contents of a file

         /*
         Acá está el código para guardar ficheros, en caso que transabank solicite los logs,
         lo que falta es que se pueda agregar al mismo archivo el resto de los casos,
         una forma de manejarlo es guardar la ruta del archivo en un campo y llamar la ruta
         para sobreescribir la información.

         $our = Carbon::now()->second.Carbon::now()->minute.Carbon::now()->hour;
         $day = Carbon::now()->day.Carbon::now()->month.Carbon::now()->year;
         $file = \Storage::disk('local')->put('Transbank_'.$our.'_'.$day.'_NameFunction.log', json_encode($result));
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
         $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
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
         $historial = HistorialCanje::where('estado', 'encanje')->where('ordenCompraCarrito', $result->buyOrder)->get();
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
                  $historial = HistorialCanje::where('estado', 'encanje')->where('ordenCompraCarrito', $WebpayPago->ord_compra)->get();
                  $historial = json_decode(json_encode($historial[0]));
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

   public function procesarTransaccionNoAprobada($TBK_ORDEN_COMPRA)
   {
      try {
         $historial = HistorialCanje::select('user_rut')->where('estado', 'encanje')->where('ordenCompraCarrito', $TBK_ORDEN_COMPRA)->get();
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