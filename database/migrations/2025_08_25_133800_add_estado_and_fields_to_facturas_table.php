<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoAndFieldsToFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Estado de la factura
            $table->enum('estado', [
                'CREADA',           // Factura creada, XML generado
                'FIRMADA',          // Factura firmada digitalmente
                'ENVIADA',          // Enviada al SRI
                'RECIBIDA',         // Recibida por el SRI (autorizada)
                'DEVUELTA',         // Devuelta por el SRI (con errores)
                'AUTORIZADA',       // Autorizada por el SRI
                'NO_AUTORIZADA'     // No autorizada por el SRI
            ])->default('CREADA')->after('tipo');
            
            // Campos relacionados con la firma digital
            $table->string('xml_firmado', 500)->nullable()->after('xml');
            
            // Campos relacionados con el SRI
            $table->enum('estado_sri', ['RECIBIDA', 'DEVUELTA', 'AUTORIZADA', 'NO_AUTORIZADA'])->nullable()->after('estado');
            $table->string('numero_autorizacion', 100)->nullable()->after('estado_sri');
            $table->timestamp('fecha_autorizacion')->nullable()->after('numero_autorizacion');
            $table->text('mensajes_sri')->nullable()->after('fecha_autorizacion'); // Para almacenar errores o mensajes del SRI
            
            // Campos de seguimiento
            $table->timestamp('fecha_firma')->nullable()->after('mensajes_sri');
            $table->timestamp('fecha_envio_sri')->nullable()->after('fecha_firma');
            
            // Información adicional
            $table->string('clave_acceso', 49)->nullable()->after('fecha_envio_sri'); // Clave de acceso del SRI
            $table->text('observaciones')->nullable()->after('clave_acceso');
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
            $table->dropColumn([
                'estado',
                'xml_firmado',
                'estado_sri',
                'numero_autorizacion',
                'fecha_autorizacion',
                'mensajes_sri',
                'fecha_firma',
                'fecha_envio_sri',
                'clave_acceso',
                'observaciones'
            ]);
        });
    }
}
