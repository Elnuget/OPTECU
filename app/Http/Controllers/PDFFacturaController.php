<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;

class PDFFacturaController extends Controller
{
    /**
     * Mostrar la vista PDF de una factura
     */
    public function show($id)
    {
        try {
            $factura = Factura::with(['declarante', 'pedido'])->findOrFail($id);
            
            // Obtener el XML más apropiado según el estado
            $xmlContent = $factura->getXmlContent();
            $xmlType = $factura->getXmlType();
            
            // Parsear el XML para obtener datos estructurados de la factura
            $datosFactura = $this->parsearXMLFactura($xmlContent);
            
            // Datos adicionales del modelo
            $datosFactura['modelo'] = [
                'id' => $factura->id,
                'fecha_creacion' => $factura->created_at ? $factura->created_at->format('d/m/Y H:i:s') : 'N/A',
                'estado_actual' => $factura->estado ?? 'CREADA',
                'estado_sri' => $factura->estado_sri,
                'numero_autorizacion' => $factura->numero_autorizacion,
                'fecha_autorizacion' => $factura->fecha_autorizacion ? 
                    (is_object($factura->fecha_autorizacion) ? 
                        $factura->fecha_autorizacion->format('d/m/Y H:i:s') : 
                        $factura->fecha_autorizacion) : null,
                'xml_type' => $xmlType,
                'monto' => $factura->monto,
                'iva' => $factura->iva,
                'total' => $factura->total
            ];
            
            return view('facturas.pdf', compact('factura', 'xmlContent', 'datosFactura'));
            
        } catch (\Exception $e) {
            // Para vistas públicas, mostrar error más genérico
            return response()->view('errors.factura_no_disponible', [
                'mensaje' => 'La factura no está disponible en este momento.'
            ], 404);
        }
    }
    
    /**
     * Parsear XML de factura para extraer datos estructurados
     */
    private function parsearXMLFactura($xmlContent)
    {
        if (empty($xmlContent)) {
            return $this->datosVacios();
        }
        
        try {
            // Limpiar el XML de caracteres especiales si es necesario
            $xmlContent = trim($xmlContent);
            
            // Cargar el XML
            $xml = simplexml_load_string($xmlContent);
            
            if ($xml === false) {
                return $this->datosVacios();
            }
            
            // Extraer información básica
            $datosFactura = [
                'comprobante' => $this->extraerDatosComprobante($xml),
                'emisor' => $this->extraerDatosEmisor($xml),
                'comprador' => $this->extraerDatosComprador($xml),
                'detalles' => $this->extraerDetallesFactura($xml),
                'totales' => $this->extraerTotales($xml),
                'infoTributaria' => $this->extraerInfoTributaria($xml),
                'pagos' => $this->extraerFormasPago($xml),
                'impuestos' => $this->extraerImpuestos($xml)
            ];
            
            return $datosFactura;
            
        } catch (\Exception $e) {
            // En caso de error, devolver estructura vacía
            return $this->datosVacios();
        }
    }
    
    /**
     * Extraer datos básicos del comprobante
     */
    private function extraerDatosComprobante($xml)
    {
        $infoFactura = $xml->infoFactura ?? null;
        
        return [
            'numero' => (string)($xml->infoTributaria->secuencial ?? ''),
            'fecha_emision' => (string)($infoFactura->fechaEmision ?? ''),
            'clave_acceso' => (string)($xml->infoTributaria->claveAcceso ?? ''),
            'ambiente' => (string)($xml->infoTributaria->ambiente ?? ''),
            'tipo_emision' => (string)($xml->infoTributaria->tipoEmision ?? ''),
            'establecimiento' => (string)($xml->infoTributaria->estab ?? ''),
            'punto_emision' => (string)($xml->infoTributaria->ptoEmi ?? ''),
            'secuencial' => (string)($xml->infoTributaria->secuencial ?? ''),
            'tipo_comprobante' => (string)($xml->infoTributaria->codDoc ?? ''),
            'direccion_matriz' => (string)($infoFactura->dirMatriz ?? ''),
            'contribuyente_especial' => (string)($infoFactura->contribuyenteEspecial ?? ''),
            'obligado_contabilidad' => (string)($infoFactura->obligadoContabilidad ?? ''),
            'guia_remision' => (string)($infoFactura->guiaRemision ?? '')
        ];
    }
    
