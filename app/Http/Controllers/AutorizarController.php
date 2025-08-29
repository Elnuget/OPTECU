<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutorizarController extends Controller
{
    /**
     * Mostrar la vista de autorización para una factura específica
     */
    public function index($facturaId)
    {
        try {
            // Buscar la factura por ID
            $factura = Factura::with('declarante')->findOrFail($facturaId);
            
            // Verificar que la factura existe
            if (!$factura) {
                return redirect()->route('facturas.index')
                    ->with('error', 'Factura no encontrada.');
            }
            
            // Log para debugging
            Log::info('Accediendo a vista de autorización', [
                'factura_id' => $facturaId,
                'estado' => $factura->estado,
                'declarante' => $factura->declarante->nombre ?? 'Sin declarante'
            ]);
            
            return view('autorizar.index', compact('factura'));
            
        } catch (\Exception $e) {
            Log::error('Error al acceder a vista de autorización', [
                'factura_id' => $facturaId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('facturas.index')
                ->with('error', 'Error al acceder a la vista de autorización: ' . $e->getMessage());
        }
    }
}
