<form id="form1" name="form1" method="post" action="http://ecorpbancadesa.celmedia.cl/modules/celmediapago/webpay_exito.php" autocomplete="off"  onSubmit="">
  <input type="hidden" name="TBK_MONTO" value="{{$request['TBK_MONTO']}}">
  <input type="hidden" name="TBK_ORDEN_COMPRA" value="{{$request['TBK_ORDEN_COMPRA']}}">
  <script type="text/javascript"> document.form1.submit(); </script>
</form>