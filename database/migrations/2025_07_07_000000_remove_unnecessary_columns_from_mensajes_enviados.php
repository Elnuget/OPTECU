<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Solo procedemos a eliminar las columnas si existen
        Schema::table('mensajes_enviados', function (Blueprint $table) {
            // Eliminar las columnas no deseadas
            if (Schema::hasColumn('mensajes_enviados', 'cliente_id')) {
                $table->dropColumn('cliente_id');
            }
            
            if (Schema::hasColumn('mensajes_enviados', 'tipo_cliente')) {
                $table->dropColumn('tipo_cliente');
            }
            
            if (Schema::hasColumn('mensajes_enviados', 'nombres')) {
                $table->dropColumn('nombres');
            }
            
            if (Schema::hasColumn('mensajes_enviados', 'apellidos')) {
                $table->dropColumn('apellidos');
            }
            
            if (Schema::hasColumn('mensajes_enviados', 'celular')) {
                $table->dropColumn('celular');
            }
        });
    }

    public function down()
    {
        // Si se necesita revertir, volver a agregar las columnas
        Schema::table('mensajes_enviados', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('tipo_cliente')->nullable(); // 'cliente' o 'paciente'
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('celular')->nullable();
        });
    }
};
