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
        $empresas = Empresa::orderBy('nombre')->get();
        $empresa = Empresa::first(); // Obtener la primera empresa registrada
        
        return view('prestamos.index', compact('prestamos', 'empresas', 'empresa'));
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

    /**
     * Obtiene los egresos locales de préstamos para un período específico
     */
    public function getEgresosLocales(Request $request)
    {
        try {
            $request->validate([
                'ano' => 'required|integer',
                'mes' => 'required|integer|min:1|max:12',
                'empresa_id' => 'nullable|exists:empresas,id'
            ]);

            $ano = $request->ano;
            $mes = $request->mes;
            $empresaId = $request->empresa_id;

            // Consultar egresos que contengan "prestamo" en el motivo
            $query = \DB::table('egresos')
                ->leftJoin('empresas', 'egresos.empresa_id', '=', 'empresas.id')
                ->leftJoin('users', 'egresos.user_id', '=', 'users.id')
                ->whereYear('egresos.created_at', $ano)
                ->whereMonth('egresos.created_at', $mes)
                ->where('egresos.motivo', 'LIKE', '%prestamo%')
                ->select(
                    'egresos.*',
                    'empresas.nombre as empresa',
                    'users.name as usuario',
                    \DB::raw('DATE(egresos.created_at) as fecha'),
                    \DB::raw('TIME(egresos.created_at) as hora')
                );

            if ($empresaId) {
                $query->where('egresos.empresa_id', $empresaId);
            }

            $egresos = $query->orderBy('egresos.created_at', 'desc')->get();

            $totalEgresos = $egresos->sum('valor');

            return response()->json([
                'success' => true,
                'total_egresos' => $totalEgresos,
                'egresos' => $egresos->map(function ($egreso) {
                    return [
                        'id' => $egreso->id,
                        'fecha' => $egreso->fecha,
                        'hora' => $egreso->hora,
                        'empresa' => $egreso->empresa ?? 'SIN ESPECIFICAR',
                        'motivo' => $egreso->motivo,
                        'valor' => $egreso->valor,
                        'usuario' => $egreso->usuario ?? 'DESCONOCIDO'
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener egresos: ' . $e->getMessage()
            ], 500);
        }
    }
} 