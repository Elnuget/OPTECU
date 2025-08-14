<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentoToSueldosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sueldos', function (Blueprint $table) {
            $table->string('documento')->nullable()->after('valor')->comment('Ruta del documento escaneado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sueldos', function (Blueprint $table) {
            $table->dropColumn('documento');
        });
    }
}
