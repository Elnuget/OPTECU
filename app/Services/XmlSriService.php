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
        $this->pythonScriptPath = public_path('SriSignXml');
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
            file_put_contents($tempFile, json_encode($invoiceData, JSON_PRETTY_PRINT));
            
            // 2. Crear comando Python para procesar la factura
            $pythonCommand = $this->buildPythonCommand($tempFile, $certificatePath, $password);
            
            Log::info('Ejecutando comando Python', ['command' => $pythonCommand]);
            
            // 3. Ejecutar el comando Python
            $output = [];
            $returnCode = 0;
            exec($pythonCommand, $output, $returnCode);
            
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
            $jsonOutput = implode("\n", $output);
            $result = json_decode($jsonOutput, true);
            
            if ($result && isset($result['success']) && $result['success']) {
                Log::info('Procesamiento exitoso', $result);
                return [
                    'success' => true,
                    'result' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Error desconocido en el procesamiento'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error parseando resultado Python', ['output' => $output]);
            return [
                'success' => false,
                'message' => 'Error parseando respuesta: ' . $e->getMessage()
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
}
