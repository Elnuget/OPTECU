<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
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
            $query = Egreso::with('user');

            // Obtener año y mes actual como valores por defecto
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes', date('n'));

            // Aplicar filtros usando los valores por defecto o los proporcionados
            $query->whereYear('created_at', $ano)
                  ->whereMonth('created_at', $mes);

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
                'usuario' => 'required_if:motivo,PAGO DE SUELDO|exists:users,id'
            ]);

            $egreso = new Egreso();
            $egreso->user_id = $request->motivo === 'PAGO DE SUELDO' ? $request->usuario : auth()->id();
            $egreso->valor = $request->valor;
            $egreso->motivo = strtoupper($request->motivo);
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
        return view('egresos.show', compact('egreso'));
    }

    public function edit(Egreso $egreso)
    {
        return view('egresos.edit', compact('egreso'));
    }

    public function update(Request $request, Egreso $egreso)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric|min:0',
                'motivo' => 'required|string|max:255'
            ]);

            $egreso->valor = $request->valor;
            $egreso->motivo = strtoupper($request->motivo);
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
        return view('egresos.finanzas');
    }
} 