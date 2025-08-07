<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Empresa;
use App\Models\PagoPrestamo;
use App\Models\User;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestamo::with(['user', 'empresa']);
        
        // Filtro por empresa
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }
        
        // Filtro por estado del préstamo (opcional)
        if ($request->filled('estado')) {
            switch ($request->estado) {
                case 'pendiente':
                    $query->whereDoesntHave('pagos', function($q) {
                        $q->where('estado', 'pagado');
                    });
                    break;
                case 'parcial':
                    $query->whereHas('pagos', function($q) {
                        $q->where('estado', 'pagado');
                    })->whereRaw('(SELECT COALESCE(SUM(valor), 0) FROM pago_prestamos WHERE prestamo_id = prestamos.id AND estado = "pagado") < valor_neto');
                    break;
                case 'pagado':
                    $query->whereRaw('(SELECT COALESCE(SUM(valor), 0) FROM pago_prestamos WHERE prestamo_id = prestamos.id AND estado = "pagado") >= valor_neto');
                    break;
            }
        }
        
        $prestamos = $query->latest()->get();
        $empresas = Empresa::orderBy('nombre')->get();
        
        return view('prestamos.index', compact('prestamos', 'empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
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
            'empresa_id' => 'nullable|exists:empresas,id',
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
     * Obtener estadísticas de préstamos por empresa
     */
    public function getEstadisticas(Request $request)
    {
        $empresaId = $request->get('empresa_id');
        
        $query = Prestamo::query();
        
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }
        
        $totalPrestamos = $query->count();
        $valorTotalPrestado = $query->sum('valor_neto');
        $prestamosPendientes = $query->where('estado_prestamo', 'pendiente')->count();
        $prestamosPagados = $query->where('estado_prestamo', 'pagado')->count();
        
        return response()->json([
            'total_prestamos' => $totalPrestamos,
            'valor_total_prestado' => $valorTotalPrestado,
            'prestamos_pendientes' => $prestamosPendientes,
            'prestamos_pagados' => $prestamosPagados
        ]);
    }

    /**
     * Obtiene los pagos de préstamos locales para un período específico
     */
    public function getPagosLocales(Request $request)
    {
        try {
            $currentYear = date('Y');
            $currentMonth = date('n');
            
            $ano = $request->get('ano', $currentYear);
            $mes = $request->get('mes', $currentMonth);
            $empresaId = $request->get('empresa_id');

            // Consultar pagos de préstamos
            $query = PagoPrestamo::with(['prestamo', 'empresa', 'user'])
                ->whereYear('fecha_pago', $ano)
                ->whereMonth('fecha_pago', $mes);

            if ($empresaId && $empresaId !== 'todas') {
                $query->where('empresa_id', $empresaId);
            }

            $pagos = $query->orderBy('fecha_pago', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->get();

            $totalPagos = $pagos->sum('valor');

            return response()->json([
                'success' => true,
                'total_pagos' => $totalPagos,
                'pagos' => $pagos->map(function ($pago) {
                    return [
                        'id' => $pago->id,
                        'fecha' => $pago->fecha_pago->format('Y-m-d'),
                        'hora' => $pago->created_at->format('H:i:s'),
                        'empresa' => $pago->empresa->nombre ?? 'SIN ESPECIFICAR',
                        'empresa_id' => $pago->empresa_id,
                        'motivo' => $pago->motivo ?? 'Pago préstamo: ' . ($pago->prestamo->motivo ?? ''),
                        'valor' => $pago->valor,
                        'usuario' => $pago->user->name ?? 'DESCONOCIDO',
                        'prestamo_id' => $pago->prestamo_id,
                        'prestamo_usuario' => $pago->prestamo->user->name ?? 'N/A',
                        'observaciones' => $pago->observaciones,
                        'estado' => $pago->estado
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener pagos de préstamos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar un nuevo pago de préstamo
     */
    public function storePago(Request $request)
    {
        try {
            $request->validate([
                'prestamo_id' => 'required|exists:prestamos,id',
                'empresa_id' => 'required|exists:empresas,id',
                'valor' => 'required|numeric|min:0.01',
                'fecha_pago' => 'required|date',
                'motivo' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string'
            ]);

            $pago = PagoPrestamo::create([
                'prestamo_id' => $request->prestamo_id,
                'empresa_id' => $request->empresa_id,
                'user_id' => auth()->id(),
                'valor' => $request->valor,
                'fecha_pago' => $request->fecha_pago,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'estado' => 'pagado'
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Pago registrado exitosamente',
                'pago' => $pago->load(['prestamo', 'empresa', 'user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de pagos por préstamo
     */
    public function getResumenPagos($prestamoId)
    {
        try {
            $prestamo = Prestamo::with(['pagos.empresa', 'pagos.user', 'user'])->findOrFail($prestamoId);

            return response()->json([
                'success' => true,
                'prestamo' => [
                    'id' => $prestamo->id,
                    'usuario' => $prestamo->user->name,
                    'valor_original' => $prestamo->valor,
                    'valor_neto' => $prestamo->valor_neto,
                    'cuotas_totales' => $prestamo->cuotas,
                    'motivo' => $prestamo->motivo,
                    'total_pagado' => $prestamo->total_pagado,
                    'saldo_pendiente' => $prestamo->saldo_pendiente,
                    'cuotas_pagadas' => $prestamo->cuotas_pagadas,
                    'cuotas_pendientes' => $prestamo->cuotas_pendientes,
                    'estado' => $prestamo->estado_prestamo
                ],
                'pagos' => $prestamo->pagos->map(function ($pago) {
                    return [
                        'id' => $pago->id,
                        'fecha' => $pago->fecha_pago->format('Y-m-d'),
                        'empresa' => $pago->empresa->nombre,
                        'valor' => $pago->valor,
                        'motivo' => $pago->motivo,
                        'usuario' => $pago->user->name,
                        'observaciones' => $pago->observaciones,
                        'estado' => $pago->estado
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
        }
    }
} 