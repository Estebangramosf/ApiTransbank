<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrestashopProduct extends Model
{
   protected $table = "prestashop_products";
   protected $fillable = [
      'orden_compra_id','carro_id','cantidad_compra','estado_orden_compra','producto_id','stock_real','estado_producto'
   ];
}
