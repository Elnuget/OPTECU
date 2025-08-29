<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verificando campo xml_autorizado en tabla facturas:\n";
$result = DB::select("SHOW COLUMNS FROM facturas WHERE Field = 'xml_autorizado'");

if (!empty($result)) {
    $column = $result[0];
    echo "Campo encontrado:\n";
    echo "- Field: " . $column->Field . "\n";
    echo "- Type: " . $column->Type . "\n";
    echo "- Null: " . $column->Null . "\n";
    echo "- Comment: " . ($column->Comment ?? 'Sin comentario') . "\n";
} else {
    echo "Campo xml_autorizado NO encontrado!\n";
}

echo "\nVerificando si hay facturas con XML autorizado:\n";
$facturas = DB::select("SELECT id, clave_acceso, estado_sri, 
    CASE 
        WHEN xml_autorizado IS NULL THEN 'NULL'
        WHEN xml_autorizado = '' THEN 'VACÍO'
        ELSE CONCAT('CON CONTENIDO (', LENGTH(xml_autorizado), ' caracteres)')
    END as xml_status
    FROM facturas 
    WHERE estado_sri = 'AUTORIZADA' 
    LIMIT 5");

foreach ($facturas as $factura) {
    echo "- Factura ID: {$factura->id}, Clave: {$factura->clave_acceso}, Estado: {$factura->estado_sri}, XML: {$factura->xml_status}\n";
}

if (empty($facturas)) {
    echo "No hay facturas autorizadas en la base de datos.\n";
}
