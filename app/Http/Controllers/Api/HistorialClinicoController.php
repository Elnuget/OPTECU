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
            $historial = HistorialClinico::with(['recetas' => function($query) {
                $query->orderBy('created_at', 'desc');
            }, 'empresa']) // Incluir la relación con empresa
            ->where($campo, $valorDecodificado)
            ->orderBy('created_at', 'desc')
            ->first();
                
            if (!$historial) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontraron historiales con este $campo"
                ], 404);
            }

            // Si tiene recetas, agregar los datos de la última receta al objeto historial
            if ($historial->recetas && $historial->recetas->count() > 0) {
                $ultimaReceta = $historial->recetas->first();
                
                // Agregar los campos de la receta directamente al historial para el frontend
                $historial->od_esfera = $ultimaReceta->od_esfera;
                $historial->od_cilindro = $ultimaReceta->od_cilindro;
                $historial->od_eje = $ultimaReceta->od_eje;
                $historial->od_adicion = $ultimaReceta->od_adicion;
                $historial->oi_esfera = $ultimaReceta->oi_esfera;
                $historial->oi_cilindro = $ultimaReceta->oi_cilindro;
                $historial->oi_eje = $ultimaReceta->oi_eje;
                $historial->oi_adicion = $ultimaReceta->oi_adicion;
                
                // Asegurarse de que ADD solo se asigna una vez
                if (!$historial->add && $ultimaReceta->od_adicion) {
                    $historial->add = $ultimaReceta->od_adicion;
                }
                
                $historial->dp = $ultimaReceta->dp;
                $historial->observaciones = $ultimaReceta->observaciones;
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