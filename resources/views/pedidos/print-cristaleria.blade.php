<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cristalería - Órdenes de Trabajo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
            background-color: #000;
            color: white;
            padding: 15px;
        }
        
        .header h2 {
            margin: 10px 0;
            font-size: 16px;
            color: #333;
        }
        
        .fecha-impresion {
            text-align: right;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .tabla-cristaleria {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid #000;
        }
        
        .tabla-cristaleria th {
            background-color: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 11px;
        }
        
        .tabla-cristaleria td {
            padding: 10px 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 10px;
            vertical-align: middle;
        }
        
        .tabla-cristaleria tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .tabla-cristaleria tbody tr:hover {
            background-color: #e3f2fd;
        }
        
        .orden-info {
            background-color: #fff3cd;
            font-weight: bold;
            color: #856404;
        }
        
        .cliente-info {
            background-color: #d4edda;
            font-weight: bold;
            color: #155724;
        }
        
        .precio-cell {
            background-color: #f8d7da;
            font-weight: bold;
            color: #721c24;
        }
        
        .medida-cell {
            background-color: #cce5ff;
            font-weight: bold;
            color: #004085;
            font-size: 11px;
            padding: 5px !important;
        }
        
        .medida-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin: 0;
        }
        
        .medida-table th, .medida-table td {
            border: 1px solid #666;
            padding: 2px 4px;
            text-align: center;
        }
        
        .medida-table th {
            background-color: #004085;
            color: white;
            font-size: 7px;
            font-weight: bold;
        }
        
        .medida-table td {
            font-size: 8px;
            background-color: white;
        }
        
        .ojo-label {
            background-color: #e3f2fd;
            font-weight: bold;
            font-size: 8px;
        }
        
        .detalle-cell {
            text-align: left;
            font-size: 9px;
            max-width: 150px;
            word-wrap: break-word;
        }
        
        .tipo-lente-cell {
            background-color: #e2f3ff;
            font-size: 9px;
        }
        
        .material-cell {
            background-color: #f0f8ff;
            font-size: 9px;
        }
        
        .filtro-cell {
            background-color: #fff5f5;
            font-size: 9px;
        }
        
        .total-general {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            font-size: 14px;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
        }
        
        .observaciones {
            margin-top: 30px;
            border: 2px solid #000;
            padding: 15px;
        }
        
        .observaciones h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            background-color: #6c757d;
            color: white;
            padding: 10px;
            margin: -15px -15px 15px -15px;
        }
        
        .linea-observacion {
            border-bottom: 1px solid #999;
            height: 25px;
            margin-bottom: 10px;
        }
        
        .firmas {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 50px;
            text-align: center;
        }
        
        .firma-box {
            border-top: 2px solid #000;
            padding-top: 10px;
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        .header-repeat {
            display: none;
        }
        
        @media print {
            body {
                margin: 0;
            }
            
            .tabla-cristaleria {
                page-break-inside: auto;
            }
            
            .observaciones {
                page-break-inside: avoid;
            }
            
            .header-repeat {
                display: block;
                page-break-before: always;
                margin-top: 20px;
            }
            
            .header-repeat .header {
                margin-bottom: 15px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            
            .header-repeat .header h1 {
                font-size: 24px;
                padding: 10px;
            }
            
            .header-repeat .header h2 {
                font-size: 14px;
            }
            
            /* Forzar salto de página cada cierto número de filas */
            .tabla-cristaleria tbody tr:nth-child(15n) {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔬 CRISTALERÍA - ÓRDENES DE TRABAJO 🔬</h1>
        <h2>RESUMEN DE LUNAS PARA PROCESAR</h2>
    </div>

    <div class="fecha-impresion">
        FECHA DE IMPRESIÓN: {{ date('d/m/Y H:i:s') }} | TOTAL ÓRDENES: {{ $pedidos->count() }}
    </div>

    @php
        $totalPedidos = $pedidos->count();
        $pedidosPerPage = 10; // Número de pedidos por página para mantener legibilidad
        $totalPages = ceil($totalPedidos / $pedidosPerPage);
        $currentPage = 1;
        $pedidosProcessed = 0;
    @endphp

    @foreach($pedidos->chunk($pedidosPerPage) as $chunkIndex => $pedidosChunk)
        @if($chunkIndex > 0)
            <!-- Encabezado repetido para páginas adicionales -->
            <div class="header-repeat">
                <div class="header">
                    <h1>🔬 CRISTALERÍA - ÓRDENES DE TRABAJO 🔬</h1>
                    <h2>RESUMEN DE LUNAS PARA PROCESAR - PÁGINA {{ $chunkIndex + 1 }} DE {{ $totalPages }}</h2>
                </div>
                <div class="fecha-impresion">
                    FECHA DE IMPRESIÓN: {{ date('d/m/Y H:i:s') }} | TOTAL ÓRDENES: {{ $pedidos->count() }}
                </div>
            </div>
        @endif

        <table class="tabla-cristaleria {{ $chunkIndex > 0 ? 'no-break' : '' }}">
            <thead>
                <tr>
                    <th>SUCURSAL</th>
                    <th>ORDEN</th>
                    <th>FECHA</th>
                    <th>MEDIDA</th>
                    <th>TIPO LENTE</th>
                    <th>MATERIAL</th>
                    <th>FILTRO</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalLunasChunk = 0;
                @endphp
                
                @foreach($pedidosChunk as $pedido)
                    @if($pedido->lunas->count() > 0)
                        @foreach($pedido->lunas as $index => $luna)
                            @php
                                $totalLunasChunk++;
                            @endphp
                            <tr>
                                @if($index == 0)
                                    <td class="cliente-info" rowspan="{{ $pedido->lunas->count() }}">
                                        {{ $pedido->empresa->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="orden-info" rowspan="{{ $pedido->lunas->count() }}">
                                        {{ $pedido->numero_orden }}
                                    </td>
                                    <td class="orden-info" rowspan="{{ $pedido->lunas->count() }}">
                                        {{ date('d/m/Y', strtotime($pedido->fecha)) }}
                                    </td>
                                @endif
                                <td class="medida-cell">
                                    @php
                                        // Parsear el campo l_medida para extraer los datos
                                        $medidaText = $luna->l_medida ?? '';
                                        
                                        // Extraer datos de OD - Mejorada para capturar decimales en todos los campos
                                        preg_match('/OD:\s*([+-]?\d+(?:\.\d+)?)\s*([+-]?\d+(?:\.\d+)?)\s*X\s*(\d+(?:\.\d+)?)°?/i', $medidaText, $odMatches);
                                        $od_esfera = $odMatches[1] ?? 'N/A';
                                        $od_cilindro = $odMatches[2] ?? 'N/A';
                                        $od_eje = $odMatches[3] ?? 'N/A';
                                        
                                        // Extraer datos de OI - Mejorada para capturar decimales en todos los campos
                                        preg_match('/OI:\s*([+-]?\d+(?:\.\d+)?)\s*([+-]?\d+(?:\.\d+)?)\s*X\s*(\d+(?:\.\d+)?)°?/i', $medidaText, $oiMatches);
                                        $oi_esfera = $oiMatches[1] ?? 'N/A';
                                        $oi_cilindro = $oiMatches[2] ?? 'N/A';
                                        $oi_eje = $oiMatches[3] ?? 'N/A';
                                        
                                        // Extraer ADD - Mejorada para mayor flexibilidad
                                        preg_match('/ADD:\s*([+-]?\d+(?:\.\d+)?)/i', $medidaText, $addMatch);
                                        $add = $addMatch[1] ?? 'N/A';
                                        
                                        // Extraer DP - Mejorada para mayor flexibilidad
                                        preg_match('/DP:\s*(\d+(?:\.\d+)?)/i', $medidaText, $dpMatch);
                                        $dp = $dpMatch[1] ?? 'N/A';
                                    @endphp
                                    
                                    <table class="medida-table">
                                        <tr>
                                            <th style="width: 15%;">OJO</th>
                                            <th style="width: 25%;">ESFÉRICO</th>
                                            <th style="width: 25%;">CILINDRO</th>
                                            <th style="width: 25%;">EJE</th>
                                            <th style="width: 10%;">ADD</th>
                                        </tr>
                                        <tr>
                                            <td class="ojo-label">OD</td>
                                            <td>{{ $od_esfera }}</td>
                                            <td>{{ $od_cilindro }}</td>
                                            <td>{{ $od_eje }}°</td>
                                            <td rowspan="2" style="vertical-align: middle; font-weight: bold;">{{ $add }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ojo-label">OI</td>
                                            <td>{{ $oi_esfera }}</td>
                                            <td>{{ $oi_cilindro }}</td>
                                            <td>{{ $oi_eje }}°</td>
                                        </tr>
                                        @if($dp !== 'N/A')
                                        <tr>
                                            <td colspan="5" style="text-align: center; font-weight: bold; background-color: #f0f8ff;">
                                                DP: {{ $dp }}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                                <td class="tipo-lente-cell">{{ $luna->tipo_lente }}</td>
                                <td class="material-cell">
                                    @php
                                        $materialText = $luna->material ?? '';
                                        
                                        // Verificar si el material tiene formato "OD: ... | OI: ..."
                                        if (preg_match('/OD:\s*([^|]+)\|\s*OI:\s*(.+)/i', $materialText, $materialMatches)) {
                                            $od_material = trim($materialMatches[1]);
                                            $oi_material = trim($materialMatches[2]);
                                        } else {
                                            $od_material = null;
                                            $oi_material = null;
                                        }
                                    @endphp
                                    
                                    @if($od_material && $oi_material)
                                        <div style="font-size: 8px; line-height: 1.2;">
                                            <div style="background-color: #e3f2fd; padding: 2px; margin-bottom: 2px; border-radius: 2px;">
                                                <strong>OD:</strong> {{ $od_material }}
                                            </div>
                                            <div style="background-color: #f3e5f5; padding: 2px; border-radius: 2px;">
                                                <strong>OI:</strong> {{ $oi_material }}
                                            </div>
                                        </div>
                                    @else
                                        {{ $luna->material }}
                                    @endif
                                </td>
                                <td class="filtro-cell">{{ $luna->filtro }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
            @if($chunkIndex == count($pedidos->chunk($pedidosPerPage)) - 1)
                <!-- Solo mostrar el total en la última página -->
                <tfoot>
                    <tr style="background-color: #28a745; color: white; font-weight: bold;">
                        <td colspan="6" style="text-align: right; padding: 15px; font-size: 14px;">
                            TOTAL LUNAS PARA PROCESAR:
                        </td>
                        <td style="font-size: 16px; padding: 15px; text-align: center;">
                            @php
                                $totalLunasGlobal = 0;
                                foreach($pedidos as $pedido) {
                                    $totalLunasGlobal += $pedido->lunas->count();
                                }
                                echo $totalLunasGlobal;
                            @endphp
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endforeach

    <script>
        // Auto-imprimir cuando se carga la página
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
