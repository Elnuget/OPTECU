<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecuencialesSriTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secuenciales_sri', function (Blueprint $table) {
            $table->id();
            $table->string('secuencial', 9)->unique()->index(); // Número secuencial de 9 dígitos
            $table->string('clave_acceso', 49)->unique()->index(); // Clave de acceso completa de 49 dígitos
            $table->string('establecimiento', 3); // Código establecimiento
            $table->string('punto_emision', 3); // Código punto de emisión
            $table->string('ruc', 13); // RUC del declarante
            $table->enum('estado', ['USADO', 'DEVUELTA', 'AUTORIZADA'])->default('USADO');
            $table->unsignedBigInteger('factura_id')->nullable(); // ID de la factura asociada
            $table->date('fecha_emision'); // Fecha de emisión
            $table->json('metadata')->nullable(); // Información adicional
            $table->timestamps();
            
            // Índices adicionales para optimizar consultas
            $table->index(['ruc', 'establecimiento', 'punto_emision']);
            $table->index(['fecha_emision', 'estado']);
            $table->index('factura_id');
            
            // Clave única compuesta para evitar duplicados por establecimiento/punto
            $table->unique(['ruc', 'establecimiento', 'punto_emision', 'secuencial'], 'unique_secuencial_por_punto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secuenciales_sri');
    }
}
