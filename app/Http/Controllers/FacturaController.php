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
            
            // Agregar teléfono si existe
            if (is_object($pedido) && property_exists($pedido, 'celular') && $pedido->celular) {
                $campoTelefono = $dom->createElement('campoAdicional', htmlspecialchars($pedido->celular));
                $campoTelefono->setAttribute('nombre', 'Telefono');
                $infoAdicional->appendChild($campoTelefono);
            }
            
            // Agregar email si existe
            if (is_object($pedido) && property_exists($pedido, 'correo_electronico') && $pedido->correo_electronico) {
                $campoEmail = $dom->createElement('campoAdicional', htmlspecialchars($pedido->correo_electronico));
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

            // Verificar que el declarante tiene certificado PEM
            if (!isset($factura->declarante->firma) || !$factura->declarante->firma) {
                \Log::error('Declarante sin certificado PEM', [
                    'factura_id' => $id,
                    'declarante_id' => $factura->declarante_id,
                    'firma' => $factura->declarante->firma ?? 'NULL'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'El declarante no tiene un certificado digital configurado. Configure un certificado PEM válido en el campo firma antes de firmar.'
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

            // Log para verificar contenido del XML antes de enviar
            \Log::info('XML que se enviará al SRI', [
                'factura_id' => $id,
                'xml_tiene_infoAdicional' => strpos($xmlFirmado, '<infoAdicional>') !== false,
                'xml_tiene_campoAdicional' => strpos($xmlFirmado, '<campoAdicional') !== false,
                'xml_tiene_signature' => strpos($xmlFirmado, '<ds:Signature') !== false,
                'xml_tiene_signature_value' => strpos($xmlFirmado, '<ds:SignatureValue>') !== false,
                'xml_tiene_placeholder' => strpos($xmlFirmado, 'PEM_SIGNATURE_PLACEHOLDER') !== false,
                'xml_preview_infoAdicional' => $this->extraerSeccionXML($xmlFirmado, 'infoAdicional'),
                'xml_preview_signature' => substr($this->extraerSeccionXML($xmlFirmado, 'ds:SignatureValue'), 0, 200) . '...'
            ]);

            $resultadoSRI = $this->enviarAlSRI($xmlFirmado);
            
            \Log::info('Resultado del SRI', [
                'factura_id' => $id,
                'resultado' => $resultadoSRI
            ]);

            if (!$resultadoSRI['success']) {
                // Error al enviar al SRI
                $factura->estado = 'DEVUELTA';
                $factura->estado_sri = 'DEVUELTA';
                
                // Recopilar todos los detalles de error del SRI
                $erroresDetallados = [];
                if (isset($resultadoSRI['errores_detallados'])) {
                    $erroresDetallados = $resultadoSRI['errores_detallados'];
                } else {
                    $erroresDetallados = $resultadoSRI['errors'] ?? [$resultadoSRI['message']];
                }
                
                $factura->mensajes_sri = json_encode($erroresDetallados);
                $factura->save();

                \Log::error('Error al enviar al SRI', [
                    'factura_id' => $id,
                    'error_message' => $resultadoSRI['message'],
                    'errors' => $resultadoSRI['errors'] ?? [],
                    'errores_detallados' => $erroresDetallados
                ]);

                // Preparar mensaje de error más detallado para el usuario
                $mensajeError = 'Error del SRI: ' . $resultadoSRI['message'];
                if (!empty($erroresDetallados)) {
                    $mensajeError .= "\n\nDetalles de los errores:";
                    foreach ($erroresDetallados as $index => $error) {
                        if (is_array($error)) {
                            $mensajeError .= "\n" . ($index + 1) . ". " . ($error['mensaje'] ?? 'Error sin descripción');
                            if (isset($error['informacionAdicional'])) {
                                $mensajeError .= "\n   Información adicional: " . $error['informacionAdicional'];
                            }
                            if (isset($error['identificador'])) {
                                $mensajeError .= "\n   Código: " . $error['identificador'];
                            }
                        } else {
                            $mensajeError .= "\n" . ($index + 1) . ". " . $error;
                        }
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => $mensajeError,
                    'errors' => $resultadoSRI['errors'] ?? [],
                    'errores_detallados' => $erroresDetallados
                ], 422);
            }

            // Actualizar estado según respuesta del SRI
            try {
                $estadoSRI = $resultadoSRI['estado'] ?? 'DESCONOCIDO';
                
                \Log::info('Procesando estado del SRI', [
                    'factura_id' => $id,
                    'estado_sri' => $estadoSRI,
                    'resultado_completo' => $resultadoSRI
                ]);
                
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
                $factura->fecha_autorizacion = isset($resultadoSRI['fecha_autorizacion']) && $resultadoSRI['fecha_autorizacion'] ? 
                    \Carbon\Carbon::parse($resultadoSRI['fecha_autorizacion']) : now();
                $factura->save();

                \Log::info('Factura actualizada exitosamente', [
                    'factura_id' => $id,
                    'estado' => $factura->estado,
                    'estado_sri' => $factura->estado_sri,
                    'numero_autorizacion' => $factura->numero_autorizacion
                ]);

            } catch (\Exception $e) {
                \Log::error('Error al procesar respuesta del SRI', [
                    'factura_id' => $id,
                    'error' => $e->getMessage(),
                    'resultado_sri' => $resultadoSRI
                ]);
                
                // Marcar como error de procesamiento
                $factura->estado = 'ERROR_PROCESAMIENTO';
                $factura->estado_sri = 'ERROR_PROCESAMIENTO';
                $factura->mensajes_sri = json_encode(['Error al procesar respuesta: ' . $e->getMessage()]);
                $factura->save();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar respuesta del SRI: ' . $e->getMessage()
                ], 500);
            }

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
     * NOTA: Esta es una implementación simplificada
     * En producción se recomienda usar librerías especializadas como XMLSecLibs
     *
     * @param  string  $xmlContent
     * @param  \OpenSSLCertificate|bool  $cert
     * @param  \OpenSSLAsymmetricKey|bool  $privateKey
     * @return string
     */
    private function aplicarFirmaXAdESPEM($xmlContent, $cert, $privateKey)
    {
        try {
            \Log::info('Aplicando firma XAdES-BES con certificado PEM');
            
            // Validar parámetros de entrada
            if (!$cert) {
                throw new \Exception('Certificado no válido');
            }
            
            if (!$privateKey) {
                throw new \Exception('Clave privada no válida');
            }
            
            // Por ahora, retornamos el XML original con una marca de firmado
            // En una implementación real, aquí se aplicaría la firma XAdES-BES
            $dom = new \DOMDocument();
            if (!$dom->loadXML($xmlContent)) {
                throw new \Exception('Error al cargar XML para firmar');
            }
            
            // Obtener información del certificado
            $certInfo = openssl_x509_parse($cert);
            $certData = null;
            openssl_x509_export($cert, $certData);
            
            // Agregar elemento de firma XAdES-BES (simplificado)
            $signature = $dom->createElement('ds:Signature');
            $signature->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            $signature->setAttribute('xmlns:etsi', 'http://uri.etsi.org/01903/v1.3.2#');
            $signature->setAttribute('Id', 'Signature-' . uniqid());
            
            // SignedInfo
            $signedInfo = $dom->createElement('ds:SignedInfo');
            $canonicalizationMethod = $dom->createElement('ds:CanonicalizationMethod');
            $canonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            $signedInfo->appendChild($canonicalizationMethod);
            
            $signatureMethod = $dom->createElement('ds:SignatureMethod');
            $signatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            $signedInfo->appendChild($signatureMethod);
            
            // Reference
            $reference = $dom->createElement('ds:Reference');
            $reference->setAttribute('URI', '#comprobante');
            
            $transforms = $dom->createElement('ds:Transforms');
            $transform = $dom->createElement('ds:Transform');
            $transform->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            $transforms->appendChild($transform);
            $reference->appendChild($transforms);
            
            $digestMethod = $dom->createElement('ds:DigestMethod');
            $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $reference->appendChild($digestMethod);
            
            // Calcular digest del documento
            $canonicalXML = $dom->C14N();
            $digestValue = base64_encode(sha1($canonicalXML, true));
            $digestValueNode = $dom->createElement('ds:DigestValue', $digestValue);
            $reference->appendChild($digestValueNode);
            
            $signedInfo->appendChild($reference);
            $signature->appendChild($signedInfo);
            
            // Calcular SignatureValue real usando la clave privada
            $signedInfoCanonical = $signedInfo->C14N();
            $signatureBinary = '';
            
            // Firmar el SignedInfo con la clave privada
            if (openssl_sign($signedInfoCanonical, $signatureBinary, $privateKey, OPENSSL_ALGO_SHA1)) {
                $signatureValueBase64 = base64_encode($signatureBinary);
                \Log::info('Firma digital generada exitosamente', [
                    'signedInfo_length' => strlen($signedInfoCanonical),
                    'signature_binary_length' => strlen($signatureBinary),
                    'signature_base64_length' => strlen($signatureValueBase64)
                ]);
            } else {
                \Log::error('Error al generar firma digital');
                throw new \Exception('No se pudo generar la firma digital con la clave privada');
            }
            
            $signatureValue = $dom->createElement('ds:SignatureValue', $signatureValueBase64);
            $signature->appendChild($signatureValue);
            
            // KeyInfo
            $keyInfo = $dom->createElement('ds:KeyInfo');
            $x509Data = $dom->createElement('ds:X509Data');
            
            // Limpiar y formatear el certificado
            $certPEM = str_replace([
                '-----BEGIN CERTIFICATE-----',
                '-----END CERTIFICATE-----',
                "\r", "\n", " "
            ], '', $certData);
            
            $x509Certificate = $dom->createElement('ds:X509Certificate', $certPEM);
            $x509Data->appendChild($x509Certificate);
            $keyInfo->appendChild($x509Data);
            $signature->appendChild($keyInfo);
            
            // Agregar información del certificado al log
            \Log::info('Información del certificado PEM usado', [
                'subject' => $certInfo['subject']['CN'] ?? 'N/A',
                'issuer' => $certInfo['issuer']['CN'] ?? 'N/A',
                'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t'] ?? 0),
                'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t'] ?? 0)
            ]);
            
            // Insertar firma antes del cierre del elemento raíz
            $dom->documentElement->appendChild($signature);
            
            \Log::info('Firma XAdES-BES aplicada exitosamente con PEM');
            
            return $dom->saveXML();
            
        } catch (\Exception $e) {
            \Log::error('Error al aplicar firma XAdES-BES con PEM: ' . $e->getMessage());
            throw new \Exception('Error al aplicar firma digital PEM: ' . $e->getMessage());
        }
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

            // Convertir XML a Base64
            $xmlBase64 = base64_encode($xmlFirmado);
            
            \Log::info('XML convertido a Base64', [
                'xml_base64_length' => strlen($xmlBase64),
                'xml_base64_preview' => substr($xmlBase64, 0, 100) . '...'
            ]);

            // Crear SOAP envelope manualmente (como en el código funcional)
            $soapEnvelope = $this->crearSoapEnvelope($xmlBase64);
            
            \Log::info('SOAP envelope creado', [
                'soap_length' => strlen($soapEnvelope),
                'soap_preview' => substr($soapEnvelope, 0, 500) . '...'
            ]);

            // Enviar con cURL (como en el código funcional)
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
     * @param  string  $xmlBase64
     * @return string
     */
    private function crearSoapEnvelope($xmlBase64)
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
        $url = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline';
        
        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: ""',
            'Content-Length: ' . strlen($soapEnvelope)
        ];
        
        \Log::info('Enviando petición cURL al SRI', [
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
        
        \Log::info('Respuesta cURL del SRI', [
            'http_code' => $httpCode,
            'curl_error' => $error,
            'response_length' => $response ? strlen($response) : 0
        ]);
        
        if ($error) {
            \Log::error('Error CURL: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            \Log::error('HTTP Error: ' . $httpCode);
            return false;
        }
        
        return $response;
    }

    /**
     * Procesar respuesta XML del SRI
     *
     * @param  string  $xmlResponse
     * @return array
     */
    private function procesarRespuestaSRI($xmlResponse)
    {
        try {
            \Log::info('Procesando respuesta XML del SRI');
            
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
            
            \Log::info('Estado extraído del XML', ['estado' => $estado]);
            
            $respuesta = [
                'estado' => $estado,
                'comprobantes' => []
            ];
            
            // Si hay errores, extraer los mensajes
            $comprobantesNodes = $xpath->query('//ns2:validarComprobanteResponse/RespuestaRecepcionComprobante/comprobantes/comprobante');
            
            foreach ($comprobantesNodes as $comprobanteNode) {
                $comprobante = [];
                
                $claveAccesoNode = $xpath->query('.//claveAcceso', $comprobanteNode);
                if ($claveAccesoNode->length > 0) {
                    $comprobante['claveAcceso'] = $claveAccesoNode->item(0)->textContent;
                }
                
                $mensajesNodes = $xpath->query('.//mensajes/mensaje', $comprobanteNode);
                $comprobante['mensajes'] = [];
                
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
                    
                    $infoAdicionalNode = $xpath->query('.//informacionAdicional', $mensajeNode);
                    if ($infoAdicionalNode->length > 0) {
                        $mensaje['informacionAdicional'] = $infoAdicionalNode->item(0)->textContent;
                    }
                    
                    $tipoNode = $xpath->query('.//tipo', $mensajeNode);
                    if ($tipoNode->length > 0) {
                        $mensaje['tipo'] = $tipoNode->item(0)->textContent;
                    }
                    
                    $comprobante['mensajes'][] = $mensaje;
                }
                
                $respuesta['comprobantes'][] = $comprobante;
            }
            
            \Log::info('Respuesta XML procesada exitosamente', [
                'estado' => $respuesta['estado'],
                'num_comprobantes' => count($respuesta['comprobantes'])
            ]);
            
            return $respuesta;
            
        } catch (\Exception $e) {
            \Log::error('Error procesando respuesta SRI: ' . $e->getMessage());
            return [
                'estado' => 'ERROR',
                'comprobantes' => [],
                'error' => 'Error procesando respuesta: ' . $e->getMessage()
            ];
        }
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
}
