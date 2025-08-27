<?php

// Script simplificado para probar el servicio Python
echo "=== PRUEBA SIMPLIFICADA DEL SERVICIO PYTHON ===\n\n";

$facturaId = $argv[1] ?? null;
$password = $argv[2] ?? null;

if (!$facturaId || !$password) {
    echo "Uso: php test_simple.php <factura_id> <password>\n";
    exit(1);
}

echo "Factura ID: $facturaId\n";
echo "Contraseña: " . str_repeat("*", strlen($password)) . "\n\n";

// Buscar archivos P12 disponibles
$archivosP12 = glob("public/uploads/firmas/*.p12");
$certPath = $archivosP12[0];
echo "Usando certificado: " . basename($certPath) . "\n\n";

// Crear XML de prueba simple
$xmlPrueba = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="1.0.0">
    <infoTributaria>
        <razonSocial>EMPRESA DE PRUEBA</razonSocial>
        <ruc>1234567890001</ruc>
    </infoTributaria>
    <infoFactura>
        <fechaEmision>2024-01-01</fechaEmision>
        <total>100.00</total>
    </infoFactura>
</factura>
XML;

// Crear archivo temporal para el XML
$tempXmlFile = tempnam(sys_get_temp_dir(), 'xml_');
file_put_contents($tempXmlFile, $xmlPrueba);

// Ejecutar comando Python
$command = 'py "' . __DIR__ . '/firma_service.py" "' . $certPath . '" "' . $password . '" "' . $tempXmlFile . '"';
echo "Ejecutando: $command\n\n";

$output = shell_exec($command);

// Limpiar archivo temporal
unlink($tempXmlFile);

// Guardar la salida completa en un archivo para análisis
$outputFile = __DIR__ . '/resultado_python.json';
file_put_contents($outputFile, $output);

echo "=== RESULTADO ===\n";
echo "Salida guardada en: $outputFile\n";
echo "Longitud: " . strlen($output) . " caracteres\n\n";

// Intentar parsear JSON
$result = json_decode($output, true);

if ($result && isset($result['success']) && $result['success']) {
    echo "✅ ¡ÉXITO! Servicio Python funcionó correctamente\n\n";

    echo "=== INFORMACIÓN DEL CERTIFICADO ===\n";
    $certInfo = $result['certificate_info'];
    echo "Asunto: {$certInfo['subject']}\n";
    echo "Emisor: {$certInfo['issuer']}\n";
    echo "Válido desde: {$certInfo['not_valid_before']}\n";
    echo "Válido hasta: {$certInfo['not_valid_after']}\n\n";

    echo "✅ XML firmado correctamente (tamaño: " . strlen($result['signed_xml']) . " caracteres)\n";

    // Guardar XML firmado
    $xmlFile = __DIR__ . '/xml_firmado_python.xml';
    file_put_contents($xmlFile, $result['signed_xml']);
    echo "XML firmado guardado en: $xmlFile\n";

} elseif ($result && isset($result['success']) && !$result['success']) {
    echo "❌ ERROR en servicio Python: {$result['error']}\n";
} else {
    echo "❌ No se pudo parsear JSON. Revisar archivo: $outputFile\n";
    echo "Primeros 200 caracteres: " . substr($output, 0, 200) . "\n";
}

echo "\n=== FIN ===\n";

?>
