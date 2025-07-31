<?php

/**
 * Script de prueba para la lógica de generación de números de orden
 * Prueba diferentes casos de números con letras
 */

function extraerNumeroOrden($numeroOrdenAnterior) {
    // Buscar todos los números en la cadena y tomar el más grande
    preg_match_all('/\d+/', $numeroOrdenAnterior, $matches);
    
    if (!empty($matches[0])) {
        // Tomar el número más grande encontrado en la cadena
        $parteNumerica = max(array_map('intval', $matches[0]));
        $nuevoNumero = $parteNumerica + 1;
        
        echo "Último pedido: '$numeroOrdenAnterior' → Extraer '$parteNumerica' → Nuevo pedido: '$nuevoNumero'\n";
        return $nuevoNumero;
    } else {
        echo "No se encontraron números en '$numeroOrdenAnterior'\n";
        return 1;
    }
}

// Casos de prueba según los ejemplos requeridos
echo "=== CASOS DE PRUEBA PARA NÚMEROS DE ORDEN ===\n\n";

// Casos especificados en los requisitos
extraerNumeroOrden("150A");  // Debería dar 151
extraerNumeroOrden("200");   // Debería dar 201
extraerNumeroOrden("99X");   // Debería dar 100

echo "\n=== CASOS ADICIONALES ===\n\n";

// Casos edge adicionales
extraerNumeroOrden("123B456C");  // Debería tomar 456 (el más grande) y dar 457
extraerNumeroOrden("A100B200");  // Debería tomar 200 y dar 201
extraerNumeroOrden("50-A-75");   // Debería tomar 75 y dar 76
extraerNumeroOrden("ORD001");    // Debería dar 2
extraerNumeroOrden("ABC");       // Debería dar 1 (sin números)
extraerNumeroOrden("2025-001");  // Debería tomar 2025 y dar 2026
extraerNumeroOrden("001X");      // Debería dar 2

echo "\n=== PRUEBA COMPLETADA ===\n";

?>
