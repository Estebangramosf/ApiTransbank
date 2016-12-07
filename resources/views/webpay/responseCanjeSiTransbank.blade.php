{{--
{#168 ▼
  +"id": 3
  +"user_rut": "116138328"
  +"rc": "00"
  +"fecha_canje": "Dec  7 2016 12:59:20:507PM"
  +"id_transaccion": "22567530"
  +"saldo_final": "0"
  +"puntos": "100000"
  +"copago": "460956"
  +"ordenCompraCarrito": "101"
  +"estado": "encanje"
  +"created_at": "2016-12-07 15:59:26"
  +"updated_at": "2016-12-07 15:59:26"
}
--}}

<!DOCTYPE html>
<html>
<head>
  <title>ApiTransbank</title>

  <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
  <!-- Latest compiled and minified CSS -->


  <script
    src="https://code.jquery.com/jquery-1.12.4.js"
    integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
    crossorigin="anonymous"></script>
  <style>
    html, body {
      height: 100%;
    }

    body {
      margin: 0;
      padding: 0;
      width: 100%;
      display: table;
      font-weight: 200;
      font-family: 'Lato';
    }

    .container {
      text-align: center;
      display: table-cell;
      vertical-align: middle;
    }

    .content {
      text-align: center;
      display: inline-block;
    }

    .title {
      font-size: 96px;
    }

    .sub-title {
      font-size: 26px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="content">
    <div class="title">Transacción exitosa{{--ApiTransbank--}}</div>

    <div class="sub-title">
      <span id="wait">Espere por favor . </span><br>
      <span id="reddirect"></span>

      <form id="form1" name="form1" method="post" action="http://ecorpbancadesa.celmedia.cl/module/celmediapago/validation" autocomplete="off"  onSubmit="">
        <p>Tu clave ha sido enviada exit&oacute;samente</p>
        <input type="hidden" name="CELPAGO_TBK_OC" value="{{$historial->ordenCompraCarrito}}">
        <input type="hidden" name="CELPAGO_TBK_MONTO" value="{{$historial->copago}}">
        <input type="hidden" name="CELPAGO_ID_CART" value="{{$historial->ordenCompraCarrito}}">
        <input type="hidden" name="CELPAGO_COD_CANJE" value="{{$historial->id_transaccion}}">
        <input type="hidden" name="CELPAGO_PUNTOS" value="{{$historial->puntos}}">

      </form>

      {{-- <a href="http://ecorpbancadesa.celmedia.cl/" class="btn btn-primary">Volver</a> --}}

      <script>
        $(function () {

          var timeReddirect = 3000;

          setInterval(function(){
            console.log('Redireccionando en => '+(timeReddirect/1000));
            $('#reddirect').text('Redireccionando en '+(timeReddirect/1000));
            $('#wait').text() +' .';
            timeReddirect -= 1000;
            if(timeReddirect==0){reddirect();}
            return true;
          }, 1000);

          function reddirect(){
            timeReddirect = 3000;
            document.form1.submit();
          }

        });
      </script>

    </div>
  </div>
</div>
</body>
</html>
