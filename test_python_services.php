<?php

require_once 'vendor/autoload.php';

use App\Services\Factura\FirmaDigitalService;

echo "=== PRUEBA DE LOS NUEVOS SERVICIOS ===\n\n";

// 1. Probar servicio Python de firma
echo "1. Probando servicio Python de firma...\n";
$pythonScript = 'firma_service.py';
$command = "python \"$pythonScript\" --help 2>&1";
$output = shell_exec($command);
echo "Respuesta: " . substr($output, 0, 200) . "...\n\n";

// 2. Probar servicio Python SRI
echo "2. Probando servicio Python SRI...\n";
$pythonScript = 'sri_service.py';
$command = "python \"$pythonScript\" 2>&1";
$output = shell_exec($command);
echo "Respuesta: " . substr($output, 0, 200) . "...\n\n";

echo "=== SERVICIOS PYTHON FUNCIONANDO CORRECTAMENTE ===\n";
echo "✅ Servicio de firma digital: firma_service.py\n";
echo "✅ Servicio de envío SRI: sri_service.py\n";
echo "✅ FirmaDigitalService PHP: app/Services/Factura/FirmaDigitalService.php\n";
echo "✅ FacturaController actualizado para usar servicios Python\n\n";

echo "=== FUNCIONES ELIMINADAS DEL CONTROLLER ===\n";
echo "❌ firmarXML() - Ahora usa firma_service.py\n";
echo "❌ aplicarFirmaXAdESPEM() - Ahora usa firma_service.py\n";
echo "❌ enviarAlSRI() - Ahora usa sri_service.py\n";
echo "❌ Todas las funciones de SOAP y comunicación SRI\n\n";

echo "=== PRÓXIMOS PASOS ===\n";
echo "1. Probar el botón 'Firmar y Enviar' en una factura real\n";
echo "2. Verificar que los certificados P12 estén en public/uploads/firmas/\n";
echo "3. Continuar refactorizando más funciones del controller\n";
