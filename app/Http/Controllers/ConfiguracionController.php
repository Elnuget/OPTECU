<?php

namespace App\Http\Controllers;

use App\Models\MensajePredeterminado;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Guarda un mensaje predeterminado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guardarMensajePredeterminado(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string',
            'mensaje' => 'required|string'
        ]);

        try {
            $mensajePredeterminado = MensajePredeterminado::create([
                'tipo' => $request->tipo,
                'mensaje' => $request->mensaje
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Mensaje predeterminado guardado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al guardar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }
} 