<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CajaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin')->only(['destroy', 'edit', 'update']);
    }

    public function index(Request $request)
    {
        $query = Caja::with(['user', 'empresa']);
        $currentUser = Auth::user();
        $isAdmin = $currentUser->is_admin;
        
        // Use current date as default if no date filter is provided
        $fechaFiltro = $request->get('fecha_filtro') ?? now()->format('Y-m-d');
        
        // If no date filter is provided or "todos" is selected, don't apply date filter
        if ($request->has('mostrar_todos') || $request->get('fecha_filtro') === 'todos') {
            // Don't apply date filter
        } else {
            $query->whereDate('created_at', $fechaFiltro);
        }

        // Obtener empresas según el tipo de usuario
        if ($isAdmin) {
            $empresas = Empresa::all();
        } else {
            // Para usuarios no admin, obtener todas sus empresas asignadas
            $empresas = $currentUser->todasLasEmpresas();
        }

        // Filter by company if specified
        $empresaFiltro = $request->get('empresa_filtro') ?? ($currentUser->empresa_id ? $currentUser->empresa_id : 'todas');
        
        if ($empresaFiltro && $empresaFiltro !== 'todas') {
            if ($empresaFiltro === 'sin_empresa') {
                $query->whereNull('empresa_id');
            } else {
                // Para usuarios no admin, verificar que tengan acceso a la empresa solicitada
                if (!$isAdmin) {
                    $empresaIds = $empresas->pluck('id')->toArray();
                    if (!in_array($empresaFiltro, $empresaIds)) {
                        // Si no tiene acceso, usar su empresa principal por defecto
                        $empresaFiltro = $currentUser->empresa_id;
                    }
                }
                $query->where('empresa_id', $empresaFiltro);
            }
        }
        
        $movimientos = $query->latest()->get();
        $totalCaja = $isAdmin ? Caja::sum('valor') : 0; // Calculate total from all records only for admins
        
        // Calculate total per company
        $totalesPorEmpresa = [];
        
        if ($isAdmin) {
            // Si es admin, mostrar TODAS LAS EMPRESAS
            foreach($empresas as $empresa) {
                $totalEmpresa = Caja::where('empresa_id', $empresa->id)->sum('valor');
                $totalesPorEmpresa[] = [
                    'empresa' => $empresa,
                    'total' => $totalEmpresa
                ];
            }
        } else {
            // Si no es admin, mostrar totales de sus empresas asignadas
            foreach($empresas as $empresa) {
                $totalEmpresa = Caja::where('empresa_id', $empresa->id)->sum('valor');
                $totalesPorEmpresa[] = [
                    'empresa' => $empresa,
                    'total' => $totalEmpresa
                ];
            }
        }
        
        // Calculate total for movements without company assigned (solo para admins)
        $totalSinEmpresa = $isAdmin ? Caja::whereNull('empresa_id')->sum('valor') : 0;
        
        return view('caja.index', compact('movimientos', 'fechaFiltro', 'totalCaja', 'empresas', 'totalesPorEmpresa', 'totalSinEmpresa', 'empresaFiltro', 'currentUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric',
            'motivo' => 'required|string',
            'user_email' => 'required|email',
            'empresa_id' => 'nullable|exists:empresas,id'
        ]);

        // Verificar si es un cuadre de caja (positivo) o un retiro (negativo)
        if ($request->has('is_positive') && $request->get('is_positive') == 1) {
            // Para cuadrar caja, aseguramos que sea positivo
            $valor = abs($request->get('valor'));
        } else {
            // Para retiros, aseguramos que sea negativo
            $valor = -abs($request->get('valor'));
        }

        // Create Caja entry
        $caja = Caja::create([
            'valor' => $valor,
            'motivo' => $request->get('motivo'),
            'user_id' => Auth::id(),
            'empresa_id' => $request->get('empresa_id')
        ]);

        return redirect()->back()->with('success', 'Movimiento registrado exitosamente');
    }

    public function edit(Caja $caja)
    {
        $currentUser = Auth::user();
        
        if ($currentUser->is_admin) {
            $empresas = Empresa::all();
        } else {
            $empresas = $currentUser->todasLasEmpresas();
        }
        
        return response()->json([
            'caja' => $caja->load('user', 'empresa'),
            'empresas' => $empresas
        ]);
    }

    public function update(Request $request, Caja $caja)
    {
        $request->validate([
            'valor' => 'required|numeric',
            'motivo' => 'required|string',
            'empresa_id' => 'nullable|exists:empresas,id'
        ]);

        // Update the caja entry
        $caja->update([
            'valor' => $request->get('valor'),
            'motivo' => $request->get('motivo'),
            'empresa_id' => $request->get('empresa_id')
        ]);

        return redirect()->back()->with('success', 'Movimiento actualizado exitosamente');
    }

    public function destroy(Caja $caja)
    {
        $caja->delete();
        return redirect()->back()->with('success', 'Movimiento eliminado exitosamente');
    }
}
