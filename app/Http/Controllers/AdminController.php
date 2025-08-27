<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\Empresa;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Primero verificar la conexión a la base de datos
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                \Log::error('Error de conexión a la base de datos: ' . $e->getMessage());
                return back()->with([
                    'error' => 'Error de Conexión',
                    'mensaje' => 'No se pudo conectar a la base de datos. Por favor, contacte al administrador.',
                    'tipo' => 'alert-danger'
                ]);
            }

            $hoy = Carbon::now('America/Guayaquil');
            
            // Obtener el año y mes seleccionados o usar los actuales
            $selectedYear = $request->get('year', $hoy->year);
            $selectedMonth = $request->get('month', $hoy->month);

            // Inicializar variables con valores por defecto
            $pedidos = collect();
            $salesData = ['years' => [$hoy->year], 'totals' => [0]];
            $salesDataMonthly = ['months' => [], 'totals' => []];
            $userSalesData = [
                'users' => ['Sin datos'],
                'totals' => [0],
                'quantities' => [0]  // Valor por defecto para cantidades
            ];
            $ventasPorLugar = collect([(object)[
                'lugar' => 'Sin datos',
                'cantidad_vendida' => 0,
                'total_ventas' => 0
            ]]);
            $rankingPedidosUsuario = [
                'usuarios' => ['Sin datos'],
                'pedidos' => [0],
                'ventas' => [0]
            ];
            $rankingVentasEmpresa = [
                'empresas' => ['Sin datos'],
                'pedidos' => [0],
                'ventas' => [0]
            ];

            // Obtener pedidos calificados
            $pedidosCalificados = Pedido::whereNotNull('calificacion')
                ->select('id', 'cliente', 'usuario', 'calificacion', 'comentario_calificacion')
                ->whereYear('fecha', $selectedYear)
                ->when($selectedMonth, function($query) use ($selectedMonth) {
                    return $query->whereMonth('fecha', $selectedMonth);
                })
                ->orderBy('fecha', 'desc')
                ->get();

            try {
                // Obtener datos de ventas por usuario incluyendo cantidades (incluyendo administradores)
                $users = DB::table('pedidos')
                    ->join('users', 'pedidos.usuario', '=', 'users.name')
                    ->select(
                        'pedidos.usuario',
                        DB::raw('SUM(pedidos.total) as total_ventas'),
                        DB::raw('COUNT(*) as total_cantidad') // Usamos COUNT como alternativa si no existe columna cantidad
                    )
                    // Removido el filtro where('users.is_admin', false) para incluir administradores
                    ->whereYear('pedidos.fecha', $selectedYear)
                    ->when($selectedMonth, function($query) use ($selectedMonth) {
                        return $query->whereMonth('pedidos.fecha', $selectedMonth);
                    })
                    ->groupBy('pedidos.usuario')
                    ->orderBy('total_ventas', 'desc')
                    ->get();

                if ($users->isNotEmpty()) {
                    $userSalesData = [
                        'users' => $users->pluck('usuario')->toArray(),
                        'totals' => $users->pluck('total_ventas')->toArray(),
                        'quantities' => $users->pluck('total_cantidad')->toArray()
                    ];
                }

                // Construir la consulta base con los filtros
                $query = Pedido::query();
                $query->whereYear('fecha', $selectedYear);
                
                if ($selectedMonth) {
                    $query->whereMonth('fecha', $selectedMonth);
                }

                $pedidos = $query->orderBy('fecha', 'desc')
                    ->take(10)
                    ->get();

                $salesData = $this->getSalesData();
                $salesDataMonthly = $this->getMonthlySalesData($selectedYear);
                $ventasPorLugar = $this->getVentasPorLugar($selectedYear, $selectedMonth);
                $datosGraficoPuntuaciones = $this->getDatosGraficoPuntuaciones($selectedYear, $selectedMonth);
                $rankingPedidosUsuario = $this->getRankingPedidosUsuario($selectedYear, $selectedMonth);
                $rankingVentasEmpresa = $this->getRankingVentasEmpresa($selectedYear, $selectedMonth);

            } catch (\Exception $e) {
                \Log::error('Error obteniendo datos de ventas: ' . $e->getMessage());
                // Continuamos con los valores por defecto
            }

            return view('admin.index', compact(
                'pedidos',
                'salesData',
                'salesDataMonthly',
                'userSalesData',
                'selectedYear',
                'selectedMonth',
                'ventasPorLugar',
                'datosGraficoPuntuaciones',
                'pedidosCalificados',
                'rankingPedidosUsuario',
                'rankingVentasEmpresa'
            ));

        } catch (\Exception $e) {
            \Log::error('Error general en AdminController@index: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al cargar el dashboard. Por favor, intente de nuevo más tarde.',
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function getSalesData()
    {
        $salesData = Pedido::select(
            DB::raw('YEAR(fecha) as year'),
            DB::raw('SUM(total) as total')
        )
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->pluck('total', 'year')
        ->toArray();

        return [
            'years' => array_keys($salesData) ?: [now()->year],
            'totals' => array_values($salesData) ?: [0]
        ];
    }

    private function getMonthlySalesData($year)
    {
        $salesDataMonthly = Pedido::whereYear('fecha', $year)
            ->select(
                DB::raw('MONTH(fecha) as month'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Asegurar que tenemos datos para todos los meses
        $months = [];
        $totals = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $months[] = DateTime::createFromFormat('!m', $i)->format('F');
            $totals[] = $salesDataMonthly[$i] ?? 0;
        }

        return [
            'months' => $months,
            'totals' => $totals
        ];
    }

    private function getUserSalesData($year = null, $month = null)
    {
        $query = Pedido::select(
            'pedidos.usuario',
            DB::raw('SUM(pedidos.total) as total')
        )
        ->join('users', 'pedidos.usuario', '=', 'users.name')
        ->whereNotNull('pedidos.usuario');
        // Removido el filtro where('users.is_admin', false) para incluir administradores

        if ($year) {
            $query->whereYear('pedidos.fecha', $year);
        }
        
        if ($month) {
            $query->whereMonth('pedidos.fecha', $month);
        }

        $userSalesData = $query->groupBy('pedidos.usuario')
            ->orderBy('total', 'desc')
            ->get()
            ->pluck('total', 'usuario')
            ->toArray();

        return [
            'users' => array_keys($userSalesData) ?: ['Sin ventas'],
            'totals' => array_values($userSalesData) ?: [0]
        ];
    }

    private function getVentasPorLugar($year, $month = null)
    {
        try {
            $query = DB::table('pedido_inventario as pi')
                ->join('inventarios as i', 'pi.inventario_id', '=', 'i.id')
                ->join('pedidos as p', 'pi.pedido_id', '=', 'p.id')
                ->select('i.lugar', 
                        DB::raw('COUNT(*) as cantidad_vendida'),
                        DB::raw('SUM(pi.precio) as total_ventas'))
                ->whereYear('p.fecha', $year);

            if ($month) {
                $query->whereMonth('p.fecha', $month);
            }

            $result = $query->whereNotNull('i.lugar')
                ->groupBy('i.lugar')
                ->orderBy('cantidad_vendida', 'desc')
                ->get();

            return $result->isEmpty() ? collect([(object)[
                'lugar' => 'Sin ventas',
                'cantidad_vendida' => 0,
                'total_ventas' => 0
            ]]) : $result;

        } catch (\Exception $e) {
            \Log::error('Error en getVentasPorLugar: ' . $e->getMessage());
            return collect([(object)[
                'lugar' => 'Error al cargar datos',
                'cantidad_vendida' => 0,
                'total_ventas' => 0
            ]]);
        }
    }

    private function getDatosGraficoPuntuaciones($year, $month = null)
    {
        try {
            $query = Pedido::select(
                'pedidos.usuario',
                DB::raw('AVG(pedidos.calificacion) as promedio_calificacion'),
                DB::raw('COUNT(pedidos.calificacion) as total_calificaciones')
            )
            ->join('users', 'pedidos.usuario', '=', 'users.name')
            ->whereNotNull('pedidos.calificacion')
            ->whereNotNull('pedidos.usuario')
            // Removido el filtro where('users.is_admin', false) para incluir administradores
            ->whereYear('pedidos.fecha', $year);

            if ($month) {
                $query->whereMonth('pedidos.fecha', $month);
            }

            $resultados = $query->groupBy('pedidos.usuario')
                ->having('total_calificaciones', '>', 0)
                ->orderBy('total_calificaciones', 'desc') // Ordenar por número de calificaciones primero
                ->orderBy('promedio_calificacion', 'desc') // Luego por promedio como criterio secundario
                ->get();

            if ($resultados->isEmpty()) {
                return [
                    'usuarios' => ['Sin calificaciones'],
                    'promedios' => [0],
                    'totales' => [0],
                    'scores' => [0],
                    'posiciones' => [1]
                ];
            }

            // Calcular score ponderado para referencia (aunque ya no se use para ordenar)
            $maxCalificaciones = $resultados->max('total_calificaciones');
            
            $resultados = $resultados->map(function($item) use ($maxCalificaciones) {
                $factorVolumen = min($item->total_calificaciones / max($maxCalificaciones * 0.2, 1), 1) * 5;
                $score = ($item->promedio_calificacion * 0.7) + ($factorVolumen * 0.3);
                
                $item->score_ponderado = round($score, 2);
                return $item;
            });

            // Agregar posiciones en el ranking
            $posiciones = [];
            for ($i = 0; $i < $resultados->count(); $i++) {
                $posiciones[] = $i + 1;
            }

            return [
                'usuarios' => $resultados->pluck('usuario')->toArray(),
                'promedios' => $resultados->pluck('promedio_calificacion')->map(function($valor) {
                    return round($valor, 2);
                })->toArray(),
                'totales' => $resultados->pluck('total_calificaciones')->toArray(),
                'scores' => $resultados->pluck('score_ponderado')->toArray(),
                'posiciones' => $posiciones
            ];

        } catch (\Exception $e) {
            \Log::error('Error en getDatosGraficoPuntuaciones: ' . $e->getMessage());
            return [
                'usuarios' => ['Error'],
                'promedios' => [0],
                'totales' => [0],
                'scores' => [0],
                'posiciones' => [1]
            ];
        }
    }

    private function getRankingPedidosUsuario($year, $month = null)
    {
        try {
            $query = Pedido::select(
                'pedidos.usuario',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(pedidos.total) as total_ventas')
            )
            ->join('users', 'pedidos.usuario', '=', 'users.name')
            ->whereNotNull('pedidos.usuario')
            // Removido el filtro where('users.is_admin', false) para incluir administradores
            ->whereYear('pedidos.fecha', $year);

            if ($month) {
                $query->whereMonth('pedidos.fecha', $month);
            }

            $resultados = $query->groupBy('pedidos.usuario')
                ->orderBy('total_pedidos', 'desc')
                ->get();

            if ($resultados->isEmpty()) {
                return [
                    'usuarios' => ['Sin pedidos'],
                    'pedidos' => [0],
                    'ventas' => [0]
                ];
            }

            return [
                'usuarios' => $resultados->pluck('usuario')->toArray(),
                'pedidos' => $resultados->pluck('total_pedidos')->toArray(),
                'ventas' => $resultados->pluck('total_ventas')->toArray()
            ];

        } catch (\Exception $e) {
            \Log::error('Error en getRankingPedidosUsuario: ' . $e->getMessage());
            return [
                'usuarios' => ['Error'],
                'pedidos' => [0],
                'ventas' => [0]
            ];
        }
    }

    private function getRankingVentasEmpresa($year, $month = null)
    {
        try {
            $query = Pedido::join('empresas', 'pedidos.empresa_id', '=', 'empresas.id')
                ->select(
                    'empresas.nombre as empresa',
                    DB::raw('COUNT(pedidos.id) as total_pedidos'),
                    DB::raw('SUM(pedidos.total) as total_ventas')
                )
                ->whereYear('pedidos.fecha', $year);

            if ($month) {
                $query->whereMonth('pedidos.fecha', $month);
            }

            $resultados = $query->groupBy('empresas.id', 'empresas.nombre')
                ->orderBy('total_ventas', 'desc')
                ->get();

            if ($resultados->isEmpty()) {
                return [
                    'empresas' => ['Sin ventas'],
                    'pedidos' => [0],
                    'ventas' => [0]
                ];
            }

            return [
                'empresas' => $resultados->pluck('empresa')->toArray(),
                'pedidos' => $resultados->pluck('total_pedidos')->toArray(),
                'ventas' => $resultados->pluck('total_ventas')->toArray()
            ];

        } catch (\Exception $e) {
            \Log::error('Error en getRankingVentasEmpresa: ' . $e->getMessage());
            return [
                'empresas' => ['Error'],
                'pedidos' => [0],
                'ventas' => [0]
            ];
        }
    }
}
