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
                            <span class="info-box-number">${{ number_format($totalPagos, 2, ',', '.') }}</span>
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
                                <span class="info-box-number">${{ number_format($totalPorMedio, 2, ',', '.') }}</span>
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
                        @foreach (['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ request('mes') == ($index + 1) ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
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
                <div class="col-md-2 align-self-end">
                    <button type="button" class="btn btn-primary" id="actualButton">ACTUAL</button>
                </div>
            </form>

            {{-- Botones de Filtro TC y Añadir Pago --}}
            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group mr-2" role="group" aria-label="Grupo Filtro TC">
                    <a href="{{ route('pagos.index', ['tc_status' => 'pendientes']) }}" 
                       class="btn {{ request('tc_status') === 'pendientes' ? 'btn-warning active' : 'btn-outline-warning' }}">
                       PAGOS PENDIENTES TC
                    </a>
                    <a href="{{ route('pagos.index', ['tc_status' => 'pagados']) }}" 
                       class="btn {{ request('tc_status') === 'pagados' ? 'btn-success active' : 'btn-outline-success' }}">
                       PAGOS RECIBIDOS TC
                    </a>
                    <a href="{{ route('pagos.index', request()->except('tc_status')) }}" 
                       class="btn {{ !request('tc_status') ? 'btn-secondary active' : 'btn-outline-secondary' }}">
                       TODOS
                    </a>
                </div>
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
                                <!-- Removed Paciente data -->
                                <td>{{ $pago->mediodepago->medio_de_pago }}</td>
                                <td>{{ $pago->pedido->saldo }}</td> <!-- Updated to access saldo from pedido -->
                                <td>{{ $pago->pago }}</td>
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

            // Configurar el modal antes de mostrarse
            $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Botón que activó el modal
                var url = button.data('url'); // Extraer la URL del atributo data-url
                var modal = $(this);
                modal.find('#eliminarForm').attr('action', url); // Actualizar la acción del formulario
            });

            // Inicializar DataTable
            var pagosTable = $('#pagosTable').DataTable({
                "order": [[0, "desc"]],
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
                            "columns": [0, 1, 2, 3, 4, 5, 6]
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
                            "columns": [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            document.getElementById('filtroAno').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            document.getElementById('filtroMes').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            // Añadir evento al botón "Actual"
            document.getElementById('actualButton').addEventListener('click', function() {
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1; // getMonth() es 0-indexado

                document.getElementById('filtroAno').value = currentYear;
                document.getElementById('filtroMes').value = currentMonth;

                document.getElementById('filterForm').submit();
            });

            // Add event listener for payment method filter
            document.getElementById('metodo_pago').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            // Removed Paciente filter
            // $('#filtroPaciente').select2();
            // $('#filtroPaciente').on('change', function() {
            //     var pacienteId = $(this).val();
            //     pagosTable.column(1).search(pacienteId).draw();
            // });
        });
    </script>
@stop
