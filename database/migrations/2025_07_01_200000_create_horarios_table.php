<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->time('hora_entrada');
            $table->time('hora_salida');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            // Índices y claves foráneas
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->index('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('horarios');
    }
}
