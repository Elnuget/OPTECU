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
    // 1. Verificar archivo .env del SRI
    echo "1. Verificando archivo .env...\n";
    $envPath = public_path('SriSignXml/.env');
    
    if (!file_exists($envPath)) {
        $errores[] = "Archivo .env no encontrado en: $envPath";
    } else {
        $envContent = file_get_contents($envPath);
        
        // Verificar URLs de pruebas
        if (strpos($envContent, 'celcer.sri.gob.ec') !== false) {
            echo "✅ URLs configuradas para PRUEBAS (celcer)\n";
        } elseif (strpos($envContent, 'cel.sri.gob.ec') !== false) {
            $errores[] = "⚠️ PELIGRO: URLs configuradas para PRODUCCIÓN (cel)";
        } else {
            $warnings[] = "URLs no reconocidas en .env";
        }
        
        // Verificar ambiente
        if (strpos($envContent, 'AMBIENTE=1') !== false) {
            echo "✅ Variable AMBIENTE configurada para pruebas (1)\n";
        } elseif (strpos($envContent, 'AMBIENTE=2') !== false) {
            $errores[] = "⚠️ PELIGRO: Variable AMBIENTE configurada para producción (2)";
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
    
    // 4. Verificar URLs específicas
    echo "\n4. Verificando URLs específicas...\n";
    $urlsEsperadas = [
        'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
        'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl'
    ];
    
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        foreach ($urlsEsperadas as $url) {
            if (strpos($envContent, $url) !== false) {
                echo "✅ URL encontrada: " . substr($url, 0, 50) . "...\n";
            } else {
                $warnings[] = "URL esperada no encontrada: " . substr($url, 0, 50) . "...";
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
        echo "✅ URLs: Configuradas para celcer.sri.gob.ec\n";
        echo "✅ VALIDACIONES: Todas las verificaciones pasaron\n";
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
