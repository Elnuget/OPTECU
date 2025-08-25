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
            
            // Generar XML
            $xmlPath = $this->generarXMLFactura($pedido, $declarante, $elementos, $subtotal, $iva, $total, $medioPago);
            
            // Crear la factura
            $factura = new Factura();
            $factura->declarante_id = $request->declarante_id;
            $factura->pedido_id = $request->pedido_id ?: null;
            $factura->xml = $xmlPath;
            $factura->monto = round($subtotal, 2);
            $factura->iva = round($iva, 2);
            $factura->tipo = 'comprobante';
            $factura->estado = 'CREADA'; // Estado inicial
            
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

    /**
     * Firmar digitalmente y enviar factura al SRI
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function firmarYEnviar($id, Request $request)
    {
        try {
            // Log inicial
            \Log::info('=== INICIO PROCESO FIRMA Y ENVÍO ===', [
                'factura_id' => $id,
                'request_data' => $request->except(['password_certificado']),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validar entrada
            $validator = Validator::make($request->all(), [
                'password_certificado' => 'required|string|min:1',
            ]);

            if ($validator->fails()) {
                \Log::warning('Validación fallida en firmarYEnviar', [
                    'factura_id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            // Obtener la factura
            \Log::info('Buscando factura', ['factura_id' => $id]);
            $factura = Factura::with('declarante')->findOrFail($id);
            
            \Log::info('Factura encontrada', [
                'factura_id' => $factura->id,
                'declarante_id' => $factura->declarante_id,
                'declarante_nombre' => $factura->declarante->nombre ?? 'N/A',
                'xml_path' => $factura->xml,
                'estado_actual' => $factura->estado ?? 'SIN_ESTADO'
            ]);

            // Verificar que existe el XML
            if (!$factura->xml) {
                \Log::error('Factura sin XML', ['factura_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene un XML generado'
                ], 422);
            }

            // Verificar que el declarante tiene certificado
            if (!isset($factura->declarante->firma) || !$factura->declarante->firma) {
                \Log::error('Declarante sin certificado', [
                    'factura_id' => $id,
                    'declarante_id' => $factura->declarante_id,
                    'firma' => $factura->declarante->firma ?? 'NULL'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado digital configurado. Configure un certificado .p12 válido en el campo firma antes de firmar.'
                ], 422);
            }

            // Leer el XML existente
            $xmlPath = storage_path('app/public/' . $factura->xml);
            \Log::info('Verificando archivo XML', [
                'xml_path' => $xmlPath,
                'file_exists' => file_exists($xmlPath),
                'file_size' => file_exists($xmlPath) ? filesize($xmlPath) : 0
            ]);

            if (!file_exists($xmlPath)) {
                \Log::error('Archivo XML no encontrado', [
                    'factura_id' => $id,
                    'xml_path' => $xmlPath
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el archivo XML de la factura'
                ], 422);
            }

            $xmlContent = file_get_contents($xmlPath);
            if (!$xmlContent) {
                \Log::error('Error al leer XML', [
                    'factura_id' => $id,
                    'xml_path' => $xmlPath
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer el contenido del archivo XML'
                ], 422);
            }

            \Log::info('XML leído correctamente', [
                'factura_id' => $id,
                'xml_length' => strlen($xmlContent),
                'xml_preview' => substr($xmlContent, 0, 200) . '...'
            ]);

            // Paso 1: Firmar el XML
            \Log::info('Iniciando proceso de firma XML', ['factura_id' => $id]);
            $xmlFirmado = $this->firmarXML($xmlContent, $factura->declarante, $request->password_certificado);
            
            if (!$xmlFirmado) {
                \Log::error('Error en proceso de firma', ['factura_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al firmar el XML. Verifique la contraseña del certificado.'
                ], 422);
            }

            \Log::info('XML firmado exitosamente', [
                'factura_id' => $id,
                'xml_firmado_length' => strlen($xmlFirmado)
            ]);

            // Guardar XML firmado
            $xmlFirmadoPath = str_replace('.xml', '_firmado.xml', $xmlPath);
            if (!file_put_contents($xmlFirmadoPath, $xmlFirmado)) {
                \Log::error('Error al guardar XML firmado', [
                    'factura_id' => $id,
                    'xml_firmado_path' => $xmlFirmadoPath
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo guardar el XML firmado'
                ], 500);
            }

            \Log::info('XML firmado guardado', [
                'factura_id' => $id,
                'xml_firmado_path' => $xmlFirmadoPath,
                'file_size' => filesize($xmlFirmadoPath)
            ]);

            // Actualizar estado de la factura después de la firma
            $factura->estado = 'FIRMADA';
            $factura->xml_firmado = str_replace(storage_path('app/public/'), '', $xmlFirmadoPath);
            $factura->fecha_firma = now();
            $factura->save();

            \Log::info('Factura marcada como FIRMADA', [
                'factura_id' => $id,
                'xml_firmado_path' => $factura->xml_firmado
            ]);

            // Paso 2: Enviar al SRI
            \Log::info('Iniciando envío al SRI', ['factura_id' => $id]);
            $factura->fecha_envio_sri = now();
            $factura->estado = 'ENVIADA';
            $factura->save();

            $resultadoSRI = $this->enviarAlSRI($xmlFirmado);
            
            \Log::info('Resultado del SRI', [
                'factura_id' => $id,
                'resultado' => $resultadoSRI
            ]);

            if (!$resultadoSRI['success']) {
                // Error al enviar al SRI
                $factura->estado = 'DEVUELTA';
                $factura->estado_sri = 'DEVUELTA';
                $factura->mensajes_sri = json_encode($resultadoSRI['errors'] ?? [$resultadoSRI['message']]);
                $factura->save();

                \Log::error('Error al enviar al SRI', [
                    'factura_id' => $id,
                    'error_message' => $resultadoSRI['message'],
                    'errors' => $resultadoSRI['errors'] ?? []
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar al SRI: ' . $resultadoSRI['message'],
                    'errors' => $resultadoSRI['errors'] ?? []
                ], 422);
            }

            // Actualizar estado según respuesta del SRI
            $estadoSRI = $resultadoSRI['estado'];
            
            if ($estadoSRI === 'RECIBIDA') {
                $factura->estado = 'RECIBIDA';
                $factura->estado_sri = 'RECIBIDA';
            } elseif ($estadoSRI === 'AUTORIZADA') {
                $factura->estado = 'AUTORIZADA';
                $factura->estado_sri = 'AUTORIZADA';
            } else {
                $factura->estado = 'DEVUELTA';
                $factura->estado_sri = $estadoSRI;
                $factura->mensajes_sri = json_encode($resultadoSRI['errors'] ?? ['Estado desconocido: ' . $estadoSRI]);
            }
            
            $factura->numero_autorizacion = $resultadoSRI['numero_autorizacion'] ?? null;
            $factura->fecha_autorizacion = $resultadoSRI['fecha_autorizacion'] ? 
                \Carbon\Carbon::parse($resultadoSRI['fecha_autorizacion']) : now();
            $factura->save();

            \Log::info('Factura actualizada exitosamente', [
                'factura_id' => $id,
                'estado' => $factura->estado,
                'estado_sri' => $factura->estado_sri,
                'numero_autorizacion' => $factura->numero_autorizacion
            ]);

            \Log::info('=== FIN PROCESO FIRMA Y ENVÍO EXITOSO ===', ['factura_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Factura firmada y enviada exitosamente al SRI',
                'data' => [
                    'estado' => $factura->estado,
                    'estado_sri' => $factura->estado_sri,
                    'numero_autorizacion' => $factura->numero_autorizacion ?? 'N/A',
                    'fecha_autorizacion' => $factura->fecha_autorizacion->format('d/m/Y H:i:s'),
                    'fecha_firma' => $factura->fecha_firma->format('d/m/Y H:i:s'),
                    'fecha_envio_sri' => $factura->fecha_envio_sri->format('d/m/Y H:i:s'),
                    'xml_firmado_path' => $factura->xml_firmado
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('=== ERROR CRÍTICO EN FIRMA Y ENVÍO ===', [
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
     * Firmar XML con certificado digital .p12
     *
     * @param  string  $xmlContent
     * @param  object  $declarante
     * @param  string  $password
     * @return string|false
     */
    private function firmarXML($xmlContent, $declarante, $password)
    {
        try {
            \Log::info('=== INICIO PROCESO FIRMA XML ===', [
                'declarante_id' => $declarante->id,
                'declarante_nombre' => $declarante->nombre,
                'firma' => $declarante->firma,
                'xml_length' => strlen($xmlContent)
            ]);

            // Obtener el certificado .p12
            $certificadoPath = $this->obtenerRutaCertificado($declarante);
            
            \Log::info('Ruta del certificado', [
                'certificado_path' => $certificadoPath,
                'file_exists' => $certificadoPath ? file_exists($certificadoPath) : false
            ]);
            
            if (!$certificadoPath || !file_exists($certificadoPath)) {
                \Log::error('Certificado no encontrado', [
                    'certificado_path' => $certificadoPath,
                    'declarante_firma' => $declarante->firma
                ]);
                throw new \Exception('No se encontró el archivo del certificado en: ' . $certificadoPath);
            }

            // Leer el certificado real
            $certificadoContent = file_get_contents($certificadoPath);
            if (!$certificadoContent) {
                \Log::error('Error al leer certificado', ['certificado_path' => $certificadoPath]);
                throw new \Exception('No se pudo leer el contenido del certificado');
            }

            \Log::info('Certificado leído', [
                'certificado_size' => strlen($certificadoContent),
                'password_length' => strlen($password)
            ]);

            // Extraer certificado y clave privada
            $certificados = [];
            if (!openssl_pkcs12_read($certificadoContent, $certificados, $password)) {
                $openssl_error = openssl_error_string();
                \Log::error('Error al leer PKCS12', [
                    'openssl_error' => $openssl_error,
                    'password_provided' => !empty($password)
                ]);
                throw new \Exception('Contraseña del certificado incorrecta o certificado inválido');
            }

            // Verificar que tenemos los componentes necesarios
            if (!isset($certificados['cert']) || !isset($certificados['pkey'])) {
                \Log::error('Certificado incompleto', [
                    'has_cert' => isset($certificados['cert']),
                    'has_pkey' => isset($certificados['pkey']),
                    'keys' => array_keys($certificados)
                ]);
                throw new \Exception('El certificado no contiene los componentes necesarios');
            }

            \Log::info('Certificado PKCS12 procesado exitosamente', [
                'cert_length' => strlen($certificados['cert']),
                'pkey_available' => !empty($certificados['pkey'])
            ]);

            // Implementar firma XAdES-BES (simplificada para el ejemplo)
            // En producción, necesitarías una librería especializada como XMLSecLibs
            $xmlFirmado = $this->aplicarFirmaXAdES($xmlContent, $certificados['cert'], $certificados['pkey']);

            \Log::info('=== FIN PROCESO FIRMA XML EXITOSO ===', [
                'xml_firmado_length' => strlen($xmlFirmado)
            ]);

            return $xmlFirmado;

        } catch (\Exception $e) {
            \Log::error('=== ERROR EN FIRMA XML ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Obtener ruta del certificado del declarante
     *
     * @param  object  $declarante
     * @return string|null
     */
    private function obtenerRutaCertificado($declarante)
    {
        \Log::info('Obteniendo ruta del certificado', [
            'declarante_id' => $declarante->id,
            'firma' => $declarante->firma
        ]);

        $rutaBase = public_path('uploads/firmas/');
        
        // Si el campo firma es una ruta
        if (str_contains($declarante->firma, '/') || str_contains($declarante->firma, '\\')) {
            $rutaCompleta = $rutaBase . basename($declarante->firma);
        } else {
            // Si es un nombre de archivo
            $rutaCompleta = $rutaBase . $declarante->firma;
        }
        
        \Log::info('Ruta del certificado calculada', [
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
     * Aplicar firma XAdES-BES al XML
     * NOTA: Esta es una implementación simplificada
     * En producción se recomienda usar librerías especializadas
     *
     * @param  string  $xmlContent
     * @param  string  $cert
     * @param  string  $privateKey
     * @return string
     */
    private function aplicarFirmaXAdES($xmlContent, $cert, $privateKey)
    {
        // Por ahora, retornamos el XML original con una marca de firmado
        // En una implementación real, aquí se aplicaría la firma XAdES-BES
        $dom = new \DOMDocument();
        $dom->loadXML($xmlContent);
        
        // Agregar elemento de firma básico (placeholder)
        $signature = $dom->createElement('ds:Signature');
        $signature->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        
        $signedInfo = $dom->createElement('ds:SignedInfo');
        $signature->appendChild($signedInfo);
        
        $dom->documentElement->appendChild($signature);
        
        return $dom->saveXML();
    }

    /**
     * Enviar XML firmado al SRI
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

            // URL del servicio web del SRI (pruebas)
            $urlSRI = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
            
            \Log::info('Configurando cliente SOAP', [
                'url_sri' => $urlSRI
            ]);

            // Convertir XML a Base64
            $xmlBase64 = base64_encode($xmlFirmado);
            
            \Log::info('XML convertido a Base64', [
                'xml_base64_length' => strlen($xmlBase64),
                'xml_base64_preview' => substr($xmlBase64, 0, 100) . '...'
            ]);

            // Preparar solicitud SOAP
            $soapClient = new \SoapClient($urlSRI, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE
            ]);

            \Log::info('Cliente SOAP creado, enviando validarComprobante...');

            // Llamar al método validarComprobante
            $response = $soapClient->validarComprobante([
                'xml' => $xmlBase64
            ]);

            \Log::info('Respuesta del SRI recibida', [
                'response_type' => gettype($response),
                'response_data' => is_object($response) ? get_object_vars($response) : $response
            ]);

            // Procesar respuesta
            if (isset($response->RespuestaRecepcionComprobante)) {
                $respuesta = $response->RespuestaRecepcionComprobante;
                
                \Log::info('Procesando RespuestaRecepcionComprobante', [
                    'estado' => $respuesta->estado ?? 'NO_DEFINIDO',
                    'numero_autorizacion' => $respuesta->numeroAutorizacion ?? 'N/A',
                    'fecha_autorizacion' => $respuesta->fechaAutorizacion ?? 'N/A'
                ]);
                
                if ($respuesta->estado === 'RECIBIDA') {
                    \Log::info('=== COMPROBANTE RECIBIDO EXITOSAMENTE ===');
                    return [
                        'success' => true,
                        'estado' => 'RECIBIDA',
                        'numero_autorizacion' => $respuesta->numeroAutorizacion ?? null,
                        'fecha_autorizacion' => $respuesta->fechaAutorizacion ?? null
                    ];
                } else {
                    // Estado DEVUELTA u otro
                    $errores = [];
                    if (isset($respuesta->comprobantes->comprobante->mensajes)) {
                        foreach ($respuesta->comprobantes->comprobante->mensajes as $mensaje) {
                            $errores[] = $mensaje->mensaje;
                        }
                    }
                    
                    \Log::warning('Comprobante devuelto por el SRI', [
                        'estado' => $respuesta->estado,
                        'errores' => $errores
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'Comprobante devuelto por el SRI',
                        'estado' => $respuesta->estado,
                        'errors' => $errores
                    ];
                }
            }

            \Log::error('Respuesta inesperada del SRI', [
                'response' => $response
            ]);

            return [
                'success' => false,
                'message' => 'Respuesta inesperada del SRI'
            ];

        } catch (\SoapFault $e) {
            \Log::error('=== ERROR SOAP AL CONECTAR CON SRI ===', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Error de conexión con el SRI: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            \Log::error('=== ERROR GENERAL AL ENVIAR AL SRI ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar respuesta del SRI: ' . $e->getMessage()
            ];
        }
    }
}
