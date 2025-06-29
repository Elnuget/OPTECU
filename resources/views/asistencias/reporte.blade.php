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
    </style>

    <!-- Filtros de Fechas -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('asistencias.reporte') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="fecha_inicio">FECHA INICIO</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin">FECHA FIN</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4">
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
        <div class="col-lg-3 col-6">
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

        <div class="col-lg-3 col-6">
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

        <div class="col-lg-3 col-6">
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

        <div class="col-lg-3 col-6">
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
    </div>

    <!-- Tabla de Asistencias -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DETALLE DE ASISTENCIAS</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
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
                            <th>ENTRADA</th>
                            <th>SALIDA</th>
                            <th>ESTADO</th>
                            <th>HORAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asistencias as $asistencia)
                            <tr>
                                <td>{{ $asistencia->fecha_hora->format('d/m/Y') }}</td>
                                <td>{{ strtoupper($asistencia->user->name) }}</td>
                                <td>{{ $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '-' }}</td>
                                <td>{{ $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $asistencia->estado == 'presente' ? 'success' : ($asistencia->estado == 'tardanza' ? 'warning' : 'danger') }}">
                                        {{ strtoupper($asistencia->estado_formateado) }}
                                    </span>
                                </td>
                                <td>{{ $asistencia->horas_trabajadas ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráfico de Estadísticas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">GRÁFICO DE ESTADÍSTICAS</h3>
        </div>
        <div class="card-body">
            <canvas id="estadisticasChart" width="400" height="200"></canvas>
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
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
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
                    }
                }
            }
        });
    });

    function exportToExcel() {
        const table = document.getElementById('reporteTable');
        const wb = XLSX.utils.table_to_book(table, {sheet: "Asistencias"});
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `reporte_asistencias_${fecha}.xlsx`);
    }
</script>
@stop
