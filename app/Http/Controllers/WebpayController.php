<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
use App\WebpayPago;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Libraries\libwebpay\webpay;
use App\Libraries\libwebpay\configuration;
use Illuminate\Support\Facades\Redirect;

class WebpayController extends Controller
{
    private $webpay;
    private $webpay_config;
    private $webpay_certificate;

    public function __construct()
    {

    }

    public function initTransaction($a,$bO,$sId)
    {
      try{

        $wp = $this->setParametersForTransbankTransactions();

        /** Monto de la transacción */
        //$amount = 9990;
        $amount = $a;

        /** Orden de compra de la tienda */
        //$buyOrder = rand();
        $buyOrder = $bO;

        /** Código comercio de la tienda entregado por Transbank */
        //$sessionId = uniqid();
        $sessionId = $sId;

        /** URL de retorno */
        //$urlReturn = "http://dev.apitransbank.com/getResult";
        $urlReturn = "http://192.168.1.192/getResult";

        /** URL Final */
        //l$urlFinal  = "http://dev.apitransbank.com/end";
        $urlFinal  = "http://192.168.1.192/end";

        $request = array(
          "amount"    => $amount,
          "buyOrder"  => $buyOrder,
          "sessionId" => $sessionId,
          "urlReturn" => $urlReturn,
          "urlFinal"  => $urlFinal,
        );

        /** Iniciamos Transaccion */


        //dd($request);
        $result = $wp->getNormalTransaction()->initTransaction($amount, $buyOrder, $sessionId, $urlReturn, $urlFinal);
        //dd($result);


        //Guardamos el token para despues actualizar con el resto de la información
        $WebpayPago = new WebpayPago();
        $WebpayPago->pago_id = $bO;
        $WebpayPago->ord_compra = $bO;
        $WebpayPago->id_sesion = $bO;
        $WebpayPago->token_ws = $result->token;
        $WebpayPago->estado_transaccion = 'initTransaction';
        $WebpayPago->save();

         return view('webpay.index', ['result'=>$result]);


      }catch(Exception $e){
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA' => $bO]);
      }
    }

    public function setParametersForTransbankTransactions(){
      $wp_config = new configuration();
      $wp_certificate = $this->cert_normal();

      $wp_config->setEnvironment($wp_certificate['environment']);
      $wp_config->setCommerceCode($wp_certificate['commerce_code']);
      $wp_config->setPrivateKey($wp_certificate['private_key']);
      $wp_config->setPublicCert($wp_certificate['public_cert']);
      $wp_config->setWebpayCert($wp_certificate['webpay_cert']);

      return new webpay($wp_config);
    }

    public function getResult(Request $request){
      try{

        $wp = $this->setParametersForTransbankTransactions();

        //dd($request);
        $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
        //dd($result);

        //Desde acá filtrar el response code

        switch($result->detailOutput->responseCode){
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
            return view('webpay.canjePendiente');
          }
      }catch(Exception $e){
         //Excepcion que reacciona cuando ocurre un error al comprobar los certificados
         $WebpayPago = WebpayPago::select('ord_compra')->where('token_ws', $request->token_ws)->get();
         $WebpayPago = json_decode(json_encode($WebpayPago[0]));
         $this->procesarTransaccionNoAprobada($WebpayPago->ord_compra);
         return view('webpay.webpayResponseErrors.invalidWebpayCert', ['TBK_ORDEN_COMPRA'=>$WebpayPago->ord_compra]);
      }

    }

    public function saveTransactionResult($token_ws, $result, $transactionStatus){
      try{

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
        $WebpayPago->tp_transaction = 'TR_NORMAL_WS';
        $WebpayPago->tpago = date('Y-m-d H:i:s');
        $WebpayPago->hora_pago = date('Y-m-d H:i:s');
        $WebpayPago->estado_transaccion = $transactionStatus; //'getTransactionResult';

        $WebpayPago->save();

        return $WebpayPago;

      }catch(Exception $e){}
    }

