<?php

namespace App\Services\Factura;

use App\Models\Factura;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FirmaDigitalService
{
    /**
     * Firmar XML usando el servicio Python de firma digital
     *
     * @param string $xmlPath Ruta al archivo XML
     * @param object $declarante Declarante con certificado
     * @param string $password Contraseña del certificado
     * @return array
     */
    public function firmarXMLConPython($xmlPath, $declarante, $password)
    {
        try {
            Log::info('=== INICIO FIRMA XML CON PYTHON ===', [
                'xml_path' => $xmlPath,
                'declarante_id' => $declarante->id
            ]);

            // Verificar que existe el archivo XML
            if (!file_exists($xmlPath)) {
                throw new \Exception("Archivo XML no encontrado: $xmlPath");
            }

            // Obtener ruta del certificado P12
            $certificadoPath = $this->obtenerRutaCertificadoP12($declarante);
            
            if (!$certificadoPath || !file_exists($certificadoPath)) {
                throw new \Exception("Certificado P12 no encontrado para el declarante");
            }

            Log::info('Certificado P12 encontrado', [
                'certificado_path' => $certificadoPath,
                'certificado_existe' => file_exists($certificadoPath)
            ]);

            // Ejecutar servicio Python de firma
            $pythonScript = base_path('firma_service.py');
            $command = "python \"$pythonScript\" \"$certificadoPath\" \"$password\" \"$xmlPath\"";
            
            Log::info('Ejecutando comando Python', [
                'command' => $command
            ]);

            // Ejecutar comando y capturar salida
            $output = shell_exec($command . ' 2>&1');
            
            if (!$output) {
                throw new \Exception("No se recibió respuesta del servicio Python de firma");
            }

            Log::info('Respuesta del servicio Python', [
                'output_preview' => substr($output, 0, 500) . '...'
            ]);

            // Decodificar respuesta JSON
            $resultado = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decodificando JSON', [
                    'json_error' => json_last_error_msg(),
                    'output' => $output
                ]);
                throw new \Exception("Error decodificando respuesta JSON: " . json_last_error_msg());
            }

            if (!$resultado['success']) {
                throw new \Exception("Error en firma Python: " . ($resultado['error'] ?? 'Error desconocido'));
            }

            Log::info('XML firmado exitosamente con Python', [
                'certificate_subject' => $resultado['certificate_info']['subject'] ?? 'N/A',
                'signed_xml_length' => strlen($resultado['signed_xml'])
            ]);

            return [
                'success' => true,
                'signed_xml' => $resultado['signed_xml'],
                'certificate_info' => $resultado['certificate_info']
            ];

        } catch (\Exception $e) {
            Log::error('Error en firma con Python', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar XML firmado al SRI usando el servicio Python
     *
     * @param string $xmlFirmadoPath Ruta al archivo XML firmado
     * @return array
     */
    public function enviarXMLAlSRI($xmlFirmadoPath)
    {
        try {
            Log::info('=== INICIO ENVÍO AL SRI CON PYTHON ===', [
                'xml_firmado_path' => $xmlFirmadoPath
            ]);

            // Verificar que existe el archivo XML firmado
            if (!file_exists($xmlFirmadoPath)) {
                throw new \Exception("Archivo XML firmado no encontrado: $xmlFirmadoPath");
            }

            // Ejecutar servicio Python de envío al SRI
            $pythonScript = base_path('sri_service.py');
            $command = "python \"$pythonScript\" enviar \"$xmlFirmadoPath\"";
            
            Log::info('Ejecutando comando Python SRI', [
                'command' => $command
            ]);

            // Ejecutar comando y capturar salida
            $output = shell_exec($command . ' 2>&1');
            
            if (!$output) {
                throw new \Exception("No se recibió respuesta del servicio Python SRI");
            }

            Log::info('Respuesta del servicio Python SRI', [
                'output_preview' => substr($output, 0, 500) . '...'
            ]);

            // Decodificar respuesta JSON
            $resultado = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decodificando JSON SRI', [
                    'json_error' => json_last_error_msg(),
                    'output' => $output
                ]);
                throw new \Exception("Error decodificando respuesta JSON SRI: " . json_last_error_msg());
            }

            Log::info('Respuesta del SRI procesada', [
                'success' => $resultado['success'] ?? false,
                'estado' => $resultado['estado'] ?? 'DESCONOCIDO',
                'mensajes_count' => count($resultado['mensajes'] ?? [])
            ]);

            return $resultado;

        } catch (\Exception $e) {
            Log::error('Error enviando al SRI con Python', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'estado' => 'ERROR'
            ];
        }
    }

    /**
     * Autorizar comprobante en el SRI usando el servicio Python
     *
     * @param string $claveAcceso Clave de acceso del comprobante
     * @return array
     */
    public function autorizarComprobanteEnSRI($claveAcceso)
    {
        try {
            Log::info('=== INICIO AUTORIZACIÓN SRI CON PYTHON ===', [
                'clave_acceso' => $claveAcceso
            ]);

            // Ejecutar servicio Python de autorización
            $pythonScript = base_path('sri_service.py');
            $command = "python \"$pythonScript\" autorizar \"$claveAcceso\"";
            
            Log::info('Ejecutando comando Python autorización', [
                'command' => $command
            ]);

            // Ejecutar comando y capturar salida
            $output = shell_exec($command . ' 2>&1');
            
            if (!$output) {
                throw new \Exception("No se recibió respuesta del servicio Python autorización");
            }

            Log::info('Respuesta del servicio Python autorización', [
                'output_preview' => substr($output, 0, 500) . '...'
            ]);

            // Decodificar respuesta JSON
            $resultado = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decodificando JSON autorización', [
                    'json_error' => json_last_error_msg(),
                    'output' => $output
                ]);
                throw new \Exception("Error decodificando respuesta JSON autorización: " . json_last_error_msg());
            }

            Log::info('Respuesta de autorización procesada', [
                'success' => $resultado['success'] ?? false,
                'estado' => $resultado['estado'] ?? 'DESCONOCIDO',
                'numero_autorizacion' => $resultado['numero_autorizacion'] ?? 'N/A'
            ]);

            return $resultado;

        } catch (\Exception $e) {
            Log::error('Error autorizando en SRI con Python', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'estado' => 'ERROR'
            ];
        }
    }

    /**
     * Proceso completo: Firmar y enviar al SRI
     *
     * @param Factura $factura
     * @param string $password
     * @return array
     */
    public function firmarYEnviarCompleto(Factura $factura, $password)
    {
        try {
            Log::info('=== PROCESO COMPLETO FIRMA Y ENVÍO ===', [
                'factura_id' => $factura->id
            ]);

            // 1. Verificar que existe el XML original
            $xmlPath = storage_path('app/public/' . $factura->xml);
            if (!file_exists($xmlPath)) {
                throw new \Exception("XML original no encontrado");
            }

            // 2. Firmar XML
            $resultadoFirma = $this->firmarXMLConPython($xmlPath, $factura->declarante, $password);
            
            if (!$resultadoFirma['success']) {
                throw new \Exception("Error en firma: " . $resultadoFirma['error']);
            }

            // 3. Guardar XML firmado
            $xmlFirmadoPath = str_replace('.xml', '_firmado.xml', $xmlPath);
            file_put_contents($xmlFirmadoPath, $resultadoFirma['signed_xml']);

            // 4. Actualizar factura con estado FIRMADA
            $factura->estado = 'FIRMADA';
            $factura->xml_firmado = str_replace(storage_path('app/public/'), '', $xmlFirmadoPath);
            $factura->fecha_firma = now();
            $factura->save();

            Log::info('XML firmado y guardado', [
                'xml_firmado_path' => $xmlFirmadoPath,
                'factura_estado' => $factura->estado
            ]);

            // 5. Enviar al SRI
            $resultadoEnvio = $this->enviarXMLAlSRI($xmlFirmadoPath);

            // 6. Actualizar factura según resultado del envío
            $factura->fecha_envio_sri = now();
            
            if ($resultadoEnvio['success']) {
                $factura->estado = 'ENVIADA';
                $factura->clave_acceso = $resultadoEnvio['clave_acceso'] ?? null;
            } else {
                $factura->estado = 'ERROR_ENVIO';
            }

            // Guardar mensajes del SRI
            if (isset($resultadoEnvio['mensajes'])) {
                $factura->mensajes_sri = json_encode($resultadoEnvio['mensajes']);
            }

            $factura->save();

            Log::info('Proceso completo finalizado', [
                'factura_estado_final' => $factura->estado,
                'envio_exitoso' => $resultadoEnvio['success']
            ]);

            return [
                'success' => $resultadoEnvio['success'],
                'estado' => $resultadoEnvio['estado'],
                'factura' => $factura,
                'resultado_firma' => $resultadoFirma,
                'resultado_envio' => $resultadoEnvio
            ];

        } catch (\Exception $e) {
            // Actualizar factura con error
            if (isset($factura)) {
                $factura->estado = 'ERROR';
                $factura->save();
            }

            Log::error('Error en proceso completo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'estado' => 'ERROR'
            ];
        }
    }

    /**
     * Obtener ruta del certificado P12 del declarante
     *
     * @param object $declarante
     * @return string|null
     */
    private function obtenerRutaCertificadoP12($declarante)
    {
        Log::info('Obteniendo ruta del certificado P12', [
            'declarante_id' => $declarante->id,
            'firma' => $declarante->firma
        ]);

        if (empty($declarante->firma)) {
            Log::warning('Declarante no tiene archivo de firma configurado', [
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
        
        // Verificar que el archivo tenga extensión .p12
        $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
        if ($extension !== 'p12') {
            Log::warning('El archivo de firma no tiene extensión .p12', [
                'extension' => $extension,
                'ruta_original' => $rutaCompleta
            ]);
            // Buscar archivo con extensión .p12
            $rutaCompleta = str_replace('.' . $extension, '.p12', $rutaCompleta);
        }
        
        Log::info('Ruta del certificado P12 calculada', [
            'ruta_base' => $rutaBase,
            'ruta_completa' => $rutaCompleta,
            'directorio_existe' => is_dir($rutaBase),
            'archivo_existe' => file_exists($rutaCompleta)
        ]);
        
        // Crear directorio si no existe
        if (!is_dir($rutaBase)) {
            Log::warning('Directorio de firmas no existe, creando...', ['ruta' => $rutaBase]);
            mkdir($rutaBase, 0755, true);
        }
        
        return $rutaCompleta;
    }
}
