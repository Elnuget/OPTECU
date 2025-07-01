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
        $currentUser = Auth::user();
        $query = CashHistory::with(['user', 'empresa']);
        
        // Si el usuario no es administrador, solo muestra registros de su empresa
        if ($currentUser && !$currentUser->is_admin && $currentUser->empresa_id) {
            $query->where('empresa_id', $currentUser->empresa_id);
        }
        
        // Filtrar por fecha si se proporciona
        if ($request->has('fecha_filtro') && $request->fecha_filtro) {
            $query->whereDate('created_at', $request->fecha_filtro);
        }
        
        // Filtrar por empresa si se proporciona y el usuario es administrador
        if ($currentUser && $currentUser->is_admin && $request->has('empresa_id') && $request->empresa_id) {
            $query->where('empresa_id', $request->empresa_id);
        }
        
        $cashHistories = $query->latest()->get();
        $sumCaja = Caja::sum('valor');
        
        // Si el usuario no es administrador, solo muestra su empresa asignada
        if ($currentUser && !$currentUser->is_admin && $currentUser->empresa_id) {
            $empresas = \App\Models\Empresa::where('id', $currentUser->empresa_id)->get();
        } else {
            $empresas = \App\Models\Empresa::orderBy('nombre')->get();
        }
        
        return view('cash-histories.index', compact('cashHistories', 'sumCaja', 'empresas', 'currentUser'));
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
            
            // Asignar empresa_id si está disponible en la solicitud o usar la empresa del usuario autenticado
            if ($request->has('empresa_id')) {
                $cashHistory->empresa_id = $request->empresa_id;
            } elseif (auth()->user()->empresa_id) {
                $cashHistory->empresa_id = auth()->user()->empresa_id;
            }
            
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
        // Verificar si el usuario es administrador
        if (!Auth::user()->is_admin) {
            return redirect()->route('cash-histories.index')
                ->with('error', 'No tienes permisos para actualizar registros de caja');
        }
        
        $request->validate([
            'monto' => 'required|numeric',
            'estado' => 'required|string',
            'empresa_id' => 'nullable|exists:empresas,id'
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
        // Verificar si el usuario es administrador
        if (!Auth::user()->is_admin) {
            return redirect()->route('cash-histories.index')
                ->with('error', 'No tienes permisos para eliminar registros de caja');
        }
        
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
        // Verificar si el usuario es administrador
        if (!Auth::user()->is_admin) {
            return redirect()->route('cash-histories.index')
                ->with('error', 'No tienes permisos para editar registros de caja');
        }
        
        $empresas = \App\Models\Empresa::orderBy('nombre')->get();
        return view('cash-histories.edit', compact('cashHistory', 'empresas'));
    }

    public function checkStatus(Request $request)
    {
        // Validar el request
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id'
        ]);

        $empresaId = $request->empresa_id;
        
        // Obtener el último registro de caja para esta empresa
        $lastCashHistory = CashHistory::where('empresa_id', $empresaId)
                                     ->latest()
                                     ->first();
        
        // Obtener el valor en caja para esta empresa
        $cajaValue = Caja::where('empresa_id', $empresaId)->sum('valor');
        
        // Determinar estado (Apertura o Cierre)
        $estado = 'Apertura'; // Por defecto, permitir apertura
        if ($lastCashHistory && $lastCashHistory->estado === 'Apertura') {
            $estado = 'Cierre'; // Caja abierta, permitir cierre
        }
        
        return response()->json([
            'estado' => $estado,
            'valor' => $cajaValue,
            'lastCashHistory' => $lastCashHistory
        ]);
    }
}
