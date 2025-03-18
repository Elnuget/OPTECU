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
        $query = Caja::with('user');
        
        // Use current date as default if no date filter is provided
        $fechaFiltro = $request->fecha_filtro ?? now()->format('Y-m-d');
        $query->whereDate('created_at', $fechaFiltro);
        
        $movimientos = $query->latest()->get();
        $totalCaja = Caja::sum('valor'); // Calculate total from all records
        
        return view('caja.index', compact('movimientos', 'fechaFiltro', 'totalCaja'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric',
            'motivo' => 'required|string',
            'user_email' => 'required|email'
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
            'user_id' => Auth::id()
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
