<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeclaranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('declarante')) {
            Schema::create('declarante', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('ruc')->unique();
                $table->text('firma')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('declarante')) {
            Schema::dropIfExists('declarante');
        }
    }
}
