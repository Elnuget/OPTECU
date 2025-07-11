@extends('adminlte::page')

@section('title', 'PAGOS')

@section('content_header')
    <h1>PAGOS</h1>
    <p>ADMINISTRACIÓN DE PAGOS</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong> {{ session('mensaje') }}</strong>
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

        /* Asegurar que el placeholder también esté en mayúsculas */
        input::placeholder,
        .dataTables_filter input::placeholder {
            text-transform: uppercase !important;
        }
    </style>

    <div class="card">
        <div class="card-body">
            {{-- Agregar resumen de totales --}}
            @can('admin')
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL PAGOS</span>
                            <span class="info-box-number">${{ number_format($totalPagos, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @foreach($mediosdepago as $medio)
                    @php
                        $totalPorMedio = $pagos->filter(function($pago) use ($medio) {
                            return $pago->mediodepago->id === $medio->id;
                        })->sum('pago');
                    @endphp
                    <div class="col-md-4">
                        <div class="info-box {{ $totalPorMedio > 0 ? 'bg-info' : 'bg-secondary' }}">
                            <div class="info-box-content">
                                <span class="info-box-text">TOTAL {{ strtoupper($medio->medio_de_pago) }}</span>
                                <span class="info-box-number">${{ number_format($totalPorMedio, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endcan

            {{-- Agregar formulario de filtro --}}
            <form method="GET" class="form-row mb-3" id="filterForm">
                <div class="col-md-2">
                    <label for="filtroAno">SELECCIONAR AÑO:</label>
                    <select name="ano" class="form-control custom-select" id="filtroAno">
                        <option value="">SELECCIONE AÑO</option>
                        @for ($year = date('Y'); $year >= 2000; $year--)
                            <option value="{{ $year }}" {{ request('ano') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroMes">SELECCIONAR MES:</label>
                    <select name="mes" class="form-control custom-select" id="filtroMes">
                        <option value="">SELECCIONE MES</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('mes') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ strtoupper(date('F', mktime(0, 0, 0, $i, 1))) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="metodo_pago">MÉTODO DE PAGO:</label>
                    <select name="metodo_pago" class="form-control custom-select" id="metodo_pago">
                        <option value="">TODOS LOS MÉTODOS</option>
                        @foreach($mediosdepago as $medio)
                            <option value="{{ $medio->id }}" {{ request('metodo_pago') == $medio->id ? 'selected' : '' }}>
                                {{ strtoupper($medio->medio_de_pago) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="empresa">SUCURSAL:</label>
                    @if(isset($isAdmin) && $isAdmin)
                        {{-- Si es admin, puede seleccionar cualquier empresa --}}
                        <select name="empresa" class="form-control custom-select" id="filtroEmpresa">
                            <option value="">TODAS LAS SUCURSALES</option>
                            @foreach($empresas ?? [] as $empresa)
                                <option value="{{ $empresa->id }}" {{ request('empresa') == $empresa->id ? 'selected' : '' }}>
                                    {{ strtoupper($empresa->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    @elseif(isset($userEmpresaId) && $userEmpresaId)
                        {{-- Si no es admin y tiene empresa asociada, mostrar su empresa como texto y campo oculto --}}
                        @php
                            $empresaNombre = '';
                            foreach($empresas ?? [] as $empresa) {
                                if($empresa->id == $userEmpresaId) {
                                    $empresaNombre = $empresa->nombre;
                                    break;
                                }
                            }
                        @endphp
                        <input type="text" class="form-control" value="{{ strtoupper($empresaNombre) }}" readonly>
                        <input type="hidden" name="empresa" value="{{ $userEmpresaId }}">
                    @else
                        {{-- Si no es admin y no tiene empresa, mostrar selector pero deshabilitado --}}
                        <select name="empresa" class="form-control custom-select" id="filtroEmpresa" disabled>
                            <option value="">TODAS LAS SUCURSALES</option>
                            @foreach($empresas ?? [] as $empresa)
                                <option value="{{ $empresa->id }}" {{ request('empresa') == $empresa->id ? 'selected' : '' }}>
                                    {{ strtoupper($empresa->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="button" class="btn btn-primary mr-2" id="actualButton">ACTUAL</button>
                    <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
                </div>
            </form>

            {{-- Botones de Filtro TC y Añadir Pago --}}
            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group" role="group" aria-label="Grupo Añadir">
                    <a type="button" class="btn btn-success" href="{{ route('pagos.create') }}">AÑADIR PAGO</a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="pagosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <!-- Removed Paciente filter -->
                        </tr>
                        <tr>
                            <td>ID</td>
                            <td>FECHA DE PAGO</td> <!-- Nueva columna -->
                            <td>ORDEN ASOCIADA</td> <!-- Nueva columna -->
                            <td>CLIENTE ASOCIADO</td> <!-- Nueva columna -->
                            <td>EMPRESA</td> <!-- Nueva columna para Empresa -->
                            <!-- Removed Paciente column -->
                            <td>MÉTODO DE PAGO</td>
                            <td>SALDO</td>
                            <td>PAGO</td>
                            <td style="display: none;">TC</td>
                            <td>ACCIONES</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagos as $index => $pago)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $pago->created_at->format('Y-m-d') }}</td> <!-- Fecha de Pago -->
                                <td>{{ $pago->pedido->numero_orden }}</td> <!-- Orden Asociada -->
                                <td>{{ $pago->pedido->cliente }}</td> <!-- Cliente Asociado -->
                                <td>{{ $pago->pedido->empresa ? $pago->pedido->empresa->nombre : 'N/A' }}</td> <!-- Empresa Asociada -->
                                <!-- Removed Paciente data -->
                                <td>{{ $pago->mediodepago->medio_de_pago }}</td>
                                <td>${{ number_format($pago->pedido->saldo, 0, ',', '.') }}</td> <!-- Updated to access saldo from pedido -->
                                <td>${{ number_format($pago->pago, 0, ',', '.') }}</td>
                                <td style="display: none;">{{ $pago->TC ? 'SÍ' : 'NO' }}</td>
                                <td>
                                    <a href="{{ route('pagos.show', $pago->id) }}"
                                        class="btn btn-xs btn-default text-info mx-1 shadow" title="Ver">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </a>
                                    @can('admin')
                                    <a href="{{ route('pagos.edit', $pago->id) }}"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </a>

                                    <a class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        href="#"
                                        data-toggle="modal"
                                        data-target="#confirmarEliminarModal"
                                        data-id="{{ $pago->id }}"
                                        data-url="{{ route('pagos.destroy', $pago->id) }}">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </a>
                                    @endcan

                                    @if(!$pago->TC && $pago->mediodepago->medio_de_pago === 'Tarjeta Crédito')
                                    <button class="btn btn-xs btn-warning mx-1 shadow tc-button" 
                                        data-id="{{ $pago->id }}"
                                        data-status="pending"
                                        onclick="updateTC({{ $pago->id }}, this)">
                                        PENDIENTE
                                    </button>
                                    @elseif($pago->TC && $pago->mediodepago->medio_de_pago === 'Tarjeta Crédito')
                                    <button class="btn btn-xs btn-success mx-1 shadow" disabled>
                                        RECIBIDO
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <br>

            </div>
        </div>
    </div>

    <!-- Confirmar Eliminar Modal -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿ESTÁS SEGURO DE QUE DESEAS ELIMINAR ESTE ELEMENTO?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                    <form id="eliminarForm" method="post" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">ELIMINAR</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
@include('atajos')

    <script>
        function updateTC(id, button) {
            if (!confirm('¿Está seguro de marcar este pago como recibido?')) {
                return;
            }

            $.ajax({
                url: `/pagos/${id}/update-tc`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Cambiar el botón a "RECIBIDO"
                        $(button).replaceWith(`
                            <button class="btn btn-xs btn-success mx-1 shadow" disabled>
                                RECIBIDO
                            </button>
                        `);
                        
                        // Actualizar la celda oculta de TC
                        $(button).closest('tr').find('td:nth-child(8)').text('SÍ');
                    } else {
                        alert('Error al actualizar el estado');
                    }
                },
                error: function() {
                    alert('Error al procesar la solicitud');
                }
            });
        }

        $(document).ready(function() {
            // Manejar clic en el botón MOSTRAR TODOS
            $('#mostrarTodosButton').click(function() {
                $('#filtroAno').val('');
                $('#filtroMes').val('');
                $('#metodo_pago').val('');
                
                // Solo permitir cambiar la empresa si es admin
                @if(isset($isAdmin) && $isAdmin)
                    $('#filtroEmpresa').val('');
                @endif
                
                const form = $('#filterForm');
                form.append('<input type="hidden" name="todos" value="1">');
                form.submit();
            });

            // Manejar clic en el botón ACTUAL
            $('#actualButton').click(function() {
                const currentDate = new Date();
                $('#filtroAno').val(currentDate.getFullYear());
                $('#filtroMes').val(String(currentDate.getMonth() + 1).padStart(2, '0'));
                $('#metodo_pago').val('');
                
                // Solo permitir cambiar la empresa si es admin
                @if(isset($isAdmin) && $isAdmin)
                    $('#filtroEmpresa').val('');
                @endif
                
                $('#filterForm').submit();
            });

            // Manejar cambios en los filtros
            $('#filtroAno, #filtroMes, #metodo_pago, #filtroEmpresa').change(function() {
                $('#filterForm').submit();
            });

            // Configurar el modal antes de mostrarse
            $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Botón que activó el modal
                var url = button.data('url'); // Extraer la URL del atributo data-url
                var modal = $(this);
                modal.find('#eliminarForm').attr('action', url); // Actualizar la acción del formulario
            });

            // Inicializar DataTable
            var pagosTable = $('#pagosTable').DataTable({
                "order": [[0, "asc"]],
                "paging": false,     // Disable pagination
                "info": false,       // Remove "Showing X of Y entries" text
                "searching": false,  // Remove search box
                "columnDefs": [{
                    "targets": [2],
                    "visible": true,
                    "searchable": true,
                },
                {
                    "targets": [7], // Índice de la columna TC
                    "visible": false,
                    "searchable": false
                }],
                "dom": 'Bfrt',      // Modified to remove pagination and info elements
                "buttons": [
                    'excelHtml5',
                    'csvHtml5',
                    {
                        "extend": 'print',
                        "text": 'IMPRIMIR',
                        "autoPrint": true,
                        "exportOptions": {
                            "columns": [0, 1, 2, 3, 4, 5, 6, 7]
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
                        "filename": 'Pagos.pdf',
                        "pageSize": 'LETTER',
                        "exportOptions": {
                            "columns": [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop
