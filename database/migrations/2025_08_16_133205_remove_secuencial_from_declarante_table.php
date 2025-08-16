<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSecuencialFromDeclaranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('declarante', function (Blueprint $table) {
            $table->dropColumn('secuencial');
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
            $table->string('secuencial', 9)->nullable()->default('000000001');
        });
    }
}
