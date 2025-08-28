<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class XmlSriService
{
    private $pythonScriptPath;
    private $certificatePath;
    
    public function __construct()
    {
        $this->pythonScriptPath = base_path(); // Raíz del proyecto donde está sri_service.py
        $this->certificatePath = public_path('SriSignXml/app');
    }
    
    /**
     * Procesar factura completa: generar XML, firmar y enviar al SRI
     */
    public function procesarFacturaCompleta($invoiceData, $certificatePath, $password)
    {
        try {
            Log::info('=== INICIO PROCESAMIENTO XML SRI ===');
            
            // 1. Generar archivo JSON temporal con los datos de la factura
            $tempFile = tempnam(sys_get_temp_dir(), 'invoice_') . '.json';
            $jsonData = json_encode($invoiceData, JSON_PRETTY_PRINT);
            
            // Log para depuración
            Log::info('Datos JSON que se enviarán a Python', [
                'temp_file' => $tempFile,
                'json_size' => strlen($jsonData),
                'json_preview' => substr($jsonData, 0, 500) . '...'
            ]);
            
            file_put_contents($tempFile, $jsonData);
            
            // Verificar que el archivo se escribió correctamente
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Error escribiendo archivo temporal JSON');
            }
            
            // 2. Crear comando Python para procesar la factura
            $pythonCommand = $this->buildPythonCommand($tempFile, $certificatePath, $password);
            
            Log::info('Ejecutando comando Python', ['command' => $pythonCommand]);
            
            // 3. Ejecutar el comando Python capturando STDERR también
            $output = [];
            $returnCode = 0;
            
            // Agregar 2>&1 para capturar tanto stdout como stderr
            $commandWithError = $pythonCommand . ' 2>&1';
            exec($commandWithError, $output, $returnCode);
            
            Log::info('Resultado ejecución Python', [
                'return_code' => $returnCode,
                'output_lines' => count($output),
                'output' => $output
            ]);
            
            // 4. Limpiar archivo temporal
            @unlink($tempFile);
            
            // 5. Procesar resultado
            if ($returnCode === 0) {
                return $this->processSuccessResult($output);
            } else {
                return $this->processErrorResult($output, $returnCode);
            }
            
        } catch (\Exception $e) {
            Log::error('Error en procesamiento XML SRI', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Construir comando Python
     */
    private function buildPythonCommand($invoiceFile, $certificatePath, $password)
    {
        // Usar el script optimizado sri_processor.py
        $pythonScript = public_path('SriSignXml/sri_processor.py');
        
        // Verificar que el script existe
        if (!file_exists($pythonScript)) {
            throw new \Exception('Script optimizado sri_processor.py no encontrado: ' . $pythonScript);
        }
        
        // Escapar parámetros para Windows
        $escapedInvoiceFile = escapeshellarg($invoiceFile);
        $escapedCertPath = escapeshellarg($certificatePath);
        $escapedPassword = escapeshellarg($password);
        
        return "python \"{$pythonScript}\" procesar {$escapedInvoiceFile} {$escapedCertPath} {$escapedPassword}";
    }
    
    /**
     * Procesar resultado exitoso
     */
    private function processSuccessResult($output)
    {
        try {
            Log::info('Procesando resultado Python', ['output_lines' => count($output)]);
            
            // Unir todas las líneas de salida
            $jsonOutput = implode("\n", $output);
            
            // Verificar si hay salida JSON válida
            if (empty($jsonOutput)) {
                Log::warning('Salida Python vacía');
                return [
                    'success' => false,
                    'message' => 'Respuesta vacía del script Python'
                ];
            }

            // Intentar parsear el JSON directamente
            $result = json_decode($jsonOutput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Error parseando JSON completo, intentando limpieza', [
                    'json_error' => json_last_error_msg(),
                    'first_500_chars' => substr($jsonOutput, 0, 500)
                ]);
                
                // Buscar el JSON válido en el output
                $cleanJsonOutput = '';
                $jsonStartFound = false;
                $braceLevel = 0;
                $jsonLines = [];
                
                foreach ($output as $line) {
                    // Detectar el inicio del JSON principal - puede ser success true o false
                    if (!$jsonStartFound && (strpos($line, '"success": true') !== false || strpos($line, '"success": false') !== false)) {
                        $jsonStartFound = true;
                        // Buscar hacia atrás para encontrar la línea que empieza con {
                        for ($i = count($jsonLines); $i >= 0; $i--) {
                            if ($i < count($jsonLines) && trim($jsonLines[$i]) === '{') {
                                $jsonLines = array_slice($jsonLines, $i);
                                break;
                            }
                        }
                        if (empty($jsonLines) || trim($jsonLines[0]) !== '{') {
                            $jsonLines = ['{'];
                        }
                    }
                    
                    if ($jsonStartFound) {
                        $jsonLines[] = $line;
                        
                        // Contar llaves para detectar el final del JSON
                        $braceLevel += substr_count($line, '{') - substr_count($line, '}');
                        
                        if ($braceLevel <= 0 && strpos($line, '}') !== false) {
                            break;
                        }
                    } else {
                        $jsonLines[] = $line;
                    }
                }
                
                $cleanJsonOutput = implode("\n", $jsonLines);
                
                Log::info('JSON procesado para parsing', [
                    'json_lines_count' => count($jsonLines),
                    'json_preview' => substr($cleanJsonOutput, 0, 500)
                ]);
                
                $result = json_decode($cleanJsonOutput, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Error parseando JSON Python', [
                        'json_error' => json_last_error_msg(),
                        'first_1000_chars' => substr($cleanJsonOutput, 0, 1000)
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'Error parseando respuesta JSON: ' . json_last_error_msg()
                    ];
                }
            }

            if ($result && isset($result['success'])) {
                if ($result['success']) {
                    Log::info('Procesamiento exitoso', [
                        'tiene_datos' => isset($result['data']),
                        'tiene_xml_firmado' => isset($result['xmlFileSigned']),
                        'clave_acceso' => $result['accessKey'] ?? null
                    ]);
                    
                    // Asegurar que el resultado incluya el XML firmado
                    return [
                        'success' => true,
                        'result' => [
                            'accessKey' => $result['accessKey'] ?? null,
                            'xmlFileSigned' => $result['xmlFileSigned'] ?? null,
                            'isReceived' => $result['isReceived'] ?? false,
                            'isAuthorized' => $result['isAuthorized'] ?? false,
                            'sriResponse' => $result['sriResponse'] ?? null,
                            'xmlAutorizado' => $result['xmlAutorizado'] ?? null,
                            'data' => $result['data'] ?? null
                        ]
                    ];
                } else {
                    // success: false pero respuesta válida del SRI (ej: NO AUTORIZADO)
                    Log::info('Respuesta válida del SRI pero no autorizada', [
                        'estado' => $result['estado'] ?? 'desconocido',
                        'tiene_mensajes' => isset($result['mensajes'])
                    ]);
                    
                    return [
                        'success' => true, // La comunicación fue exitosa
                        'result' => [
                            'isAuthorized' => false,
                            'estado' => $result['estado'] ?? 'NO_AUTORIZADO',
                            'mensajes' => $result['mensajes'] ?? [],
                            'numeroAutorizacion' => $result['numero_autorizacion'] ?? null,
                            'fechaAutorizacion' => $result['fecha_autorizacion'] ?? null,
                            'xmlAutorizado' => $result['comprobante'] ?? null,
                            'respuestaCompleta' => $result['respuesta_completa'] ?? null
                        ]
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Error desconocido en el procesamiento',
                    'error' => $result['error'] ?? null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error procesando resultado Python', [
                'error' => $e->getMessage(),
                'output_lines' => count($output)
            ]);
            return [
                'success' => false,
                'message' => 'Error procesando respuesta: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Procesar resultado con error
     */
    private function processErrorResult($output, $returnCode)
    {
        $errorMessage = implode("\n", $output);
        Log::error('Error en script Python', [
            'return_code' => $returnCode,
            'output' => $errorMessage
        ]);
        
        return [
            'success' => false,
            'message' => 'Error en procesamiento: ' . $errorMessage
        ];
    }
    
    /**
     * Consultar estado de autorización de una factura
     */
    public function consultarEstadoAutorizacion($claveAcceso)
    {
        try {
            Log::info('=== CONSULTANDO ESTADO AUTORIZACIÓN ===', ['clave_acceso' => $claveAcceso]);
            
            // Usar el script optimizado sri_processor.py
            $pythonScript = public_path('SriSignXml/sri_processor.py');
            
            if (!file_exists($pythonScript)) {
                throw new \Exception('Script optimizado sri_processor.py no encontrado: ' . $pythonScript);
            }
            
            $escapedClaveAcceso = escapeshellarg($claveAcceso);
            $command = "python \"{$pythonScript}\" consultar {$escapedClaveAcceso}";
            
            Log::info('Ejecutando consulta de estado', ['command' => $command]);
            
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);
            
            if ($returnCode === 0) {
                return $this->processSuccessResult($output);
            } else {
                return $this->processErrorResult($output, $returnCode);
            }
            
        } catch (\Exception $e) {
            Log::error('Error consultando estado de autorización', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error consultando estado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Solicitar autorización de una factura ya enviada al SRI
     */
    public function solicitarAutorizacion($claveAcceso)
    {
        try {
            Log::info('=== SOLICITANDO AUTORIZACIÓN SRI ===', ['clave_acceso' => $claveAcceso]);
            
            $pythonScript = $this->pythonScriptPath . DIRECTORY_SEPARATOR . 'sri_service.py';
            
            if (!file_exists($pythonScript)) {
                throw new \Exception('Script de Python no encontrado: ' . $pythonScript);
            }
            
            $escapedClaveAcceso = escapeshellarg($claveAcceso);
            $command = "python \"{$pythonScript}\" autorizar {$escapedClaveAcceso}";
            
            Log::info('Ejecutando solicitud de autorización', ['command' => $command]);
            
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);
            
            if ($returnCode === 0) {
                $resultado = $this->processSuccessResult($output);
                
                // Si obtuvo la autorización correctamente, incluir información adicional
                if ($resultado['success'] && isset($resultado['data']['numeroAutorizacion'])) {
                    $resultado['data']['fechaAutorizacion'] = now()->toISOString();
                    $resultado['data']['estadoAutorizacion'] = 'AUTORIZADA';
                }
                
                return $resultado;
            } else {
                return $this->processErrorResult($output, $returnCode);
            }
            
        } catch (\Exception $e) {
            Log::error('Error solicitando autorización', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error solicitando autorización: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
