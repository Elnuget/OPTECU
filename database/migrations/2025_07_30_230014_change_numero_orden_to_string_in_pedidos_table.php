<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeNumeroOrdenToStringInPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar el tipo de columna usando SQL crudo
        DB::statement('ALTER TABLE pedidos MODIFY COLUMN numero_orden VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el cambio usando SQL crudo
        DB::statement('ALTER TABLE pedidos MODIFY COLUMN numero_orden INT NULL');
    }
}