    public function end(Request $request){
      try{
         //en esta parte debiese eliminar la validation_transaction

        $wp = $this->setParametersForTransbankTransactions();
        $result = $wp->getNormalTransaction()->getTransactionResult($request->token_ws);
        if(is_array($result)){
          $result = json_decode(json_encode($result));

           //dd($result);

          if(strpos($result->detail,'274', 15)){
            //return "Transacción anulada";
            //Acá es cuando el cliente anula desde módulo de webpay transbank
            $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
            return view('webpay.end', ['TBK_ORDEN_COMPRA'=>$request->TBK_ORDEN_COMPRA]);
          }elseif(strpos($result->detail,'272', 15)){
            //return "Error de transaccion por Timeout";
            $WebpayPago = WebpayPago::where('token_ws', $request->token_ws)->get();
            $WebpayPago = $WebpayPago[0];



            if($WebpayPago->estado_transaccion == 'ApprovedTransaction'){
              $historial = HistorialCanje::where('estado','encanje')->where('ordenCompraCarrito',$WebpayPago->ord_compra)->get();
              $historial = json_decode(json_encode($historial[0]));
              $historial->authorization_code = $WebpayPago->authorization_code;
              $historial->payment_type_code = $WebpayPago->payment_type_code;
              $historial->shares_number = $WebpayPago->shares_number;

              //dd($historial);

              return view('webpay.responseCanjeSiTransbank', ['historial'=>$historial]);
            }else{
              //Se deja nuevamente al cliente en estado activo para realizar un nuevo canje
              $this->procesarTransaccionNoAprobada($WebpayPago->ord_compra);
              //Cuando el estado no fue aprobado, lo redicrecciona a su vista correspondiente
              return view('webpay.webpayResponseErrors.'.$WebpayPago->estado_transaccion, ['TBK_ORDEN_COMPRA'=>$WebpayPago->ord_compra]);
            }
          }else{
            $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
            return view('webpay.end', ['TBK_ORDEN_COMPRA'=>$request->TBK_ORDEN_COMPRA]);
          }
        }else{
          $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
          return view('webpay.end', ['TBK_ORDEN_COMPRA'=>$request->TBK_ORDEN_COMPRA]);
        }
      }catch(Exception $e){
        $this->procesarTransaccionNoAprobada($request->TBK_ORDEN_COMPRA);
        return view('webpay.end', ['TBK_ORDEN_COMPRA'=>$request->TBK_ORDEN_COMPRA]);
      }
    }

    public function procesarTransaccionNoAprobada($TBK_ORDEN_COMPRA){
      try{

        $historial = HistorialCanje::select('user_rut')->where('estado','encanje')->where('ordenCompraCarrito',$TBK_ORDEN_COMPRA)->get();
        $historial = $historial[0];
        $this->CambioEstadoPorAnulacionWSCLOTPC($historial->user_rut);

      }catch(Exception $e){
         $this->procesarTransaccionNoAprobada($TBK_ORDEN_COMPRA);
        return view('webpay.end', ['TBK_ORDEN_COMPRA'=>$TBK_ORDEN_COMPRA]);
      }
    }

    public function CambioEstadoPorAnulacionWSCLOTPC($user_rut){
      try {
        //Se instancia un nuevo comunicador de webservice con SoapWrapper
        SoapWrapper::add(function ($service) {
          $service
            ->name('currency')
            ->wsdl('http://190.196.23.184/clop_otpc_web_prestashop_desa/wscl/wsclotpc_server_ps.php?wsdl')
            ->trace(true);
        });
        //Se definen los parametros que consume el webservice
        $data = [
          'usuario'=>'celmediapago',
          'password'=>'0x552A6798E1F1BCF715EFDB1E1DDC0874',
          'rut'=>$user_rut,
        ];


        // Se usa el nuevo webservice creado
        SoapWrapper::service('currency', function ($service) use ($data) {
            $service->call('CambioEstadoPorAnulacionWSCLOTPC', [$data]);
            return true;
        });

      } catch(Exception $e) {

      }
    }


