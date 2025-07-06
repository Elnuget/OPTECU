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
            // Buscar si ya existe un mensaje del mismo tipo y actualizarlo, o crear uno nuevo
            $mensajePredeterminado = MensajePredeterminado::where('tipo', $request->tipo)->first();
            
            if ($mensajePredeterminado) {
                $mensajePredeterminado->update(['mensaje' => $request->mensaje]);
            } else {
                $mensajePredeterminado = MensajePredeterminado::create([
                    'tipo' => $request->tipo,
                    'mensaje' => $request->mensaje
                ]);
            }

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

    /**
     * Obtiene un mensaje predeterminado por tipo
     *
     * @param  string  $tipo
     * @return \Illuminate\Http\Response
     */
    public function obtenerMensajePredeterminado($tipo)
    {
        try {
            $mensaje = MensajePredeterminado::where('tipo', $tipo)
                ->latest()
                ->first();

            if ($mensaje) {
                return response()->json([
                    'success' => true,
                    'mensaje' => $mensaje->mensaje
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'mensaje' => null
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }
}