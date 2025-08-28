<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\SecuencialSri;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SriPythonService
{
    private $xmlSriService;
    
    public function __construct(XmlSriService $xmlSriService)
    {
        $this->xmlSriService = $xmlSriService;
    }
    
    /**
     * Obtener el servicio XML SRI para operaciones directas
     */
    public function getXmlSriService()
    {
        return $this->xmlSriService;
    }
    
    /**
     * Procesar factura completa: generar XML, firmar y enviar al SRI
     */
    public function procesarFacturaCompleta($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago, $passwordCertificado)
    {
        try {
            // ⚠️ VALIDACIÓN OBLIGATORIA: Verificar que estemos en ambiente de pruebas
            $this->validarAmbientePruebas();
            
            Log::info('=== INICIO PROCESAMIENTO FACTURA CON API PYTHON ===', [
                'factura_id' => $factura->id
            ]);
            
            // Generar datos en formato requerido por el API Python
            $invoiceData = $this->prepararDatosFactura($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago);
            
            Log::info('Datos de factura preparados para procesamiento local', [
                'invoice_data_keys' => array_keys($invoiceData)
            ]);
            
            // Usar certificado del declarante directamente (mayor seguridad)
            $certificatePath = $declarante->ruta_certificado;
            
            if (!file_exists($certificatePath)) {
                throw new \Exception('Certificado del declarante no encontrado: ' . $certificatePath);
            }
            
            // Procesar con servicio local
            $resultado = $this->xmlSriService->procesarFacturaCompleta(
                $invoiceData, 
                $certificatePath, 
                $passwordCertificado
            );
            
            Log::info('Respuesta del procesamiento local recibida', [
                'success' => $resultado['success'] ?? false
            ]);
            
            if (!$resultado['success']) {
                throw new \Exception($resultado['message'] ?? 'Error en el procesamiento local');
            }
            
            $result = $resultado['result'];
            
            // Verificar si el SRI devolvió la factura (error secuencial registrado, etc.)
            if (isset($result['isReceived']) && !$result['isReceived']) {
                // Analizar motivo del rechazo
                $motivoRechazo = $this->analizarRespuestaSRI($result);
                
                if ($motivoRechazo['requiere_nuevo_secuencial']) {
                    Log::warning('SRI rechazó por secuencial duplicado, generando nuevo secuencial', [
                        'factura_id' => $factura->id,
                        'secuencial_anterior' => $result['accessKey'] ?? 'NO_DISPONIBLE',
                        'motivo' => $motivoRechazo['mensaje']
                    ]);
                    
                    // Generar nuevo secuencial y reintentar una vez
                    return $this->reintentarConNuevoSecuencial($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago, $passwordCertificado);
                }
            }
            
            // Actualizar factura en base de datos
            $this->actualizarEstadoFactura($factura, $result);
            
            // Guardar XML firmado si está disponible
            $xmlFirmadoGuardado = false;
            
            if (isset($result['xmlFileSigned']) && !empty($result['xmlFileSigned'])) {
                $this->guardarXMLFirmado($factura, $result['xmlFileSigned']);
                $xmlFirmadoGuardado = true;
                Log::info('XML firmado guardado exitosamente desde procesamiento', [
                    'factura_id' => $factura->id,
                    'xml_length' => strlen($result['xmlFileSigned'])
                ]);
            } else {
                Log::warning('XML firmado no disponible en resultado', [
                    'result_keys' => array_keys($result),
                    'tiene_xmlFileSigned' => isset($result['xmlFileSigned']),
                    'xmlFileSigned_empty' => empty($result['xmlFileSigned'] ?? ''),
                    'xmlFileSigned_type' => gettype($result['xmlFileSigned'] ?? null),
                    'xmlFileSigned_preview' => isset($result['xmlFileSigned']) ? substr($result['xmlFileSigned'], 0, 200) : 'NO_EXISTE'
                ]);
                
                // Intentar buscar el XML en otros lugares del resultado
                if (isset($result['data']['xmlFileSigned']) && !empty($result['data']['xmlFileSigned'])) {
                    $this->guardarXMLFirmado($factura, $result['data']['xmlFileSigned']);
                    $xmlFirmadoGuardado = true;
                    Log::info('XML firmado encontrado y guardado desde result.data.xmlFileSigned');
                }
            }
            
            // Guardar XML autorizado si está disponible
            if (isset($result['xmlAutorizado']) && !empty($result['xmlAutorizado'])) {
                $this->guardarXMLAutorizado($factura, $result['xmlAutorizado']);
                Log::info('XML autorizado guardado exitosamente desde procesamiento');
            } elseif (isset($result['sriResponse']['comprobante']) && !empty($result['sriResponse']['comprobante'])) {
                $this->guardarXMLAutorizado($factura, $result['sriResponse']['comprobante']);
                Log::info('XML autorizado guardado desde respuesta SRI');
            }
            
            Log::info('=== FIN PROCESAMIENTO EXITOSO CON SERVICIO LOCAL ===', [
                'clave_acceso' => $result['accessKey'] ?? 'NO_DISPONIBLE',
                'recibida' => $result['isReceived'] ?? false,
                'autorizada' => $result['isAuthorized'] ?? false
            ]);
            
            return [
                'success' => true,
                'factura' => $factura->fresh(),
                'clave_acceso' => $result['accessKey'] ?? null,
                'recibida' => $result['isReceived'] ?? false,
                'autorizada' => $result['isAuthorized'] ?? false,
                'xml_firmado' => $result['xmlFileSigned'] ?? null,
                'message' => $this->generarMensajeExito($result)
            ];
            
        } catch (\Exception $e) {
            Log::error('=== ERROR EN PROCESAMIENTO CON API PYTHON ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al procesar factura: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    /**
     * Preparar datos de factura en formato requerido por API Python
     */
    private function prepararDatosFactura($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago)
    {
        // Generar secuencial único usando el nuevo sistema
        $secuencial = $this->generarSecuencialUnico($factura, $declarante, $pedido);
        
        Log::info('Secuencial generado para factura', [
            'factura_id' => $factura->id,
            'secuencial' => $secuencial,
            'ruc' => $declarante->ruc,
            'establecimiento' => $declarante->establecimiento ?? '001',
            'punto_emision' => $declarante->punto_emision ?? '001'
        ]);
        
        // Fecha actual
        $fecha = now();
        
        // Información del documento
        $documentInfo = [
            'accessKey' => '', // Se generará en el API Python
            'businessName' => $this->limpiarTexto($declarante->nombre ?? 'RAZON SOCIAL'),
            'commercialName' => $this->limpiarTexto($declarante->nombre_comercial ?? $declarante->nombre ?? 'NOMBRE COMERCIAL'),
            'businessAddress' => $this->limpiarTexto($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA'),
            'dayEmission' => $fecha->format('d'),
            'monthEmission' => $fecha->format('m'),
            'yearEmission' => $fecha->format('Y'),
            'codDoc' => '01', // Factura
            'rucBusiness' => str_pad($declarante->ruc ?? '9999999999999', 13, '0', STR_PAD_LEFT),
            'environment' => '1', // ⚠️ AMBIENTE DE PRUEBAS SRI (1=pruebas, 2=producción)
            'typeEmission' => '1', // ⚠️ EMISIÓN NORMAL (1=normal, 2=contingencia)
            'establishment' => str_pad($declarante->establecimiento ?? '001', 3, '0', STR_PAD_LEFT),
            'establishmentAddress' => $this->limpiarTexto($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA'),
            'emissionPoint' => str_pad($declarante->punto_emision ?? '001', 3, '0', STR_PAD_LEFT),
            'sequential' => $secuencial,
            'obligatedAccounting' => ($declarante->obligado_contabilidad ?? false) ? 'SI' : 'NO'
        ];
        
        // Información del cliente
        $customer = [
            'identificationType' => '05', // Cédula
            'customerName' => $this->limpiarTexto($pedido->cliente ?? 'CLIENTE NO ESPECIFICADO'),
            'customerDni' => $pedido->cedula ?? '9999999999',
            'customerAddress' => $this->limpiarTexto($declarante->direccion_matriz ?? 'DIRECCION NO ESPECIFICADA')
        ];
        
        // Mapear medio de pago
        $paymentMethodCode = $this->mapearMedioPago($medioPago);
        
        // Información de pago
        $payment = [
            'totalWithoutTaxes' => number_format($subtotal, 2, '.', ''),
            'totalDiscount' => '0.00',
            'gratuity' => '0.00',
            'totalAmount' => number_format($total, 2, '.', ''),
            'currency' => 'DOLAR',
            'paymentMethodCode' => $paymentMethodCode,
            'totalPayment' => number_format($total, 2, '.', '')
        ];
        
        // Detalles de productos
        $details = [];
        foreach ($elementos as $elemento) {
            $details[] = [
                'productCode' => $elemento['codigo'],
                'productName' => $this->limpiarTexto($elemento['descripcion']),
                'description' => $this->limpiarTexto($elemento['descripcion']),
                'quantity' => $elemento['cantidad'],
                'price' => number_format($elemento['precio_unitario'], 2, '.', ''),
                'discount' => '0.00',
                'subTotal' => number_format($elemento['subtotal'], 2, '.', ''),
                'taxTypeCode' => '2', // IVA
                'percentageCode' => $elemento['codigo_porcentaje'],
                'rate' => $elemento['tarifa'],
                'taxableBaseTax' => number_format($elemento['subtotal'], 2, '.', ''),
                'taxValue' => number_format($elemento['valor_impuesto'], 2, '.', '')
            ];
        }
        
        // Totales con impuestos
        $totalsWithTax = [];
        
        // IVA 15% si existe
        if ($iva > 0) {
            $totalsWithTax[] = [
                'taxCode' => '2', // IVA
                'percentageCode' => '4', // 15%
                'taxableBase' => number_format($subtotal - $this->calcularSubtotalExento($elementos), 2, '.', ''),
                'taxValue' => number_format($iva, 2, '.', '')
            ];
        }
        
        // IVA 0% si hay elementos exentos
        $subtotalExento = $this->calcularSubtotalExento($elementos);
        if ($subtotalExento > 0) {
            $totalsWithTax[] = [
                'taxCode' => '2', // IVA
                'percentageCode' => '0', // 0%
                'taxableBase' => number_format($subtotalExento, 2, '.', ''),
                'taxValue' => '0.00'
            ];
        }
        
        // Información adicional
        $additionalInfo = [];
        
        if (is_object($pedido) && property_exists($pedido, 'celular') && $pedido->celular) {
            $additionalInfo[] = [
                'name' => 'Telefono',
                'value' => $this->limpiarTexto($pedido->celular)
            ];
        }
        
        if (is_object($pedido) && property_exists($pedido, 'correo_electronico') && $pedido->correo_electronico) {
            $additionalInfo[] = [
                'name' => 'Email',
                'value' => $this->limpiarTexto($pedido->correo_electronico)
            ];
        }
        
        // Si no hay campos adicionales, agregar uno por defecto
        if (empty($additionalInfo)) {
            $additionalInfo[] = [
                'name' => 'Sistema',
                'value' => 'Sistema de Facturacion Electronica OPTECU'
            ];
        }
        
        return [
            'documentInfo' => $documentInfo,
            'customer' => $customer,
            'payment' => $payment,
            'details' => $details,
            'additionalInfo' => $additionalInfo,
            'totalsWithTax' => $totalsWithTax
        ];
    }
    
    /**
     * Actualizar estado de factura según resultado del API Python
     */
    private function actualizarEstadoFactura($factura, $result)
    {
        try {
            // Actualizar clave de acceso si está disponible
            if (isset($result['accessKey'])) {
                $factura->clave_acceso = $result['accessKey'];
            }
            
            // Actualizar estado SRI si está disponible
            if (isset($result['sriResponse']['estado'])) {
                $factura->estado_sri = $result['sriResponse']['estado'];
            }
            
            // Actualizar número de autorización si está disponible
            if (isset($result['sriResponse']['numeroAutorizacion'])) {
                $factura->numero_autorizacion = $result['sriResponse']['numeroAutorizacion'];
            }
            
            // Actualizar fecha de autorización si está disponible
            if (isset($result['sriResponse']['fechaAutorizacion'])) {
                try {
                    $factura->fecha_autorizacion = \Carbon\Carbon::parse($result['sriResponse']['fechaAutorizacion']);
                } catch (\Exception $e) {
                    Log::warning('Error parseando fecha de autorización', ['fecha' => $result['sriResponse']['fechaAutorizacion']]);
                }
            }
            
            // Guardar mensajes del SRI si existen
            if (isset($result['sriResponse']['mensajes'])) {
                $factura->mensajes_sri = is_array($result['sriResponse']['mensajes']) 
                    ? json_encode($result['sriResponse']['mensajes'])
                    : $result['sriResponse']['mensajes'];
            }
            
            // Determinar estado interno según resultados
            if (isset($result['isAuthorized']) && $result['isAuthorized']) {
                $factura->estado = 'AUTORIZADA';
            } elseif (isset($result['isReceived']) && $result['isReceived']) {
                $factura->estado = 'RECIBIDA';
            } elseif (isset($result['sriResponse']['estado'])) {
                // Si hay respuesta del SRI, usar ese estado
                $estadoSri = $result['sriResponse']['estado'];
                if ($estadoSri === 'RECIBIDA') {
                    $factura->estado = 'RECIBIDA';
                } elseif ($estadoSri === 'AUTORIZADA') {
                    $factura->estado = 'AUTORIZADA';
                } elseif ($estadoSri === 'DEVUELTA') {
                    $factura->estado = 'DEVUELTA';
                } elseif ($estadoSri === 'NO_AUTORIZADA') {
                    $factura->estado = 'NO_AUTORIZADA';
                } else {
                    $factura->estado = 'ENVIADA';
                }
            } elseif (isset($result['isRejected']) && $result['isRejected']) {
                $factura->estado = 'DEVUELTA';
            } elseif (isset($result['accessKey'])) {
                // Si tenemos clave de acceso pero no respuesta clara del SRI, 
                // es porque se firmó pero no se envió exitosamente al SRI
                $factura->estado = 'FIRMADA';
            } else {
                $factura->estado = 'FIRMADA';
            }
            
            // Actualizar fechas de proceso
            $factura->fecha_firma = now();
            if ($factura->estado !== 'FIRMADA') {
                $factura->fecha_envio_sri = now();
            }
            
            $factura->save();
            
            // Registrar secuencial en la base de datos para evitar duplicados futuros
            $this->registrarSecuencialEnBD($factura, $result);
            
            Log::info('Estado de factura actualizado completamente', [
                'factura_id' => $factura->id,
                'estado_interno' => $factura->estado,
                'estado_sri' => $factura->estado_sri,
                'clave_acceso' => $factura->clave_acceso,
                'numero_autorizacion' => $factura->numero_autorizacion
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error actualizando estado de factura', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Guardar XML firmado en el sistema de archivos y base de datos
     */
    private function guardarXMLFirmado($factura, $xmlContent)
    {
        try {
            Log::info('Iniciando guardado de XML firmado', [
                'factura_id' => $factura->id,
                'xml_length' => strlen($xmlContent ?? '')
            ]);
            
            if (empty($xmlContent)) {
                throw new \Exception('Contenido XML vacío');
            }
            
            // Opción 1: Guardar directamente en la base de datos (más confiable)
            $factura->xml_firmado = $xmlContent;
            $factura->fecha_firma = now();
            
            // Actualizar estado si aún está en CREADA
            if ($factura->estado === 'CREADA') {
                $factura->estado = 'FIRMADA';
            }
            
            $factura->save();
            
            Log::info('XML firmado guardado exitosamente en BD', [
                'factura_id' => $factura->id,
                'xml_firmado_length' => strlen($factura->xml_firmado),
                'nuevo_estado' => $factura->estado
            ]);
            
            // Opción 2: También guardar en archivo como respaldo (opcional)
            try {
                $xmlPath = 'facturas/' . date('Y/m');
                $xmlFullPath = storage_path('app/public/' . $xmlPath);
                
                if (!is_dir($xmlFullPath)) {
                    mkdir($xmlFullPath, 0755, true);
                }
                
                $xmlFileName = 'factura_firmada_' . ($factura->clave_acceso ?? $factura->id) . '_' . time() . '.xml';
                $xmlFilePath = $xmlFullPath . DIRECTORY_SEPARATOR . $xmlFileName;
                
                file_put_contents($xmlFilePath, $xmlContent);
                
                Log::info('XML firmado también guardado como archivo de respaldo', [
                    'factura_id' => $factura->id,
                    'archivo_respaldo' => $xmlFilePath
                ]);
                
            } catch (\Exception $fileError) {
                Log::warning('No se pudo crear archivo de respaldo (pero XML se guardó en BD)', [
                    'factura_id' => $factura->id,
                    'error_archivo' => $fileError->getMessage()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error guardando XML firmado', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Guardar XML autorizado en la base de datos
     */
    private function guardarXMLAutorizado($factura, $xmlContent)
    {
        try {
            Log::info('Iniciando guardado de XML autorizado', [
                'factura_id' => $factura->id,
                'xml_length' => strlen($xmlContent ?? '')
            ]);
            
            if (empty($xmlContent)) {
                throw new \Exception('Contenido XML autorizado vacío');
            }
            
            // Guardar XML autorizado directamente en la base de datos
            $factura->xml_autorizado = $xmlContent;
            $factura->fecha_autorizacion = now();
            $factura->estado = 'AUTORIZADA';
            
            $factura->save();
            
            Log::info('XML autorizado guardado exitosamente en BD', [
                'factura_id' => $factura->id,
                'xml_autorizado_length' => strlen($factura->xml_autorizado),
                'estado_final' => $factura->estado
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error guardando XML autorizado', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mapear medio de pago a códigos SRI
     */
    private function mapearMedioPago($medioPago)
    {
        $medioPagoNombre = strtolower(trim($medioPago->medio_de_pago ?? ''));
        
        switch ($medioPagoNombre) {
            case 'efectivo':
                return '01'; // Sin utilización del sistema financiero
            case 'transferencia':
            case 'transferencia pichincha':
            case 'transferencia guayaquil':
            case 'transferencia de una':
                return '17'; // Dinero electrónico
            case 'tarjeta débito':
            case 'tarjeta banco':
                return '16'; // Tarjeta de débito
            case 'tarjeta crédito':
                return '19'; // Tarjeta de crédito
            default:
                return '01'; // Valor por defecto
        }
    }
    
    /**
     * Calcular subtotal de elementos exentos
     */
    private function calcularSubtotalExento($elementos)
    {
        $subtotalExento = 0;
        foreach ($elementos as $elemento) {
            if ($elemento['codigo_porcentaje'] === '0') {
                $subtotalExento += $elemento['subtotal'];
            }
        }
        return $subtotalExento;
    }
    
    /**
     * Limpiar texto eliminando caracteres especiales
     */
    private function limpiarTexto($texto)
    {
        if (empty($texto)) {
            return '';
        }
        
        $texto = (string) $texto;
        
        // Eliminar tildes y caracteres especiales
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
        
        $textoLimpio = strtr($texto, $caracteresEspeciales);
        $textoLimpio = preg_replace('/[^\x20-\x7E\s]/', '', $textoLimpio);
        
        return trim($textoLimpio);
    }
    
    /**
     * Validar que estemos en ambiente de pruebas SRI
     * ⚠️ SEGURIDAD: Previene envío accidental a producción
     * NOTA: Ya no depende de archivo .env - configuración hardcodeada
     */
    private function validarAmbientePruebas()
    {
        // Verificar que la configuración Python esté en pruebas
        $pythonScript = public_path('SriSignXml/sri_processor.py');
        
        if (file_exists($pythonScript)) {
            $scriptContent = file_get_contents($pythonScript);
            
            // Verificar que las URLs sean de pruebas (celcer)
            if (strpos($scriptContent, 'celcer.sri.gob.ec') === false) {
                throw new \Exception('⚠️ PELIGRO: Script Python no configurado para ambiente de pruebas');
            }
            
            // Verificar que no contenga URLs de producción (cel)
            if (strpos($scriptContent, '://cel.sri.gob.ec') !== false) {
                throw new \Exception('⚠️ PELIGRO: Detectadas URLs de PRODUCCIÓN en script Python');
            }
        }
        
        // Log de seguridad
        Log::warning('✅ VALIDACIÓN AMBIENTE: Confirmado uso de webservices de PRUEBAS SRI (sin .env)');
    }

    /**
     * Generar mensaje de éxito según resultados
     */
    private function generarMensajeExito($result)
    {
        $mensaje = 'Procesamiento completado exitosamente';
        
        if (isset($result['isAuthorized']) && $result['isAuthorized']) {
            $mensaje = 'Factura firmada y autorizada por el SRI exitosamente';
        } elseif (isset($result['isReceived']) && $result['isReceived']) {
            $mensaje = 'Factura firmada y recibida por el SRI. Pendiente de autorización.';
        } elseif (isset($result['xmlFileSigned']) && !empty($result['xmlFileSigned'])) {
            $mensaje = 'Factura firmada digitalmente. Lista para envío al SRI.';
        }
        
        return $mensaje;
    }
    
    /**
     * Analizar respuesta del SRI para determinar causa del rechazo
     */
    private function analizarRespuestaSRI($result)
    {
        $analisis = [
            'requiere_nuevo_secuencial' => false,
            'mensaje' => 'Error desconocido del SRI',
            'codigo_error' => null,
            'solucion_sugerida' => ''
        ];
        
        // Verificar si hay respuesta del SRI en el resultado
        if (isset($result['sriResponse']['mensajes'])) {
            $mensajes = $result['sriResponse']['mensajes'];
            
            foreach ($mensajes as $mensaje) {
                $identificador = $mensaje['identificador'] ?? '';
                $mensajeTexto = $mensaje['mensaje'] ?? '';
                
                // Error secuencial registrado (código 45)
                if ($identificador === '45' || stripos($mensajeTexto, 'SECUENCIAL REGISTRADO') !== false) {
                    $analisis['requiere_nuevo_secuencial'] = true;
                    $analisis['mensaje'] = 'Número secuencial ya fue utilizado anteriormente';
                    $analisis['codigo_error'] = '45';
                    $analisis['solucion_sugerida'] = 'Generar nuevo número secuencial único';
                    
                    // Marcar secuencial como devuelto en BD si tenemos clave de acceso
                    $this->marcarSecuencialComoDevuelto($result, $mensajeTexto);
                    break;
                }
                
                // Otros códigos de error que requieren nuevo secuencial
                if (in_array($identificador, ['43', '44', '45'])) {
                    $analisis['requiere_nuevo_secuencial'] = true;
                    $analisis['mensaje'] = $mensajeTexto;
                    $analisis['codigo_error'] = $identificador;
                    $analisis['solucion_sugerida'] = 'Generar nuevo número secuencial';
                    
                    // Marcar como devuelto
                    $this->marcarSecuencialComoDevuelto($result, $mensajeTexto);
                    break;
                }
            }
        }
        
        // Si no se encontró información específica en mensajes, verificar estado general
        if (!$analisis['requiere_nuevo_secuencial'] && isset($result['sriResponse']['estado'])) {
            $estado = $result['sriResponse']['estado'];
            if ($estado === 'DEVUELTA') {
                $analisis['mensaje'] = 'Comprobante devuelto por el SRI';
                $analisis['solucion_sugerida'] = 'Revisar datos del comprobante';
                
                // Marcar como devuelto
                $this->marcarSecuencialComoDevuelto($result, 'Comprobante devuelto');
            }
        }
        
        return $analisis;
    }
    
    /**
     * Marcar secuencial como devuelto en la base de datos
     */
    private function marcarSecuencialComoDevuelto($result, $motivo)
    {
        try {
            $claveAcceso = $result['accessKey'] ?? null;
            if (!$claveAcceso) {
                return;
            }
            
            $registro = SecuencialSri::where('clave_acceso', $claveAcceso)->first();
            if ($registro) {
                $registro->marcarComoDevuelta($motivo);
                Log::info('Secuencial marcado como devuelto por SRI', [
                    'clave_acceso' => $claveAcceso,
                    'motivo' => $motivo
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error marcando secuencial como devuelto', [
                'error' => $e->getMessage(),
                'clave_acceso' => $result['accessKey'] ?? 'N/A'
            ]);
        }
    }
    
    /**
     * Reintentar procesamiento con nuevo secuencial
     */
    private function reintentarConNuevoSecuencial($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago, $passwordCertificado)
    {
        try {
            Log::info('=== REINTENTO CON NUEVO SECUENCIAL ===', [
                'factura_id' => $factura->id
            ]);
            
            // Generar nuevo secuencial único basado en timestamp actual + ID factura
            $nuevoSecuencial = $this->generarSecuencialUnico($factura, $pedido);
            
            Log::info('Nuevo secuencial generado', [
                'secuencial_anterior' => $pedido->numero_orden ?? 'N/A',
                'secuencial_nuevo' => $nuevoSecuencial
            ]);
            
            // Actualizar temporalmente el número de orden del pedido
            $numeroOrdenOriginal = null;
            if (is_object($pedido) && isset($pedido->numero_orden)) {
                $numeroOrdenOriginal = $pedido->numero_orden;
                $pedido->numero_orden = $nuevoSecuencial;
            }
            
            // Preparar datos con nuevo secuencial
            $invoiceData = $this->prepararDatosFactura($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago);
            
            // Restaurar número de orden original
            if ($numeroOrdenOriginal !== null && is_object($pedido)) {
                $pedido->numero_orden = $numeroOrdenOriginal;
            }
            
            // Usar certificado del declarante
            $certificatePath = $declarante->ruta_certificado;
            
            if (!file_exists($certificatePath)) {
                throw new \Exception('Certificado del declarante no encontrado: ' . $certificatePath);
            }
            
            // Procesar con nuevo secuencial
            $resultado = $this->xmlSriService->procesarFacturaCompleta(
                $invoiceData, 
                $certificatePath, 
                $passwordCertificado
            );
            
            if (!$resultado['success']) {
                throw new \Exception('Fallo en reintento: ' . ($resultado['message'] ?? 'Error desconocido'));
            }
            
            $result = $resultado['result'];
            
            // Verificar que esta vez sí fue recibida
            if (isset($result['isReceived']) && !$result['isReceived']) {
                Log::error('Reintento también fue rechazado por el SRI', [
                    'factura_id' => $factura->id,
                    'nuevo_secuencial' => $nuevoSecuencial
                ]);
                
                // No hacer más reintentos para evitar bucle infinito
                throw new \Exception('SRI rechazó la factura incluso con nuevo secuencial. Revisar configuración.');
            }
            
            // Actualizar factura en base de datos
            $this->actualizarEstadoFactura($factura, $result);
            
            // Guardar XML firmado si está disponible
            if (isset($result['xmlFileSigned']) && !empty($result['xmlFileSigned'])) {
                $this->guardarXMLFirmado($factura, $result['xmlFileSigned']);
                Log::info('XML firmado guardado exitosamente desde reintento', [
                    'factura_id' => $factura->id,
                    'xml_length' => strlen($result['xmlFileSigned'])
                ]);
            }
            
            // Guardar XML autorizado si está disponible
            if (isset($result['xmlAutorizado']) && !empty($result['xmlAutorizado'])) {
                $this->guardarXMLAutorizado($factura, $result['xmlAutorizado']);
                Log::info('XML autorizado guardado exitosamente desde reintento');
            }
            
            Log::info('=== REINTENTO EXITOSO ===', [
                'clave_acceso' => $result['accessKey'] ?? 'NO_DISPONIBLE',
                'recibida' => $result['isReceived'] ?? false,
                'autorizada' => $result['isAuthorized'] ?? false
            ]);
            
            return [
                'success' => true,
                'factura' => $factura->fresh(),
                'clave_acceso' => $result['accessKey'] ?? null,
                'recibida' => $result['isReceived'] ?? false,
                'autorizada' => $result['isAuthorized'] ?? false,
                'xml_firmado' => $result['xmlFileSigned'] ?? null,
                'message' => 'Factura procesada exitosamente con nuevo secuencial (reintento automático)'
            ];
            
        } catch (\Exception $e) {
            Log::error('=== ERROR EN REINTENTO ===', [
                'error' => $e->getMessage(),
                'factura_id' => $factura->id
            ]);
            
            throw new \Exception('Error en reintento con nuevo secuencial: ' . $e->getMessage());
        }
    }
    
    /**
     * Generar secuencial único para evitar duplicados
     */
    private function generarSecuencialUnico($factura, $declarante, $pedido = null)
    {
        try {
            $ruc = $declarante->ruc ?? '9999999999999';
            $establecimiento = str_pad($declarante->establecimiento ?? '001', 3, '0', STR_PAD_LEFT);
            $puntoEmision = str_pad($declarante->punto_emision ?? '001', 3, '0', STR_PAD_LEFT);
            
            // Estrategia 1: Intentar usar número de orden del pedido si está disponible y no está en uso
            if (is_object($pedido) && isset($pedido->numero_orden) && !empty($pedido->numero_orden)) {
                $numerosExtraidos = preg_replace('/[^0-9]/', '', $pedido->numero_orden);
                if (!empty($numerosExtraidos) && strlen($numerosExtraidos) <= 9) {
                    $secuencialPedido = str_pad($numerosExtraidos, 9, '0', STR_PAD_LEFT);
                    
                    // Verificar si este secuencial no está en uso
                    if (!SecuencialSri::secuencialEnUso($secuencialPedido, $ruc, $establecimiento, $puntoEmision)) {
                        Log::info('Usando secuencial del pedido', [
                            'secuencial' => $secuencialPedido,
                            'numero_orden_original' => $pedido->numero_orden
                        ]);
                        return $secuencialPedido;
                    } else {
                        Log::warning('Secuencial del pedido ya está en uso, generando nuevo', [
                            'secuencial_ocupado' => $secuencialPedido,
                            'numero_orden' => $pedido->numero_orden
                        ]);
                    }
                }
            }
            
            // Estrategia 2: Generar próximo secuencial disponible automáticamente
            $secuencialUnico = SecuencialSri::generarProximoSecuencial($ruc, $establecimiento, $puntoEmision);
            
            Log::info('Secuencial único generado automáticamente', [
                'secuencial' => $secuencialUnico,
                'metodo' => 'proximo_disponible'
            ]);
            
            return $secuencialUnico;
            
        } catch (\Exception $e) {
            Log::error('Error generando secuencial único', [
                'error' => $e->getMessage(),
                'factura_id' => $factura->id
            ]);
            
            // Fallback: usar timestamp + ID factura como antes
            $timestamp = time();
            $facturaId = str_pad($factura->id, 3, '0', STR_PAD_LEFT);
            
            // Tomar los últimos 6 dígitos del timestamp + 3 dígitos del ID factura = 9 dígitos
            $secuencialFallback = substr($timestamp, -6) . $facturaId;
            
            // Asegurar que tenga exactamente 9 dígitos
            $secuencialFallback = str_pad($secuencialFallback, 9, '0', STR_PAD_LEFT);
            
            Log::warning('Usando secuencial fallback por error', [
                'secuencial_fallback' => $secuencialFallback,
                'error_original' => $e->getMessage()
            ]);
            
            return $secuencialFallback;
        }
    }
    
    /**
     * Registrar secuencial en base de datos para control de duplicados
     */
    private function registrarSecuencialEnBD($factura, $result)
    {
        try {
            // Extraer información necesaria
            $claveAcceso = $result['accessKey'] ?? null;
            if (!$claveAcceso) {
                Log::warning('No se puede registrar secuencial sin clave de acceso', [
                    'factura_id' => $factura->id
                ]);
                return;
            }
            
            // Extraer secuencial de la clave de acceso (posiciones 25-33, 9 dígitos)
            $secuencial = substr($claveAcceso, 24, 9);
            
            // Extraer otros datos de la clave de acceso
            $ruc = substr($claveAcceso, 10, 13);
            $establecimiento = substr($claveAcceso, 23, 3);
            $puntoEmision = substr($claveAcceso, 26, 3);
            
            // Verificar si ya está registrado
            $registroExistente = SecuencialSri::where('clave_acceso', $claveAcceso)->first();
            if ($registroExistente) {
                Log::info('Secuencial ya está registrado en BD', [
                    'clave_acceso' => $claveAcceso,
                    'registro_id' => $registroExistente->id
                ]);
                return;
            }
            
            // Determinar estado inicial
            $estado = 'USADO';
            if (isset($result['sriResponse']['estado'])) {
                switch ($result['sriResponse']['estado']) {
                    case 'AUTORIZADA':
                        $estado = 'AUTORIZADA';
                        break;
                    case 'DEVUELTA':
                        $estado = 'DEVUELTA';
                        break;
                    case 'RECIBIDA':
                        $estado = 'USADO';
                        break;
                }
            }
            
            // Preparar metadata
            $metadata = [
                'fecha_registro' => now()->toISOString(),
                'usuario_sistema' => 'Sistema Facturación OPTECU',
                'resultado_sri' => $result['sriResponse'] ?? null
            ];
            
            // Registrar en base de datos
            $registro = SecuencialSri::registrarSecuencial([
                'secuencial' => $secuencial,
                'clave_acceso' => $claveAcceso,
                'establecimiento' => $establecimiento,
                'punto_emision' => $puntoEmision,
                'ruc' => $ruc,
                'estado' => $estado,
                'factura_id' => $factura->id,
                'fecha_emision' => now()->toDateString(),
                'metadata' => $metadata
            ]);
            
            Log::info('Secuencial registrado exitosamente en BD', [
                'registro_id' => $registro->id,
                'secuencial' => $secuencial,
                'clave_acceso' => $claveAcceso,
                'estado' => $estado
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error registrando secuencial en BD', [
                'error' => $e->getMessage(),
                'factura_id' => $factura->id,
                'clave_acceso' => $result['accessKey'] ?? 'N/A'
            ]);
            // No lanzar excepción para que no afecte el flujo principal
        }
    }
}
