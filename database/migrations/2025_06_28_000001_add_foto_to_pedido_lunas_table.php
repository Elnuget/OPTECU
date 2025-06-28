<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFotoToPedidoLunasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedido_lunas', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('l_precio_descuento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedido_lunas', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
}
