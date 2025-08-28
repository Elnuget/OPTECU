<?php
/**
 * Prueba para verificar el comportamiento de contraseñas en declarantes
 */
require_once 'vendor/autoload.php';

use App\Models\Declarante;
use Illuminate\Support\Facades\DB;

// Simular configuración mínima de Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DE COMPORTAMIENTO DE CONTRASEÑAS EN DECLARANTES ===\n\n";

// Obtener todos los declarantes
$declarantes = Declarante::all();

if ($declarantes->count() == 0) {
    echo "❌ No hay declarantes en la base de datos.\n";
    exit;
}

echo "📋 Declarantes encontrados: " . $declarantes->count() . "\n\n";

foreach ($declarantes as $declarante) {
    echo "🏢 Declarante: {$declarante->nombre} (RUC: {$declarante->ruc})\n";
    echo "   ID: {$declarante->id}\n";
    
    // Verificar si tiene contraseña guardada
    $tienePassword = !empty($declarante->password_certificado);
    echo "   ¿Tiene contraseña guardada? " . ($tienePassword ? "✅ SÍ" : "❌ NO") . "\n";
    
    if ($tienePassword) {
        echo "   📝 Valor encriptado existe en BD: SÍ\n";
        echo "   🔓 Contraseña desencriptada: " . (strlen($declarante->password_certificado) > 0 ? "[OCULTA - " . strlen($declarante->password_certificado) . " caracteres]" : "VACÍA") . "\n";
    }
    
    echo "   📄 ¿Tiene certificado P12? " . ($declarante->tieneCertificadoP12Attribute() ? "✅ SÍ" : "❌ NO") . "\n";
    
    echo "\n";
}

echo "✅ Prueba completada.\n";
