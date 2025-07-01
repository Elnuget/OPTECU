<?php

namespace App\Http\Controllers;

use App\Models\CashHistory;
use Illuminate\Http\Request;
use App\Models\Caja;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CashHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CashHistory::with('user');
        
        // Filtrar por fecha si se proporciona
        if ($request->has('fecha_filtro') && $request->fecha_filtro) {
            $query->whereDate('created_at', $request->fecha_filtro);
        }
        
        $cashHistories = $query->latest()->get();
        $sumCaja = Caja::sum('valor');
        
        return view('cash-histories.index', compact('cashHistories', 'sumCaja'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'monto' => 'required|numeric',
                'estado' => 'required|in:Apertura,Cierre'
            ]);

            $lastRecord = CashHistory::latest()->first();
            $requestedState = $request->estado;

            if ($requestedState === 'Cierre' && (!$lastRecord || $lastRecord->estado !== 'Apertura')) {
                $message = 'No se puede cerrar una caja que no ha sido abierta';
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }
                return redirect()->back()->with('error', $message);
            }

            $cashHistory = new CashHistory();
            $cashHistory->monto = $request->monto;
            $cashHistory->estado = $requestedState;
            $cashHistory->user_id = auth()->id();
            $cashHistory->save();

            $message = $requestedState === 'Apertura' ? 'Caja abierta exitosamente' : 'Caja cerrada exitosamente';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('cash-histories.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error en operación de caja: ' . $e->getMessage());
            $message = 'Error al procesar la operación de caja';
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }
            
            return redirect()->back()->with('error', $message);
        }
    }

    public function update(Request $request, CashHistory $cashHistory)
    {
        $request->validate([
            'monto' => 'required|numeric',
            'estado' => 'required|string'
        ]);

        $cashHistory->update($request->all());

        return redirect()->back()->with([
            'error' => 'Exito',
            'mensaje' => 'Registro de caja actualizado exitosamente',
            'tipo' => 'alert-success'
        ]);
    }

    public function destroy(CashHistory $cashHistory)
    {
        $cashHistory->delete();

        return redirect()->back()->with([
            'error' => 'Exito',
            'mensaje' => 'Registro de caja eliminado exitosamente',
            'tipo' => 'alert-success'
        ]);
    }

    public function showClosingCard()
    {
        session(['showClosingCard' => true]);
        return redirect()->back();
    }

    public function cancelClosingCard()
    {
        session()->forget('showClosingCard');
        return redirect()->route('dashboard');
    }

    public function edit(CashHistory $cashHistory)
    {
        return view('cash-histories.edit', compact('cashHistory'));
    }
}
