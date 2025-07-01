<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Mostrar la lista de asistencias.
     */
    public function index(Request $request)
    {
        $query = Asistencia::with(['user', 'user.empresa']);

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('empresa_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_hora', $request->fecha);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);
        $usuarios = User::where('active', true)->get();
        $empresas = Empresa::all();

        return view('asistencias.index', compact('asistencias', 'usuarios', 'empresas'));
    }

    /**
     * Mostrar el formulario para crear una nueva asistencia.
     */
    public function create()
    {
        $usuarios = User::where('active', true)->get();
        return view('asistencias.create', compact('usuarios'));
    }

    /**
     * Almacenar una nueva asistencia.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i|after:hora_entrada',
            'estado' => 'required|in:presente,ausente,tardanza',
        ]);

        // Verificar si ya existe una asistencia para el usuario en la fecha
        $fechaSolo = Carbon::parse($validated['fecha_hora'])->toDateString();
        $existeAsistencia = Asistencia::where('user_id', $validated['user_id'])
            ->whereDate('fecha_hora', $fechaSolo)
            ->exists();

        if ($existeAsistencia) {
            return back()->withErrors(['user_id' => 'Ya existe una asistencia registrada para este usuario en la fecha seleccionada.']);
        }

        Asistencia::create($validated);

        return redirect()->route('asistencias.index')
            ->with('success', 'Asistencia registrada exitosamente.');
    }

    /**
     * Mostrar una asistencia específica.
     */
    public function show(Asistencia $asistencia)
    {
        $asistencia->load('user');
        return view('asistencias.show', compact('asistencia'));
    }

    /**
     * Mostrar el formulario para editar una asistencia.
     */
    public function edit(Asistencia $asistencia)
    {
        $usuarios = User::where('active', true)->get();
        return view('asistencias.edit', compact('asistencia', 'usuarios'));
    }

    /**
     * Actualizar una asistencia.
     */
    public function update(Request $request, Asistencia $asistencia)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i|after:hora_entrada',
            'estado' => 'required|in:presente,ausente,tardanza',
        ]);

        // Verificar si ya existe otra asistencia para el usuario en la fecha (excluyendo la actual)
        $fechaSolo = Carbon::parse($validated['fecha_hora'])->toDateString();
        $existeAsistencia = Asistencia::where('user_id', $validated['user_id'])
            ->whereDate('fecha_hora', $fechaSolo)
            ->where('id', '!=', $asistencia->id)
            ->exists();

        if ($existeAsistencia) {
            return back()->withErrors(['user_id' => 'Ya existe una asistencia registrada para este usuario en la fecha seleccionada.']);
        }

        $asistencia->update($validated);

        return redirect()->route('asistencias.index')
            ->with('success', 'Asistencia actualizada exitosamente.');
    }

    /**
     * Eliminar una asistencia.
     */
    public function destroy(Asistencia $asistencia)
    {
        $asistencia->delete();

        return redirect()->route('asistencias.index')
            ->with('success', 'Asistencia eliminada exitosamente.');
    }

    /**
     * Registrar entrada del usuario autenticado.
     */
    public function marcarEntrada(Request $request)
    {
        $user = Auth::user();
        $hoy = Carbon::today();

        // Verificar si ya hay una asistencia registrada hoy
        $asistenciaHoy = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', $hoy)
            ->first();

        if ($asistenciaHoy && $asistenciaHoy->hora_entrada) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has marcado tu entrada hoy.'
            ]);
        }

        $horaActual = Carbon::now()->format('H:i:s');
        $estado = $horaActual > '08:00:00' ? 'tardanza' : 'presente';

        if ($asistenciaHoy) {
            // Actualizar asistencia existente
            $asistenciaHoy->update([
                'hora_entrada' => $horaActual,
                'estado' => $estado
            ]);
        } else {
            // Crear nueva asistencia
            Asistencia::create([
                'user_id' => $user->id,
                'fecha_hora' => Carbon::now(),
                'hora_entrada' => $horaActual,
                'estado' => $estado
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Entrada registrada exitosamente.',
            'hora' => $horaActual,
            'estado' => $estado
        ]);
    }

    /**
     * Registrar salida del usuario autenticado.
     */
    public function marcarSalida(Request $request)
    {
        $user = Auth::user();
        $hoy = Carbon::today();

        $asistenciaHoy = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', $hoy)
            ->first();

        if (!$asistenciaHoy) {
            return response()->json([
                'success' => false,
                'message' => 'No has marcado entrada hoy.'
            ]);
        }

        if ($asistenciaHoy->hora_salida) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has marcado tu salida hoy.'
            ]);
        }

        $horaActual = Carbon::now()->format('H:i:s');
        $asistenciaHoy->update([
            'hora_salida' => $horaActual
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salida registrada exitosamente.',
            'hora' => $horaActual
        ]);
    }

    /**
     * Obtener reporte de asistencias.
     */
    public function reporte(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

        $asistencias = Asistencia::with('user')
            ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        $estadisticas = [
            'total' => $asistencias->count(),
            'presentes' => $asistencias->where('estado', 'presente')->count(),
            'ausentes' => $asistencias->where('estado', 'ausente')->count(),
            'tardanzas' => $asistencias->where('estado', 'tardanza')->count(),
        ];

        return view('asistencias.reporte', compact('asistencias', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Mostrar vista de QR personal del usuario.
     */
    public function miQr()
    {
        return view('asistencias.mi-qr');
    }

    /**
     * Mostrar vista del escáner QR.
     */
    public function scan()
    {
        return view('asistencias.scan');
    }

    /**
     * Procesar código QR escaneado.
     */
    public function procesarQr(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'qr_data' => 'required|string',
        ]);

        $user = User::with('empresa')->find($validated['user_id']);
        
        if (!$user || !$user->active) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado o inactivo.'
            ]);
        }

        $hoy = Carbon::today();
        $horaActual = Carbon::now();
        $hora = $horaActual->format('H:i:s');

        // Buscar asistencia existente para hoy
        $asistenciaHoy = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', $hoy)
            ->first();

        $accion = '';
        $estado = 'presente'; // Valor por defecto

        // LÓGICA PRINCIPAL: Determinar acción según empresa y horarios
        if (!$user->empresa_id) {
            // CASO 1: Usuario SIN empresa - siempre marcar como presente
            $estado = 'presente';
            
            if (!$asistenciaHoy) {
                // Primera vez del día - crear nueva asistencia
                Asistencia::create([
                    'user_id' => $user->id,
                    'fecha_hora' => $horaActual,
                    'hora_entrada' => $hora,
                    'estado' => $estado
                ]);
                $accion = 'ENTRADA REGISTRADA (SIN EMPRESA)';
            } elseif (!$asistenciaHoy->hora_entrada) {
                // Actualizar entrada
                $asistenciaHoy->update([
                    'hora_entrada' => $hora,
                    'estado' => $estado
                ]);
                $accion = 'ENTRADA REGISTRADA (SIN EMPRESA)';
            } elseif (!$asistenciaHoy->hora_salida) {
                // Marcar salida
                $asistenciaHoy->update([
                    'hora_salida' => $hora
                ]);
                $accion = 'SALIDA REGISTRADA (SIN EMPRESA)';
            } else {
                // Ya tiene entrada y salida - crear nueva entrada para el día siguiente o rechazar
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has completado tu jornada de hoy (entrada y salida registradas).'
                ]);
            }
        } else {
            // CASO 2: Usuario CON empresa - consultar horario de la empresa
            $horarioEmpresa = \App\Models\Horario::where('empresa_id', $user->empresa_id)->first();
            
            if (!$horarioEmpresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay horario configurado para su empresa. Contacte al administrador.'
                ]);
            }

            // Convertir horarios a Carbon para comparación
            $horaEntradaEmpresa = Carbon::createFromFormat('H:i:s', $horarioEmpresa->hora_entrada);
            $horaActualCarbon = Carbon::createFromFormat('H:i:s', $hora);
            
            // Calcular diferencias en minutos
            $diferenciaMinutos = $horaActualCarbon->diffInMinutes($horaEntradaEmpresa, false);
            
            // Determinar estado según la hora
            if ($diferenciaMinutos <= -10) {
                // Más de 10 minutos después de la hora de entrada = ATRASO
                $estado = 'tardanza';
            } elseif ($diferenciaMinutos >= -10) {
                // 10 minutos antes o hasta 10 minutos después = PRESENTE
                $estado = 'presente';
            }

            // Verificar si hay asistencia del día anterior sin salida registrada
            $ayer = Carbon::yesterday();
            $asistenciaAyer = Asistencia::where('user_id', $user->id)
                ->whereDate('fecha_hora', $ayer)
                ->whereNotNull('hora_entrada')
                ->whereNull('hora_salida')
                ->first();

            if ($asistenciaAyer) {
                // CASO ESPECIAL: Marcar salida del día anterior primero
                $asistenciaAyer->update([
                    'hora_salida' => $hora
                ]);
                
                // Luego proceder con la entrada de hoy
                if (!$asistenciaHoy) {
                    Asistencia::create([
                        'user_id' => $user->id,
                        'fecha_hora' => $horaActual,
                        'hora_entrada' => $hora,
                        'estado' => $estado
                    ]);
                } else {
                    $asistenciaHoy->update([
                        'hora_entrada' => $hora,
                        'estado' => $estado
                    ]);
                }
                
                $estadoTexto = $estado == 'tardanza' ? 'CON ATRASO' : 'A TIEMPO';
                $accion = "SALIDA ANTERIOR REGISTRADA Y ENTRADA {$estadoTexto}";
            } else {
                // Lógica normal para empresa
                if (!$asistenciaHoy) {
                    // Primera vez del día - crear nueva asistencia
                    Asistencia::create([
                        'user_id' => $user->id,
                        'fecha_hora' => $horaActual,
                        'hora_entrada' => $hora,
                        'estado' => $estado
                    ]);
                    $estadoTexto = $estado == 'tardanza' ? 'CON ATRASO' : 'A TIEMPO';
                    $accion = "ENTRADA REGISTRADA {$estadoTexto}";
                } elseif (!$asistenciaHoy->hora_entrada) {
                    // Actualizar entrada
                    $asistenciaHoy->update([
                        'hora_entrada' => $hora,
                        'estado' => $estado
                    ]);
                    $estadoTexto = $estado == 'tardanza' ? 'CON ATRASO' : 'A TIEMPO';
                    $accion = "ENTRADA REGISTRADA {$estadoTexto}";
                } elseif (!$asistenciaHoy->hora_salida) {
                    // Marcar salida
                    $asistenciaHoy->update([
                        'hora_salida' => $hora
                    ]);
                    $accion = 'SALIDA REGISTRADA';
                } else {
                    // Ya tiene entrada y salida
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya has completado tu jornada de hoy (entrada y salida registradas).'
                    ]);
                }
            }
        }

        // Obtener información de pedidos del usuario
        $pedidos = \App\Models\Pedido::where('usuario', $user->user)->get();
        $totalPedidos = $pedidos->count();
        $totalVentas = $pedidos->sum('total');

        // Pedidos del mes actual
        $pedidosMesActual = \App\Models\Pedido::where('usuario', $user->user)
            ->whereMonth('fecha', Carbon::now()->month)
            ->whereYear('fecha', Carbon::now()->year)
            ->get();
        
        $totalPedidosMes = $pedidosMesActual->count();
        $totalVentasMes = $pedidosMesActual->sum('total');

        // Último pedido
        $ultimoPedido = \App\Models\Pedido::where('usuario', $user->user)
            ->orderBy('fecha', 'desc')
            ->first();

        // Información adicional sobre el procesamiento
        $mensajeDetallado = $accion;
        if ($user->empresa_id && isset($horarioEmpresa)) {
            $mensajeDetallado .= " (Horario empresa: {$horarioEmpresa->hora_entrada} - {$horarioEmpresa->hora_salida})";
        }

        return response()->json([
            'success' => true,
            'message' => $mensajeDetallado,
            'action' => $accion,
            'hora' => $hora,
            'user_name' => strtoupper($user->name),
            'user_username' => strtoupper($user->user),
            'estado' => $estado,
            'empresa' => $user->empresa ? strtoupper($user->empresa->nombre) : 'SIN EMPRESA',
            // Información de pedidos
            'pedidos_info' => [
                'total_pedidos' => $totalPedidos,
                'total_ventas' => number_format($totalVentas, 2),
                'total_pedidos_mes' => $totalPedidosMes,
                'total_ventas_mes' => number_format($totalVentasMes, 2),
                'ultimo_pedido' => $ultimoPedido ? [
                    'fecha' => $ultimoPedido->fecha->format('d/m/Y'),
                    'numero_orden' => $ultimoPedido->numero_orden,
                    'cliente' => $ultimoPedido->cliente,
                    'total' => number_format($ultimoPedido->total, 2)
                ] : null
            ]
        ]);
    }
}
