<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakePersonalFieldsNullableInHistorialesClinicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Hacer nullable los campos de información personal
        DB::statement('ALTER TABLE historiales_clinicos MODIFY nombres VARCHAR(255) NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY apellidos VARCHAR(255) NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY edad INT NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY celular VARCHAR(20) NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY ocupacion VARCHAR(100) NULL');
        DB::statement('ALTER TABLE historiales_clinicos MODIFY fecha DATE NULL');
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
