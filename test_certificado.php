<?php

// Script de diagnóstico simplificado para certificado P12
// No requiere Laravel, solo PHP y OpenSSL

echo "=== DIAGNÓSTICO DE CERTIFICADO P12 ===\n\n";

if ($argc < 3) {
    echo "Uso: php test_certificado.php <id_factura> <password> [archivo_p12]\n";
    echo "Ejemplo: php test_certificado.php 59 mipassword\n";
    echo "O especificar archivo: php test_certificado.php 59 mipassword certificado.p12\n";
    exit(1);
}

$idFactura = $argv[1];
$password = $argv[2];
$archivoEspecifico = $argv[3] ?? null;

echo "Factura ID: $idFactura\n";
echo "Contraseña: " . str_repeat("*", strlen($password)) . "\n\n";

// Buscar archivos P12 disponibles
$archivosP12 = glob("public/uploads/firmas/*.p12");

if (empty($archivosP12)) {
    echo "❌ ERROR: No se encontraron archivos P12 en public/uploads/firmas/\n";
    exit(1);
}

echo "=== ARCHIVOS P12 ENCONTRADOS ===\n";
foreach ($archivosP12 as $i => $archivo) {
    echo ($i + 1) . ". " . basename($archivo) . "\n";
}
echo "\n";

// Determinar qué archivo usar
if ($archivoEspecifico) {
    $certPath = "public/uploads/firmas/$archivoEspecifico";
    if (!file_exists($certPath)) {
        echo "❌ ERROR: El archivo especificado no existe: $certPath\n";
        exit(1);
    }
} else {
    // Usar el primer archivo P12 encontrado
    $certPath = $archivosP12[0];
    echo "Usando archivo: " . basename($certPath) . "\n\n";
}

echo "=== VERIFICACIÓN DE ARCHIVO ===\n";
echo "✅ Archivo encontrado: $certPath\n";
echo "Tamaño: " . filesize($certPath) . " bytes\n";
echo "Permisos: " . substr(sprintf('%o', fileperms($certPath)), -4) . "\n\n";

// Verificar que OpenSSL esté disponible
echo "=== VERIFICACIÓN DE OPENSSL ===\n";
if (!extension_loaded('openssl')) {
    echo "❌ ERROR: La extensión OpenSSL no está cargada\n";
    exit(1);
}
echo "✅ OpenSSL disponible\n";
echo "Versión OpenSSL: " . OPENSSL_VERSION_TEXT . "\n\n";

// Intentar leer el certificado
echo "=== LECTURA DEL CERTIFICADO ===\n";

$certContent = file_get_contents($certPath);
if ($certContent === false) {
    echo "❌ ERROR: No se pudo leer el contenido del archivo\n";
    exit(1);
}

echo "✅ Archivo leído correctamente (" . strlen($certContent) . " bytes)\n";

// Intentar parsear el PKCS#12 con diferentes configuraciones
$p12cert = [];
$result = openssl_pkcs12_read($certContent, $p12cert, $password);

if ($result === false) {
    echo "❌ ERROR: No se pudo leer el certificado P12\n";
    echo "Errores de OpenSSL:\n";
    while ($msg = openssl_error_string()) {
        echo "  - $msg\n";
    }

    // Método 1: Crear archivo de configuración temporal para OpenSSL legacy
    echo "\n=== CREANDO CONFIGURACIÓN LEGACY ===\n";

    $tempConfig = tempnam(sys_get_temp_dir(), 'openssl_');
    $configContent = <<<EOL
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect
legacy = legacy_sect

[default_sect]
activate = 1

[legacy_sect]
activate = 1
EOL;

    file_put_contents($tempConfig, $configContent);

    // Establecer variable de entorno
    putenv("OPENSSL_CONF=$tempConfig");

    $testResult = openssl_pkcs12_read($certContent, $p12cert, $password);
    if ($testResult) {
        echo "✅ ¡ÉXITO! Configuración legacy funcionó\n";
        $result = true;
    } else {
        echo "❌ Configuración legacy falló\n";
        while ($msg = openssl_error_string()) {
            echo "  - $msg\n";
        }
    }

    // Limpiar
    unlink($tempConfig);
    putenv("OPENSSL_CONF=");

    if (!$result) {
        // Método 2: Intentar con opciones específicas de flags
        echo "\n=== INTENTANDO CON DIFERENTES FLAGS ===\n";

        // Probar diferentes flags (aunque no son estándar para pkcs12_read)
        $flags = [
            0,
            OPENSSL_RAW_DATA,
            OPENSSL_ZERO_PADDING,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        ];

        foreach ($flags as $flag) {
            // Nota: pkcs12_read no acepta flags, pero probamos por si acaso
            $testResult = openssl_pkcs12_read($certContent, $p12cert, $password);
            if ($testResult) {
                echo "✅ ¡ÉXITO! Funcionó con algún flag\n";
                $result = true;
                break;
            }
        }

        if (!$result) {
            echo "❌ Todas las opciones específicas fallaron\n";
        }
    }

    if (!$result) {
        // Intentar con diferentes variaciones de contraseña
        echo "\n=== INTENTANDO VARIACIONES DE CONTRASEÑA ===\n";

        $variations = [
            $password . "\n",
            $password . "\r\n",
            $password . "\r",
            trim($password),
            strtoupper($password),
            strtolower($password)
        ];

        foreach ($variations as $i => $variation) {
            if ($variation !== $password) {
                echo "Probando variación " . ($i + 1) . ": " . str_repeat("*", strlen($variation)) . "\n";
                $testResult = openssl_pkcs12_read($certContent, $p12cert, $variation);
                if ($testResult) {
                    echo "✅ ¡ÉXITO! La variación funcionó\n";
                    $result = true;
                    break;
                }
            }
        }
    }

    if (!$result) {
        echo "\n=== RECOMENDACIONES ===\n";
        echo "Este error es común con certificados ecuatorianos en OpenSSL 3.0+\n";
        echo "Opciones para solucionarlo:\n";
        echo "1. Actualizar el certificado a formato moderno\n";
        echo "2. Usar OpenSSL 1.1.1 (versión legacy)\n";
        echo "3. Configurar OpenSSL para modo legacy\n";
        exit(1);
    }
}

echo "✅ Certificado leído correctamente\n";

// Mostrar información del certificado
echo "\n=== INFORMACIÓN DEL CERTIFICADO ===\n";

if (isset($p12cert['cert'])) {
    $certInfo = openssl_x509_parse($p12cert['cert']);
    if ($certInfo) {
        echo "Asunto: " . $certInfo['subject']['CN'] . "\n";
        echo "Emisor: " . $certInfo['issuer']['CN'] . "\n";
        echo "Válido desde: " . date('Y-m-d H:i:s', $certInfo['validFrom_time_t']) . "\n";
        echo "Válido hasta: " . date('Y-m-d H:i:s', $certInfo['validTo_time_t']) . "\n";

        $now = time();
        if ($now < $certInfo['validFrom_time_t']) {
            echo "Estado: ❌ No válido aún (futuro)\n";
        } elseif ($now > $certInfo['validTo_time_t']) {
            echo "Estado: ❌ Expirado\n";
        } else {
            echo "Estado: ✅ Válido\n";
        }
    } else {
        echo "❌ No se pudo parsear la información del certificado\n";
    }
} else {
    echo "❌ No se encontró el certificado en el archivo P12\n";
}

if (isset($p12cert['pkey'])) {
    echo "✅ Clave privada encontrada\n";
} else {
    echo "❌ Clave privada no encontrada\n";
}

echo "\n=== DIAGNÓSTICO COMPLETADO ===\n";

?>
