@extends('adminlte::page')

@section('title', 'SUELDOS')

@section('content_header')
    <h1>SUELDOS</h1>
    <p>ADMINISTRACIÓN DE SUELDOS</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong>{{ session('mensaje') }}</strong>
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
            {{-- Resumen de totales --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL SUELDOS</span>
                            <span class="info-box-number">${{ number_format($totalSueldos, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario de filtro --}}
            <form method="GET" class="form-row mb-3" id="filterForm">
                <div class="col-md-2">
                    <label for="filtroAno">SELECCIONAR AÑO:</label>
                    <select name="ano" class="form-control custom-select" id="filtroAno">
                        <option value="">SELECCIONE AÑO</option>
                        @php
                            $currentYear = date('Y');
                            $selectedYear = request('ano', $currentYear);
                        @endphp
                        @for ($year = date('Y'); $year >= 2000; $year--)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroMes">SELECCIONAR MES:</label>
                    <select name="mes" class="form-control custom-select" id="filtroMes">
                        <option value="">SELECCIONE MES</option>
                        @php
                            $currentMonth = date('n');
                            $selectedMonth = request('mes', $currentMonth);
                        @endphp
                        @foreach (['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ $selectedMonth == ($index + 1) ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="button" class="btn btn-primary" id="actualButton">ACTUAL</button>
                </div>
            </form>

            {{-- Botón Añadir Sueldo --}}
            <div class="btn-group mb-3">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearSueldoModal">
                    <i class="fas fa-plus mr-2"></i>AÑADIR SUELDO
                </button>
            </div>

            <div class="table-responsive">
                <table id="sueldosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>DESCRIPCIÓN</th>
                            <th>VALOR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sueldos as $sueldo)
                            <tr>
                                <td>{{ $sueldo->fecha->format('Y-m-d') }}</td>
                                <td>{{ $sueldo->descripcion }}</td>
                                <td>${{ number_format($sueldo->valor, 2, ',', '.') }}</td>
                                <td>
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-info mx-1 shadow" 
                                        title="Ver"
                                        data-toggle="modal" 
                                        data-target="#verSueldoModal" 
                                        data-id="{{ $sueldo->id }}"
                                        data-fecha="{{ $sueldo->fecha->format('Y-m-d') }}"
                                        data-descripcion="{{ $sueldo->descripcion }}"
                                        data-valor="{{ $sueldo->valor }}">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </button>
                                    
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" 
                                        title="Editar"
                                        data-toggle="modal" 
                                        data-target="#editarSueldoModal" 
                                        data-id="{{ $sueldo->id }}"
                                        data-fecha="{{ $sueldo->fecha->format('Y-m-d') }}"
                                        data-descripcion="{{ $sueldo->descripcion }}"
                                        data-valor="{{ $sueldo->valor }}">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        title="Eliminar"
                                        data-toggle="modal"
                                        data-target="#confirmarEliminarModal"
                                        data-id="{{ $sueldo->id }}"
                                        data-url="{{ route('sueldos.destroy', $sueldo->id) }}">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear Sueldo -->
    <div class="modal fade" id="crearSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">REGISTRAR NUEVO SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sueldos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fecha">FECHA:</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                        </div>
                        <div class="form-group">
                            <label for="valor">VALOR:</label>
                            <input type="number" class="form-control" id="valor" name="valor" required step="0.01" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-success">GUARDAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Sueldo -->
    <div class="modal fade" id="verSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">DETALLES DEL SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>FECHA:</label>
                        <p id="verFecha" class="form-control-static"></p>
                    </div>
                    <div class="form-group">
                        <label>DESCRIPCIÓN:</label>
                        <p id="verDescripcion" class="form-control-static"></p>
                    </div>
                    <div class="form-group">
                        <label>VALOR:</label>
                        <p id="verValor" class="form-control-static"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Sueldo -->
    <div class="modal fade" id="editarSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">EDITAR SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarSueldo" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editFecha">FECHA:</label>
                            <input type="date" class="form-control" id="editFecha" name="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="editDescripcion">DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="editDescripcion" name="descripcion" required>
                        </div>
                        <div class="form-group">
                            <label for="editValor">VALOR:</label>
                            <input type="number" class="form-control" id="editValor" name="valor" required step="0.01" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                    </div>
                </form>
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
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE REGISTRO DE SUELDO?</p>
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
    <script>
        $(document).ready(function() {
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
                            "columns": [0, 1, 2]
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
                            "columns": [0, 1, 2]
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            // Modal Ver Sueldo
            $('#verSueldoModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var fecha = button.data('fecha');
                var descripcion = button.data('descripcion');
                var valor = button.data('valor');
                
                var modal = $(this);
                modal.find('#verFecha').text(fecha);
                modal.find('#verDescripcion').text(descripcion);
                modal.find('#verValor').text('$' + parseFloat(valor).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            });

            // Modal Editar Sueldo
            $('#editarSueldoModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var fecha = button.data('fecha');
                var descripcion = button.data('descripcion');
                var valor = button.data('valor');
                
                var modal = $(this);
                modal.find('#formEditarSueldo').attr('action', '/sueldos/' + id);
                modal.find('#editFecha').val(fecha);
                modal.find('#editDescripcion').val(descripcion);
                modal.find('#editValor').val(valor);
            });

            // Modal Eliminar Sueldo
            $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var modal = $(this);
                modal.find('#eliminarForm').attr('action', url);
            });

            // Manejar cambios en los filtros
            $('#filtroAno, #filtroMes').change(function() {
                $('#filterForm').submit();
            });

            // Botón "Actual"
            $('#actualButton').click(function() {
                const now = new Date();
                $('#filtroAno').val(now.getFullYear());
                $('#filtroMes').val(now.getMonth() + 1);
                $('#filterForm').submit();
            });
        });
    </script>
@stop 