<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Factura;

echo "=== VERIFICACIÓN ESPECÍFICA FACTURA 92 ===\n\n";

$factura = Factura::find(92);

if (!$factura) {
    echo "❌ Factura 92 no encontrada\n";
    exit(1);
}

echo "Estado: {$factura->estado}\n";
echo "Clave de acceso: {$factura->clave_acceso}\n\n";

echo "=== CAMPOS XML ===\n";
echo "xml: " . (empty($factura->xml) ? 'VACÍO' : strlen($factura->xml) . ' caracteres') . "\n";
echo "xml_firmado: " . (empty($factura->xml_firmado) ? 'VACÍO' : strlen($factura->xml_firmado) . ' caracteres') . "\n";
echo "xml_autorizado: " . (empty($factura->xml_autorizado) ? 'VACÍO' : strlen($factura->xml_autorizado) . ' caracteres') . "\n\n";

echo "=== MÉTODO getXmlContent() ===\n";
$xmlContent = $factura->getXmlContent();
$xmlType = $factura->getXmlType();

echo "Tipo detectado: {$xmlType}\n";
echo "Longitud: " . strlen($xmlContent) . " caracteres\n";

if (strlen($xmlContent) > 0) {
    echo "Primeros 200 caracteres:\n";
    echo substr($xmlContent, 0, 200) . "\n\n";
    
    echo "Últimos 200 caracteres:\n";
    echo substr($xmlContent, -200) . "\n\n";
    
    // Verificar si es XML válido
    if (strpos($xmlContent, '<?xml') === 0) {
        echo "✅ Es XML válido\n";
        
        // Buscar elementos importantes
        if (strpos($xmlContent, '<claveAcceso>') !== false) {
            preg_match('/<claveAcceso>(.*?)<\/claveAcceso>/', $xmlContent, $matches);
            if (!empty($matches[1])) {
                echo "✅ Clave de acceso en XML: {$matches[1]}\n";
            }
        }
        
        if (strpos($xmlContent, '<totalConImpuestos>') !== false) {
            echo "✅ Contiene totalConImpuestos\n";
        }
        
        if (strpos($xmlContent, 'ds:Signature') !== false) {
            echo "✅ Contiene firma digital\n";
        }
        
    } else {
        echo "❌ No es XML válido\n";
        echo "Contenido: {$xmlContent}\n";
    }
} else {
    echo "❌ Contenido vacío\n";
}

echo "\n=== CAMPOS RAW DE BD ===\n";
echo "xml raw: " . ($factura->xml ? 'TIENE CONTENIDO' : 'VACÍO') . "\n";
echo "xml_firmado raw: " . ($factura->xml_firmado ? 'TIENE CONTENIDO' : 'VACÍO') . "\n";
echo "xml_autorizado raw: " . ($factura->xml_autorizado ? 'TIENE CONTENIDO' : 'VACÍO') . "\n";

echo "\n=== FIN VERIFICACIÓN ===\n";
