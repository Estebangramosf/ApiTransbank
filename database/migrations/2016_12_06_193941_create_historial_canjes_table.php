<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistorialCanjesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_canjes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_rut');
            $table->string('rc');
            $table->string('fecha_canje');
            $table->string('id_transaccion');
            $table->string('saldo_final');
            $table->string('puntos');
            $table->string('copago');
            $table->string('ordenCompraCarrito');
            $table->string('estado');
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
        Schema::drop('historial_canjes');
    }
}
