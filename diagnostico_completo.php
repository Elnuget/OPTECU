<?php

// Script completo de diagnóstico y solución para certificados P12
echo "=== DIAGNÓSTICO COMPLETO DE CERTIFICADO P12 ===\n\n";

$facturaId = $argv[1] ?? null;
$password = $argv[2] ?? null;

if (!$facturaId || !$password) {
    echo "Uso: php diagnostico_completo.php <factura_id> <password>\n";
    echo "Ejemplo: php diagnostico_completo.php 59 mipassword\n";
    exit(1);
}

echo "Factura ID: $facturaId\n";
echo "Contraseña: " . str_repeat("*", strlen($password)) . "\n\n";

// Buscar archivos P12 disponibles
$archivosP12 = glob("public/uploads/firmas/*.p12");

if (empty($archivosP12)) {
    echo "❌ ERROR: No se encontraron archivos P12\n";
    exit(1);
}

$certPath = $archivosP12[0];
echo "=== ARCHIVO ENCONTRADO ===\n";
echo "Ubicación: $certPath\n";
echo "Tamaño: " . filesize($certPath) . " bytes\n\n";

echo "=== INFORMACIÓN DEL SISTEMA ===\n";
echo "Versión de PHP: " . PHP_VERSION . "\n";
echo "Versión de OpenSSL: " . OPENSSL_VERSION_TEXT . "\n";
echo "Sistema Operativo: " . PHP_OS . "\n\n";

echo "=== DIAGNÓSTICO DEL PROBLEMA ===\n";

// Verificar si es el error específico de OpenSSL 3.0+
if (version_compare(PHP_VERSION, '8.0', '>=') && strpos(OPENSSL_VERSION_TEXT, 'OpenSSL 3') === 0) {
    echo "⚠️  DETECTADO: Sistema con OpenSSL 3.0+ que puede tener problemas con certificados antiguos\n\n";
} else {
    echo "✅ Sistema compatible con certificados antiguos\n\n";
}

echo "=== INTENTANDO SOLUCIONES ===\n";

// Solución 1: Configuración legacy
echo "1. Probando configuración OpenSSL legacy...\n";
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
putenv("OPENSSL_CONF=$tempConfig");

$certContent = file_get_contents($certPath);
$certData = [];
$result = openssl_pkcs12_read($certContent, $certData, $password);

if ($result) {
    echo "✅ ¡ÉXITO! Configuración legacy funcionó\n";
    echo "Certificado válido desde: " . date('Y-m-d H:i:s', openssl_x509_parse($certData['cert'])['validFrom_time_t']) . "\n";
    echo "Certificado válido hasta: " . date('Y-m-d H:i:s', openssl_x509_parse($certData['cert'])['validTo_time_t']) . "\n";
    unlink($tempConfig);
    exit(0);
} else {
    echo "❌ Configuración legacy falló\n";
    $error = openssl_error_string();
    echo "Error: $error\n";
}

unlink($tempConfig);
putenv("OPENSSL_CONF=");

// Solución 2: Verificar contraseña con variaciones
echo "\n2. Probando variaciones de contraseña...\n";
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
        $certData = [];
        if (openssl_pkcs12_read($certContent, $certData, $variation)) {
            echo "✅ ¡ÉXITO! La variación " . ($i + 1) . " funcionó: " . str_repeat("*", strlen($variation)) . "\n";
            echo "Sugerencia: Use esta variación de contraseña\n";
            exit(0);
        }
    }
}
echo "❌ Ninguna variación de contraseña funcionó\n";

// Solución 3: Verificar si el archivo está corrupto
echo "\n3. Verificando integridad del archivo...\n";
$certHex = bin2hex(substr($certContent, 0, 20));
if (strpos($certHex, '3082') === 0 || strpos($certHex, '3081') === 0) {
    echo "✅ El archivo parece ser un PKCS#12 válido\n";
} else {
    echo "❌ El archivo no parece ser un PKCS#12 válido\n";
    echo "Hex dump inicial: $certHex\n";
}

echo "\n=== RECOMENDACIONES PARA SOLUCIONAR ===\n";
echo "Este problema es común con certificados digitales ecuatorianos antiguos.\n\n";

echo "OPCIÓN 1 - Actualizar el certificado (RECOMENDADO):\n";
echo "• Obtenga un nuevo certificado digital del SRI\n";
echo "• Los nuevos certificados son compatibles con OpenSSL 3.0+\n";
echo "• Proceso: Ingrese al portal del SRI y solicite renovación\n\n";

echo "OPCIÓN 2 - Usar OpenSSL 1.1.1:\n";
echo "• Desinstale XAMPP actual\n";
echo "• Instale XAMPP con PHP 7.4 (que incluye OpenSSL 1.1.1)\n";
echo "• Descargue desde: https://sourceforge.net/projects/xampp/files/\n\n";

echo "OPCIÓN 3 - Configuración manual:\n";
echo "• Edite el archivo C:\\xampp\\apache\\conf\\openssl.cnf\n";
echo "• Asegúrese de que tenga las secciones [legacy_sect] y [default_sect]\n";
echo "• Reinicie Apache después de los cambios\n\n";

echo "OPCIÓN 4 - Solución temporal:\n";
echo "• Use un certificado de prueba generado con OpenSSL 1.1.1\n";
echo "• Configure su aplicación para usar este certificado de prueba\n\n";

echo "Para más información, consulte:\n";
echo "• https://www.openssl.org/docs/man3.0/man7/migration_guide.html\n";
echo "• https://www.php.net/manual/es/openssl.installation.php\n";

exit(1);

?>
