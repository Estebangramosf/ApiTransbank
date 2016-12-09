<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistorialCanje extends Model
{
  protected $table = "historial_canjes";
  protected $fillable = [
    'user_rut', 'rc', 'fecha_canje', 'id_transaccion', 'saldo_final', 'puntos', 'copago','ordenCompraCarrito', 'estado'
  ];

}
