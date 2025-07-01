<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
        
        // Use current date as default if no date filter is provided
        $fechaFiltro = $request->fecha_filtro ?? now()->format('Y-m-d');
        
        // If no date filter is provided or "todos" is selected, don't apply date filter
        if ($request->has('mostrar_todos') || $request->fecha_filtro === 'todos') {
            // Don't apply date filter
        } else {
            $query->whereDate('created_at', $fechaFiltro);
        }

        // Filter by company if specified
        $empresaFiltro = $request->empresa_filtro ?? ($currentUser->empresa_id ? $currentUser->empresa_id : 'todas');
        
        if ($empresaFiltro && $empresaFiltro !== 'todas') {
            if ($empresaFiltro === 'sin_empresa') {
                $query->whereNull('empresa_id');
            } else {
                $query->where('empresa_id', $empresaFiltro);
            }
        }
        
        $movimientos = $query->latest()->get();
        $totalCaja = $currentUser->is_admin ? Caja::sum('valor') : 0; // Calculate total from all records only for admins
        $empresas = Empresa::all(); // Get all companies for the dropdown
        
        // Calculate total per company
        $totalesPorEmpresa = [];
        
        if ($currentUser->is_admin) {
            // Si es admin, mostrar todas las empresas
            foreach($empresas as $empresa) {
                $totalEmpresa = Caja::where('empresa_id', $empresa->id)->sum('valor');
                $totalesPorEmpresa[] = [
                    'empresa' => $empresa,
                    'total' => $totalEmpresa
                ];
            }
        } else {
            // Si no es admin y tiene empresa, mostrar solo su empresa
            if ($currentUser->empresa_id) {
                $totalEmpresa = Caja::where('empresa_id', $currentUser->empresa_id)->sum('valor');
                $totalesPorEmpresa[] = [
                    'empresa' => $currentUser->empresa,
                    'total' => $totalEmpresa
                ];
            }
        }
        
        // Calculate total for movements without company assigned (solo para admins)
        $totalSinEmpresa = $currentUser->is_admin ? Caja::whereNull('empresa_id')->sum('valor') : 0;
        
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
        if ($request->has('is_positive') && $request->is_positive == 1) {
            // Para cuadrar caja, aseguramos que sea positivo
            $valor = abs($request->valor);
        } else {
            // Para retiros, aseguramos que sea negativo
            $valor = -abs($request->valor);
        }

        // Create Caja entry
        $caja = Caja::create([
            'valor' => $valor,
            'motivo' => $request->motivo,
            'user_id' => Auth::id(),
            'empresa_id' => $request->empresa_id
        ]);

        // Send email notification
        $mensaje = "Se ha registrado un nuevo movimiento en caja.\nMotivo: {$caja->motivo}\nValor: {$caja->valor}";
        $empresas = Empresa::all();
        
        if($empresas->isNotEmpty()) {
            foreach($empresas as $empresa) {
                Mail::raw($mensaje, function ($message) use ($empresa) {
                    $message->to($empresa->correo)
                            ->subject('Nuevo Movimiento en Caja');
                });
            }
        } else {
            Log::info('No registered companies found to send email notifications for cash movement');
        }

        return redirect()->back()->with('success', 'Movimiento registrado exitosamente');
    }

    public function edit(Caja $caja)
    {
        $empresas = Empresa::all();
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
            'valor' => $request->valor,
            'motivo' => $request->motivo,
            'empresa_id' => $request->empresa_id
        ]);

        return redirect()->back()->with('success', 'Movimiento actualizado exitosamente');
    }

    public function destroy(Caja $caja)
    {
        $caja->delete();
        return redirect()->back()->with('success', 'Movimiento eliminado exitosamente');
    }
}
