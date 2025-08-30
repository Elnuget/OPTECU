<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AutorizarController extends Controller
{
    /**
     * Mostrar la vista de autorización para una factura específica
     */
    public function index($facturaId)
    {
        try {
            // Buscar la factura por ID
            $factura = Factura::with('declarante')->findOrFail($facturaId);
            
            // Verificar que la factura existe
            if (!$factura) {
                return redirect()->route('facturas.index')
                    ->with('error', 'Factura no encontrada.');
            }
            
            // Log para debugging
            Log::info('Accediendo a vista de autorización', [
                'factura_id' => $facturaId,
                'estado' => $factura->estado,
                'declarante' => $factura->declarante->nombre ?? 'Sin declarante'
            ]);
            
            return view('autorizar.index', compact('factura'));
            
        } catch (\Exception $e) {
            Log::error('Error al acceder a vista de autorización', [
                'factura_id' => $facturaId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('facturas.index')
                ->with('error', 'Error al acceder a la vista de autorización: ' . $e->getMessage());
        }
    }

    /**
     * Consultar el estado de autorización de una factura en el SRI
     */
    public function consultarAutorizacion($facturaId)
    {
        try {
            // Buscar la factura por ID
            $factura = Factura::findOrFail($facturaId);
            
            // Verificar que la factura tiene clave de acceso
            if (!$factura->clave_acceso) {
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no tiene clave de acceso generada.'
                ], 400);
            }
            
            Log::info('Iniciando consulta de autorización SRI', [
                'factura_id' => $facturaId,
                'clave_acceso' => $factura->clave_acceso,
                'estado_actual' => $factura->estado
            ]);
            
            // Validar ambiente de pruebas
            $this->validarAmbientePruebas();
            
            // Consultar autorización en el SRI
            $resultado = $this->consultarSriAutorizacion($factura->clave_acceso);
            
            // Actualizar datos de la factura si se obtuvo respuesta
            if ($resultado['success']) {
                $this->actualizarFacturaConAutorizacion($factura, $resultado['data']);
            }
            
            return response()->json($resultado);
            
        } catch (\Exception $e) {
            Log::error('Error al consultar autorización SRI', [
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar autorización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar que estamos en ambiente de pruebas
     */
    private function validarAmbientePruebas()
    {
        $envPath = public_path('SriSignXml/.env');
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // Verificar que las URLs sean de pruebas (celcer)
            if (strpos($envContent, 'cel.sri.gob.ec') !== false && 
                strpos($envContent, 'celcer.sri.gob.ec') === false) {
                throw new Exception('ADVERTENCIA: Se detectaron URLs de PRODUCCIÓN. Se requiere ambiente de PRUEBAS.');
            }
        }
        
        Log::info('Ambiente de pruebas validado correctamente');
    }

    /**
     * Realizar consulta de autorización al SRI
     */
    private function consultarSriAutorizacion($claveAcceso)
    {
        try {
            // URL del servicio de autorización (PRUEBAS)
            $wsdlUrl = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
            
            Log::info('Consultando autorización SRI', [
                'url' => $wsdlUrl,
                'clave_acceso' => $claveAcceso
            ]);
            
            // Crear cliente SOAP
            $client = new \SoapClient($wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ])
            ]);
            
            // Parámetros de la consulta
            $params = [
                'claveAccesoComprobante' => $claveAcceso
            ];
            
            // Realizar la consulta
            $response = $client->autorizacionComprobante($params);
            
            Log::info('Respuesta SRI recibida', [
                'response' => json_encode($response, JSON_PRETTY_PRINT)
            ]);
            
            // Procesar respuesta
            return $this->procesarRespuestaSri($response);
            
        } catch (\SoapFault $e) {
            Log::error('Error SOAP al consultar SRI', [
                'error' => $e->getMessage(),
                'faultcode' => $e->faultcode ?? 'N/A',
                'faultstring' => $e->faultstring ?? 'N/A',
                'clave_acceso' => $claveAcceso
            ]);
            
            // Determinar tipo de error SOAP
            $errorMessage = 'Error de comunicación con el SRI';
            
            if (isset($e->faultcode)) {
                switch ($e->faultcode) {
                    case 'SOAP-ENV:Client':
                        $errorMessage = 'Error en la petición: datos inválidos o formato incorrecto';
                        break;
                    case 'SOAP-ENV:Server':
                        $errorMessage = 'Error en el servidor del SRI: servicio no disponible temporalmente';
                        break;
                    default:
                        $errorMessage = 'Error SOAP: ' . ($e->faultstring ?? $e->getMessage());
                }
            }
            
            throw new Exception($errorMessage);
            
        } catch (\Exception $e) {
            Log::error('Error general al consultar SRI', [
                'error' => $e->getMessage(),
                'clave_acceso' => $claveAcceso
            ]);
            
            throw $e;
        }
    }

    /**
     * Procesar respuesta del SRI
     */
    private function procesarRespuestaSri($response)
    {
        try {
            // Log de la respuesta completa para debugging
            Log::info('Respuesta completa del SRI', [
                'response' => json_encode($response, JSON_PRETTY_PRINT)
            ]);
            
            $autorizaciones = $response->RespuestaAutorizacionComprobante ?? null;
            
            if (!$autorizaciones) {
                throw new Exception('Respuesta inválida del SRI: no se encontraron autorizaciones');
            }
            
            // Verificar si existe la propiedad autorizacion o autorizaciones
            $autorizacion = null;
            
            if (isset($autorizaciones->autorizacion)) {
                $autorizacion = is_array($autorizaciones->autorizacion) 
                    ? $autorizaciones->autorizacion[0] 
                    : $autorizaciones->autorizacion;
            } elseif (isset($autorizaciones->autorizaciones)) {
                $autorizacionesData = $autorizaciones->autorizaciones;
                
                // Si la respuesta tiene 'autorizaciones', verificar si tiene contenido
                if (is_object($autorizacionesData) && property_exists($autorizacionesData, 'autorizacion')) {
                    $autorizacionInterna = $autorizacionesData->autorizacion;
                    $autorizacion = is_array($autorizacionInterna) ? $autorizacionInterna[0] : $autorizacionInterna;
                } elseif (is_array($autorizacionesData) && count($autorizacionesData) > 0) {
                    $autorizacion = $autorizacionesData[0];
                }
            } else {
                // Si no encontramos autorizacion, veamos qué propiedades tiene
                $propiedades = get_object_vars($autorizaciones);
                Log::info('Propiedades disponibles en autorizaciones', [
                    'propiedades' => array_keys($propiedades)
                ]);
                
                throw new Exception('No se encontró información de autorización en la respuesta del SRI. Propiedades disponibles: ' . implode(', ', array_keys($propiedades)));
            }
            
            if (!$autorizacion) {
                throw new Exception('No se encontró información de autorización en la respuesta del SRI');
            }
            
            // Extraer datos de la autorización
            $estado = $autorizacion->estado ?? 'DESCONOCIDO';
            $numeroAutorizacion = $autorizacion->numeroAutorizacion ?? null;
            $fechaAutorizacion = $autorizacion->fechaAutorizacion ?? null;
            $ambiente = $autorizacion->ambiente ?? null;
            $comprobante = $autorizacion->comprobante ?? null;
            
            // Log específico para facturas ya autorizadas
            if ($estado === 'AUTORIZADA' || $estado === 'AUTORIZADO') {
                Log::info('Factura ya autorizada detectada', [
                    'estado' => $estado,
                    'numeroAutorizacion' => $numeroAutorizacion,
                    'fechaAutorizacion' => $fechaAutorizacion,
                    'ambiente_original' => $ambiente,
                    'ambiente_procesado' => $ambiente === '1' ? 'PRUEBAS' : ($ambiente === '2' ? 'PRODUCCION' : $ambiente),
                    'tiene_comprobante' => !empty($comprobante)
                ]);
            }
            
            // Procesar mensajes
            $mensajes = [];
            if (isset($autorizacion->mensajes) && isset($autorizacion->mensajes->mensaje)) {
                $mensajesSri = is_array($autorizacion->mensajes->mensaje) 
                    ? $autorizacion->mensajes->mensaje 
                    : [$autorizacion->mensajes->mensaje];
                
                foreach ($mensajesSri as $mensaje) {
                    $mensajes[] = [
                        'identificador' => $mensaje->identificador ?? null,
                        'mensaje' => $mensaje->mensaje ?? 'Mensaje sin descripción',
                        'informacionAdicional' => $mensaje->informacionAdicional ?? null,
                        'tipo' => $mensaje->tipo ?? 'INFO'
                    ];
                }
            }
            
            // Para facturas ya autorizadas, agregar mensaje informativo si no hay mensajes
            if (($estado === 'AUTORIZADA' || $estado === 'AUTORIZADO') && empty($mensajes)) {
                $mensajes[] = [
                    'identificador' => 'AUTORIZADA_PREVIAMENTE',
                    'mensaje' => 'Factura autorizada correctamente por el SRI',
                    'informacionAdicional' => 'La factura se encuentra en estado autorizado en el sistema del SRI',
                    'tipo' => 'INFO'
                ];
            }
            
            // Si no hay autorizaciones pero hay mensajes de error, extraerlos
            if ($estado === 'DESCONOCIDO' && empty($mensajes)) {
                // Buscar mensajes en otros lugares de la respuesta
                if (isset($autorizaciones->mensajes)) {
                    $mensajesGenerales = is_array($autorizaciones->mensajes) 
                        ? $autorizaciones->mensajes 
                        : [$autorizaciones->mensajes];
                    
                    foreach ($mensajesGenerales as $mensaje) {
                        if (is_object($mensaje)) {
                            $mensajes[] = [
                                'identificador' => $mensaje->identificador ?? null,
                                'mensaje' => $mensaje->mensaje ?? 'Error en procesamiento',
                                'informacionAdicional' => $mensaje->informacionAdicional ?? null,
                                'tipo' => $mensaje->tipo ?? 'ERROR'
                            ];
                        }
                    }
                }
                
                // Si aún no hay mensajes, indicar que no se encontró la factura
                if (empty($mensajes)) {
                    $mensajes[] = [
                        'identificador' => 'NO_ENCONTRADA',
                        'mensaje' => 'No se encontró información de autorización para esta clave de acceso',
                        'informacionAdicional' => 'La factura puede no haber sido enviada al SRI o la clave de acceso no es válida',
                        'tipo' => 'WARNING'
                    ];
                    // Usar estado válido del ENUM: DEVUELTA para casos donde no se encuentra
                    $estado = 'DEVUELTA';
                }
            }
            
            $datosAutorizacion = [
                'estado' => $estado,
                'numeroAutorizacion' => $numeroAutorizacion,
                'fechaAutorizacion' => $fechaAutorizacion,
                'ambiente' => $ambiente, // Mantener el valor original del SRI
                'ambiente_texto' => $ambiente === '1' ? 'PRUEBAS' : ($ambiente === '2' ? 'PRODUCCION' : $ambiente), // Versión legible
                'comprobante' => $comprobante,
                'mensajes' => $mensajes
            ];
            
            // Log detallado para debugging
            Log::info('Autorización procesada con datos completos', [
                'estado' => $estado,
                'numeroAutorizacion' => $numeroAutorizacion ? 'Presente' : 'Ausente',
                'fechaAutorizacion' => $fechaAutorizacion ? 'Presente' : 'Ausente',
                'ambiente_original' => $ambiente,
                'ambiente_texto' => $ambiente === '1' ? 'PRUEBAS' : ($ambiente === '2' ? 'PRODUCCION' : $ambiente),
                'comprobante_length' => $comprobante ? strlen($comprobante) : 0,
                'mensajes_count' => count($mensajes)
            ]);
            
            return [
                'success' => true,
                'data' => $datosAutorizacion
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al procesar respuesta SRI', [
                'error' => $e->getMessage(),
                'response' => json_encode($response, JSON_PRETTY_PRINT)
            ]);
            
            throw new Exception('Error al procesar respuesta del SRI: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar factura con datos de autorización
     */
    private function actualizarFacturaConAutorizacion($factura, $datosAutorizacion)
    {
        try {
            // Mapear estados del SRI a estados válidos del ENUM
            $estadoSriValido = $this->mapearEstadoSriAEnum($datosAutorizacion['estado']);
            
            $updates = [
                'estado_sri' => $estadoSriValido,
                'mensajes_sri' => json_encode($datosAutorizacion['mensajes']),
                'updated_at' => now()
            ];
            
            // Si está autorizada, actualizar datos adicionales y estado principal
            if ($datosAutorizacion['estado'] === 'AUTORIZADA' || $datosAutorizacion['estado'] === 'AUTORIZADO') {
                $updates['estado'] = 'AUTORIZADA'; // Cambiar estado principal de la factura
                $updates['numero_autorizacion'] = $datosAutorizacion['numeroAutorizacion'];
                $updates['fecha_autorizacion'] = $datosAutorizacion['fechaAutorizacion'];
                
                // Guardar XML autorizado si está disponible
                if (!empty($datosAutorizacion['comprobante'])) {
                    $updates['xml_autorizado'] = $datosAutorizacion['comprobante'];
                    
                    Log::info('XML autorizado guardado', [
                        'factura_id' => $factura->id,
                        'xml_length' => strlen($datosAutorizacion['comprobante']),
                        'xml_preview' => substr($datosAutorizacion['comprobante'], 0, 200) . '...'
                    ]);
                }
                
                // Actualizar el campo 'fact' del pedido relacionado a 'facturado'
                if ($factura->pedido_id) {
                    $pedido = \App\Models\Pedido::find($factura->pedido_id);
                    if ($pedido) {
                        $estadoAnteriorPedido = $pedido->fact;
                        $pedido->fact = 'facturado';
                        $pedido->save();
                        
                        Log::info('Estado del pedido actualizado a facturado', [
                            'factura_id' => $factura->id,
                            'pedido_id' => $pedido->id,
                            'estado_anterior' => $estadoAnteriorPedido,
                            'estado_nuevo' => 'facturado'
                        ]);
                    } else {
                        Log::warning('No se encontró el pedido asociado a la factura autorizada', [
                            'factura_id' => $factura->id,
                            'pedido_id' => $factura->pedido_id
                        ]);
                    }
                } else {
                    Log::info('La factura autorizada no tiene pedido asociado', [
                        'factura_id' => $factura->id
                    ]);
                }
            }
            
            $factura->update($updates);
            
            Log::info('Factura actualizada con datos de autorización', [
                'factura_id' => $factura->id,
                'estado_original' => $factura->estado,
                'estado_nuevo' => $updates['estado'] ?? 'Sin cambio',
                'estado_sri_original' => $datosAutorizacion['estado'],
                'estado_sri_mapeado' => $estadoSriValido,
                'numero_autorizacion' => $datosAutorizacion['numeroAutorizacion'] ?? 'N/A',
                'xml_autorizado_guardado' => !empty($datosAutorizacion['comprobante'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar factura con autorización', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Mapear estados del SRI a valores válidos del ENUM estado_sri
     */
    private function mapearEstadoSriAEnum($estadoSri)
    {
        // Valores válidos del ENUM: 'RECIBIDA', 'DEVUELTA', 'AUTORIZADA', 'NO_AUTORIZADA'
        $mapeoEstados = [
            'AUTORIZADA' => 'AUTORIZADA',
            'AUTORIZADO' => 'AUTORIZADA',
            'NO_AUTORIZADA' => 'NO_AUTORIZADA',
            'NO_AUTORIZADO' => 'NO_AUTORIZADA',
            'DEVUELTA' => 'DEVUELTA',
            'DEVUELTO' => 'DEVUELTA',
            'RECIBIDA' => 'RECIBIDA',
            'RECIBIDO' => 'RECIBIDA',
            'EN_PROCESO' => 'RECIBIDA', // Estado temporal se mapea a RECIBIDA
            'PROCESANDO' => 'RECIBIDA',
            'DESCONOCIDO' => 'DEVUELTA', // Estados desconocidos se mapean a DEVUELTA
            'ERROR' => 'DEVUELTA'
        ];
        
        $estadoMapeado = $mapeoEstados[$estadoSri] ?? 'DEVUELTA';
        
        Log::info('Mapeando estado SRI', [
            'estado_original' => $estadoSri,
            'estado_mapeado' => $estadoMapeado
        ]);
        
        return $estadoMapeado;
    }
}
