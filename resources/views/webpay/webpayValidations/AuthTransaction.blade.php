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

   <!-- Latest compiled and minified CSS -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

   <!-- Optional theme -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

   <!-- Latest compiled and minified JavaScript -->
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


   <style>
      html, body {
         height: 100%;
      }

      body {
         margin: 0;
         padding: 0;
         width: 100%;
         display: table;
         font-family: 'Lato';
         font-weight: 400;
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
      <div class="title">Atención</div>

      <div class="sub-title">
         <span>
            Ya existe una transacción con esta orden de compra, <br>
            para crear una nueva transacción con esta orden de compra <br>
            presione continuar.
         </span>
         <br>

         <form id="form1" name="form1" method="post" action="{{$urlApi}}/celmediaPagoInit" autocomplete="off"  onSubmit="">
            <input type="hidden" name="TBK_ORDEN_COMPRA" value="{{$TBK_ORDEN_COMPRA}}">
            {{csrf_field()}}
            <button type="submit" class="btn btn-success btn-lg"><b>Continuar ...</b></button>
         </form>

      </div>

   </div>
</div>
</body>
</html>
