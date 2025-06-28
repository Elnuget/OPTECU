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
        
        @media print {
            body {
                margin: 0;
            }
            
            .tabla-cristaleria {
                page-break-inside: avoid;
            }
            
            .observaciones {
                page-break-inside: avoid;
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

    <table class="tabla-cristaleria">
        <thead>
            <tr>
                <th>ORDEN</th>
                <th>FECHA</th>
                <th>CLIENTE</th>
                <th>PACIENTE</th>
                <th>CELULAR</th>
                <th>MEDIDA</th>
                <th>DETALLE</th>
                <th>TIPO LENTE</th>
                <th>MATERIAL</th>
                <th>FILTRO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalLunas = 0;
            @endphp
            
            @foreach($pedidos as $pedido)
                @if($pedido->lunas->count() > 0)
                    @foreach($pedido->lunas as $index => $luna)
                        @php
                            $totalLunas++;
                        @endphp
                        <tr>
                            @if($index == 0)
                                <td class="orden-info" rowspan="{{ $pedido->lunas->count() }}">
                                    {{ $pedido->numero_orden }}
                                </td>
                                <td class="orden-info" rowspan="{{ $pedido->lunas->count() }}">
                                    {{ date('d/m/Y', strtotime($pedido->fecha)) }}
                                </td>
                                <td class="cliente-info" rowspan="{{ $pedido->lunas->count() }}">
                                    {{ $pedido->cliente }}
                                </td>
                                <td class="cliente-info" rowspan="{{ $pedido->lunas->count() }}">
                                    {{ $pedido->paciente }}
                                </td>
                                <td class="cliente-info" rowspan="{{ $pedido->lunas->count() }}">
                                    {{ $pedido->celular }}
                                </td>
                            @endif
                            <td class="medida-cell">{{ $luna->l_medida }}</td>
                            <td class="detalle-cell">{{ $luna->l_detalle }}</td>
                            <td class="tipo-lente-cell">{{ $luna->tipo_lente }}</td>
                            <td class="material-cell">{{ $luna->material }}</td>
                            <td class="filtro-cell">{{ $luna->filtro }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #28a745; color: white; font-weight: bold;">
                <td colspan="9" style="text-align: right; padding: 15px; font-size: 14px;">
                    TOTAL LUNAS PARA PROCESAR:
                </td>
                <td style="font-size: 16px; padding: 15px; text-align: center;">
                    {{ $totalLunas }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Resumen por tipo de lente --}}
    <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
        @php
            $tiposLente = $pedidos->flatMap->lunas->groupBy('tipo_lente');
            $materiales = $pedidos->flatMap->lunas->groupBy('material');
            $filtros = $pedidos->flatMap->lunas->groupBy('filtro');
        @endphp
        
        <div style="border: 2px solid #007bff; padding: 15px;">
            <h4 style="background-color: #007bff; color: white; margin: -15px -15px 10px -15px; padding: 10px; text-align: center;">TIPOS DE LENTE</h4>
            @foreach($tiposLente as $tipo => $lunas)
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dotted #ccc;">
                    <span>{{ $tipo }}:</span>
                    <strong>{{ $lunas->count() }}</strong>
                </div>
            @endforeach
        </div>
        
        <div style="border: 2px solid #28a745; padding: 15px;">
            <h4 style="background-color: #28a745; color: white; margin: -15px -15px 10px -15px; padding: 10px; text-align: center;">MATERIALES</h4>
            @foreach($materiales as $material => $lunas)
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dotted #ccc;">
                    <span>{{ $material }}:</span>
                    <strong>{{ $lunas->count() }}</strong>
                </div>
            @endforeach
        </div>
        
        <div style="border: 2px solid #ffc107; padding: 15px;">
            <h4 style="background-color: #ffc107; color: #000; margin: -15px -15px 10px -15px; padding: 10px; text-align: center;">FILTROS</h4>
            @foreach($filtros as $filtro => $lunas)
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dotted #ccc;">
                    <span>{{ $filtro }}:</span>
                    <strong>{{ $lunas->count() }}</strong>
                </div>
            @endforeach
        </div>
    </div>

    <div class="observaciones">
        <h3>📝 OBSERVACIONES GENERALES</h3>
        <div class="linea-observacion"></div>
        <div class="linea-observacion"></div>
        <div class="linea-observacion"></div>
        <div class="linea-observacion"></div>
        <div class="linea-observacion"></div>
    </div>

    <div class="firmas">
        <div class="firma-box">
            <strong>TÉCNICO CRISTALERÍA</strong><br>
            <small>FECHA: _______________</small>
        </div>
        <div class="firma-box">
            <strong>SUPERVISOR</strong><br>
            <small>FECHA: _______________</small>
        </div>
        <div class="firma-box">
            <strong>CONTROL CALIDAD</strong><br>
            <small>FECHA: _______________</small>
        </div>
    </div>

    <script>
        // Auto-imprimir cuando se carga la página
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
