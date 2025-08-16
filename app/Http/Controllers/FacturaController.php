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
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        $declarantes = Declarante::orderBy('nombre')->get();
        return view('facturas.index', compact('declarantes'));
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
            
            // Devolvemos las facturas completas para que la vista pueda acceder a todas las propiedades
            // como declarante.nombre, pedido.cliente, etc.
            
            return response()->json([
                'success' => true,
                'data' => $facturas
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request)
    {
        $declarantes = Declarante::all();
        
        // Verificar si viene un pedido_id en la solicitud
        $pedido = null;
        if ($request->has('pedido_id')) {
            $pedido = \App\Models\Pedido::find($request->pedido_id);
        }
        
        return view('facturas.create', compact('declarantes', 'pedido'));
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
                'pedido_id' => 'required|exists:pedidos,id',
                // Campos de elementos a facturar
                'incluir_examen' => 'nullable|boolean',
                'incluir_armazon' => 'nullable|boolean',
                'incluir_luna' => 'nullable|boolean',
                'incluir_accesorio' => 'nullable|boolean',
                'precio_examen' => 'nullable|numeric|min:0',
                'precio_armazon' => 'nullable|numeric|min:0',
                'precio_luna' => 'nullable|numeric|min:0',
                'precio_accesorio' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validar que al menos un elemento esté seleccionado
            if (!$request->incluir_examen && !$request->incluir_armazon && 
                !$request->incluir_luna && !$request->incluir_accesorio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar al menos un elemento para facturar.'
                ], 422);
            }
            
            // Obtener datos del pedido y declarante
            $pedido = \App\Models\Pedido::findOrFail($request->pedido_id);
            $declarante = Declarante::findOrFail($request->declarante_id);
            
            // Calcular totales según los elementos seleccionados
            $subtotal = 0;
            $iva = 0;
            $elementos = [];
            
            // Examen Visual - 0% IVA (exento)
            if ($request->incluir_examen && $request->precio_examen > 0) {
                $precioExamen = floatval($request->precio_examen);
                $subtotal += $precioExamen;
                $elementos[] = [
                    'tipo' => 'Examen Visual',
                    'descripcion' => $pedido->examen_visual ?: 'Examen Visual',
                    'precio' => $precioExamen,
                    'iva_porcentaje' => 0,
                    'iva_valor' => 0
                ];
            }
            
            // Armazón - 15% IVA
            if ($request->incluir_armazon && $request->precio_armazon > 0) {
                $precioArmazon = floatval($request->precio_armazon);
                $ivaArmazon = $precioArmazon * 0.15;
                $subtotal += $precioArmazon;
                $iva += $ivaArmazon;
                $elementos[] = [
                    'tipo' => 'Armazón',
                    'descripcion' => 'Armazón',
                    'precio' => $precioArmazon,
                    'iva_porcentaje' => 15,
                    'iva_valor' => $ivaArmazon
                ];
            }
            
            // Luna - 15% IVA
            if ($request->incluir_luna && $request->precio_luna > 0) {
                $precioLuna = floatval($request->precio_luna);
                $ivaLuna = $precioLuna * 0.15;
                $subtotal += $precioLuna;
                $iva += $ivaLuna;
                $elementos[] = [
                    'tipo' => 'Luna',
                    'descripcion' => 'Luna',
                    'precio' => $precioLuna,
                    'iva_porcentaje' => 15,
                    'iva_valor' => $ivaLuna
                ];
            }
            
            // Accesorio - 15% IVA
            if ($request->incluir_accesorio && $request->precio_accesorio > 0) {
                $precioAccesorio = floatval($request->precio_accesorio);
                $ivaAccesorio = $precioAccesorio * 0.15;
                $subtotal += $precioAccesorio;
                $iva += $ivaAccesorio;
                $elementos[] = [
                    'tipo' => 'Accesorio',
                    'descripcion' => 'Accesorio',
                    'precio' => $precioAccesorio,
                    'iva_porcentaje' => 15,
                    'iva_valor' => $ivaAccesorio
                ];
            }
            
            $total = $subtotal + $iva;
            
            // Generar XML
            $xmlPath = $this->generarXMLFactura($pedido, $declarante, $elementos, $subtotal, $iva, $total);
            
            // Crear la factura
            $factura = new Factura();
            $factura->declarante_id = $request->declarante_id;
            $factura->pedido_id = $request->pedido_id;
            $factura->xml = $xmlPath;
            $factura->monto = round($subtotal, 2);
            $factura->iva = round($iva, 2);
            $factura->tipo = 'comprobante';
            
            $factura->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Factura creada correctamente',
                'data' => [
                    'factura_id' => $factura->id,
                    'xml_path' => $xmlPath,
                    'subtotal' => $subtotal,
                    'iva' => $iva,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generar XML de la factura
     */
    private function generarXMLFactura($pedido, $declarante, $elementos, $subtotal, $iva, $total)
    {
        try {
            // Crear estructura XML
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><factura></factura>');
            
            // Información general
            $info = $xml->addChild('informacion');
            $info->addChild('numero', 'COMP-' . time());
            $info->addChild('fecha', date('Y-m-d'));
            $info->addChild('hora', date('H:i:s'));
            
            // Información del declarante (emisor)
            $emisor = $xml->addChild('emisor');
            $emisor->addChild('nombre', htmlspecialchars($declarante->nombre));
            $emisor->addChild('ruc', $declarante->ruc);
            if ($declarante->direccion_matriz) {
                $emisor->addChild('direccion', htmlspecialchars($declarante->direccion_matriz));
            }
            
            // Información del cliente
            $cliente = $xml->addChild('cliente');
            $cliente->addChild('nombre', htmlspecialchars($pedido->cliente));
            if ($pedido->cedula) {
                $cliente->addChild('cedula', $pedido->cedula);
            }
            if ($pedido->direccion) {
                $cliente->addChild('direccion', htmlspecialchars($pedido->direccion));
            }
            
            // Información del pedido
            $pedidoXml = $xml->addChild('pedido');
            $pedidoXml->addChild('id', $pedido->id);
            $pedidoXml->addChild('numero_orden', $pedido->numero_orden);
            $pedidoXml->addChild('fecha_pedido', $pedido->fecha ? $pedido->fecha->format('Y-m-d') : date('Y-m-d'));
            
            // Detalles de elementos facturados
            $detalles = $xml->addChild('detalles');
            foreach ($elementos as $elemento) {
                $detalle = $detalles->addChild('detalle');
                $detalle->addChild('tipo', $elemento['tipo']);
                $detalle->addChild('descripcion', htmlspecialchars($elemento['descripcion']));
                $detalle->addChild('precio', number_format($elemento['precio'], 2));
                $detalle->addChild('iva_porcentaje', $elemento['iva_porcentaje']);
                $detalle->addChild('iva_valor', number_format($elemento['iva_valor'], 2));
                $detalle->addChild('total_item', number_format($elemento['precio'] + $elemento['iva_valor'], 2));
            }
            
            // Totales
            $totales = $xml->addChild('totales');
            $totales->addChild('subtotal', number_format($subtotal, 2));
            $totales->addChild('iva_total', number_format($iva, 2));
            $totales->addChild('total', number_format($total, 2));
            
            // Generar nombre del archivo y ruta
            $filename = 'factura_' . $pedido->id . '_' . time() . '.xml';
            $xmlPath = 'facturas/' . $filename;
            $fullPath = storage_path('app/public/' . $xmlPath);
            
            // Crear directorio si no existe
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Guardar el archivo XML
            $xml->asXML($fullPath);
            
            return $xmlPath;
            
        } catch (\Exception $e) {
            throw new \Exception('Error al generar XML: ' . $e->getMessage());
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
