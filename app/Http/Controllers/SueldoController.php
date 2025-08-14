<?php

namespace App\Http\Controllers;

use App\Models\Sueldo;
use App\Models\DetalleSueldo;
use App\Models\CashHistory;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SueldoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener los sueldos (mantener la funcionalidad original)
        $sueldos = Sueldo::with(['user', 'empresa'])->orderBy('fecha', 'desc')->get();
        
        // Inicializar variables
        $pedidos = collect();
        $retirosCaja = collect();
        $detallesSueldo = collect();
        $historialCaja = collect();
        $usuariosConPedidos = [];
        $anio = $request->get('anio');
        $mes = $request->get('mes');
        $usuario = $request->get('usuario');
        
        // Solo ejecutar consultas si se ha realizado una búsqueda
        if ($request->hasAny(['anio', 'mes', 'usuario']) && $request->isMethod('get')) {
            // Si no se especifica año, usar el actual
            if (!$anio) $anio = date('Y');
            // Si no se especifica mes, usar el actual
            if (!$mes) $mes = date('m');
            
            // Consultar los pedidos según los filtros
            $pedidosQuery = \App\Models\Pedido::whereYear('fecha', $anio)
                ->whereMonth('fecha', $mes);
                
            // Si se seleccionó un usuario específico
            if ($usuario) {
                $pedidosQuery->where('usuario', $usuario);
            }
            
            // Obtener los pedidos filtrados
            $pedidos = $pedidosQuery->orderBy('fecha', 'desc')->get();
            
            // Obtener la lista de usuarios únicos que han realizado pedidos
            $usuariosConPedidos = \App\Models\Pedido::select('usuario')
                ->whereNotNull('usuario')
                ->distinct()
                ->orderBy('usuario')
                ->pluck('usuario')
                ->toArray();
            
            // Obtener los retiros de caja para el mismo periodo
            $cajaQuery = \App\Models\Caja::with(['user', 'empresa'])
                ->whereYear('created_at', $anio)
                ->whereMonth('created_at', $mes)
                ->where(function($query) {
                    // Excluir registros que contengan "abono" o "deposito" en el motivo
                    $query->whereRaw("LOWER(motivo) NOT LIKE ?", ['%abono%'])
                          ->whereRaw("LOWER(motivo) NOT LIKE ?", ['%deposito%']);
                });
                
            // Si se seleccionó un usuario específico, buscamos su ID en la tabla users
            if ($usuario) {
                $user = \App\Models\User::where('name', $usuario)->first();
                if ($user) {
                    $cajaQuery->where('user_id', $user->id);
                }
            }
            
            // Obtener los retiros de caja filtrados
            $retirosCaja = $cajaQuery->orderBy('created_at', 'desc')->get();
            
            // Obtener detalles de sueldo con filtros
            $detallesSueldoQuery = DetalleSueldo::with('user')
                ->where('ano', $anio)
                ->where('mes', $mes);
                
            // Si se seleccionó un usuario específico
            if ($usuario) {
                $detallesSueldoQuery->whereHas('user', function($query) use ($usuario) {
                    $query->where('name', 'LIKE', '%' . $usuario . '%');
                });
            }
            
            $detallesSueldo = $detallesSueldoQuery->orderBy('created_at', 'desc')->get();
            
            // Obtener historial de caja (aperturas y cierres)
            $cashHistoryQuery = CashHistory::with(['user', 'empresa'])
                ->whereYear('created_at', $anio)
                ->whereMonth('created_at', $mes);
                
            // Si se seleccionó un usuario específico
            if ($usuario) {
                $cashHistoryQuery->whereHas('user', function($query) use ($usuario) {
                    $query->where('name', 'LIKE', '%' . $usuario . '%');
                });
            }
            
            $cashHistoryRaw = $cashHistoryQuery->orderBy('created_at', 'asc')->get();
            
            // Procesar historial de caja para calcular horas trabajadas
            $historialCaja = $this->procesarHistorialCaja($cashHistoryRaw);
        } else {
            // Si no hay búsqueda, obtener usuarios para el dropdown
            $usuariosConPedidos = \App\Models\Pedido::select('usuario')
                ->whereNotNull('usuario')
                ->distinct()
                ->orderBy('usuario')
                ->pluck('usuario')
                ->toArray();
        }
        
        return view('sueldos.index', compact('sueldos', 'usuariosConPedidos', 'pedidos', 'anio', 'mes', 'usuario', 'retirosCaja', 'detallesSueldo', 'historialCaja'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $usuarios = User::all();
        $empresas = Empresa::all();
        return view('sueldos.create', compact('usuarios', 'empresas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:191',
            'valor' => 'required|numeric|min:0',
        ]);

        Sueldo::create($request->all());

        return redirect()->route('sueldos.index')
            ->with('success', 'Sueldo registrado correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\View\View
     */
    public function show(Sueldo $sueldo)
    {
        return view('sueldos.show', compact('sueldo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\View\View
     */
    public function edit(Sueldo $sueldo)
    {
        $usuarios = User::all();
        $empresas = Empresa::all();
        return view('sueldos.edit', compact('sueldo', 'usuarios', 'empresas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Sueldo $sueldo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:191',
            'valor' => 'required|numeric|min:0',
        ]);

        $sueldo->update($request->all());

        return redirect()->route('sueldos.index')
            ->with('success', 'Sueldo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Sueldo $sueldo)
    {
        $sueldo->delete();

        return redirect()->route('sueldos.index')
            ->with('success', 'Sueldo eliminado correctamente');
    }

    /**
     * Procesar historial de caja para calcular horas trabajadas por día
     */
    private function procesarHistorialCaja($cashHistoryRaw)
    {
        $historialProcesado = collect();
        
        // Agrupar por usuario, empresa y fecha
        $agrupado = $cashHistoryRaw->groupBy(function($item) {
            $empresaNombre = $item->empresa ? $item->empresa->nombre : 'Sin empresa';
            return $item->user->name . '_' . $empresaNombre . '_' . $item->created_at->format('Y-m-d');
        });
        
        foreach ($agrupado as $key => $registrosDia) {
            $partes = explode('_', $key, 3); // Limitar a 3 partes
            $usuario = $partes[0];
            $empresa = $partes[1];
            $fecha = $partes[2];
            
            // Buscar apertura y cierre
            $apertura = $registrosDia->where('estado', 'Apertura')->first();
            $cierre = $registrosDia->where('estado', 'Cierre')->first();
            
            // Calcular horas trabajadas
            $horasTrabajadas = null;
            $minutosTrabajados = null;
            $totalMinutos = null;
            $horaApertura = null;
            $horaCierre = null;
            $estado = 'Sin registros';
            
            if ($apertura) {
                $horaApertura = $apertura->created_at->format('H:i:s');
                $estado = 'Solo apertura';
                
                if ($cierre) {
                    $horaCierre = $cierre->created_at->format('H:i:s');
                    $totalMinutos = $apertura->created_at->diffInMinutes($cierre->created_at);
                    $horasTrabajadas = intval($totalMinutos / 60);
                    $minutosTrabajados = $totalMinutos % 60;
                    $horasFormateadas = $horasTrabajadas . 'h ' . $minutosTrabajados . 'm';
                    $estado = 'Completo';
                } else {
                    $horasFormateadas = 'En progreso';
                }
            } elseif ($cierre) {
                $horaCierre = $cierre->created_at->format('H:i:s');
                $horasFormateadas = 'Solo cierre';
                $estado = 'Solo cierre';
            } else {
                $horasFormateadas = 'Sin registros';
            }
            
            $historialProcesado->push((object) [
                'usuario' => $usuario,
                'empresa' => $empresa,
                'fecha' => $fecha,
                'fecha_formateada' => Carbon::parse($fecha)->format('d/m/Y'),
                'dia_semana' => Carbon::parse($fecha)->locale('es')->dayName,
                'hora_apertura' => $horaApertura,
                'hora_cierre' => $horaCierre,
                'horas_trabajadas' => $horasTrabajadas,
                'minutos_trabajados' => $minutosTrabajados,
                'total_minutos' => $totalMinutos,
                'horas_formateadas' => $horasFormateadas,
                'estado' => $estado,
                'monto_apertura' => $apertura ? $apertura->monto : null,
                'monto_cierre' => $cierre ? $cierre->monto : null,
                'registros_count' => $registrosDia->count()
            ]);
        }
        
        return $historialProcesado->sortByDesc('fecha');
    }
}
