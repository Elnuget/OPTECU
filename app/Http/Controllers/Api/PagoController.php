<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\mediosdepago;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function getTotalesPorMes(Request $request)
    {
        $mes = $request->query('mes', Carbon::now()->month);
        $ano = $request->query('ano', Carbon::now()->year);

        // Primero obtenemos los pedidos del mes/año
        $pedidos = Pedido::whereYear('fecha', $ano)
                        ->whereMonth('fecha', $mes)
                        ->get();

        // Luego obtenemos los pagos asociados a esos pedidos
        $pagos = Pago::whereIn('pedido_id', $pedidos->pluck('id'))
                     ->validPayments()
                     ->get();

        $totalPagos = $pagos->sum('pago');

        $mediosDePago = mediosdepago::all();
        $totalesPorMedio = [];

        foreach ($mediosDePago as $medio) {
            $totalPorMedio = $pagos->filter(function($pago) use ($medio) {
                return $pago->mediodepago->id === $medio->id;
            })->sum('pago');

            $totalesPorMedio[] = [
                'medio_de_pago' => $medio->medio_de_pago,
                'total' => number_format($totalPorMedio, 2, '.', '')
            ];
        }

        return response()->json([
            'mes' => $mes,
            'ano' => $ano,
            'total_pagos' => number_format($totalPagos, 2, '.', ''),
            'desglose_por_medio' => $totalesPorMedio
        ]);
    }
} 