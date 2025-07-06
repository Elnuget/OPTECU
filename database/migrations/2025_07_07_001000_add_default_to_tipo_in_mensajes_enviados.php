<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modificar el campo tipo para darle un valor predeterminado
        DB::statement("ALTER TABLE `mensajes_enviados` MODIFY `tipo` VARCHAR(255) NOT NULL DEFAULT 'telemarketing'");
    }

    public function down()
    {
        // Revertir a la definición original (sin valor predeterminado)
        DB::statement("ALTER TABLE `mensajes_enviados` MODIFY `tipo` VARCHAR(255) NOT NULL");
    }
};
