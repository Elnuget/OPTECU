<?php

// Script de prueba del servicio de firma electrónica
echo "=== PRUEBA DEL SERVICIO DE FIRMA ELECTRÓNICA ===\n\n";

$facturaId = $argv[1] ?? null;
$password = $argv[2] ?? null;

if (!$facturaId || !$password) {
    echo "Uso: php test_servicio.php <factura_id> <password>\n";
    echo "Ejemplo: php test_servicio.php 59 mipassword\n";
    exit(1);
}

echo "Factura ID: $facturaId\n";
echo "Contraseña: " . str_repeat("*", strlen($password)) . "\n\n";

// Buscar archivos P12 disponibles
$archivosP12 = glob("public/uploads/firmas/*.p12");

if (empty($archivosP12)) {
    echo "❌ ERROR: No se encontraron archivos P12\n";
    exit(1);
}

$certPath = $archivosP12[0];
echo "Usando certificado: " . basename($certPath) . "\n\n";

// Inicializar Laravel para poder usar el servicio
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Importar el servicio
use App\Services\FirmaElectronicaService;

try {
    echo "=== INICIALIZANDO SERVICIO ===\n";
    $servicio = new FirmaElectronicaService($certPath, $password);

    echo "✅ Servicio inicializado correctamente\n";

    // Crear un XML de prueba simple
    $xmlPrueba = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<factura>
    <infoTributaria>
        <razonSocial>Empresa de Prueba</razonSocial>
        <ruc>1234567890001</ruc>
    </infoTributaria>
    <infoFactura>
        <fechaEmision>2024-01-01</fechaEmision>
        <total>100.00</total>
    </infoFactura>
</factura>
XML;

    echo "\n=== FIRMANDO XML DE PRUEBA ===\n";
    $xmlFirmado = $servicio->firmarXML($xmlPrueba);

    if ($xmlFirmado) {
        echo "✅ XML firmado correctamente\n";
        echo "Longitud del XML firmado: " . strlen($xmlFirmado) . " caracteres\n";

        // Verificar que contiene la firma
        if (strpos($xmlFirmado, '<Signature') !== false) {
            echo "✅ Firma XAdES-BES encontrada en el XML\n";
        } else {
            echo "❌ No se encontró la firma en el XML\n";
        }
    } else {
        echo "❌ Error al firmar el XML\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== PRUEBA COMPLETADA ===\n";

?>
