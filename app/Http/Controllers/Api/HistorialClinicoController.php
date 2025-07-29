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

            // Si tiene recetas, agregar todas las recetas y los datos de la última al objeto historial
            if ($historial->recetas && $historial->recetas->count() > 0) {
                $ultimaReceta = $historial->recetas->first();
                
                // Agregar los campos de la receta más reciente directamente al historial para compatibilidad
                $historial->od_esfera = $ultimaReceta->od_esfera;
                $historial->od_cilindro = $ultimaReceta->od_cilindro;
                $historial->od_eje = $ultimaReceta->od_eje;
                $historial->od_adicion = $ultimaReceta->od_adicion;
                $historial->oi_esfera = $ultimaReceta->oi_esfera;
                $historial->oi_cilindro = $ultimaReceta->oi_cilindro;
                $historial->oi_eje = $ultimaReceta->oi_eje;
                $historial->oi_adicion = $ultimaReceta->oi_adicion;
                $historial->tipo = $ultimaReceta->tipo;
                
                // Asegurarse de que ADD solo se asigna una vez
                if (!$historial->add && $ultimaReceta->od_adicion) {
                    $historial->add = $ultimaReceta->od_adicion;
                }
                
                $historial->dp = $ultimaReceta->dp;
                $historial->observaciones = $ultimaReceta->observaciones;
                
                // Agregar array con todas las recetas para múltiples recetas
                $historial->todasLasRecetas = $historial->recetas->map(function($receta) {
                    return [
                        'id' => $receta->id,
                        'tipo' => $receta->tipo,
                        'od_esfera' => $receta->od_esfera,
                        'od_cilindro' => $receta->od_cilindro,
                        'od_eje' => $receta->od_eje,
                        'od_adicion' => $receta->od_adicion,
                        'oi_esfera' => $receta->oi_esfera,
                        'oi_cilindro' => $receta->oi_cilindro,
                        'oi_eje' => $receta->oi_eje,
                        'oi_adicion' => $receta->oi_adicion,
                        'dp' => $receta->dp,
                        'observaciones' => $receta->observaciones,
                        'created_at' => $receta->created_at
                    ];
                });
                
                $historial->cantidadRecetas = $historial->recetas->count();
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

    /**
     * Busca historiales clínicos por nombre completo (nombres + apellidos)
     * 
     * @param string $nombreCompleto Nombre completo a buscar
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarPorNombreCompleto($nombreCompleto)
    {
        try {
            // Decodificar el valor (ya que viene de una URL)
            $valorDecodificado = urldecode($nombreCompleto);
            
            // Buscar por concatenación de nombres y apellidos (en ambos órdenes posibles)
            $historial = HistorialClinico::with(['recetas' => function($query) {
                $query->orderBy('created_at', 'desc');
            }, 'empresa'])
            ->whereRaw("CONCAT(nombres, ' ', apellidos) = ?", [$valorDecodificado])
            ->orWhereRaw("CONCAT(apellidos, ' ', nombres) = ?", [$valorDecodificado])
            ->orderBy('created_at', 'desc')
            ->first();

            // Si no se encuentra con concatenación exacta, buscar con LIKE para coincidencias parciales
            if (!$historial) {
                $historial = HistorialClinico::with(['recetas' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }, 'empresa'])
                ->whereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%$valorDecodificado%"])
                ->orWhereRaw("CONCAT(apellidos, ' ', nombres) LIKE ?", ["%$valorDecodificado%"])
                ->orderBy('created_at', 'desc')
                ->first();
            }
                
            if (!$historial) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontraron historiales con el nombre completo: $valorDecodificado"
                ], 404);
            }

            // Si tiene recetas, agregar todas las recetas y los datos de la última al objeto historial
            if ($historial->recetas && $historial->recetas->count() > 0) {
                $ultimaReceta = $historial->recetas->first();
                
                // Agregar los campos de la receta más reciente directamente al historial para compatibilidad
                $historial->od_esfera = $ultimaReceta->od_esfera;
                $historial->od_cilindro = $ultimaReceta->od_cilindro;
                $historial->od_eje = $ultimaReceta->od_eje;
                $historial->od_adicion = $ultimaReceta->od_adicion;
                $historial->oi_esfera = $ultimaReceta->oi_esfera;
                $historial->oi_cilindro = $ultimaReceta->oi_cilindro;
                $historial->oi_eje = $ultimaReceta->oi_eje;
                $historial->oi_adicion = $ultimaReceta->oi_adicion;
                $historial->tipo = $ultimaReceta->tipo;
                
                // Asegurarse de que ADD solo se asigna una vez
                if (!$historial->add && $ultimaReceta->od_adicion) {
                    $historial->add = $ultimaReceta->od_adicion;
                }
                
                $historial->dp = $ultimaReceta->dp;
                $historial->observaciones = $ultimaReceta->observaciones;
                
                // Agregar array con todas las recetas para múltiples recetas
                $historial->todasLasRecetas = $historial->recetas->map(function($receta) {
                    return [
                        'id' => $receta->id,
                        'tipo' => $receta->tipo,
                        'od_esfera' => $receta->od_esfera,
                        'od_cilindro' => $receta->od_cilindro,
                        'od_eje' => $receta->od_eje,
                        'od_adicion' => $receta->od_adicion,
                        'oi_esfera' => $receta->oi_esfera,
                        'oi_cilindro' => $receta->oi_cilindro,
                        'oi_eje' => $receta->oi_eje,
                        'oi_adicion' => $receta->oi_adicion,
                        'dp' => $receta->dp,
                        'observaciones' => $receta->observaciones,
                        'created_at' => $receta->created_at
                    ];
                });
                
                $historial->cantidadRecetas = $historial->recetas->count();
            }
            
            return response()->json([
                'success' => true,
                'historial' => $historial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar historial por nombre completo: ' . $e->getMessage()
            ], 500);
        }
    }
}