<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordCertificadoToDeclaranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('declarante', function (Blueprint $table) {
            // Agregar campo para contraseña del certificado (encriptada)
            $table->text('password_certificado')->nullable()->after('firma')
                  ->comment('Contraseña del certificado P12 (encriptada)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('declarante', function (Blueprint $table) {
            $table->dropColumn('password_certificado');
        });
    }
}
