@section('title') Transactions @endsection
@extends('layouts.app')
@section('content')
  <div class=" page-wrapper{{-- jumbotron --}}">
    <div class="container-fluid">
      <div class="">

        <!-- Page Heading -->
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9">
            <h1 class="page-header">
              Transacciones <small>Registro de Transacciones</small>
            </h1>
            <ol class="breadcrumb">
              <li class="active">
                <i class="fa fa-dashboard"></i> Transacciones
              </li>
            </ol>
          </div>
        </div>
        <!-- /.row -->

        <div class="row">

          <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9">
            @include('alerts.allAlerts')
          </div><!-- -->
          <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9">

            <div class="list-group">
              <div class="list-group-item">
                <h4>
                  Listado de Transacciones
                </h4>
              </div>
              <div class="list-group-item">
                <table class="table">
                  <thead>
                  <th>ID</th>
                  <th>Rut</th>
                  <th>Result Code</th>
                  <th>Fecha Canje</th>
                  <th>ID Transacción</th>
                  <th>Saldo Final</th>
                  <th>Puntos</th>
                  <th>Copago</th>
                  <th>Orden Compra Carrito</th>
                  <th>Estado</th>
                  </thead>
                  @foreach($transactions as $transaction)
                    <tbody>
                    <td>{!!$transaction->id!!}</td>
                    <td>{!!$transaction->user_rut!!}</td>
                    <td>{!!$transaction->rc!!}</td>
                    <td>{!!$transaction->fecha_canje!!}</td>
                    <td>{!!$transaction->id_transaccion!!}</td>
                    <td>{!!$transaction->saldo_final!!}</td>
                    <td>{!!$transaction->puntos!!}</td>
                    <td>{!!$transaction->copago!!}</td>
                    <td>{!!$transaction->ordenCompraCarrito!!}</td>
                    <td>{!!$transaction->estado!!}</td>
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

