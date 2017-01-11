<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionValidation extends Model
{
    protected $table = "transaction_validations";
    protected $fillable = [
       'TBK_MONTO',
       'TBK_TIPO_TRANSACCION',
       'TBK_ORDEN_COMPRA',
       'TBK_ID_SESION',
       'TBK_RUT',
       'TBK_CORPBANCA',
       'TBK_OTPC_WEB',
       'STATUS_TRANSACTION',
    ];



}
