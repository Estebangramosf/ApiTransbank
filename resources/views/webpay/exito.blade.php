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
      .<br>
      <span id="reddirect"></span>
      <form id="form1" name="form1" method="get" action="http://ecorpbancadesa.celmedia.cl/">
      </form>
      
      {{-- <a href="http://ecorpbancadesa.celmedia.cl/" class="btn btn-primary">Volver</a> --}}

      <script>
        $(function () {

          var timeReddirect = 3000;

          setInterval(function(){
            console.log('Redireccionando en => '+(timeReddirect/1000));
            $('#reddirect').text('Redireccionando en '+(timeReddirect/1000));
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
