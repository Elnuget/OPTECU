<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SimplifyFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Eliminar campos agregados que no necesitamos
            if (Schema::hasColumn('facturas', 'numero')) {
                $table->dropColumn('numero');
            }
            if (Schema::hasColumn('facturas', 'fecha')) {
                $table->dropColumn('fecha');
            }
            if (Schema::hasColumn('facturas', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('facturas', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('facturas', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
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
            // Reagregar campos si necesitamos revertir
            $table->string('numero')->nullable()->after('xml');
            $table->date('fecha')->nullable()->after('numero');
            $table->decimal('subtotal', 15, 2)->default(0)->after('fecha');
            $table->string('estado')->default('pendiente')->after('tipo');
            $table->text('observaciones')->nullable()->after('estado');
            
            // Reagregar índices
            $table->index(['numero']);
            $table->index(['fecha']);
            $table->index(['estado']);
        });
    }
}
