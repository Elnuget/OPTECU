<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Pedidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            text-transform: uppercase;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th, td {
            padding: 3px;
            text-align: left;
            font-size: 9px;
            vertical-align: top;
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
        
        tr.summary-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ESCLERÓPTICA - RESUMEN DE PEDIDOS</h1>
        <p>Fecha de impresión: {{ date('d-m-Y') }}</p>
        <p>Cantidad de pedidos: {{ $pedidos->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nº ORDEN</th>
                <th>FECHA</th>
                <th>EMPRESA</th>
                <th>CLIENTE</th>
                <th>PACIENTE</th>
                <th>TELÉFONO</th>
                <th>ARMAZÓN/ACCESORIOS</th>
                <th>MEDIDAS</th>
                <th>ESTADO</th>
                <th>TOTAL</th>
                <th>SALDO</th>
                <th>USUARIO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalVentas = 0;
                $totalSaldos = 0;
            @endphp
            
            @foreach($pedidos as $pedido)
                @php
                    $totalVentas += floatval(str_replace(',', '.', str_replace('.', '', $pedido->total)));
                    $totalSaldos += floatval(str_replace(',', '.', str_replace('.', '', $pedido->saldo)));
                    
                    // Preparar datos de armazones y accesorios
                    $armazones = [];
                    foreach ($pedido->inventarios as $inventario) {
                        $armazones[] = $inventario->codigo . ' (' . number_format($inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100)), 2, ',', '.') . ')';
                    }
                    $armazonesStr = implode(', ', $armazones);
                    
                    // Preparar datos de medidas
                    $medidas = [];
                    foreach ($pedido->lunas as $luna) {
                        $medidas[] = $luna->l_detalle . ': ' . $luna->l_medida;
                    }
                    $medidasStr = implode(' | ', $medidas);
                @endphp
                <tr>
                    <td>{{ $pedido->numero_orden }}</td>
                    <td>{{ date('d-m-Y', strtotime($pedido->fecha)) }}</td>
                    <td>{{ $pedido->empresa ? $pedido->empresa->nombre : 'N/A' }}</td>
                    <td>{{ $pedido->cliente }}</td>
                    <td>{{ $pedido->paciente }}</td>
                    <td>{{ $pedido->celular }}</td>
                    <td>{{ $armazonesStr ?: 'N/A' }}</td>
                    <td>{{ $medidasStr ?: 'N/A' }}</td>
                    <td>{{ $pedido->fact }}</td>
                    <td class="text-right">${{ $pedido->total }}</td>
                    <td class="text-right">${{ $pedido->saldo }}</td>
                    <td>{{ $pedido->usuario }}</td>
                </tr>
            @endforeach
            
            <tr class="summary-row">
                <td colspan="9" class="text-right">TOTALES:</td>
                <td class="text-right">${{ number_format($totalVentas, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format($totalSaldos, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    
    <div class="totales">
        <div class="total-row">
            <span>CANTIDAD DE PEDIDOS:</span>
            <span>{{ $pedidos->count() }}</span>
        </div>
        <div class="total-row">
            <span>TOTAL VENTAS:</span>
            <span>${{ number_format($totalVentas, 2, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>TOTAL SALDOS PENDIENTES:</span>
            <span>${{ number_format($totalSaldos, 2, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>TOTAL COBRADO:</span>
            <span>${{ number_format($totalVentas - $totalSaldos, 2, ',', '.') }}</span>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
