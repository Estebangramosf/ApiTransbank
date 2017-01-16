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
use File;
use App\Libraries\libwebpay\webpay;
use App\Libraries\libwebpay\configuration;
use Illuminate\Support\Facades\Redirect;

class WebpayController extends Controller
{
   private $WebpayPago;
   private $webpay_config;
   private $webpay_certificate;
   private $ConfigController;

   public function __construct()
   {
      $this->ConfigController = new ConfigController();
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
            "wSTransactionType" => 'TR_NORMAL_WS',
            "amount" => $amount,
            "buyOrder" => $buyOrder,
            "sessionId" => $sessionId,
            "urlReturn" => $urlReturn,
            "urlFinal" => $urlFinal,
         );
         /** Iniciamos Transaccion */
         $day = Carbon::now()->day.Carbon::now()->month.Carbon::now()->year;
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['Transaction Process'=>'InitTransaction Request']));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(($request), JSON_PRETTY_PRINT));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['#######################################']));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['Transaction Process'=>'InitTransaction Response']));
         $result = $wp->getNormalTransaction()->initTransaction($amount, $buyOrder, $sessionId, $urlReturn, $urlFinal);
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode($result, JSON_PRETTY_PRINT));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['#######################################']));
         $WebpayPago = new WebpayPago();
         $WebpayPago->pago_id = $bO;
         $WebpayPago->ord_compra = $bO;
         $WebpayPago->id_sesion = $bO;
         $WebpayPago->token_ws = $result->token;
         $WebpayPago->estado_transaccion = 'initTransaction';
         $WebpayPago->save();
         return view('webpay.index', ['result' => $result]);
      } catch (Exception $e) {
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $bO, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function setParametersForTransbankTransactions()
   {
      $wp_config = new configuration();
      $wp_certificate = $this->cert_normal();
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
         $day = Carbon::now()->day.Carbon::now()->month.Carbon::now()->year;
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['Transaction Process'=>'GetTransaction Request']));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['token_ws'=>($request->token_ws)], JSON_PRETTY_PRINT));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['#######################################']));
         $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['Transaction Process'=>'GetTransaction Response']));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode($result, JSON_PRETTY_PRINT));
         \Storage::disk('local')->append('Transbank_'.$day.'_DailyTransactions.log', json_encode(['#######################################']));

         switch ($result->detailOutput->responseCode) {
            case '0':
               //echo "Transacción Aprobada";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'ApprovedTransaction');/*'getTransactionResult'*/
               return view('webpay.voucher', ['urlRedirection' => $result->urlRedirection, 'token' => $request->token_ws]);
               break;
            case '-1':
               //echo "Transacción Rechazada";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionDeclined');
               break;
            case '-2':
               //echo "Transacción debe Reintentarse";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'RetryTransaction');
               break;
            case '-3':
               //echo "Error en Transacción";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionError');
               break;
            case '-4':
               //echo "Rechazo de Transacción";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionRejected');
               break;
            case '-5':
               //echo "Rechazo por error de Tasa";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionRejectedByErrorRate');
               break;
            case '-6':
               //echo "Excede cupo máximo Mensual";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionExceedsMonthlyMaximumQuota');
               break;
            case '-7':
               //echo "Excede límite diario por Transacción";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionExceedsDailyLimit');
               break;
            case '-8':
               //echo "Rubro no Autorizado";
               $this->WebpayPago = $this->saveTransactionResult($request->token_ws, $result, 'TransactionUnauthorizedItem');
               break;
         }

         $this->procesarTransaccionNoAprobada($this->WebpayPago->ord_compra);
         return view('webpay.webpayResponseErrors.' . $this->WebpayPago->estado_transaccion,
            ['TBK_ORDEN_COMPRA' => $this->WebpayPago->ord_compra, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      } catch (Exception $e) {
         $WebpayPago = WebpayPago::select('ord_compra')->where('token_ws', $request->token_ws)->first();
         $this->procesarTransaccionNoAprobada($WebpayPago->ord_compra);
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $WebpayPago->ord_compra, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function saveTransactionResult($token_ws, $result, $transactionStatus)
   {
      try {
         $WebpayPago = WebpayPago::where('token_ws', $token_ws)->first();
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
         return $WebpayPago;
      } catch (Exception $e) {
      }
   }

   public function end(Request $request)
   {
      try {
         $wp = $this->setParametersForTransbankTransactions();
         $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
         if (is_array($result)) {
            $result = json_decode(json_encode($result));
            if (strpos($result->detail, '274', 15)) {
               $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
               return view('webpay.end',
                  ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
            } elseif (strpos($result->detail, '272', 15)) {
               $WebpayPago = WebpayPago::where('token_ws', $request->token_ws)->first();
               if ($WebpayPago->estado_transaccion == 'ApprovedTransaction') {
                  $historial = HistorialCanje::where('ordenCompraCarrito', $WebpayPago->ord_compra)->first();
                  $user = User::where('rut', $historial->user_rut)->first();
                  $total = $historial->puntos - $user->pts;
                  $this->generateSwap($user->rut, $user->pts, $user->otpc, ($total * 3), $WebpayPago->ord_compra);
                  $historial = HistorialCanje::where('ordenCompraCarrito', $WebpayPago->ord_compra)->first();
                  $historial->copago = $WebpayPago->monto_dinero;
                  $historial->authorization_code = $WebpayPago->authorization_code;
                  $historial->payment_type_code = $WebpayPago->payment_type_code;
                  $historial->shares_number = $WebpayPago->shares_number;
                  $historial->card_number = $WebpayPago->card_number;
                  return view('webpay.responseCanjeSiTransbank', ['historial' => $historial, 'urlExito'=>$this->ConfigController->urlExito]);
               } else {
                  $this->procesarTransaccionNoAprobada($WebpayPago->ord_compra);
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
      try {
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
            'rut' => $rut,//$rut,
            'origen' => $this->ConfigController->WebServiceOrigenCelPago,
            'monto' => $monto, //acá van los montos concatenados
            'copago' => $copago,
            'uni_canje' => $this->ConfigController->WebServiceUniCanjeCelPago,
            'descripcion' => $result->names, //acá van los nombres concatenados
            'cod_prod_prov' => $result->references, //acá van los códigos concatenados
            'id_grupo' => $this->ConfigController->WebServiceIdGrupoCelPago,
            'id_categoria' => $this->ConfigController->WebServiceIdCategoriaCelPago,
            'id_subcategoria' => $this->ConfigController->WebServiceIdSubCategoriaCelPago,
            'hash_otpc' => $otpc,
            'tdv_otpc' => $this->ConfigController->WebServiceTdvOtpcCelPago,
         ];
         SoapWrapper::service('ConfirmaCanje', function ($service) use ($data, $ordenCompraCarrito) {
            $Result = $service->call('ConfirmaCanjePSWSCLOTPC', [$data]);
            if ($Result->RC == '227') {
               return view('webpay.canjePendiente',['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
            }
            if (count( $historial = HistorialCanje::where('ordenCompraCarrito', $ordenCompraCarrito)->first()) > 0) {
               $historial->user_rut = $Result->rut;
               $historial->rc = $Result->RC;
               $historial->fecha_canje = $Result->fecha_canje;
               $historial->id_transaccion = $Result->id_transaccion;
               $historial->saldo_final = $Result->saldo_final;
               $historial->puntos = $Result->puntos;
               $historial->copago = $data['copago'];
               $historial->ordenCompraCarrito = $ordenCompraCarrito;
               $historial->estado = 'canjeado';
               $historial->save();
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
         $historial = HistorialCanje::select('user_rut')->where('ordenCompraCarrito', $TBK_ORDEN_COMPRA)->first();
         $this->CambioEstadoPorAnulacionWSCLOTPC($historial->user_rut);
      } catch (Exception $e) {
         $this->procesarTransaccionNoAprobada($TBK_ORDEN_COMPRA);
         return view('webpay.end',
            ['TBK_ORDEN_COMPRA' => $TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function CambioEstadoPorAnulacionWSCLOTPC($user_rut)
   {
      try {
         SoapWrapper::add(function ($service) {
            $service
               ->name('currency')
               ->wsdl($this->ConfigController->WebServiceServer)
               ->trace(true);
         });
         $data = [
            'usuario' => $this->ConfigController->WebServiceUserCelPago,
            'password' => $this->ConfigController->WebServicePasswordCelPago,
            'rut' => $user_rut,
         ];
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