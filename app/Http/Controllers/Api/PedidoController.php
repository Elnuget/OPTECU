<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function getPedidosPorMes(Request $request)
    {
        try {
            $query = Pedido::query()
                ->with([
                    'aInventario:id,codigo,cantidad',
                    'dInventario:id,codigo,cantidad',
                    'pagos:id,pedido_id,pago'
                ]);

            if ($request->filled('ano') && $request->filled('mes')) {
                $query->whereYear('fecha', $request->ano)
                      ->whereMonth('fecha', $request->mes);
            } else if ($request->filled('ano')) {
                $query->whereYear('fecha', $request->ano);
            }

            $pedidos = $query->select([
                'id',
                'numero_orden',
                'fecha',
                'cliente',
                'celular',
                'paciente',
                'total',
                'saldo',
                'fact',
                'usuario',
                'encuesta',
                'metodo_envio',
                'fecha_entrega'
            ])
            ->orderBy('numero_orden', 'desc')
            ->get();

            $totales = [
                'ventas' => $pedidos->sum('total'),
                'saldos' => $pedidos->sum('saldo'),
                'cobrado' => $pedidos->sum(function($pedido) {
                    return $pedido->pagos->sum('pago');
                })
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'pedidos' => $pedidos,
                    'totales' => $totales
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pedidos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene el último pedido de un cliente específico
     * 
     * @param string $cliente Nombre del cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUltimoPedidoCliente($cliente)
    {
        try {
            // Decodificar el nombre del cliente (ya que viene de una URL)
            $clienteName = urldecode($cliente);
            
            // Buscar el último pedido del cliente
            $pedido = Pedido::where('cliente', $clienteName)
                ->select([
                    'id',
                    'cliente',
                    'cedula',
                    'paciente',
                    'celular',
                    'correo_electronico'
                ])
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron pedidos para este cliente'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'pedido' => $pedido
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el último pedido: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Busca el último pedido basado en un campo y valor específicos
     * 
     * @param string $campo Campo a buscar (cliente, cedula, paciente, celular, correo)
     * @param string $valor Valor a buscar
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarPedidoPorCampo($campo, $valor)
    {
        try {
            // Decodificar el valor (ya que viene de una URL)
            $valorDecodificado = urldecode($valor);
            
            // Mapear el campo 'correo' al nombre real de la columna
            $campoReal = $campo === 'correo' ? 'correo_electronico' : $campo;
            
            // Validar que el campo sea válido
            $camposPermitidos = ['cliente', 'cedula', 'paciente', 'celular', 'correo_electronico'];
            if (!in_array($campoReal, $camposPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo de búsqueda no válido'
                ], 400);
            }
            
            // Buscar el último pedido que coincida con el campo y valor
            $pedido = Pedido::where($campoReal, $valorDecodificado)
                ->select([
                    'id',
                    'cliente',
                    'cedula',
                    'paciente',
                    'celular',
                    'correo_electronico'
                ])
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontraron pedidos con este $campo"
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'pedido' => $pedido
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar Excel de pedidos seleccionados a través de API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generarExcel(Request $request)
    {
        try {
            // Obtener IDs desde la petición
            $ids = $request->input('ids');
            
            // Validar que se reciban IDs
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se seleccionaron pedidos para generar Excel'
                ], 400);
            }

            // Convertir IDs de string a array si es necesario
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            
            // Obtener los pedidos con sus relaciones
            $pedidos = Pedido::with(['inventarios', 'lunas', 'empresa', 'pagos'])
                ->whereIn('id', $ids)
                ->select([
                    'id', 'numero_orden', 'cliente', 'cedula', 'celular', 'direccion', 
                    'correo_electronico', 'empresa_id', 'metodo_envio', 'fecha_entrega',
                    'fecha', 'total', 'saldo', 'paciente'
                ])
                ->orderBy('numero_orden', 'desc')
                ->get();

            if ($pedidos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron pedidos para generar Excel'
                ], 404);
            }

            // Formatear los datos para Excel
            $datosExcel = $pedidos->map(function($pedido) {
                return [
                    'numero_orden' => $pedido->numero_orden,
                    'fecha' => $pedido->fecha,
                    'cliente' => $pedido->cliente,
                    'paciente' => $pedido->paciente,
                    'cedula' => $pedido->cedula,
                    'celular' => $pedido->celular,
                    'direccion' => $pedido->direccion,
                    'correo_electronico' => $pedido->correo_electronico,
                    'total' => $pedido->total,
                    'saldo' => $pedido->saldo,
                    'pagado' => $pedido->pagos->sum('pago'),
                    'metodo_envio' => $pedido->metodo_envio,
                    'fecha_entrega' => $pedido->fecha_entrega,
                    'empresa' => $pedido->empresa ? $pedido->empresa->nombre : 'N/A'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $datosExcel,
                'total_pedidos' => $pedidos->count(),
                'resumen' => [
                    'total_ventas' => $pedidos->sum('total'),
                    'total_saldos' => $pedidos->sum('saldo'),
                    'total_pagado' => $pedidos->sum(function($pedido) {
                        return $pedido->pagos->sum('pago');
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}