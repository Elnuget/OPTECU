@extends('adminlte::page')

@section('title', 'Panel Administrador')

@section('content_header')
<div class="dashboard-header mb-4">
    <h1 class="font-weight-bold">Panel Administrador</h1>
    <div class="date-filter d-flex align-items-center mt-3">
        <form action="{{ route('admin.index') }}" method="GET" class="d-flex">
            <select name="year" class="custom-select mr-2" style="width: auto;" onchange="this.form.submit()">
                @foreach($salesData['years'] as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
            <select name="month" class="custom-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">Todos los meses</option>
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}" {{ $selectedMonth == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>
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
    .custom-select {
        text-transform: uppercase !important;
    }
</style>

<div class="row">
    {{-- Ranking de Pedidos por Usuario (Fila completa) --}}
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#rankingPedidosUsuario">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-trophy mr-2"></i>Top Usuarios por Número de Pedidos
                    </h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="rankingPedidosUsuario">
                <div class="card-body">
                    <canvas id="rankingPedidosChart" style="height: 500px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Ranking de Ventas por Empresa (Fila completa) --}}
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#rankingVentasEmpresa">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-building mr-2"></i>Ranking de Ventas por Empresa
                    </h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="rankingVentasEmpresa">
                <div class="card-body">
                    <canvas id="rankingEmpresasChart" style="height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen General de Ventas (Ancho completo) --}}
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#resumenVentas">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i>Resumen General de Ventas
                    </h3>
                    <i class="fas fa-chevron-down collapsed"></i>
                </div>
            </div>
            <div class="collapse" id="resumenVentas">
                <div class="card-body">
                    <canvas id="salesChart" style="min-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Ventas por Usuario y Ubicación (Dos columnas) --}}
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#ventasUsuario">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users mr-2"></i>Ventas por Usuario
                    </h3>
                    <i class="fas fa-chevron-down collapsed"></i>
                </div>
            </div>
            <div class="collapse" id="ventasUsuario">
                <div class="card-body">
                    <canvas id="userSalesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#ventasUbicacion">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt mr-2"></i>Ventas por Ubicación
                    </h3>
                    <i class="fas fa-chevron-down collapsed"></i>
                </div>
            </div>
            <div class="collapse" id="ventasUbicacion">
                <div class="card-body" style="height: 300px; padding: 0;">
                    <div style="width: 100%; height: 100%;">
                        <canvas id="locationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Puntuaciones y Últimos Pedidos (Dos columnas) --}}
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#puntuacionesUsuario">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-star mr-2"></i>Puntuaciones por Usuario
                    </h3>
                    <i class="fas fa-chevron-down collapsed"></i>
                </div>
            </div>
            <div class="collapse" id="puntuacionesUsuario">
                <div class="card-body">
                    <canvas id="ratingChart" style="height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#ultimosPedidos">
                <div class="header-container">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shopping-cart mr-2"></i>Últimos Pedidos
                    </h3>
                    <i class="fas fa-chevron-down collapsed"></i>
                </div>
            </div>
            <div class="collapse" id="ultimosPedidos">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                                <tr>
                                    <td>#{{ $pedido->id }}</td>
                                    <td>{{ $pedido->cliente }}</td>
                                    <td>${{ number_format($pedido->total, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $pedido->estado == 'Completado' ? 'success' : 'warning' }}">
                                            {{ $pedido->estado }}
                                        </span>
                                    </td>
                                    <td>{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Calificaciones de Pedidos --}}
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header border-0 bg-transparent" data-toggle="collapse" data-target="#calificacionesPedidos">
            <div class="header-container">
                <h3 class="card-title mb-0">
                    <i class="fas fa-star mr-2"></i>Calificaciones de Pedidos
                </h3>
                <i class="fas fa-chevron-down collapsed"></i>
            </div>
        </div>
        <div class="collapse" id="calificacionesPedidos">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Usuario</th>
                            <th>Calificación</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidosCalificados as $pedido)
                            <tr>
                                <td>#{{ $pedido->id }}</td>
                                <td>{{ $pedido->cliente }}</td>
                                <td>{{ $pedido->usuario }}</td>
                                <td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pedido->calificacion ? 'text-warning' : 'text-secondary' }}"></i>
                                    @endfor
                                    ({{ $pedido->calificacion }}/5)
                                </td>
                                <td>{{ $pedido->comentario_calificacion ?? 'Sin comentario' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    .dashboard-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 1.5rem;
        border-radius: 0.5rem;
        color: white;
        margin-bottom: 1.5rem;
    }

    .card {
        border: none;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 60px;
        cursor: pointer;
    }

    .card-header .card-title {
        margin: 0;
        color: #5a5c69;
        font-size: 1rem;
        font-weight: 700;
        flex: 1;
        padding-right: 20px;
    }

    .card-header .fa-chevron-down {
        transition: transform 0.3s ease;
        width: 20px;
        text-align: center;
        position: relative;
        right: 0;
    }

    .card-header.collapsed .fa-chevron-down,
    .card-header .collapsed.fa-chevron-down {
        transform: rotate(-90deg);
    }

    .card-header:hover {
        background-color: rgba(0,0,0,.03);
    }

    .card-title {
        margin: 0;
        color: #5a5c69;
        font-size: 1rem;
        font-weight: 700;
    }

    .table td, .table th {
        padding: 1rem;
        vertical-align: middle;
    }

    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }

    .custom-select {
        background-color: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem;
    }

    .custom-select option {
        color: #333;
    }

    .fa-chevron-down {
        transition: transform 0.3s ease;
    }

    .collapsed .fa-chevron-down {
        transform: rotate(-90deg);
    }

    .header-container {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Registrar el plugin personalizado para mostrar valores en las barras
    Chart.register({
        id: 'dataLabels',
        afterDatasetsDraw: function(chart) {
            const ctx = chart.ctx;
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                if (!meta.hidden) {
                    meta.data.forEach((element, index) => {
                        // Obtener posición del elemento
                        const position = element.tooltipPosition();
                        const value = dataset.data[index];
                        
                        // Configurar texto
                        ctx.fillStyle = '#333';
                        ctx.font = 'bold 11px Arial';
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        
                        // Dibujar texto
                        ctx.fillText(value + ' pedidos', position.x + 5, position.y);
                    });
                }
            });
        }
    });
    // Gráfico de Resumen General de Ventas
    const salesChart = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($salesDataMonthly['months']) !!},
            datasets: [{
                label: 'Ventas Mensuales',
                data: {!! json_encode($salesDataMonthly['totals']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#fff',
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#4e73df',
                pointHoverBorderColor: '#fff',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFont: {
                        size: 14
                    },
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Ventas: $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2],
                        drawBorder: false,
                        zeroLineColor: '#dddfeb',
                        zeroLineBorderDash: [2],
                        zeroLineBorderDashOffset: [2]
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            }
        }
    });

    // Gráfico de Ventas por Usuario
    const userSalesChart = new Chart(document.getElementById('userSalesChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($userSalesData['users']) !!},
            datasets: [{
                label: 'Monto Total ($)',
                data: {!! json_encode($userSalesData['totals']) !!},
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                borderRadius: 6,
                yAxisID: 'y',
                order: 1
            }, {
                label: 'Cantidad Vendida',
                data: {!! json_encode($userSalesData['quantities']) !!},
                backgroundColor: 'rgba(28, 200, 138, 0.8)',
                borderColor: 'rgba(28, 200, 138, 1)',
                borderWidth: 1,
                borderRadius: 6,
                yAxisID: 'y1',
                order: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#5a5c69',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyColor: '#858796',
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Monto Total ($)') {
                                return `Ventas: $${context.parsed.x.toFixed(2)}`;
                            } else {
                                return `Cantidad: ${context.parsed.x} unidades`;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    position: 'top',
                    grid: {
                        borderDash: [2],
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                },
                y: {
                    ticks: {
                        padding: 10,
                        font: {
                            weight: '500'
                        }
                    }
                }
            },
            x1: {
                beginAtZero: true,
                position: 'bottom',
                grid: {
                    drawOnChartArea: false
                },
                ticks: {
                    callback: function(value) {
                        return value + ' uds.';
                    }
                }
            }
        },
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 10
            }
        }
    });

    // Gráfico de Ventas por Ubicación
    const locationChart = new Chart(document.getElementById('locationChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($ventasPorLugar->pluck('lugar')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($ventasPorLugar->pluck('total_ventas')->toArray()) !!},
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value * 100) / total).toFixed(1);
                            return `${context.label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 0,
                    right: 20,
                    top: 0,
                    bottom: 0
                }
            },
            cutout: '50%'
        }
    });

    // Gráfico de Puntuaciones por Usuario
    const ratingChart = new Chart(document.getElementById('ratingChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($datosGraficoPuntuaciones['usuarios']) !!},
            datasets: [{
                label: 'Promedio de Estrellas',
                data: {!! json_encode($datosGraficoPuntuaciones['promedios']) !!},
                backgroundColor: 'rgba(246, 194, 62, 0.8)',
                borderColor: 'rgba(246, 194, 62, 1)',
                borderWidth: 1,
                borderRadius: 4,
                yAxisID: 'y'
            }]
        },
        options: {
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.x.toFixed(1)} ⭐ de 5 estrellas`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return '⭐'.repeat(value);
                        }
                    },
                    title: {
                        display: true,
                        text: 'Calificación (estrellas)',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Ranking de Pedidos por Usuario (Barras Horizontales)
    const usuariosData = {!! json_encode($rankingPedidosUsuario['usuarios']) !!};
    const pedidosData = {!! json_encode($rankingPedidosUsuario['pedidos']) !!};
    
    // Función para generar colores degradados basados en valores
    function generateGradientColors(values) {
        const maxValue = Math.max(...values);
        const colors = [];
        
        values.forEach(value => {
            // Calcular intensidad del color basado en el valor (0-1)
            const intensity = value / maxValue;
            
            // Crear degradado de verde claro a verde oscuro
            const hue = 120; // Verde
            const saturation = 70;
            const lightness = 85 - (intensity * 40); // De 85% (claro) a 45% (oscuro)
            
            colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
        });
        
        return colors;
    }
    
    // Generar colores degradados
    const gradientColors = generateGradientColors(pedidosData);

    const rankingPedidosChart = new Chart(document.getElementById('rankingPedidosChart'), {
        type: 'bar',
        data: {
            labels: usuariosData,
            datasets: [{
                label: 'Número de Pedidos',
                data: pedidosData,
                backgroundColor: gradientColors,
                borderColor: gradientColors.map(color => color.replace('hsl', 'hsla').replace(')', ', 1)')),
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            indexAxis: 'y', // Barras horizontales
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#5a5c69',
                    titleMarginBottom: 10,
                    bodyFont: {
                        size: 12
                    },
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    padding: 12,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `Pedidos: ${context.parsed.x}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: '#eaecf4',
                        drawBorder: false,
                        borderDash: [2]
                    },
                    ticks: {
                        color: '#858796',
                        font: {
                            family: 'Nunito',
                            size: 11
                        },
                        stepSize: 1,
                        callback: function(value) {
                            return Math.floor(value);
                        }
                    },
                    title: {
                        display: true,
                        text: 'Número de Pedidos',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        color: '#5a5c69'
                    }
                },
                y: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#858796',
                        font: {
                            family: 'Nunito',
                            size: 10,
                            weight: '500'
                        },
                        padding: 8,
                        callback: function(value, index, values) {
                            const label = this.getLabelForValue(value);
                            // Truncar nombres largos pero mantener legibilidad
                            if (typeof label === 'string' && label.length > 15) {
                                return label.substring(0, 12) + '...';
                            }
                            return label;
                        }
                    },
                    title: {
                        display: true,
                        text: `Usuarios (${usuariosData.length} total)`,
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        color: '#5a5c69'
                    }
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 10,
                    bottom: 10
                }
            }
        }
    });

    // Gráfico de Ranking de Ventas por Empresa
    const rankingEmpresasChart = new Chart(document.getElementById('rankingEmpresasChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($rankingVentasEmpresa['empresas']) !!},
            datasets: [{
                label: 'Ventas por Empresa',
                data: {!! json_encode($rankingVentasEmpresa['ventas']) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(0, 123, 255, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(231, 74, 59, 1)',
                    'rgba(28, 200, 138, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(0, 123, 255, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const dataset = data.datasets[0];
                                
                                // Convertir todos los valores a números y calcular el total
                                const numericData = dataset.data.map(value => {
                                    const num = parseFloat(value);
                                    return isNaN(num) || num === null ? 0 : num;
                                });
                                
                                const total = numericData.reduce((a, b) => a + b, 0);
                                
                                return data.labels.map((label, i) => {
                                    const value = numericData[i];
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                    
                                    // Truncar nombres largos para la leyenda
                                    let displayLabel = label;
                                    if (label && label.length > 15) {
                                        displayLabel = label.substring(0, 12) + '...';
                                    }
                                    
                                    return {
                                        text: `${displayLabel} (${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor[i],
                                        lineWidth: dataset.borderWidth,
                                        hidden: value === 0,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#5a5c69',
                    titleMarginBottom: 10,
                    bodyFont: {
                        size: 12
                    },
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    padding: 12,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = parseFloat(context.parsed);
                            const safeValue = isNaN(value) || value === null ? 0 : value;
                            
                            // Calcular el total convirtiendo todos los valores a números
                            const total = context.dataset.data.reduce((a, b) => {
                                const num = parseFloat(b);
                                return a + (isNaN(num) || num === null ? 0 : num);
                            }, 0);
                            
                            const percentage = total > 0 ? ((safeValue / total) * 100).toFixed(1) : '0.0';
                            const formattedValue = '$' + safeValue.toLocaleString();
                            return `${label}: ${formattedValue} (${percentage}%)`;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 20,
                    top: 10,
                    bottom: 10
                }
            }
        }
    });

    // Asegurarse que todos los paneles estén colapsados al cargar, excepto los rankings
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener todos los botones de colapso
        const collapseButtons = document.querySelectorAll('[data-toggle="collapse"]');
        
        // Agregar clase collapsed a todos los botones excepto los rankings
        collapseButtons.forEach(button => {
            const target = button.getAttribute('data-target');
            if (target !== '#rankingPedidosUsuario' && target !== '#rankingVentasEmpresa') {
                button.classList.add('collapsed');
                const icon = button.querySelector('.fa-chevron-down');
                if (icon) {
                    icon.style.transform = 'rotate(-90deg)';
                }
            }
        });

        // Remover clase show de todos los paneles colapsables excepto los rankings
        const collapsePanels = document.querySelectorAll('.collapse');
        collapsePanels.forEach(panel => {
            if (panel.id !== 'rankingPedidosUsuario' && panel.id !== 'rankingVentasEmpresa') {
                panel.classList.remove('show');
            }
        });
    });

    // Manejar la rotación del icono al expandir/colapsar
    $('.collapse').on('show.bs.collapse', function() {
        const icon = $(this).siblings('.card-header').find('.fa-chevron-down');
        icon.css('transform', 'rotate(0deg)');
    }).on('hide.bs.collapse', function() {
        const icon = $(this).siblings('.card-header').find('.fa-chevron-down');
        icon.css('transform', 'rotate(-90deg)');
    });
</script>
@endpush
@stop