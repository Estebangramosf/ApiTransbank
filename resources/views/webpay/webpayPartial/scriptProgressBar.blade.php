{{--
Vistas que comparten este módulo + script
  canjePendiente.blade.php
  end.blade.php
  exito.blade.php
  responseCanjeSiTransbank.blade.php

Y ahora todas las subvistas de la carpeta -> webpayResponseErrors
  TransactionDeclined.blade.php
  RetryTransaction.blade.php
  TransactionError.blade.php
  TransactionRejected.blade.php
  TransactionRejectedByErrorRate.blade.php
  TransactionExceedsMonthlyMaximumQuota.blade.php
  TransactionExceedsDailyLimit.blade.php
  TransactionUnauthorizedItem.blade.php

--}}

<div class="sub-title">
  <span id="wait">Espere por favor . </span><br>

  <div class="progress">
    <div id="progressBar" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
      <span class="sr-only">Espere por favor...</span>
    </div>
  </div>

  <span id="reddirect">Redireccionando en </span>
  <form id="form1" name="form1" method="get" action="http://ecorpbancadesa.celmedia.cl/">
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