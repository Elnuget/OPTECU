<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HISTORIAL CLÍNICO - {{ strtoupper($historialClinico->nombres) }} {{ strtoupper($historialClinico->apellidos) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.3;
            color: #000;
            font-size: 12px;
            background: #ffffff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }
        
        .fecha-impresion {
            text-align: right;
            margin-bottom: 15px;
            font-size: 11px;
            color: #000;
        }
        
        .empresa-info {
            text-align: center;
            margin: 5px 0;
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        
        .patient-info {
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
        }
        
        .info-item {
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
            color: #000;
        }
        
        .info-value {
            color: #000;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section h4 {
            padding: 0;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: bold;
            color: #000;
        }
        
        .text-content {
            font-size: 11px;
            color: #000;
            line-height: 1.3;
            margin-bottom: 5px;
        }
        
        .recetas-section {
            margin-top: 15px;
        }
        
        .receta-card {
            margin-bottom: 10px;
        }
        
        .receta-header {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            color: #000;
            margin-bottom: 5px;
        }
        
        .receta-content {
            margin-bottom: 10px;
        }
        
        .receta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .receta-table th,
        .receta-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }
        
        .receta-table th {
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }
        
        .receta-table td {
            color: #000;
        }
        
        .receta-table td:first-child {
            font-weight: bold;
            color: #000;
        }
        
        .receta-info {
            font-size: 10px;
            margin-top: 5px;
        }
        
        .receta-info-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 2px;
        }
        
        .receta-label {
            font-weight: bold;
            color: #000;
        }
        
        .receta-value {
            color: #000;
        }
        
        .firma-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .firma-box {
            width: 45%;
            text-align: center;
        }
        
        .firma-linea {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }
        
        .firma-texto {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            color: #000;
        }
        
        @media print {
            body {
                margin: 10px;
                font-size: 10px;
            }
            
            .header h1 {
                font-size: 16px;
            }
            
            .receta-table th,
            .receta-table td {
                font-size: 9px;
                padding: 3px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RECETA DE LENTES</h1>
        @if($historialClinico->empresa)
            <div class="empresa-info">
                {{ strtoupper($historialClinico->empresa->nombre) }}
            </div>
        @endif
    </div>

    <div class="fecha-impresion">
        FECHA: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>

    <!-- INFORMACIÓN ESENCIAL DEL PACIENTE -->
    <div class="patient-info">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">NOMBRES:</span>
                <span class="info-value">{{ strtoupper($historialClinico->nombres) ?? 'NO ESPECIFICADO' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">APELLIDOS:</span>
                <span class="info-value">{{ strtoupper($historialClinico->apellidos) ?? 'NO ESPECIFICADO' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">RUT:</span>
                <span class="info-value">{{ $historialClinico->cedula ?? 'NO ESPECIFICADO' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">EDAD:</span>
                <span class="info-value">{{ $historialClinico->edad ?? 'NO ESPECIFICADO' }} AÑOS</span>
            </div>
            <div class="info-item">
                <span class="info-label">FECHA NAC.:</span>
                <span class="info-value">{{ $historialClinico->fecha_nacimiento ? \Carbon\Carbon::parse($historialClinico->fecha_nacimiento)->format('d/m/Y') : 'NO ESPECIFICADO' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">CELULAR:</span>
                <span class="info-value">{{ $historialClinico->celular ?? 'NO ESPECIFICADO' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">FECHA CONSULTA:</span>
                <span class="info-value">{{ $historialClinico->fecha ? \Carbon\Carbon::parse($historialClinico->fecha)->format('d/m/Y') : 'NO ESPECIFICADO' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ATENDIDO POR:</span>
                <span class="info-value">{{ strtoupper($historialClinico->usuario->name ?? 'NO ESPECIFICADO') }}</span>
            </div>
            @if($historialClinico->proxima_consulta)
            <div class="info-item">
                <span class="info-label">PRÓXIMA CONSULTA:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($historialClinico->proxima_consulta)->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- RECETAS ASOCIADAS -->
    @if($historialClinico->recetas && $historialClinico->recetas->count() > 0)
        <div class="recetas-section">
            <h4>PRESCRIPCIÓN PARA LEJOS ({{ $historialClinico->recetas->count() }})</h4>
            
            @foreach($historialClinico->recetas as $index => $receta)
                <div class="receta-card">
                    <div class="receta-header">
                        RECETA #{{ $index + 1 }} - {{ $receta->created_at ? \Carbon\Carbon::parse($receta->created_at)->format('d/m/Y') : 'FECHA NO DISPONIBLE' }}
                    </div>
                    <div class="receta-content">
                        <!-- Tabla principal de prescripción -->
                        <table class="receta-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ESFERA</th>
                                    <th>CILINDRO</th>
                                    <th>EJE</th>
                                    <th>ADICIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>OD</strong></td>
                                    <td>{{ $receta->od_esfera ?: '-' }}</td>
                                    <td>{{ $receta->od_cilindro ?: '-' }}</td>
                                    <td>{{ $receta->od_eje ?: '-' }}</td>
                                    <td>{{ $receta->od_adicion ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>OI</strong></td>
                                    <td>{{ $receta->oi_esfera ?: '-' }}</td>
                                    <td>{{ $receta->oi_cilindro ?: '-' }}</td>
                                    <td>{{ $receta->oi_eje ?: '-' }}</td>
                                    <td>{{ $receta->oi_adicion ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Información adicional de la receta -->
                        <div class="receta-info">
                            @if($receta->distancia_pupilar)
                            <span class="receta-info-item">
                                <span class="receta-label">DIST. PUPILAR:</span>
                                <span class="receta-value">{{ $receta->distancia_pupilar }}</span>
                            </span>
                            @endif
                            
                            @if($receta->tipo_lente)
                            <span class="receta-info-item">
                                <span class="receta-label">TIPO LENTE:</span>
                                <span class="receta-value">{{ strtoupper($receta->tipo_lente) }}</span>
                            </span>
                            @endif
                            
                            @if($receta->material)
                            <span class="receta-info-item">
                                <span class="receta-label">MATERIAL:</span>
                                <span class="receta-value">{{ strtoupper($receta->material) }}</span>
                            </span>
                            @endif
                            
                            @if($receta->filtros)
                            <span class="receta-info-item">
                                <span class="receta-label">FILTROS:</span>
                                <span class="receta-value">{{ strtoupper($receta->filtros) }}</span>
                            </span>
                            @endif
                            
                            @if($receta->precio)
                            <span class="receta-info-item">
                                <span class="receta-label">PRECIO:</span>
                                <span class="receta-value">${{ number_format($receta->precio, 2) }}</span>
                            </span>
                            @endif
                        </div>
                        
                        @if($receta->observaciones)
                            <div style="margin-top: 5px;">
                                <span class="receta-label">OBSERVACIONES:</span>
                                <span class="receta-value">{{ $receta->observaciones }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- DIAGNÓSTICO (solo si tiene contenido) -->
    @if($historialClinico->diagnostico)
    <div class="section">
        <h4>DIAGNÓSTICO:</h4>
        <div class="text-content">
            {{ $historialClinico->diagnostico }}
        </div>
    </div>
    @endif

    <!-- TRATAMIENTO (solo si tiene contenido) -->
    @if($historialClinico->tratamiento)
    <div class="section">
        <h4>TRATAMIENTO:</h4>
        <div class="text-content">
            {{ $historialClinico->tratamiento }}
        </div>
    </div>
    @endif

    <!-- COTIZACIÓN (solo si tiene contenido) -->
    @if($historialClinico->cotizacion)
    <div class="section">
        <h4>COTIZACIÓN:</h4>
        <div class="text-content">
            {{ $historialClinico->cotizacion }}
        </div>
    </div>
    @endif

    <!-- ESPACIOS PARA FIRMAS -->
    <div class="firma-section">
        <div class="firma-box">
            <div class="firma-linea"></div>
            <p class="firma-texto">FIRMA Y TIMBRE</p>
        </div>
        <div class="firma-box">
            <div class="firma-linea"></div>
            <p class="firma-texto">PACIENTE CONFORME</p>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar la página
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
