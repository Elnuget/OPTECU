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
     * Obtiene el siguiente número de orden para una empresa específica
     * 
     * @param int $empresaId ID de la empresa
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiguienteNumeroOrden($empresaId)
    {
        try {
            // Obtener el número de orden más alto de la empresa (usando CAST para asegurar comparación numérica)
            $maxNumeroOrden = Pedido::where('empresa_id', $empresaId)
                ->whereNotNull('numero_orden')
                ->where('numero_orden', '!=', '')
                ->whereRaw('CAST(numero_orden as UNSIGNED) > 0')
                ->selectRaw('MAX(CAST(numero_orden as UNSIGNED)) as max_numero')
                ->value('max_numero');
            
            // Obtener el pedido con el número de orden más alto
            $ultimoPedido = null;
            if ($maxNumeroOrden !== null) {
                $ultimoPedido = Pedido::where('empresa_id', $empresaId)
                    ->whereRaw('CAST(numero_orden as UNSIGNED) = ?', [$maxNumeroOrden])
                    ->first();
            }
            
            // Obtener información adicional para debugging
            $totalPedidosEmpresa = Pedido::where('empresa_id', $empresaId)->count();
            $empresa = \App\Models\Empresa::find($empresaId);
            
            // Calcular el siguiente número
            $siguienteNumero = $maxNumeroOrden ? $maxNumeroOrden + 1 : 1;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'siguiente_numero_orden' => $siguienteNumero,
                    'ultimo_numero_orden' => $maxNumeroOrden ?? 0,
                    'empresa_id' => $empresaId,
                    'nombre_empresa' => $empresa ? $empresa->nombre : 'Empresa no encontrada',
                    'total_pedidos_empresa' => $totalPedidosEmpresa,
                    'ultimo_pedido_id' => $ultimoPedido ? $ultimoPedido->id : null,
                    'ultimo_pedido_fecha' => $ultimoPedido ? $ultimoPedido->created_at : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener siguiente número de orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para debugging - obtiene información detallada de números de orden
     * 
     * @param int $empresaId ID de la empresa
     * @return \Illuminate\Http\JsonResponse
     */
    public function debugNumerosOrden($empresaId)
    {
        try {
            $empresa = \App\Models\Empresa::find($empresaId);
            
            // Obtener todos los números de orden de esta empresa ordenados numéricamente
            $pedidosConNumeros = Pedido::where('empresa_id', $empresaId)
                ->whereNotNull('numero_orden')
                ->where('numero_orden', '!=', '')
                ->whereRaw('CAST(numero_orden as UNSIGNED) > 0')
                ->orderByRaw('CAST(numero_orden as UNSIGNED) DESC')
                ->select('id', 'numero_orden', 'cliente', 'created_at')
                ->limit(20)
                ->get()
                ->toArray();
            
            // Obtener estadísticas usando casting numérico
            $maxNumero = Pedido::where('empresa_id', $empresaId)
                ->whereNotNull('numero_orden')
                ->where('numero_orden', '!=', '')
                ->whereRaw('CAST(numero_orden as UNSIGNED) > 0')
                ->selectRaw('MAX(CAST(numero_orden as UNSIGNED)) as max_numero')
                ->value('max_numero');
                
            $minNumero = Pedido::where('empresa_id', $empresaId)
                ->whereNotNull('numero_orden')
                ->where('numero_orden', '!=', '')
                ->whereRaw('CAST(numero_orden as UNSIGNED) > 0')
                ->selectRaw('MIN(CAST(numero_orden as UNSIGNED)) as min_numero')
                ->value('min_numero');
                
            $totalPedidos = Pedido::where('empresa_id', $empresaId)->count();
            $pedidosConNumero = Pedido::where('empresa_id', $empresaId)
                ->whereNotNull('numero_orden')
                ->where('numero_orden', '!=', '')
                ->whereRaw('CAST(numero_orden as UNSIGNED) > 0')
                ->count();
            
            // Buscar posibles duplicados
            $duplicados = Pedido::where('empresa_id', $empresaId)
                ->whereNotNull('numero_orden')
                ->where('numero_orden', '!=', '')
                ->whereRaw('CAST(numero_orden as UNSIGNED) > 0')
                ->select('numero_orden', \DB::raw('count(*) as total'))
                ->groupBy('numero_orden')
                ->having('total', '>', 1)
                ->orderByRaw('CAST(numero_orden as UNSIGNED) DESC')
                ->limit(10)
                ->get()
                ->toArray();
                
            // Buscar valores problemáticos (no numéricos)
            $valoresProblematicos = Pedido::where('empresa_id', $empresaId)
                ->where(function($query) {
                    $query->whereNull('numero_orden')
                          ->orWhere('numero_orden', '=', '')
                          ->orWhereRaw('CAST(numero_orden as UNSIGNED) = 0')
                          ->orWhereRaw('numero_orden REGEXP \'^[^0-9]+$\'');
                })
                ->select('id', 'numero_orden', 'cliente', 'created_at')
                ->limit(10)
                ->get()
                ->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'empresa_id' => $empresaId,
                    'nombre_empresa' => $empresa ? $empresa->nombre : 'Empresa no encontrada',
                    'estadisticas' => [
                        'max_numero_orden' => $maxNumero,
                        'min_numero_orden' => $minNumero,
                        'total_pedidos' => $totalPedidos,
                        'pedidos_con_numero_valido' => $pedidosConNumero,
                    ],
                    'ultimos_20_pedidos' => $pedidosConNumeros,
                    'numeros_duplicados' => $duplicados,
                    'valores_problematicos' => $valoresProblematicos,
                    'siguiente_numero_calculado' => $maxNumero ? $maxNumero + 1 : 1
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en debugging: ' . $e->getMessage()
            ], 500);
        }
    }
} 