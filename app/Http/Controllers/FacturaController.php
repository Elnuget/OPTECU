<?php

namespace App\Http\Controllers;

use App\Models\Declarante;
use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('facturas.index');
    }

    /**
     * Lista las facturas para AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listar(Request $request)
    {
        try {
            $query = Factura::with('declarante', 'pedido');
            
            // Filtros
            if ($request->has('declarante_id') && $request->declarante_id) {
                $query->where('declarante_id', $request->declarante_id);
            }
            
            if ($request->has('fecha_desde') && $request->fecha_desde) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }
            
            if ($request->has('fecha_hasta') && $request->fecha_hasta) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }
            
            $facturas = $query->orderBy('id', 'desc')->get();
            
            $data = [];
            foreach ($facturas as $factura) {
                $data[] = [
                    'id' => $factura->id,
                    'numero' => $factura->numero ?? 'S/N',
                    'declarante' => $factura->declarante ? $factura->declarante->nombre : 'Sin declarante',
                    'fecha' => Carbon::parse($factura->created_at)->format('d/m/Y'),
                    'total' => $factura->monto + $factura->iva,
                    'estado' => $factura->estado ?? 'pendiente',
                ];
            }
            
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $declarantes = Declarante::all();
        return view('facturas.create', compact('declarantes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'declarante_id' => 'required|exists:declarantes,id',
                'numero' => 'required|string|max:50',
                'fecha' => 'required|date',
                'total' => 'required|numeric|min:0',
                'estado' => 'nullable|string|in:pendiente,pagada,anulada',
                'tipo' => 'nullable|string|in:venta,compra',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $total = floatval($request->total);
            $iva = $total * 0.12;
            $monto = $total - $iva;
            
            $factura = new Factura();
            $factura->declarante_id = $request->declarante_id;
            $factura->numero = $request->numero;
            $factura->monto = $monto;
            $factura->iva = $iva;
            $factura->estado = $request->estado ?? 'pendiente';
            $factura->tipo = $request->tipo ?? 'venta';
            $factura->observaciones = $request->observaciones;
            $factura->created_at = Carbon::parse($request->fecha);
            $factura->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Factura creada correctamente',
                'data' => $factura
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $factura = Factura::with(['declarante', 'pedido.cliente'])->findOrFail($id);
            
            // Cargar pedidos relacionados si existe un pedido asociado
            $pedidos = [];
            if ($factura->pedido) {
                $pedidos[] = $factura->pedido;
            }
            
            $data = $factura->toArray();
            $data['pedidos'] = $pedidos;
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'declarante_id' => 'required|exists:declarantes,id',
                'numero' => 'required|string|max:50',
                'fecha' => 'required|date',
                'total' => 'required|numeric|min:0',
                'estado' => 'nullable|string|in:pendiente,pagada,anulada',
                'tipo' => 'nullable|string|in:venta,compra',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $factura = Factura::findOrFail($id);
            
            $total = floatval($request->total);
            $iva = $total * 0.12;
            $monto = $total - $iva;
            
            $factura->declarante_id = $request->declarante_id;
            $factura->numero = $request->numero;
            $factura->monto = $monto;
            $factura->iva = $iva;
            $factura->estado = $request->estado ?? 'pendiente';
            $factura->tipo = $request->tipo ?? 'venta';
            $factura->observaciones = $request->observaciones;
            
            // Actualizar fecha de creación si es necesario
            if ($request->fecha) {
                $factura->created_at = Carbon::parse($request->fecha);
            }
            
            $factura->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Factura actualizada correctamente',
                'data' => $factura
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $factura = Factura::findOrFail($id);
            
            // Verificar si la factura tiene pedidos asociados
            if ($factura->pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la factura porque tiene pedidos asociados.'
                ], 422);
            }
            
            $factura->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Factura eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar factura: ' . $e->getMessage()
            ], 500);
        }
    }
}