    /**
     * Extraer datos del emisor
     */
    private function extraerDatosEmisor($xml)
    {
        $infoTributaria = $xml->infoTributaria ?? null;
        $infoFactura = $xml->infoFactura ?? null;
        
        return [
            'ruc' => (string)($infoTributaria->ruc ?? ''),
            'razon_social' => (string)($infoTributaria->razonSocial ?? ''),
            'nombre_comercial' => (string)($infoTributaria->nombreComercial ?? ''),
            'direccion' => (string)($infoFactura->dirEstablecimiento ?? ''),
            'email' => '',
            'telefono' => ''
        ];
    }
    
    /**
     * Extraer datos del comprador
     */
    private function extraerDatosComprador($xml)
    {
        $infoFactura = $xml->infoFactura ?? null;
        $tipoIdentificacion = (string)($infoFactura->tipoIdentificacionComprador ?? '');
        
        return [
            'identificacion' => (string)($infoFactura->identificacionComprador ?? ''),
            'tipo_identificacion' => $tipoIdentificacion,
            'tipo_identificacion_descripcion' => $this->obtenerDescripcionTipoIdentificacion($tipoIdentificacion),
            'razon_social' => (string)($infoFactura->razonSocialComprador ?? ''),
            'direccion' => (string)($infoFactura->direccionComprador ?? ''),
            'email' => (string)($infoFactura->email ?? ''),
            'telefono' => (string)($infoFactura->telefono ?? '')
        ];
    }
    
    /**
     * Extraer detalles de la factura
     */
    private function extraerDetallesFactura($xml)
    {
        $detalles = [];
        
        if (isset($xml->detalles) && isset($xml->detalles->detalle)) {
            foreach ($xml->detalles->detalle as $detalle) {
                $detalles[] = [
                    'codigo_principal' => (string)($detalle->codigoPrincipal ?? ''),
                    'codigo_auxiliar' => (string)($detalle->codigoAuxiliar ?? ''),
                    'descripcion' => (string)($detalle->descripcion ?? ''),
                    'cantidad' => (float)($detalle->cantidad ?? 0),
                    'precio_unitario' => (float)($detalle->precioUnitario ?? 0),
                    'descuento' => (float)($detalle->descuento ?? 0),
                    'precio_total_sin_impuesto' => (float)($detalle->precioTotalSinImpuesto ?? 0),
                    'impuestos' => $this->extraerImpuestosDetalle($detalle)
                ];
            }
        }
        
        return $detalles;
    }
    
    /**
     * Extraer impuestos de un detalle específico
     */
    private function extraerImpuestosDetalle($detalle)
    {
        $impuestos = [];
        
        if (isset($detalle->impuestos) && isset($detalle->impuestos->impuesto)) {
            foreach ($detalle->impuestos->impuesto as $impuesto) {
                $impuestos[] = [
                    'codigo' => (string)($impuesto->codigo ?? ''),
                    'codigo_porcentaje' => (string)($impuesto->codigoPorcentaje ?? ''),
                    'tarifa' => (float)($impuesto->tarifa ?? 0),
                    'base_imponible' => (float)($impuesto->baseImponible ?? 0),
                    'valor' => (float)($impuesto->valor ?? 0)
                ];
            }
        }
        
        return $impuestos;
    }
    
