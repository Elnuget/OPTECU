<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoToRecetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recetas', function (Blueprint $table) {
            // Agregar el campo tipo como string (varchar)
            $table->string('tipo')->nullable();
            
            // Otras opciones para el campo tipo:
            // $table->string('tipo', 50)->nullable(); // Con longitud específica
            // $table->enum('tipo', ['entrante', 'principal', 'postre'])->nullable(); // Como enum
            // $table->string('tipo')->default('principal'); // Con valor por defecto
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
}