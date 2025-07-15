<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PopulateEmpresaIdInEgresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Actualizar los egresos existentes con la empresa_id del usuario
        DB::statement('
            UPDATE egresos 
            SET empresa_id = (
                SELECT empresa_id 
                FROM users 
                WHERE users.id = egresos.user_id
            )
            WHERE empresa_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Limpiar los empresa_id asignados
        DB::table('egresos')->update(['empresa_id' => null]);
    }
}
