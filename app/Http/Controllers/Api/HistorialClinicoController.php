<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HistorialClinico;

class HistorialClinicoController extends Controller
{
    /**
     * Busca el historial clínico más reciente basado en un campo y valor específicos
     * 
     * @param string $campo Campo a buscar (nombres, apellidos, cedula, celular)
     * @param string $valor Valor a buscar
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarPorCampo($campo, $valor)
    {
        try {
            // Decodificar el valor (ya que viene de una URL)
            $valorDecodificado = urldecode($valor);
            
            // Validar que el campo sea válido
            $camposPermitidos = ['nombres', 'apellidos', 'cedula', 'celular'];
            if (!in_array($campo, $camposPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo de búsqueda no válido'
                ], 400);
            }
            
            // Buscar el último historial que coincida con el campo y valor
            $historial = HistorialClinico::where($campo, $valorDecodificado)
                ->select([
                    'id',
                    'nombres',
                    'apellidos',
                    'cedula',
                    'edad',
                    'fecha_nacimiento',
                    'celular',
                    'ocupacion'
                ])
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$historial) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontraron historiales con este $campo"
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'historial' => $historial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar historial: ' . $e->getMessage()
            ], 500);
        }
    }
} 