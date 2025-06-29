<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\User;
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
        $query = Asistencia::with('user');

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_hora', $request->fecha);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);
        $usuarios = User::where('active', true)->get();

        return view('asistencias.index', compact('asistencias', 'usuarios'));
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
}
