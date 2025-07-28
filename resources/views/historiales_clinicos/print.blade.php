<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HISTORIAL CLÍNICO - {{ strtoupper($historialClinico->nombres) }} {{ strtoupper($historialClinico->apellidos) }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            line-height: 1.4;
            color: #000;
            font-size: 14px;
            background: #ffffff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            margin-top: 70px;
            position: relative;
        }
        
        .logo {
            position: absolute;
            top: -70px;
            left: 0;
            width: 80px;
            height: auto;
            max-height: 60px;
            object-fit: contain;
        }
        
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }
        
        .fecha-impresion {
            text-align: right;
            margin-bottom: 15px;
            font-size: 13px;
            color: #000;
        }
        
        .empresa-info {
            text-align: center;
            margin: 5px 0;
            font-size: 16px;
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
            font-size: 13px;
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
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        
        .text-content {
            font-size: 13px;
            color: #000;
            line-height: 1.4;
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
            font-size: 13px;
            color: #000;
            margin-bottom: 5px;
        }
        
        .receta-content {
            margin-bottom: 10px;
        }
        
        .receta-table {
            width: 80%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .receta-table th,
        .receta-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 12px;
        }
        
        .receta-table th {
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            background: #f5f5f5;
        }
        
        .receta-table td {
            color: #000;
        }
        
        .receta-table td:first-child {
            font-weight: bold;
            color: #000;
            background: #f8f8f8;
        }
        
        /* Estilos específicos para las tablas de prescripción */
        .tabla-principal th:first-child,
        .tabla-principal td:first-child {
            width: 8%;
        }
        
        .tabla-principal th:not(:first-child),
        .tabla-principal td:not(:first-child) {
            width: 30.67%;
        }
        
        .tabla-adicional td {
            width: 25%;
            font-weight: bold;
            background: #f8f8f8;
        }
        
        .tabla-adicional td:nth-child(2),
        .tabla-adicional td:nth-child(4) {
            font-weight: normal;
            background: white;
        }
        
        .receta-info {
            font-size: 12px;
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
            font-size: 12px;
            color: #000;
        }
        
        @media print {
            body {
                margin: 10px;
                font-size: 12px;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .logo {
                width: 70px !important;
                max-height: 50px !important;
                top: -60px !important;
            }
            
            .header {
                margin-top: 60px !important;
            }
            
            .receta-table th,
            .receta-table td {
                font-size: 10px;
                padding: 3px;
                border: 1px solid #000 !important;
            }
            
            .receta-table th {
                background: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .receta-table td:first-child {
                background: #f8f8f8 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .tabla-principal th:first-child,
            .tabla-principal td:first-child {
                width: 6% !important;
            }
            
            .tabla-principal th:not(:first-child),
            .tabla-principal td:not(:first-child) {
                width: 31.33% !important;
            }
            
            .tabla-adicional td {
                width: 25% !important;
                font-weight: bold;
                background: #f8f8f8 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .tabla-adicional td:nth-child(2),
            .tabla-adicional td:nth-child(4) {
                font-weight: normal;
                background: white !important;
                -webkit-print-color-adjust: exact;
            }
            
            /* Evitar páginas en blanco adicionales */
            html, body {
                height: auto !important;
                overflow: hidden !important;
            }
            
            * {
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
            }
            
            .firma-section {
                margin-top: 20px !important;
                page-break-after: avoid !important;
            }
            
            /* Asegurar que el contenido termine sin espacio extra */
            body::after {
                content: "";
                display: block;
                height: 0;
                clear: both;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoFile = 'AdminLTELogo.png'; // Logo por defecto
            if($historialClinico->empresa) {
                $empresaNombre = strtoupper($historialClinico->empresa->nombre);
                if(str_starts_with($empresaNombre, 'TOP')) {
                    $logoFile = 'TOP.PNG';
                } elseif(str_starts_with($empresaNombre, 'BAN')) {
                    $logoFile = 'BAN.PNG';
                }
            }
        @endphp
        <img src="{{ asset($logoFile) }}" alt="Logo" class="logo">
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
                        <table class="receta-table tabla-principal">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ESFERA</th>
                                    <th>CILINDRO</th>
                                    <th>EJE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>OD</strong></td>
                                    <td>{{ $receta->od_esfera ?: '-' }}</td>
                                    <td>{{ $receta->od_cilindro ?: '-' }}</td>
                                    <td>{{ $receta->od_eje ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>OI</strong></td>
                                    <td>{{ $receta->oi_esfera ?: '-' }}</td>
                                    <td>{{ $receta->oi_cilindro ?: '-' }}</td>
                                    <td>{{ $receta->oi_eje ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Tabla adicional para ADD y DP -->
                        <table class="receta-table tabla-adicional" style="margin-top: 3px;">
                            <tbody>
                                <tr>
                                    <td>ADD</td>
                                    <td>{{ $receta->od_adicion ?: ($receta->oi_adicion ?: '-') }}</td>
                                    <td>DP pl/pc</td>
                                    <td>{{ $receta->dp ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Información adicional de la receta -->
                        <div class="receta-info">
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
                        
                        @if($historialClinico->diagnostico)
                            <div style="margin-top: 5px;">
                                <span class="receta-label">DIAGNÓSTICO:</span>
                                <span class="receta-value">{{ $historialClinico->diagnostico }}</span>
                            </div>
                        @endif
                        
                        <!-- Nota importante -->
                        <div style="margin-top: 10px; font-size: 12px; color: #000; line-height: 1.4; font-weight: bold;">
                            <strong>NOTA IMPORTANTE:</strong><br>
                            El período de adaptación del lente oftícovaria de 2 a 3 semanas, puede tener molestias como: mareos, dolor de cabeza, náuseas.<br>
                            Estas desaparecerán a medida que se adapte al lente.
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
