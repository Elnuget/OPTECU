<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeAntecedentesNullableInHistorialesClinicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Usar consultas SQL nativas para modificar las columnas a nullable
        DB::statement('ALTER TABLE historiales_clinicos MODIFY antecedentes_personales_oculares TEXT NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY antecedentes_personales_generales TEXT NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY antecedentes_familiares_oculares TEXT NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY antecedentes_familiares_generales TEXT NULL');
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
