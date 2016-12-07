@section('title') Users @endsection
@extends('layouts.app')
@section('content')
  <div class=" page-wrapper{{-- jumbotron --}}">
    <div class="container-fluid">
      <div class="">

        <!-- Page Heading -->
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9">
            <h1 class="page-header">
              Usuarios <small>Registro de usuarios</small>
            </h1>
            <ol class="breadcrumb">
              <li class="active">
                <i class="fa fa-dashboard"></i> Usuarios
              </li>
            </ol>
          </div>
        </div>
        <!-- /.row -->

        <div class="row">

          <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
            @include('alerts.allAlerts')
          </div><!-- -->
          <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6">

            <div class="list-group">
              <div class="list-group-item">
                <h4>
                  Listado de Usuarios
                </h4>

              </div>
              <div class="list-group-item">
                <table class="table">
                  <thead>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Rut</th>
                  <th>Puntos</th>
                  </thead>
                  @foreach($users as $user)
                    <tbody>
                    <td>{!!$user->id!!}</td>
                    <td>{!!$user->name!!}</td>
                    <td>{!!$user->rut!!}</td>
                    <td>{!!$user->pts!!}</td>
                    </tbody>
                  @endforeach
                </table><!-- -->
                {{--{!!$roles->render()!!}--}}
              </div>
            </div>

          </div><!-- -->

          <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
            <div class="list-group">
              <div class="list-group-item">
                Espacio adicional
              </div><!-- -->
              <div class="list-group-item">
                Sugerencias, relateds, etc.
              </div><!-- -->
            </div><!-- -->
          </div><!-- -->

        </div><!-- -->
      </div><!-- -->
    </div>

  </div><!-- -->
@endsection