    public function cert_normal(){
        return $certificate = array(

            /** Ambiente */
          "environment" => "INTEGRACION",

            /** Llave Privada */
          "private_key" => "

-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA0ClVcH8RC1u+KpCPUnzYSIcmyXI87REsBkQzaA1QJe4w/B7g
6KvKV9DaqfnNhMvd9/ypmGf0RDQPhlBbGlzymKz1xh0lQBD+9MZrg8Ju8/d1k0pI
b1QLQDnhRgR2T14ngXpP4PIQKtq7DsdHBybFU5vvAKVqdHvImZFzqexbZjXWxxhT
+/sGcD4Vs673fc6B+Xj2UrKF7QyV5pMDq0HCCLTMmafWAmNrHyl6imQM+bqC12gn
EEAEkrJiSO6P/21m9iDJs5KQanpJby0aGW8mocYRHDMHZjtTiIP0+JAJgL9KsH+r
Xdk2bT7aere7TzOK/bEwhkYEXnMMt/65vV6AfwIDAQABAoIBAHnIlOn6DTi99eXl
KVSzIb5dA747jZWMxFruL70ifM+UKSh30FGPoBP8ZtGnCiw1ManSMk6uEuSMKMEF
5iboVi4okqnTh2WSC/ec1m4BpPQqxKjlfrdTTjnHIxrZpXYNucMwkeci93569ZFR
2SY/8pZV1mBkZoG7ocLmq+qwE1EaBEL/sXMvuF/h08nJ71I4zcclpB8kN0yFrBCW
7scqOwTLiob2mmU2bFHOyyjTkGOlEsBQxhtVwVEt/0AFH/ucmMTP0vrKOA0HkhxM
oeR4k2z0qwTzZKXuEZtsau8a/9B3S3YcgoSOhRP/VdY1WL5hWDHeK8q1Nfq2eETX
jnQ4zjECgYEA7z2/biWe9nDyYDZM7SfHy1xF5Q3ocmv14NhTbt8iDlz2LsZ2JcPn
EMV++m88F3PYdFUOp4Zuw+eLJSrBqfuPYrTVNH0v/HdTqTS70R2YZCFb9g0ryaHV
TRwYovu/oQMV4LBSzrwdtCrcfUZDtqMYmmZfEkdjCWCEpEi36nlG0JMCgYEA3r49
o+soFIpDqLMei1tF+Ah/rm8oY5f4Wc82kmSgoPFCWnQEIW36i/GRaoQYsBp4loue
vyPuW+BzoZpVcJDuBmHY3UOLKr4ZldOn2KIj6sCQZ1mNKo5WuZ4YFeL5uyp9Hvio
TCPGeXghG0uIk4emSwolJVSbKSRi6SPsiANff+UCgYEAvNMRmlAbLQtsYb+565xw
NvO3PthBVL4dLL/Q6js21/tLWxPNAHWklDosxGCzHxeSCg9wJ40VM4425rjebdld
DF0Jwgnkq/FKmMxESQKA2tbxjDxNCTGv9tJsJ4dnch/LTrIcSYt0LlV9/WpN24LS
0lpmQzkQ07/YMQosDuZ1m/0CgYEAu9oHlEHTmJcO/qypmu/ML6XDQPKARpY5Hkzy
gj4ZdgJianSjsynUfsepUwK663I3twdjR2JfON8vxd+qJPgltf45bknziYWvgDtz
t/Duh6IFZxQQSQ6oN30MZRD6eo4X3dHp5eTaE0Fr8mAefAWQCoMw1q3m+ai1PlhM
uFzX4r0CgYEArx4TAq+Z4crVCdABBzAZ7GvvAXdxvBo0AhD9IddSWVTCza972wta
5J2rrS/ye9Tfu5j2IbTHaLDz14mwMXr1S4L39UX/NifLc93KHie/yjycCuu4uqNo
MtdweTnQt73lN2cnYedRUhw9UTfPzYu7jdXCUAyAD4IEjFQrswk2x04=
-----END RSA PRIVATE KEY-----


",

            /** Certificado Publico */
          "public_cert" => "

-----BEGIN CERTIFICATE-----
MIIDujCCAqICCQCZ42cY33KRTzANBgkqhkiG9w0BAQsFADCBnjELMAkGA1UEBhMC
Q0wxETAPBgNVBAgMCFNhbnRpYWdvMRIwEAYDVQQKDAlUcmFuc2JhbmsxETAPBgNV
BAcMCFNhbnRpYWdvMRUwEwYDVQQDDAw1OTcwMjAwMDA1NDExFzAVBgNVBAsMDkNh
bmFsZXNSZW1vdG9zMSUwIwYJKoZIhvcNAQkBFhZpbnRlZ3JhZG9yZXNAdmFyaW9z
LmNsMB4XDTE2MDYyMjIxMDkyN1oXDTI0MDYyMDIxMDkyN1owgZ4xCzAJBgNVBAYT
AkNMMREwDwYDVQQIDAhTYW50aWFnbzESMBAGA1UECgwJVHJhbnNiYW5rMREwDwYD
VQQHDAhTYW50aWFnbzEVMBMGA1UEAwwMNTk3MDIwMDAwNTQxMRcwFQYDVQQLDA5D
YW5hbGVzUmVtb3RvczElMCMGCSqGSIb3DQEJARYWaW50ZWdyYWRvcmVzQHZhcmlv
cy5jbDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANApVXB/EQtbviqQ
j1J82EiHJslyPO0RLAZEM2gNUCXuMPwe4OirylfQ2qn5zYTL3ff8qZhn9EQ0D4ZQ
Wxpc8pis9cYdJUAQ/vTGa4PCbvP3dZNKSG9UC0A54UYEdk9eJ4F6T+DyECrauw7H
RwcmxVOb7wClanR7yJmRc6nsW2Y11scYU/v7BnA+FbOu933Ogfl49lKyhe0MleaT
A6tBwgi0zJmn1gJjax8peopkDPm6gtdoJxBABJKyYkjuj/9tZvYgybOSkGp6SW8t
GhlvJqHGERwzB2Y7U4iD9PiQCYC/SrB/q13ZNm0+2nq3u08ziv2xMIZGBF5zDLf+
ub1egH8CAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAdgNpIS2NZFx5PoYwJZf8faze
NmKQg73seDGuP8d8w/CZf1Py/gsJFNbh4CEySWZRCzlOKxzmtPTmyPdyhObjMA8E
Adps9DtgiN2ITSF1HUFmhMjI5V7U2L9LyEdpUaieYyPBfxiicdWz2YULVuOYDJHR
n05jlj/EjYa5bLKs/yggYiqMkZdIX8NiLL6ZTERIvBa6azDKs6yDsCsnE1M5tzQI
VVEkZtEfil6E1tz8v3yLZapLt+8jmPq1RCSx3Zh4fUkxBTpUW/9SWUNEXbKK7bB3
zfB3kGE55K5nxHKfQlrqdHLcIo+vdShATwYnmhUkGxUnM9qoCDlB8lYu3rFi9w==
-----END CERTIFICATE-----


",

            /** Certificado Server */
          "webpay_cert" => "

-----BEGIN CERTIFICATE-----
MIIDKTCCAhECBFZl7uIwDQYJKoZIhvcNAQEFBQAwWTELMAkGA1UEBhMCQ0wxDjAMBgNVBAgMBUNo
aWxlMREwDwYDVQQHDAhTYW50aWFnbzEMMAoGA1UECgwDa2R1MQwwCgYDVQQLDANrZHUxCzAJBgNV
BAMMAjEwMB4XDTE1MTIwNzIwNDEwNloXDTE4MDkwMjIwNDEwNlowWTELMAkGA1UEBhMCQ0wxDjAM
BgNVBAgMBUNoaWxlMREwDwYDVQQHDAhTYW50aWFnbzEMMAoGA1UECgwDa2R1MQwwCgYDVQQLDANr
ZHUxCzAJBgNVBAMMAjEwMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAizJUWTDC7nfP
3jmZpWXFdG9oKyBrU0Bdl6fKif9a1GrwevThsU5Dq3wiRfYvomStNjFDYFXOs9pRIxqX2AWDybjA
X/+bdDTVbM+xXllA9stJY8s7hxAvwwO7IEuOmYDpmLKP7J+4KkNH7yxsKZyLL9trG3iSjV6Y6SO5
EEhUsdxoJFAow/h7qizJW0kOaWRcljf7kpqJAL3AadIuqV+hlf+Ts/64aMsfSJJA6xdbdp9ddgVF
oqUl1M8vpmd4glxlSrYmEkbYwdI9uF2d6bAeaneBPJFZr6KQqlbbrVyeJZqmMlEPy0qPco1TIxrd
EHlXgIFJLyyMRAyjX9i4l70xjwIDAQABMA0GCSqGSIb3DQEBBQUAA4IBAQBn3tUPS6e2USgMrPKp
sxU4OTfW64+mfD6QrVeBOh81f6aGHa67sMJn8FE/cG6jrUmX/FP1/Cpbpvkm5UUlFKpgaFfHv+Kg
CpEvgcRIv/OeIi6Jbuu3NrPdGPwzYkzlOQnmgio5RGb6GSs+OQ0mUWZ9J1+YtdZc+xTga0x7nsCT
5xNcUXsZKhyjoKhXtxJm3eyB3ysLNyuL/RHy/EyNEWiUhvt1SIePnW+Y4/cjQWYwNqSqMzTSW9TP
2QR2bX/W2H6ktRcLsgBK9mq7lE36p3q6c9DtZJE+xfA4NGCYWM9hd8pbusnoNO7AFxJZOuuvLZI7
JvD7YLhPvCYKry7N6x3l
-----END CERTIFICATE-----


",

            /** Codigo Comercio */
          "commerce_code" => "597020000541",

        );
    }

}
/*
 -----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA0ClVcH8RC1u+KpCPUnzYSIcmyXI87REsBkQzaA1QJe4w/B7g
6KvKV9DaqfnNhMvd9/ypmGf0RDQPhlBbGlzymKz1xh0lQBD+9MZrg8Ju8/d1k0pI
b1QLQDnhRgR2T14ngXpP4PIQKtq7DsdHBybFU5vvAKVqdHvImZFzqexbZjXWxxhT
+/sGcD4Vs673fc6B+Xj2UrKF7QyV5pMDq0HCCLTMmafWAmNrHyl6imQM+bqC12gn
EEAEkrJiSO6P/21m9iDJs5KQanpJby0aGW8mocYRHDMHZjtTiIP0+JAJgL9KsH+r
Xdk2bT7aere7TzOK/bEwhkYEXnMMt/65vV6AfwIDAQABAoIBAHnIlOn6DTi99eXl
KVSzIb5dA747jZWMxFruL70ifM+UKSh30FGPoBP8ZtGnCiw1ManSMk6uEuSMKMEF
5iboVi4okqnTh2WSC/ec1m4BpPQqxKjlfrdTTjnHIxrZpXYNucMwkeci93569ZFR
2SY/8pZV1mBkZoG7ocLmq+qwE1EaBEL/sXMvuF/h08nJ71I4zcclpB8kN0yFrBCW
7scqOwTLiob2mmU2bFHOyyjTkGOlEsBQxhtVwVEt/0AFH/ucmMTP0vrKOA0HkhxM
oeR4k2z0qwTzZKXuEZtsau8a/9B3S3YcgoSOhRP/VdY1WL5hWDHeK8q1Nfq2eETX
jnQ4zjECgYEA7z2/biWe9nDyYDZM7SfHy1xF5Q3ocmv14NhTbt8iDlz2LsZ2JcPn
EMV++m88F3PYdFUOp4Zuw+eLJSrBqfuPYrTVNH0v/HdTqTS70R2YZCFb9g0ryaHV
TRwYovu/oQMV4LBSzrwdtCrcfUZDtqMYmmZfEkdjCWCEpEi36nlG0JMCgYEA3r49
o+soFIpDqLMei1tF+Ah/rm8oY5f4Wc82kmSgoPFCWnQEIW36i/GRaoQYsBp4loue
vyPuW+BzoZpVcJDuBmHY3UOLKr4ZldOn2KIj6sCQZ1mNKo5WuZ4YFeL5uyp9Hvio
TCPGeXghG0uIk4emSwolJVSbKSRi6SPsiANff+UCgYEAvNMRmlAbLQtsYb+565xw
NvO3PthBVL4dLL/Q6js21/tLWxPNAHWklDosxGCzHxeSCg9wJ40VM4425rjebdld
DF0Jwgnkq/FKmMxESQKA2tbxjDxNCTGv9tJsJ4dnch/LTrIcSYt0LlV9/WpN24LS
0lpmQzkQ07/YMQosDuZ1m/0CgYEAu9oHlEHTmJcO/qypmu/ML6XDQPKARpY5Hkzy
gj4ZdgJianSjsynUfsepUwK663I3twdjR2JfON8vxd+qJPgltf45bknziYWvgDtz
t/Duh6IFZxQQSQ6oN30MZRD6eo4X3dHp5eTaE0Fr8mAefAWQCoMw1q3m+ai1PlhM
uFzX4r0CgYEArx4TAq+Z4crVCdABBzAZ7GvvAXdxvBo0AhD9IddSWVTCza972wta
5J2rrS/ye9Tfu5j2IbTHaLDz14mwMXr1S4L39UX/NifLc93KHie/yjycCuu4uqNo
MtdweTnQt73lN2cnYedRUhw9UTfPzYu7jdXCUAyAD4IEjFQrswk2x04=
-----END RSA PRIVATE KEY-----


",

"public_cert" => "

-----BEGIN CERTIFICATE-----
MIIDujCCAqICCQCZ42cY33KRTzANBgkqhkiG9w0BAQsFADCBnjELMAkGA1UEBhMC
Q0wxETAPBgNVBAgMCFNhbnRpYWdvMRIwEAYDVQQKDAlUcmFuc2JhbmsxETAPBgNV
BAcMCFNhbnRpYWdvMRUwEwYDVQQDDAw1OTcwMjAwMDA1NDExFzAVBgNVBAsMDkNh
bmFsZXNSZW1vdG9zMSUwIwYJKoZIhvcNAQkBFhZpbnRlZ3JhZG9yZXNAdmFyaW9z
LmNsMB4XDTE2MDYyMjIxMDkyN1oXDTI0MDYyMDIxMDkyN1owgZ4xCzAJBgNVBAYT
AkNMMREwDwYDVQQIDAhTYW50aWFnbzESMBAGA1UECgwJVHJhbnNiYW5rMREwDwYD
VQQHDAhTYW50aWFnbzEVMBMGA1UEAwwMNTk3MDIwMDAwNTQxMRcwFQYDVQQLDA5D
YW5hbGVzUmVtb3RvczElMCMGCSqGSIb3DQEJARYWaW50ZWdyYWRvcmVzQHZhcmlv
cy5jbDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANApVXB/EQtbviqQ
j1J82EiHJslyPO0RLAZEM2gNUCXuMPwe4OirylfQ2qn5zYTL3ff8qZhn9EQ0D4ZQ
Wxpc8pis9cYdJUAQ/vTGa4PCbvP3dZNKSG9UC0A54UYEdk9eJ4F6T+DyECrauw7H
RwcmxVOb7wClanR7yJmRc6nsW2Y11scYU/v7BnA+FbOu933Ogfl49lKyhe0MleaT
A6tBwgi0zJmn1gJjax8peopkDPm6gtdoJxBABJKyYkjuj/9tZvYgybOSkGp6SW8t
GhlvJqHGERwzB2Y7U4iD9PiQCYC/SrB/q13ZNm0+2nq3u08ziv2xMIZGBF5zDLf+
ub1egH8CAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAdgNpIS2NZFx5PoYwJZf8faze
NmKQg73seDGuP8d8w/CZf1Py/gsJFNbh4CEySWZRCzlOKxzmtPTmyPdyhObjMA8E
Adps9DtgiN2ITSF1HUFmhMjI5V7U2L9LyEdpUaieYyPBfxiicdWz2YULVuOYDJHR
n05jlj/EjYa5bLKs/yggYiqMkZdIX8NiLL6ZTERIvBa6azDKs6yDsCsnE1M5tzQI
VVEkZtEfil6E1tz8v3yLZapLt+8jmPq1RCSx3Zh4fUkxBTpUW/9SWUNEXbKK7bB3
zfB3kGE55K5nxHKfQlrqdHLcIo+vdShATwYnmhUkGxUnM9qoCDlB8lYu3rFi9w==
-----END CERTIFICATE-----


",

          "webpay_cert" => "

-----BEGIN CERTIFICATE-----
MIIDKTCCAhECBFZl7uIwDQYJKoZIhvcNAQEFBQAwWTELMAkGA1UEBhMCQ0wxDjAMBgNVBAgMBUNo
aWxlMREwDwYDVQQHDAhTYW50aWFnbzEMMAoGA1UECgwDa2R1MQwwCgYDVQQLDANrZHUxCzAJBgNV
BAMMAjEwMB4XDTE1MTIwNzIwNDEwNloXDTE4MDkwMjIwNDEwNlowWTELMAkGA1UEBhMCQ0wxDjAM
BgNVBAgMBUNoaWxlMREwDwYDVQQHDAhTYW50aWFnbzEMMAoGA1UECgwDa2R1MQwwCgYDVQQLDANr
ZHUxCzAJBgNVBAMMAjEwMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAizJUWTDC7nfP
3jmZpWXFdG9oKyBrU0Bdl6fKif9a1GrwevThsU5Dq3wiRfYvomStNjFDYFXOs9pRIxqX2AWDybjA
X/+bdDTVbM+xXllA9stJY8s7hxAvwwO7IEuOmYDpmLKP7J+4KkNH7yxsKZyLL9trG3iSjV6Y6SO5
EEhUsdxoJFAow/h7qizJW0kOaWRcljf7kpqJAL3AadIuqV+hlf+Ts/64aMsfSJJA6xdbdp9ddgVF
oqUl1M8vpmd4glxlSrYmEkbYwdI9uF2d6bAeaneBPJFZr6KQqlbbrVyeJZqmMlEPy0qPco1TIxrd
EHlXgIFJLyyMRAyjX9i4l70xjwIDAQABMA0GCSqGSIb3DQEBBQUAA4IBAQBn3tUPS6e2USgMrPKp
sxU4OTfW64+mfD6QrVeBOh81f6aGHa67sMJn8FE/cG6jrUmX/FP1/Cpbpvkm5UUlFKpgaFfHv+Kg
CpEvgcRIv/OeIi6Jbuu3NrPdGPwzYkzlOQnmgio5RGb6GSs+OQ0mUWZ9J1+YtdZc+xTga0x7nsCT
5xNcUXsZKhyjoKhXtxJm3eyB3ysLNyuL/RHy/EyNEWiUhvt1SIePnW+Y4/cjQWYwNqSqMzTSW9TP
2QR2bX/W2H6ktRcLsgBK9mq7lE36p3q6c9DtZJE+xfA4NGCYWM9hd8pbusnoNO7AFxJZOuuvLZI7
JvD7YLhPvCYKry7N6x3l
-----END CERTIFICATE-----
*/