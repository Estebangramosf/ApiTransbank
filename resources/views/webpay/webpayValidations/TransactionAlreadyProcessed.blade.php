<!DOCTYPE html>
<html>
<head>
   <title>Transacción ya aprobada</title>

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
      <div class="title">Orden de compra ya procesada</div>

      <div class="sub-title">
         <span id="wait">Utilice una nueva orden de compra para generar la compra</span><br>

         <div class="progress">
            <div id="progressBar" class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
               <span class="sr-only">Espere por favor...</span>
            </div>
         </div>

         <span id="reddirect">Redireccionando en </span>
         <form id="form1" name="form1" method="get" action="{{$ecommerceHomeUrl}}">
         </form>


         <script>
            $(function () {

               var timeVisualReddirect = 5000;
               var timeReddirect = 3000;

               setInterval(function(){
                  console.log('Redireccionando dentro de => '+(timeVisualReddirect/1000));
                  $('#reddirect').text('Redireccionando dentro de '+(timeVisualReddirect/1000));
                  var wait = $('#wait').text() +' .';
                  $('#wait').text(wait);
                  timeVisualReddirect -= 1000;
                  timeReddirect -= 1000;
                  if(timeReddirect==0){reddirect();}
                  return true;
               }, 1000);

               var width = 0;
               setInterval(function(){
                  width += 0.5;
                  console.log();
                  $('#progressBar').attr('style','width:'+width+'%;');


               },8);

               function reddirect(){
                  timeReddirect = 3000;
                  console.log('Redireccionando');
                  return document.form1.submit();
               }

            });
         </script>


      </div>
   </div>
</div>
</body>
</html>