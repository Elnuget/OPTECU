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
            line-height: 1.4;
            color: #333;
            font-size: 14px;
        }
        
        .header {
            text-align: center;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .fecha-impresion {
            text-align: left;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .empresa-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            text-transform: uppercase;
            color: #555;
        }
        
        .patient-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 120px;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .info-value {
            margin-left: 10px;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section h4 {
            background: #333;
            color: white;
            padding: 8px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .section-content {
            padding: 0 10px;
        }
        
        .text-content {
            background: white;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            min-height: 30px;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .recetas-section {
            margin-top: 20px;
        }
        
        .receta-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .receta-header {
            background: #007bff;
            color: white;
            padding: 8px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
        }
        
        .receta-content {
            padding: 10px;
        }
        
        .receta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .receta-table th,
        .receta-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            font-size: 12px;
        }
        
        .receta-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .receta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .receta-field {
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }
        
        .receta-label {
            font-weight: bold;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        .receta-value {
            font-size: 12px;
            color: #333;
            text-transform: uppercase;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        @media print {
            body {
                margin: 0;
                font-size: 12px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .header h2 {
                font-size: 16px;
            }
            
            .section {
                page-break-inside: avoid;
            }
            
            .receta-card {
                page-break-inside: avoid;
            }
            
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .no-content {
            color: #999;
            font-style: italic;
            text-transform: uppercase;
        }
        
        .essential-only {
            display: block;
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

    <!-- MOTIVO DE CONSULTA (solo si tiene contenido) -->
    @if($historialClinico->motivo_consulta)
    <div class="section">
        <h4>MOTIVO DE CONSULTA</h4>
        <div class="section-content">
            <div class="text-content">
                {{ strtoupper($historialClinico->motivo_consulta) }}
            </div>
        </div>
    </div>
    @endif

    <!-- DIAGNÓSTICO (solo si tiene contenido) -->
    @if($historialClinico->diagnostico)
    <div class="section">
        <h4>DIAGNÓSTICO</h4>
        <div class="section-content">
            <div class="text-content">
                {{ strtoupper($historialClinico->diagnostico) }}
            </div>
        </div>
    </div>
    @endif

    <!-- TRATAMIENTO (solo si tiene contenido) -->
    @if($historialClinico->tratamiento)
    <div class="section">
        <h4>TRATAMIENTO</h4>
        <div class="section-content">
            <div class="text-content">
                {{ strtoupper($historialClinico->tratamiento) }}
            </div>
        </div>
    </div>
    @endif

    <!-- RECETAS ASOCIADAS -->
    @if($historialClinico->recetas && $historialClinico->recetas->count() > 0)
        <div class="recetas-section">
            <h4 style="background: #28a745; color: white; padding: 10px; margin: 0 0 15px 0; text-transform: uppercase; font-size: 16px;">
                RECETAS MÉDICAS ({{ $historialClinico->recetas->count() }})
            </h4>
            
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
                        <div class="receta-grid">
                            @if($receta->distancia_pupilar)
                            <div class="receta-field">
                                <div class="receta-label">DIST. PUPILAR:</div>
                                <div class="receta-value">{{ $receta->distancia_pupilar }}</div>
                            </div>
                            @endif
                            
                            @if($receta->tipo_lente)
                            <div class="receta-field">
                                <div class="receta-label">TIPO LENTE:</div>
                                <div class="receta-value">{{ strtoupper($receta->tipo_lente) }}</div>
                            </div>
                            @endif
                            
                            @if($receta->material)
                            <div class="receta-field">
                                <div class="receta-label">MATERIAL:</div>
                                <div class="receta-value">{{ strtoupper($receta->material) }}</div>
                            </div>
                            @endif
                            
                            @if($receta->filtros)
                            <div class="receta-field">
                                <div class="receta-label">FILTROS:</div>
                                <div class="receta-value">{{ strtoupper($receta->filtros) }}</div>
                            </div>
                            @endif
                            
                            @if($receta->precio)
                            <div class="receta-field">
                                <div class="receta-label">PRECIO:</div>
                                <div class="receta-value">${{ number_format($receta->precio, 2) }}</div>
                            </div>
                            @endif
                        </div>
                        
                        @if($receta->observaciones)
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                <div class="receta-label">OBSERVACIONES:</div>
                                <div class="text-content" style="margin-top: 5px; font-size: 11px;">
                                    {{ strtoupper($receta->observaciones) }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- COTIZACIÓN (solo si tiene contenido) -->
    @if($historialClinico->cotizacion)
    <div class="section">
        <h4>COTIZACIÓN</h4>
        <div class="section-content">
            <div class="text-content">
                {{ strtoupper($historialClinico->cotizacion) }}
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>HISTORIAL CLÍNICO GENERADO EL {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        @if($historialClinico->empresa)
            <p>{{ strtoupper($historialClinico->empresa->nombre) }}</p>
        @endif
    </div>

    <script>
        // Auto-imprimir al cargar la página
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
