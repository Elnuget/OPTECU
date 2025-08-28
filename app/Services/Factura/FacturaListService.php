<?php

namespace App\Services\Factura;

use App\Models\Declarante;
use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaListService
{
    /**
     * Obtener datos para la vista index de facturas
     *
     * @return array
     */
    public function getIndexData()
    {
        $declarantes = Declarante::orderBy('nombre')->get();
        
        return [
            'declarantes' => $declarantes
        ];
    }

    /**
     * Listar facturas para AJAX con filtros
     *
     * @param Request $request
     * @return array
     */
    public function listarFacturas(Request $request)
    {
        try {
            $query = Factura::with('declarante', 'pedido');
            
            // Aplicar filtros
            $this->aplicarFiltros($query, $request);
            
            $facturas = $query->orderBy('id', 'desc')->get();
            
            return [
                'success' => true,
                'data' => $facturas
            ];
        } catch (\Exception $e) {
            \Log::error('Error en FacturaListService::listarFacturas: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al cargar facturas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Aplicar filtros a la consulta de facturas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function aplicarFiltros($query, Request $request)
    {
        // Filtro por declarante
        if ($request->has('declarante_id') && $request->declarante_id) {
            $query->where('declarante_id', $request->declarante_id);
        }
        
        // Filtro por fecha desde
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        
        // Filtro por fecha hasta
        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
    }
}