    /**
     * Extraer totales de la factura
     */
    private function extraerTotales($xml)
    {
        $infoFactura = $xml->infoFactura ?? null;
        
        // Calcular subtotales por tipo de IVA
        $subtotales = $this->calcularSubtotalesPorIVA($xml);
        
        return [
            'total_sin_impuestos' => (float)($infoFactura->totalSinImpuestos ?? 0),
            'total_descuento' => (float)($infoFactura->totalDescuento ?? 0),
            'importe_total' => (float)($infoFactura->importeTotal ?? 0),
            'moneda' => (string)($infoFactura->moneda ?? 'DOLAR'),
            'propina' => (float)($infoFactura->propina ?? 0),
            'subtotal_15' => $subtotales['subtotal_15'],
            'subtotal_0' => $subtotales['subtotal_0'],
            'subtotal_sin_impuesto' => $subtotales['subtotal_sin_impuesto']
        ];
    }
    
    /**
     * Calcular subtotales por tipo de IVA basándose en los detalles
     */
    private function calcularSubtotalesPorIVA($xml)
    {
        $subtotal_15 = 0;
        $subtotal_0 = 0;
        $subtotal_sin_impuesto = 0;
        
        if (isset($xml->detalles) && isset($xml->detalles->detalle)) {
            foreach ($xml->detalles->detalle as $detalle) {
                $precioTotalSinImpuesto = (float)($detalle->precioTotalSinImpuesto ?? 0);
                
                // Revisar los impuestos del detalle para determinar el tipo de IVA
                if (isset($detalle->impuestos) && isset($detalle->impuestos->impuesto)) {
                    foreach ($detalle->impuestos->impuesto as $impuesto) {
                        $codigoPorcentaje = (string)($impuesto->codigoPorcentaje ?? '');
                        $tarifa = (float)($impuesto->tarifa ?? 0);
                        
                        // Código porcentaje 4 = IVA 15%
                        if ($codigoPorcentaje == '4' || $tarifa == 15) {
                            $subtotal_15 += $precioTotalSinImpuesto;
                        }
                        // Código porcentaje 0 = IVA 0%
                        elseif ($codigoPorcentaje == '0' || $tarifa == 0) {
                            $subtotal_0 += $precioTotalSinImpuesto;
                        }
                        // Otros casos (sin impuesto)
                        else {
                            $subtotal_sin_impuesto += $precioTotalSinImpuesto;
                        }
                    }
                } else {
                    // Si no tiene impuestos definidos, asumir sin impuesto
                    $subtotal_sin_impuesto += $precioTotalSinImpuesto;
                }
            }
        }
        
        return [
            'subtotal_15' => $subtotal_15,
            'subtotal_0' => $subtotal_0,
            'subtotal_sin_impuesto' => $subtotal_sin_impuesto
        ];
    }
    
    /**
     * Extraer información tributaria
     */
    private function extraerInfoTributaria($xml)
    {
        $infoTributaria = $xml->infoTributaria ?? null;
        
        return [
            'ambiente' => (string)($infoTributaria->ambiente ?? ''),
            'tipo_emision' => (string)($infoTributaria->tipoEmision ?? ''),
            'razon_social' => (string)($infoTributaria->razonSocial ?? ''),
            'nombre_comercial' => (string)($infoTributaria->nombreComercial ?? ''),
            'ruc' => (string)($infoTributaria->ruc ?? ''),
            'clave_acceso' => (string)($infoTributaria->claveAcceso ?? ''),
            'codigo_documento' => (string)($infoTributaria->codDoc ?? ''),
            'establecimiento' => (string)($infoTributaria->estab ?? ''),
            'punto_emision' => (string)($infoTributaria->ptoEmi ?? ''),
            'secuencial' => (string)($infoTributaria->secuencial ?? ''),
            'direccion_matriz' => (string)($xml->infoFactura->dirMatriz ?? '')
        ];
    }
    
