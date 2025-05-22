<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Empresa;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    public function index()
    {
        $prestamos = Prestamo::with('user')->latest()->get();
        $empresa = Empresa::first(); // Obtener la primera empresa registrada
        $sucursalPorDefecto = 'TODAS';

        if ($empresa) {
            switch ($empresa->nombre) {
                case 'Matriz':
                    $sucursalPorDefecto = 'MATRIZ';
                    break;
                case 'EL ROCIO':
                    $sucursalPorDefecto = 'ROCÍO';
                    break;
                case 'NORTE WENDY':
                    $sucursalPorDefecto = 'NORTE';
                    break;
            }
        }

        return view('prestamos.index', compact('prestamos', 'sucursalPorDefecto'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'valor' => 'required|numeric|min:0',
            'valor_neto' => 'required|numeric|min:0',
            'cuotas' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255'
        ]);

        Prestamo::create($request->all());

        return redirect()->route('prestamos.index')
            ->with('mensaje', 'PRÉSTAMO CREADO EXITOSAMENTE')
            ->with('tipo', 'alert-success');
    }

    public function show(Prestamo $prestamo)
    {
        return view('prestamos.show', compact('prestamo'));
    }

    public function edit(Prestamo $prestamo)
    {
        return view('prestamos.edit', compact('prestamo'));
    }

    public function update(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'valor' => 'required|numeric|min:0',
            'valor_neto' => 'required|numeric|min:0',
            'cuotas' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255'
        ]);

        $prestamo->update($request->all());

        return redirect()->route('prestamos.index')
            ->with('mensaje', 'PRÉSTAMO ACTUALIZADO EXITOSAMENTE')
            ->with('tipo', 'alert-success');
    }

    public function destroy(Prestamo $prestamo)
    {
        try {
            $prestamo->delete();
            return redirect()->route('prestamos.index')
                ->with('mensaje', 'PRÉSTAMO ELIMINADO EXITOSAMENTE')
                ->with('tipo', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->route('prestamos.index')
                ->with('mensaje', 'ERROR AL ELIMINAR EL PRÉSTAMO')
                ->with('tipo', 'alert-danger');
        }
    }
} 