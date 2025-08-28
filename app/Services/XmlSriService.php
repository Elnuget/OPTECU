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
     *         } catch (\Exception $e) {
            Log::error('Error solicitando autorización', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error ejecutando comando: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar el estado de autorización de un comprobante
     */
    public function verificarAutorizacion($claveAcceso)
    {
        try {
            Log::info('=== VERIFICANDO ESTADO AUTORIZACIÓN ===', ['clave_acceso' => $claveAcceso]);
            
            $pythonScript = public_path('SriSignXml/sri_processor.py');
            $command = "python \"{$pythonScript}\" verificar_autorizacion \"{$claveAcceso}\"";
            
            Log::info('Ejecutando comando verificación', ['command' => $command]);
            
            exec($command . ' 2>&1', $output, $returnCode);
            
            Log::info('Resultado verificación Python', [
                'return_code' => $returnCode,
                'output_lines' => count($output),
                'output' => $output
            ]);
            
            if ($returnCode !== 0) {
                return [
                    'success' => false,
                    'message' => 'Error ejecutando verificación de autorización',
                    'output' => implode("\n", $output)
                ];
            }
            
            // Extraer JSON del output
            $jsonOutput = $this->extractValidJsonFromOutput($output);
            
            if (!$jsonOutput) {
                Log::warning('No se pudo extraer JSON válido de verificación autorización');
                return [
                    'success' => false,
                    'message' => 'No se pudo extraer respuesta válida'
                ];
            }
            
            $result = json_decode($jsonOutput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Error parseando respuesta JSON: ' . json_last_error_msg()
                ];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error en verificación de autorización', [
                'error' => $e->getMessage(),
                'clave_acceso' => $claveAcceso
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al verificar autorización: ' . $e->getMessage()
            ];
        }
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
                
                // Nueva lógica mejorada para extraer JSON válido
                $cleanJsonOutput = $this->extractValidJsonFromOutput($output);
                
                if ($cleanJsonOutput === null) {
                    Log::error('No se pudo extraer JSON válido del output');
                    return [
                        'success' => false,
                        'message' => 'No se pudo extraer JSON válido de la respuesta Python'
                    ];
                }
                
                Log::info('JSON extraído para parsing', [
                    'json_length' => strlen($cleanJsonOutput),
                    'json_preview' => substr($cleanJsonOutput, 0, 500)
                ]);
                
                $result = json_decode($cleanJsonOutput, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Error parseando JSON Python después de limpieza', [
                        'json_error' => json_last_error_msg(),
                        'json_content' => substr($cleanJsonOutput, 0, 1000)
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'Error parseando respuesta JSON después de limpieza: ' . json_last_error_msg()
                    ];
                }
            }

            if ($result && isset($result['success'])) {
                if ($result['success']) {
                    // Debugging extendido del resultado
                    Log::info('Procesamiento exitoso - análisis detallado', [
                        'result_keys' => array_keys($result),
                        'tiene_result' => isset($result['result']),
                        'result_result_keys' => isset($result['result']) ? array_keys($result['result']) : [],
                        'clave_acceso_directo' => $result['accessKey'] ?? null,
                        'clave_acceso_en_result' => isset($result['result']['accessKey']) ? $result['result']['accessKey'] : null
                    ]);
                    
                    // Verificar si xmlFileSigned está en el result
                    $xmlFirmado = null;
                    $claveAcceso = null;
                    
                    // Buscar XML firmado
                    if (isset($result['result']['xmlFileSigned']) && !empty($result['result']['xmlFileSigned'])) {
                        $xmlFirmado = $result['result']['xmlFileSigned'];
                        Log::info('XML firmado encontrado en result.result.xmlFileSigned');
                    } elseif (isset($result['xmlFileSigned']) && !empty($result['xmlFileSigned'])) {
                        $xmlFirmado = $result['xmlFileSigned'];
                        Log::info('XML firmado encontrado en result.xmlFileSigned');
                    }
                    
                    // Buscar clave de acceso
                    if (isset($result['result']['accessKey']) && !empty($result['result']['accessKey'])) {
                        $claveAcceso = $result['result']['accessKey'];
                        Log::info('Clave de acceso encontrada en result.result.accessKey', ['clave' => $claveAcceso]);
                    } elseif (isset($result['accessKey']) && !empty($result['accessKey'])) {
                        $claveAcceso = $result['accessKey'];
                        Log::info('Clave de acceso encontrada en result.accessKey', ['clave' => $claveAcceso]);
                    }
                    
                    // Asegurar que el resultado incluya el XML firmado y clave de acceso
                    return [
                        'success' => true,
                        'result' => [
                            'accessKey' => $claveAcceso,
                            'xmlFileSigned' => $xmlFirmado,
                            'isReceived' => $result['result']['isReceived'] ?? $result['isReceived'] ?? false,
                            'isAuthorized' => $result['result']['isAuthorized'] ?? $result['isAuthorized'] ?? false,
                            'sriResponse' => $result['result']['sriResponse'] ?? $result['sriResponse'] ?? null,
                            'xmlAutorizado' => $result['result']['xmlAutorizado'] ?? $result['xmlAutorizado'] ?? null,
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
     * Extraer JSON válido del output mezclado con logs
     */
    private function extractValidJsonFromOutput($output)
    {
        try {
            Log::info('Iniciando extracción de JSON válido', ['total_lines' => count($output)]);
            
            // Método 1: Buscar líneas que empiecen con { seguidas de "success":
            $jsonStartIndex = -1;
            $jsonEndIndex = -1;
            
            for ($i = 0; $i < count($output); $i++) {
                $line = trim($output[$i]);
                
                // Buscar la línea que comienza el JSON principal
                if ($line === '{' && $i + 1 < count($output)) {
                    $nextLine = trim($output[$i + 1]);
                    if (strpos($nextLine, '"success":') !== false) {
                        $jsonStartIndex = $i;
                        break;
                    }
                }
            }
            
            // Si encontramos el inicio, buscar el final
            if ($jsonStartIndex !== -1) {
                $braceCount = 0;
                for ($i = $jsonStartIndex; $i < count($output); $i++) {
                    $line = $output[$i];
                    $braceCount += substr_count($line, '{') - substr_count($line, '}');
                    
                    if ($braceCount <= 0 && strpos($line, '}') !== false) {
                        $jsonEndIndex = $i;
                        break;
                    }
                }
                
                if ($jsonEndIndex !== -1) {
                    $jsonLines = array_slice($output, $jsonStartIndex, $jsonEndIndex - $jsonStartIndex + 1);
                    $jsonContent = implode("\n", $jsonLines);
                    
                    Log::info('JSON extraído por método 1', [
                        'start_index' => $jsonStartIndex,
                        'end_index' => $jsonEndIndex,
                        'json_preview' => substr($jsonContent, 0, 200)
                    ]);
                    
                    // Verificar que es JSON válido
                    $testJson = json_decode($jsonContent, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($testJson['success'])) {
                        return $jsonContent;
                    }
                }
            }
            
            // Método 2: Buscar desde el final hacia atrás
            Log::info('Intentando método 2: búsqueda desde el final');
            
            $jsonLines = [];
            $foundEnd = false;
            $braceCount = 0;
            
            for ($i = count($output) - 1; $i >= 0; $i--) {
                $line = trim($output[$i]);
                
                if (!$foundEnd && $line === '}') {
                    $foundEnd = true;
                    array_unshift($jsonLines, $output[$i]);
                    $braceCount = 1;
                    continue;
                }
                
                if ($foundEnd) {
                    array_unshift($jsonLines, $output[$i]);
                    $braceCount += substr_count($line, '{') - substr_count($line, '}');
                    
                    if ($braceCount <= 0 && strpos($line, '{') !== false) {
                        break;
                    }
                }
            }
            
            if (!empty($jsonLines)) {
                $jsonContent = implode("\n", $jsonLines);
                
                Log::info('JSON extraído por método 2', [
                    'lines_count' => count($jsonLines),
                    'json_preview' => substr($jsonContent, 0, 200)
                ]);
                
                // Verificar que es JSON válido
                $testJson = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($testJson['success'])) {
                    return $jsonContent;
                }
            }
            
            // Método 3: Buscar patrones específicos en el output
            Log::info('Intentando método 3: búsqueda por patrones');
            
            $outputString = implode("\n", $output);
            
            // Buscar el patrón: {\n  "success": 
            if (preg_match('/\{\s*\n\s*"success":\s*(true|false).*?\n\}/s', $outputString, $matches)) {
                $jsonContent = $matches[0];
                
                Log::info('JSON extraído por método 3 (regex)', [
                    'json_preview' => substr($jsonContent, 0, 200)
                ]);
                
                $testJson = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($testJson['success'])) {
                    return $jsonContent;
                }
            }
            
            // Método 4: Extraer todo lo que está entre el último { y el último }
            Log::info('Intentando método 4: último bloque JSON completo');
            
            $lastOpenBrace = strrpos($outputString, '{');
            $lastCloseBrace = strrpos($outputString, '}');
            
            if ($lastOpenBrace !== false && $lastCloseBrace !== false && $lastCloseBrace > $lastOpenBrace) {
                $jsonContent = substr($outputString, $lastOpenBrace, $lastCloseBrace - $lastOpenBrace + 1);
                
                Log::info('JSON extraído por método 4', [
                    'json_preview' => substr($jsonContent, 0, 200)
                ]);
                
                $testJson = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($testJson['success'])) {
                    return $jsonContent;
                }
            }
            
            Log::error('Todos los métodos de extracción fallaron');
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error extrayendo JSON válido del output', ['error' => $e->getMessage()]);
            return null;
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
