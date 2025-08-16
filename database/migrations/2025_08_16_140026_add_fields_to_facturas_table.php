<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->string('numero')->nullable()->after('xml');
            $table->date('fecha')->nullable()->after('numero');
            $table->decimal('subtotal', 15, 2)->default(0)->after('fecha');
            $table->string('estado')->default('pendiente')->after('tipo');
            $table->text('observaciones')->nullable()->after('estado');
            
            // Agregar índices
            $table->index(['numero']);
            $table->index(['fecha']);
            $table->index(['estado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Remover campos agregados
            $table->dropColumn(['numero', 'fecha', 'subtotal', 'estado', 'observaciones']);
            
            // Remover índices
            $table->dropIndex(['numero']);
            $table->dropIndex(['fecha']);
            $table->dropIndex(['estado']);
        });
    }
}
