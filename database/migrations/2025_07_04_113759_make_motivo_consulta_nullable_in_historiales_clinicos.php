<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeMotivoConsultaNullableInHistorialesClinicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Usar consultas SQL nativas para modificar la columna motivo_consulta a nullable
        DB::statement('ALTER TABLE historiales_clinicos MODIFY motivo_consulta TEXT NULL');
        
        // También hacer nullable el campo enfermedad_actual ya que podría estar relacionado
        DB::statement('ALTER TABLE historiales_clinicos MODIFY enfermedad_actual TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No hacemos nada en el down ya que estamos haciendo los campos nuleables,
        // lo cual es una operación permisiva que no requiere revertirse
    }
}
