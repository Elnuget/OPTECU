<?php

/**
 * Script de prueba para verificar la consulta de autorización con clave específica
 * Ambiente: PRUEBAS
 */

require_once 'vendor/autoload.php';

echo "🧪 PRUEBA: Consulta de Autorización con Clave Específica\n";
echo "======================================================\n\n";

// Clave de acceso proporcionada
$claveAcceso = '2808202501172587499200110010010000015130539257810';

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
    
    // Analizar la estructura de la respuesta (mismo código del controlador)
    echo "🔍 Procesando respuesta usando lógica del controlador:\n";
    
    $autorizaciones = $response->RespuestaAutorizacionComprobante ?? null;
    
    if (!$autorizaciones) {
        throw new Exception('Respuesta inválida del SRI: no se encontraron autorizaciones');
    }
    
    echo "✅ Se encontró RespuestaAutorizacionComprobante\n";
    
    // Mostrar propiedades disponibles
    $propiedades = get_object_vars($autorizaciones);
    echo "📋 Propiedades disponibles: " . implode(', ', array_keys($propiedades)) . "\n\n";
    
    // Verificar si existe la propiedad autorizacion o autorizaciones
    $autorizacion = null;
    
    if (isset($autorizaciones->autorizacion)) {
        $autorizacion = is_array($autorizaciones->autorizacion) 
            ? $autorizaciones->autorizacion[0] 
            : $autorizaciones->autorizacion;
        echo "✅ Encontrada propiedad 'autorizacion'\n";
    } elseif (isset($autorizaciones->autorizaciones)) {
        echo "✅ Encontrada propiedad 'autorizaciones'\n";
        
        $autorizacionesData = $autorizaciones->autorizaciones;
        
        // Si la respuesta tiene 'autorizaciones', verificar si tiene contenido
        if (is_object($autorizacionesData) && property_exists($autorizacionesData, 'autorizacion')) {
            $autorizacionInterna = $autorizacionesData->autorizacion;
            $autorizacion = is_array($autorizacionInterna) ? $autorizacionInterna[0] : $autorizacionInterna;
            echo "✅ Encontrada autorización dentro de autorizaciones->autorizacion\n";
        } elseif (is_array($autorizacionesData) && count($autorizacionesData) > 0) {
            $autorizacion = $autorizacionesData[0];
            echo "✅ Encontrada autorización en array de autorizaciones\n";
        } else {
            echo "⚠️ Autorizaciones existe pero está vacío o no tiene estructura esperada\n";
            echo "📋 Estructura de autorizaciones:\n";
            print_r($autorizacionesData);
        }
    } else {
        echo "❌ No se encontró propiedad de autorización\n";
        echo "📋 Estructura completa de autorizaciones:\n";
        print_r($autorizaciones);
    }
    
    // Procesar datos como en el controlador
    $estado = 'DESCONOCIDO';
    $numeroAutorizacion = null;
    $fechaAutorizacion = null;
    $ambiente = null;
    $comprobante = null;
    $mensajes = [];
    
    if ($autorizacion) {
        echo "✅ Procesando autorización encontrada\n";
        
        // Extraer datos de la autorización
        $estado = $autorizacion->estado ?? 'DESCONOCIDO';
        $numeroAutorizacion = $autorizacion->numeroAutorizacion ?? null;
        $fechaAutorizacion = $autorizacion->fechaAutorizacion ?? null;
        $ambiente = $autorizacion->ambiente ?? null;
        $comprobante = $autorizacion->comprobante ?? null;
        
        // Procesar mensajes
        if (isset($autorizacion->mensajes) && isset($autorizacion->mensajes->mensaje)) {
            $mensajesSri = is_array($autorizacion->mensajes->mensaje) 
                ? $autorizacion->mensajes->mensaje 
                : [$autorizacion->mensajes->mensaje];
            
            foreach ($mensajesSri as $mensaje) {
                $mensajes[] = [
                    'identificador' => $mensaje->identificador ?? null,
                    'mensaje' => $mensaje->mensaje ?? 'Mensaje sin descripción',
                    'informacionAdicional' => $mensaje->informacionAdicional ?? null,
                    'tipo' => $mensaje->tipo ?? 'INFO'
                ];
            }
        }
    }
    
    // Si no hay autorizaciones pero hay mensajes de error, extraerlos
    if ($estado === 'DESCONOCIDO' && empty($mensajes)) {
        echo "⚠️ No se encontró autorización, buscando mensajes generales...\n";
        
        // Buscar mensajes en otros lugares de la respuesta
        if (isset($autorizaciones->mensajes)) {
            $mensajesGenerales = is_array($autorizaciones->mensajes) 
                ? $autorizaciones->mensajes 
                : [$autorizaciones->mensajes];
            
            foreach ($mensajesGenerales as $mensaje) {
                if (is_object($mensaje)) {
                    $mensajes[] = [
                        'identificador' => $mensaje->identificador ?? null,
                        'mensaje' => $mensaje->mensaje ?? 'Error en procesamiento',
                        'informacionAdicional' => $mensaje->informacionAdicional ?? null,
                        'tipo' => $mensaje->tipo ?? 'ERROR'
                    ];
                }
            }
        }
        
        // Si aún no hay mensajes, indicar que no se encontró la factura
        if (empty($mensajes)) {
            $mensajes[] = [
                'identificador' => 'NO_ENCONTRADA',
                'mensaje' => 'No se encontró información de autorización para esta clave de acceso',
                'informacionAdicional' => 'La factura puede no haber sido enviada al SRI o la clave de acceso no es válida',
                'tipo' => 'WARNING'
            ];
            // Usar estado válido del ENUM: DEVUELTA para casos donde no se encuentra
            $estado = 'DEVUELTA';
        }
    }
    
    // Mapear estado a ENUM válido
    $mapeoEstados = [
        'AUTORIZADA' => 'AUTORIZADA',
        'AUTORIZADO' => 'AUTORIZADA',
        'NO_AUTORIZADA' => 'NO_AUTORIZADA',
        'NO_AUTORIZADO' => 'NO_AUTORIZADA',
        'DEVUELTA' => 'DEVUELTA',
        'DEVUELTO' => 'DEVUELTA',
        'RECIBIDA' => 'RECIBIDA',
        'RECIBIDO' => 'RECIBIDA',
        'EN_PROCESO' => 'RECIBIDA',
        'PROCESANDO' => 'RECIBIDA',
        'DESCONOCIDO' => 'DEVUELTA',
        'ERROR' => 'DEVUELTA'
    ];
    
    $estadoMapeado = $mapeoEstados[$estado] ?? 'DEVUELTA';
    
    echo "\n📊 Resultado final del procesamiento:\n";
    echo "- Estado original: {$estado}\n";
    echo "- Estado mapeado (para ENUM): {$estadoMapeado}\n";
    echo "- Número de autorización: " . ($numeroAutorizacion ?? 'N/A') . "\n";
    echo "- Fecha de autorización: " . ($fechaAutorizacion ?? 'N/A') . "\n";
    echo "- Ambiente: " . ($ambiente ?? 'N/A') . "\n";
    echo "- Comprobante disponible: " . ($comprobante ? 'SÍ' : 'NO') . "\n";
    
    if (!empty($mensajes)) {
        echo "- Mensajes (" . count($mensajes) . "):\n";
        foreach ($mensajes as $i => $mensaje) {
            echo "  " . ($i + 1) . ". " . $mensaje['mensaje'] . "\n";
            if ($mensaje['identificador']) {
                echo "     Código: " . $mensaje['identificador'] . "\n";
            }
            if ($mensaje['informacionAdicional']) {
                echo "     Detalle: " . $mensaje['informacionAdicional'] . "\n";
            }
        }
    }
    
    // Simular datos para actualización de BD
    echo "\n�️ Datos que se guardarían en la BD:\n";
    echo "- estado_sri: '{$estadoMapeado}'\n";
    echo "- mensajes_sri: '" . json_encode($mensajes) . "'\n";
    if ($estado === 'AUTORIZADA') {
        echo "- estado: 'AUTORIZADA'\n";
        echo "- numero_autorizacion: '{$numeroAutorizacion}'\n";
        echo "- fecha_autorizacion: '{$fechaAutorizacion}'\n";
        if ($comprobante) {
            echo "- xml_autorizado: [XML de " . strlen($comprobante) . " caracteres]\n";
        }
    }
    
    echo "\n✅ PRUEBA COMPLETADA EXITOSAMENTE\n";
    
} catch (SoapFault $e) {
    echo "❌ Error SOAP:\n";
    echo "- Código: " . ($e->faultcode ?? 'N/A') . "\n";
    echo "- Mensaje: " . $e->getMessage() . "\n";
    echo "- Detalle: " . ($e->faultstring ?? 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n======================================================\n";
echo "🏁 Prueba finalizada\n";
