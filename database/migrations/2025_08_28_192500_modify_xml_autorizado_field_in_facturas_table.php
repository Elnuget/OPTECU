<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyXmlAutorizadoFieldInFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Usar SQL directo para evitar problemas con DBAL
        DB::statement('ALTER TABLE facturas MODIFY COLUMN xml_autorizado LONGTEXT NULL COMMENT "XML autorizado completo por el SRI con firmas digitales"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE facturas MODIFY COLUMN xml_autorizado LONGTEXT NULL');
    }
}
