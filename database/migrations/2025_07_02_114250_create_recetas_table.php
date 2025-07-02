<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historial_clinico_id')->constrained('historiales_clinicos')->onDelete('cascade');
            $table->string('od_esfera')->nullable();
            $table->string('od_cilindro')->nullable();
            $table->string('od_eje')->nullable();
            $table->string('od_adicion')->nullable();
            $table->string('oi_esfera')->nullable();
            $table->string('oi_cilindro')->nullable();
            $table->string('oi_eje')->nullable();
            $table->string('oi_adicion')->nullable();
            $table->string('dp')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recetas');
    }
}
