@extends('adminlte::page')

@section('title', 'HISTORIALES CLÍNICOS')

@section('content_header')
<h1>HISTORIALES CLÍNICOS</h1>
<p>ADMINISTRACIÓN DE HISTORIALES CLÍNICOS</p>
@if (session('error'))
<div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
    <strong>{{ strtoupper(session('mensaje')) }}</strong>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@stop

@section('content')

<div class="card">
    <div class="card-body">
        {{-- Filtros de Mes y Año --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <form action="{{ route('historiales_clinicos.index') }}" method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="mes" class="mr-2">MES:</label>
                        <select name="mes" id="mes" class="form-control">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('mes', date('m')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ strtoupper(date('F', mktime(0, 0, 0, $i, 1))) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="ano" class="mr-2">AÑO:</label>
                        <select name="ano" id="ano" class="form-control">
                            @for ($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                                <option value="{{ $i }}" {{ request('ano', date('Y')) == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="empresa_id" class="mr-2">SUCURSAL:</label>
                        <select name="empresa_id" id="empresa_id" class="form-control" {{ !$isUserAdmin && $userEmpresaId ? 'disabled' : '' }}>
                            <option value="">TODAS LAS SUCURSALES</option>
                            @foreach($empresas ?? [] as $empresa)
                                <option value="{{ $empresa->id }}" {{ ($userEmpresaId == $empresa->id) || request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                    {{ strtoupper($empresa->nombre) }}
                                </option>
                            @endforeach
                        </select>
                        @if(!$isUserAdmin && $userEmpresaId)
                            <input type="hidden" name="empresa_id" value="{{ $userEmpresaId }}">
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">FILTRAR</button>
                    <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
                </form>
            </div>
        </div>

        {{-- Botón Añadir Historial Clínico --}}
        <div class="btn-group mb-3">
            <a type="button" class="btn btn-success" href="{{ route('historiales_clinicos.create') }}">
                AÑADIR HISTORIAL CLÍNICO
            </a>
        </div>

        <div class="table-responsive">
            <table id="historialesTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NOMBRES</th>
                        <th>APELLIDOS</th>
                        <th>FECHA</th>
                        <th>MOTIVO CONSULTA</th>
                        <th>PRÓXIMA CONSULTA</th>
                        <th>EMPRESA</th>
                        <th>USUARIO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historiales as $index => $historial)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ strtoupper($historial->nombres) }}</td>
                        <td>{{ strtoupper($historial->apellidos) }}</td>
                        <td>{{ \Carbon\Carbon::parse($historial->fecha)->format('d/m/Y') }}</td>
                        <td>{{ strtoupper($historial->motivo_consulta) }}</td>
                        <td>
                            @if($historial->proxima_consulta)
                                <span class="badge {{ strtotime($historial->proxima_consulta) < time() ? 'badge-danger' : 'badge-success' }}">
                                    {{ \Carbon\Carbon::parse($historial->proxima_consulta)->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="badge badge-secondary">NO PROGRAMADA</span>
                            @endif
                        </td>
                        <td>{{ $historial->empresa ? strtoupper($historial->empresa->nombre) : 'SIN EMPRESA' }}</td>
                        <td>{{ strtoupper($historial->usuario->name ?? 'N/A') }}</td>
                        <td>
                            <a href="{{ route('historiales_clinicos.show', $historial->id) }}"
                               class="btn btn-xs btn-default text-primary mx-1 shadow"
                               title="VER">
                                <i class="fa fa-lg fa-fw fa-eye"></i>
                            </a>
                            <a href="{{ route('historiales_clinicos.edit', $historial->id) }}"
                                class="btn btn-xs btn-default text-warning mx-1 shadow" 
                                title="EDITAR">
                                <i class="fa fa-lg fa-fw fa-pen"></i>
                            </a>
                            <button type="button" 
                                class="btn btn-xs btn-info mx-1 shadow ver-historiales-relacionados" 
                                data-nombres="{{ $historial->nombres }}" 
                                data-apellidos="{{ $historial->apellidos }}"
                                data-toggle="modal" 
                                data-target="#historialesRelacionadosModal"
                                title="VER HISTORIALES">
                                <i class="fa fa-lg fa-fw fa-history"></i>
                            </button>
                            <a class="btn btn-xs btn-default text-danger mx-1 shadow" 
                               href="#" 
                               data-toggle="modal"
                               data-target="#confirmarEliminarModal" 
                               data-id="{{ $historial->id }}"
                               data-url="{{ route('historiales_clinicos.destroy', $historial->id) }}"
                               title="ELIMINAR">
                                <i class="fa fa-lg fa-fw fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal de Confirmación de Eliminación --}}
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarModalLabel">CONFIRMAR ELIMINACIÓN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿ESTÁ SEGURO DE QUE DESEA ELIMINAR ESTE HISTORIAL CLÍNICO?
            </div>
            <div class="modal-footer">
                <form id="eliminarForm" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-danger">ELIMINAR</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Historiales Relacionados --}}
<div class="modal fade" id="historialesRelacionadosModal" tabindex="-1" role="dialog" aria-labelledby="historialesRelacionadosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historialesRelacionadosModalLabel">HISTORIALES CLÍNICOS DEL PACIENTE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="historialesLoader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">CARGANDO...</span>
                    </div>
                </div>
                <div id="historialesRelacionadosContent">
                    <h4 id="pacienteNombre" class="mb-3"></h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered dt-responsive nowrap" id="tablaHistorialesRelacionados" width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>FECHA</th>
                                    <th>MOTIVO CONSULTA</th>
                                    <th>PRÓXIMA CONSULTA</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody id="historialesRelacionadosBody">
                                <!-- Los historiales relacionados se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Convertir todo el texto a mayúsculas */
    .card-title,
    .card-header,
    .table th,
    .table td,
    .alert,
    h1, h2, h3, h4, h5,
    p,
    .btn,
    .modal-title,
    .modal-body,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_paginate,
    .buttons-html5,
    .buttons-print {
        text-transform: uppercase !important;
    }

    /* Estilos para los badges de próxima consulta */
    .badge {
        font-size: 0.9em;
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: 600;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#historialesTable').DataTable({
            "order": [[0, "desc"]],
            "columnDefs": [{
                "targets": [2],
                "visible": true,
                "searchable": true,
            }],
            "dom": 'Bfrtip',
            "paging": false,
            "lengthChange": false,
            "info": false,
            "processing": false,
            "serverSide": false,
            "buttons": [
                'excelHtml5',
                'csvHtml5',
                {
                    "extend": 'print',
                    "text": 'IMPRIMIR',
                    "autoPrint": true,
                    "exportOptions": {
                        "columns": [0, 1, 2, 3]
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
                    "filename": 'HISTORIALES_CLINICOS.pdf',
                    "pageSize": 'LETTER',
                    "exportOptions": {
                        "columns": [0, 1, 2, 3]
                    }
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            }
        });

        // Convertir a mayúsculas los textos del DataTable
        $('.dataTables_wrapper').find('label, .dataTables_info').css('text-transform', 'uppercase');

        // Modal de eliminación
        $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var url = button.data('url');
            var modal = $(this);
            modal.find('#eliminarForm').attr('action', url);
        });

        // Botón Mostrar Todos
        $('#mostrarTodosButton').click(function() {
            $('#mes').val('');
            $('#ano').val('');
            
            // Solo limpiamos el filtro de empresa si el usuario es administrador o no tiene empresa asignada
            @if($isUserAdmin || !$userEmpresaId)
                $('#empresa_id').val('');
            @endif
            
            const form = $(this).closest('form');
            form.append('<input type="hidden" name="todos" value="1">');
            form.submit();
        });

        // Manejo del botón de ver historiales relacionados
        $('.ver-historiales-relacionados').click(function() {
            const nombres = $(this).data('nombres');
            const apellidos = $(this).data('apellidos');
            
            $('#pacienteNombre').text(nombres.toUpperCase() + ' ' + apellidos.toUpperCase());
            $('#historialesLoader').show();
            $('#historialesRelacionadosContent').hide();
            
            // Hacer la solicitud AJAX para obtener los historiales relacionados
            $.ajax({
                url: '{{ route("historiales_clinicos.relacionados") }}',
                method: 'GET',
                data: {
                    nombres: nombres,
                    apellidos: apellidos
                },
                success: function(response) {
                    // Llenar la tabla con los datos recibidos
                    const historialesBody = $('#historialesRelacionadosBody');
                    historialesBody.empty();
                    
                    if (response.historiales && response.historiales.length > 0) {
                        // Ordenar por ID de forma descendente
                        const historialesOrdenados = response.historiales.sort((a, b) => b.id - a.id);
                        
                        historialesOrdenados.forEach(function(historial) {
                            const fechaConsulta = new Date(historial.fecha);
                            const fechaFormateada = ('0' + fechaConsulta.getDate()).slice(-2) + '/' +
                                                  ('0' + (fechaConsulta.getMonth() + 1)).slice(-2) + '/' +
                                                  fechaConsulta.getFullYear();
                            
                            let proximaConsultaHtml = '<span class="badge badge-secondary">NO PROGRAMADA</span>';
                            if (historial.proxima_consulta) {
                                const proximaFecha = new Date(historial.proxima_consulta);
                                const esPasada = proximaFecha < new Date();
                                const badgeClass = esPasada ? 'badge-danger' : 'badge-success';
                                const proximaFormateada = ('0' + proximaFecha.getDate()).slice(-2) + '/' +
                                                         ('0' + (proximaFecha.getMonth() + 1)).slice(-2) + '/' +
                                                         proximaFecha.getFullYear();
                                proximaConsultaHtml = `<span class="badge ${badgeClass}">${proximaFormateada}</span>`;
                            }
                            
                            const row = `
                                <tr>
                                    <td>${historial.id}</td>
                                    <td>${fechaFormateada}</td>
                                    <td>${historial.motivo_consulta.toUpperCase()}</td>
                                    <td>${proximaConsultaHtml}</td>
                                    <td>
                                        <a href="/historiales_clinicos/${historial.id}" class="btn btn-xs btn-default text-primary mx-1 shadow" title="VER">
                                            <i class="fa fa-lg fa-fw fa-eye"></i>
                                        </a>
                                        <a href="/historiales_clinicos/${historial.id}/edit" class="btn btn-xs btn-default text-warning mx-1 shadow" title="EDITAR">
                                            <i class="fa fa-lg fa-fw fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                            historialesBody.append(row);
                        });
                        
                        // Inicializar DataTable en la tabla de historiales relacionados
                        if ($.fn.DataTable.isDataTable('#tablaHistorialesRelacionados')) {
                            $('#tablaHistorialesRelacionados').DataTable().destroy();
                        }
                        
                        $('#tablaHistorialesRelacionados').DataTable({
                            "responsive": true,
                            "order": [[0, "desc"]], // Ordenar por ID descendente
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                            },
                            "paging": false,
                            "searching": false,
                            "info": false
                        });
                        
                    } else {
                        historialesBody.append('<tr><td colspan="5" class="text-center">NO SE ENCONTRARON HISTORIALES ADICIONALES</td></tr>');
                    }
                    
                    $('#historialesLoader').hide();
                    $('#historialesRelacionadosContent').show();
                },
                error: function() {
                    $('#historialesRelacionadosBody').html('<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS HISTORIALES</td></tr>');
                    $('#historialesLoader').hide();
                    $('#historialesRelacionadosContent').show();
                }
            });
        });
    });
</script>
@stop
