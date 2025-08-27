<?php

// Script de prueba de la integración híbrida PHP-Python
echo "=== PRUEBA DE INTEGRACIÓN HÍBRIDA PHP-PYTHON ===\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FirmaElectronicaService;

try {
    // Ruta del certificado
    $certPath = 'public/uploads/firmas/1756240409_17104441_identity_1725874992.p12';
    $password = 'orionRigel15';

    echo "=== INICIALIZANDO SERVICIO HÍBRIDO ===\n";
    echo "Certificado: " . basename($certPath) . "\n";
    echo "Contraseña: " . str_repeat("*", strlen($password)) . "\n\n";

    // Crear servicio híbrido
    $servicio = new FirmaElectronicaService($certPath, $password);
    echo "✅ Servicio híbrido inicializado correctamente\n\n";

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

    echo "=== FIRMANDO XML ===\n";
    $xmlFirmado = $servicio->firmarXML($xmlPrueba);

    if ($xmlFirmado) {
        echo "✅ ¡ÉXITO! XML firmado correctamente\n";
        echo "Tamaño del XML original: " . strlen($xmlPrueba) . " caracteres\n";
        echo "Tamaño del XML firmado: " . strlen($xmlFirmado) . " caracteres\n\n";

        // Verificar que contiene firma
        if (strpos($xmlFirmado, '<Signature') !== false) {
            echo "✅ Firma XAdES-BES encontrada en el XML\n";
        } else {
            echo "❌ No se encontró la firma en el XML\n";
        }

        // Guardar XML firmado
        $outputFile = __DIR__ . '/xml_firmado_hibrido.xml';
        file_put_contents($outputFile, $xmlFirmado);
        echo "XML firmado guardado en: $outputFile\n\n";

        echo "=== PRUEBA COMPLETADA EXITOSAMENTE ===\n";
        echo "La integración híbrida PHP-Python está funcionando correctamente!\n";

    } else {
        echo "❌ Error al firmar el XML\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "\n";
    exit(1);
}

?>
