<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_validations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('TBK_MONTO');
            $table->string('TBK_TIPO_TRANSACCION');
            $table->string('TBK_ORDEN_COMPRA');
            $table->string('TBK_ID_SESION');
            $table->string('TBK_RUT');
            $table->string('TBK_CORPBANCA');
            $table->string('TBK_OTPC_WEB');

            $table->string('TRANSACTION_STATUS');

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
        Schema::drop('transaction_validations');
    }
}
