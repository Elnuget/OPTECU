<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateDeclaranteFirmaToP12Only extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ejecutar consulta SQL directa para actualizar el comentario de la columna
        DB::statement("ALTER TABLE declarante MODIFY COLUMN firma VARCHAR(255) NULL COMMENT 'Archivo de certificado digital en formato P12/PFX para firma electrónica del SRI Ecuador'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el comentario al formato anterior
        DB::statement("ALTER TABLE declarante MODIFY COLUMN firma VARCHAR(255) NULL COMMENT 'Archivo de certificado digital en formato P12/PFX o PEM para firma electrónica'");
    }
}
