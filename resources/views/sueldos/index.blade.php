@extends('adminlte::page')

@section('title', 'SUELDOS')

@section('content_header')
    <h1>SUELDOS</h1>
    
    <!-- Tarjeta de Rol de Pago con Filtros -->
    <div class="card card-info mb-3">
        <div class="card-header">
            <h3 class="card-title">ROL DE PAGO</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="rolDePagoForm" method="GET" action="{{ route('sueldos.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="anio">AÑO</label>
                            <select class="form-control" id="anio" name="anio">
                                @for ($i = date('Y'); $i >= date('Y')-5; $i--)
                                    <option value="{{ $i }}" {{ request('anio') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="mes">MES</label>
                            <select class="form-control" id="mes" name="mes">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('mes') == $i ? 'selected' : '' }}>{{ strtoupper(date('F', mktime(0, 0, 0, $i, 1))) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="usuario">USUARIO</label>
                            <select class="form-control select2" id="usuario" name="usuario">
                                <option value="">TODOS LOS USUARIOS</option>
                                @foreach($usuariosConPedidos ?? [] as $nombreUsuario)
                                    <option value="{{ $nombreUsuario }}" {{ request('usuario') == $nombreUsuario ? 'selected' : '' }}>{{ $nombreUsuario }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group" style="padding-top: 32px;">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> BUSCAR
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            @if(isset($pedidos) && count($pedidos) > 0)
                <!-- Resumen de Estadísticas -->
                <div class="row mt-3">
                    <!-- Total de Ventas -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>${{ number_format($pedidos->sum('total'), 2, ',', '.') }}</h3>
                                <p>TOTAL DE VENTAS</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total de Saldo -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>${{ number_format($pedidos->sum('saldo'), 2, ',', '.') }}</h3>
                                <p>SALDO PENDIENTE</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cantidad de Pedidos -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $pedidos->count() }}</h3>
                                <p>PEDIDOS REALIZADOS</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Promedio por Pedido -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>${{ number_format($pedidos->count() > 0 ? $pedidos->sum('total') / $pedidos->count() : 0, 2, ',', '.') }}</h3>
                                <p>PROMEDIO POR PEDIDO</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pedidos por Sucursal -->
                <div class="card mt-3">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">PEDIDOS POR SUCURSAL</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>SUCURSAL</th>
                                    <th>PEDIDOS</th>
                                    <th>TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pedidosPorEmpresa = $pedidos->groupBy('empresa_id');
                                @endphp
                                
                                @foreach($pedidosPorEmpresa as $empresaId => $pedidosEmpresa)
                                    @php
                                        $nombreEmpresa = 'SIN SUCURSAL';
                                        if ($empresaId && $pedidosEmpresa->first()->empresa) {
                                            $nombreEmpresa = $pedidosEmpresa->first()->empresa->nombre;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $nombreEmpresa }}</td>
                                        <td>{{ $pedidosEmpresa->count() }}</td>
                                        <td>${{ number_format($pedidosEmpresa->sum('total'), 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>FECHA</th>
                                <th>ORDEN</th>
                                <th>CLIENTE</th>
                                <th>SUCURSAL</th>
                                <th>USUARIO</th>
                                <th>TOTAL</th>
                                <th>SALDO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                                <tr>
                                    <td>{{ $pedido->fecha->format('Y-m-d') }}</td>
                                    <td>{{ $pedido->numero_orden }}</td>
                                    <td>{{ $pedido->cliente }}</td>
                                    <td>{{ $pedido->empresa ? $pedido->empresa->nombre : 'SIN SUCURSAL' }}</td>
                                    <td>{{ $pedido->usuario ?: 'N/A' }}</td>
                                    <td>${{ number_format($pedido->total, 2, ',', '.') }}</td>
                                    <td>${{ number_format($pedido->saldo, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-secondary">
                                <th colspan="5">TOTAL</th>
                                <th>${{ isset($pedidos) ? number_format($pedidos->sum('total'), 2, ',', '.') : '0,00' }}</th>
                                <th>${{ isset($pedidos) ? number_format($pedidos->sum('saldo'), 2, ',', '.') : '0,00' }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info mt-3">
                    NO SE ENCONTRARON PEDIDOS PARA LOS FILTROS SELECCIONADOS
                </div>
            @endif
        </div>
    </div>
    
    <p>ADMINISTRACIÓN DE SUELDOS</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong>{{ session('mensaje') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ session('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
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
        .info-box span {
            text-transform: uppercase !important;
        }
    </style>

    <div class="card">
        <div class="card-body">

            {{-- Botón Añadir Sueldo --}}
            <div class="btn-group mb-3">
                <a type="button" class="btn btn-success" href="{{ route('sueldos.create') }}">REGISTRAR SUELDO</a>
            </div>

            <div class="table-responsive">
                <table id="sueldosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>EMPLEADO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>VALOR</th>
                            <th>SUCURSAL</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sueldos as $sueldo)
                            <tr>
                                <td>{{ $sueldo->fecha->format('Y-m-d') }}</td>
                                <td>{{ $sueldo->user->name }}</td>
                                <td>{{ $sueldo->descripcion }}</td>
                                <td>${{ number_format($sueldo->valor, 2, ',', '.') }}</td>
                                <td>{{ $sueldo->empresa ? $sueldo->empresa->nombre : 'SIN SUCURSAL' }}</td>
                                <td>
                                    <a href="{{ route('sueldos.show', $sueldo->id) }}"
                                        class="btn btn-xs btn-default text-info mx-1 shadow" title="Ver">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </a>
                                    @can('admin')
                                    <a href="{{ route('sueldos.edit', $sueldo->id) }}"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </a>

                                    <a class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        href="#"
                                        data-toggle="modal"
                                        data-target="#confirmarEliminarModal"
                                        data-id="{{ $sueldo->id }}"
                                        data-url="{{ route('sueldos.destroy', $sueldo->id) }}">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminar -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE SUELDO?</p>
                </div>
                <div class="modal-footer">
                    <form id="eliminarForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-danger">ELIMINAR</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log('JavaScript cargado correctamente');
            
            // Inicializar Select2 para el selector de usuarios
            $('#usuario').select2({
                theme: 'bootstrap4',
                placeholder: "SELECCIONAR USUARIO",
                allowClear: true
            });
            
            // Configurar el modal antes de mostrarse
            $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var modal = $(this);
                modal.find('#eliminarForm').attr('action', url);
            });

            // Inicializar DataTable
            var sueldosTable = $('#sueldosTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,
                "info": false,
                "dom": 'Bfrt',
                "buttons": [
                    'excelHtml5',
                    'csvHtml5',
                    {
                        "extend": 'print',
                        "text": 'IMPRIMIR',
                        "autoPrint": true,
                        "exportOptions": {
                            "columns": [0, 1, 2, 3, 4]
                        },
                        "customize": function(win) {
                            $(win.document.body).css('font-size', '16pt');
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    },
                    {
                        "extend": 'pdfHtml5',
                        "text": 'PDF',
                        "filename": 'Sueldos.pdf',
                        "pageSize": 'LETTER',
                        "exportOptions": {
                            "columns": [0, 1, 2, 3, 4]
                        }
                    }
                ],
                "language": {
                    "url": "{{ asset('js/datatables/Spanish.json') }}"
                }
            });

            // Ninguna funcionalidad de filtro es necesaria ya que hemos eliminado los filtros
        });
    </script>
@stop
