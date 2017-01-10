<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrestashopProductStock extends Model
{
   protected $table = "prestashop_product_stocks";
   protected $fillable = [
      'producto_id','stock_real','estado_producto'
   ];


}
