<!DOCTYPE html>
<html>
<head>
   <title>Example Ajax</title>


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

   <meta name="csrf_token" content="{{ csrf_token() }}" />

</head>
<body>
<div class="container">
   <div class="content">
      <div class="title">Example Ajax</div>




      <div>

         <div class="container">
            <div class="content">
               <div class="row">
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                     <form method="post" action="">

                        <input type="text" name="cardNumber" id="cardNumber" value="123123">
                        <input type="hidden" value="{{csrf_token()}}" name="token" id="token">
                        <span id="button" class="btn btn-default btn-md button">Validar</span>



                     </form>

                  </div>
               </div>
            </div>
         </div>



         <script>
            $(document).ready(function(){
               $('#button').click(function(){

                  validateCorpbancaCard();

               });


               function validateCorpbancaCard(){
                  var token = $("#token").val();
                  var cardNumber = $("#cardNumber").val();
                  $.ajax({
                     url: "http://dev.apitransbank.com/validateCorpbancaCard",
                     type: 'post',
                     headers: {'X-CSRF-TOKEN': token},
                     beforeSend: function (xhr) {
                        var token = $('meta[name="csrf_token"]').attr('content');

                        if (token) {
                           return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                        }
                     },
                     dataType: 'json',
                     data:{
                        cardNumber:cardNumber
                     },
                     success: function success(data) {
                        console.log(data);
                     },
                     error: function error(xhr, textStatus, errorThrown) {
                        //alert('Remote sever unavailable. Please try later');
                     }
                  });
                  return true;
               }




               return true;
            });

         </script>



      </div>


      </div>
   </div>
</div>



</body>


</html>


