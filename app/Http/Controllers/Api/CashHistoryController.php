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

            // Filtrar por año y mes si se proporcionan
            if ($request->filled(['ano', 'mes'])) {
                $ano = $request->ano;
                $mes = $request->mes;
                $query->whereYear('created_at', $ano)
                      ->whereMonth('created_at', $mes);
            }

            // Filtrar por estado si se proporciona
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $movimientos = $query->latest()->get();

            // Calcular totales
            $ingresos = $movimientos->where('estado', 'Apertura')->sum('monto');
            $egresos = $movimientos->where('estado', 'Cierre')->sum('monto');

            return response()->json([
                'success' => true,
                'data' => [
                    'movimientos' => $movimientos->map(function($movimiento) {
                        return [
                            'id' => $movimiento->id,
                            'fecha' => $movimiento->created_at,
                            'descripcion' => $movimiento->estado,
                            'monto' => $movimiento->monto,
                            'usuario' => $movimiento->user->name
                        ];
                    }),
                    'totales' => [
                        'ingresos' => $ingresos,
                        'egresos' => $egresos
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