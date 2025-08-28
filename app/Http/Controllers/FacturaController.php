<?php

namespace App\Http\Controllers;

use App\Models\Declarante;
use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\FirmaElectronicaService;
use App\Services\Factura\FacturaListService;
use App\Services\Factura\FacturaCalculationService;
use App\Services\Factura\FirmaDigitalService;
use App\Services\SriPythonService;

class FacturaController extends Controller
{
    protected $facturaListService;
    protected $facturaCalculationService;
    protected $firmaDigitalService;
    protected $sriPythonService;

    public function __construct(
        FacturaListService $facturaListService, 
        FacturaCalculationService $facturaCalculationService,
        FirmaDigitalService $firmaDigitalService,
        SriPythonService $sriPythonService
    ) {
        $this->facturaListService = $facturaListService;
        $this->facturaCalculationService = $facturaCalculationService;
        $this->firmaDigitalService = $firmaDigitalService;
        $this->sriPythonService = $sriPythonService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        $data = $this->facturaListService->getIndexData();
        return view('facturas.index', $data);
    }

    /**
     * Lista las facturas para AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listar(Request $request)
    {
        $resultado = $this->facturaListService->listarFacturas($request);
        
        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'data' => $resultado['data']
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $resultado['message']
        ], 500);
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
        $mediosPago = \App\Models\mediosdepago::all();
        
        // Verificar si viene un pedido_id en la solicitud
        $pedido = null;
        if ($request->has('pedido_id')) {
            $pedido = \App\Models\Pedido::with(['pagos.mediodepago'])->find($request->pedido_id);
        }
        
        return view('facturas.create', compact('declarantes', 'pedido', 'mediosPago'));
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
            // Log para depuración
            \Log::info('Datos recibidos en FacturaController::store', $request->all());
            
            $validator = Validator::make($request->all(), [
                'declarante_id' => 'required|exists:declarante,id',
                'medio_pago_xml' => 'required|numeric',
                'pedido_id' => 'nullable|exists:pedidos,id',
                'correo_cliente' => 'nullable|email|max:255',
                'password_certificado' => 'required|string|min:1', // Nueva validación para contraseña
                // Campos de elementos a facturar
                'incluir_examen' => 'nullable',
                'incluir_armazon' => 'nullable',
                'incluir_luna' => 'nullable',
                'incluir_compra_rapida' => 'nullable',
                'precio_examen' => 'nullable|numeric|min:0',
                'precio_armazon' => 'nullable|numeric|min:0',
                'precio_luna' => 'nullable|numeric|min:0',
                'precio_compra_rapida' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validar que al menos un elemento esté seleccionado
            if (!$request->has('incluir_examen') && !$request->has('incluir_armazon') && 
                !$request->has('incluir_luna') && !$request->has('incluir_compra_rapida')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar al menos un elemento para facturar.'
                ], 422);
            }
            
            // Obtener datos del declarante
            $declarante = Declarante::findOrFail($request->declarante_id);
            
            // Obtener el medio de pago seleccionado
            $medioPago = \App\Models\mediosdepago::find($request->medio_pago_xml);
            if (!$medioPago) {
                return response()->json([
                    'success' => false,
                    'message' => 'El medio de pago seleccionado no existe.'
                ], 422);
            }
            
            // Log para verificar el medio de pago obtenido
            \Log::info('Medio de pago obtenido en store', [
                'medio_pago_id' => $request->medio_pago_xml,
                'medio_pago_encontrado' => $medioPago ? get_object_vars($medioPago) : 'NULL',
                'nombre_medio_pago' => $medioPago->medio_de_pago ?? 'NO DISPONIBLE'
            ]);
            
            // Obtener datos del pedido (puede ser null)
            $pedido = null;
            if ($request->pedido_id) {
                $pedido = Pedido::find($request->pedido_id);
                \Log::info('Pedido encontrado', [
                    'pedido_id' => $request->pedido_id,
                    'numero_orden' => $pedido ? $pedido->numero_orden : 'PEDIDO NO ENCONTRADO',
                    'cliente' => $pedido ? $pedido->cliente : 'N/A'
                ]);
                
                // Actualizar correo del pedido si se proporcionó uno nuevo
                if ($pedido && $request->filled('correo_cliente')) {
                    $pedido->correo_electronico = $request->correo_cliente;
                    $pedido->save();
                    \Log::info('Correo del pedido actualizado', [
                        'pedido_id' => $pedido->id,
                        'nuevo_correo' => $request->correo_cliente
                    ]);
                }
            } else {
                \Log::info('No se recibió pedido_id en la solicitud');
            }
            
            // Si no hay pedido, crear datos por defecto
            if (!$pedido) {
                $pedido = (object) [
                    'id' => 0,
                    'cliente' => 'CLIENTE GENERICO',
                    'cedula' => null,
                    'celular' => null,
                    'correo_electronico' => null,
                    'examen_visual' => null,
                    'motivo_compra' => null
                ];
            }
            
            // Calcular totales según los elementos seleccionados
            $subtotal = 0;
            $iva = 0;
            $elementos = [];
            
            // Examen Visual - 0% IVA (exento)
            if ($request->has('incluir_examen') && $request->precio_examen > 0) {
                $precioExamen = floatval($request->precio_examen);
                $subtotal += $precioExamen;
                $elementos[] = [
                    'tipo' => 'Examen Visual',
                    'descripcion' => (is_object($pedido) && property_exists($pedido, 'examen_visual') && $pedido->examen_visual) ? $pedido->examen_visual : 'Examen Visual',
                    'precio' => $precioExamen,
                    'iva_porcentaje' => 0,
                    'iva_valor' => 0
                ];
            }
            
            // Armazón/Accesorios - 15% IVA
            if ($request->has('incluir_armazon') && $request->precio_armazon > 0) {
                $precioArmazon = floatval($request->precio_armazon);
                $ivaArmazon = $precioArmazon * 0.15;
                $subtotal += $precioArmazon;
                $iva += $ivaArmazon;
                $elementos[] = [
                    'tipo' => 'Armazón/Accesorios',
                    'descripcion' => 'Armazón/Accesorios',
                    'precio' => $precioArmazon,
                    'iva_porcentaje' => 15,
                    'iva_valor' => $ivaArmazon
                ];
            }
            
            // Luna - 15% IVA
            if ($request->has('incluir_luna') && $request->precio_luna > 0) {
                $precioLuna = floatval($request->precio_luna);
                $ivaLuna = $precioLuna * 0.15;
                $subtotal += $precioLuna;
                $iva += $ivaLuna;
                $elementos[] = [
                    'tipo' => 'Cristalería',
                    'descripcion' => 'Cristalería',
                    'precio' => $precioLuna,
                    'iva_porcentaje' => 15,
                    'iva_valor' => $ivaLuna
                ];
            }
            
            // Compra Rápida - 0% IVA (exento)
            if ($request->has('incluir_compra_rapida') && $request->precio_compra_rapida >= 0) {
                $precioCompraRapida = floatval($request->precio_compra_rapida);
                $subtotal += $precioCompraRapida;
                $elementos[] = [
                    'tipo' => 'Compra Rápida',
                    'descripcion' => (is_object($pedido) && property_exists($pedido, 'motivo_compra') && $pedido->motivo_compra) ? $pedido->motivo_compra : 'Servicio de compra rápida',
                    'precio' => $precioCompraRapida,
                    'iva_porcentaje' => 0,
                    'iva_valor' => 0
                ];
            }
            
            $total = $subtotal + $iva;
            
            // Validar que se calcularon elementos
            if (empty($elementos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular ningún elemento para la factura. Verifique los precios ingresados.'
                ], 422);
            }
            
            // Verificar que el declarante tenga certificado P12
            if (!$declarante->firma || !file_exists(public_path('uploads/firmas/' . $declarante->firma))) {
                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado P12 válido configurado.'
                ], 422);
            }
            
            // Crear la factura primero con estado temporal
            $factura = new Factura();
            $factura->declarante_id = $request->declarante_id;
            $factura->pedido_id = $request->pedido_id ?: null;
            $factura->xml = 'temp'; // Se actualizará después del procesamiento
            $factura->monto = round($subtotal, 2);
            $factura->iva = round($iva, 2);
            $factura->tipo = 'comprobante';
            $factura->estado = 'PROCESANDO'; // Estado temporal mientras se procesa
            
            if (!$factura->save()) {
                throw new \Exception('No se pudo guardar la factura en la base de datos');
            }
            
            // Procesar con API Python directamente
            try {
                $resultado = $this->sriPythonService->procesarFacturaCompleta(
                    $factura,
                    $declarante,
                    $elementos,
                    $medioPago,
                    $pedido,
                    $request->password_certificado
                );
                
                if ($resultado['success']) {
                    // La factura ya fue actualizada por el servicio
                    $factura->refresh(); // Recargar desde la base de datos
                    
                    return response()->json([
                        'success' => true,
                        'message' => $resultado['message'] ?? 'Factura procesada exitosamente con Python API',
                        'data' => [
                            'factura_id' => $factura->id,
                            'estado' => $factura->estado,
                            'estado_sri' => $factura->estado_sri,
                            'clave_acceso' => $factura->clave_acceso,
                            'numero_autorizacion' => $factura->numero_autorizacion,
                            'fecha_autorizacion' => $factura->fecha_autorizacion?->format('Y-m-d H:i:s'),
                            'subtotal' => $subtotal,
                            'iva' => $iva,
                            'total' => $total
                        ],
                        'redirect_url' => route('facturas.show', $factura->id)
                    ]);
                } else {
                    // Error en el procesamiento - actualizar estado de la factura
                    $factura->estado = 'ERROR';
                    if (isset($resultado['error'])) {
                        $factura->mensajes_sri = is_array($resultado['error']) 
                            ? json_encode($resultado['error'])
                            : $resultado['error'];
                    }
                    $factura->save();
                    
                    return response()->json([
                        'success' => false,
                        'message' => $resultado['message'] ?? 'Error al procesar la factura con Python API',
                        'error' => $resultado['error'] ?? 'Error desconocido'
                    ], 500);
                }
            } catch (\Exception $pythonError) {
                // Error en el procesamiento con Python - actualizar estado de la factura
                $factura->estado = 'ERROR';
                $factura->save();
                
                \Log::error('Error en procesamiento Python: ' . $pythonError->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar con Python API: ' . $pythonError->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error en FacturaController::store: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Procesar factura completa: generar, firmar y enviar usando API Python
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function procesarConPython(Request $request)
    {
        try {
            \Log::info('=== INICIO PROCESAMIENTO CON API PYTHON ===', $request->all());
            
            $validator = Validator::make($request->all(), [
                'declarante_id' => 'required|exists:declarante,id',
                'medio_pago_xml' => 'required|numeric',
                'pedido_id' => 'nullable|exists:pedidos,id',
                'password_certificado' => 'required|string|min:1',
                'correo_cliente' => 'nullable|email|max:255',
                // Campos de elementos a facturar
                'incluir_examen' => 'nullable',
                'incluir_armazon' => 'nullable',
                'incluir_luna' => 'nullable',
                'incluir_compra_rapida' => 'nullable',
                'precio_examen' => 'nullable|numeric|min:0',
                'precio_armazon' => 'nullable|numeric|min:0',
                'precio_luna' => 'nullable|numeric|min:0',
                'precio_compra_rapida' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validar que al menos un elemento esté seleccionado
            if (!$request->has('incluir_examen') && !$request->has('incluir_armazon') && 
                !$request->has('incluir_luna') && !$request->has('incluir_compra_rapida')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar al menos un elemento para facturar.'
                ], 422);
            }
            
            // Obtener datos necesarios
            $declarante = Declarante::findOrFail($request->declarante_id);
            $medioPago = \App\Models\mediosdepago::findOrFail($request->medio_pago_xml);
            
            // Verificar que el declarante tenga certificado P12
            if (!$declarante->firma || !file_exists(public_path('uploads/firmas/' . $declarante->firma))) {
                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado P12 válido configurado.'
                ], 422);
            }
            
            // Obtener pedido si existe
            $pedido = null;
            if ($request->pedido_id) {
                $pedido = Pedido::find($request->pedido_id);
                
                // Actualizar correo del pedido si se proporcionó uno nuevo
                if ($pedido && $request->filled('correo_cliente')) {
                    $pedido->correo_electronico = $request->correo_cliente;
                    $pedido->save();
                }
            }
            
            // Si no hay pedido, crear datos por defecto
            if (!$pedido) {
                $pedido = (object) [
                    'id' => 0,
                    'numero_orden' => null,
                    'cliente' => 'CLIENTE GENERICO',
                    'cedula' => '9999999999',
                    'celular' => null,
                    'correo_electronico' => $request->correo_cliente,
                    'examen_visual' => null,
                    'motivo_compra' => null
                ];
            }
            
            // Preparar elementos para facturación (misma lógica que store original)
            $subtotal = 0;
            $iva = 0;
            $elementos = [];
            
            // Examen Visual - 0% IVA (exento)
            if ($request->has('incluir_examen') && $request->precio_examen > 0) {
                $precioExamen = floatval($request->precio_examen);
                $subtotal += $precioExamen;
                $elementos[] = [
                    'codigo' => 'EXA001',
                    'descripcion' => (is_object($pedido) && property_exists($pedido, 'examen_visual') && $pedido->examen_visual) ? $pedido->examen_visual : 'Examen Visual',
                    'cantidad' => 1,
                    'precio_unitario' => $precioExamen,
                    'subtotal' => $precioExamen,
                    'codigo_porcentaje' => '0', // 0% IVA
                    'tarifa' => '0',
                    'valor_impuesto' => 0
                ];
            }
            
            // Armazón/Accesorios - 15% IVA
            if ($request->has('incluir_armazon') && $request->precio_armazon > 0) {
                $precioArmazon = floatval($request->precio_armazon);
                $ivaArmazon = $precioArmazon * 0.15;
                $subtotal += $precioArmazon;
                $iva += $ivaArmazon;
                $elementos[] = [
                    'codigo' => 'ARM001',
                    'descripcion' => 'Armazon/Accesorios',
                    'cantidad' => 1,
                    'precio_unitario' => $precioArmazon,
                    'subtotal' => $precioArmazon,
                    'codigo_porcentaje' => '6', // 15% IVA
                    'tarifa' => '15',
                    'valor_impuesto' => $ivaArmazon
                ];
            }
            
            // Luna - 15% IVA
            if ($request->has('incluir_luna') && $request->precio_luna > 0) {
                $precioLuna = floatval($request->precio_luna);
                $ivaLuna = $precioLuna * 0.15;
                $subtotal += $precioLuna;
                $iva += $ivaLuna;
                $elementos[] = [
                    'codigo' => 'LUN001',
                    'descripcion' => 'Cristaleria',
                    'cantidad' => 1,
                    'precio_unitario' => $precioLuna,
                    'subtotal' => $precioLuna,
                    'codigo_porcentaje' => '6', // 15% IVA
                    'tarifa' => '15',
                    'valor_impuesto' => $ivaLuna
                ];
            }
            
            // Compra Rápida - 0% IVA (exento)
            if ($request->has('incluir_compra_rapida') && $request->precio_compra_rapida >= 0) {
                $precioCompraRapida = floatval($request->precio_compra_rapida);
                $subtotal += $precioCompraRapida;
                $elementos[] = [
                    'codigo' => 'COM001',
                    'descripcion' => (is_object($pedido) && property_exists($pedido, 'motivo_compra') && $pedido->motivo_compra) ? $pedido->motivo_compra : 'Servicio de compra rapida',
                    'cantidad' => 1,
                    'precio_unitario' => $precioCompraRapida,
                    'subtotal' => $precioCompraRapida,
                    'codigo_porcentaje' => '0', // 0% IVA
                    'tarifa' => '0',
                    'valor_impuesto' => 0
                ];
            }
            
            $total = $subtotal + $iva;
            
            // Validar que se calcularon elementos
            if (empty($elementos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular ningún elemento para la factura. Verifique los precios ingresados.'
                ], 422);
            }
            
            // Crear la factura inicialmente
            $factura = new Factura();
            $factura->declarante_id = $request->declarante_id;
            $factura->pedido_id = $request->pedido_id ?: null;
            $factura->monto = round($subtotal, 2);
            $factura->iva = round($iva, 2);
            $factura->tipo = 'comprobante';
            $factura->estado = 'PROCESANDO'; // Estado temporal
            
            if (!$factura->save()) {
                throw new \Exception('No se pudo guardar la factura en la base de datos');
            }
            
            // Procesar con API Python
            $resultado = $this->sriPythonService->procesarFacturaCompleta(
                $factura,
                $declarante,
                $pedido,
                $elementos,
                $subtotal,
                $iva,
                $total,
                $medioPago,
                $request->password_certificado
            );
            
            if (!$resultado['success']) {
                // Si falla, actualizar estado de la factura
                $factura->estado = 'ERROR';
                $factura->save();
                
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                    'errors' => $resultado['errors'] ?? []
                ], 500);
            }
            
            \Log::info('=== FIN PROCESAMIENTO EXITOSO CON API PYTHON ===', [
                'factura_id' => $factura->id,
                'estado_final' => $resultado['factura']->estado
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'data' => [
                    'factura_id' => $factura->id,
                    'clave_acceso' => $resultado['clave_acceso'],
                    'recibida' => $resultado['recibida'],
                    'autorizada' => $resultado['autorizada'],
                    'subtotal' => $subtotal,
                    'iva' => $iva,
                    'total' => $total
                ],
                'redirect_url' => route('facturas.show', $factura->id)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('=== ERROR EN PROCESAMIENTO CON API PYTHON ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar factura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generar XML de la factura según formato Ecuador
```
     */
    private function generarXMLFactura($pedido, $declarante, $elementos, $subtotal, $iva, $total, $medioPago)
    {
        try {
            // Verificar que hay elementos para facturar
            if (empty($elementos)) {
                throw new \Exception('No hay elementos para incluir en la factura');
            }
            
            // Obtener secuencial del número de orden del pedido
            $secuencial = '000000001'; // Valor por defecto si no hay pedido
            
            \Log::info('Iniciando generación de secuencial', [
                'pedido_es_objeto' => is_object($pedido),
                'pedido_tipo' => gettype($pedido),
                'pedido_data' => is_object($pedido) ? get_object_vars($pedido) : $pedido
            ]);
            
            if (is_object($pedido) && isset($pedido->numero_orden) && !empty($pedido->numero_orden)) {
                \Log::info('Procesando número de orden', [
                    'numero_orden_original' => $pedido->numero_orden,
                    'tipo_numero_orden' => gettype($pedido->numero_orden)
                ]);
                
                // Extraer solo los números del número de orden
                $numeroOrden = preg_replace('/[^0-9]/', '', (string)$pedido->numero_orden);
                
                \Log::info('Número de orden procesado', [
                    'numero_orden_limpio' => $numeroOrden,
                    'longitud' => strlen($numeroOrden)
                ]);
                
                if (!empty($numeroOrden)) {
                    // Asegurar que tenga máximo 9 dígitos
                    if (strlen($numeroOrden) > 9) {
                        $numeroOrden = substr($numeroOrden, -9); // Tomar los últimos 9 dígitos
                        \Log::info('Número truncado a 9 dígitos', ['numero_truncado' => $numeroOrden]);
                    }
                    $secuencial = str_pad($numeroOrden, 9, '0', STR_PAD_LEFT);
                    \Log::info('Secuencial generado desde número de orden', ['secuencial' => $secuencial]);
                } else {
                    // Si no hay números en el numero_orden, usar timestamp
                    $secuencial = str_pad(substr(time(), -9), 9, '0', STR_PAD_LEFT);
                    \Log::warning('No se encontraron números en numero_orden, usando timestamp', ['secuencial' => $secuencial]);
                }
            } else {
                // Si no hay pedido válido, usar un número secuencial basado en timestamp
                $secuencial = str_pad(substr(time(), -9), 9, '0', STR_PAD_LEFT);
                \Log::warning('No hay pedido válido o numero_orden vacío, usando timestamp', [
                    'secuencial' => $secuencial,
                    'razon' => !is_object($pedido) ? 'No es objeto' : (!isset($pedido->numero_orden) ? 'No tiene numero_orden' : 'numero_orden vacío')
                ]);
            }
            
            // Generar clave de acceso según especificaciones del SRI (49 dígitos)
            $fechaEmision = date('dmY'); // 8 dígitos: ddmmaaaa
            $tipoComprobante = '01'; // 2 dígitos: 01 = Factura
            $ruc = str_pad($declarante->ruc ?? '9999999999999', 13, '0', STR_PAD_LEFT); // 13 dígitos exactos
            
            \Log::info('Generando clave de acceso', [
                'declarante_id' => $declarante->id,
                'ruc_original' => $declarante->ruc,
                'ruc_formateado' => $ruc,
                'ruc_length' => strlen($ruc)
            ]);
            
            $tipoAmbiente = '1'; // 1 dígito: 1 = Pruebas, 2 = Producción
            $establecimiento = str_pad($declarante->establecimiento ?? '001', 3, '0', STR_PAD_LEFT); // 3 dígitos exactos
            $puntoEmision = str_pad($declarante->punto_emision ?? '001', 3, '0', STR_PAD_LEFT); // 3 dígitos exactos
            $serie = $establecimiento . $puntoEmision; // 6 dígitos: estab (3) + punto (3)
            $numeroComprobante = $secuencial; // 9 dígitos
            $codigoNumerico = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT); // 8 dígitos aleatorios
            $tipoEmision = '1'; // 1 dígito: 1 = Emisión normal
            
            // Construir los primeros 48 dígitos
            $claveAcceso48 = $fechaEmision . $tipoComprobante . $ruc . $tipoAmbiente . $serie . $numeroComprobante . $codigoNumerico . $tipoEmision;
            
            // Verificar que los primeros 48 dígitos tengan la longitud correcta
            if (strlen($claveAcceso48) !== 48) {
                throw new \Exception('Error en la generación de clave de acceso: longitud incorrecta (' . strlen($claveAcceso48) . ' dígitos)');
            }
            
            // Calcular dígito verificador (módulo 11)
            $digitoVerificador = $this->calcularDigitoVerificador($claveAcceso48);
            
            // Clave de acceso completa (49 dígitos)
            $claveAcceso = $claveAcceso48 . $digitoVerificador;
            
            // Log para depuración final
            \Log::info('Secuencial final y clave de acceso', [
                'pedido_id' => is_object($pedido) ? ($pedido->id ?? 'N/A') : 'Sin pedido',
                'pedido_numero_orden' => is_object($pedido) ? ($pedido->numero_orden ?? 'N/A') : 'Sin pedido',
                'secuencial_final' => $secuencial,
                'numero_comprobante_usado' => $numeroComprobante,
                'clave_acceso_48_digitos' => $claveAcceso48,
                'digito_verificador' => $digitoVerificador,
                'clave_acceso_completa' => $claveAcceso,
                'longitud_clave_acceso' => strlen($claveAcceso)
            ]);
            
            // Crear DOM document para mejor control del XML
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;
            
            // Elemento raíz
            $factura = $dom->createElement('factura');
            $factura->setAttribute('id', 'comprobante');
            $factura->setAttribute('version', '2.1.0');
            $dom->appendChild($factura);
            
            // Información tributaria
            $infoTributaria = $dom->createElement('infoTributaria');
            $infoTributaria->appendChild($dom->createElement('ambiente', '1'));
            $infoTributaria->appendChild($dom->createElement('tipoEmision', '1'));
            $infoTributaria->appendChild($dom->createElement('razonSocial', $this->limpiarTextoParaXML($declarante->nombre ?? 'RAZON SOCIAL')));
            $infoTributaria->appendChild($dom->createElement('ruc', $ruc));
            $infoTributaria->appendChild($dom->createElement('claveAcceso', $claveAcceso));
            $infoTributaria->appendChild($dom->createElement('codDoc', '01'));
            $infoTributaria->appendChild($dom->createElement('estab', $establecimiento));
            $infoTributaria->appendChild($dom->createElement('ptoEmi', $puntoEmision));
            $infoTributaria->appendChild($dom->createElement('secuencial', $secuencial));
            $infoTributaria->appendChild($dom->createElement('dirMatriz', $this->limpiarTextoParaXML($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA')));
            $factura->appendChild($infoTributaria);
            
            // Información de la factura
            $infoFactura = $dom->createElement('infoFactura');
            $infoFactura->appendChild($dom->createElement('fechaEmision', date('d/m/Y')));
            $infoFactura->appendChild($dom->createElement('dirEstablecimiento', $this->limpiarTextoParaXML($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA')));
            $infoFactura->appendChild($dom->createElement('obligadoContabilidad', ($declarante->obligado_contabilidad ?? false) ? 'SI' : 'NO'));
            
            // Siempre usar datos del pedido para el comprador
            $infoFactura->appendChild($dom->createElement('tipoIdentificacionComprador', '05'));
            $infoFactura->appendChild($dom->createElement('razonSocialComprador', $this->limpiarTextoParaXML($pedido->cliente ?? 'CLIENTE NO ESPECIFICADO')));
            $infoFactura->appendChild($dom->createElement('identificacionComprador', $pedido->cedula ?? '9999999999'));
            
            $infoFactura->appendChild($dom->createElement('totalSinImpuestos', number_format($subtotal, 2, '.', '')));
            $infoFactura->appendChild($dom->createElement('totalDescuento', '0.00'));
            
            // Total con impuestos
            $totalConImpuestos = $dom->createElement('totalConImpuestos');
            if ($iva > 0) {
                $totalImpuesto = $dom->createElement('totalImpuesto');
                $totalImpuesto->appendChild($dom->createElement('codigo', '2')); // IVA
                $totalImpuesto->appendChild($dom->createElement('codigoPorcentaje', '4')); // 15%
                $totalImpuesto->appendChild($dom->createElement('baseImponible', number_format($subtotal - $this->calcularSubtotalExento($elementos), 2, '.', '')));
                $totalImpuesto->appendChild($dom->createElement('valor', number_format($iva, 2, '.', '')));
                $totalConImpuestos->appendChild($totalImpuesto);
            }
            
            // Si hay elementos exentos, agregar impuesto 0%
            $subtotalExento = $this->calcularSubtotalExento($elementos);
            if ($subtotalExento > 0) {
                $totalImpuestoExento = $dom->createElement('totalImpuesto');
                $totalImpuestoExento->appendChild($dom->createElement('codigo', '2')); // IVA
                $totalImpuestoExento->appendChild($dom->createElement('codigoPorcentaje', '0')); // 0%
                $totalImpuestoExento->appendChild($dom->createElement('baseImponible', number_format($subtotalExento, 2, '.', '')));
                $totalImpuestoExento->appendChild($dom->createElement('valor', '0.00'));
                $totalConImpuestos->appendChild($totalImpuestoExento);
            }
            
            $infoFactura->appendChild($totalConImpuestos);
            $infoFactura->appendChild($dom->createElement('propina', '0.00'));
            $infoFactura->appendChild($dom->createElement('importeTotal', number_format($total, 2, '.', '')));
            $infoFactura->appendChild($dom->createElement('moneda', 'DOLAR'));
            
            // Pagos
            $pagos = $dom->createElement('pagos');
            $pago = $dom->createElement('pago');
            
            // Mapear el medio de pago a códigos del SRI
            $formaPago = '01'; // Valor por defecto: Sin sistema financiero
            
            // Log para verificar que el medio de pago llega correctamente
            \Log::info('Medio de pago recibido en generarXMLFactura', [
                'medio_pago_objeto' => $medioPago ? get_object_vars($medioPago) : 'NULL',
                'tipo_objeto' => gettype($medioPago),
                'existe_propiedad_medio_de_pago' => isset($medioPago->medio_de_pago),
                'valor_medio_de_pago' => $medioPago->medio_de_pago ?? 'NO EXISTE'
            ]);
            
            $medioPagoNombre = strtolower(trim($medioPago->medio_de_pago ?? ''));
            
            // Mapeo específico según los medios de pago de la empresa
            // Nombres exactos de la BD: Efectivo, Transferencia, Tarjeta Débito, Tarjeta Crédito, 
            // Tarjeta Banco, Transferencia Pichincha, Transferencia Guayaquil, Transferencia De Una
            switch ($medioPagoNombre) {
                case 'efectivo':
                    $formaPago = '01'; // Sin sistema financiero
                    break;
                case 'transferencia':
                case 'transferencia pichincha':
                case 'transferencia guayaquil':
                case 'transferencia de una':
                    $formaPago = '20'; // Transferencia de fondos
                    break;
                case 'tarjeta débito':
                case 'tarjeta banco':
                    $formaPago = '16'; // Tarjeta de débito
                    break;
                case 'tarjeta crédito':
                    $formaPago = '19'; // Tarjeta de crédito
                    break;
                default:
                    // Si no coincide exactamente, usar lógica de detección por palabras clave
                    if (strpos($medioPagoNombre, 'efectivo') !== false) {
                        $formaPago = '01';
                    } elseif (strpos($medioPagoNombre, 'transferencia') !== false) {
                        $formaPago = '20';
                    } elseif (strpos($medioPagoNombre, 'débito') !== false || strpos($medioPagoNombre, 'debito') !== false || strpos($medioPagoNombre, 'banco') !== false) {
                        $formaPago = '16';
                    } elseif (strpos($medioPagoNombre, 'crédito') !== false || strpos($medioPagoNombre, 'credito') !== false) {
                        $formaPago = '19';
                    }
                    break;
            }
            
            // Log para depuración
            \Log::info('Medio de pago mapeado para XML', [
                'medio_pago_original' => $medioPago->medio_de_pago ?? 'NO DISPONIBLE',
                'medio_pago_lower' => $medioPagoNombre,
                'forma_pago_sri' => $formaPago,
                'coincidencia_exacta' => in_array($medioPagoNombre, [
                    'efectivo', 'transferencia', 'transferencia pichincha', 
                    'transferencia guayaquil', 'transferencia de una',
                    'tarjeta débito', 'tarjeta banco', 'tarjeta crédito'
                ]),
                'entro_en_switch' => !empty($medioPagoNombre),
                'mapeo_resultado' => [
                    '01' => 'EFECTIVO',
                    '16' => 'TARJETA DÉBITO/BANCO', 
                    '19' => 'TARJETA CRÉDITO',
                    '20' => 'TRANSFERENCIAS'
                ][$formaPago] ?? 'DESCONOCIDO'
            ]);
            
            \Log::info('Forma de pago que se incluirá en XML', [
                'formaPago' => $formaPago,
                'elemento_xml' => '<formaPago>' . $formaPago . '</formaPago>'
            ]);
            
            $pago->appendChild($dom->createElement('formaPago', $formaPago));
            $pago->appendChild($dom->createElement('total', number_format($total, 2, '.', '')));
            $pago->appendChild($dom->createElement('plazo', '0'));
            $pago->appendChild($dom->createElement('unidadTiempo', 'dias'));
            $pagos->appendChild($pago);
            $infoFactura->appendChild($pagos);
            
            $factura->appendChild($infoFactura);
            
            // Detalles
            $detalles = $dom->createElement('detalles');
            foreach ($elementos as $elemento) {
                $detalle = $dom->createElement('detalle');
                $detalle->appendChild($dom->createElement('codigoPrincipal', strtoupper(str_replace([' ', '/'], '', $elemento['tipo']))));
                $detalle->appendChild($dom->createElement('descripcion', $this->limpiarTextoParaXML($elemento['descripcion'])));
                $detalle->appendChild($dom->createElement('cantidad', '1.00'));
                $detalle->appendChild($dom->createElement('precioUnitario', number_format($elemento['precio'], 2, '.', '')));
                $detalle->appendChild($dom->createElement('descuento', '0.00'));
                $detalle->appendChild($dom->createElement('precioTotalSinImpuesto', number_format($elemento['precio'], 2, '.', '')));
                
                // Impuestos del detalle
                $impuestos = $dom->createElement('impuestos');
                $impuesto = $dom->createElement('impuesto');
                $impuesto->appendChild($dom->createElement('codigo', '2')); // IVA
                
                if ($elemento['iva_porcentaje'] > 0) {
                    $impuesto->appendChild($dom->createElement('codigoPorcentaje', '4')); // 15%
                    $impuesto->appendChild($dom->createElement('tarifa', '15.00'));
                } else {
                    $impuesto->appendChild($dom->createElement('codigoPorcentaje', '0')); // 0%
                    $impuesto->appendChild($dom->createElement('tarifa', '0.00'));
                }
                
                $impuesto->appendChild($dom->createElement('baseImponible', number_format($elemento['precio'], 2, '.', '')));
                $impuesto->appendChild($dom->createElement('valor', number_format($elemento['iva_valor'], 2, '.', '')));
                $impuestos->appendChild($impuesto);
                $detalle->appendChild($impuestos);
                
                $detalles->appendChild($detalle);
            }
            $factura->appendChild($detalles);
            
            // Información adicional
            $infoAdicional = $dom->createElement('infoAdicional');
            
            // Agregar teléfono si existe
            if (is_object($pedido) && property_exists($pedido, 'celular') && $pedido->celular) {
                $campoTelefono = $dom->createElement('campoAdicional', $this->limpiarTextoParaXML($pedido->celular));
                $campoTelefono->setAttribute('nombre', 'Telefono');
                $infoAdicional->appendChild($campoTelefono);
            }
            
            // Agregar email si existe
            if (is_object($pedido) && property_exists($pedido, 'correo_electronico') && $pedido->correo_electronico) {
                $campoEmail = $dom->createElement('campoAdicional', $this->limpiarTextoParaXML($pedido->correo_electronico));
                $campoEmail->setAttribute('nombre', 'Email');
                $infoAdicional->appendChild($campoEmail);
            }
            
            // Si no hay campos adicionales, agregar uno por defecto (requerido por el SRI)
            if (!$infoAdicional->hasChildNodes()) {
                $campoDefault = $dom->createElement('campoAdicional', 'SISTEMA DE FACTURACION ELECTRONICA');
                $campoDefault->setAttribute('nombre', 'Observaciones');
                $infoAdicional->appendChild($campoDefault);
            }
            
            $factura->appendChild($infoAdicional);
            
            // Guardar archivo XML
            $xmlPath = 'facturas/' . date('Y/m');
            $xmlFullPath = storage_path('app/public/' . $xmlPath);
            
            // Crear directorio si no existe
            if (!is_dir($xmlFullPath)) {
                if (!mkdir($xmlFullPath, 0755, true)) {
                    throw new \Exception('No se pudo crear el directorio para guardar el XML');
                }
            }
            
            $xmlFileName = 'factura_' . $secuencial . '_' . time() . '.xml';
            $xmlFilePath = $xmlFullPath . DIRECTORY_SEPARATOR . $xmlFileName;
            
            if (!$dom->save($xmlFilePath)) {
                throw new \Exception('No se pudo guardar el archivo XML');
            }
            
            return $xmlPath . '/' . $xmlFileName;
            
        } catch (\Exception $e) {
            throw new \Exception('Error al generar XML: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcular subtotal de elementos exentos de IVA
     */
    private function calcularSubtotalExento($elementos)
    {
        return $this->facturaCalculationService->calcularSubtotalExento($elementos);
    }
    
    /**
     * Calcular dígito verificador usando módulo 11
     */
    private function calcularDigitoVerificador($claveAcceso48)
    {
        return $this->facturaCalculationService->calcularDigitoVerificador($claveAcceso48);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        try {
            $factura = Factura::with(['declarante', 'pedido'])->findOrFail($id);
            
            // Procesar mensajes del SRI para la vista
            $mensajesSriProcesados = null;
            if ($factura->mensajes_sri) {
                try {
                    \Log::info('Procesando mensajes SRI para vista', [
                        'factura_id' => $id,
                        'mensajes_sri_raw' => $factura->mensajes_sri,
                        'tipo_mensajes_sri' => gettype($factura->mensajes_sri)
                    ]);
                    
                    $mensajesSriProcesados = is_string($factura->mensajes_sri) 
                        ? json_decode($factura->mensajes_sri, true) 
                        : $factura->mensajes_sri;
                        
                    // Asegurarse de que sea un array
                    if (!is_array($mensajesSriProcesados)) {
                        $mensajesSriProcesados = [$factura->mensajes_sri];
                    }
                    
                    \Log::info('Mensajes SRI procesados para vista', [
                        'factura_id' => $id,
                        'mensajes_procesados' => $mensajesSriProcesados,
                        'total_mensajes' => count($mensajesSriProcesados)
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error procesando mensajes SRI para vista', [
                        'factura_id' => $id,
                        'error' => $e->getMessage(),
                        'mensajes_sri_original' => $factura->mensajes_sri
                    ]);
                    
                    // Si hay error al decodificar, usar el valor original como string
                    $mensajesSriProcesados = [
                        [
                            'mensaje' => is_string($factura->mensajes_sri) ? $factura->mensajes_sri : 'Error procesando mensajes del SRI',
                            'tipo' => 'ERROR',
                            'identificador' => 'PROCESAMIENTO',
                            'informacionAdicional' => 'No se pudieron procesar los mensajes del SRI correctamente'
                        ]
                    ];
                }
            }
            
            // Agregar los mensajes procesados a la factura
            $factura->mensajes_sri_procesados = $mensajesSriProcesados;
            
            // Leer el contenido del XML si existe
            $xmlContent = null;
            $xmlFormatted = null;
            if ($factura->xml) {
                $xmlPath = storage_path('app/public/' . $factura->xml);
                if (file_exists($xmlPath)) {
                    $xmlContent = file_get_contents($xmlPath);
                    
                    // Formatear el XML para mejor visualización
                    $dom = new \DOMDocument();
                    $dom->preserveWhiteSpace = false;
                    $dom->formatOutput = true;
                    if ($dom->loadXML($xmlContent)) {
                        $xmlFormatted = $dom->saveXML();
                    }
                }
            }
            
            // Si es una petición AJAX, devolver JSON
            if (request()->wantsJson()) {
                $pedidos = [];
                if ($factura->pedido) {
                    $pedidos[] = $factura->pedido;
                }
                
                $data = $factura->toArray();
                $data['pedidos'] = $pedidos;
                $data['xml_content'] = $xmlContent;
                $data['mensajes_sri_procesados'] = $mensajesSriProcesados;
                
                \Log::info('Devolviendo factura via JSON', [
                    'factura_id' => $id,
                    'tiene_mensajes_sri' => !empty($mensajesSriProcesados),
                    'total_mensajes' => $mensajesSriProcesados ? count($mensajesSriProcesados) : 0
                ]);
                
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }
            
            // Verificar si el declarante tiene certificado P12
            $tieneCertificadoP12 = false;
            if ($factura->declarante && $factura->declarante->firma) {
                $rutaCertificado = public_path('uploads/firmas/' . $factura->declarante->firma);
                $tieneCertificadoP12 = file_exists($rutaCertificado) && 
                                      (pathinfo($factura->declarante->firma, PATHINFO_EXTENSION) === 'p12');
            }

            // Si no es AJAX, devolver vista HTML
            return view('facturas.show', compact('factura', 'xmlContent', 'xmlFormatted', 'tieneCertificadoP12'));
            
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar factura: ' . $e->getMessage()
                ], 500);
            }
            
            // Para vistas HTML, redirigir con error
            return redirect()->route('facturas.index')->with('error', 'Error al cargar factura: ' . $e->getMessage());
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

    /**
     * Firmar digitalmente y enviar factura al SRI usando servicios Python
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function firmarYEnviar($id, Request $request)
    {
        try {
            \Log::info('=== INICIO PROCESO FIRMA Y ENVÍO CON PYTHON ===', [
                'factura_id' => $id,
                'user_agent' => $request->userAgent()
            ]);

            // Validar entrada
            $validator = Validator::make($request->all(), [
                'password_certificado' => 'required|string|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            // Obtener la factura
            $factura = Factura::with('declarante')->findOrFail($id);
            
            \Log::info('Factura encontrada', [
                'factura_id' => $factura->id,
                'estado_actual' => $factura->estado ?? 'SIN_ESTADO'
            ]);

            // Verificar que existe el XML
            if (!$factura->xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene un XML generado'
                ], 422);
            }

            // Verificar que el declarante tiene certificado
            if (!isset($factura->declarante->firma) || !$factura->declarante->firma) {
                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado digital configurado'
                ], 422);
            }

            // Usar el servicio Python para firmar y enviar
            $resultado = $this->firmaDigitalService->firmarYEnviarCompleto($factura, $request->password_certificado);
            
            if (!$resultado['success']) {
                \Log::error('Error en proceso completo Python', [
                    'factura_id' => $id,
                    'error' => $resultado['error']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $resultado['error']
                ], 422);
            }

            \Log::info('=== FIN PROCESO FIRMA Y ENVÍO EXITOSO CON PYTHON ===', [
                'factura_id' => $id,
                'estado_final' => $resultado['factura']->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Factura firmada y enviada exitosamente al SRI',
                'data' => [
                    'estado' => $resultado['factura']->estado,
                    'fecha_firma' => $resultado['factura']->fecha_firma ? $resultado['factura']->fecha_firma->format('d/m/Y H:i:s') : 'N/A',
                    'fecha_envio_sri' => $resultado['factura']->fecha_envio_sri ? $resultado['factura']->fecha_envio_sri->format('d/m/Y H:i:s') : 'N/A',
                    'xml_firmado_path' => $resultado['factura']->xml_firmado,
                    'resultado_sri' => $resultado['resultado_envio']
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('=== ERROR CRÍTICO EN FIRMA Y ENVÍO CON PYTHON ===', [
                'factura_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Firmar XML con certificado digital PEM
     *
     * @param  string  $xmlContent
     * @param  object  $declarante
     * @param  string  $password
     * @return string|false
     */
    private function firmarXML($xmlContent, $declarante, $password)
    {
        try {
            \Log::info('=== INICIO PROCESO FIRMA XML CON PEM ===', [
                'declarante_id' => $declarante->id,
                'declarante_nombre' => $declarante->nombre,
                'firma' => $declarante->firma,
                'xml_length' => strlen($xmlContent)
            ]);

            // Obtener el certificado PEM
            $certificadoPath = $this->obtenerRutaCertificadoPEM($declarante);
            
            \Log::info('Ruta del certificado PEM', [
                'certificado_path' => $certificadoPath,
                'file_exists' => $certificadoPath ? file_exists($certificadoPath) : false
            ]);
            
            if (!$certificadoPath || !file_exists($certificadoPath)) {
                \Log::error('Certificado PEM no encontrado', [
                    'certificado_path' => $certificadoPath,
                    'declarante_firma' => $declarante->firma
                ]);
                throw new \Exception('No se encontró el archivo del certificado PEM en: ' . $certificadoPath);
            }

            // Leer el certificado PEM
            $certificadoContent = file_get_contents($certificadoPath);
            if (!$certificadoContent) {
                \Log::error('Error al leer certificado PEM', ['certificado_path' => $certificadoPath]);
                throw new \Exception('No se pudo leer el contenido del certificado PEM');
            }

            \Log::info('Certificado PEM leído', [
                'certificado_size' => strlen($certificadoContent),
                'password_length' => strlen($password),
                'contains_private_key' => strpos($certificadoContent, '-----BEGIN PRIVATE KEY-----') !== false,
                'contains_certificate' => strpos($certificadoContent, '-----BEGIN CERTIFICATE-----') !== false
            ]);

            // Extraer certificado y clave privada del archivo PEM
            $certificado = null;
            $clavePrivada = null;

            // Leer el certificado
            $certificado = openssl_x509_read($certificadoContent);
            if (!$certificado) {
                \Log::error('Error al leer certificado X509 del archivo PEM');
                throw new \Exception('El archivo PEM no contiene un certificado X509 válido');
            }

            // Leer la clave privada (puede requerir contraseña)
            $clavePrivada = openssl_pkey_get_private($certificadoContent, $password);
            if (!$clavePrivada) {
                // Intentar sin contraseña
                $clavePrivada = openssl_pkey_get_private($certificadoContent);
                if (!$clavePrivada) {
                    $openssl_error = openssl_error_string();
                    \Log::error('Error al leer clave privada PEM', [
                        'openssl_error' => $openssl_error,
                        'password_provided' => !empty($password)
                    ]);
                    throw new \Exception('No se pudo leer la clave privada del certificado PEM. Verifique la contraseña.');
                }
            }

            \Log::info('Certificado PEM procesado exitosamente', [
                'certificate_valid' => !empty($certificado),
                'private_key_valid' => $clavePrivada !== false,
                'private_key_type' => gettype($clavePrivada)
            ]);

            // VALIDACIÓN CRÍTICA: Verificar que el RUC del certificado coincida con el del declarante
            $this->validarRUCCertificado($certificado, $declarante);

            // Aplicar firma XAdES-BES con PEM
            $xmlFirmado = $this->aplicarFirmaXAdESPEM($xmlContent, $certificado, $clavePrivada);

            \Log::info('=== FIN PROCESO FIRMA XML PEM EXITOSO ===', [
                'xml_firmado_length' => strlen($xmlFirmado)
            ]);

            return $xmlFirmado;

        } catch (\Exception $e) {
            \Log::error('=== ERROR EN FIRMA XML PEM ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Obtener ruta del certificado PEM del declarante
     *
     * @param  object  $declarante
     * @return string|null
     */
    private function obtenerRutaCertificadoPEM($declarante)
    {
        \Log::info('Obteniendo ruta del certificado PEM', [
            'declarante_id' => $declarante->id,
            'firma' => $declarante->firma
        ]);

        if (empty($declarante->firma)) {
            \Log::warning('Declarante no tiene archivo de firma configurado', [
                'declarante_id' => $declarante->id
            ]);
            return null;
        }

        $rutaBase = public_path('uploads/firmas/');
        
        // Si el campo firma es una ruta
        if (str_contains($declarante->firma, '/') || str_contains($declarante->firma, '\\')) {
            $rutaCompleta = $rutaBase . basename($declarante->firma);
        } else {
            // Si es un nombre de archivo
            $rutaCompleta = $rutaBase . $declarante->firma;
        }
        
        // Verificar que el archivo tenga extensión .pem
        $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
        if ($extension !== 'pem') {
            \Log::warning('El archivo de firma no tiene extensión .pem', [
                'archivo' => $rutaCompleta,
                'extension' => $extension
            ]);
            // Agregar extensión .pem si no la tiene
            $rutaCompleta .= '.pem';
        }
        
        \Log::info('Ruta del certificado PEM calculada', [
            'ruta_base' => $rutaBase,
            'ruta_completa' => $rutaCompleta,
            'directorio_existe' => is_dir($rutaBase),
            'archivo_existe' => file_exists($rutaCompleta)
        ]);
        
        // Crear directorio si no existe
        if (!is_dir($rutaBase)) {
            \Log::warning('Directorio de firmas no existe, creando...', ['ruta' => $rutaBase]);
            mkdir($rutaBase, 0755, true);
        }
        
        return $rutaCompleta;
    }

    /**
     * Aplicar firma XAdES-BES al XML usando certificado PEM
     * Implementación completa según estándares del SRI Ecuador
     *
     * @param  string  $xmlContent
     * @param  \OpenSSLCertificate|bool  $cert
     * @param  \OpenSSLAsymmetricKey|bool  $privateKey
     * @return string
     */
    private function aplicarFirmaXAdESPEM($xmlContent, $cert, $privateKey)
    {
        try {
            \Log::info('=== INICIANDO FIRMA XAdES-BES COMPLETA ===');
            
            // Validar parámetros de entrada
            if (!$cert) {
                throw new \Exception('Certificado no válido');
            }
            
            if (!$privateKey) {
                throw new \Exception('Clave privada no válida');
            }
            
            // Cargar el XML
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = false;
            
            if (!$dom->loadXML($xmlContent)) {
                throw new \Exception('Error al cargar XML para firmar');
            }
            
            // Obtener información del certificado
            $certInfo = openssl_x509_parse($cert);
            $certData = null;
            openssl_x509_export($cert, $certData);
            
            \Log::info('Información del certificado extraída', [
                'subject_CN' => $certInfo['subject']['CN'] ?? 'N/A',
                'subject_complete' => $certInfo['subject'] ?? [],
                'issuer_CN' => $certInfo['issuer']['CN'] ?? 'N/A',
                'issuer_complete' => $certInfo['issuer'] ?? [],
                'serial_number' => $certInfo['serialNumber'] ?? 'N/A',
                'serial_hex_length' => strlen($certInfo['serialNumber'] ?? ''),
                'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t'] ?? 0),
                'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t'] ?? 0),
                'cert_data_length' => strlen($certData),
                'extensions_available' => isset($certInfo['extensions']) ? array_keys($certInfo['extensions']) : []
            ]);
            
            // Generar IDs únicos para los elementos
            $signatureId = 'Signature' . mt_rand(100000, 999999);
            $signedInfoId = 'Signature-SignedInfo' . mt_rand(100000, 999999);
            $signedPropsId = $signatureId . '-SignedProperties' . mt_rand(100000, 999999);
            $certificateId = 'Certificate' . mt_rand(100000, 999999);
            $objectId = $signatureId . '-Object' . mt_rand(100000, 999999);
            $signatureValueId = 'SignatureValue' . mt_rand(100000, 999999);
            $referenceId = 'Reference-ID-' . mt_rand(100000, 999999);
            $signedPropsRefId = 'SignedPropertiesID' . mt_rand(100000, 999999);
            
            \Log::info('IDs generados para firma', [
                'signatureId' => $signatureId,
                'signedInfoId' => $signedInfoId,
                'signedPropsId' => $signedPropsId
            ]);
            
            // Crear elemento de firma XAdES-BES
            $signature = $dom->createElement('ds:Signature');
            $signature->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            $signature->setAttribute('xmlns:etsi', 'http://uri.etsi.org/01903/v1.3.2#');
            $signature->setAttribute('Id', $signatureId);
            
            // 1. SignedInfo
            $signedInfo = $dom->createElement('ds:SignedInfo');
            $signedInfo->setAttribute('Id', $signedInfoId);
            
            // CanonicalizationMethod
            $canonicalizationMethod = $dom->createElement('ds:CanonicalizationMethod');
            $canonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            $signedInfo->appendChild($canonicalizationMethod);
            
            // SignatureMethod
            $signatureMethod = $dom->createElement('ds:SignatureMethod');
            $signatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            $signedInfo->appendChild($signatureMethod);
            
            // Referencia a SignedProperties (requerida para XAdES)
            $signedPropsRef = $dom->createElement('ds:Reference');
            $signedPropsRef->setAttribute('Id', $signedPropsRefId);
            $signedPropsRef->setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
            $signedPropsRef->setAttribute('URI', '#' . $signedPropsId);
            
            $digestMethodSignedProps = $dom->createElement('ds:DigestMethod');
            $digestMethodSignedProps->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $signedPropsRef->appendChild($digestMethodSignedProps);
            
            // Placeholder para DigestValue de SignedProperties (se calculará después)
            $digestValueSignedProps = $dom->createElement('ds:DigestValue', 'SIGNED_PROPS_DIGEST_PLACEHOLDER');
            $signedPropsRef->appendChild($digestValueSignedProps);
            $signedInfo->appendChild($signedPropsRef);
            
            // Referencia al certificado
            $certificateRef = $dom->createElement('ds:Reference');
            $certificateRef->setAttribute('URI', '#' . $certificateId);
            
            $digestMethodCert = $dom->createElement('ds:DigestMethod');
            $digestMethodCert->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $certificateRef->appendChild($digestMethodCert);
            
            // Placeholder para DigestValue del certificado
            $digestValueCert = $dom->createElement('ds:DigestValue', 'CERTIFICATE_DIGEST_PLACEHOLDER');
            $certificateRef->appendChild($digestValueCert);
            $signedInfo->appendChild($certificateRef);
            
            // Referencia al documento principal
            $documentRef = $dom->createElement('ds:Reference');
            $documentRef->setAttribute('Id', $referenceId);
            $documentRef->setAttribute('URI', '#comprobante');
            
            $transforms = $dom->createElement('ds:Transforms');
            $transform = $dom->createElement('ds:Transform');
            $transform->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            $transforms->appendChild($transform);
            $documentRef->appendChild($transforms);
            
            $digestMethodDoc = $dom->createElement('ds:DigestMethod');
            $digestMethodDoc->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $documentRef->appendChild($digestMethodDoc);
            
            // Placeholder para DigestValue del documento
            $digestValueDoc = $dom->createElement('ds:DigestValue', 'DOCUMENT_DIGEST_PLACEHOLDER');
            $documentRef->appendChild($digestValueDoc);
            $signedInfo->appendChild($documentRef);
            
            $signature->appendChild($signedInfo);
            
            // 2. SignatureValue (placeholder)
            $signatureValue = $dom->createElement('ds:SignatureValue', 'SIGNATURE_VALUE_PLACEHOLDER');
            $signatureValue->setAttribute('Id', $signatureValueId);
            $signature->appendChild($signatureValue);
            
            // 3. KeyInfo
            $keyInfo = $dom->createElement('ds:KeyInfo');
            $keyInfo->setAttribute('Id', $certificateId);
            
            $x509Data = $dom->createElement('ds:X509Data');
            
            // Limpiar y formatear el certificado
            $certPEM = str_replace([
                '-----BEGIN CERTIFICATE-----',
                '-----END CERTIFICATE-----',
                "\r", "\n", " ", "\t"
            ], '', $certData);
            
            $x509Certificate = $dom->createElement('ds:X509Certificate', $certPEM);
            $x509Data->appendChild($x509Certificate);
            
            // Obtener la clave pública del certificado
            $publicKey = openssl_pkey_get_public($cert);
            $keyDetails = openssl_pkey_get_details($publicKey);
            
            if (isset($keyDetails['rsa'])) {
                $keyValue = $dom->createElement('ds:KeyValue');
                $rsaKeyValue = $dom->createElement('ds:RSAKeyValue');
                
                $modulus = $dom->createElement('ds:Modulus', base64_encode($keyDetails['rsa']['n']));
                $exponent = $dom->createElement('ds:Exponent', base64_encode($keyDetails['rsa']['e']));
                
                $rsaKeyValue->appendChild($modulus);
                $rsaKeyValue->appendChild($exponent);
                $keyValue->appendChild($rsaKeyValue);
                $keyInfo->appendChild($keyValue);
            }
            
            $keyInfo->appendChild($x509Data);
            $signature->appendChild($keyInfo);
            
            // 4. Object con QualifyingProperties (XAdES)
            $object = $dom->createElement('ds:Object');
            $object->setAttribute('Id', $objectId);
            
            $qualifyingProperties = $dom->createElement('etsi:QualifyingProperties');
            $qualifyingProperties->setAttribute('Target', '#' . $signatureId);
            
            $signedProperties = $dom->createElement('etsi:SignedProperties');
            $signedProperties->setAttribute('Id', $signedPropsId);
            
            $signedSignatureProperties = $dom->createElement('etsi:SignedSignatureProperties');
            
            // SigningTime con formato específico para Ecuador
            $now = new \DateTime('now', new \DateTimeZone('America/Guayaquil'));
            $signingTimeFormatted = $now->format('c'); // ISO 8601 con zona horaria -05:00
            $signingTime = $dom->createElement('etsi:SigningTime', $signingTimeFormatted);
            $signedSignatureProperties->appendChild($signingTime);
            
            // SigningCertificate
            $signingCertificate = $dom->createElement('etsi:SigningCertificate');
            $cert_element = $dom->createElement('etsi:Cert');
            
            $certDigest = $dom->createElement('etsi:CertDigest');
            $digestMethodCertXAdES = $dom->createElement('ds:DigestMethod');
            $digestMethodCertXAdES->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $certDigest->appendChild($digestMethodCertXAdES);
            
            // Calcular digest del certificado
            $certBinary = base64_decode($certPEM);
            $certDigestValue = base64_encode(sha1($certBinary, true));
            $digestValueCertXAdES = $dom->createElement('ds:DigestValue', $certDigestValue);
            $certDigest->appendChild($digestValueCertXAdES);
            $cert_element->appendChild($certDigest);
            
            // IssuerSerial
            $issuerSerial = $dom->createElement('etsi:IssuerSerial');
            $x509IssuerName = $this->formatearDN($certInfo['issuer']);
            
            // Convertir serial number de hex a decimal si es necesario
            $x509SerialNumber = $certInfo['serialNumber'];
            if (is_string($x509SerialNumber) && ctype_xdigit($x509SerialNumber)) {
                $x509SerialNumber = base_convert($x509SerialNumber, 16, 10);
            }
            
            $issuerName = $dom->createElement('ds:X509IssuerName', $x509IssuerName);
            $serialNumber = $dom->createElement('ds:X509SerialNumber', $x509SerialNumber);
            
            $issuerSerial->appendChild($issuerName);
            $issuerSerial->appendChild($serialNumber);
            $cert_element->appendChild($issuerSerial);
            
            $signingCertificate->appendChild($cert_element);
            $signedSignatureProperties->appendChild($signingCertificate);
            
            $signedProperties->appendChild($signedSignatureProperties);
            $qualifyingProperties->appendChild($signedProperties);
            $object->appendChild($qualifyingProperties);
            $signature->appendChild($object);
            
            // Agregar la firma al documento
            $root = $dom->documentElement;
            $root->appendChild($signature);
            
            \Log::info('Estructura XAdES creada, calculando digestos...');
            
            // Ahora calcular los digestos reales
            
            // 1. Digest del documento (sin la firma)
            $docCopy = clone $dom;
            $signatureNodes = $docCopy->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
            if ($signatureNodes->length > 0) {
                $signatureNodes->item(0)->parentNode->removeChild($signatureNodes->item(0));
            }
            $documentCanonical = $docCopy->C14N();
            $documentDigest = base64_encode(sha1($documentCanonical, true));
            
            // 2. Digest de SignedProperties
            $signedPropertiesCanonical = $signedProperties->C14N();
            $signedPropertiesDigest = base64_encode(sha1($signedPropertiesCanonical, true));
            
            // Actualizar los digestos en el XML
            $digestValueDoc->nodeValue = $documentDigest;
            $digestValueSignedProps->nodeValue = $signedPropertiesDigest;
            $digestValueCert->nodeValue = $certDigestValue;
            
            \Log::info('Digestos calculados', [
                'document_digest' => $documentDigest,
                'signed_props_digest' => $signedPropertiesDigest,
                'cert_digest' => $certDigestValue,
                'document_canonical_length' => strlen($documentCanonical),
                'signed_props_canonical_length' => strlen($signedPropertiesCanonical)
            ]);
            
            // 3. Firmar SignedInfo
            $signedInfoCanonical = $signedInfo->C14N();
            $signatureBinary = '';
            
            \Log::info('Firmando SignedInfo', [
                'signedInfo_canonical_length' => strlen($signedInfoCanonical),
                'signedInfo_preview' => substr($signedInfoCanonical, 0, 200) . '...'
            ]);
            
            if (!openssl_sign($signedInfoCanonical, $signatureBinary, $privateKey, OPENSSL_ALGO_SHA1)) {
                $opensslError = openssl_error_string();
                \Log::error('Error en openssl_sign', ['error' => $opensslError]);
                throw new \Exception('Error al generar la firma digital con la clave privada: ' . $opensslError);
            }
            
            $signatureValueBase64 = base64_encode($signatureBinary);
            $signatureValue->nodeValue = $signatureValueBase64;
            
            \Log::info('Firma digital generada exitosamente', [
                'signedInfo_length' => strlen($signedInfoCanonical),
                'signature_length' => strlen($signatureBinary),
                'signature_base64_length' => strlen($signatureValueBase64)
            ]);
            
            // Formatear el XML final
            $dom->formatOutput = false;
            $xmlFirmado = $dom->saveXML();
            
            \Log::info('=== FIRMA XAdES-BES COMPLETADA EXITOSAMENTE ===', [
                'xml_firmado_length' => strlen($xmlFirmado),
                'tiene_signature' => strpos($xmlFirmado, '<ds:Signature') !== false || strpos($xmlFirmado, '<ns0:Signature') !== false,
                'tiene_xades' => strpos($xmlFirmado, 'etsi:QualifyingProperties') !== false,
                'tiene_signing_time' => strpos($xmlFirmado, 'etsi:SigningTime') !== false
            ]);
            
            return $xmlFirmado;
            
        } catch (\Exception $e) {
            \Log::error('=== ERROR EN FIRMA XAdES-BES ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error al aplicar firma XAdES-BES: ' . $e->getMessage());
        }
    }
    
    /**
     * Validar que el RUC del certificado coincida con el RUC del declarante
     */
    private function validarRUCCertificado($certificado, $declarante)
    {
        try {
            \Log::info('=== VALIDANDO RUC DEL CERTIFICADO ===');
            
            // Obtener información del certificado
            $certInfo = openssl_x509_parse($certificado);
            
            // El RUC del declarante
            $rucDeclarante = $declarante->ruc;
            \Log::info('RUC del declarante', ['ruc' => $rucDeclarante]);
            
            // Extraer RUC del certificado (puede estar en diferentes campos)
            $rucCertificado = null;
            
            // 1. Buscar en el Subject
            if (isset($certInfo['subject']['CN'])) {
                $cn = $certInfo['subject']['CN'];
                if (preg_match('/(\d{13})/', $cn, $matches)) {
                    $rucCertificado = $matches[1];
                    \Log::info('RUC encontrado en CN', ['cn' => $cn, 'ruc' => $rucCertificado]);
                }
            }
            
            // 2. Buscar en extensiones del certificado
            if (!$rucCertificado && isset($certInfo['extensions'])) {
                foreach ($certInfo['extensions'] as $oid => $extension) {
                    if (preg_match('/(\d{13})/', $extension, $matches)) {
                        $rucCertificado = $matches[1];
                        \Log::info('RUC encontrado en extensión', ['oid' => $oid, 'ruc' => $rucCertificado]);
                        break;
                    }
                }
            }
            
            // 3. Buscar en Subject Alternative Name si existe
            if (!$rucCertificado && isset($certInfo['extensions']['subjectAltName'])) {
                $san = $certInfo['extensions']['subjectAltName'];
                if (preg_match('/(\d{13})/', $san, $matches)) {
                    $rucCertificado = $matches[1];
                    \Log::info('RUC encontrado en SAN', ['san' => $san, 'ruc' => $rucCertificado]);
                }
            }
            
            // Log de toda la información del certificado para debuggear
            \Log::info('Información completa del certificado', [
                'subject' => $certInfo['subject'] ?? 'N/A',
                'extensions_keys' => isset($certInfo['extensions']) ? array_keys($certInfo['extensions']) : [],
                'serial_number' => $certInfo['serialNumber'] ?? 'N/A'
            ]);
            
            // Validar concordancia
            if (!$rucCertificado) {
                \Log::warning('No se pudo extraer RUC del certificado digital');
                // No lanzamos excepción aquí, solo advertencia
                return;
            }
            
            // Limpiar ambos RUCs para comparación (remover caracteres no numéricos)
            $rucDeclaranteLimpio = preg_replace('/[^0-9]/', '', $rucDeclarante);
            $rucCertificadoLimpio = preg_replace('/[^0-9]/', '', $rucCertificado);
            
            \Log::info('Comparación de RUCs', [
                'declarante_original' => $rucDeclarante,
                'declarante_limpio' => $rucDeclaranteLimpio,
                'certificado_original' => $rucCertificado,
                'certificado_limpio' => $rucCertificadoLimpio,
                'coinciden' => $rucDeclaranteLimpio === $rucCertificadoLimpio
            ]);
            
            if ($rucDeclaranteLimpio !== $rucCertificadoLimpio) {
                \Log::error('RUC DEL CERTIFICADO NO COINCIDE CON EL DECLARANTE', [
                    'ruc_declarante' => $rucDeclaranteLimpio,
                    'ruc_certificado' => $rucCertificadoLimpio
                ]);
                
                throw new \Exception(
                    "El RUC del certificado digital ($rucCertificadoLimpio) no coincide con el RUC del declarante ($rucDeclaranteLimpio). " .
                    "Para firmar electrónicamente, debe usar un certificado digital emitido para el mismo RUC."
                );
            }
            
            \Log::info('✓ RUC del certificado coincide con el declarante', [
                'ruc' => $rucDeclaranteLimpio
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al validar RUC del certificado', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Formatear Distinguished Name según estándar X.500
     */
    private function formatearDN($dnArray)
    {
        $components = [];
        
        // Orden específico según el ejemplo del SRI Ecuador
        $order = ['CN', 'C', 'L', '2.5.4.97', 'O', 'OU'];
        
        foreach ($order as $component) {
            if (isset($dnArray[$component])) {
                if ($component === '2.5.4.97') {
                    // Formatear el OID 2.5.4.97 como en el ejemplo
                    $value = $dnArray[$component];
                    if (is_string($value)) {
                        $hexValue = strtoupper(bin2hex($value));
                        $components[] = '2.5.4.97=#0C' . sprintf('%02X', strlen($value)) . $hexValue;
                    }
                } else {
                    $components[] = $component . '=' . $dnArray[$component];
                }
            }
        }
        
        \Log::info('DN formateado', [
            'original' => $dnArray,
            'formatted' => implode(',', $components)
        ]);
        
        return implode(',', $components);
    }

    /**
     * Enviar XML firmado al SRI
     * 
     * IMPORTANTE: En el proceso de facturación electrónica del SRI hay dos pasos:
     * 1. ENVÍO (recepción): Se envía solo el XML firmado (sin encapsular)
     * 2. AUTORIZACIÓN: Se solicita autorización usando solo la clave de acceso
     * 
     * Esta función maneja el primer paso (envío/recepción)
     *
     * @param  string  $xmlFirmado
     * @return array
     */
    private function enviarAlSRI($xmlFirmado)
    {
        try {
            \Log::info('=== INICIO ENVÍO AL SRI ===', [
                'xml_length' => strlen($xmlFirmado)
            ]);

            // Convertir XML firmado a base64 (método estándar del SRI)
            $xmlBase64 = base64_encode($xmlFirmado);
            
            \Log::info('XML firmado preparado para envío', [
                'xml_length' => strlen($xmlFirmado),
                'xml_base64_length' => strlen($xmlBase64),
                'xml_preview' => substr($xmlFirmado, 0, 100) . '...'
            ]);

            // Crear SOAP envelope con base64 (método estándar)
            $soapEnvelope = $this->crearSoapEnvelopeBase64($xmlBase64);
            
            \Log::info('SOAP envelope creado', [
                'soap_length' => strlen($soapEnvelope),
                'soap_preview' => substr($soapEnvelope, 0, 500) . '...',
                'uses_base64' => strpos($soapEnvelope, 'base64') === false ? 'NO' : 'SI'
            ]);

            // Enviar con cURL (como en el código funcional)
            $respuesta = $this->enviarSoapRequestCurl($soapEnvelope);
            
            if ($respuesta === false) {
                \Log::error('Error al conectar con el servicio del SRI - Intentando método alternativo');
                
                // Intentar con CDATA como método alternativo
                $soapEnvelopeCDATA = $this->crearSoapEnvelope($xmlFirmado);
                $respuesta = $this->enviarSoapRequestCurl($soapEnvelopeCDATA);
                
                if ($respuesta === false) {
                    return [
                        'success' => false,
                        'message' => 'Error al conectar con el servicio del SRI (métodos Base64 y CDATA fallaron)',
                        'estado' => 'ERROR_CONEXION'
                    ];
                }
                \Log::info('Método alternativo CDATA funcionó');
            }

            \Log::info('Respuesta del SRI recibida', [
                'response_length' => strlen($respuesta),
                'response_preview' => substr($respuesta, 0, 1000) . '...'
            ]);

            // Procesar la respuesta XML manualmente (como en el código funcional)
            $respuestaProcesada = $this->procesarRespuestaSRI($respuesta);
            
            \Log::info('Respuesta procesada del SRI', [
                'estado' => $respuestaProcesada['estado'] ?? 'NO_DEFINIDO',
                'respuesta_completa' => $respuestaProcesada
            ]);

            if ($respuestaProcesada['estado'] === 'RECIBIDA') {
                \Log::info('=== COMPROBANTE RECIBIDO EXITOSAMENTE ===');
                return [
                    'success' => true,
                    'estado' => 'RECIBIDA',
                    'numero_autorizacion' => null,
                    'fecha_autorizacion' => null
                ];
            } elseif ($respuestaProcesada['estado'] === 'DEVUELTA') {
                // Extraer errores de los comprobantes
                $errores = [];
                $erroresDetallados = [];
                
                if (!empty($respuestaProcesada['comprobantes'])) {
                    foreach ($respuestaProcesada['comprobantes'] as $comprobante) {
                        if (!empty($comprobante['mensajes'])) {
                            foreach ($comprobante['mensajes'] as $mensaje) {
                                $errores[] = $mensaje['mensaje'] ?? 'Error sin descripción';
                                $erroresDetallados[] = $mensaje; // Incluir toda la información del mensaje
                            }
                        }
                    }
                }
                
                \Log::warning('Comprobante devuelto por el SRI', [
                    'estado' => $respuestaProcesada['estado'],
                    'errores' => $errores,
                    'errores_detallados' => $erroresDetallados
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Comprobante devuelto por el SRI',
                    'estado' => 'DEVUELTA',
                    'errors' => $errores,
                    'errores_detallados' => $erroresDetallados
                ];
            } else {
                \Log::error('Estado desconocido del SRI', [
                    'estado' => $respuestaProcesada['estado'],
                    'respuesta' => $respuestaProcesada
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Estado desconocido del SRI: ' . ($respuestaProcesada['estado'] ?? 'INDEFINIDO'),
                    'estado' => $respuestaProcesada['estado'] ?? 'DESCONOCIDO'
                ];
            }

        } catch (\Exception $e) {
            \Log::error('=== ERROR GENERAL AL ENVIAR AL SRI ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar respuesta del SRI: ' . $e->getMessage(),
                'estado' => 'ERROR_PROCESAMIENTO'
            ];
        }
    }

    /**
     * Crear SOAP envelope para enviar al SRI
     *
     * @param  string  $xmlContent
     * @return string
     */
    private function crearSoapEnvelope($xmlContent)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
               xmlns:rec="http://ec.gob.sri.ws.recepcion">
    <soap:Header />
    <soap:Body>
        <rec:validarComprobante>
            <xml><![CDATA[' . $xmlContent . ']]></xml>
        </rec:validarComprobante>
    </soap:Body>
</soap:Envelope>';
    }

    /**
     * Crear SOAP envelope para enviar al SRI con Base64 (método alternativo)
     *
     * @param  string  $xmlBase64
     * @return string
     */
    private function crearSoapEnvelopeBase64($xmlBase64)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
               xmlns:rec="http://ec.gob.sri.ws.recepcion">
    <soap:Header />
    <soap:Body>
        <rec:validarComprobante>
            <xml>' . $xmlBase64 . '</xml>
        </rec:validarComprobante>
    </soap:Body>
</soap:Envelope>';
    }

    /**
     * Enviar petición SOAP con cURL
     *
     * @param  string  $soapEnvelope
     * @return string|false
     */
    private function enviarSoapRequestCurl($soapEnvelope)
    {
        // SIEMPRE USAR PRUEBAS - No usar producción
        $urls = [
            'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline', // SOLO PRUEBAS
        ];
        
        foreach ($urls as $index => $url) {
            \Log::info('Intentando envío con URL DE PRUEBAS', [
                'url_index' => $index + 1,
                'url' => $url,
                'ambiente' => 'PRUEBAS'
            ]);
            
            $headers = [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: ""',
                'Content-Length: ' . strlen($soapEnvelope)
            ];
            
            \Log::info('Enviando petición cURL al SRI PRUEBAS', [
                'url' => $url,
                'headers' => $headers
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $soapEnvelope);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Sistema Facturacion Electronica OPTECU');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);
            
            \Log::info('Respuesta cURL del SRI PRUEBAS', [
                'url_index' => $index + 1,
                'http_code' => $httpCode,
                'curl_error' => $error,
                'response_length' => $response ? strlen($response) : 0,
                'response_preview' => $response ? substr($response, 0, 500) . '...' : 'N/A'
            ]);
            
            if ($error) {
                \Log::error('Error CURL con URL ' . ($index + 1) . ': ' . $error);
                continue; // Intentar con la siguiente URL
            }
            
            if ($httpCode === 200) {
                \Log::info('Éxito con URL DE PRUEBAS ' . ($index + 1));
                return $response;
            } else if ($httpCode === 500) {
                // Error 500 puede ser un problema temporal del SRI, intentar siguiente URL
                \Log::warning('Error 500 temporal del SRI con URL ' . ($index + 1) . ', intentando siguiente URL');
                continue;
            } else {
                \Log::error('HTTP Error con URL ' . ($index + 1) . ': ' . $httpCode, [
                    'response_body' => $response ? $response : 'Sin respuesta',
                    'response_length' => $response ? strlen($response) : 0
                ]);
                
                // Si es la última URL, continuar al return false
                if ($index === count($urls) - 1) {
                    break;
                }
            }
        }
        
        return false;
    }

    /**
     * Procesar respuesta XML del SRI con debugging mejorado
     *
     * @param  string  $xmlResponse
     * @param  Factura|null  $factura
     * @return array
     */
    private function procesarRespuestaSRI($xmlResponse, $factura = null)
    {
        try {
            \Log::info('=== INICIO PROCESAMIENTO RESPUESTA SRI ===', [
                'factura_id' => $factura ? $factura->id : 'N/A',
                'xml_response_length' => strlen($xmlResponse),
                'xml_response_preview' => substr($xmlResponse, 0, 1000) . '...'
            ]);
            
            // Limpiar posibles BOM y espacios
            $xmlResponse = trim($xmlResponse);
            if (substr($xmlResponse, 0, 3) === "\xEF\xBB\xBF") {
                $xmlResponse = substr($xmlResponse, 3);
            }
            
            // Cargar el XML
            $dom = new \DOMDocument();
            $loadResult = $dom->loadXML($xmlResponse);
            
            if (!$loadResult) {
                \Log::error('Error al cargar XML de respuesta del SRI');
                return [
                    'estado' => 'ERROR',
                    'comprobantes' => [],
                    'error' => 'Error al cargar XML de respuesta'
                ];
            }
            
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpath->registerNamespace('ns2', 'http://ec.gob.sri.ws.recepcion');
            
            // Extraer el estado
            $estadoNode = $xpath->query('//ns2:validarComprobanteResponse/RespuestaRecepcionComprobante/estado');
            $estado = $estadoNode->length > 0 ? $estadoNode->item(0)->textContent : '';
            
            \Log::info('Estado extraído del XML SRI', [
                'estado' => $estado,
                'factura_id' => $factura ? $factura->id : 'N/A'
            ]);
            
            $respuesta = [
                'estado' => $estado,
                'comprobantes' => []
            ];
            
            // Si hay errores, extraer los mensajes con MÁXIMO DETALLE
            $comprobantesNodes = $xpath->query('//ns2:validarComprobanteResponse/RespuestaRecepcionComprobante/comprobantes/comprobante');
            
            \Log::info('Número de comprobantes en respuesta SRI', [
                'num_comprobantes' => $comprobantesNodes->length,
                'factura_id' => $factura ? $factura->id : 'N/A'
            ]);
            
            foreach ($comprobantesNodes as $index => $comprobanteNode) {
                $comprobante = [];
                
                $claveAccesoNode = $xpath->query('.//claveAcceso', $comprobanteNode);
                if ($claveAccesoNode->length > 0) {
                    $comprobante['claveAcceso'] = $claveAccesoNode->item(0)->textContent;
                }
                
                $mensajesNodes = $xpath->query('.//mensajes/mensaje', $comprobanteNode);
                $comprobante['mensajes'] = [];
                
                \Log::info('Procesando comprobante', [
                    'comprobante_index' => $index,
                    'clave_acceso' => $comprobante['claveAcceso'] ?? 'N/A',
                    'num_mensajes' => $mensajesNodes->length,
                    'factura_id' => $factura ? $factura->id : 'N/A'
                ]);
                
                foreach ($mensajesNodes as $mensajeIndex => $mensajeNode) {
                    $mensaje = [];
                    
                    $identificadorNode = $xpath->query('.//identificador', $mensajeNode);
                    if ($identificadorNode->length > 0) {
                        $mensaje['identificador'] = $identificadorNode->item(0)->textContent;
                    }
                    
                    $mensajeTextNode = $xpath->query('.//mensaje', $mensajeNode);
                    if ($mensajeTextNode->length > 0) {
                        $mensaje['mensaje'] = $mensajeTextNode->item(0)->textContent;
                    }
                    
                    $infoAdicionalNode = $xpath->query('.//informacionAdicional', $mensajeNode);
                    if ($infoAdicionalNode->length > 0) {
                        $mensaje['informacionAdicional'] = $infoAdicionalNode->item(0)->textContent;
                    }
                    
                    $tipoNode = $xpath->query('.//tipo', $mensajeNode);
                    if ($tipoNode->length > 0) {
                        $mensaje['tipo'] = $tipoNode->item(0)->textContent;
                    }
                    
                    // Log detallado de cada mensaje individual
                    \Log::info('MENSAJE SRI DETALLADO', [
                        'factura_id' => $factura ? $factura->id : 'N/A',
                        'comprobante_index' => $index,
                        'mensaje_index' => $mensajeIndex,
                        'identificador' => $mensaje['identificador'] ?? 'SIN_IDENTIFICADOR',
                        'codigo_error' => $mensaje['identificador'] ?? 'SIN_CODIGO',
                        'mensaje_texto' => $mensaje['mensaje'] ?? 'SIN_MENSAJE',
                        'info_adicional' => $mensaje['informacionAdicional'] ?? 'SIN_INFO_ADICIONAL',
                        'tipo_mensaje' => $mensaje['tipo'] ?? 'SIN_TIPO',
                        'mensaje_completo' => $mensaje
                    ]);
                    
                    // Análisis específico para errores de firma
                    if (isset($mensaje['identificador'])) {
                        $codigo = $mensaje['identificador'];
                        $textoMensaje = $mensaje['mensaje'] ?? '';
                        $infoAdicional = $mensaje['informacionAdicional'] ?? '';
                        
                        // Mapear códigos de error conocidos del SRI
                        $analisisError = $this->analizarCodigoErrorSRI($codigo, $textoMensaje, $infoAdicional);
                        
                        \Log::warning('ANÁLISIS DETALLADO ERROR SRI', [
                            'factura_id' => $factura ? $factura->id : 'N/A',
                            'codigo_error' => $codigo,
                            'descripcion_error' => $textoMensaje,
                            'info_adicional' => $infoAdicional,
                            'categoria_error' => $analisisError['categoria'],
                            'causa_probable' => $analisisError['causa_probable'],
                            'solucion_sugerida' => $analisisError['solucion_sugerida'],
                            'es_error_critico' => $analisisError['es_critico']
                        ]);
                        
                        // Agregar análisis al mensaje
                        $mensaje['analisis'] = $analisisError;
                    }
                    
                    $comprobante['mensajes'][] = $mensaje;
                }
                
                $respuesta['comprobantes'][] = $comprobante;
            }
            
            \Log::info('=== FIN PROCESAMIENTO RESPUESTA SRI ===', [
                'factura_id' => $factura ? $factura->id : 'N/A',
                'estado_final' => $respuesta['estado'],
                'total_comprobantes' => count($respuesta['comprobantes']),
                'total_mensajes' => array_sum(array_map(function($comp) { 
                    return count($comp['mensajes'] ?? []); 
                }, $respuesta['comprobantes']))
            ]);
            
            return $respuesta;
            
        } catch (\Exception $e) {
            \Log::error('=== ERROR PROCESANDO RESPUESTA SRI ===', [
                'factura_id' => $factura ? $factura->id : 'N/A',
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'xml_response_debug' => substr($xmlResponse, 0, 2000)
            ]);
            
            return [
                'estado' => 'ERROR',
                'comprobantes' => [],
                'error' => 'Error procesando respuesta: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Analizar código de error del SRI para proporcionar información útil
     */
    private function analizarCodigoErrorSRI($codigo, $mensaje, $infoAdicional)
    {
        $analisis = [
            'categoria' => 'DESCONOCIDO',
            'causa_probable' => 'Error no catalogado',
            'solucion_sugerida' => 'Revisar documentación del SRI',
            'es_critico' => true
        ];
        
        // Mapeo de códigos de error conocidos del SRI Ecuador
        switch ($codigo) {
            case '39':
                $analisis = [
                    'categoria' => 'FIRMA_DIGITAL',
                    'causa_probable' => 'Firma digital inválida o incorrecta',
                    'solucion_sugerida' => 'Verificar certificado, algoritmos de firma y estructura XAdES-BES',
                    'es_critico' => true
                ];
                
                // Análisis más específico basado en el mensaje
                if (strpos(strtolower($mensaje), 'certificado') !== false) {
                    $analisis['causa_probable'] = 'Problema con el certificado digital';
                    $analisis['solucion_sugerida'] = 'Verificar validez y fecha del certificado digital';
                } elseif (strpos(strtolower($mensaje), 'algoritmo') !== false) {
                    $analisis['causa_probable'] = 'Algoritmo de firma no compatible';
                    $analisis['solucion_sugerida'] = 'Usar RSA-SHA1 para SignatureMethod y SHA1 para DigestMethod';
                } elseif (strpos(strtolower($mensaje), 'estructura') !== false) {
                    $analisis['causa_probable'] = 'Estructura XAdES-BES incorrecta';
                    $analisis['solucion_sugerida'] = 'Verificar orden de elementos y namespaces XAdES';
                } elseif (strpos(strtolower($mensaje), 'ruc') !== false) {
                    $analisis['causa_probable'] = 'RUC del certificado no coincide con el declarante';
                    $analisis['solucion_sugerida'] = 'Usar certificado digital emitido para el mismo RUC';
                } elseif (strpos(strtolower($infoAdicional), 'timestamp') !== false || 
                         strpos(strtolower($infoAdicional), 'fecha') !== false) {
                    $analisis['causa_probable'] = 'Problema con SigningTime o fechas en XAdES';
                    $analisis['solucion_sugerida'] = 'Verificar formato de fechas ISO 8601 con zona horaria';
                }
                break;
                
            case '70':
                $analisis = [
                    'categoria' => 'ESTRUCTURA_XML',
                    'causa_probable' => 'Estructura del XML no conforme',
                    'solucion_sugerida' => 'Verificar esquema XSD del comprobante',
                    'es_critico' => true
                ];
                break;
                
            case '35':
                $analisis = [
                    'categoria' => 'CERTIFICADO',
                    'causa_probable' => 'Certificado digital no válido o expirado',
                    'solucion_sugerida' => 'Renovar o verificar el certificado digital',
                    'es_critico' => true
                ];
                break;
                
            case '60':
                $analisis = [
                    'categoria' => 'CLAVE_ACCESO',
                    'causa_probable' => 'Clave de acceso incorrecta o duplicada',
                    'solucion_sugerida' => 'Verificar algoritmo de generación de clave de acceso',
                    'es_critico' => true
                ];
                break;
                
            default:
                // Para códigos desconocidos, intentar análisis por palabras clave
                $mensajeCompleto = strtolower($mensaje . ' ' . $infoAdicional);
                
                if (strpos($mensajeCompleto, 'firma') !== false) {
                    $analisis['categoria'] = 'FIRMA_DIGITAL';
                    $analisis['causa_probable'] = 'Problema relacionado con firma digital';
                } elseif (strpos($mensajeCompleto, 'certificado') !== false) {
                    $analisis['categoria'] = 'CERTIFICADO';
                    $analisis['causa_probable'] = 'Problema con certificado digital';
                } elseif (strpos($mensajeCompleto, 'xml') !== false) {
                    $analisis['categoria'] = 'ESTRUCTURA_XML';
                    $analisis['causa_probable'] = 'Problema con estructura XML';
                } elseif (strpos($mensajeCompleto, 'clave') !== false) {
                    $analisis['categoria'] = 'CLAVE_ACCESO';
                    $analisis['causa_probable'] = 'Problema con clave de acceso';
                }
                break;
        }
        
        return $analisis;
    }
    
    /**
     * Extraer una sección específica del XML para logging
     *
     * @param  string  $xml
     * @param  string  $elemento
     * @return string
     */
    private function extraerSeccionXML($xml, $elemento)
    {
        try {
            $inicio = strpos($xml, '<' . $elemento . '>');
            $fin = strpos($xml, '</' . $elemento . '>');
            
            if ($inicio !== false && $fin !== false) {
                $longitud = $fin - $inicio + strlen('</' . $elemento . '>');
                return substr($xml, $inicio, $longitud);
            }
            
            return 'Elemento <' . $elemento . '> no encontrado';
        } catch (\Exception $e) {
            return 'Error extrayendo elemento: ' . $e->getMessage();
        }
    }
    
    /**
     * Autorizar comprobante en el SRI
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autorizarComprobante($id, Request $request)
    {
        try {
            // Log inicial
            \Log::info('=== INICIO PROCESO AUTORIZACIÓN ===', [
                'factura_id' => $id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Obtener la factura
            \Log::info('Buscando factura para autorización', ['factura_id' => $id]);
            $factura = Factura::with('declarante')->findOrFail($id);
            
            \Log::info('Factura encontrada para autorización', [
                'factura_id' => $factura->id,
                'estado_actual' => $factura->estado ?? 'SIN_ESTADO',
                'estado_sri' => $factura->estado_sri ?? 'SIN_ESTADO_SRI'
            ]);

            // Verificar que la factura esté en estado RECIBIDA
            if ($factura->estado !== 'RECIBIDA') {
                \Log::error('Factura no está en estado RECIBIDA', [
                    'factura_id' => $id,
                    'estado_actual' => $factura->estado
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'La factura debe estar en estado RECIBIDA para poder autorizarla. Estado actual: ' . ($factura->estado ?? 'DESCONOCIDO')
                ], 422);
            }

            // Verificar que no haya sido autorizada ya (solo verificar por estado)
            if ($factura->estado === 'AUTORIZADA') {
                \Log::warning('Factura ya autorizada', [
                    'factura_id' => $id,
                    'estado' => $factura->estado,
                    'numero_autorizacion' => $factura->numero_autorizacion
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Esta factura ya fue autorizada'
                ], 422);
            }

            // Verificar que tenga clave de acceso
            $claveAcceso = $this->extraerClaveAccesoDeXML($factura);
            if (empty($claveAcceso)) {
                \Log::error('Factura sin clave de acceso', ['factura_id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene clave de acceso válida'
                ], 422);
            }

            \Log::info('Iniciando solicitud de autorización al SRI', [
                'factura_id' => $id,
                'clave_acceso' => $claveAcceso
            ]);

            // Enviar solicitud de autorización al SRI
            $resultadoSRI = $this->solicitarAutorizacionSRI($claveAcceso);
            
            \Log::info('Resultado de autorización del SRI', [
                'factura_id' => $id,
                'resultado' => $resultadoSRI
            ]);

            if (!$resultadoSRI['success']) {
                // Error al autorizar en SRI
                $factura->mensajes_sri = json_encode($resultadoSRI['errores_detallados'] ?? [$resultadoSRI['message']]);
                $factura->save();

                \Log::error('Error al autorizar en SRI', [
                    'factura_id' => $id,
                    'error_message' => $resultadoSRI['message'],
                    'errors' => $resultadoSRI['errors'] ?? []
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error del SRI: ' . $resultadoSRI['message'],
                    'errors' => $resultadoSRI['errors'] ?? []
                ], 422);
            }

            // Actualizar estado según respuesta del SRI
            $estadoSRI = $resultadoSRI['estado'] ?? 'DESCONOCIDO';
            
            if ($estadoSRI === 'AUTORIZADO') {
                $factura->estado = 'AUTORIZADA';
                $factura->estado_sri = 'AUTORIZADA';
                $factura->numero_autorizacion = $resultadoSRI['numero_autorizacion'] ?? null;
                $factura->fecha_autorizacion = isset($resultadoSRI['fecha_autorizacion']) && $resultadoSRI['fecha_autorizacion'] ? 
                    \Carbon\Carbon::parse($resultadoSRI['fecha_autorizacion']) : now();
                    
            } elseif (in_array($estadoSRI, ['RECHAZADO', 'NO_AUTORIZADO'])) {
                $factura->estado = 'NO_AUTORIZADA';
                $factura->estado_sri = 'NO_AUTORIZADA';
                $factura->mensajes_sri = json_encode($resultadoSRI['errores_detallados'] ?? ['No autorizada por el SRI']);
                
            } else {
                $factura->estado = 'ERROR_AUTORIZACION';
                $factura->estado_sri = $estadoSRI;
                $factura->mensajes_sri = json_encode($resultadoSRI['errores_detallados'] ?? ['Estado desconocido: ' . $estadoSRI]);
            }
            
            $factura->save();

            \Log::info('Factura actualizada después de autorización', [
                'factura_id' => $id,
                'estado' => $factura->estado,
                'estado_sri' => $factura->estado_sri,
                'numero_autorizacion' => $factura->numero_autorizacion
            ]);

            \Log::info('=== FIN PROCESO AUTORIZACIÓN EXITOSO ===', ['factura_id' => $id]);

            // Determinar si es realmente exitoso o no
            if ($estadoSRI === 'AUTORIZADO') {
                $responseData = [
                    'estado' => $factura->estado,
                    'estado_sri' => $factura->estado_sri,
                    'numero_autorizacion' => $factura->numero_autorizacion,
                ];
                
                // Solo incluir fecha_autorizacion si existe y es válida
                if ($factura->fecha_autorizacion) {
                    $responseData['fecha_autorizacion'] = $factura->fecha_autorizacion->format('Y-m-d H:i:s');
                }
                
                return response()->json([
                    'success' => true,
                    'authorized' => true,
                    'message' => 'Factura autorizada exitosamente por el SRI',
                    'data' => $responseData
                ]);
            } else {
                // No autorizada - proceso completado pero factura rechazada
                return response()->json([
                    'success' => true,
                    'authorized' => false,
                    'message' => 'La factura NO fue autorizada por el SRI',
                    'data' => [
                        'estado' => $factura->estado,
                        'estado_sri' => $factura->estado_sri,
                        'motivo_rechazo' => $resultadoSRI['errores_detallados'] ?? 'Rechazada por el SRI'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('=== ERROR CRÍTICO EN AUTORIZACIÓN ===', [
                'factura_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Extraer clave de acceso del XML de la factura
     *
     * @param  \App\Models\Factura  $factura
     * @return string|null
     */
    private function extraerClaveAccesoDeXML($factura)
    {
        try {
            if (!$factura->xml) {
                return null;
            }
            
            $xmlPath = storage_path('app/public/' . $factura->xml);
            if (!file_exists($xmlPath)) {
                return null;
            }
            
            $xmlContent = file_get_contents($xmlPath);
            if (!$xmlContent) {
                return null;
            }
            
            // Buscar clave de acceso en el XML
            $dom = new \DOMDocument();
            if (!$dom->loadXML($xmlContent)) {
                return null;
            }
            
            $xpath = new \DOMXPath($dom);
            $claveAccesoNode = $xpath->query('//claveAcceso');
            
            if ($claveAccesoNode->length > 0) {
                return $claveAccesoNode->item(0)->textContent;
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Error extrayendo clave de acceso del XML', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Solicitar autorización al SRI
     *
     * @param  string  $claveAcceso
     * @return array
     */
    private function solicitarAutorizacionSRI($claveAcceso)
    {
        try {
            \Log::info('=== INICIO SOLICITUD AUTORIZACIÓN SRI ===', [
                'clave_acceso' => $claveAcceso
            ]);

            // Crear SOAP envelope para autorización
            $soapEnvelope = $this->crearSoapEnvelopeAutorizacion($claveAcceso);
            
            \Log::info('SOAP envelope de autorización creado', [
                'soap_length' => strlen($soapEnvelope)
            ]);

            // Enviar con cURL
            $respuesta = $this->enviarSoapRequestAutorizacion($soapEnvelope);
            
            if ($respuesta === false) {
                \Log::error('Error al conectar con el servicio de autorización del SRI');
                return [
                    'success' => false,
                    'message' => 'Error al conectar con el servicio de autorización del SRI',
                    'estado' => 'ERROR_CONEXION'
                ];
            }

            \Log::info('Respuesta de autorización del SRI recibida', [
                'response_length' => strlen($respuesta)
            ]);

            // Procesar la respuesta XML
            $respuestaProcesada = $this->procesarRespuestaAutorizacionSRI($respuesta);
            
            \Log::info('Respuesta de autorización procesada', [
                'estado' => $respuestaProcesada['estado'] ?? 'NO_DEFINIDO',
                'respuesta_completa' => $respuestaProcesada
            ]);

            if ($respuestaProcesada['estado'] === 'AUTORIZADO') {
                \Log::info('=== COMPROBANTE AUTORIZADO EXITOSAMENTE ===');
                return [
                    'success' => true,
                    'estado' => 'AUTORIZADO',
                    'numero_autorizacion' => $respuestaProcesada['numeroAutorizacion'] ?? null,
                    'fecha_autorizacion' => $respuestaProcesada['fechaAutorizacion'] ?? null
                ];
            } else {
                // Estado RECHAZADO, NO_AUTORIZADO u otro
                $errores = [];
                $erroresDetallados = [];
                
                if (!empty($respuestaProcesada['mensajes'])) {
                    foreach ($respuestaProcesada['mensajes'] as $mensaje) {
                        $errores[] = $mensaje['mensaje'] ?? 'Error sin descripción';
                        $erroresDetallados[] = $mensaje;
                    }
                }
                
                \Log::warning('Comprobante no autorizado por el SRI', [
                    'estado' => $respuestaProcesada['estado'],
                    'errores' => $errores
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Comprobante no autorizado por el SRI',
                    'estado' => $respuestaProcesada['estado'],
                    'errors' => $errores,
                    'errores_detallados' => $erroresDetallados
                ];
            }

        } catch (\Exception $e) {
            \Log::error('=== ERROR GENERAL EN AUTORIZACIÓN SRI ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar autorización del SRI: ' . $e->getMessage(),
                'estado' => 'ERROR_PROCESAMIENTO'
            ];
        }
    }
    
    /**
     * Crear SOAP envelope para autorización al SRI
     *
     * @param  string  $claveAcceso
     * @return string
     */
    private function crearSoapEnvelopeAutorizacion($claveAcceso)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
               xmlns:aut="http://ec.gob.sri.ws.autorizacion">
    <soap:Header />
    <soap:Body>
        <aut:autorizacionComprobante>
            <claveAccesoComprobante>' . $claveAcceso . '</claveAccesoComprobante>
        </aut:autorizacionComprobante>
    </soap:Body>
</soap:Envelope>';
    }
    
    /**
     * Enviar petición SOAP de autorización con cURL
     *
     * @param  string  $soapEnvelope
     * @return string|false
     */
    private function enviarSoapRequestAutorizacion($soapEnvelope)
    {
        $url = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline';
        
        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: ""',
            'Content-Length: ' . strlen($soapEnvelope)
        ];
        
        \Log::info('Enviando petición cURL de autorización al SRI', [
            'url' => $url,
            'headers' => $headers
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soapEnvelope);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Sistema Facturacion Electronica OPTECU');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        \Log::info('Respuesta cURL de autorización del SRI', [
            'http_code' => $httpCode,
            'curl_error' => $error,
            'response_length' => $response ? strlen($response) : 0
        ]);
        
        if ($error) {
            \Log::error('Error CURL Autorización: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            \Log::error('HTTP Error Autorización: ' . $httpCode);
            return false;
        }
        
        return $response;
    }
    
    /**
     * Procesar respuesta XML de autorización del SRI
     *
     * @param  string  $xmlResponse
     * @return array
     */
    private function procesarRespuestaAutorizacionSRI($xmlResponse)
    {
        try {
            \Log::info('Procesando respuesta XML de autorización del SRI');
            
            // Limpiar posibles BOM y espacios
            $xmlResponse = trim($xmlResponse);
            if (substr($xmlResponse, 0, 3) === "\xEF\xBB\xBF") {
                $xmlResponse = substr($xmlResponse, 3);
            }
            
            // Cargar el XML
            $dom = new \DOMDocument();
            if (!$dom->loadXML($xmlResponse)) {
                \Log::error('Error al cargar XML de respuesta de autorización del SRI');
                return [
                    'estado' => 'ERROR',
                    'claveAcceso' => '',
                    'numeroAutorizacion' => '',
                    'fechaAutorizacion' => '',
                    'ambiente' => '',
                    'mensajes' => [],
                    'error' => 'Error al cargar XML de respuesta'
                ];
            }
            
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpath->registerNamespace('ns2', 'http://ec.gob.sri.ws.autorizacion');
            
            // Extraer la clave de acceso consultada
            $claveAccesoNode = $xpath->query('//ns2:autorizacionComprobanteResponse/RespuestaAutorizacionComprobante/claveAccesoConsultada');
            $claveAcceso = $claveAccesoNode->length > 0 ? $claveAccesoNode->item(0)->textContent : '';
            
            $respuesta = [
                'claveAcceso' => $claveAcceso,
                'numeroComprobantes' => '',
                'estado' => '',
                'numeroAutorizacion' => '',
                'fechaAutorizacion' => '',
                'ambiente' => '',
                'mensajes' => []
            ];
            
            // Extraer número de comprobantes
            $numeroComprobantesNode = $xpath->query('//ns2:autorizacionComprobanteResponse/RespuestaAutorizacionComprobante/numeroComprobantes');
            if ($numeroComprobantesNode->length > 0) {
                $respuesta['numeroComprobantes'] = $numeroComprobantesNode->item(0)->textContent;
            }
            
            // Extraer información de la autorización
            $autorizacionNodes = $xpath->query('//ns2:autorizacionComprobanteResponse/RespuestaAutorizacionComprobante/autorizaciones/autorizacion');
            
            if ($autorizacionNodes->length > 0) {
                $autorizacionNode = $autorizacionNodes->item(0);
                
                // Estado
                $estadoNode = $xpath->query('.//estado', $autorizacionNode);
                if ($estadoNode->length > 0) {
                    $respuesta['estado'] = $estadoNode->item(0)->textContent;
                }
                
                // Número de autorización
                $numeroAutorizacionNode = $xpath->query('.//numeroAutorizacion', $autorizacionNode);
                if ($numeroAutorizacionNode->length > 0) {
                    $respuesta['numeroAutorizacion'] = $numeroAutorizacionNode->item(0)->textContent;
                }
                
                // Fecha de autorización
                $fechaAutorizacionNode = $xpath->query('.//fechaAutorizacion', $autorizacionNode);
                if ($fechaAutorizacionNode->length > 0) {
                    $respuesta['fechaAutorizacion'] = $fechaAutorizacionNode->item(0)->textContent;
                }
                
                // Ambiente
                $ambienteNode = $xpath->query('.//ambiente', $autorizacionNode);
                if ($ambienteNode->length > 0) {
                    $respuesta['ambiente'] = $ambienteNode->item(0)->textContent;
                }
                
                // Mensajes
                $mensajesNodes = $xpath->query('.//mensajes/mensaje', $autorizacionNode);
                foreach ($mensajesNodes as $mensajeNode) {
                    $mensaje = [];
                    
                    $identificadorNode = $xpath->query('.//identificador', $mensajeNode);
                    if ($identificadorNode->length > 0) {
                        $mensaje['identificador'] = $identificadorNode->item(0)->textContent;
                    }
                    
                    $mensajeTextNode = $xpath->query('.//mensaje', $mensajeNode);
                    if ($mensajeTextNode->length > 0) {
                        $mensaje['mensaje'] = $mensajeTextNode->item(0)->textContent;
                    }
                    
                    $tipoNode = $xpath->query('.//tipo', $mensajeNode);
                    if ($tipoNode->length > 0) {
                        $mensaje['tipo'] = $tipoNode->item(0)->textContent;
                    }
                    
                    $respuesta['mensajes'][] = $mensaje;
                }
            }
            
            \Log::info('Respuesta XML de autorización procesada exitosamente', [
                'estado' => $respuesta['estado'],
                'numero_autorizacion' => $respuesta['numeroAutorizacion'],
                'num_mensajes' => count($respuesta['mensajes'])
            ]);
            
            return $respuesta;
            
        } catch (\Exception $e) {
            \Log::error('Error procesando respuesta autorización SRI: ' . $e->getMessage());
            return [
                'estado' => 'ERROR',
                'claveAcceso' => '',
                'numeroAutorizacion' => '',
                'fechaAutorizacion' => '',
                'ambiente' => '',
                'mensajes' => [],
                'error' => 'Error procesando respuesta: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar email al cliente con la factura autorizada
     *
     * @param  \App\Models\Factura  $factura
     * @return void
     */
    private function enviarEmailFacturaAutorizada($factura)
    {
        try {
            \Log::info('=== INICIO ENVÍO EMAIL FACTURA AUTORIZADA ===', [
                'factura_id' => $factura->id
            ]);

            // Obtener email del cliente desde el pedido relacionado
            $emailCliente = null;
            if ($factura->pedido_id) {
                $pedido = \App\Models\Pedido::find($factura->pedido_id);
                if ($pedido && $pedido->correo_electronico) {
                    $emailCliente = $pedido->correo_electronico;
                }
            }
            
            if (!$emailCliente) {
                \Log::warning('No se encontró email del cliente para enviar factura', [
                    'factura_id' => $factura->id,
                    'pedido_id' => $factura->pedido_id
                ]);
                return;
            }

            // Obtener la ruta del XML firmado
            $xmlPath = null;
            if ($factura->xml_firmado) {
                $xmlPath = storage_path('app/public/' . $factura->xml_firmado);
            } elseif ($factura->xml) {
                $xmlPath = storage_path('app/public/' . $factura->xml);
            }
            
            if (!$xmlPath || !file_exists($xmlPath)) {
                \Log::error('No se encontró archivo XML para enviar', [
                    'factura_id' => $factura->id,
                    'xml_firmado' => $factura->xml_firmado,
                    'xml' => $factura->xml,
                    'xml_path' => $xmlPath
                ]);
                return;
            }

            \Log::info('Enviando email de factura autorizada', [
                'factura_id' => $factura->id,
                'email_cliente' => $emailCliente,
                'xml_path' => $xmlPath
            ]);

            // Enviar el email
            \Mail::to($emailCliente)->send(new \App\Mail\FacturaAutorizada($factura, $xmlPath));
            
            \Log::info('Email de factura autorizada enviado exitosamente', [
                'factura_id' => $factura->id,
                'email_cliente' => $emailCliente
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al enviar email de factura autorizada', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Extraer clave de acceso del contenido XML
     *
     * @param  string  $xmlContent
     * @return string|null
     */
    private function extraerClaveAccesoDelXMLContent($xmlContent)
    {
        try {
            $dom = new \DOMDocument();
            if (!$dom->loadXML($xmlContent)) {
                return null;
            }

            $xpath = new \DOMXPath($dom);
            $claveAccesoNode = $xpath->query('//claveAcceso');
            
            if ($claveAccesoNode->length > 0) {
                return $claveAccesoNode->item(0)->textContent;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error extrayendo clave de acceso del XML: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Encapsular XML firmado en estructura de autorización
     * 
     * NOTA: Esta función se creó para casos especiales donde se necesite
     * encapsular el XML en una estructura de autorización. En el flujo
     * normal del SRI, esto NO se usa para el envío inicial.
     *
     * @param  string  $xmlFirmado
     * @param  string  $claveAcceso
     * @return string
     */
    private function encapsularXMLEnAutorizacion($xmlFirmado, $claveAcceso)
    {
        try {
            // Crear la estructura de autorización
            $fechaActual = date('Y-m-d H:i:s');
            
            // Determinar el ambiente (se puede hacer configurable en config/app.php)
            $ambiente = config('app.sri_ambiente', 'PRUEBAS'); // Por defecto PRUEBAS
            
            $xmlAutorizacion = '<?xml version="1.0" encoding="UTF-8"?>
<autorizacion>
    <estado>AUTORIZADO</estado>
    <numeroAutorizacion>' . htmlspecialchars($claveAcceso) . '</numeroAutorizacion>
    <fechaAutorizacion>' . $fechaActual . '</fechaAutorizacion>
    <ambiente>' . $ambiente . '</ambiente>
    <comprobante><![CDATA[' . $xmlFirmado . ']]></comprobante>
    <mensajes/>
</autorizacion>';

            \Log::info('XML encapsulado en estructura de autorización creado', [
                'clave_acceso' => $claveAcceso,
                'fecha_autorizacion' => $fechaActual,
                'ambiente' => $ambiente,
                'xml_autorizacion_length' => strlen($xmlAutorizacion)
            ]);

            return $xmlAutorizacion;
        } catch (\Exception $e) {
            \Log::error('Error encapsulando XML en autorización: ' . $e->getMessage());
            throw new \Exception('Error al encapsular XML en estructura de autorización: ' . $e->getMessage());
        }
    }

    /**
     * Obtener XML de la factura para firma en JavaScript
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerXML($id)
    {
        try {
            $factura = Factura::findOrFail($id);
            
            if (!$factura->xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene un XML generado'
                ], 422);
            }
            
            $xmlPath = storage_path('app/public/' . $factura->xml);
            
            if (!file_exists($xmlPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el archivo XML de la factura'
                ], 422);
            }
            
            $xmlContent = file_get_contents($xmlPath);
            
            if (!$xmlContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer el contenido del archivo XML'
                ], 422);
            }
            
            \Log::info('XML obtenido para firma JavaScript', [
                'factura_id' => $id,
                'xml_length' => strlen($xmlContent)
            ]);
            
            return response()->json([
                'success' => true,
                'xml_content' => $xmlContent
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al obtener XML para firma JavaScript', [
                'factura_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener XML: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir XML firmado desde JavaScript y enviarlo al SRI
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recibirXMLFirmado($id, Request $request)
    {
        try {
            \Log::info('=== RECIBIENDO XML FIRMADO DESDE JAVASCRIPT ===', [
                'factura_id' => $id
            ]);
            
            // Validar entrada
            $validator = Validator::make($request->all(), [
                'xml_firmado' => 'required|string|min:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'XML firmado requerido',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $factura = Factura::findOrFail($id);
            $xmlFirmado = $request->xml_firmado;
            
            \Log::info('XML firmado recibido desde JavaScript', [
                'factura_id' => $id,
                'xml_length' => strlen($xmlFirmado),
                'xml_preview' => substr($xmlFirmado, 0, 200) . '...'
            ]);

            // Guardar XML firmado
            $xmlFirmadoPath = str_replace('.xml', '_firmado_js.xml', storage_path('app/public/' . $factura->xml));
            if (!file_put_contents($xmlFirmadoPath, $xmlFirmado)) {
                \Log::error('Error al guardar XML firmado desde JavaScript', [
                    'factura_id' => $id,
                    'xml_firmado_path' => $xmlFirmadoPath
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo guardar el XML firmado'
                ], 500);
            }

            // Actualizar estado de la factura
            $factura->estado = 'FIRMADA';
            $factura->xml_firmado = str_replace(storage_path('app/public/'), '', $xmlFirmadoPath);
            $factura->fecha_firma = now();
            $factura->save();

            \Log::info('Factura marcada como FIRMADA (JavaScript)', [
                'factura_id' => $id,
                'xml_firmado_path' => $factura->xml_firmado
            ]);

            // Enviar al SRI
            \Log::info('Iniciando envío al SRI (XML firmado por JavaScript)', ['factura_id' => $id]);
            $factura->fecha_envio_sri = now();
            $factura->estado = 'ENVIADA';
            $factura->save();

            $resultadoSRI = $this->enviarAlSRI($xmlFirmado);

            if ($resultadoSRI['success']) {
                // Actualizar factura con respuesta del SRI
                $factura->estado = 'RECIBIDA';
                $factura->estado_sri = $resultadoSRI['estado'];
                if (isset($resultadoSRI['mensajes'])) {
                    $factura->mensajes_sri = json_encode($resultadoSRI['mensajes']);
                }
                $factura->save();

                \Log::info('XML enviado exitosamente al SRI (JavaScript)', [
                    'factura_id' => $id,
                    'estado_sri' => $resultadoSRI['estado']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Factura firmada y enviada exitosamente al SRI',
                    'data' => [
                        'estado' => $factura->estado,
                        'estado_sri' => $factura->estado_sri,
                        'fecha_firma' => $factura->fecha_firma ? $factura->fecha_firma->format('d/m/Y H:i:s') : 'N/A',
                        'fecha_envio_sri' => $factura->fecha_envio_sri ? $factura->fecha_envio_sri->format('d/m/Y H:i:s') : 'N/A',
                        'xml_firmado_path' => $factura->xml_firmado
                    ]
                ]);
            } else {
                // Error en el SRI
                $factura->estado = 'ERROR_SRI';
                $factura->estado_sri = $resultadoSRI['estado'] ?? 'ERROR';
                if (isset($resultadoSRI['mensajes'])) {
                    $factura->mensajes_sri = json_encode($resultadoSRI['mensajes']);
                }
                $factura->save();

                \Log::error('Error al enviar XML al SRI (JavaScript)', [
                    'factura_id' => $id,
                    'error_sri' => $resultadoSRI['message']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar al SRI: ' . $resultadoSRI['message'],
                    'errors' => $resultadoSRI['mensajes'] ?? []
                ], 422);
            }

        } catch (\Exception $e) {
            \Log::error('Error al procesar XML firmado desde JavaScript', [
                'factura_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Firmar factura usando el certificado P12 del declarante
     */
    public function firmarConCertificadoDeclarante(Request $request, $id)
    {
        try {
            $factura = Factura::findOrFail($id);

            // Validar que el declarante tenga certificado P12
            if (!$factura->declarante) {
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene un declarante asignado'
                ], 422);
            }

            if (!$factura->declarante->firma) {
                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado configurado'
                ], 422);
            }

            // Verificar que el archivo de certificado existe y es P12
            $rutaCertificado = public_path('uploads/firmas/' . $factura->declarante->firma);

            if (!file_exists($rutaCertificado)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo del certificado no existe en el servidor'
                ], 422);
            }

            // Verificar que sea un archivo P12
            $extensionCertificado = pathinfo($factura->declarante->firma, PATHINFO_EXTENSION);
            if (strtolower($extensionCertificado) !== 'p12') {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo del certificado debe ser de tipo P12'
                ], 422);
            }

            // Validar contraseña
            $request->validate([
                'password_certificado' => 'required|string'
            ]);

            $passwordCertificado = $request->password_certificado;
            
            // Log detallado para diagnóstico
            \Log::info('Intentando firmar factura', [
                'factura_id' => $id,
                'ruta_certificado' => $rutaCertificado,
                'archivo_existe' => file_exists($rutaCertificado),
                'tamano_archivo' => file_exists($rutaCertificado) ? filesize($rutaCertificado) : 'N/A',
                'es_legible' => file_exists($rutaCertificado) ? is_readable($rutaCertificado) : 'N/A',
                'password_length' => strlen($passwordCertificado)
            ]);
            
            // Obtener XML de la factura
            $rutaXML = storage_path('app/public/' . $factura->xml);
            if (!file_exists($rutaXML)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo XML de la factura no existe'
                ], 422);
            }
            
            $xmlContent = file_get_contents($rutaXML);
            
            \Log::info('Iniciando firma con certificado del declarante', [
                'factura_id' => $id,
                'declarante_id' => $factura->declarante->id,
                'declarante_nombre' => $factura->declarante->nombre,
                'certificado_archivo' => $factura->declarante->firma,
                'xml_size' => strlen($xmlContent)
            ]);
            
            // Usar el nuevo servicio de firma electrónica
            $firmaService = new FirmaElectronicaService($rutaCertificado, $passwordCertificado);
            $xmlFirmado = $firmaService->firmarXML($xmlContent);            // Guardar el XML firmado
            file_put_contents($rutaXML, $xmlFirmado);

            // Actualizar estado de la factura
            $factura->estado = 'FIRMADA';
            $factura->fecha_firma = now();
            $factura->save();

            \Log::info('XML firmado guardado exitosamente', [
                'factura_id' => $id,
                'ruta_xml' => $rutaXML,
                'tamano_xml' => strlen($xmlFirmado)
            ]);

            // Enviar al SRI
            $resultadoEnvio = $this->enviarXMLAlSRI($xmlFirmado, $factura);

            if ($resultadoEnvio['success']) {
                // Si el envío fue exitoso, intentar autorización inmediata
                if ($factura->estado === 'RECIBIDA') {
                    \Log::info('Factura recibida por SRI, iniciando autorización automática');

                    // Esperar un momento para que el SRI procese
                    sleep(2);

                    // Intentar autorización
                    $claveAcceso = $this->extraerClaveAccesoDeXML($factura);
                    if (!empty($claveAcceso)) {
                        $resultadoAutorizacion = $this->solicitarAutorizacionSRI($claveAcceso);

                        if ($resultadoAutorizacion['success'] && isset($resultadoAutorizacion['estado'])) {
                            if ($resultadoAutorizacion['estado'] === 'AUTORIZADO') {
                                $factura->estado = 'AUTORIZADA';
                                $factura->estado_sri = 'AUTORIZADA';
                                $factura->numero_autorizacion = $resultadoAutorizacion['numero_autorizacion'] ?? null;
                                $factura->fecha_autorizacion = isset($resultadoAutorizacion['fecha_autorizacion']) &&
                                    $resultadoAutorizacion['fecha_autorizacion'] ?
                                    \Carbon\Carbon::parse($resultadoAutorizacion['fecha_autorizacion']) : now();
                                $factura->save();

                                \Log::info('Factura autorizada automáticamente', [
                                    'factura_id' => $id,
                                    'numero_autorizacion' => $factura->numero_autorizacion
                                ]);
                            }
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Factura firmada y enviada exitosamente al SRI',
                    'data' => [
                        'estado' => $factura->estado,
                        'estado_sri' => $factura->estado_sri,
                        'numero_autorizacion' => $factura->numero_autorizacion,
                        'fecha_autorizacion' => $factura->fecha_autorizacion,
                        'fecha_firma' => $factura->fecha_firma
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura firmada pero error al enviar al SRI: ' . $resultadoEnvio['message'],
                    'errors' => $resultadoEnvio['errors'] ?? []
                ], 422);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al firmar con certificado del declarante', [
                'factura_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preparar XML para firma en JavaScript
     */
    public function prepararXMLParaFirma(Request $request, $id)
    {
        try {
            \Log::info('=== PREPARANDO XML PARA FIRMA JS ===', ['factura_id' => $id]);
            
            $factura = Factura::findOrFail($id);
            
            // Validar que el declarante tenga certificado P12
            if (!$factura->declarante) {
                \Log::error('Factura sin declarante', ['factura_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene un declarante asignado'
                ], 422);
            }
            
            if (!$factura->declarante->firma) {
                \Log::error('Declarante sin certificado', [
                    'factura_id' => $id,
                    'declarante_id' => $factura->declarante->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado configurado'
                ], 422);
            }
            
            // Verificar que el archivo de certificado existe
            $rutaCertificado = public_path('uploads/firmas/' . $factura->declarante->firma);
            
            if (!file_exists($rutaCertificado)) {
                \Log::error('Archivo de certificado no encontrado', [
                    'factura_id' => $id,
                    'ruta_certificado' => $rutaCertificado,
                    'archivo_nombre' => $factura->declarante->firma
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo del certificado no existe en el servidor',
                    'debug_info' => [
                        'ruta_esperada' => $rutaCertificado,
                        'archivo_nombre' => $factura->declarante->firma,
                        'directorio_existe' => is_dir(dirname($rutaCertificado))
                    ]
                ], 422);
            }
            
            // Obtener XML de la factura
            if (!$factura->xml) {
                \Log::error('Factura sin archivo XML', ['factura_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene archivo XML asignado'
                ], 422);
            }
            
            $rutaXML = storage_path('app/public/' . $factura->xml);
            if (!file_exists($rutaXML)) {
                \Log::error('Archivo XML no encontrado', [
                    'factura_id' => $id,
                    'ruta_xml' => $rutaXML,
                    'xml_nombre' => $factura->xml
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo XML de la factura no existe'
                ], 422);
            }
            
            $xmlContent = file_get_contents($rutaXML);
            if ($xmlContent === false) {
                \Log::error('No se pudo leer el archivo XML', [
                    'factura_id' => $id,
                    'ruta_xml' => $rutaXML
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer el archivo XML'
                ], 500);
            }
            
            // Convertir certificado P12 a base64 para enviar al frontend
            $certificadoContent = file_get_contents($rutaCertificado);
            if ($certificadoContent === false) {
                \Log::error('No se pudo leer el certificado P12', [
                    'factura_id' => $id,
                    'ruta_certificado' => $rutaCertificado
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer el certificado P12'
                ], 500);
            }
            
            $certificadoP12Base64 = base64_encode($certificadoContent);
            
            \Log::info('XML preparado exitosamente para firma JS', [
                'factura_id' => $id,
                'xml_size' => strlen($xmlContent),
                'certificado_size' => strlen($certificadoP12Base64)
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'xml_content' => $xmlContent,
                    'certificado_p12_base64' => $certificadoP12Base64,
                    'factura_id' => $factura->id,
                    'declarante' => [
                        'nombre' => $factura->declarante->nombre,
                        'ruc' => $factura->declarante->ruc
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('=== ERROR PREPARANDO XML PARA FIRMA JS ===', [
                'factura_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir XML firmado desde JavaScript
     */
    public function procesarXMLFirmadoJS(Request $request, $id)
    {
        try {
            $factura = Factura::findOrFail($id);
            
            // Validar que se recibió el XML firmado
            $request->validate([
                'xml_firmado' => 'required|string'
            ]);
            
            $xmlFirmado = $request->input('xml_firmado');
            
            \Log::info('XML recibido para validación XAdES', [
                'factura_id' => $id,
                'xml_size' => strlen($xmlFirmado),
                'xml_preview' => substr($xmlFirmado, 0, 1000),
                'tiene_ds_signature' => strpos($xmlFirmado, '<ds:Signature') !== false || strpos($xmlFirmado, '<ns0:Signature') !== false,
                'tiene_signature' => strpos($xmlFirmado, '<Signature') !== false,
                'tiene_ns1_signature' => strpos($xmlFirmado, '<ns1:Signature') !== false,
                'tiene_xades_properties' => strpos($xmlFirmado, '<xades:QualifyingProperties') !== false,
                'tiene_xades_signed_props' => strpos($xmlFirmado, '<xades:SignedProperties') !== false,
                'contiene_xmlns_ds' => strpos($xmlFirmado, 'xmlns:ds') !== false,
                'contiene_xmlns_xades' => strpos($xmlFirmado, 'xmlns:xades') !== false
            ]);
            
            // Validar que el XML tenga firma digital XAdES-BES
            $tieneDsSignature = strpos($xmlFirmado, '<ds:Signature') !== false || strpos($xmlFirmado, '<ns0:Signature') !== false;
            $tieneSignature = strpos($xmlFirmado, '<Signature') !== false;
            $tieneNs1Signature = strpos($xmlFirmado, '<ns1:Signature') !== false;
            $tieneXAdESProperties = strpos($xmlFirmado, '<xades:QualifyingProperties') !== false;
            
            if (!$tieneDsSignature && !$tieneSignature && !$tieneNs1Signature) {
                \Log::error('XML sin firma digital válida', [
                    'factura_id' => $id,
                    'xml_size' => strlen($xmlFirmado),
                    'tiene_ds_signature' => $tieneDsSignature,
                    'tiene_signature' => $tieneSignature,
                    'tiene_ns1_signature' => $tieneNs1Signature,
                    'tiene_xades_properties' => $tieneXAdESProperties,
                    'xml_completo' => $xmlFirmado // Log completo para debug
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El XML recibido no contiene firma digital válida'
                ], 422);
            }
            
            \Log::info('XML firmado recibido desde JavaScript', [
                'factura_id' => $id,
                'xml_firmado_size' => strlen($xmlFirmado)
            ]);
            
            // Guardar el XML firmado
            $rutaXML = storage_path('app/public/' . $factura->xml);
            file_put_contents($rutaXML, $xmlFirmado);
            
            // Actualizar estado de la factura
            $factura->estado = 'FIRMADA';
            $factura->fecha_firma = now();
            $factura->save();
            
            \Log::info('XML firmado guardado exitosamente', [
                'factura_id' => $id,
                'ruta_xml' => $rutaXML
            ]);
            
            // Leer el XML guardado para asegurar consistencia
            $xmlParaEnvio = file_get_contents($rutaXML);
            
            \Log::info('XML leído para envío al SRI', [
                'factura_id' => $id,
                'xml_size_guardado' => strlen($xmlFirmado),
                'xml_size_leido' => strlen($xmlParaEnvio),
                'son_iguales' => ($xmlFirmado === $xmlParaEnvio)
            ]);
            
            // Enviar al SRI usando el XML leído del archivo
            $resultadoEnvio = $this->enviarXMLAlSRI($xmlParaEnvio, $factura);
            
            if ($resultadoEnvio['success']) {
                // Si el envío fue exitoso, intentar autorización inmediata
                if ($factura->estado === 'RECIBIDA') {
                    \Log::info('Factura recibida por SRI, iniciando autorización automática');
                    
                    // Esperar un momento para que el SRI procese
                    sleep(2);
                    
                    // Intentar autorización
                    $claveAcceso = $this->extraerClaveAccesoDeXML($factura);
                    if (!empty($claveAcceso)) {
                        $resultadoAutorizacion = $this->solicitarAutorizacionSRI($claveAcceso);
                        
                        if ($resultadoAutorizacion['success'] && isset($resultadoAutorizacion['estado'])) {
                            if ($resultadoAutorizacion['estado'] === 'AUTORIZADO') {
                                $factura->estado = 'AUTORIZADA';
                                $factura->estado_sri = 'AUTORIZADA';
                                $factura->numero_autorizacion = $resultadoAutorizacion['numero_autorizacion'] ?? null;
                                $factura->fecha_autorizacion = isset($resultadoAutorizacion['fecha_autorizacion']) && 
                                    $resultadoAutorizacion['fecha_autorizacion'] ? 
                                    \Carbon\Carbon::parse($resultadoAutorizacion['fecha_autorizacion']) : now();
                                $factura->save();
                                
                                \Log::info('Factura autorizada automáticamente', [
                                    'factura_id' => $id,
                                    'numero_autorizacion' => $factura->numero_autorizacion
                                ]);
                            }
                        }
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Factura firmada y enviada exitosamente al SRI',
                    'data' => [
                        'estado' => $factura->estado,
                        'estado_sri' => $factura->estado_sri,
                        'numero_autorizacion' => $factura->numero_autorizacion,
                        'fecha_autorizacion' => $factura->fecha_autorizacion,
                        'fecha_firma' => $factura->fecha_firma
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura firmada pero error al enviar al SRI: ' . $resultadoEnvio['message'],
                    'errors' => $resultadoEnvio['errors'] ?? []
                ], 422);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al recibir XML firmado', [
                'factura_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar XML al SRI (método auxiliar)
     */
    private function enviarXMLAlSRI($xmlContent, $factura)
    {
        try {
            \Log::info('Enviando XML al SRI', [
                'factura_id' => $factura->id,
                'xml_length' => strlen($xmlContent)
            ]);
            
            // Validar que el XML esté firmado (con cualquier prefijo: ds:, ns1:, o sin prefijo)
            $tieneDsSignature = strpos($xmlContent, '<ds:Signature') !== false || strpos($xmlContent, '<ns0:Signature') !== false;
            $tieneSignature = strpos($xmlContent, '<Signature') !== false;
            $tieneNs1Signature = strpos($xmlContent, '<ns1:Signature') !== false;
            
            if (!$tieneDsSignature && !$tieneSignature && !$tieneNs1Signature) {
                \Log::error('XML sin firma digital para envío al SRI', [
                    'factura_id' => $factura->id,
                    'tiene_ds_signature' => $tieneDsSignature,
                    'tiene_signature' => $tieneSignature,
                    'tiene_ns1_signature' => $tieneNs1Signature
                ]);
                throw new \Exception('El XML debe estar firmado digitalmente antes de enviar al SRI');
            }
            
            \Log::info('XML firmado XAdES validado para envío al SRI', [
                'factura_id' => $factura->id,
                'tiene_ds_signature' => $tieneDsSignature,
                'tiene_signature' => $tieneSignature,
                'tiene_ns1_signature' => $tieneNs1Signature,
                'tiene_xades_properties' => strpos($xmlContent, '<xades:QualifyingProperties') !== false,
                'tiene_xades_signed_props' => strpos($xmlContent, '<xades:SignedProperties') !== false
            ]);
            
            // Usar XML en base64 (método estándar del SRI)
            $xmlBase64 = base64_encode($xmlContent);
            
            // Crear SOAP envelope con base64
            $soapEnvelope = $this->crearSoapEnvelopeBase64($xmlBase64);
            
            \Log::info('SOAP envelope creado para envío', [
                'soap_length' => strlen($soapEnvelope),
                'factura_id' => $factura->id,
                'uses_base64' => strpos($soapEnvelope, 'base64') === false ? 'NO' : 'SI'
            ]);
            
            // Enviar al webservice del SRI
            $respuesta = $this->enviarSoapRequestCurl($soapEnvelope);
            
            if ($respuesta === false) {
                \Log::error('Error al conectar con el servicio del SRI');
                return [
                    'success' => false,
                    'message' => 'Error al conectar con el servicio del SRI',
                    'estado' => 'ERROR_CONEXION'
                ];
            }
            
            \Log::info('Respuesta del SRI recibida', [
                'factura_id' => $factura->id,
                'respuesta_length' => strlen($respuesta)
            ]);
            
            // Procesar respuesta del SRI
            $resultadoProcesamiento = $this->procesarRespuestaSRI($respuesta, $factura);
            
            // Verificar si el procesamiento fue exitoso basándose en el estado
            $estadoExitoso = in_array($resultadoProcesamiento['estado'], ['RECIBIDA', 'DEVUELTA']);
            
            if ($estadoExitoso) {
                // Actualizar estado de la factura
                $factura->estado = 'RECIBIDA';
                $factura->estado_sri = $resultadoProcesamiento['estado'];
                $factura->fecha_envio_sri = now();
                
                if (isset($resultadoProcesamiento['comprobantes']) && !empty($resultadoProcesamiento['comprobantes'])) {
                    $factura->mensajes_sri = json_encode($resultadoProcesamiento['comprobantes']);
                }
                
                $factura->save();
                
                \Log::info('Factura actualizada después de envío al SRI', [
                    'factura_id' => $factura->id,
                    'estado' => $factura->estado,
                    'estado_sri' => $factura->estado_sri
                ]);
                
                return [
                    'success' => true,
                    'message' => 'XML enviado exitosamente al SRI',
                    'estado' => $factura->estado,
                    'estado_sri' => $factura->estado_sri,
                    'datos_sri' => $resultadoProcesamiento
                ];
            } else {
                // El SRI rechazó el comprobante
                $mensajesError = [];
                if (isset($resultadoProcesamiento['comprobantes'])) {
                    foreach ($resultadoProcesamiento['comprobantes'] as $comprobante) {
                        if (isset($comprobante['mensajes'])) {
                            foreach ($comprobante['mensajes'] as $mensaje) {
                                $mensajesError[] = $mensaje['mensaje'] ?? 'Error desconocido';
                            }
                        }
                    }
                }
                
                $errorMessage = !empty($mensajesError) ? implode('; ', $mensajesError) : 'Error desconocido del SRI';
                
                \Log::error('SRI rechazó el comprobante', [
                    'factura_id' => $factura->id,
                    'estado_sri' => $resultadoProcesamiento['estado'],
                    'mensajes_error' => $mensajesError
                ]);
                
                throw new \Exception('SRI rechazó el comprobante: ' . $errorMessage);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar XML al SRI', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al enviar al SRI: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    /**
     * Limpiar texto para XML eliminando caracteres especiales y tildes
     */
    private function limpiarTextoParaXML($texto)
    {
        if (empty($texto)) {
            return '';
        }
        
        // Convertir a string si no lo es
        $texto = (string) $texto;
        
        // Eliminar caracteres especiales y tildes
        $caracteresEspeciales = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'ŏ' => 'o', 'ő' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u',
            'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n',
            'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A', 'Ā' => 'A', 'Ă' => 'A', 'Ą' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E', 'Ē' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ę' => 'E', 'Ě' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Ī' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O', 'Ō' => 'O', 'Ŏ' => 'O', 'Ő' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ŭ' => 'U', 'Ů' => 'U', 'Ű' => 'U', 'Ų' => 'U',
            'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N',
            'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C',
        ];
        
        // Reemplazar caracteres especiales
        $textoLimpio = strtr($texto, $caracteresEspeciales);
        
        // Eliminar otros caracteres que puedan causar problemas en XML
        $textoLimpio = preg_replace('/[^\x20-\x7E\s]/', '', $textoLimpio);
        
        // Escapar caracteres especiales de XML
        $textoLimpio = htmlspecialchars($textoLimpio, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        
        return trim($textoLimpio);
    }
}
