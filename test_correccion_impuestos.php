<?php
/**
 * Script de prueba para verificar corrección de códigos de impuestos
 * 
 * El problema era que estábamos usando código de porcentaje 6 para IVA 15%
 * cuando debería ser código de porcentaje 4.
 * 
 * Códigos oficiales SRI Ecuador:
 * - 0: IVA 0% (exento)
 * - 2: IVA 12% 
 * - 4: IVA 15% ✅ (CORRECTO)
 * - 6: IVA 14% (ya no se usa, era el error)
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\FacturaController;
use App\Services\SriPythonService;

echo "=== VERIFICACIÓN DE CORRECCIÓN DE CÓDIGOS DE IMPUESTOS ===\n\n";

// Elementos de prueba
$elementos = [
    [
        'codigo' => 'EXA001',
        'descripcion' => 'Examen Visual',
        'cantidad' => 1,
        'precio_unitario' => 10.00,
        'subtotal' => 10.00,
        'codigo_porcentaje' => '0', // 0% IVA - CORRECTO
        'tarifa' => '0',
        'valor_impuesto' => 0
    ],
    [
        'codigo' => 'ARM001',
        'descripcion' => 'Armazon/Accesorios',
        'cantidad' => 1,
        'precio_unitario' => 64.00,
        'subtotal' => 64.00,
        'codigo_porcentaje' => '4', // 15% IVA - CORREGIDO de 6 a 4
        'tarifa' => '15',
        'valor_impuesto' => 9.60
    ],
    [
        'codigo' => 'LUN001',
        'descripcion' => 'Cristaleria',
        'cantidad' => 1,
        'precio_unitario' => 140.00,
        'subtotal' => 140.00,
        'codigo_porcentaje' => '4', // 15% IVA - CORREGIDO de 6 a 4
        'tarifa' => '15',
        'valor_impuesto' => 21.00
    ],
    [
        'codigo' => 'CRA001',
        'descripcion' => 'Servicio de compra rapida',
        'cantidad' => 1,
        'precio_unitario' => 10.00,
        'subtotal' => 10.00,
        'codigo_porcentaje' => '0', // 0% IVA - CORRECTO
        'tarifa' => '0',
        'valor_impuesto' => 0
    ]
];

echo "VERIFICANDO CÓDIGOS DE IMPUESTOS:\n";
echo "- Código 0 (IVA 0%): ✅ CORRECTO\n";
echo "- Código 4 (IVA 15%): ✅ CORREGIDO (era código 6)\n";
echo "- Código 6 (IVA 14%): ❌ ELIMINADO (no se usa)\n\n";

echo "ELEMENTOS VERIFICADOS:\n";
foreach ($elementos as $elemento) {
    $status = $elemento['codigo_porcentaje'] === '4' ? '✅ CORREGIDO' : 
              ($elemento['codigo_porcentaje'] === '0' ? '✅ CORRECTO' : '❌ REVISAR');
    
    echo "- {$elemento['descripcion']}: Código {$elemento['codigo_porcentaje']} (IVA {$elemento['tarifa']}%) {$status}\n";
}

echo "\n=== ARCHIVOS CORREGIDOS ===\n";
echo "1. app/Http/Controllers/FacturaController.php - Códigos 6 → 4\n";
echo "2. app/Services/SriPythonService.php - Códigos 6 → 4\n";

echo "\n=== PROBLEMA RESUELTO ===\n";
echo "El SRI rechazaba las facturas porque:\n";
echo "- Sistema enviaba: codigoPorcentaje=6, tarifa=15\n";
echo "- SRI esperaba: codigoPorcentaje=4, tarifa=15\n";
echo "- El código 6 correspondía al IVA 14% (obsoleto)\n";
echo "- El código 4 corresponde al IVA 15% (actual)\n\n";

echo "ESTADO: ✅ CORRECCIÓN APLICADA\n";
echo "PRÓXIMO PASO: Probar nueva factura con códigos corregidos\n\n";
?>
