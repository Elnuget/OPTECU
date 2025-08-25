<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateDeclaranteFirmaToPemFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ejecutar consulta SQL directa para actualizar el comentario de la columna
        DB::statement("ALTER TABLE declarante MODIFY COLUMN firma VARCHAR(255) NULL COMMENT 'Archivo de certificado digital en formato PEM para firma electrónica'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el comentario al formato anterior
        DB::statement("ALTER TABLE declarante MODIFY COLUMN firma VARCHAR(255) NULL COMMENT 'Archivo de certificado digital'");
    }
}
