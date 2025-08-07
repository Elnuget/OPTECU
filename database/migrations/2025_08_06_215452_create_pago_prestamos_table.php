<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoPrestamosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pago_prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id'); // Usuario que registra el pago
            $table->decimal('valor', 10, 2);
            $table->date('fecha_pago');
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'pagado', 'cancelado'])->default('pagado');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('prestamo_id')->references('id')->on('prestamos')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Índices para mejor rendimiento
            $table->index(['prestamo_id', 'fecha_pago']);
            $table->index(['empresa_id', 'fecha_pago']);
            $table->index('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pago_prestamos');
    }
}
