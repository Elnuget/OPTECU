<?php

namespace App\Services\Factura;

class FacturaCalculationService
{
    /**
     * Calcular subtotal de elementos exentos de IVA
     *
     * @param array $elementos
     * @return float
     */
    public function calcularSubtotalExento(array $elementos)
    {
        $subtotalExento = 0;
        
        foreach ($elementos as $elemento) {
            if (isset($elemento['iva_porcentaje']) && $elemento['iva_porcentaje'] == 0) {
                $subtotalExento += floatval($elemento['precio'] ?? 0);
            }
        }
        
        return $subtotalExento;
    }

    /**
     * Calcular dígito verificador usando módulo 11
     * Según especificaciones del SRI Ecuador
     *
     * @param string $claveAcceso48
     * @return int
     */
    public function calcularDigitoVerificador(string $claveAcceso48)
    {
        try {
            // Validar que la clave tenga exactamente 48 dígitos
            if (strlen($claveAcceso48) !== 48) {
                throw new \InvalidArgumentException("La clave de acceso debe tener exactamente 48 dígitos, recibidos: " . strlen($claveAcceso48));
            }

            // Validar que solo contenga números
            if (!ctype_digit($claveAcceso48)) {
                throw new \InvalidArgumentException("La clave de acceso debe contener solo números");
            }

            $factor = 7;
            $suma = 0;
            
            // Recorrer los 48 dígitos de derecha a izquierda
            for ($i = 47; $i >= 0; $i--) {
                $suma += intval($claveAcceso48[$i]) * $factor;
                $factor--;
                if ($factor == 1) {
                    $factor = 7; // Reiniciar el factor cuando llegue a 1
                }
            }
            
            $residuo = $suma % 11;
            $digitoVerificador = 11 - $residuo;
            
            // Casos especiales según normativa del SRI
            if ($digitoVerificador == 11) {
                $digitoVerificador = 0;
            } elseif ($digitoVerificador == 10) {
                $digitoVerificador = 1;
            }
            
            \Log::info('Cálculo de dígito verificador completado', [
                'clave_48_digitos' => $claveAcceso48,
                'suma_total' => $suma,
                'residuo' => $residuo,
                'digito_verificador' => $digitoVerificador
            ]);
            
            return $digitoVerificador;
            
        } catch (\Exception $e) {
            \Log::error('Error calculando dígito verificador: ' . $e->getMessage(), [
                'clave_acceso_48' => $claveAcceso48
            ]);
            throw $e;
        }
    }

    /**
     * Calcular totales de una factura según los elementos seleccionados
     *
     * @param array $elementos
     * @return array
     */
    public function calcularTotalesFactura(array $elementos)
    {
        $subtotal = 0;
        $iva = 0;
        $subtotalExento = 0;
        $subtotalGravado = 0;
        
        foreach ($elementos as $elemento) {
            $precio = floatval($elemento['precio'] ?? 0);
            $ivaPorc = floatval($elemento['iva_porcentaje'] ?? 0);
            
            $subtotal += $precio;
            
            if ($ivaPorc > 0) {
                $subtotalGravado += $precio;
                $iva += $precio * ($ivaPorc / 100);
            } else {
                $subtotalExento += $precio;
            }
        }
        
        $total = $subtotal + $iva;
        
        return [
            'subtotal' => round($subtotal, 2),
            'subtotal_exento' => round($subtotalExento, 2),
            'subtotal_gravado' => round($subtotalGravado, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2)
        ];
    }
}
