<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToDeclaranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('declarante', function (Blueprint $table) {
            $table->string('direccion_matriz')->nullable()->after('ruc');
            $table->string('establecimiento', 3)->default('001')->after('direccion_matriz');
            $table->string('punto_emision', 3)->default('001')->after('establecimiento');
            $table->boolean('obligado_contabilidad')->default(true)->after('punto_emision');
            $table->string('secuencial', 9)->default('000000001')->after('obligado_contabilidad');
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
            $table->dropColumn([
                'direccion_matriz',
                'establecimiento', 
                'punto_emision',
                'obligado_contabilidad',
                'secuencial'
            ]);
        });
    }
}
