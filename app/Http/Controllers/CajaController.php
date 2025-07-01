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
        $this->middleware('can:admin')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Caja::with(['user', 'empresa']);
        
        // Use current date as default if no date filter is provided
        $fechaFiltro = $request->fecha_filtro ?? now()->format('Y-m-d');
        
        // If no date filter is provided or "todos" is selected, don't apply date filter
        if ($request->has('mostrar_todos') || $request->fecha_filtro === 'todos') {
            // Don't apply date filter
        } else {
            $query->whereDate('created_at', $fechaFiltro);
        }
        
        $movimientos = $query->latest()->get();
        $totalCaja = Caja::sum('valor'); // Calculate total from all records
        $empresas = Empresa::all(); // Get all companies for the dropdown
        
        return view('caja.index', compact('movimientos', 'fechaFiltro', 'totalCaja', 'empresas'));
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

    public function destroy(Caja $caja)
    {
        $caja->delete();
        return redirect()->back()->with('success', 'Movimiento eliminado exitosamente');
    }
}
