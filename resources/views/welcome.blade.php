@section('title') ApiTransbank @endsection
@extends('layouts.app')
@section('content')
    <div class=" page-wrapper{{-- jumbotron --}}">
        <div class="container-fluid">
            <div class="">
                <div class="container">
                    <div class="content" style="padding-top: 50px;">
                        <div class="title">ApiTransbank</div>

                        <div class="sub-title">
                            Métodos de la Api <br>

                            <div class="well">
                                <strong>Route::</strong><i style="color:orange;">get</i><b>(</b>'<a href="#!">webpaynormal</a>', 'WebpayController@index'<b>);</b><br>
                            </div>

                            <div class="well">
                                <strong>Route::</strong><i style="color:orange;">post</i><b>(</b>'<a href="#!">getResult</a>', 'WebpayController@getResult'<b>);</b><br>
                            </div>

                            <div class="well">
                                <strong>Route::</strong><i style="color:orange;">post</i><b>(</b>'<a href="#!">end</a>', 'WebpayController@end'<b>);</b><br>
                            </div>

                            <div class="well">
                                <strong>Route::</strong><i style="color:orange;">post</i><b>(</b>'<a href="#!">getShoppingCart</a>','WebpayController@getShoppingCart'<b>);</b><br>
                            </div>
                        </div>

                    </div>
                </div>
            </div><!-- -->
        </div>

    </div><!-- -->
@endsection


