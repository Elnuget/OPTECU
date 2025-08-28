<?php

require_once 'vendor/autoload.php';

/**
 * Script para corregir facturas que tienen rutas de archivos en lugar de contenido XML
 */

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Factura;
use Illuminate\Support\Facades\Storage;

echo "=== CORRECCIÓN DE RUTAS XML A CONTENIDO ===\n\n";

// Buscar facturas que tienen rutas en lugar de contenido XML
$facturas = Factura::where(function($query) {
    $query->where('xml_firmado', 'LIKE', 'facturas/%')
          ->orWhere('xml', 'LIKE', 'facturas/%')
          ->orWhere('xml', 'LIKE', '%.xml')
          ->orWhere('xml_firmado', 'LIKE', '%.xml');
})->get();

echo "Facturas encontradas con rutas: " . $facturas->count() . "\n\n";

$corregidas = 0;
$errores = 0;

foreach ($facturas as $factura) {
    echo "Procesando Factura ID: {$factura->id}\n";
    echo "  Estado: {$factura->estado}\n";
    
    $cambios = false;
    
    // Corregir campo xml
    if (!empty($factura->xml) && !str_starts_with($factura->xml, '<?xml')) {
        $rutaXml = $factura->xml;
        echo "  XML original es ruta: {$rutaXml}\n";
        
        // Intentar cargar desde storage/app/public
        $rutaCompleta = storage_path('app/public/' . $rutaXml);
        if (file_exists($rutaCompleta)) {
            $contenidoXml = file_get_contents($rutaCompleta);
            if ($contenidoXml && str_starts_with($contenidoXml, '<?xml')) {
                $factura->xml = $contenidoXml;
                $cambios = true;
                echo "    ✅ XML original corregido (desde archivo)\n";
            } else {
                echo "    ❌ Archivo no contiene XML válido\n";
            }
        } else {
            echo "    ⚠️ Archivo no encontrado: {$rutaCompleta}\n";
        }
    }
    
    // Corregir campo xml_firmado
    if (!empty($factura->xml_firmado) && !str_starts_with($factura->xml_firmado, '<?xml')) {
        $rutaXmlFirmado = $factura->xml_firmado;
        echo "  XML firmado es ruta: {$rutaXmlFirmado}\n";
        
        // Intentar cargar desde storage/app/public
        $rutaCompleta = storage_path('app/public/' . $rutaXmlFirmado);
        if (file_exists($rutaCompleta)) {
            $contenidoXml = file_get_contents($rutaCompleta);
            if ($contenidoXml && str_starts_with($contenidoXml, '<?xml')) {
                $factura->xml_firmado = $contenidoXml;
                $cambios = true;
                echo "    ✅ XML firmado corregido (desde archivo)\n";
            } else {
                echo "    ❌ Archivo no contiene XML válido\n";
            }
        } else {
            echo "    ⚠️ Archivo no encontrado: {$rutaCompleta}\n";
        }
    }
    
    // Guardar cambios
    if ($cambios) {
        try {
            $factura->save();
            $corregidas++;
            echo "    ✅ Factura actualizada exitosamente\n";
        } catch (Exception $e) {
            $errores++;
            echo "    ❌ Error guardando: " . $e->getMessage() . "\n";
        }
    } else {
        echo "    ⏭️ No se realizaron cambios\n";
    }
    
    echo "\n";
}

echo "=== RESUMEN ===\n";
echo "Facturas procesadas: " . $facturas->count() . "\n";
echo "Facturas corregidas: {$corregidas}\n";
echo "Errores: {$errores}\n";

// Ahora verificar algunas facturas específicas
echo "\n=== VERIFICACIÓN POST-CORRECCIÓN ===\n";

$facturasVerificar = Factura::whereIn('id', [92, 93])->get();

foreach ($facturasVerificar as $factura) {
    echo "\nFactura ID: {$factura->id}\n";
    echo "Estado: {$factura->estado}\n";
    
    $xmlContent = $factura->getXmlContent();
    $xmlType = $factura->getXmlType();
    
    echo "Tipo XML: {$xmlType}\n";
    echo "Tiene contenido: " . (!empty($xmlContent) ? 'SÍ' : 'NO') . "\n";
    
    if (!empty($xmlContent)) {
        $esXmlValido = str_starts_with($xmlContent, '<?xml');
        echo "Es XML válido: " . ($esXmlValido ? 'SÍ' : 'NO') . "\n";
        echo "Longitud: " . strlen($xmlContent) . " caracteres\n";
        
        if ($esXmlValido) {
            // Intentar extraer clave de acceso
            if (preg_match('/<claveAcceso>(.*?)<\/claveAcceso>/', $xmlContent, $matches)) {
                echo "Clave de acceso: {$matches[1]}\n";
            }
        }
    }
}

echo "\n=== CORRECCIÓN COMPLETADA ===\n";
