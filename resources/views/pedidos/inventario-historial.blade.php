@extends('adminlte::page')

@section('title', 'HISTORIAL DE MOVIMIENTOS')

@section('content_header')
    <h1>HISTORIAL DE ARMAZONES Y PEDIDOS</h1>
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
    datalist,
    datalist option,
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
    .close,
    .table thead th,
    .table tbody td,
    .dataTables_filter,
    .dataTables_info,
    .paginate_button,
    .alert-info,
    .form-select,
    .badge {
        text-transform: uppercase !important;
    }

    /* Asegurar que el placeholder también esté en mayúsculas */
    input::placeholder,
    .dataTables_filter input::placeholder {
        text-transform: uppercase !important;
    }
</style>

<div class="container">
    <div class="card">
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>LUGAR</label>
                        <select class="form-control" id="filtroCategoria">
                            <option value="">TODOS LOS LUGARES</option>
                            @foreach($inventario->pluck('lugar')->unique() as $lugar)
                                <option value="{{ $lugar }}">{{ $lugar }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>MES</label>
                        <input type="month" class="form-control" id="filtroMes">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>BUSCAR</label>
                        <input type="text" class="form-control" id="filtroBusqueda" placeholder="CÓDIGO O DESCRIPCIÓN...">
                    </div>
                </div>
            </div>

            <!-- Contadores -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-glasses"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL ARTÍCULOS</span>
                            <span class="info-box-number" id="totalArticulos">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-box"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL EN STOCK</span>
                            <span class="info-box-number" id="totalStock">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL VENDIDOS</span>
                            <span class="info-box-number" id="totalVendidos">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">SIN STOCK</span>
                            <span class="info-box-number" id="sinStock">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Inventario -->
            <div class="table-responsive">
                <table id="tablaInventario" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>CATEGORÍA</th>
                            <th>STOCK ACTUAL</th>
                            <th>HISTORIAL DE VENTAS</th>
                            <th>ÓRDENES ASOCIADAS</th>
                            <th>ÚLTIMO MOVIMIENTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventario as $item)
                        <tr>
                            <td>{{ $item->codigo }}</td>
                            <td>{{ $item->lugar }}</td>
                            <td data-stock="{{ $item->cantidad }}">
                                <span class="badge {{ $item->cantidad > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->cantidad }}
                                </span>
                            </td>
                            <td>
                                @if($item->pedidos->count() > 0)
                                    VENDIDO ({{ $item->pedidos->count() }} {{ $item->pedidos->count() == 1 ? 'PEDIDO' : 'PEDIDOS' }})
                                @else
                                    SIN MOVIMIENTOS
                                @endif
                            </td>
                            <td>
                                @if($item->pedidos->count() > 0)
                                    @foreach($item->pedidos as $pedido)
                                        <div class="mb-1">
                                            <span class="badge bg-info">
                                                ORDEN #{{ $pedido->id }} - {{ $pedido->created_at->format('d/m/Y') }}<br>
                                                CLIENTE: {{ $pedido->cliente ?? 'N/A' }}<br>
                                                VENDEDOR: {{ $pedido->usuario ?? 'N/A' }}
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary">SIN ÓRDENES</span>
                                @endif
                            </td>
                            <td>{{ $item->updated_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .timeline-item {
            padding: 5px 0;
        }
        .timeline-content {
            margin-bottom: 5px;
            font-size: 0.9em;
        }
        .badge {
            font-size: 1em;
        }
        .toggle-movimientos {
            padding: 0;
            text-decoration: none !important;
        }
        .toggle-movimientos:hover {
            text-decoration: underline !important;
        }
        .movimientos-completos {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #dee2e6;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Establecer el mes actual por defecto en el filtro
    var today = new Date();
    var mesActual = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
    $('#filtroMes').val(mesActual);

    // Inicializar DataTable
    var tabla = $('#tablaInventario').DataTable({
        "language": {
            "url": "{{ asset('js/datatables/Spanish.json') }}"
        },
        "order": [[4, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "initComplete": function(settings, json) {
            // Aplicar el filtro del mes actual al cargar la página
            aplicarFiltroMes(mesActual);
        }
    });

    // Función para aplicar filtro de mes
    function aplicarFiltroMes(fecha) {
        if (fecha) {
            let [año, mes] = fecha.split('-');
            
            // Limpiar filtros de búsqueda anteriores
            $.fn.dataTable.ext.search = [];
            
            // Agregar nuevo filtro
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    let fechaMovimiento = data[4]; // Columna de último movimiento
                    if (!fechaMovimiento) return false;
                    
                    let [dia, mesMovimiento, añoMovimiento] = fechaMovimiento.split('/');
                    return (parseInt(mesMovimiento) === parseInt(mes) && 
                            parseInt(añoMovimiento) === parseInt(año));
                }
            );
        } else {
            // Si no hay fecha, limpiar los filtros
            $.fn.dataTable.ext.search = [];
        }
        tabla.draw();
        actualizarContadores();
    }

    // Función para actualizar contadores
    function actualizarContadores() {
        let totalArticulos = 0;
        let totalStock = 0;
        let totalVendidos = 0;
        let sinStock = 0;

        tabla.rows({search: 'applied'}).every(function() {
            let data = this.data();
            totalArticulos++;
            
            let stock = parseInt($(data[2]).text().trim());
            if (stock > 0) {
                totalStock++;
            } else {
                sinStock++;
            }

            if (data[3].includes('VENDIDO')) {
                totalVendidos++;
            }
        });

        $('#totalArticulos').text(totalArticulos);
        $('#totalStock').text(totalStock);
        $('#totalVendidos').text(totalVendidos);
        $('#sinStock').text(sinStock);
    }

    // Filtro por categoría
    $('#filtroCategoria').on('change', function() {
        let valor = $(this).val();
        tabla.column(1).search(valor).draw();
    });

    // Filtro por mes
    $('#filtroMes').on('change', function() {
        aplicarFiltroMes($(this).val());
    });

    // Búsqueda general
    $('#filtroBusqueda').on('keyup', function() {
        tabla.search(this.value).draw();
    });

    // Actualizar contadores después de cada filtro
    tabla.on('draw', actualizarContadores);

    // Inicializar contadores
    actualizarContadores();
});
</script>
@stop