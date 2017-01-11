@section('title') Payments @endsection
@extends('layouts.app')
@section('content')
   <div class=" page-wrapper{{-- jumbotron --}}">
      <div class="container-fluid">
         <div class="">

            <!-- Page Heading -->
            <div class="row">
               <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                  <h1 class="page-header">
                     Pagos <small>Registro de Pagos y Estados de Pago</small>
                  </h1>
                  <ol class="breadcrumb">
                     <li class="active">
                        <i class="fa fa-dashboard"></i> Pagos
                     </li>
                  </ol>
               </div>
            </div>
            <!-- /.row -->

            <div class="row">

               <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                  @include('alerts.allAlerts')
               </div><!-- -->
               <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                  <div class="list-group">
                     <div class="list-group-item">
                        <h4>
                           Listado de Pagos y Estados de Pago
                        </h4>
                     </div>
                     <div class="list-group-item">
                        <table class="table">
                           <thead>
                           <th>ID</th>
                           <th>Id Pago</th>
                           <th>Monto Puntos</th>
                           <th>Monto Dinero</th>
                           <th>Diferencia</th>
                           <th>Estado Pago</th>
                           <th>Orden de Compra</th>
                           <th>Id Sesión</th>
                           {{--<th>FH Transacción</th>--}}
                           {{--<th>Token WS</th>--}}
                           <th>Accounting Date</th>
                           <th>Card Detail</th>
                           <th>Card Number</th>
                           <th>Card Exp. Date</th>
                           <th>Auth. Code</th>
                           <th>Payment Type Code</th>
                           <th>Resp. Code</th>
                           <th>Commerce Code</th>
                           {{--<th>Transaction Date</th>--}}
                           <th>VCI</th>
                           <th>TP Transaction</th>
                           <th>Estado Transacción</th>
                           <th>Fecha Registro</th>
                           </thead>
                           @foreach($payments as $payment)
                              <tbody>
                              <td>{!!$payment->id!!}</td>
                              <td>{!!$payment->pago_id!!}</td>
                              <td>{!!$payment->monto_puntos!!}</td>
                              <td>{!!$payment->monto_dinero!!}</td>
                              <td>{!!$payment->diferencia!!}</td>
                              <td>{!!$payment->estado_pago!!}</td>
                              <td>{!!$payment->ord_compra!!}</td>
                              <td>{!!$payment->id_sesion!!}</td>
                              {{--<td>{!!$payment->fh_transaccion!!}</td>--}}
                              {{--<td>{!!$payment->token_ws!!}</td>--}}
                              <td>{!!$payment->accounting_date!!}</td>
                              <td>{!!$payment->card_detail!!}</td>
                              <td>{!!$payment->card_number!!}</td>
                              <td>{!!$payment->card_expiration_date!!}</td>
                              <td>{!!$payment->authorization_code!!}</td>
                              <td>{!!$payment->payment_type_code!!}</td>
                              <td>{!!$payment->response_code!!}</td>
                              <td>{!!$payment->commerce_code!!}</td>
                              {{--<td>{!!$payment->transaction_date!!}</td>--}}
                              <td>{!!$payment->vci!!}</td>
                              <td>{!!$payment->tp_transaction!!}</td>
                              <td>{!!$payment->estado_transaccion!!}</td>
                              <td>{!!$payment->created_at!!}</td>

                              </tbody>
                           @endforeach
                        </table><!-- -->
                        {{--{!!$roles->render()!!}--}}
                     </div>
                  </div>

               </div><!-- -->

            </div><!-- -->
         </div><!-- -->
      </div>

   </div><!-- -->
@endsection
