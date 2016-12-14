<form id="form1" name="form1" method="post" action="http://ecorpbancadesa.celmedia.cl/module/celmediapago/validation" autocomplete="off"  onSubmit="">
  <input type="hidden" name="CELPAGO_TBK_OC" value="{{$historial->ordenCompraCarrito}}">
  <input type="hidden" name="CELPAGO_TBK_MONTO" value="0">
  <input type="hidden" name="CELPAGO_ID_CART" value="{{$historial->ordenCompraCarrito}}">
  <input type="hidden" name="CELPAGO_COD_CANJE" value="{{$historial->id_transaccion}}">
  <input type="hidden" name="CELPAGO_PUNTOS" value="{{$historial->puntos}}">
  <script type="text/javascript"> document.form1.submit(); </script>
</form>