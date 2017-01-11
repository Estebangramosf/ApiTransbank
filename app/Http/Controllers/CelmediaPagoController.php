<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
use App\TransactionValidation;
use App\WebpayPago;
use Carbon\Carbon;
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
   private $PrestashopController;

   public function __construct()
   {
      $this->WebserviceController = new WebserviceController();
      $this->WebpayController = new WebpayController();
      $this->ConfigController = new ConfigController();
      $this->PrestashopController = new PrestashopController();
   }

   public function getShoppingCart(Request $request)
   {
      try {
         $WebpayPago = WebpayPago::where('ord_compra', $request->TBK_ORDEN_COMPRA)->first();
         if (count($WebpayPago) > 0) {
            if ($WebpayPago->estado_transaccion != 'ApprovedTransaction') {
               $TV = TransactionValidation::where('TBK_ORDEN_COMPRA', '=',$WebpayPago->ord_compra)->first();
               if(count($TV)>0){
                  $TV->delete();
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
               return view('webpay.webpayValidations.AuthTransaction',
                  ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlApi' => $this->ConfigController->urlApi]);
            } else {
               return view('webpay.webpayValidations.TransactionAlreadyApproved',
                  ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA,'urlFracaso'=>$this->ConfigController->urlFracaso]);
            }
         } else {
            return $this->celmediaPagoPostInit($request);
         }
      } catch (Exception $e) {
         return view('webpay.webpayResponseErrors.invalidWebpayCert',
            ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }//End function getShoppingCart()
   public function celmediaPagoInit(Request $request)
   {
      try {
         $request = TransactionValidation::where('TBK_ORDEN_COMPRA', '=', $request->TBK_ORDEN_COMPRA)->first();
         if ($request->TRANSACTION_STATUS == 'ApprovedTransaction') {
            return view('webpay.webpayValidations.TransactionAlreadyApproved',
               ['urlFracaso'=>$this->ConfigController->urlFracaso]);
         }
         $WebpayPagoOld = WebpayPago::where('ord_compra', $request->TBK_ORDEN_COMPRA)->first();
         if (count($WebpayPagoOld) > 0) {
            $WebpayPagoOld->delete();
         }
         return $this->celmediaPagoPostInit($request);
      } catch (Exception $e) {
         return view('webpay.webpayResponseErrors.invalidWebpayCert',
            ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }

   public function celmediaPagoPostInit($request)
   {
      try {
         $userResult = $this->verifyRUTExistanceAndGetUser($request);
         $request->TBK_MONTO = round($request->TBK_MONTO, 0);
         if ($userResult->pts >= $request->TBK_MONTO) {
            if (count(HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first()) == 0) {
               HistorialCanje::create([
                  'user_rut' => $request->TBK_RUT,
                  'fecha_canje' => Carbon::now(),
                  'ordenCompraCarrito' => $request->TBK_ORDEN_COMPRA,
                  'estado' => 'encanje',
               ])->save();
            }
            $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first();
            if (count($historial) > 0) {
               $this->generateSwap($request->TBK_RUT, $request->TBK_MONTO, $request->TBK_OTPC_WEB, 0, $request->TBK_ORDEN_COMPRA);
               $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first();
               return view('webpay.responseCanjeNoTransbank',
                  ['historial' => $historial, 'urlExito'=>$this->ConfigController->urlExito]);
            }
            $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first();
            if (count($historial) == 0) {
               return view('webpay.canjePendiente',['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
            }
         } else {
            $total = ($request->TBK_MONTO - $userResult->pts);
            if (count(HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first()) == 0) {
               HistorialCanje::create([
                  'user_rut' => $request->TBK_RUT,
                  'fecha_canje' => Carbon::now(),
                  'puntos' => $request->TBK_MONTO,
                  'ordenCompraCarrito' => $request->TBK_ORDEN_COMPRA,
                  'estado' => 'encanje',
               ])->save();
            }
            $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first();
            $result = '';
            if (count($historial) > 0) {
               $result = $this->WebpayController->initTransaction($total * 3, $request->TBK_ORDEN_COMPRA, $request->TBK_ID_SESION);
            }
            $historial = HistorialCanje::where('ordenCompraCarrito', $request->TBK_ORDEN_COMPRA)->first();
            if (count($historial) == 0) {
               return view('webpay.canjePendiente',['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
            }
            return $result;
         }
      } catch (Exception $e) {
         return view('webpay.webpayResponseErrors.invalidWebpayCert',
            ['TBK_ORDEN_COMPRA' => $request->TBK_ORDEN_COMPRA, 'urlFracaso'=>$this->ConfigController->urlFracaso]);
      }
   }
   public function verifyRUTExistanceAndGetUser($request)
   {
      try {
         if ($request->TBK_RUT && isset($request->TBK_RUT)) {
            $this->ConsultaPuntosWSCLOTPC($request->TBK_RUT, $request->TBK_OTPC_WEB);
            $user = User::where('rut', $request->TBK_RUT)->first();
            if (count($user)>0) {
               return $user;
            } else {
               return Redirect::to($this->ConfigController->ecommerceHomeUrl);
            }
         }
      } catch (Exception $e) {
      }
   }//End function verifyRUTExistanceAndGetUser()
   public function ConsultaPuntosWSCLOTPC($rut, $otpc)
   {
      try {
         SoapWrapper::add(function ($service) {
            $service
               ->name('currency')
               ->wsdl($this->ConfigController->WebServiceServer)
               ->trace(true);
         });
         $data = [
            'rut' => $rut,
            'usuario' => $this->ConfigController->WebServiceUserCelPago,
            'origen' => $this->ConfigController->WebServiceOrigenCelPago,
            'password' => $this->ConfigController->WebServicePasswordCelPago,
            'idproveedor' => $this->ConfigController->WebServiceIdProveedorCelPago
         ];
         SoapWrapper::service('currency', function ($service) use ($data, $otpc) {
            $ClientData = $service->call('ConsultaPuntosWSCLOTPC', [$data]);
            $user = User::where('rut', $ClientData->rut)->first();
            if (isset($user->rut)) {
               $user = User::findOrFail($user->id);
               $user->pts = $ClientData->SaldoPuntos;//30000;//
               $user->otpc = $otpc;
               $user->save();
               return true;
            } else {
               User::create([
                  'name' => $ClientData->Nombre,
                  'rut' => $ClientData->rut,
                  'pts' => $ClientData->SaldoPuntos,
                  'otpc' => $otpc
               ])->save();
               return true;
            }
         });
      } catch (Exception $e) {
      }
   }//End function ConsultaPuntosWSCLOTPC()

   public function generateSwap($rut, $monto, $otpc, $copago, $ordenCompraCarrito)
   {
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
               return view('webpay.canjePendiente',
                  ['ecommerceHomeUrl' => $this->ConfigController->ecommerceHomeUrl]);
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
   }//End function generateSwap()
}
