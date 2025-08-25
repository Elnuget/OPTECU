<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura Electrónica Autorizada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #e9ecef;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 0.9em;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
            border-radius: 3px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #dee2e6;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .success-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            display: inline-block;
            margin: 10px 0;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Factura Electrónica Autorizada</h1>
        <p>Su factura ha sido procesada y autorizada exitosamente por el SRI</p>
    </div>

    <div class="content">
        <p>Estimado cliente,</p>
        
        <p>Nos complace informarle que su factura electrónica ha sido <strong>autorizada exitosamente</strong> por el Servicio de Rentas Internas (SRI) de Ecuador.</p>

        <div class="success-badge">
            ✅ FACTURA AUTORIZADA
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">Información de la Factura</h3>
            
            <div class="info-row">
                <span class="info-label">Número de Factura:</span>
                <span class="info-value">{{ $numeroFactura }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Número de Autorización:</span>
                <span class="info-value">{{ $numeroAutorizacion }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Fecha de Autorización:</span>
                <span class="info-value">{{ $fechaAutorizacion ? $fechaAutorizacion->format('d/m/Y H:i:s') : 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Total:</span>
                <span class="info-value">${{ number_format($total, 2) }}</span>
            </div>
        </div>

        <div class="warning">
            <strong>📎 Archivo Adjunto:</strong> En este correo encontrará adjunto el archivo XML de su factura autorizada. Este documento tiene validez legal y fiscal ante el SRI.
        </div>

        <p><strong>¿Qué significa esto?</strong></p>
        <ul>
            <li>Su factura es válida ante el SRI de Ecuador</li>
            <li>Puede usar este documento para sus declaraciones tributarias</li>
            <li>El archivo XML adjunto contiene toda la información fiscal necesaria</li>
            <li>Esta factura electrónica tiene la misma validez que una factura física</li>
        </ul>

        <p>Si tiene alguna pregunta sobre esta factura, no dude en contactarnos.</p>

        <p>Gracias por confiar en nosotros.</p>
        
        <p style="margin-top: 30px;">
            <strong>Equipo OPTECU</strong><br>
            Sistema de Facturación Electrónica
        </p>
    </div>

    <div class="footer">
        <p style="margin: 0;">Este es un mensaje automático del sistema de facturación electrónica de OPTECU</p>
        <p style="margin: 5px 0 0 0;">📧 No responda a este correo - Para consultas contáctenos por nuestros canales oficiales</p>
    </div>
</body>
</html>
