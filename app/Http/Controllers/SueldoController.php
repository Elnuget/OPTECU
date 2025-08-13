<?php

namespace App\Http\Controllers;

use App\Models\Sueldo;
use App\Models\Egreso;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SueldoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Obtener todos los sueldos sin filtros
        $query = Sueldo::with(['user', 'empresa']);
        
        $sueldos = $query->orderBy('fecha', 'desc')->get();
        $totalSueldos = $sueldos->sum('valor');
        
        // Obtener todas las empresas para referencia (no se usará para filtros)
        $empresas = Empresa::orderBy('nombre')->get();
        
        return view('sueldos.index', compact('sueldos', 'totalSueldos', 'empresas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('sueldos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'empresa_id' => 'nullable|exists:empresas,id',
                'fecha' => 'required|date',
                'descripcion' => 'required|string',
                'valor' => 'required|numeric'
            ]);

            $sueldo = Sueldo::create([
                'user_id' => $request->user_id,
                'empresa_id' => $request->empresa_id,
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'valor' => $request->valor
            ]);

            // Crear el registro de egreso correspondiente
            $egreso = new Egreso();
            $egreso->user_id = $request->user_id;
            $egreso->valor = $request->valor;
            $egreso->motivo = $request->descripcion;
            $egreso->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'SUELDO REGISTRADO CORRECTAMENTE',
                'data' => $sueldo
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar sueldo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL REGISTRAR EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Sueldo $sueldo)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $sueldo->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Sueldo $sueldo)
    {
        return view('sueldos.edit', compact('sueldo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'empresa_id' => 'nullable|exists:empresas,id',
                'fecha' => 'required|date',
                'descripcion' => 'required|string',
                'valor' => 'required|numeric'
            ]);

            $sueldo = Sueldo::findOrFail($id);
            
            $sueldo->update([
                'user_id' => $request->user_id,
                'empresa_id' => $request->empresa_id,
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'valor' => $request->valor
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'SUELDO ACTUALIZADO CORRECTAMENTE',
                'data' => $sueldo
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar sueldo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL ACTUALIZAR EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $sueldo = Sueldo::findOrFail($id);
            $sueldo->delete();

            return response()->json([
                'success' => true,
                'mensaje' => 'SUELDO ELIMINADO CORRECTAMENTE'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar sueldo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL ELIMINAR EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guarda un valor de sueldo vía AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function guardarValor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha' => 'required|date',
                'valor' => 'required|numeric|min:0',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'DATOS INVÁLIDOS',
                    'errores' => $validator->errors()
                ], 422);
            }

            // Buscar si existe un registro para esta fecha y usuario
            $sueldo = Sueldo::where('user_id', $request->user_id)
                           ->where('fecha', $request->fecha)
                           ->where('descripcion', 'REGISTROCOBRO')
                           ->first();

            if ($sueldo) {
                // Si existe, actualizar el valor
                $sueldo->valor = $request->valor;
                $sueldo->save();
            } else {
                // Si no existe, crear nuevo registro
                $sueldo = Sueldo::create([
                    'fecha' => $request->fecha,
                    'descripcion' => 'REGISTROCOBRO',
                    'valor' => $request->valor,
                    'user_id' => $request->user_id
                ]);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'VALOR GUARDADO CORRECTAMENTE',
                'data' => $sueldo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL GUARDAR EL VALOR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los sueldos con descripción REGISTROCOBRO
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegistrosCobro(Request $request)
    {
        try {
            $query = Sueldo::where('descripcion', 'REGISTROCOBRO');
            
            // Filtros por año y mes
            if ($request->has('ano')) {
                $query->whereYear('fecha', $request->ano);
            }
            
            if ($request->has('mes')) {
                $query->whereMonth('fecha', $request->mes);
            }
            
            // Filtros opcionales
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            if ($request->has('fecha_inicio')) {
                $query->where('fecha', '>=', $request->fecha_inicio);
            }
            
            if ($request->has('fecha_fin')) {
                $query->where('fecha', '<=', $request->fecha_fin);
            }
            
            $registros = $query->with('user')
                             ->orderBy('fecha', 'desc')
                             ->get();
            
            return response()->json([
                'success' => true,
                'data' => $registros,
                'total' => $registros->sum('valor')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER LOS REGISTROS DE COBRO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el total de registros de cobro para un usuario en un período específico
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalRegistrosCobro(Request $request)
    {
        try {
            $query = Sueldo::where('descripcion', 'REGISTROCOBRO');
            
            // Filtros por año y mes
            if ($request->has('ano')) {
                $query->whereYear('fecha', $request->ano);
            }
            
            if ($request->has('mes')) {
                $query->whereMonth('fecha', $request->mes);
            }
            
            // Filtro por usuario
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            $total = $query->sum('valor');
            
            return response()->json([
                'success' => true,
                'total' => $total
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER EL TOTAL DE REGISTROS DE COBRO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los datos necesarios para el rol de pagos de forma local
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDatosRolPagos(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'ano' => 'required|integer',
                'mes' => 'required|string|size:2',
                'empresa_id' => 'nullable|exists:empresas,id'
            ]);

            $userId = $request->user_id;
            $ano = $request->ano;
            $mes = $request->mes;
            $empresaId = $request->empresa_id;

            // Obtener datos de pedidos locales
            // IMPORTANTE: En la tabla pedidos, el campo que identifica al vendedor es 'usuario' (no user_id)
            $pedidosQuery = DB::table('pedidos')
                ->where('usuario', $userId) // El usuario corresponde al vendedor del pedido
                ->whereYear('fecha', $ano)
                ->whereMonth('fecha', $mes);
            
            if ($empresaId) {
                $pedidosQuery->where('empresa_id', $empresaId);
            }
            
            $pedidos = $pedidosQuery->leftJoin('empresas', 'pedidos.empresa_id', '=', 'empresas.id')
                ->select(
                    'pedidos.*',
                    'empresas.nombre as empresa'
                )
                ->get();

            // Obtener datos de egresos/retiros locales
            $retirosQuery = DB::table('egresos')
                ->where('user_id', $userId)
                ->whereYear('egresos.created_at', $ano)
                ->whereMonth('egresos.created_at', $mes)
                ->where(function($query) {
                    $query->where('motivo', 'NOT LIKE', '%deposito%')
                          ->where('motivo', 'NOT LIKE', '%depósito%');
                });
            
            if ($empresaId) {
                $retirosQuery->where('empresa_id', $empresaId);
            }
            
            $retiros = $retirosQuery->leftJoin('empresas', 'egresos.empresa_id', '=', 'empresas.id')
                ->select(
                    'egresos.*',
                    'empresas.nombre as empresa',
                    'egresos.created_at as fecha',
                    'egresos.motivo',
                    'egresos.valor'
                )
                ->get();

            // Obtener movimientos de caja locales (si existe la tabla)
            $movimientos = [];
            if (DB::getSchemaBuilder()->hasTable('cash_history')) {
                $movimientosQuery = DB::table('cash_history')
                    ->where('user_id', $userId)
                    ->whereYear('cash_history.created_at', $ano)
                    ->whereMonth('cash_history.created_at', $mes);
                
                if ($empresaId) {
                    $movimientosQuery->where('empresa_id', $empresaId);
                }
                
                $movimientos = $movimientosQuery->leftJoin('empresas', 'cash_history.empresa_id', '=', 'empresas.id')
                    ->select(
                        'cash_history.*',
                        'empresas.nombre as empresa',
                        'cash_history.created_at as fecha',
                        DB::raw("CASE WHEN cash_history.estado = 'apertura' THEN 'Apertura' ELSE 'Cierre' END as descripcion"),
                        'cash_history.monto'
                    )
                    ->get();
            }

            // Obtener registros de cobro
            $registrosCobro = Sueldo::where('user_id', $userId)
                ->where('descripcion', 'REGISTROCOBRO')
                ->whereYear('fecha', $ano)
                ->whereMonth('fecha', $mes)
                ->get();
                
            // Calcular otros sueldos (no REGISTROCOBRO)
            $otrosSueldos = Sueldo::where('user_id', $userId)
                ->where('descripcion', '!=', 'REGISTROCOBRO')
                ->whereYear('fecha', $ano)
                ->whereMonth('fecha', $mes);
                
            if ($empresaId) {
                $otrosSueldos->where('empresa_id', $empresaId);
            }
            
            $otrosSueldos = $otrosSueldos->with('empresa')->get();

            // Calcular totales
            $pedidos_total = $pedidos->sum('total');
            $retiros_total = $retiros->sum('valor');
            $registros_cobro_total = $registrosCobro->sum('valor');
            $otros_sueldos_total = $otrosSueldos->sum('valor');
            
            // Calcular saldo final
            $saldo_final = $pedidos_total - $retiros_total - $otros_sueldos_total;

            return response()->json([
                'success' => true,
                'data' => [
                    'pedidos' => $pedidos,
                    'pedidos_total' => $pedidos_total,
                    'retiros' => $retiros,
                    'retiros_total' => $retiros_total,
                    'movimientos' => $movimientos,
                    'historial' => [
                        'ingresos' => $movimientos->where('estado', 'apertura')->sum('monto'),
                        'egresos' => $movimientos->where('estado', '!=', 'apertura')->sum('monto')
                    ],
                    'registros_cobro' => $registrosCobro,
                    'registros_cobro_total' => $registros_cobro_total,
                    'otros_sueldos' => $otrosSueldos,
                    'otros_sueldos_total' => $otros_sueldos_total,
                    'saldo_final' => $saldo_final
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener datos de rol de pagos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER LOS DATOS DEL ROL DE PAGOS: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene solo los pedidos de un usuario en un mes y año específicos
     * Método simplificado para evitar problemas con joins complejos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPedidosUsuario(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'ano' => 'required|integer',
                'mes' => 'required|string|size:2'
            ]);

            $userId = $request->user_id;
            $ano = $request->ano;
            $mes = $request->mes;

            // Consulta simple solo para pedidos, sin joins complejos
            $pedidos = DB::table('pedidos')
                ->where('usuario', $userId)
                ->whereYear('fecha', $ano)
                ->whereMonth('fecha', $mes)
                ->select('pedidos.*')
                ->get();

            // Calcular el total de pedidos
            $pedidos_total = $pedidos->sum('total');

            return response()->json([
                'success' => true,
                'data' => [
                    'pedidos' => $pedidos,
                    'pedidos_total' => $pedidos_total
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener pedidos de usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER PEDIDOS: ' . $e->getMessage()
            ], 500);
        }
    }
} 