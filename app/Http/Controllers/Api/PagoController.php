<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\mediosdepago;
use App\Models\Pedido;
use App\Models\Caja;
use App\Models\Egreso;
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

    public function getRetirosCaja(Request $request)
    {
        $mes = $request->query('mes', Carbon::now()->month);
        $ano = $request->query('ano', Carbon::now()->year);

        // Obtener todos los valores negativos (retiros) del mes y año seleccionado
        $retiros = Caja::whereYear('created_at', $ano)
                      ->whereMonth('created_at', $mes)
                      ->where('valor', '<', 0)
                      ->get();

        // Calcular el total de retiros
        $totalRetiros = $retiros->sum('valor');

        // Preparar el listado de retiros
        $listaRetiros = $retiros->map(function($retiro) {
            return [
                'fecha' => $retiro->created_at->format('Y-m-d'),
                'motivo' => $retiro->motivo,
                'valor' => number_format($retiro->valor, 2, '.', ''),
                'usuario' => $retiro->user ? $retiro->user->name : 'N/A'
            ];
        });

        return response()->json([
            'mes' => $mes,
            'ano' => $ano,
            'retiro_total' => number_format($totalRetiros, 2, '.', ''),
            'retiros' => $listaRetiros
        ]);
    }

    public function getEgresosPorMes(Request $request)
    {
        $mes = $request->query('mes', Carbon::now()->month);
        $ano = $request->query('ano', Carbon::now()->year);

        // Obtener los egresos del mes y año seleccionado
        $egresos = Egreso::whereYear('created_at', $ano)
                        ->whereMonth('created_at', $mes)
                        ->with('user:id,name') // Solo traemos los campos necesarios del usuario
                        ->get();

        // Calcular el total de egresos
        $totalEgresos = $egresos->sum('valor');

        // Preparar el listado de egresos
        $listaEgresos = $egresos->map(function($egreso) {
            return [
                'fecha' => $egreso->created_at->format('Y-m-d'),
                'motivo' => $egreso->motivo,
                'valor' => number_format($egreso->valor, 2, '.', ''),
                'usuario' => $egreso->user ? $egreso->user->name : 'N/A'
            ];
        });

        return response()->json([
            'mes' => $mes,
            'ano' => $ano,
            'total_egresos' => number_format($totalEgresos, 2, '.', ''),
            'egresos' => $listaEgresos
        ]);
    }
} 