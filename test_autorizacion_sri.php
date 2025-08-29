<?php

/**
 * Script de prueba para verificar la funcionalidad de consulta de autorización SRI
 * Ambiente: PRUEBAS
 */

require_once 'vendor/autoload.php';

echo "🧪 PRUEBA: Consulta de Autorización SRI\n";
echo "=====================================\n\n";

// Simular consulta con clave de acceso de prueba
$claveAcceso = '2808202501179214210700110010010000000011234567890';

echo "📋 Información de prueba:\n";
echo "- Clave de acceso: {$claveAcceso}\n";
echo "- Ambiente: PRUEBAS\n";
echo "- URL: https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl\n\n";

try {
    // Validar ambiente de pruebas
    $envPath = 'public/SriSignXml/.env';
    
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        
        if (strpos($envContent, 'cel.sri.gob.ec') !== false && 
            strpos($envContent, 'celcer.sri.gob.ec') === false) {
            throw new Exception('⚠️ ADVERTENCIA: Se detectaron URLs de PRODUCCIÓN');
        }
        
        echo "✅ Ambiente de pruebas validado\n\n";
    }
    
    // URL del servicio de autorización (PRUEBAS)
    $wsdlUrl = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
    
    echo "🔄 Iniciando consulta SOAP...\n";
    
    // Crear cliente SOAP
    $client = new SoapClient($wsdlUrl, [
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'stream_context' => stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ])
    ]);
    
    echo "✅ Cliente SOAP creado exitosamente\n";
    
    // Parámetros de la consulta
    $params = [
        'claveAccesoComprobante' => $claveAcceso
    ];
    
    echo "📤 Enviando consulta...\n";
    
    // Realizar la consulta
    $response = $client->autorizacionComprobante($params);
    
    echo "📥 Respuesta recibida:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
    
    // Analizar la estructura de la respuesta
    echo "🔍 Análisis de la respuesta:\n";
    
    $autorizaciones = $response->RespuestaAutorizacionComprobante ?? null;
    
    if ($autorizaciones) {
        echo "✅ Se encontró RespuestaAutorizacionComprobante\n";
        
        // Mostrar propiedades disponibles
        $propiedades = get_object_vars($autorizaciones);
        echo "📋 Propiedades disponibles: " . implode(', ', array_keys($propiedades)) . "\n\n";
        
        // Buscar autorización
        $autorizacion = null;
        
        if (isset($autorizaciones->autorizacion)) {
            $autorizacion = is_array($autorizaciones->autorizacion) 
                ? $autorizaciones->autorizacion[0] 
                : $autorizaciones->autorizacion;
            echo "✅ Encontrada propiedad 'autorizacion'\n";
        } elseif (isset($autorizaciones->autorizaciones)) {
            $autorizacion = is_array($autorizaciones->autorizaciones) 
                ? $autorizaciones->autorizaciones[0] 
                : $autorizaciones->autorizaciones;
            echo "✅ Encontrada propiedad 'autorizaciones'\n";
        } else {
            echo "❌ No se encontró propiedad de autorización\n";
            echo "📋 Estructura completa de la respuesta:\n";
            print_r($autorizaciones);
        }
        
        if ($autorizacion) {
            echo "\n📊 Datos de autorización:\n";
            echo "- Estado: " . ($autorizacion->estado ?? 'N/A') . "\n";
            echo "- Número autorización: " . ($autorizacion->numeroAutorizacion ?? 'N/A') . "\n";
            echo "- Fecha autorización: " . ($autorizacion->fechaAutorizacion ?? 'N/A') . "\n";
            echo "- Ambiente: " . ($autorizacion->ambiente ?? 'N/A') . "\n";
            
            // Mensajes
            if (isset($autorizacion->mensajes) && isset($autorizacion->mensajes->mensaje)) {
                echo "- Mensajes:\n";
                $mensajes = is_array($autorizacion->mensajes->mensaje) 
                    ? $autorizacion->mensajes->mensaje 
                    : [$autorizacion->mensajes->mensaje];
                
                foreach ($mensajes as $i => $mensaje) {
                    echo "  " . ($i + 1) . ". " . ($mensaje->mensaje ?? 'Sin mensaje') . "\n";
                    if (isset($mensaje->identificador)) {
                        echo "     Código: " . $mensaje->identificador . "\n";
                    }
                    if (isset($mensaje->informacionAdicional)) {
                        echo "     Detalle: " . $mensaje->informacionAdicional . "\n";
                    }
                }
            }
        }
        
        // Verificar mensajes generales
        if (isset($autorizaciones->mensajes)) {
            echo "\n📨 Mensajes generales:\n";
            $mensajesGenerales = is_array($autorizaciones->mensajes) 
                ? $autorizaciones->mensajes 
                : [$autorizaciones->mensajes];
            
            foreach ($mensajesGenerales as $i => $mensaje) {
                echo "  " . ($i + 1) . ". " . ($mensaje->mensaje ?? 'Sin mensaje') . "\n";
                if (isset($mensaje->identificador)) {
                    echo "     Código: " . $mensaje->identificador . "\n";
                }
            }
        }
    } else {
        echo "❌ No se encontró RespuestaAutorizacionComprobante\n";
        echo "📋 Estructura completa de la respuesta:\n";
        print_r($response);
    }
    
    echo "\n✅ PRUEBA COMPLETADA EXITOSAMENTE\n";
    
} catch (SoapFault $e) {
    echo "❌ Error SOAP:\n";
    echo "- Código: " . ($e->faultcode ?? 'N/A') . "\n";
    echo "- Mensaje: " . $e->getMessage() . "\n";
    echo "- Detalle: " . ($e->faultstring ?? 'N/A') . "\n";
    
    if (method_exists($e, 'getTraceAsString')) {
        echo "- Trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=====================================\n";
echo "🏁 Prueba finalizada\n";
