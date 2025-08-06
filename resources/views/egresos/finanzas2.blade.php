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

            // Simular carga de datos (aquí deberías hacer una llamada AJAX real)
            setTimeout(() => {
                // Datos de ejemplo - deberían venir del servidor
                const ingresos = 150000;
                const egresos = 80000;
                const ganancia = ingresos - egresos;
                const margen = ingresos > 0 ? ((ganancia / ingresos) * 100).toFixed(1) : 0;

                $('#ingresos-mes').text('$ ' + number_format(ingresos));
                $('#egresos-mes').text('$ ' + number_format(egresos));
                $('#ganancia-neta').text('$ ' + number_format(ganancia));
                $('#margen').text(margen + '%');

                // Actualizar gráficos
                actualizarGraficos();
                cargarMovimientos();
            }, 1000);
        }

        function actualizarGraficos() {
            // Datos de ejemplo para los gráficos
            const datosIngresoEgreso = {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    data: [150000, 80000],
                    backgroundColor: ['#28a745', '#ffc107'],
                    borderWidth: 2
                }]
            };

            const datosDistribucion = {
                labels: ['Sueldos', 'Gastos Operativos', 'Inventario', 'Otros'],
                datasets: [{
                    data: [40000, 20000, 15000, 5000],
                    backgroundColor: ['#dc3545', '#6f42c1', '#fd7e14', '#6c757d'],
                    borderWidth: 2
                }]
            };

            // Destruir gráficos existentes
            if (chartIngresosEgresos) {
                chartIngresosEgresos.destroy();
            }
            if (chartDistribucionEgresos) {
                chartDistribucionEgresos.destroy();
            }

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
                            display: false
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
        }

        function cargarMovimientos() {
            // Simular datos de movimientos recientes
            const movimientos = [
                {
                    fecha: '2025-08-06',
                    tipo: 'Ingreso',
                    concepto: 'Venta de productos',
                    usuario: 'Juan Pérez',
                    empresa: 'ESCLERÓPTICA',
                    monto: 15000
                },
                {
                    fecha: '2025-08-05',
                    tipo: 'Egreso',
                    concepto: 'Pago de sueldo',
                    usuario: 'María García',
                    empresa: 'ESCLERÓPTICA',
                    monto: -8000
                }
            ];

            const tbody = $('#tabla-movimientos tbody');
            tbody.empty();

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
        }

        function limpiarFiltros() {
            $('#ano').val(new Date().getFullYear());
            $('#mes').val(new Date().getMonth() + 1);
            $('#empresa').val('');
            cargarDatos();
        }

        function number_format(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
@stop
