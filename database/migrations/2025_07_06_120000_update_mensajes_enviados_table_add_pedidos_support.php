<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mensajes_enviados', function (Blueprint $table) {
            // Eliminar la clave foránea existente
            $table->dropForeign(['historial_id']);
            
            // Agregar nuevos campos para soporte de pedidos y telemarketing
            $table->unsignedBigInteger('pedido_id')->nullable()->after('historial_id');
            $table->string('tipo_mensaje')->nullable()->after('tipo'); // nuevo campo para tipo de mensaje
            $table->unsignedBigInteger('usuario_id')->nullable()->after('fecha_envio');
            $table->unsignedBigInteger('empresa_id')->nullable()->after('usuario_id');
        });

        // Ejecutar SQL directo para hacer historial_id nullable
        DB::statement('ALTER TABLE mensajes_enviados MODIFY historial_id BIGINT UNSIGNED NULL');

        // Agregar las claves foráneas
        Schema::table('mensajes_enviados', function (Blueprint $table) {
            $table->foreign('historial_id')
                  ->references('id')
                  ->on('historiales_clinicos')
                  ->onDelete('set null');
                  
            $table->foreign('pedido_id')
                  ->references('id')
                  ->on('pedidos')
                  ->onDelete('set null');
                  
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('mensajes_enviados', function (Blueprint $table) {
            // Eliminar las claves foráneas nuevas
            $table->dropForeign(['historial_id']);
            $table->dropForeign(['pedido_id']);
            $table->dropForeign(['usuario_id']);
            $table->dropForeign(['empresa_id']);
            
            // Eliminar las nuevas columnas
            $table->dropColumn([
                'pedido_id',
                'tipo_mensaje',
                'usuario_id',
                'empresa_id'
            ]);
            
            // Restaurar historial_id como no nullable y recrear la clave foránea original
            $table->unsignedBigInteger('historial_id')->nullable(false)->change();
            $table->foreign('historial_id')
                  ->references('id')
                  ->on('historiales_clinicos')
                  ->onDelete('cascade');
        });
    }
};
