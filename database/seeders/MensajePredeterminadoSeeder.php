<?php

namespace Database\Seeders;

use App\Models\MensajePredeterminado;
use Illuminate\Database\Seeder;

class MensajePredeterminadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MensajePredeterminado::create([
            'tipo' => 'cumpleanos',
            'mensaje' => '¡Feliz Cumpleaños! 🎉
Queremos desearte un día muy especial.

Te recordamos que puedes aprovechar nuestro descuento especial de cumpleaños en tu próxima compra.

¡Que tengas un excelente día!'
        ]);
    }
} 