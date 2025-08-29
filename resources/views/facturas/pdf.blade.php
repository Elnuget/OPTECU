<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $datosFactura['comprobante']['secuencial'] ?? $factura->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .header {
            border: 2px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        
        .header-grid {
            display: grid;
            grid-template-columns: 150px 2fr 1fr;
            gap: 20px;
            align-items: start;
        }
        
        .logo-section {
            text-align: center;
        }
        
        .logo-section img {
            max-width: 120px;
            max-height: 120px;
            object-fit: contain;
        }
        
        .company-info h1 {
            font-size: 18px;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .company-info p {
            margin: 2px 0;
            font-size: 10px;
        }
        
        .document-info {
            text-align: center;
            border: 1px solid #dee2e6;
            padding: 10px;
            background-color: white;
        }
        
        .document-info h2 {
            font-size: 14px;
            margin-bottom: 5px;
            color: #dc3545;
        }
        
        .document-number {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .authorization-info {
            font-size: 9px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-section {
            border: 1px solid #dee2e6;
            padding: 10px;
            background-color: #f8f9fa;
        }
        
        .info-section h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #007bff;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 100px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .items-table td.number {
            text-align: right;
        }
        
        .items-table td.center {
            text-align: center;
        }
        
        .totals-section {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .payment-info {
            border: 1px solid #dee2e6;
            padding: 10px;
            background-color: #f8f9fa;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .totals-table td:first-child {
            font-weight: bold;
            text-align: right;
        }
        
        .totals-table td:last-child {
            text-align: right;
            min-width: 80px;
        }
        
        .total-final {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .actions {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .access-key {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            word-break: break-all;
            background-color: #f8f9fa;
            padding: 5px;
            border: 1px solid #dee2e6;
            margin: 10px 0;
        }
        
        .login-required {
            background-color: #6c757d !important;
            color: white !important;
            opacity: 0.6 !important;
            cursor: not-allowed !important;
            font-size: 10px !important;
        }
        
        @media print {
            .actions {
                display: none;
            }
            
            body {
                font-size: 9px;
            }
            
            .container {
                padding: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Acciones de la vista -->
        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">
                🖨️ Imprimir
            </button>
            @auth
                @if(isset($factura))
                <a href="{{ route('facturas.show', $factura->id) }}" class="btn btn-secondary">
                    ← Volver a Factura
                </a>
                <a href="{{ route('facturas.index') }}" class="btn btn-secondary">
                    📋 Lista de Facturas
                </a>
                @endif
            @else
                <span class="btn login-required" title="Debes iniciar sesión para acceder a las funciones administrativas">
                    🔒 Funciones administrativas requieren login
                </span>
            @endauth
        </div>

        <!-- Encabezado de la factura -->
        <div class="header">
            <div class="header-grid">
                <!-- Logo de AdminLTE -->
                <div class="logo-section">
                    <img src="{{ asset('AdminLTELogo.png') }}" alt="Logo OPTECU" />
                    <p style="font-size: 8px; margin-top: 5px; color: #666;">Sistema OPTECU</p>
                </div>
                
                <div class="company-info">
                    <h1>{{ $datosFactura['emisor']['razon_social'] ?? ($factura->declarante->nombre ?? 'EMPRESA') }}</h1>
                    @if(!empty($datosFactura['emisor']['nombre_comercial']))
                        <p><strong>Nombre Comercial:</strong> {{ $datosFactura['emisor']['nombre_comercial'] }}</p>
                    @endif
                    <p><strong>RUC:</strong> {{ $datosFactura['emisor']['ruc'] ?? ($factura->declarante->ruc ?? 'N/A') }}</p>
                    <p><strong>Dirección:</strong> {{ $datosFactura['emisor']['direccion'] ?? ($factura->declarante->direccion ?? 'N/A') }}</p>
                    @if(!empty($datosFactura['comprobante']['direccion_matriz']))
                        <p><strong>Dirección Matriz:</strong> {{ $datosFactura['comprobante']['direccion_matriz'] }}</p>
                    @endif
                    @if(!empty($datosFactura['comprobante']['contribuyente_especial']))
                        <p><strong>Contribuyente Especial:</strong> {{ $datosFactura['comprobante']['contribuyente_especial'] }}</p>
                    @endif
                    <p><strong>Obligado a llevar Contabilidad:</strong> {{ $datosFactura['comprobante']['obligado_contabilidad'] ?? 'N/A' }}</p>
                </div>
                
                <div class="document-info">
                    <h2>FACTURA</h2>
                    <div class="document-number">
                        No. {{ sprintf('%03d-%03d-%09d', 
                            $datosFactura['comprobante']['establecimiento'] ?? 0,
                            $datosFactura['comprobante']['punto_emision'] ?? 0,
                            $datosFactura['comprobante']['secuencial'] ?? $factura->id
                        ) }}
                    </div>
                    <p><strong>Fecha Emisión:</strong> {{ $datosFactura['comprobante']['fecha_emision'] ?? ($factura->created_at ? $factura->created_at->format('d/m/Y') : 'N/A') }}</p>
                    @if(!empty($datosFactura['modelo']['numero_autorizacion']))
                        <div class="authorization-info">
                            <p><strong>AUTORIZACIÓN SRI</strong></p>
                            <p>{{ $datosFactura['modelo']['numero_autorizacion'] }}</p>
                            <p>{{ $datosFactura['modelo']['fecha_autorizacion'] }}</p>
                            <p>Ambiente: {{ $datosFactura['comprobante']['ambiente'] == '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}</p>
                            <p>Emisión: {{ $datosFactura['comprobante']['tipo_emision'] == '1' ? 'NORMAL' : 'CONTINGENCIA' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Información del cliente -->
        @if(!empty($datosFactura['comprador']))
        <div class="info-section" style="margin-bottom: 20px;">
            <h3>🏢 DATOS DEL CLIENTE</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <div class="info-row">
                        <span class="info-label">Identificación:</span>
                        <span class="info-value">{{ $datosFactura['comprador']['identificacion'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Razón Social:</span>
                        <span class="info-value">{{ $datosFactura['comprador']['razon_social'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dirección:</span>
                        <span class="info-value">{{ $datosFactura['comprador']['direccion'] }}</span>
                    </div>
                </div>
                <div>
                    @if(!empty($datosFactura['comprador']['email']))
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $datosFactura['comprador']['email'] }}</span>
                    </div>
                    @endif
                    @if(!empty($datosFactura['comprador']['telefono']))
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value">{{ $datosFactura['comprador']['telefono'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Detalles de la factura -->
        @if(!empty($datosFactura['detalles']))
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Código</th>
                    <th>Descripción</th>
                    <th style="width: 60px;">Cant.</th>
                    <th style="width: 80px;">P. Unit.</th>
                    <th style="width: 80px;">Descuento</th>
                    <th style="width: 80px;">P. Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosFactura['detalles'] as $detalle)
                <tr>
                    <td class="center">{{ $detalle['codigo_principal'] }}</td>
                    <td>{{ $detalle['descripcion'] }}</td>
                    <td class="center">{{ number_format($detalle['cantidad'], 2) }}</td>
                    <td class="number">${{ number_format($detalle['precio_unitario'], 2) }}</td>
                    <td class="number">${{ number_format($detalle['descuento'], 2) }}</td>
                    <td class="number">${{ number_format($detalle['precio_total_sin_impuesto'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Sección de totales y pagos -->
        <div class="totals-section">
            <!-- Formas de pago -->
            <div class="payment-info">
                <h3>💳 FORMA DE PAGO</h3>
                @if(!empty($datosFactura['pagos']))
                    @foreach($datosFactura['pagos'] as $pago)
                        <div class="info-row">
                            <span class="info-label">
                                @switch($pago['forma_pago'])
                                    @case('01') Efectivo @break
                                    @case('15') Transferencia @break
                                    @case('16') Tarjeta Débito @break
                                    @case('17') Tarjeta Crédito @break
                                    @case('18') Cheque @break
                                    @default Forma {{ $pago['forma_pago'] }}
                                @endswitch
                            </span>
                            <span class="info-value">${{ number_format($pago['total'], 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <p>Sin información</p>
                @endif
            </div>

            <!-- Totales -->
            <div>
                <table class="totals-table">
                    @if(!empty($datosFactura['totales']['subtotal_15']) && $datosFactura['totales']['subtotal_15'] > 0)
                    <tr>
                        <td>SUBTOTAL 15%:</td>
                        <td>${{ number_format($datosFactura['totales']['subtotal_15'], 2) }}</td>
                    </tr>
                    @endif
                    @if(!empty($datosFactura['totales']['subtotal_0']) && $datosFactura['totales']['subtotal_0'] > 0)
                    <tr>
                        <td>SUBTOTAL 0%:</td>
                        <td>${{ number_format($datosFactura['totales']['subtotal_0'], 2) }}</td>
                    </tr>
                    @endif
                    @if(!empty($datosFactura['totales']['subtotal_sin_impuesto']) && $datosFactura['totales']['subtotal_sin_impuesto'] > 0)
                    <tr>
                        <td>SUBTOTAL SIN IMPUESTO:</td>
                        <td>${{ number_format($datosFactura['totales']['subtotal_sin_impuesto'], 2) }}</td>
                    </tr>
                    @endif
                    @if(!empty($datosFactura['totales']['total_descuento']) && $datosFactura['totales']['total_descuento'] > 0)
                    <tr>
                        <td>DESCUENTO:</td>
                        <td>${{ number_format($datosFactura['totales']['total_descuento'], 2) }}</td>
                    </tr>
                    @endif
                    @if(!empty($datosFactura['impuestos']))
                        @foreach($datosFactura['impuestos'] as $impuesto)
                        <tr>
                            <td>
                                @if($impuesto['codigo'] == '2')
                                    @if($impuesto['codigo_porcentaje'] == '4')
                                        IVA 15%:
                                    @elseif($impuesto['codigo_porcentaje'] == '0')
                                        IVA 0%:
                                    @else
                                        IVA {{ number_format($impuesto['tarifa'], 0) }}%:
                                    @endif
                                @else
                                    IMPUESTO ({{ $impuesto['codigo'] }}):
                                @endif
                            </td>
                            <td>${{ number_format($impuesto['valor'], 2) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>IVA:</td>
                            <td>${{ number_format($factura->iva ?? 0, 2) }}</td>
                        </tr>
                    @endif
                    @if(!empty($datosFactura['totales']['propina']) && $datosFactura['totales']['propina'] > 0)
                    <tr>
                        <td>PROPINA:</td>
                        <td>${{ number_format($datosFactura['totales']['propina'], 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-final">
                        <td>TOTAL:</td>
                        <td>${{ number_format($datosFactura['totales']['importe_total'] ?? $factura->total ?? 0, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Clave de acceso -->
        @if(!empty($datosFactura['comprobante']['clave_acceso']))
        <div class="access-key">
            <strong>CLAVE DE ACCESO:</strong><br>
            {{ $datosFactura['comprobante']['clave_acceso'] }}
        </div>
        @endif

        <!-- Información adicional -->
        @if(!empty($datosFactura['comprobante']['guia_remision']))
        <div class="info-section" style="margin-bottom: 15px;">
            <h3>📋 INFORMACIÓN ADICIONAL</h3>
            <div class="info-row">
                <span class="info-label">Guía de Remisión:</span>
                <span class="info-value">{{ $datosFactura['comprobante']['guia_remision'] }}</span>
            </div>
        </div>
        @endif

        <!-- Pie de página -->
        <div class="footer">
            <p><strong>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</strong></p>
            @if(!empty($datosFactura['modelo']['xml_type']))
                <p>Tipo de XML: {{ strtoupper($datosFactura['modelo']['xml_type']) }}</p>
            @endif
            <p>Sistema de Facturación Electrónica OPTECU &copy; {{ date('Y') }}</p>
        </div>
    </div>

    <script>
        // Auto-imprimir si viene con parámetro
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('auto_print'))
                setTimeout(() => {
                    window.print();
                }, 1000);
            @endif
        });
    </script>
</body>
</html>
