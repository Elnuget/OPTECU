<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestamo::with('user');

        // Filtrar por año y mes si se proporcionan
        if ($request->filled('ano') && $request->filled('mes')) {
            $query->whereYear('created_at', $request->ano)
                  ->whereMonth('created_at', $request->mes);
        }

        $prestamos = $query->latest()->get();

        // Calcular totales para el período seleccionado
        $totales = [
            'prestamos' => $prestamos->sum('valor')
        ];

        return view('prestamos.index', compact('prestamos', 'totales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'valor' => 'required|numeric|min:0',
            'motivo' => 'required|string|max:255'
        ]);

        Prestamo::create($request->all());

        return redirect()->route('prestamos.index')
            ->with('mensaje', 'PRÉSTAMO CREADO EXITOSAMENTE')
            ->with('tipo', 'alert-success');
    }

    public function show(Prestamo $prestamo)
    {
        return response()->json($prestamo->load('user'));
    }

    public function update(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'valor' => 'required|numeric|min:0',
            'motivo' => 'required|string|max:255'
        ]);

        $prestamo->update($request->all());

        return redirect()->route('prestamos.index')
            ->with('mensaje', 'PRÉSTAMO ACTUALIZADO EXITOSAMENTE')
            ->with('tipo', 'alert-success');
    }

    public function destroy(Prestamo $prestamo)
    {
        $prestamo->delete();

        return redirect()->route('prestamos.index')
            ->with('mensaje', 'PRÉSTAMO ELIMINADO EXITOSAMENTE')
            ->with('tipo', 'alert-success');
    }
} 