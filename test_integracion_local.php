<?php

/**
 * Script de prueba para verificar la integración local del servicio SRI
 * Este script simula el procesamiento de una factura usando el servicio local
 * en lugar de la API HTTP para eliminar problemas de timeout
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\SriPythonService;
use App\Services\XmlSriService;
use App\Models\Factura;
use App\Models\User;

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRUEBA DE INTEGRACIÓN LOCAL SRI ===\n";

try {
    // Crear instancia del servicio local
    $xmlSriService = new XmlSriService();
    $sriPythonService = new SriPythonService($xmlSriService);
    
    echo "✓ Servicios creados correctamente\n";
    
    // Datos de prueba mínimos
    $datosFactura = [
        'infoTributaria' => [
            'ambiente' => '1',
            'tipoEmision' => '1',
            'razonSocial' => 'EMPRESA DE PRUEBA',
            'nombreComercial' => 'EMPRESA PRUEBA',
            'ruc' => '1234567890001',
            'codDoc' => '01',
            'estab' => '001',
            'ptoEmi' => '001',
            'secuencial' => '000000001',
            'dirMatriz' => 'DIRECCION MATRIZ PRUEBA'
        ],
        'infoFactura' => [
            'fechaEmision' => date('d/m/Y'),
            'dirEstablecimiento' => 'DIRECCION ESTABLECIMIENTO',
            'obligadoContabilidad' => 'SI',
            'tipoIdentificacionComprador' => '05',
            'razonSocialComprador' => 'CONSUMIDOR FINAL',
            'identificacionComprador' => '9999999999999',
            'totalSinImpuestos' => '10.00',
            'totalDescuento' => '0.00',
            'totalConImpuestos' => [
                [
                    'codigo' => '2',
                    'codigoPorcentaje' => '2',
                    'baseImponible' => '10.00',
                    'valor' => '1.20'
                ]
            ],
            'importeTotal' => '11.20',
            'moneda' => 'DOLAR',
            'formaPago' => '01'
        ],
        'detalles' => [
            [
                'codigoPrincipal' => 'PROD001',
                'descripcion' => 'PRODUCTO DE PRUEBA',
                'cantidad' => '1',
                'precioUnitario' => '10.00',
                'descuento' => '0.00',
                'precioTotalSinImpuesto' => '10.00',
                'impuestos' => [
                    [
                        'codigo' => '2',
                        'codigoPorcentaje' => '2',
                        'tarifa' => '12',
                        'baseImponible' => '10.00',
                        'valor' => '1.20'
                    ]
                ]
            ]
        ]
    ];
    
    echo "✓ Datos de prueba preparados\n";
    
    // Crear archivos temporales para la prueba
    $tempDir = sys_get_temp_dir() . '/sri_test_' . time();
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    // Simular certificado (en una implementación real, sería el certificado real)
    $certPath = $tempDir . '/test_certificate.p12';
    file_put_contents($certPath, 'CERTIFICADO_DE_PRUEBA'); // Placeholder
    
    echo "✓ Archivos temporales creados\n";
    
    // Probar el servicio local
    echo "Iniciando procesamiento local...\n";
    
    $resultado = $xmlSriService->procesarFacturaCompleta(
        $datosFactura,
        $certPath,
        'password123'
    );
    
    if ($resultado['success']) {
        echo "✓ Procesamiento completado exitosamente\n";
        echo "Resultado: " . json_encode($resultado['result'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "✗ Error en procesamiento: " . $resultado['message'] . "\n";
    }
    
    // Limpiar archivos temporales
    if (file_exists($certPath)) {
        unlink($certPath);
    }
    if (is_dir($tempDir)) {
        rmdir($tempDir);
    }
    
    echo "✓ Archivos temporales eliminados\n";
    
} catch (Exception $e) {
    echo "✗ Error en la prueba: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "=== FIN DE PRUEBA ===\n";
