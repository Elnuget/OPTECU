<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashHistory;
use App\Models\Caja;
use Illuminate\Http\Request;

class CashHistoryController extends Controller
{
    public function getHistorialCaja(Request $request)
    {
        try {
            $query = CashHistory::query()
                ->with('user:id,name');

            // Filtrar por fecha si se proporcionan
            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
            }

            // Filtrar por estado si se proporciona
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $historial = $query->latest()->get();

            // Obtener el total en caja
            $sumCaja = Caja::sum('valor');

            // Obtener el último registro para saber el estado actual
            $ultimoRegistro = CashHistory::latest()->first();
            $estadoActual = $ultimoRegistro ? $ultimoRegistro->estado : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'historial' => $historial,
                    'total_en_caja' => $sumCaja,
                    'estado_actual' => $estadoActual,
                    'resumen' => [
                        'total_aperturas' => $historial->where('estado', 'Apertura')->count(),
                        'total_cierres' => $historial->where('estado', 'Cierre')->count(),
                        'monto_total_movimientos' => $historial->sum('monto')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de caja: ' . $e->getMessage()
            ], 500);
        }
    }
} 