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
