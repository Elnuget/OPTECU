<?php

namespace App\Services;

use App\Models\Factura;
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
     * Procesar factura completa: generar XML, firmar y enviar al SRI
     */
    public function procesarFacturaCompleta($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago, $passwordCertificado)
    {
        try {
            Log::info('=== INICIO PROCESAMIENTO FACTURA CON API PYTHON ===', [
                'factura_id' => $factura->id
            ]);
            
            // Generar datos en formato requerido por el API Python
            $invoiceData = $this->prepararDatosFactura($factura, $declarante, $pedido, $elementos, $subtotal, $iva, $total, $medioPago);
            
            Log::info('Datos de factura preparados para procesamiento local', [
                'invoice_data_keys' => array_keys($invoiceData)
            ]);
            
            // Copiar certificado P12 del declarante al directorio del procesador
            $certificatePath = $this->copiarCertificadoP12($declarante, $passwordCertificado);
            
            // Procesar con servicio local en lugar de API HTTP
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
            
            // Actualizar factura en base de datos
            $this->actualizarEstadoFactura($factura, $result);
            
            // Guardar XML firmado si está disponible
            if (isset($result['xmlFileSigned']) && !empty($result['xmlFileSigned'])) {
                $this->guardarXMLFirmado($factura, $result['xmlFileSigned']);
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
        // Generar secuencial
        $secuencial = '000000001';
        if (is_object($pedido) && isset($pedido->numero_orden) && !empty($pedido->numero_orden)) {
            $numerosExtraidos = preg_replace('/[^0-9]/', '', $pedido->numero_orden);
            if (!empty($numerosExtraidos)) {
                $secuencial = str_pad($numerosExtraidos, 9, '0', STR_PAD_LEFT);
            }
        }
        
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
            'environment' => '1', // Pruebas
            'typeEmission' => '1', // Normal
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
                'percentageCode' => '6', // 15%
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
     * Copiar certificado P12 del declarante al directorio del API Python
     */
    private function copiarCertificadoP12($declarante, $passwordCertificado)
    {
        try {
            // Ruta del certificado del declarante
            $rutaCertificadoDeclarante = public_path('uploads/firmas/' . $declarante->firma);
            
            if (!file_exists($rutaCertificadoDeclarante)) {
                throw new \Exception('Certificado del declarante no encontrado: ' . $rutaCertificadoDeclarante);
            }
            
            // Ruta destino en el API Python
            $rutaDestinoP12 = public_path('SriSignXml/app/signature.p12');
            
            // Copiar certificado
            if (!copy($rutaCertificadoDeclarante, $rutaDestinoP12)) {
                throw new \Exception('No se pudo copiar el certificado al directorio del API Python');
            }
            
            // Crear archivo .env con la contraseña
            $envContent = "URL_RECEPTION=https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl\n";
            $envContent .= "URL_AUTHORIZATION=https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl\n";
            $envContent .= "PASSWORD=" . $passwordCertificado . "\n";
            
            $rutaEnv = public_path('SriSignXml/.env');
            file_put_contents($rutaEnv, $envContent);
            
            Log::info('Certificado P12 copiado exitosamente', [
                'origen' => $rutaCertificadoDeclarante,
                'destino' => $rutaDestinoP12
            ]);

            return $rutaDestinoP12;

        } catch (\Exception $e) {
            Log::error('Error copiando certificado P12', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }    /**
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
            } elseif (isset($result['isRejected']) && $result['isRejected']) {
                $factura->estado = 'DEVUELTA';
            } elseif (isset($result['accessKey'])) {
                $factura->estado = 'ENVIADA';
            } else {
                $factura->estado = 'FIRMADA';
            }
            
            // Actualizar fechas de proceso
            $factura->fecha_firma = now();
            if ($factura->estado !== 'FIRMADA') {
                $factura->fecha_envio_sri = now();
            }
            
            $factura->save();
            
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
     * Guardar XML firmado en el sistema de archivos
     */
    private function guardarXMLFirmado($factura, $xmlContent)
    {
        try {
            // Crear directorio si no existe
            $xmlPath = 'facturas/' . date('Y/m');
            $xmlFullPath = storage_path('app/public/' . $xmlPath);
            
            if (!is_dir($xmlFullPath)) {
                mkdir($xmlFullPath, 0755, true);
            }
            
            // Nombre del archivo XML firmado
            $xmlFileName = 'factura_firmada_' . ($factura->clave_acceso ?? $factura->id) . '_' . time() . '.xml';
            $xmlFilePath = $xmlFullPath . DIRECTORY_SEPARATOR . $xmlFileName;
            
            // Guardar XML firmado
            file_put_contents($xmlFilePath, $xmlContent);
            
            // Actualizar ruta en la factura
            $factura->xml_firmado = $xmlPath . '/' . $xmlFileName;
            $factura->save();
            
            Log::info('XML firmado guardado exitosamente', [
                'factura_id' => $factura->id,
                'ruta_xml' => $xmlFilePath
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error guardando XML firmado', [
                'error' => $e->getMessage()
            ]);
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
     * Generar mensaje de éxito según resultados
     */
    private function generarMensajeExito($result)
    {
        $mensaje = 'Factura procesada exitosamente. ';
        
        if (isset($result['isAuthorized']) && $result['isAuthorized']) {
            $mensaje .= 'XML firmado y AUTORIZADO por el SRI.';
        } elseif (isset($result['isReceived']) && $result['isReceived']) {
            $mensaje .= 'XML firmado y RECIBIDO por el SRI. Esperando autorización.';
        } else {
            $mensaje .= 'XML firmado correctamente.';
        }
        
        return $mensaje;
    }
}
