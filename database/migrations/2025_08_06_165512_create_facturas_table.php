<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('declarante_id');
            $table->longText('xml')->nullable();
            $table->decimal('monto', 15, 2);
            $table->decimal('iva', 15, 2);
            $table->string('tipo');
            $table->timestamps();
            $table->softDeletes();

            // Índices y claves foráneas
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('declarante_id')->references('id')->on('declarante')->onDelete('cascade');
            
            // Índices para mejorar performance
            $table->index(['pedido_id']);
            $table->index(['declarante_id']);
            $table->index(['tipo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facturas');
    }
}