    /**
     * Extraer formas de pago
     */
    private function extraerFormasPago($xml)
    {
        $pagos = [];
        
        if (isset($xml->infoFactura->pagos) && isset($xml->infoFactura->pagos->pago)) {
            foreach ($xml->infoFactura->pagos->pago as $pago) {
                $codigoPago = (string)($pago->formaPago ?? '');
                $pagos[] = [
                    'forma_pago' => $codigoPago,
                    'forma_pago_descripcion' => $this->obtenerDescripcionFormaPago($codigoPago),
                    'total' => (float)($pago->total ?? 0),
                    'plazo' => (string)($pago->plazo ?? ''),
                    'unidad_tiempo' => (string)($pago->unidadTiempo ?? '')
                ];
            }
        }
        
        return $pagos;
    }
    
    /**
     * Extraer resumen de impuestos
     */
    private function extraerImpuestos($xml)
    {
        $impuestos = [];
        
        if (isset($xml->infoFactura->totalConImpuestos) && isset($xml->infoFactura->totalConImpuestos->totalImpuesto)) {
            foreach ($xml->infoFactura->totalConImpuestos->totalImpuesto as $impuesto) {
                $impuestos[] = [
                    'codigo' => (string)($impuesto->codigo ?? ''),
                    'codigo_porcentaje' => (string)($impuesto->codigoPorcentaje ?? ''),
                    'descuento_adicional' => (float)($impuesto->descuentoAdicional ?? 0),
                    'base_imponible' => (float)($impuesto->baseImponible ?? 0),
                    'tarifa' => (float)($impuesto->tarifa ?? 0),
                    'valor' => (float)($impuesto->valor ?? 0)
                ];
            }
        }
        
        return $impuestos;
    }
    
    /**
     * Obtener descripción de forma de pago según código SRI
     */
    private function obtenerDescripcionFormaPago($codigo)
    {
        $formasPago = [
            '01' => 'SIN UTILIZACIÓN DEL SISTEMA FINANCIERO',
            '15' => 'COMPENSACIÓN DE DEUDAS',
            '16' => 'TARJETA DE DÉBITO',
            '17' => 'DINERO ELECTRÓNICO',
            '18' => 'TARJETA PREPAGO',
            '19' => 'TARJETA DE CRÉDITO',
            '20' => 'OTROS CON UTILIZACIÓN DEL SISTEMA FINANCIERO',
            '21' => 'ENDOSO DE TÍTULOS'
        ];
        
        return $formasPago[$codigo] ?? "Forma de pago código {$codigo}";
    }
    
    /**
     * Obtener descripción del tipo de identificación según código SRI
     */
    private function obtenerDescripcionTipoIdentificacion($codigo)
    {
        $tiposIdentificacion = [
            '04' => 'RUC',
            '05' => 'CÉDULA',
            '06' => 'PASAPORTE',
            '07' => 'VENTA A CONSUMIDOR FINAL',
            '08' => 'IDENTIFICACIÓN DEL EXTERIOR'
        ];
        
        return $tiposIdentificacion[$codigo] ?? "Tipo {$codigo}";
    }
    
    /**
     * Retornar estructura de datos vacía
     */
    private function datosVacios()
    {
        return [
            'comprobante' => [],
            'emisor' => [],
            'comprador' => [],
            'detalles' => [],
            'totales' => [],
            'infoTributaria' => [],
            'pagos' => [],
            'impuestos' => []
        ];
    }
    
    /**
     * Generar y descargar PDF de la factura
     */
    public function download($id)
    {
        try {
            $factura = Factura::with(['declarante', 'pedido'])->findOrFail($id);
            
            // Aquí puedes implementar la lógica para generar un PDF real
            // Por ejemplo usando DomPDF o TCPDF
            
            // Por ahora, redireccionar a la vista con parámetro de impresión
            return redirect()->route('facturas.pdf', $id)->with('auto_print', true);
            
        } catch (\Exception $e) {
            return response()->view('errors.factura_no_disponible', [
                'mensaje' => 'Error al generar el PDF: La factura no está disponible.'
            ], 404);
        }
    }
}
