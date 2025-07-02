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
            $historial = HistorialClinico::with('recetas')
                ->where($campo, $valorDecodificado)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$historial) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontraron historiales con este $campo"
                ], 404);
            }
            
            // Agregar los campos de la receta al historial
            if ($historial->recetas && $historial->recetas->count() > 0) {
                $receta = $historial->recetas->first();
                $historial->od_esfera = $receta->od_esfera;
                $historial->od_cilindro = $receta->od_cilindro;
                $historial->od_eje = $receta->od_eje;
                $historial->oi_esfera = $receta->oi_esfera;
                $historial->oi_cilindro = $receta->oi_cilindro;
                $historial->oi_eje = $receta->oi_eje;
                // Asegurarse de que ADD solo se asigna una vez, 
                // ya que en el formulario es un campo único
                if (!$historial->add && $receta->od_adicion) {
                    $historial->add = $receta->od_adicion;
                }
                $historial->dp = $receta->dp;
                $historial->observaciones = $receta->observaciones;
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