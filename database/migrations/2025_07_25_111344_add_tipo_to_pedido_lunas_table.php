<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoToPedidoLunasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedido_lunas', function (Blueprint $table) {
            // Agregar el campo tipo como string (varchar)
            $table->string('tipo')->nullable()->after('pedido_id');
            
            // Otras opciones para el campo tipo:
            // $table->string('tipo', 50)->nullable(); // Con longitud específica
            // $table->enum('tipo', ['CERCA', 'LEJOS'])->nullable(); // Como enum
            // $table->string('tipo')->default('LEJOS'); // Con valor por defecto
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
            $table->dropColumn('tipo');
        });
    }
}
