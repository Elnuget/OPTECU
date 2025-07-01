<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    /**
     * Mostrar la lista de horarios
     */
    public function index(Request $request)
    {
        $query = Horario::with('empresa');

        // Filtro por empresa si se proporciona
        if ($request->has('empresa_id')) {
            $query->byEmpresa($request->empresa_id);
        }

        $horarios = $query->orderBy('hora_entrada')->paginate(15);
        $empresas = Empresa::all();

        return view('horarios.index', compact('horarios', 'empresas'));
    }

    /**
     * Mostrar el formulario para crear un nuevo horario
     */
    public function create()
    {
        $empresas = Empresa::all();
        return view('horarios.create', compact('empresas'));
    }

    /**
     * Almacenar un nuevo horario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            'empresa_id' => 'required|exists:empresas,id',
        ], [
            'hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'hora_entrada.date_format' => 'La hora de entrada debe tener el formato HH:MM.',
            'hora_salida.required' => 'La hora de salida es obligatoria.',
            'hora_salida.date_format' => 'La hora de salida debe tener el formato HH:MM.',
            'hora_salida.after' => 'La hora de salida debe ser posterior a la hora de entrada.',
            'empresa_id.required' => 'Debe seleccionar una empresa.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
        ]);

        Horario::create($validated);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario creado exitosamente.');
    }

    /**
     * Mostrar un horario específico
     */
    public function show(Horario $horario)
    {
        $horario->load('empresa');
        return view('horarios.show', compact('horario'));
    }

    /**
     * Mostrar el formulario para editar un horario
     */
    public function edit(Horario $horario)
    {
        $empresas = Empresa::all();
        return view('horarios.edit', compact('horario', 'empresas'));
    }

    /**
     * Actualizar un horario específico
     */
    public function update(Request $request, Horario $horario)
    {
        $validated = $request->validate([
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            'empresa_id' => 'required|exists:empresas,id',
        ], [
            'hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'hora_entrada.date_format' => 'La hora de entrada debe tener el formato HH:MM.',
            'hora_salida.required' => 'La hora de salida es obligatoria.',
            'hora_salida.date_format' => 'La hora de salida debe tener el formato HH:MM.',
            'hora_salida.after' => 'La hora de salida debe ser posterior a la hora de entrada.',
            'empresa_id.required' => 'Debe seleccionar una empresa.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
        ]);

        $horario->update($validated);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario actualizado exitosamente.');
    }

    /**
     * Eliminar un horario específico
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();

        return redirect()->route('horarios.index')
            ->with('success', 'Horario eliminado exitosamente.');
    }

    /**
     * API: Obtener horarios por empresa (para AJAX)
     */
    public function getByEmpresa($empresaId)
    {
        $horarios = Horario::byEmpresa($empresaId)
            ->orderBy('hora_entrada')
            ->get();

        return response()->json($horarios);
    }

    /**
     * API: Verificar si hay horarios activos
     */
    public function horariosActivos()
    {
        $horaActual = now()->format('H:i:s');
        
        $horariosActivos = Horario::with('empresa')
            ->whereTime('hora_entrada', '<=', $horaActual)
            ->whereTime('hora_salida', '>=', $horaActual)
            ->get();

        return response()->json($horariosActivos);
    }

    /**
     * API: Obtener horarios activos (alias para compatibilidad)
     */
    public function activos()
    {
        return $this->horariosActivos();
    }
}
