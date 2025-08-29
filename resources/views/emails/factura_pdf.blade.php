<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $numero_factura }} - {{ $declarante->nombre ?? 'OPTECU' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        
        .email-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .content {
            margin-bottom: 25px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .factura-info {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        
        .factura-info h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 18px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        
        .pdf-button {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-pdf {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-pdf:hover {
            background-color: #c82333;
            color: white;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #666;
        }
        
        .company-info {
            margin-top: 15px;
            font-size: 11px;
            color: #777;
        }
        
        .note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
            font-size: 13px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-container {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                margin-bottom: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Encabezado -->
        <div class="header">
            <h1>{{ $declarante->nombre ?? 'OPTECU' }}</h1>
            <p>Sistema de Facturación Electrónica</p>
        </div>

        <!-- Contenido -->
        <div class="content">
            <div class="greeting">
                Estimado/a {{ $cliente_nombre }},
            </div>

            <p>Esperamos que se encuentre muy bien. Le enviamos su factura electrónica correspondiente a su reciente compra.</p>

            <!-- Información de la factura -->
            <div class="factura-info">
                <h3>📄 Detalles de la Factura</h3>
                
                <div class="info-row">
                    <span class="info-label">Número de Factura:</span>
                    <span class="info-value">#{{ $numero_factura }}</span>
                </div>
                
                @if($pedido && $pedido->numero_orden)
                <div class="info-row">
                    <span class="info-label">Número de Orden:</span>
                    <span class="info-value">{{ $pedido->numero_orden }}</span>
                </div>
                @endif
                
                <div class="info-row">
                    <span class="info-label">Fecha de Emisión:</span>
                    <span class="info-value">{{ $factura->created_at ? $factura->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
                
                @if($declarante)
                <div class="info-row">
                    <span class="info-label">Empresa:</span>
                    <span class="info-value">{{ $declarante->nombre }}</span>
                </div>
                
                @if($declarante->ruc)
                <div class="info-row">
                    <span class="info-label">RUC:</span>
                    <span class="info-value">{{ $declarante->ruc }}</span>
                </div>
                @endif
                @endif
                
                <div class="info-row">
                    <span class="info-label">Total:</span>
                    <span class="info-value total-amount">${{ $total_factura }}</span>
                </div>
            </div>

            <!-- Botón para ver PDF -->
            <div class="pdf-button">
                <a href="{{ $pdf_url }}" target="_blank" class="btn-pdf">
                    📄 Ver Factura en PDF
                </a>
            </div>

            <!-- Nota informativa -->
            <div class="note">
                <strong>📌 Importante:</strong> Haga clic en el botón de arriba para abrir su factura en formato PDF. 
                Puede descargar e imprimir el documento para sus registros.
            </div>

            <p>Si tiene alguna pregunta sobre esta factura o necesita asistencia adicional, no dude en contactarnos.</p>

            <p>
                Gracias por confiar en nosotros.<br>
                <strong>Equipo de {{ $declarante->nombre ?? 'OPTECU' }}</strong>
            </p>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p><strong>{{ $declarante->nombre ?? 'OPTECU' }}</strong></p>
            @if($declarante && $declarante->direccion)
            <p>{{ $declarante->direccion }}</p>
            @endif
            @if($declarante && $declarante->telefono)
            <p>Teléfono: {{ $declarante->telefono }}</p>
            @endif
            
            <div class="company-info">
                <p>Este es un mensaje automático, por favor no responda a este correo.</p>
                <p>Factura generada el {{ now()->format('d/m/Y H:i:s') }}</p>
                <p>&copy; {{ date('Y') }} Sistema OPTECU - Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
