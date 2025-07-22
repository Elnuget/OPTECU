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
        $isAdmin = $currentUser->is_admin;
        $query = CashHistory::with(['user', 'empresa']);
        
        // Obtener empresas según el tipo de usuario
        if ($isAdmin) {
            $empresas = \App\Models\Empresa::orderBy('nombre')->get();
        } else {
            // Para usuarios no admin, obtener todas sus empresas asignadas
            $empresas = $currentUser->todasLasEmpresas();
        }
        
        // Si el usuario no es administrador, filtrar solo por sus empresas asignadas
        if (!$isAdmin && $empresas->count() > 0) {
            $empresaIds = $empresas->pluck('id')->toArray();
            $query->whereIn('empresa_id', $empresaIds);
        }
        
        // Filtrar por fecha si se proporciona
        if ($request->has('fecha_filtro') && $request->filled('fecha_filtro')) {
            $query->whereDate('created_at', $request->get('fecha_filtro'));
        }
        
        // Filtrar por empresa específica si se proporciona
        if ($request->has('empresa_id') && $request->filled('empresa_id')) {
            $empresaFiltro = $request->get('empresa_id');
            
            // Para usuarios no admin, verificar que tengan acceso a la empresa solicitada
            if (!$isAdmin) {
                $empresaIds = $empresas->pluck('id')->toArray();
                if (!in_array($empresaFiltro, $empresaIds)) {
                    // Si no tiene acceso, no aplicar filtro específico (mostrará todas sus empresas)
                    $empresaFiltro = null;
                }
            }
            
            if ($empresaFiltro) {
                $query->where('empresa_id', $empresaFiltro);
            }
        }
        
        $cashHistories = $query->latest()->get();
        $sumCaja = Caja::sum('valor');
        
        return view('cash-histories.index', compact('cashHistories', 'sumCaja', 'empresas', 'currentUser'));
    }

    public function store(Request $request)
    {
        try {
            // Log de debug para ver qué datos llegan
            Log::info('CashHistory store request data: ' . json_encode($request->all()));
            
            $request->validate([
                'monto' => 'required|numeric',
                'estado' => 'required|in:Apertura,Cierre',
                'empresa_id' => 'nullable|exists:empresas,id'
            ]);

            $requestedState = $request->get('estado');
            $empresaId = $request->get('empresa_id');
            
            // Si no se proporciona empresa_id, intentar obtenerla del usuario
            if (!$empresaId && auth()->user()->empresa_id) {
                $empresaId = auth()->user()->empresa_id;
            }
            
            // Validar que se tenga una empresa_id válida
            if (!$empresaId) {
                $message = 'No se puede determinar la empresa para esta operación de caja';
                Log::warning('CashHistory store: No empresa_id provided');
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }
                return redirect()->back()->with('error', $message);
            }
            
            // Obtener el último registro para la empresa específica
            $lastRecord = CashHistory::where('empresa_id', $empresaId)
                                    ->latest()
                                    ->first();

            Log::info("CashHistory store: Last record for empresa {$empresaId}: " . ($lastRecord ? $lastRecord->estado : 'None'));

            // Validación específica para cierre de caja
            if ($requestedState === 'Cierre') {
                if (!$lastRecord || $lastRecord->estado !== 'Apertura') {
                    $message = 'No se puede cerrar una caja que no ha sido abierta para esta empresa';
                    Log::warning("CashHistory store: Cannot close unopened cash box for empresa {$empresaId}");
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 400);
                    }
                    return redirect()->back()->with('error', $message);
                }
            }

            // Validación específica para apertura de caja
            if ($requestedState === 'Apertura') {
                if ($lastRecord && $lastRecord->estado === 'Apertura') {
                    $message = 'La caja ya está abierta para esta empresa';
                    Log::warning("CashHistory store: Cash box already open for empresa {$empresaId}");
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 400);
                    }
                    return redirect()->back()->with('error', $message);
                }
            }

            $cashHistory = new CashHistory();
            $cashHistory->monto = floatval($request->get('monto'));
            $cashHistory->estado = $requestedState;
            $cashHistory->user_id = auth()->id();
            $cashHistory->empresa_id = $empresaId;
            
            $savedSuccessfully = $cashHistory->save();
            
            if (!$savedSuccessfully) {
                throw new \Exception('Error al guardar el registro de caja en la base de datos');
            }

            $message = $requestedState === 'Apertura' ? 'Caja abierta exitosamente' : 'Caja cerrada exitosamente';

            Log::info("CashHistory saved successfully - ID: {$cashHistory->id}, Estado: {$requestedState}, Usuario: " . auth()->user()->name . ", Empresa ID: {$empresaId}, Monto: {$cashHistory->monto}");

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'id' => $cashHistory->id,
                        'estado' => $cashHistory->estado,
                        'monto' => $cashHistory->monto,
                        'empresa_id' => $cashHistory->empresa_id,
                        'user_id' => $cashHistory->user_id,
                        'created_at' => $cashHistory->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            }

            return redirect()->route('cash-histories.index')->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('CashHistory validation error: ' . json_encode($e->errors()));
            $errors = collect($e->errors())->flatten();
            $message = 'Datos de entrada inválidos: ' . $errors->implode(', ');
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->with('error', $message);

        } catch (\Exception $e) {
            Log::error('CashHistory store error: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            Log::error('CashHistory store trace: ' . $e->getTraceAsString());
            $message = 'Error al procesar la operación de caja. Por favor, inténtelo de nuevo.';
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'debug' => config('app.debug') ? $e->getMessage() : null
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

        $empresaId = $request->get('empresa_id');
        
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

    /**
     * Abre automáticamente las cajas para un usuario no administrador
     * Solo abre cajas que estén cerradas y no hayan sido abiertas hoy
     */
    public function autoOpenCashForUser($user)
    {
        if ($user->is_admin) {
            return 0; // Los administradores no necesitan apertura automática
        }

        $empresas = $user->todasLasEmpresas();
        $openedCount = 0;

        foreach ($empresas as $empresa) {
            // Verificar si ya existe una apertura hoy
            $aperturaHoy = CashHistory::where('empresa_id', $empresa->id)
                                     ->where('estado', 'Apertura')
                                     ->whereDate('created_at', now())
                                     ->first();

            if ($aperturaHoy) {
                continue; // Ya hay apertura hoy, saltar esta empresa
            }

            // Verificar el último estado de la caja
            $lastHistory = CashHistory::where('empresa_id', $empresa->id)
                                     ->latest()
                                     ->first();

            $isClosed = !$lastHistory || $lastHistory->estado !== 'Apertura';

            if ($isClosed) {
                try {
                    // Obtener el valor actual en caja
                    $sumCaja = Caja::where('empresa_id', $empresa->id)->sum('valor');

                    // Crear el registro de apertura automática
                    $cashHistory = new CashHistory();
                    $cashHistory->monto = intval($sumCaja);
                    $cashHistory->estado = 'Apertura';
                    $cashHistory->user_id = $user->id;
                    $cashHistory->empresa_id = $empresa->id;
                    $cashHistory->save();

                    $openedCount++;

                    Log::info("Apertura automática de caja - Usuario: {$user->name}, Empresa: {$empresa->nombre}, Monto: {$sumCaja}");
                    
                } catch (\Exception $e) {
                    Log::error('Error en apertura automática de caja: ' . $e->getMessage());
                }
            }
        }

        return $openedCount;
    }
    
    /**
     * Método para debuggear el estado de la caja de una empresa específica
     */
    public function debugCashStatus(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $empresaId = $request->get('empresa_id');
        
        $records = CashHistory::where('empresa_id', $empresaId)
                             ->orderBy('created_at', 'desc')
                             ->limit(10)
                             ->get();
        
        $cajaSum = Caja::where('empresa_id', $empresaId)->sum('valor');
        
        return response()->json([
            'empresa_id' => $empresaId,
            'caja_sum' => $cajaSum,
            'recent_records' => $records,
            'last_record' => $records->first()
        ]);
    }
}
