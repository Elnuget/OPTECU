<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROL DE PAGO - {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}{{ $usuario ? ' - ' . strtoupper($usuario) : '' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .header .company-info {
            font-size: 14px;
            margin: 5px 0;
        }
        
        .periodo-info {
            background-color: #f0f0f0;
            padding: 15px;
            border: 2px solid #000;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .periodo-info h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #000;
            color: #fff;
            padding: 10px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .resumen-boxes {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .resumen-box {
            flex: 1;
            border: 2px solid #000;
            padding: 15px;
            margin: 0 5px 10px 5px;
            text-align: center;
            min-width: 200px;
        }
        
        .resumen-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
            background-color: #f0f0f0;
            padding: 5px;
            border: 1px solid #000;
        }
        
        .resumen-box .amount {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }
        
        .resumen-box .count {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            padding: 6px;
            font-size: 10px;
            vertical-align: top;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .total-general {
            background-color: #000;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            font-style: italic;
            background-color: #f9f9f9;
            border: 2px dashed #ccc;
            margin: 20px 0;
        }
        
        .footer-info {
            margin-top: 40px;
            border-top: 2px solid #000;
            padding-top: 20px;
            font-size: 10px;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        @media print {
            body {
                margin: 10px;
            }
            
            .section {
                page-break-inside: avoid;
            }
            
            .resumen-boxes {
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            .header {
                page-break-after: avoid;
            }
            
            .periodo-info {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <img src="{{ asset('AdminLTELogo.png') }}" alt="Logo" style="width: 80px; height: 80px; margin-bottom: 10px;">
        <div class="subtitle">ESCLERÓPTICA</div>
        <div class="subtitle">ROL DE PAGO</div>
        <div class="company-info">Reporte generado el {{ date('d/m/Y H:i') }}</div>
    </div>

    <!-- Información del Período -->
    <div class="periodo-info">
        <h2>
            PERÍODO: {{ strtoupper(date('F', mktime(0, 0, 0, $mes, 1))) }} {{ $anio }}
            @if($usuario)
                - EMPLEADO: {{ strtoupper($usuario) }}
            @else
                - TODOS LOS EMPLEADOS
            @endif
        </h2>
    </div>

    <!-- Resumen de Estadísticas de Ventas -->
    <div class="resumen-boxes">
        <div class="resumen-box">
            <h3>Total de Ventas</h3>
            <div class="amount">${{ number_format($pedidos->sum('total'), 2, ',', '.') }}</div>
            <div class="count">{{ $pedidos->count() }} pedidos</div>
        </div>
        <div class="resumen-box">
            <h3>Saldo Pendiente</h3>
            <div class="amount">${{ number_format($pedidos->sum('saldo'), 2, ',', '.') }}</div>
            <div class="count">Por cobrar</div>
        </div>
        <div class="resumen-box">
            <h3>Retiros de Caja</h3>
            <div class="amount">$-{{ number_format(abs($retirosCaja->sum('valor')), 2, ',', '.') }}</div>
            <div class="count">{{ $retirosCaja->count() }} retiros</div>
        </div>
        <div class="resumen-box">
            <h3>Pedidos Realizados</h3>
            <div class="amount">{{ $pedidos->count() }}</div>
            <div class="count">Total órdenes</div>
        </div>
        <div class="resumen-box">
            <h3>Balance Neto</h3>
            <div class="amount">${{ number_format($pedidos->sum('total') + $retirosCaja->sum('valor'), 2, ',', '.') }}</div>
            <div class="count">Ventas - Retiros</div>
        </div>
    </div>

    <!-- Pedidos por Sucursal -->
    <div class="section">
        <div class="section-title">Pedidos por Sucursal</div>
        @if($pedidos->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%;">Sucursal</th>
                        <th style="width: 20%;">Pedidos</th>
                        <th style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $pedidosPorEmpresa = $pedidos->groupBy('empresa_id');
                    @endphp
                    
                    @foreach($pedidosPorEmpresa as $empresaId => $pedidosEmpresa)
                        @php
                            $nombreEmpresa = 'SIN SUCURSAL';
                            if ($empresaId && $pedidosEmpresa->first()->empresa) {
                                $nombreEmpresa = $pedidosEmpresa->first()->empresa->nombre;
                            }
                        @endphp
                        <tr>
                            <td>{{ $nombreEmpresa }}</td>
                            <td class="text-center">{{ $pedidosEmpresa->count() }}</td>
                            <td class="text-right">${{ number_format($pedidosEmpresa->sum('total'), 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-general">
                        <td class="text-center">TOTAL GENERAL</td>
                        <td class="text-center">{{ $pedidos->count() }}</td>
                        <td class="text-right">${{ number_format($pedidos->sum('total'), 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="no-data">
                No se encontraron pedidos para el período seleccionado
            </div>
        @endif
    </div>

    @if($detallesSueldo->count() > 0)
    <!-- Sección: Detalles de Sueldo -->
    <div class="section">
        <div class="section-title">Detalles de Sueldo</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 20%;">Empleado</th>
                    <th style="width: 45%;">Descripción</th>
                    <th style="width: 20%;">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detallesSueldo as $detalle)
                <tr>
                    <td class="text-center">{{ $detalle->created_at ? $detalle->created_at->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $detalle->user ? $detalle->user->name : 'N/A' }}</td>
                    <td>{{ $detalle->descripcion }}</td>
                    <td class="text-right">${{ number_format($detalle->valor, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-general">
                    <td colspan="3" class="text-center">TOTAL DETALLES DE SUELDO</td>
                    <td class="text-right">${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @else
    <div class="section">
        <div class="section-title">Detalles de Sueldo</div>
        <div class="no-data">
            No se encontraron detalles de sueldo para el período seleccionado
        </div>
    </div>
    @endif

    @if(isset($historialCaja) && count($historialCaja) > 0)
    <!-- Sección: Historial de Caja (Horas Trabajadas) -->
    <div class="section">
        @php
            // Calcular totales usando minutos para mayor precisión (como en el componente original)
            $totalMinutosGlobal = 0;
            if (isset($historialCaja) && count($historialCaja) > 0) {
                $totalMinutosGlobal = $historialCaja->where('total_minutos', '!=', null)->sum('total_minutos');
            }
            $totalHorasCalculadas = intval($totalMinutosGlobal / 60);
            $totalMinutosRestantes = $totalMinutosGlobal % 60;
        @endphp
        <div class="section-title">Historial de Caja - Control de Horas</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 8%;">Día</th>
                    <th style="width: 15%;">Empleado</th>
                    <th style="width: 10%;">Empresa</th>
                    <th style="width: 8%;">Apertura</th>
                    <th style="width: 8%;">Cierre</th>
                    <th style="width: 12%;">Horas Trabajadas</th>
                    <th style="width: 8%;">Monto Inicial</th>
                    <th style="width: 8%;">Monto Final</th>
                    <th style="width: 8%;">Estado</th>
                    <th style="width: 5%;">Cobro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historialCaja as $historial)
                <tr>
                    <td class="text-center">{{ $historial->fecha_formateada ?? 'N/A' }}</td>
                    <td class="text-center">{{ $historial->dia_semana ?? 'N/A' }}</td>
                    <td>{{ $historial->usuario ?? 'N/A' }}</td>
                    <td>{{ $historial->empresa ?? 'N/A' }}</td>
                    <td class="text-center">{{ $historial->hora_apertura ?? '-' }}</td>
                    <td class="text-center">{{ $historial->hora_cierre ?? '-' }}</td>
                    <td class="text-center">
                        @if(isset($historial->horas_formateadas))
                            {{ $historial->horas_formateadas }}
                        @elseif(isset($historial->horas_trabajadas) && $historial->horas_trabajadas > 0)
                            {{ $historial->horas_trabajadas }}h
                            @if(isset($historial->minutos_trabajados) && $historial->minutos_trabajados > 0)
                                {{ $historial->minutos_trabajados }}m
                            @endif
                        @else
                            Sin registros
                        @endif
                    </td>
                    <td class="text-right">
                        @if(isset($historial->monto_apertura))
                            ${{ number_format($historial->monto_apertura, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if(isset($historial->monto_cierre))
                            ${{ number_format($historial->monto_cierre, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $historial->estado ?? '' }}</td>
                    <td class="text-center"><!-- Columna vacía para Corbor --></td>
                </tr>
                @endforeach
                <tr class="total-general">
                    <td colspan="6" class="text-center">TOTAL HORAS TRABAJADAS</td>
                    <td class="text-center">{{ $totalHorasCalculadas }}h {{ $totalMinutosRestantes }}m</td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div>GERENTE</div>
            <br><br><br>
            <div>Rogger Pucuji</div>
        </div>
        <div class="signature-box">
            <div>EMPLEADO</div>
            <br><br><br>
            <div>{{ $usuario ? strtoupper($usuario) : '____________________' }}</div>
        </div>
    </div>

    <script>
        // Auto-imprimir cuando la página carga
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
