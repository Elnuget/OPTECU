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
            // Log para depuración
            \Log::info('Datos recibidos en FacturaController::store', $request->all());
            
            $validator = Validator::make($request->all(), [
                'declarante_id' => 'required|exists:declarante,id',
                'pedido_id' => 'nullable|exists:pedidos,id',
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
            
            // Obtener datos del pedido (puede ser null)
            $pedido = null;
            if ($request->pedido_id) {
                $pedido = Pedido::find($request->pedido_id);
                \Log::info('Pedido encontrado', [
                    'pedido_id' => $request->pedido_id,
                    'numero_orden' => $pedido ? $pedido->numero_orden : 'PEDIDO NO ENCONTRADO',
                    'cliente' => $pedido ? $pedido->cliente : 'N/A'
                ]);
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
                    'tipo' => 'Luna',
                    'descripcion' => 'Luna',
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
            
            // Generar XML
            $xmlPath = $this->generarXMLFactura($pedido, $declarante, $elementos, $subtotal, $iva, $total);
            
            // Crear la factura
            $factura = new Factura();
            $factura->declarante_id = $request->declarante_id;
            $factura->pedido_id = $request->pedido_id ?: null;
            $factura->xml = $xmlPath;
            $factura->monto = round($subtotal, 2);
            $factura->iva = round($iva, 2);
            $factura->tipo = 'comprobante';
            
            if (!$factura->save()) {
                throw new \Exception('No se pudo guardar la factura en la base de datos');
            }
            
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
            \Log::error('Error en FacturaController::store: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generar XML de la factura según formato Ecuador
     */
    private function generarXMLFactura($pedido, $declarante, $elementos, $subtotal, $iva, $total)
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
            $infoTributaria->appendChild($dom->createElement('razonSocial', htmlspecialchars($declarante->nombre ?? 'RAZON SOCIAL')));
            $infoTributaria->appendChild($dom->createElement('ruc', $ruc));
            $infoTributaria->appendChild($dom->createElement('claveAcceso', $claveAcceso));
            $infoTributaria->appendChild($dom->createElement('codDoc', '01'));
            $infoTributaria->appendChild($dom->createElement('estab', $establecimiento));
            $infoTributaria->appendChild($dom->createElement('ptoEmi', $puntoEmision));
            $infoTributaria->appendChild($dom->createElement('secuencial', $secuencial));
            $infoTributaria->appendChild($dom->createElement('dirMatriz', htmlspecialchars($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA')));
            $factura->appendChild($infoTributaria);
            
            // Información de la factura
            $infoFactura = $dom->createElement('infoFactura');
            $infoFactura->appendChild($dom->createElement('fechaEmision', date('d/m/Y')));
            $infoFactura->appendChild($dom->createElement('dirEstablecimiento', htmlspecialchars($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA')));
            $infoFactura->appendChild($dom->createElement('obligadoContabilidad', ($declarante->obligado_contabilidad ?? false) ? 'SI' : 'NO'));
            
            // Siempre usar datos del pedido para el comprador
            $infoFactura->appendChild($dom->createElement('tipoIdentificacionComprador', '05'));
            $infoFactura->appendChild($dom->createElement('razonSocialComprador', htmlspecialchars($pedido->cliente ?? 'CLIENTE NO ESPECIFICADO')));
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
            $pago->appendChild($dom->createElement('formaPago', '01')); // Sin sistema financiero
            $pago->appendChild($dom->createElement('total', number_format($total, 2, '.', '')));
            $pagos->appendChild($pago);
            $infoFactura->appendChild($pagos);
            
            $factura->appendChild($infoFactura);
            
            // Detalles
            $detalles = $dom->createElement('detalles');
            foreach ($elementos as $elemento) {
                $detalle = $dom->createElement('detalle');
                $detalle->appendChild($dom->createElement('codigoPrincipal', strtoupper(str_replace([' ', '/'], '', $elemento['tipo']))));
                $detalle->appendChild($dom->createElement('descripcion', htmlspecialchars($elemento['descripcion'])));
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
            if (is_object($pedido) && property_exists($pedido, 'celular') && $pedido->celular) {
                $campoTelefono = $dom->createElement('campoAdicional', $pedido->celular);
                $campoTelefono->setAttribute('nombre', 'Telefono');
                $infoAdicional->appendChild($campoTelefono);
            }
            if (is_object($pedido) && property_exists($pedido, 'correo_electronico') && $pedido->correo_electronico) {
                $campoEmail = $dom->createElement('campoAdicional', htmlspecialchars($pedido->correo_electronico));
                $campoEmail->setAttribute('nombre', 'Email');
                $infoAdicional->appendChild($campoEmail);
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
        $subtotalExento = 0;
        foreach ($elementos as $elemento) {
            if ($elemento['iva_porcentaje'] == 0) {
                $subtotalExento += $elemento['precio'];
            }
        }
        return $subtotalExento;
    }
    
    /**
     * Calcular dígito verificador usando módulo 11
     */
    private function calcularDigitoVerificador($claveAcceso48)
    {
        $factor = 7;
        $suma = 0;
        
        // Recorrer los 48 dígitos de derecha a izquierda
        for ($i = 47; $i >= 0; $i--) {
            $suma += intval($claveAcceso48[$i]) * $factor;
            $factor--;
            if ($factor == 1) {
                $factor = 7;
            }
        }
        
        $residuo = $suma % 11;
        $digitoVerificador = 11 - $residuo;
        
        if ($digitoVerificador == 11) {
            $digitoVerificador = 0;
        } elseif ($digitoVerificador == 10) {
            $digitoVerificador = 1;
        }
        
        return $digitoVerificador;
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
                
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }
            
            // Si no es AJAX, devolver vista HTML
            return view('facturas.show', compact('factura', 'xmlContent', 'xmlFormatted'));
            
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
}
