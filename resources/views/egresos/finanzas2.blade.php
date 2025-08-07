@extends('adminlte::page')

@section('title', 'Finanzas')

@section('content_header')
    <h1>
        <i class="fas fa-coins"></i> Finanzas
    </h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Resumen General -->
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Resumen Financiero
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Ingresos del Mes</span>
                                        <span class="info-box-number" id="ingresos-mes">$ 0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Egresos del Mes</span>
                                        <span class="info-box-number" id="egresos-mes">$ 0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Ganancia Neta</span>
                                        <span class="info-box-number" id="ganancia-neta">$ 0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-secondary">
                                    <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Margen</span>
                                        <span class="info-box-number" id="margen">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Filtros -->
            <div class="col-md-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i> Filtros
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="filtros-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="ano">Año:</label>
                                        <select class="form-control" id="ano" name="ano">
                                            @for($i = date('Y'); $i >= 2020; $i--)
                                                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="mes">Mes:</label>
                                        <select class="form-control" id="mes" name="mes">
                                            <option value="">Todos los meses</option>
                                            <option value="1" {{ date('n') == 1 ? 'selected' : '' }}>Enero</option>
                                            <option value="2" {{ date('n') == 2 ? 'selected' : '' }}>Febrero</option>
                                            <option value="3" {{ date('n') == 3 ? 'selected' : '' }}>Marzo</option>
                                            <option value="4" {{ date('n') == 4 ? 'selected' : '' }}>Abril</option>
                                            <option value="5" {{ date('n') == 5 ? 'selected' : '' }}>Mayo</option>
                                            <option value="6" {{ date('n') == 6 ? 'selected' : '' }}>Junio</option>
                                            <option value="7" {{ date('n') == 7 ? 'selected' : '' }}>Julio</option>
                                            <option value="8" {{ date('n') == 8 ? 'selected' : '' }}>Agosto</option>
                                            <option value="9" {{ date('n') == 9 ? 'selected' : '' }}>Septiembre</option>
                                            <option value="10" {{ date('n') == 10 ? 'selected' : '' }}>Octubre</option>
                                            <option value="11" {{ date('n') == 11 ? 'selected' : '' }}>Noviembre</option>
                                            <option value="12" {{ date('n') == 12 ? 'selected' : '' }}>Diciembre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="empresa">Empresa:</label>
                                        <select class="form-control" id="empresa" name="empresa">
                                            <option value="">Todas las empresas</option>
                                            @if(isset($empresas))
                                                @foreach($empresas as $empresa)
                                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-primary" onclick="cargarDatos()">
                                                <i class="fas fa-search"></i> Filtrar
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                                                <i class="fas fa-times"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Ingresos vs Egresos -->
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i> Ingresos vs Egresos
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartIngresosEgresos" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribución de Egresos -->
            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Distribución de Egresos
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartDistribucionEgresos" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tabla de Movimientos Recientes -->
            <div class="col-md-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Movimientos Recientes
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabla-movimientos">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Usuario</th>
                                        <th>Empresa</th>
                                        <th class="text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box-number {
            font-weight: bold;
        }
        .card-header {
            font-weight: bold;
        }
        .table th {
            background-color: #f8f9fa;
        }
        #tabla-movimientos {
            font-size: 0.9em;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartIngresosEgresos;
        let chartDistribucionEgresos;

        $(document).ready(function() {
            cargarDatos();
            
            // Inicializar DataTable
            $('#tabla-movimientos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "order": [[ 0, "desc" ]],
                "pageLength": 10
            });
        });

        function cargarDatos() {
            const ano = $('#ano').val();
            const mes = $('#mes').val();
            const empresa = $('#empresa').val();

            // Mostrar loading
            $('#ingresos-mes').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#egresos-mes').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#ganancia-neta').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#margen').html('<i class="fas fa-spinner fa-spin"></i>');

            // Cargar datos financieros reales
            $.ajax({
                url: '{{ route("egresos.datos-financieros") }}',
                method: 'GET',
                data: {
                    ano: ano,
                    mes: mes,
                    empresa: empresa
                },
                success: function(data) {
                    $('#ingresos-mes').text('$ ' + number_format(data.ingresos));
                    $('#egresos-mes').text('$ ' + number_format(data.egresos));
                    $('#ganancia-neta').text('$ ' + number_format(data.ganancia));
                    $('#margen').text(data.margen + '%');

                    // Actualizar gráficos
                    actualizarGraficos();
                    cargarMovimientos();
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar datos financieros:', error);
                    $('#ingresos-mes').text('Error');
                    $('#egresos-mes').text('Error');
                    $('#ganancia-neta').text('Error');
                    $('#margen').text('Error');
                }
            });
        }

        function actualizarGraficos() {
            const ano = $('#ano').val();
            const mes = $('#mes').val();
            const empresa = $('#empresa').val();

            // Cargar datos para gráficos
            $.ajax({
                url: '{{ route("egresos.graficos-financieros") }}',
                method: 'GET',
                data: {
                    ano: ano,
                    mes: mes,
                    empresa: empresa
                },
                success: function(data) {
                    // Destruir gráficos existentes
                    if (chartIngresosEgresos) {
                        chartIngresosEgresos.destroy();
                    }
                    if (chartDistribucionEgresos) {
                        chartDistribucionEgresos.destroy();
                    }

                    // Preparar datos para gráfico de ingresos vs egresos
                    const datosIngresoEgreso = {
                        labels: data.ingresoEgreso.labels,
                        datasets: [{
                            label: 'Ingresos',
                            data: data.ingresoEgreso.ingresos,
                            backgroundColor: '#28a745',
                            borderWidth: 2
                        }, {
                            label: 'Egresos',
                            data: data.ingresoEgreso.egresos,
                            backgroundColor: '#ffc107',
                            borderWidth: 2
                        }]
                    };

                    // Preparar datos para distribución de egresos
                    const datosDistribucion = {
                        labels: data.distribucion.labels,
                        datasets: [{
                            data: data.distribucion.data,
                            backgroundColor: [
                                '#dc3545', '#6f42c1', '#fd7e14', '#6c757d', 
                                '#20c997', '#e83e8c', '#17a2b8', '#28a745',
                                '#ffc107', '#f8f9fa'
                            ],
                            borderWidth: 2
                        }]
                    };

                    // Crear gráfico de Ingresos vs Egresos
                    const ctx1 = document.getElementById('chartIngresosEgresos').getContext('2d');
                    chartIngresosEgresos = new Chart(ctx1, {
                        type: 'bar',
                        data: datosIngresoEgreso,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$ ' + number_format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Crear gráfico de Distribución de Egresos
                    const ctx2 = document.getElementById('chartDistribucionEgresos').getContext('2d');
                    chartDistribucionEgresos = new Chart(ctx2, {
                        type: 'doughnut',
                        data: datosDistribucion,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar gráficos:', error);
                }
            });
        }

        function cargarMovimientos() {
            const ano = $('#ano').val();
            const mes = $('#mes').val();
            const empresa = $('#empresa').val();

            // Cargar movimientos recientes reales
            $.ajax({
                url: '{{ route("egresos.movimientos-recientes") }}',
                method: 'GET',
                data: {
                    ano: ano,
                    mes: mes,
                    empresa: empresa
                },
                success: function(movimientos) {
                    const tbody = $('#tabla-movimientos tbody');
                    tbody.empty();

                    if (movimientos.length === 0) {
                        tbody.append(`
                            <tr>
                                <td colspan="6" class="text-center">No hay movimientos para mostrar</td>
                            </tr>
                        `);
                        return;
                    }

                    movimientos.forEach(mov => {
                        const row = `
                            <tr>
                                <td>${mov.fecha}</td>
                                <td>
                                    <span class="badge ${mov.tipo === 'Ingreso' ? 'badge-success' : 'badge-danger'}">
                                        ${mov.tipo}
                                    </span>
                                </td>
                                <td>${mov.concepto}</td>
                                <td>${mov.usuario}</td>
                                <td>${mov.empresa}</td>
                                <td class="text-right ${mov.monto >= 0 ? 'text-success' : 'text-danger'}">
                                    $ ${number_format(Math.abs(mov.monto))}
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    // Reinicializar DataTable
                    if ($.fn.DataTable.isDataTable('#tabla-movimientos')) {
                        $('#tabla-movimientos').DataTable().destroy();
                    }
                    
                    $('#tabla-movimientos').DataTable({
                        "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                        },
                        "order": [[ 0, "desc" ]],
                        "pageLength": 10
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar movimientos:', error);
                    const tbody = $('#tabla-movimientos tbody');
                    tbody.empty();
                    tbody.append(`
                        <tr>
                            <td colspan="6" class="text-center text-danger">Error al cargar movimientos</td>
                        </tr>
                    `);
                }
            });
        }

        function limpiarFiltros() {
            $('#ano').val(new Date().getFullYear());
            $('#mes').val('{{ date("n") }}');
            $('#empresa').val('');
            cargarDatos();
        }

        function number_format(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
@stop
