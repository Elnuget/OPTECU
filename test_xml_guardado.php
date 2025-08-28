<?php

require_once 'vendor/autoload.php';

/**
 * Script de prueba para verificar que el XML se guarde correctamente
 */

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Factura;
use Illuminate\Support\Facades\Log;

echo "=== VERIFICACIÓN DE GUARDADO DE XML ===\n\n";

// Buscar una factura existente para probar
$factura = Factura::orderBy('id', 'desc')->first();

if (!$factura) {
    echo "❌ No se encontraron facturas para probar\n";
    exit(1);
}

echo "Probando con Factura ID: {$factura->id}\n";
echo "Estado actual: {$factura->estado}\n";

// Probar los métodos del modelo
echo "\n=== MÉTODOS DEL MODELO ===\n";

$xmlContent = $factura->getXmlContent();
$xmlType = $factura->getXmlType();

echo "Tipo de XML detectado: {$xmlType}\n";
echo "Tiene XML content: " . (!empty($xmlContent) ? 'SÍ' : 'NO') . "\n";

if (!empty($xmlContent)) {
    $length = strlen($xmlContent);
    echo "Longitud del XML: {$length} caracteres\n";
    
    // Verificar si es XML válido
    if (strpos($xmlContent, '<?xml') === 0) {
        echo "✅ Formato XML válido detectado\n";
        
        // Intentar parsear
        try {
            $dom = new DOMDocument();
            if ($dom->loadXML($xmlContent)) {
                echo "✅ XML se puede parsear correctamente\n";
                
                // Buscar clave de acceso
                $xpath = new DOMXPath($dom);
                $claveAccesoNodes = $xpath->query('//claveAcceso');
                if ($claveAccesoNodes->length > 0) {
                    $claveAcceso = $claveAccesoNodes->item(0)->textContent;
                    echo "✅ Clave de acceso encontrada: {$claveAcceso}\n";
                } else {
                    echo "⚠️ No se encontró clave de acceso en el XML\n";
                }
                
                // Buscar totalConImpuestos
                $totalImpuestosNodes = $xpath->query('//totalConImpuestos/totalImpuesto');
                echo "✅ Total de impuestos encontrados: " . $totalImpuestosNodes->length . "\n";
                
                foreach ($totalImpuestosNodes as $i => $impuesto) {
                    $codigo = $xpath->query('.//codigo', $impuesto)->item(0)->textContent ?? 'N/A';
                    $porcentaje = $xpath->query('.//codigoPorcentaje', $impuesto)->item(0)->textContent ?? 'N/A';
                    $base = $xpath->query('.//baseImponible', $impuesto)->item(0)->textContent ?? 'N/A';
                    $valor = $xpath->query('.//valor', $impuesto)->item(0)->textContent ?? 'N/A';
                    
                    echo "  Impuesto " . ($i + 1) . ": Código={$codigo}, Porcentaje={$porcentaje}, Base={$base}, Valor={$valor}\n";
                }
                
            } else {
                echo "❌ No se puede parsear el XML\n";
            }
        } catch (Exception $e) {
            echo "❌ Error parseando XML: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ El contenido no parece ser XML válido\n";
        echo "Primeros 100 caracteres: " . substr($xmlContent, 0, 100) . "\n";
    }
} else {
    echo "❌ No hay contenido XML disponible\n";
}

echo "\n=== VERIFICACIÓN DE CAMPOS EN BD ===\n";

echo "xml (original): " . (!empty($factura->xml) ? 'TIENE CONTENIDO' : 'VACÍO') . "\n";
echo "xml_firmado: " . (!empty($factura->xml_firmado) ? 'TIENE CONTENIDO' : 'VACÍO') . "\n";
echo "xml_autorizado: " . (!empty($factura->xml_autorizado) ? 'TIENE CONTENIDO' : 'VACÍO') . "\n";

echo "\n=== PRUEBA DE GUARDADO ===\n";

// Probar guardar XML firmado
$xmlPrueba = '<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="1.0.0">
    <infoTributaria>
        <ambiente>1</ambiente>
        <tipoEmision>1</tipoEmision>
        <razonSocial>EMPRESA DE PRUEBA</razonSocial>
        <ruc>1234567890001</ruc>
        <claveAcceso>2808202501123456789012345678901234567890123456789</claveAcceso>
        <codDoc>01</codDoc>
        <estab>001</estab>
        <ptoEmi>001</ptoEmi>
        <secuencial>000000001</secuencial>
    </infoTributaria>
    <!-- XML de prueba simplificado -->
</factura>';

try {
    $facturaPrueba = $factura->replicate();
    $facturaPrueba->estado = 'CREADA';
    $facturaPrueba->save();
    
    echo "Factura de prueba creada con ID: {$facturaPrueba->id}\n";
    
    // Probar método de guardado
    $resultado = $facturaPrueba->guardarXmlFirmado($xmlPrueba);
    
    if ($resultado) {
        echo "✅ XML firmado guardado exitosamente\n";
        
        // Verificar que se guardó
        $facturaPrueba->refresh();
        echo "Estado después del guardado: {$facturaPrueba->estado}\n";
        echo "XML firmado tiene contenido: " . (!empty($facturaPrueba->xml_firmado) ? 'SÍ' : 'NO') . "\n";
        
        if (!empty($facturaPrueba->xml_firmado)) {
            echo "✅ XML firmado se guardó correctamente en BD\n";
            echo "Longitud: " . strlen($facturaPrueba->xml_firmado) . " caracteres\n";
        }
        
    } else {
        echo "❌ Error guardando XML firmado\n";
    }
    
    // Limpiar factura de prueba
    $facturaPrueba->delete();
    echo "Factura de prueba eliminada\n";
    
} catch (Exception $e) {
    echo "❌ Error en prueba de guardado: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
