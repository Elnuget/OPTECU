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
                'encuesta'
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
} 