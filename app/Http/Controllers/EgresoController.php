<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EgresoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin')->only(['edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        try {
            $query = Egreso::with(['user', 'empresa']);

            // Obtener año y mes actual como valores por defecto
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes', date('n'));
            $empresa = $request->get('empresa');

            // Aplicar filtros usando los valores por defecto o los proporcionados
            $query->whereYear('created_at', $ano)
                  ->whereMonth('created_at', $mes);

            // Filtrar por empresa si se especifica
            if ($empresa) {
                $query->where('empresa_id', $empresa);
            }

            $egresos = $query->orderBy('created_at', 'desc')->get();

            // Calcular totales
            $totales = [
                'egresos' => $egresos->sum('valor')
            ];

            return view('egresos.index', compact('egresos', 'totales'));
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@index: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al cargar los egresos: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function create()
    {
        return view('egresos.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric|min:0',
                'motivo' => 'required|string|max:255',
                'usuario' => 'required_if:motivo,PAGO DE SUELDO|exists:users,id',
                'empresa_id' => 'nullable|exists:empresas,id'
            ]);

            $egreso = new Egreso();
            $targetUserId = $request->motivo === 'PAGO DE SUELDO' ? $request->usuario : auth()->id();
            $egreso->user_id = $targetUserId;
            // Asegurar que el valor se maneje como decimal
            $egreso->valor = $request->valor;
            
            // Asignar empresa
            if ($request->motivo === 'PAGO DE SUELDO') {
                // Para pago de sueldo, usar la empresa del usuario al que se le paga
                $targetUser = \App\Models\User::find($targetUserId);
                if ($targetUser && $targetUser->empresa_id) {
                    $egreso->empresa_id = $targetUser->empresa_id;
                }
            } else {
                // Para otros egresos, usar la empresa seleccionada o la del usuario actual
                $egreso->empresa_id = $request->empresa_id ?: auth()->user()->empresa_id;
            }
            
            // Si es pago de sueldo, generar motivo con mes y año
            if ($request->motivo === 'PAGO DE SUELDO') {
                $mes = $request->get('mes_pedidos', date('n'));
                $ano = $request->get('ano_pedidos', date('Y'));
                
                $meses = [
                    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
                ];
                
                $nombreMes = $meses[$mes] ?? 'MES_DESCONOCIDO';
                $egreso->motivo = "SUELDO ({$nombreMes} {$ano})";
            } else {
                $egreso->motivo = strtoupper($request->motivo);
            }
            
            $egreso->save();

            return redirect()->route('egresos.index')->with([
                'error' => 'Exito',
                'mensaje' => $request->motivo === 'PAGO DE SUELDO' ? 'Sueldo pagado exitosamente' : 'Egreso registrado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@store: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al registrar el egreso: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function show(Egreso $egreso)
    {
        $egreso->load(['user', 'empresa']);
        return view('egresos.show', compact('egreso'));
    }

    public function edit(Egreso $egreso)
    {
        $egreso->load(['user', 'empresa']);
        return view('egresos.edit', compact('egreso'));
    }

    public function update(Request $request, Egreso $egreso)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric|min:0',
                'motivo' => 'required|string|max:255',
                'empresa_id' => 'nullable|exists:empresas,id'
            ]);

            // Asegurar que el valor se maneje como decimal
            $egreso->valor = $request->valor;
            $egreso->motivo = strtoupper($request->motivo);
            $egreso->empresa_id = $request->empresa_id;
            $egreso->save();

            return redirect()->route('egresos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Egreso actualizado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@update: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al actualizar el egreso: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function destroy(Egreso $egreso)
    {
        try {
            $egreso->delete();

            return redirect()->route('egresos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Egreso eliminado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@destroy: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al eliminar el egreso: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function finanzas()
    {
        // Obtener empresas para el filtro
        $empresas = \App\Models\Empresa::all();
        
        return view('egresos.finanzas2', compact('empresas'));
    }

    public function getDatosFinancieros(Request $request)
    {
        try {
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes');
            $empresaId = $request->get('empresa');

            // Construir consulta base para ingresos (pedidos)
            $queryIngresos = \App\Models\Pedido::whereYear('fecha', $ano);
            
            if ($mes) {
                $queryIngresos->whereMonth('fecha', $mes);
            }
            
            if ($empresaId) {
                $queryIngresos->where('empresa_id', $empresaId);
            }

            // Construir consulta base para egresos
            $queryEgresos = Egreso::whereYear('created_at', $ano);
            
            if ($mes) {
                $queryEgresos->whereMonth('created_at', $mes);
            }
            
            if ($empresaId) {
                $queryEgresos->where('empresa_id', $empresaId);
            }

            // Calcular totales
            $totalIngresos = $queryIngresos->sum('total');
            $totalEgresos = $queryEgresos->sum('valor');
            $ganancia = $totalIngresos - $totalEgresos;
            $margen = $totalIngresos > 0 ? ($ganancia / $totalIngresos) * 100 : 0;

            return response()->json([
                'ingresos' => $totalIngresos,
                'egresos' => $totalEgresos,
                'ganancia' => $ganancia,
                'margen' => round($margen, 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener datos financieros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getGraficosFinancieros(Request $request)
    {
        try {
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes');
            $empresaId = $request->get('empresa');

            // Datos para gráfico de ingresos vs egresos por mes
            if ($mes) {
                // Si hay mes específico, mostrar datos diarios
                $ingresosPorDia = [];
                $egresosPorDia = [];
                
                $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
                
                for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                    $fecha = "$ano-$mes-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
                    
                    $queryIngresos = \App\Models\Pedido::whereDate('fecha', $fecha);
                    $queryEgresos = Egreso::whereDate('created_at', $fecha);
                    
                    if ($empresaId) {
                        $queryIngresos->where('empresa_id', $empresaId);
                        $queryEgresos->where('empresa_id', $empresaId);
                    }
                    
                    $ingresosPorDia[] = $queryIngresos->sum('total');
                    $egresosPorDia[] = $queryEgresos->sum('valor');
                }
                
                $labels = range(1, $diasEnMes);
            } else {
                // Si no hay mes específico, mostrar datos mensuales
                $ingresosPorMes = [];
                $egresosPorMes = [];
                
                for ($mesNum = 1; $mesNum <= 12; $mesNum++) {
                    $queryIngresos = \App\Models\Pedido::whereYear('fecha', $ano)
                                                     ->whereMonth('fecha', $mesNum);
                    $queryEgresos = Egreso::whereYear('created_at', $ano)
                                         ->whereMonth('created_at', $mesNum);
                    
                    if ($empresaId) {
                        $queryIngresos->where('empresa_id', $empresaId);
                        $queryEgresos->where('empresa_id', $empresaId);
                    }
                    
                    $ingresosPorMes[] = $queryIngresos->sum('total');
                    $egresosPorMes[] = $queryEgresos->sum('valor');
                }
                
                $ingresosPorDia = $ingresosPorMes;
                $egresosPorDia = $egresosPorMes;
                $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            }

            // Datos para distribución de egresos
            $queryEgresos = Egreso::whereYear('created_at', $ano);
            
            if ($mes) {
                $queryEgresos->whereMonth('created_at', $mes);
            }
            
            if ($empresaId) {
                $queryEgresos->where('empresa_id', $empresaId);
            }

            $distribucionEgresos = $queryEgresos->selectRaw('motivo, SUM(valor) as total')
                                               ->groupBy('motivo')
                                               ->orderByDesc('total')
                                               ->limit(10)
                                               ->get();

            return response()->json([
                'ingresoEgreso' => [
                    'labels' => $labels,
                    'ingresos' => $ingresosPorDia,
                    'egresos' => $egresosPorDia
                ],
                'distribucion' => [
                    'labels' => $distribucionEgresos->pluck('motivo')->toArray(),
                    'data' => $distribucionEgresos->pluck('total')->toArray()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener datos de gráficos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMovimientosRecientes(Request $request)
    {
        try {
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes');
            $empresaId = $request->get('empresa');

            $movimientos = collect();

            // Obtener ingresos (pedidos) recientes
            $queryIngresos = \App\Models\Pedido::with(['empresa'])
                                              ->whereYear('fecha', $ano);
            
            if ($mes) {
                $queryIngresos->whereMonth('fecha', $mes);
            }
            
            if ($empresaId) {
                $queryIngresos->where('empresa_id', $empresaId);
            }

            $ingresos = $queryIngresos->orderByDesc('fecha')
                                     ->limit(20)
                                     ->get()
                                     ->map(function ($pedido) {
                                         return [
                                             'fecha' => $pedido->fecha,
                                             'tipo' => 'Ingreso',
                                             'concepto' => 'Pedido #' . $pedido->numero_orden . ' - ' . $pedido->cliente,
                                             'usuario' => $pedido->usuario ?? 'N/A',
                                             'empresa' => $pedido->empresa->nombre ?? 'N/A',
                                             'monto' => $pedido->total,
                                             'created_at' => $pedido->created_at
                                         ];
                                     });

            // Obtener egresos recientes
            $queryEgresos = Egreso::with(['user', 'empresa'])
                                 ->whereYear('created_at', $ano);
            
            if ($mes) {
                $queryEgresos->whereMonth('created_at', $mes);
            }
            
            if ($empresaId) {
                $queryEgresos->where('empresa_id', $empresaId);
            }

            $egresos = $queryEgresos->orderByDesc('created_at')
                                   ->limit(20)
                                   ->get()
                                   ->map(function ($egreso) {
                                       return [
                                           'fecha' => $egreso->created_at->format('Y-m-d'),
                                           'tipo' => 'Egreso',
                                           'concepto' => $egreso->motivo,
                                           'usuario' => $egreso->user->name ?? 'N/A',
                                           'empresa' => $egreso->empresa->nombre ?? 'N/A',
                                           'monto' => -$egreso->valor,
                                           'created_at' => $egreso->created_at
                                       ];
                                   });

            // Combinar y ordenar por fecha
            $movimientos = $ingresos->concat($egresos)
                                  ->sortByDesc('created_at')
                                  ->take(50)
                                  ->values();

            return response()->json($movimientos);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener movimientos recientes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPedidosPorUsuario(Request $request)
    {
        try {
            $usuarioId = $request->get('usuario_id');
            $mes = $request->get('mes');
            $ano = $request->get('ano');

            if (!$usuarioId || !$mes || !$ano) {
                return response()->json([
                    'total_pedidos' => 0,
                    'total_valor' => 0,
                    'mensaje' => 'Parámetros incompletos'
                ]);
            }

            // Obtener el nombre del usuario
            $usuario = \App\Models\User::find($usuarioId);
            if (!$usuario) {
                return response()->json([
                    'total_pedidos' => 0,
                    'total_valor' => 0,
                    'mensaje' => 'Usuario no encontrado'
                ]);
            }

            // Buscar pedidos por el nombre del usuario
            $query = Pedido::where('usuario', $usuario->name)
                           ->whereYear('fecha', $ano)
                           ->whereMonth('fecha', $mes);

            $pedidos = $query->get();
            $totalPedidos = $pedidos->count();
            $totalValor = $pedidos->sum('total');

            return response()->json([
                'total_pedidos' => $totalPedidos,
                'total_valor' => $totalValor,
                'mensaje' => 'Datos obtenidos correctamente',
                'usuario_nombre' => $usuario->name
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@getPedidosPorUsuario: ' . $e->getMessage());
            return response()->json([
                'total_pedidos' => 0,
                'total_valor' => 0,
                'mensaje' => 'Error al obtener los datos: ' . $e->getMessage()
            ]);
        }
    }

    public function getUltimoSueldoUsuario(Request $request)
    {
        try {
            $usuarioId = $request->get('usuario_id');
            
            if (!$usuarioId) {
                return response()->json([
                    'ultimo_sueldo' => null,
                    'mensaje' => 'Usuario no especificado'
                ]);
            }

            // Obtener el nombre del usuario
            $usuario = \App\Models\User::find($usuarioId);
            if (!$usuario) {
                return response()->json([
                    'ultimo_sueldo' => null,
                    'mensaje' => 'Usuario no encontrado'
                ]);
            }

            // Buscar el último egreso de sueldo para este usuario
            $ultimoSueldo = Egreso::where('user_id', $usuarioId)
                                  ->where('motivo', 'LIKE', 'SUELDO%')
                                  ->orderBy('created_at', 'desc')
                                  ->first();

            return response()->json([
                'ultimo_sueldo' => $ultimoSueldo ? $ultimoSueldo->valor : null,
                'mensaje' => $ultimoSueldo ? 'Último sueldo encontrado' : 'No hay sueldos anteriores'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@getUltimoSueldoUsuario: ' . $e->getMessage());
            return response()->json([
                'ultimo_sueldo' => null,
                'mensaje' => 'Error al obtener el último sueldo'
            ]);
        }
    }
}