<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebpayPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webpay_pagos', function (Blueprint $table) {
            $table->increments('id');
            /*
            $table->integer('pago_id');
            $table->integer('monto_puntos',10);
            $table->integer('monto_dinero',10);
            $table->integer('diferencia',10);
            $table->integer('estado_pago',2);
            $table->string('ord_compra');
            $table->string('id_sesion');
            $table->date('fh_transaccion');
            $table->string('token_ws');
            $table->string('accounting_date');
            $table->string('card_detail');
            $table->string('card_number');
            $table->string('card_expiration_date');
            $table->string('authorization_code');
            $table->string('payment_type_code');
            $table->string('response_code');
            $table->string('transaction_date');
            $table->string('vci');
            $table->string('tp_transaction');
            $table->date('tpago');
            $table->date('hora_pago');
            */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webpay_pagos');
    }
}
