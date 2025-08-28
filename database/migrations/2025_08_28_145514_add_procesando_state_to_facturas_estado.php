<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcesandoStateToFacturasEstado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modificar el enum del campo estado para incluir PROCESANDO y ERROR
        \DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM(
            'CREADA',
            'PROCESANDO',
            'FIRMADA',
            'ENVIADA',
            'RECIBIDA',
            'DEVUELTA',
            'AUTORIZADA',
            'NO_AUTORIZADA',
            'ERROR'
        ) DEFAULT 'CREADA'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Volver al enum original
        \DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM(
            'CREADA',
            'FIRMADA',
            'ENVIADA',
            'RECIBIDA',
            'DEVUELTA',
            'AUTORIZADA',
            'NO_AUTORIZADA'
        ) DEFAULT 'CREADA'");
    }
}
