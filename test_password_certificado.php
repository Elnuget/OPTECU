<?php
/**
 * Script de prueba para verificar el funcionamiento del campo password_certificado
 */

require_once 'vendor/autoload.php';

use App\Models\Declarante;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST PASSWORD CERTIFICADO ===\n\n";

try {
    // 1. Verificar estructura de la tabla
    echo "1. Verificando estructura de tabla declarante...\n";
    $columns = DB::select('SHOW COLUMNS FROM declarante');
    $hasPasswordField = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'password_certificado') {
            $hasPasswordField = true;
            echo "   ✓ Campo password_certificado encontrado: {$column->Type}\n";
            break;
        }
    }
    
    if (!$hasPasswordField) {
        echo "   ✗ Campo password_certificado NO encontrado\n";
        exit(1);
    }

    // 2. Verificar si existen declarantes
    echo "\n2. Verificando declarantes existentes...\n";
    $declarantes = Declarante::all();
    echo "   ✓ Encontrados {$declarantes->count()} declarantes\n";

    if ($declarantes->count() > 0) {
        $declarante = $declarantes->first();
        echo "   ✓ Primer declarante: {$declarante->nombre} (ID: {$declarante->id})\n";
        
        // 3. Probar guardar contraseña
        echo "\n3. Probando guardar contraseña encriptada...\n";
        $passwordTest = 'test123';
        $declarante->password_certificado = $passwordTest;
        $declarante->save();
        
        // 4. Verificar encriptación
        echo "\n4. Verificando encriptación...\n";
        $declaranteRefresh = Declarante::find($declarante->id);
        $declaranteRefresh->refresh(); // Forzar recarga
        $passwordGuardada = $declaranteRefresh->password_certificado;
        
        if ($passwordGuardada === $passwordTest) {
            echo "   ✓ Contraseña se guardó y recuperó correctamente: $passwordGuardada\n";
        } else {
            echo "   ✗ Error en contraseña. Esperada: $passwordTest, Obtenida: $passwordGuardada\n";
        }
        
        // 5. Probar método tienePasswordGuardada
        echo "\n5. Probando método tienePasswordGuardada...\n";
        echo "   Debug - password_certificado value: " . json_encode($declaranteRefresh->password_certificado) . "\n";
        echo "   Debug - attributes password_certificado: " . json_encode($declaranteRefresh->attributes['password_certificado'] ?? 'NO_SET') . "\n";
        
        if ($declaranteRefresh->tienePasswordGuardada) {
            echo "   ✓ Método tienePasswordGuardada funciona correctamente\n";
        } else {
            echo "   ✗ Método tienePasswordGuardada no funciona\n";
        }
        
        // 6. Limpiar datos de prueba
        echo "\n6. Limpiando datos de prueba...\n";
        $declarante->password_certificado = null;
        $declarante->save();
        echo "   ✓ Contraseña de prueba eliminada\n";
    }

    echo "\n=== PRUEBAS COMPLETADAS EXITOSAMENTE ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
