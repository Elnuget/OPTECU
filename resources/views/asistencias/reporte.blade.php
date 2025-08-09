@extends('adminlte::page')

@section('title', 'REPORTE DE ASISTENCIAS')

@section('content_header')
    <h1>REPORTE DE ASISTENCIAS</h1>
    <p>ESTADÍSTICAS Y RESUMEN DE ASISTENCIAS</p>
@stop

@section('content')
    <style>
        /* Convertir todo el texto a mayúsculas */
        body, 
        .content-wrapper, 
        .main-header, 
        .main-sidebar, 
        .card-title,
        .info-box-text,
        .info-box-number,
        .custom-select,
        .btn,
        label,
        input,
        select,
        option,
        .form-control,
        p,
        h1, h2, h3, h4, h5, h6,
        th,
        td,
        span,
        a,
        .dropdown-item,
        .alert,
        .modal-title,
        .modal-body p,
        .modal-content,
        .card-header,
        .card-footer,
        button,
        .close {
            text-transform: uppercase !important;
        }

        /* Estilos adicionales para las nuevas tarjetas */
        .small-box.bg-teal {
            background-color: #20c997 !important;
        }
        
        .small-box.bg-indigo {
            background-color: #6610f2 !important;
        }
        
        .small-box.bg-pink {
            background-color: #e83e8c !important;
        }

        /* Mejorar espaciado de badges */
        .badge {
            font-size: 0.8em;
            padding: 0.25em 0.6em;
        }

        /* Responsive para las tarjetas */
        @media (max-width: 768px) {
            .col-lg-2 {
                margin-bottom: 1rem;
            }
        }
    </style>

    <!-- Filtros de Fechas y Empresa -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('asistencias.reporte') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="fecha_inicio">FECHA INICIO</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin">FECHA FIN</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-3">
                        <label for="empresa_id">SUCURSAL</label>
                        <select name="empresa_id" class="form-control">
                            <option value="">TODAS LAS SUCURSALES</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ $empresaId == $empresa->id ? 'selected' : '' }}>
                                    {{ strtoupper($empresa->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">GENERAR REPORTE</button>
                            <a href="{{ route('asistencias.index') }}" class="btn btn-secondary">VOLVER</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total'] }}</h3>
                    <p>TOTAL REGISTROS</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['presentes'] }}</h3>
                    <p>PRESENTES</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['tardanzas'] }}</h3>
                    <p>TARDANZAS</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['ausentes'] }}</h3>
                    <p>AUSENTES</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $estadisticas['total_horas_trabajadas'] }}H</h3>
                    <p>HORAS TOTALES</p>
                </div>
                <div class="icon">
                    <i class="fas fa-business-time"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $estadisticas['total_minutos_retraso'] }}M</h3>
                    <p>RETRASO TOTAL</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda fila de estadísticas -->
    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ $estadisticas['total_horas_extra'] }}H</h3>
                    <p>HORAS EXTRA TOTALES</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-indigo">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['total_horas_trabajadas'] / max($estadisticas['total'], 1), 1) }}H</h3>
                    <p>PROMEDIO HORAS/DÍA</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-pink">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['total_minutos_retraso'] / max($estadisticas['tardanzas'], 1), 1) }}M</h3>
                    <p>PROMEDIO RETRASO</p>
                </div>
                <div class="icon">
                    <i class="fas fa-stopwatch"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Usuario -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RESUMEN POR USUARIO</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>USUARIO</th>
                            <th>EMPRESA</th>
                            <th>DÍAS TRABAJADOS</th>
                            <th>HORAS TOTALES</th>
                            <th>MINUTOS RETRASO</th>
                            <th>HORAS EXTRA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumenPorUsuario as $resumen)
                            <tr>
                                <td>{{ strtoupper($resumen['nombre']) }}</td>
                                <td>{{ strtoupper($resumen['empresa']) }}</td>
                                <td>{{ $resumen['dias_trabajados'] }}</td>
                                <td>{{ number_format($resumen['total_horas'], 2) }}H</td>
                                <td>
                                    <span class="badge badge-{{ $resumen['total_retraso'] > 0 ? 'warning' : 'success' }}">
                                        {{ $resumen['total_retraso'] }}M
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $resumen['total_horas_extra'] > 0 ? 'info' : 'secondary' }}">
                                        {{ number_format($resumen['total_horas_extra'], 2) }}H
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla de Asistencias -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DETALLE DE ASISTENCIAS</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                </button>
                <button type="button" class="btn btn-sm btn-info" onclick="window.print()">
                    <i class="fas fa-print"></i> IMPRIMIR
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reporteTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>USUARIO</th>
                            <th>EMPRESA</th>
                            <th>ENTRADA</th>
                            <th>SALIDA</th>
                            <th>ESTADO</th>
                            <th>HORAS</th>
                            <th>RETRASO (MIN)</th>
                            <th>HORAS EXTRA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asistencias as $asistencia)
                            <tr>
                                <td>{{ $asistencia->fecha_hora->format('d/m/Y') }}</td>
                                <td>{{ strtoupper($asistencia->user->name) }}</td>
                                <td>{{ strtoupper($asistencia->user->empresa ? $asistencia->user->empresa->nombre : 'SIN EMPRESA') }}</td>
                                <td>{{ $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '-' }}</td>
                                <td>{{ $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $asistencia->estado == 'presente' ? 'success' : ($asistencia->estado == 'tardanza' ? 'warning' : 'danger') }}">
                                        {{ strtoupper($asistencia->estado_formateado) }}
                                    </span>
                                </td>
                                <td>{{ $asistencia->horas_trabajadas ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $asistencia->minutos_retraso > 0 ? 'warning' : 'success' }}">
                                        {{ $asistencia->minutos_retraso }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $asistencia->horas_extra > 0 ? 'info' : 'secondary' }}">
                                        {{ number_format($asistencia->horas_extra, 2) }}H
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráficos de Estadísticas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">DISTRIBUCIÓN DE ASISTENCIAS</h3>
                </div>
                <div class="card-body">
                    <canvas id="estadisticasChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ANÁLISIS DE HORAS</h3>
                </div>
                <div class="card-body">
                    <canvas id="horasChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script>
    $(document).ready(function() {
        $('#reporteTable').DataTable({
            "language": {
                "url": "{{ asset('js/datatables/Spanish.json') }}"
            },
            "pageLength": 50,
            "order": [[ 0, "desc" ]]
        });

        // Crear gráfico
        const ctx = document.getElementById('estadisticasChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['PRESENTES', 'TARDANZAS', 'AUSENTES'],
                datasets: [{
                    data: [
                        {{ $estadisticas['presentes'] }},
                        {{ $estadisticas['tardanzas'] }},
                        {{ $estadisticas['ausentes'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = {{ $estadisticas['total'] }};
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Crear segundo gráfico para horas
        const ctx2 = document.getElementById('horasChart').getContext('2d');
        const horasChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['HORAS TRABAJADAS', 'HORAS EXTRA', 'HORAS PERDIDAS (RETRASO)'],
                datasets: [{
                    data: [
                        {{ $estadisticas['total_horas_trabajadas'] }},
                        {{ $estadisticas['total_horas_extra'] }},
                        {{ round($estadisticas['total_minutos_retraso'] / 60, 2) }}
                    ],
                    backgroundColor: [
                        '#17a2b8',
                        '#28a745',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'HORAS'
                        }
                    }
                }
            }
        });
    });

    function exportToExcel() {
        // Crear workbook
        const wb = XLSX.utils.book_new();
        
        // Hoja 1: Resumen General
        const resumenData = [
            ['ESTADÍSTICAS GENERALES'],
            [''],
            ['MÉTRICA', 'VALOR'],
            ['Total Registros', {{ $estadisticas['total'] }}],
            ['Presentes', {{ $estadisticas['presentes'] }}],
            ['Tardanzas', {{ $estadisticas['tardanzas'] }}],
            ['Ausentes', {{ $estadisticas['ausentes'] }}],
            ['Total Horas Trabajadas', '{{ $estadisticas['total_horas_trabajadas'] }}H'],
            ['Total Minutos Retraso', '{{ $estadisticas['total_minutos_retraso'] }}M'],
            ['Total Horas Extra', '{{ $estadisticas['total_horas_extra'] }}H'],
            ['Promedio Horas/Día', '{{ number_format($estadisticas['total_horas_trabajadas'] / max($estadisticas['total'], 1), 1) }}H'],
            ['Promedio Retraso', '{{ number_format($estadisticas['total_minutos_retraso'] / max($estadisticas['tardanzas'], 1), 1) }}M']
        ];
        const ws1 = XLSX.utils.aoa_to_sheet(resumenData);
        XLSX.utils.book_append_sheet(wb, ws1, "Resumen");

        // Hoja 2: Resumen por Usuario
        const resumenUsuarios = [
            ['RESUMEN POR USUARIO'],
            [''],
            ['USUARIO', 'EMPRESA', 'DÍAS TRABAJADOS', 'HORAS TOTALES', 'MINUTOS RETRASO', 'HORAS EXTRA']
        ];
        @foreach ($resumenPorUsuario as $resumen)
            resumenUsuarios.push([
                '{{ strtoupper($resumen['nombre']) }}',
                '{{ strtoupper($resumen['empresa']) }}',
                {{ $resumen['dias_trabajados'] }},
                '{{ number_format($resumen['total_horas'], 2) }}H',
                '{{ $resumen['total_retraso'] }}M',
                '{{ number_format($resumen['total_horas_extra'], 2) }}H'
            ]);
        @endforeach
        const ws2 = XLSX.utils.aoa_to_sheet(resumenUsuarios);
        XLSX.utils.book_append_sheet(wb, ws2, "Por Usuario");

        // Hoja 3: Detalle de Asistencias
        const table = document.getElementById('reporteTable');
        const ws3 = XLSX.utils.table_to_sheet(table);
        XLSX.utils.book_append_sheet(wb, ws3, "Detalle");

        // Guardar archivo
        const fecha = new Date().toISOString().split('T')[0];
        const empresa = '{{ $empresaId ? $empresas->find($empresaId)->nombre : 'TODAS' }}';
        XLSX.writeFile(wb, `reporte_asistencias_${empresa}_${fecha}.xlsx`);
    }
</script>
@stop
