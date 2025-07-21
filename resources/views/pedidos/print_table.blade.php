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
        <h1>L BARBOSA - RESUMEN DE PEDIDOS</h1>
        <p>Fecha de impresión: {{ date('d-m-Y') }}</p>
        <p>Cantidad de pedidos: {{ $pedidos->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>EMPRESA</th>
                <th>Nº ORDEN</th>
                <th>FECHA</th>
                <th>CLIENTE</th>
                <th>PACIENTE</th>
                <th>TELÉFONO</th>
                <th>ARMAZÓN/ACCESORIOS</th>
                <th>MEDIDAS</th>
                <th>ESTADO</th>
                <th>TOTAL</th>
                <th>PAGOS</th>
                <th>SALDO</th>
                <th>USUARIO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalVentas = 0;
                $totalSaldos = 0;
                $totalPagos = 0;
                $pagosPorTipo = [];
            @endphp
            
            @foreach($pedidos as $pedido)
                @php
                    // Convertir correctamente a enteros eliminando decimales
                    $totalVentas += intval(floatval($pedido->total));
                    $totalSaldos += intval(floatval($pedido->saldo));
                    
                    // Preparar datos de armazones y accesorios
                    $armazones = [];
                    foreach ($pedido->inventarios as $inventario) {
                        $precio = intval($inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100)));
                        $armazones[] = $inventario->codigo . ' ($' . number_format($precio, 0, '', '.') . ')';
                    }
                    $armazonesStr = implode(', ', $armazones);
                    
                    // Preparar datos de medidas
                    $medidas = [];
                    foreach ($pedido->lunas as $luna) {
                        $medidas[] = $luna->l_detalle . ': ' . $luna->l_medida;
                    }
                    $medidasStr = implode(' | ', $medidas);
                    
                    // Preparar datos de pagos
                    $pagosInfo = [];
                    $totalPagadoPedido = 0;
                    foreach ($pedido->pagos as $pago) {
                        $pagoEntero = intval(floatval($pago->pago));
                        $totalPagadoPedido += $pagoEntero;
                        $totalPagos += $pagoEntero;
                        
                        $medioPago = $pago->mediodepago ? $pago->mediodepago->medio_de_pago : 'N/A';
                        $fechaPago = $pago->created_at ? $pago->created_at->format('d-m-Y') : 'N/A';
                        $pagosInfo[] = '$' . number_format($pagoEntero, 0, '', '.') . ' (' . $medioPago . ' - ' . $fechaPago . ')';
                        
                        // Acumular pagos por tipo
                        if (!isset($pagosPorTipo[$medioPago])) {
                            $pagosPorTipo[$medioPago] = 0;
                        }
                        $pagosPorTipo[$medioPago] += $pagoEntero;
                    }
                    $pagosStr = implode(' | ', $pagosInfo);
                @endphp
                <tr>
                    <td>{{ $pedido->empresa ? $pedido->empresa->nombre : 'N/A' }}</td>
                    <td>{{ $pedido->numero_orden }}</td>
                    <td>{{ date('d-m-Y', strtotime($pedido->fecha)) }}</td>
                    <td>{{ $pedido->cliente }}</td>
                    <td>{{ $pedido->paciente }}</td>
                    <td>{{ $pedido->celular }}</td>
                    <td>{{ $armazonesStr ?: 'N/A' }}</td>
                    <td>{{ $medidasStr ?: 'N/A' }}</td>
                    <td>{{ $pedido->fact }}</td>
                    <td class="text-right">${{ number_format(intval(floatval($pedido->total)), 0, '', '.') }}</td>
                    <td>{{ $pagosStr ?: 'Sin pagos' }}</td>
                    <td class="text-right">${{ number_format(intval(floatval($pedido->saldo)), 0, '', '.') }}</td>
                    <td>{{ $pedido->usuario }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Tarjetas de Resumen -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
        
        <!-- Tarjeta de Totales Generales -->
        <div style="background-color: #f0f8ff; padding: 15px; border: 2px solid #4169e1; border-radius: 8px;">
            <h3 style="margin: 0 0 10px 0; color: #4169e1; text-align: center; font-size: 14px;">RESUMEN GENERAL</h3>
            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #ddd;">
                <span style="font-weight: bold;">CANTIDAD DE PEDIDOS:</span>
                <span>{{ $pedidos->count() }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #ddd;">
                <span style="font-weight: bold;">TOTAL VENTAS:</span>
                <span style="color: #008000; font-weight: bold;">${{ number_format($totalVentas, 0, '', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #ddd;">
                <span style="font-weight: bold;">TOTAL SALDOS PENDIENTES:</span>
                <span style="color: #ff4500; font-weight: bold;">${{ number_format($totalSaldos, 0, '', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #ddd;">
                <span style="font-weight: bold;">TOTAL DE PAGOS:</span>
                <span style="color: #32cd32; font-weight: bold;">${{ number_format($totalPagos, 0, '', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 8px 0; background-color: #e6f3ff; margin-top: 5px; border-radius: 4px;">
                <span style="font-weight: bold; font-size: 12px;">TOTAL COBRADO:</span>
                <span style="color: #0066cc; font-weight: bold; font-size: 12px;">${{ number_format($totalPagos, 0, '', '.') }}</span>
            </div>
        </div>

        <!-- Tarjeta de Pagos por Tipo -->
        <div style="background-color: #f0fff0; padding: 15px; border: 2px solid #32cd32; border-radius: 8px;">
            <h3 style="margin: 0 0 10px 0; color: #32cd32; text-align: center; font-size: 14px;">PAGOS POR TIPO</h3>
            @php
                arsort($pagosPorTipo); // Ordenar de mayor a menor
            @endphp
            @foreach($pagosPorTipo as $tipo => $monto)
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #ddd;">
                    <span style="font-weight: bold;">{{ strtoupper($tipo) }}:</span>
                    <span style="color: #228b22; font-weight: bold;">${{ number_format($monto, 0, '', '.') }}</span>
                </div>
            @endforeach
            
            @if(empty($pagosPorTipo))
                <div style="text-align: center; color: #666; font-style: italic; padding: 20px 0;">
                    No hay pagos registrados
                </div>
            @endif
            
            <!-- Verificación -->
            <div style="background-color: #e6ffe6; padding: 8px; margin-top: 10px; border-radius: 4px; border: 1px solid #90ee90;">
                <div style="display: flex; justify-content: space-between; font-size: 11px;">
                    <span>VERIFICACIÓN:</span>
                    <span style="color: {{ $totalPagos == array_sum($pagosPorTipo) ? '#008000' : '#ff0000' }};">
                        {{ $totalPagos == array_sum($pagosPorTipo) ? '✓ CORRECTO' : '✗ ERROR' }}
                    </span>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
