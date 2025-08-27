<?php

// Script de prueba del servicio de firma Python
echo "=== PRUEBA DEL SERVICIO DE FIRMA PYTHON ===\n\n";

$facturaId = $argv[1] ?? null;
$password = $argv[2] ?? null;

if (!$facturaId || !$password) {
    echo "Uso: php test_python_service.php <factura_id> <password>\n";
    echo "Ejemplo: php test_python_service.php 59 mipassword\n";
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

try {
    // Crear XML de prueba
    $xmlPrueba = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="1.0.0">
    <infoTributaria>
        <ambiente>1</ambiente>
        <tipoEmision>1</tipoEmision>
        <razonSocial>EMPRESA DE PRUEBA S.A.</razonSocial>
        <nombreComercial>EMPRESA DE PRUEBA</nombreComercial>
        <ruc>1234567890001</ruc>
        <claveAcceso>01234567890123456789012345678901234567890123456789</claveAcceso>
        <codDoc>01</codDoc>
        <estab>001</estab>
        <ptoEmi>001</ptoEmi>
        <secuencial>000000001</secuencial>
        <dirMatriz>Dirección Matriz</dirMatriz>
    </infoTributaria>
    <infoFactura>
        <fechaEmision>2024-01-01</fechaEmision>
        <obligadoContabilidad>NO</obligadoContabilidad>
        <tipoIdentificacionComprador>04</tipoIdentificacionComprador>
        <razonSocialComprador>Cliente de Prueba</razonSocialComprador>
        <identificacionComprador>1234567890</identificacionComprador>
        <direccionComprador>Dirección Cliente</direccionComprador>
        <totalSinImpuestos>100.00</totalSinImpuestos>
        <totalDescuento>0.00</totalDescuento>
        <totalConImpuestos>
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>0</codigoPorcentaje>
                <baseImponible>100.00</baseImponible>
                <valor>0.00</valor>
            </totalImpuesto>
        </totalConImpuestos>
        <propina>0.00</propina>
        <importeTotal>100.00</importeTotal>
        <pagos>
            <pago>
                <formaPago>01</formaPago>
                <total>100.00</total>
            </pago>
        </pagos>
    </infoFactura>
    <detalles>
        <detalle>
            <codigoPrincipal>001</codigoPrincipal>
            <descripcion>Producto de Prueba</descripcion>
            <cantidad>1.00</cantidad>
            <precioUnitario>100.00</precioUnitario>
            <descuento>0.00</descuento>
            <precioTotalSinImpuesto>100.00</precioTotalSinImpuesto>
            <impuestos>
                <impuesto>
                    <codigo>2</codigo>
                    <codigoPorcentaje>0</codigoPorcentaje>
                    <tarifa>0.00</tarifa>
                    <baseImponible>100.00</baseImponible>
                    <valor>0.00</valor>
                </impuesto>
            </impuestos>
        </detalle>
    </detalles>
</factura>
XML;

    echo "=== LLAMANDO AL SERVICIO PYTHON ===\n";

    // Crear archivo temporal para el XML
    $tempXmlFile = tempnam(sys_get_temp_dir(), 'xml_');
    file_put_contents($tempXmlFile, $xmlPrueba);

    // Preparar comando para ejecutar Python
    $command = 'py "' . __DIR__ . '/firma_service.py" "' . $certPath . '" "' . $password . '" "' . $tempXmlFile . '"';

    echo "Comando a ejecutar: $command\n";

    // Ejecutar comando y capturar salida
    $output = shell_exec($command);

    // Limpiar archivo temporal
    unlink($tempXmlFile);

    if ($output === null) {
        echo "❌ ERROR: No se pudo ejecutar el comando Python\n";
        exit(1);
    }

    // Parsear resultado JSON
    $result = json_decode($output, true);

    if (!$result) {
        echo "❌ ERROR: No se pudo parsear la respuesta JSON\n";
        echo "Longitud de la salida: " . strlen($output) . " caracteres\n";
        echo "Primeros 500 caracteres:\n" . substr($output, 0, 500) . "\n";
        echo "Últimos 500 caracteres:\n" . substr($output, -500) . "\n";
        exit(1);
    }

    if ($result['success']) {
        echo "✅ ¡ÉXITO! El servicio Python funcionó correctamente\n\n";

        echo "=== INFORMACIÓN DEL CERTIFICADO ===\n";
        $certInfo = $result['certificate_info'];
        echo "Asunto: {$certInfo['subject']}\n";
        echo "Emisor: {$certInfo['issuer']}\n";
        echo "Número de serie: {$certInfo['serial_number']}\n";
        echo "Válido desde: {$certInfo['not_valid_before']}\n";
        echo "Válido hasta: {$certInfo['not_valid_after']}\n";
        echo "Algoritmo de firma: {$certInfo['signature_algorithm']}\n\n";

        echo "=== XML FIRMADO ===\n";
        $signedXml = $result['signed_xml'];

        // Verificar que contiene firma
        if (strpos($signedXml, '<Signature') !== false) {
            echo "✅ Firma XAdES-BES encontrada en el XML\n";
        } else {
            echo "❌ No se encontró la firma en el XML\n";
        }

        // Mostrar tamaño del XML
        echo "Tamaño del XML original: " . strlen($xmlPrueba) . " caracteres\n";
        echo "Tamaño del XML firmado: " . strlen($signedXml) . " caracteres\n\n";

        // Guardar XML firmado para inspección
        $outputFile = __DIR__ . '/xml_firmado_python.xml';
        file_put_contents($outputFile, $signedXml);
        echo "XML firmado guardado en: $outputFile\n";

    } else {
        echo "❌ ERROR en el servicio Python: {$result['error']}\n";
        if (isset($result['traceback'])) {
            echo "\nTraceback completo:\n{$result['traceback']}\n";
        }
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ ERROR EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== PRUEBA COMPLETADA ===\n";

?>
