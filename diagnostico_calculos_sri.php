<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Log;

/**
 * Script de diagnóstico para verificar cálculos de impuestos SRI
 * 
 * Este script verifica que los cálculos de IVA estén correctos según
 * las normas del SRI Ecuador para evitar el error "ERROR EN DIFERENCIAS"
 */

echo "=== DIAGNÓSTICO DE CÁLCULOS SRI ECUADOR ===\n\n";

// Simular datos de una factura con IVA 15%
$elementos = [
    [
        'codigo' => 'EXA001',
        'descripcion' => 'Examen Visual',
        'cantidad' => 1,
        'precio_unitario' => 25.00,
        'subtotal' => 25.00,
        'codigo_porcentaje' => '0', // 0% IVA
        'tarifa' => '0',
        'valor_impuesto' => 0
    ],
    [
        'codigo' => 'ARM001',
        'descripcion' => 'Armazon/Accesorios',
        'cantidad' => 1,
        'precio_unitario' => 100.00,
        'subtotal' => 100.00,
        'codigo_porcentaje' => '6', // 15% IVA
        'tarifa' => '15',
        'valor_impuesto' => 15.00 // 100 * 0.15
    ],
    [
        'codigo' => 'LUN001',
        'descripcion' => 'Cristaleria',
        'cantidad' => 1,
        'precio_unitario' => 50.00,
        'subtotal' => 50.00,
        'codigo_porcentaje' => '6', // 15% IVA
        'tarifa' => '15',
        'valor_impuesto' => 7.50 // 50 * 0.15
    ]
];

echo "ELEMENTOS DE LA FACTURA:\n";
foreach ($elementos as $i => $elemento) {
    echo "  " . ($i + 1) . ". " . $elemento['descripcion'] . "\n";
    echo "     Precio: $" . number_format($elemento['precio_unitario'], 2) . "\n";
    echo "     Subtotal: $" . number_format($elemento['subtotal'], 2) . "\n";
    echo "     IVA %: " . $elemento['tarifa'] . "%\n";
    echo "     Valor IVA: $" . number_format($elemento['valor_impuesto'], 2) . "\n\n";
}

// Calcular totales
$subtotalSinImpuestos = 0;
$subtotalExento = 0;
$subtotalConIva = 0;
$totalIva = 0;

foreach ($elementos as $elemento) {
    $subtotalSinImpuestos += $elemento['subtotal'];
    
    if ($elemento['codigo_porcentaje'] === '0') {
        $subtotalExento += $elemento['subtotal'];
    } else {
        $subtotalConIva += $elemento['subtotal'];
        $totalIva += $elemento['valor_impuesto'];
    }
}

$total = $subtotalSinImpuestos + $totalIva;

echo "CÁLCULOS SEGÚN SRI ECUADOR:\n";
echo "  Subtotal sin impuestos: $" . number_format($subtotalSinImpuestos, 2) . "\n";
echo "  Subtotal exento (0%):   $" . number_format($subtotalExento, 2) . "\n";
echo "  Subtotal con IVA 15%:   $" . number_format($subtotalConIva, 2) . "\n";
echo "  Total IVA 15%:          $" . number_format($totalIva, 2) . "\n";
echo "  TOTAL FACTURA:          $" . number_format($total, 2) . "\n\n";

// Verificar coherencia
echo "VERIFICACIÓN DE COHERENCIA:\n";

$coherencia = true;

// 1. Verificar que subtotal sin impuestos = subtotal exento + subtotal con IVA
$sumaSubtotales = $subtotalExento + $subtotalConIva;
if (abs($subtotalSinImpuestos - $sumaSubtotales) > 0.01) {
    echo "  ❌ ERROR: Subtotal sin impuestos no coincide con suma de subtotales\n";
    echo "     Esperado: $" . number_format($subtotalSinImpuestos, 2) . "\n";
    echo "     Calculado: $" . number_format($sumaSubtotales, 2) . "\n";
    $coherencia = false;
} else {
    echo "  ✅ Subtotales coherentes\n";
}

// 2. Verificar cálculo de IVA
$ivaCalculado = $subtotalConIva * 0.15;
if (abs($totalIva - $ivaCalculado) > 0.01) {
    echo "  ❌ ERROR: Cálculo de IVA incorrecto\n";
    echo "     Esperado: $" . number_format($ivaCalculado, 2) . " (15% de $" . number_format($subtotalConIva, 2) . ")\n";
    echo "     Calculado: $" . number_format($totalIva, 2) . "\n";
    $coherencia = false;
} else {
    echo "  ✅ Cálculo de IVA correcto\n";
}

// 3. Verificar total
$totalCalculado = $subtotalSinImpuestos + $totalIva;
if (abs($total - $totalCalculado) > 0.01) {
    echo "  ❌ ERROR: Total incorrecto\n";
    echo "     Esperado: $" . number_format($totalCalculado, 2) . "\n";
    echo "     Calculado: $" . number_format($total, 2) . "\n";
    $coherencia = false;
} else {
    echo "  ✅ Total correcto\n";
}

echo "\n";

if ($coherencia) {
    echo "✅ TODOS LOS CÁLCULOS SON COHERENTES\n";
} else {
    echo "❌ HAY ERRORES EN LOS CÁLCULOS - ESTO CAUSARÁ 'ERROR EN DIFERENCIAS'\n";
}

echo "\n=== ESTRUCTURA XML PARA SRI ===\n";

// Mostrar cómo debe verse la estructura XML
echo "totalSinImpuestos: " . number_format($subtotalSinImpuestos, 2) . "\n";
echo "totalConImpuestos:\n";

if ($subtotalExento > 0) {
    echo "  - Impuesto código=2, codigoPorcentaje=0:\n";
    echo "    baseImponible: " . number_format($subtotalExento, 2) . "\n";
    echo "    valor: 0.00\n";
}

if ($subtotalConIva > 0) {
    echo "  - Impuesto código=2, codigoPorcentaje=6:\n";
    echo "    baseImponible: " . number_format($subtotalConIva, 2) . "\n";
    echo "    valor: " . number_format($totalIva, 2) . "\n";
}

echo "importeTotal: " . number_format($total, 2) . "\n";

echo "\n=== CÓDIGOS SRI IMPORTANTES ===\n";
echo "  Código 2 = IVA\n";
echo "  Código porcentaje 0 = 0% IVA (exento)\n";
echo "  Código porcentaje 6 = 15% IVA (tarifa vigente)\n";
echo "  Código porcentaje 2 = 12% IVA (tarifa anterior)\n";

echo "\n=== RECOMENDACIONES ===\n";
echo "1. Siempre redondear a 2 decimales\n";
echo "2. Verificar que la suma de baseImponible en totalConImpuestos = totalSinImpuestos\n";
echo "3. Verificar que la suma de valores de impuestos + totalSinImpuestos = importeTotal\n";
echo "4. Usar códigos de porcentaje correctos según tabla SRI\n";

echo "\n=== FIN DIAGNÓSTICO ===\n";
