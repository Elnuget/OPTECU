<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión de Pedidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .pedido-container {
            border: 2px solid #000;
            margin-bottom: 30px;
            padding: 15px;
            page-break-inside: avoid;
            page-break-after: always;
        }
        
        .pedido-container:last-child {
            page-break-after: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 5px;
            font-weight: bold;
            border: 1px solid #000;
            margin-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .info-label {
            font-weight: bold;
            width: 30%;
        }
        
        .info-value {
            width: 70%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th, td {
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .totales {
            background-color: #f9f9f9;
            padding: 10px;
            border: 2px solid #000;
            margin-top: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-weight: bold;
        }
        
        @media print {
            body {
                margin: 0;
            }
            
            .pedido-container {
                page-break-inside: avoid;
                page-break-after: always;
            }
            
            .pedido-container:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    @foreach($pedidos as $pedido)
    <div class="pedido-container">
        <div class="header">
            <h1>L BARBOSA - VENTA {{ $pedido->id }}</h1>
            <p>Orden: {{ $pedido->numero_orden }} | Fecha: {{ date('d-m-Y', strtotime($pedido->fecha)) }}</p>
        </div>

        {{-- Información Básica --}}
        <div class="section">
            <div class="section-title">INFORMACIÓN BÁSICA</div>
            <div class="info-row">
                <span class="info-label">NÚMERO DE ORDEN:</span>
                <span class="info-value">{{ $pedido->numero_orden }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">FACTURA:</span>
                <span class="info-value">{{ $pedido->fact }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">USUARIO:</span>
                <span class="info-value">{{ $pedido->usuario }}</span>
            </div>
        </div>

        {{-- Datos Personales --}}
        <div class="section">
            <div class="section-title">DATOS PERSONALES</div>
            <div class="info-row">
                <span class="info-label">CLIENTE:</span>
                <span class="info-value">{{ $pedido->cliente }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">RUT:</span>
                <span class="info-value">{{ $pedido->cedula ?? 'No registrada' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">PACIENTE:</span>
                <span class="info-value">{{ $pedido->paciente }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">CELULAR:</span>
                <span class="info-value">{{ $pedido->celular }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">CORREO:</span>
                <span class="info-value">{{ $pedido->correo_electronico }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">EXAMEN VISUAL:</span>
                <span class="info-value">${{ number_format($pedido->examen_visual, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Armazón y Accesorios --}}
        @if ($pedido->inventarios->count() > 0)
        <div class="section">
            <div class="section-title">ARMAZÓN Y ACCESORIOS</div>
            <table>
                <thead>
                    <tr>
                        <th>CÓDIGO</th>
                        <th>PRECIO BASE</th>
                        <th>DESCUENTO</th>
                        <th>PRECIO FINAL</th>
                        <th>BASE</th>
                        <th>IVA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido->inventarios as $inventario)
                        @php
                            $precioConDescuento = $inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100));
                            $base = round($precioConDescuento / 1.15, 2);
                            $iva = round($precioConDescuento - $base, 2);
                        @endphp
                        <tr>
                            <td>{{ $inventario->codigo }}</td>
                            <td>${{ number_format($inventario->pivot->precio, 2, ',', '.') }}</td>
                            <td>{{ $inventario->pivot->descuento }}%</td>
                            <td>${{ number_format($precioConDescuento, 2, ',', '.') }}</td>
                            <td>${{ number_format($base, 2, ',', '.') }}</td>
                            <td>${{ number_format($iva, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Lunas --}}
        @if ($pedido->lunas->count() > 0)
        <div class="section">
            <div class="section-title">LUNAS</div>
            <table>
                <thead>
                    <tr>
                        <th>MEDIDA</th>
                        <th>DETALLE</th>
                        <th>TIPO LENTE</th>
                        <th>MATERIAL</th>
                        <th>FILTRO</th>
                        <th>PRECIO</th>
                        <th>DESC. (%)</th>
                        <th>BASE</th>
                        <th>IVA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido->lunas as $luna)
                        @php
                            $precioConDescuento = $luna->l_precio * (1 - ($luna->l_precio_descuento / 100));
                            $base = round($precioConDescuento / 1.15, 2);
                            $iva = round($precioConDescuento - $base, 2);
                        @endphp
                        <tr>
                            <td>{{ $luna->l_medida }}</td>
                            <td>{{ $luna->l_detalle }}</td>
                            <td>{{ $luna->tipo_lente }}</td>
                            <td>{{ $luna->material }}</td>
                            <td>{{ $luna->filtro }}</td>
                            <td>${{ number_format($luna->l_precio, 2, ',', '.') }}</td>
                            <td>{{ $luna->l_precio_descuento }}%</td>
                            <td>${{ number_format($base, 2, ',', '.') }}</td>
                            <td>${{ number_format($iva, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Compra Rápida --}}
        @if($pedido->valor_compra > 0)
        <div class="section">
            <div class="section-title">COMPRA RÁPIDA</div>
            <div class="info-row">
                <span class="info-label">VALOR DE COMPRA:</span>
                <span class="info-value">${{ number_format($pedido->valor_compra, 2, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">MOTIVO DE COMPRA:</span>
                <span class="info-value">{{ $pedido->motivo_compra }}</span>
            </div>
        </div>
        @endif

        {{-- Totales --}}
        <div class="totales">
            <div class="total-row">
                <span>TOTAL:</span>
                <span>${{ number_format($pedido->total, 2, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>SALDO:</span>
                <span style="color: {{ $pedido->saldo == 0 ? 'green' : 'red' }}">${{ number_format($pedido->saldo, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        // Auto-imprimir cuando se carga la página
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
