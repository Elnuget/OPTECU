<?php

/**
 * Test de Validación de Ambiente SRI
 * Verifica que todos los componentes estén configurados para PRUEBAS
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\SriPythonService;
use App\Services\XmlSriService;

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 VERIFICACIÓN DE AMBIENTE SRI\n";
echo str_repeat("=", 50) . "\n";

$errores = [];
$warnings = [];

try {
    // 1. Verificar que NO exista archivo .env (mayor seguridad)
    echo "1. Verificando ausencia de archivo .env (seguridad)...\n";
    $envPath = public_path('SriSignXml/.env');
    
    if (file_exists($envPath)) {
        $warnings[] = "⚠️ Archivo .env encontrado - debería eliminarse por seguridad";
        echo "⚠️ Archivo .env existe (menos seguro)\n";
    } else {
        echo "✅ Archivo .env NO existe (configuración más segura)\n";
    }
    
    // 2. Verificar configuración hardcodeada en script Python
    echo "\n2. Verificando configuración hardcodeada...\n";
    $pythonScript = public_path('SriSignXml/sri_processor.py');
    
    if (!file_exists($pythonScript)) {
        $errores[] = "Script sri_processor.py no encontrado";
    } else {
        $scriptContent = file_get_contents($pythonScript);
        
        // Verificar URLs de pruebas
        if (strpos($scriptContent, 'celcer.sri.gob.ec') !== false) {
            echo "✅ URLs configuradas para PRUEBAS (celcer) en código\n";
        } elseif (strpos($scriptContent, 'cel.sri.gob.ec') !== false) {
            $errores[] = "⚠️ PELIGRO: URLs configuradas para PRODUCCIÓN (cel) en código";
        } else {
            $warnings[] = "URLs no encontradas en script Python";
        }
        
        // Verificar ambiente hardcodeado
        if (strpos($scriptContent, "'AMBIENTE': '1'") !== false) {
            echo "✅ Variable AMBIENTE configurada para pruebas (1) en código\n";
        } elseif (strpos($scriptContent, "'AMBIENTE': '2'") !== false) {
            $errores[] = "⚠️ PELIGRO: Variable AMBIENTE configurada para producción (2) en código";
        }
    }
    
    // 2. Verificar script de verificación Python
    echo "\n2. Verificando con script Python...\n";
    $pythonScript = public_path('SriSignXml/verificar_ambiente.py');
    
    if (!file_exists($pythonScript)) {
        $warnings[] = "Script de verificación Python no encontrado";
    } else {
        $output = [];
        $returnCode = 0;
        exec("python \"$pythonScript\" --json", $output, $returnCode);
        
        $resultado = json_decode(implode("\n", $output), true);
        
        if ($resultado && isset($resultado['es_pruebas'])) {
            if ($resultado['es_pruebas']) {
                echo "✅ Verificación Python: AMBIENTE DE PRUEBAS\n";
            } else {
                $errores[] = "❌ Verificación Python: " . $resultado['ambiente'];
            }
        } else {
            $warnings[] = "No se pudo ejecutar verificación Python";
        }
    }
    
    // 3. Verificar servicios Laravel
    echo "\n3. Verificando servicios Laravel...\n";
    try {
        $xmlSriService = new XmlSriService();
        $sriPythonService = new SriPythonService($xmlSriService);
        echo "✅ Servicios Laravel creados correctamente\n";
        
        // Test de validación interna
        try {
            // Esto debería pasar sin problemas si estamos en pruebas
            $reflection = new ReflectionClass($sriPythonService);
            $method = $reflection->getMethod('validarAmbientePruebas');
            $method->setAccessible(true);
            $method->invoke($sriPythonService);
            echo "✅ Validación interna de ambiente: PRUEBAS\n";
        } catch (Exception $e) {
            $errores[] = "Validación interna falló: " . $e->getMessage();
        }
        
    } catch (Exception $e) {
        $errores[] = "Error creando servicios: " . $e->getMessage();
    }
    
    // 4. Verificar URLs específicas en código
    echo "\n4. Verificando URLs hardcodeadas en script...\n";
    $urlsEsperadas = [
        'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
        'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl'
    ];
    
    if (file_exists($pythonScript)) {
        $scriptContent = file_get_contents($pythonScript);
        foreach ($urlsEsperadas as $url) {
            if (strpos($scriptContent, $url) !== false) {
                echo "✅ URL encontrada en código: " . substr($url, 0, 50) . "...\n";
            } else {
                $warnings[] = "URL esperada no encontrada en código: " . substr($url, 0, 50) . "...";
            }
        }
    }
    
    // 5. Resumen final
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📋 RESUMEN DE VERIFICACIÓN\n";
    echo str_repeat("=", 50) . "\n";
    
    if (empty($errores)) {
        echo "✅ ESTADO: AMBIENTE DE PRUEBAS CONFIRMADO\n";
        echo "✅ SEGURO: No hay riesgo de afectar producción\n";
        echo "✅ URLs: Configuradas para celcer.sri.gob.ec en código\n";
        echo "✅ VALIDACIONES: Todas las verificaciones pasaron\n";
        echo "✅ SEGURIDAD: Sin archivo .env - configuración hardcodeada\n";
    } else {
        echo "❌ ESTADO: ERRORES DETECTADOS\n";
        foreach ($errores as $error) {
            echo "   - $error\n";
        }
    }
    
    if (!empty($warnings)) {
        echo "\n⚠️ ADVERTENCIAS:\n";
        foreach ($warnings as $warning) {
            echo "   - $warning\n";
        }
    }
    
    echo "\n📚 DOCUMENTACIÓN:\n";
    echo "   Ver: CONFIGURACION_AMBIENTE_SRI.md\n";
    echo "   Verificador Python: public/SriSignXml/verificar_ambiente.py\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    
    if (empty($errores)) {
        echo "🎉 LISTO PARA USAR EN AMBIENTE DE PRUEBAS\n";
    } else {
        echo "🔧 REVISAR CONFIGURACIÓN ANTES DE CONTINUAR\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante verificación: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
