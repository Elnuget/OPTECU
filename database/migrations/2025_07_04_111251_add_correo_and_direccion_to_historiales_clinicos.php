<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorreoAndDireccionToHistorialesClinicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historiales_clinicos', function (Blueprint $table) {
            $table->string('correo', 255)->nullable()->after('celular');
            $table->string('direccion', 255)->nullable()->after('correo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historiales_clinicos', function (Blueprint $table) {
            $table->dropColumn(['correo', 'direccion']);
        });
    }
}
