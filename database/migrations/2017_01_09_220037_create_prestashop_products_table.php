<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrestashopProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prestashop_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('orden_compra_id');
            $table->string('carro_id');
            $table->string('cantidad_compra');
            $table->string('estado_orden_compra');
            $table->string('producto_id');
            $table->string('stock_real');
            $table->string('estado_producto');
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
        Schema::dropIfExists('prestashop_products');
    }
}
