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
                        <select name="empresa_id" id="empresa_id" class="form-control">
                            @if($isUserAdmin)
                                <option value="">TODAS LAS SUCURSALES</option>
                            @else
                                <option value="">MIS SUCURSALES</option>
                            @endif
                            @foreach($empresas ?? [] as $empresa)
                                <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                    {{ strtoupper($empresa->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">FILTRAR</button>
                    <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
                </form>
            </div>
        </div>

        {{-- Filtro por fecha específica --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="form-inline">
                    <div class="form-group mr-2">
                        <label for="fechaSeleccion" class="mr-2">FILTRAR POR FECHA ESPECÍFICA:</label>
                        <input type="date" class="form-control" id="fechaSeleccion" value="{{ request('fecha_especifica', date('Y-m-d')) }}">
                    </div>
                    <div class="btn-group">
                        @if(request()->filled('fecha_especifica'))
                            <button type="button" class="btn btn-danger" id="filtrarPorFecha">
                                <i class="fas fa-filter"></i> 
                                @if(request()->filled('empresa_id'))
                                    FILTROS ACTIVOS ({{ $historiales->count() }})
                                @else
                                    FILTRO FECHA ({{ $historiales->count() }})
                                @endif
                            </button>
                            <button type="button" class="btn btn-secondary" id="limpiarFiltroFecha">
                                <i class="fas fa-times"></i> LIMPIAR FILTRO FECHA
                            </button>
                        @else
                            <button type="button" class="btn btn-warning" id="filtrarPorFecha">
                                <i class="fas fa-calendar-day"></i> FILTRAR POR FECHA
                            </button>
                            <button type="button" class="btn btn-secondary" id="limpiarFiltroFecha" style="display: none;">
                                <i class="fas fa-times"></i> LIMPIAR FILTRO FECHA
                            </button>
                        @endif
                    </div>
                </div>
                @if(request()->filled('fecha_especifica'))
                    <small class="text-info d-block mt-2">
                        <i class="fas fa-info-circle"></i> 
                        MOSTRANDO HISTORIALES DEL {{ \Carbon\Carbon::parse(request('fecha_especifica'))->format('d/m/Y') }}
                        @if(request()->filled('empresa_id'))
                            @php
                                $empresaSeleccionada = $empresas->firstWhere('id', request('empresa_id'));
                            @endphp
                            @if($empresaSeleccionada)
                                EN <strong>{{ strtoupper($empresaSeleccionada->nombre) }}</strong>
                            @endif
                        @endif
                    </small>
                @endif
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
                        <th>PRÓXIMA CONSULTA</th>
                        <th>RECETAS</th>
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
                        <td>
                            @if($historial->proxima_consulta)
                                <span class="badge {{ strtotime($historial->proxima_consulta) < time() ? 'badge-danger' : 'badge-success' }}">
                                    {{ \Carbon\Carbon::parse($historial->proxima_consulta)->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="badge badge-secondary">NO PROGRAMADA</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($historial->recetas && $historial->recetas->count() > 0)
                                <span class="badge badge-primary" title="Número de recetas">
                                    <i class="fas fa-prescription mr-1"></i>{{ $historial->recetas->count() }}
                                </span>
                            @else
                                <span class="badge badge-secondary" title="Sin recetas">
                                    <i class="fas fa-prescription mr-1"></i>0
                                </span>
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

    /* Estilos para el filtro por fecha */
    .form-inline .form-group {
        margin-bottom: 0.5rem;
    }

    .btn-group .btn {
        transition: all 0.2s ease-in-out;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }

    /* Estilos para el indicador de filtro activo */
    .text-info {
        font-weight: 500;
    }

    .text-info i {
        margin-right: 5px;
    }

    /* Mejoras responsive para los filtros */
    @media (max-width: 768px) {
        .form-inline {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .form-inline .form-group {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .form-inline .btn-group {
            width: 100%;
            flex-direction: column;
        }
        
        .form-inline .btn-group .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
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
            const params = new URLSearchParams();
            params.set('todos', '1');
            
            // Mantener el filtro de empresa si existe
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            window.location.href = '{{ route("historiales_clinicos.index") }}?' + params.toString();
        });

        // Manejar cambios en los filtros - Filtrado automático
        $('#mes, #ano, #empresa_id').change(function() {
            const params = new URLSearchParams();
            
            // Mantener el filtro de fecha específica si está activo
            var currentParams = new URLSearchParams(window.location.search);
            if (currentParams.has('fecha_especifica')) {
                params.set('fecha_especifica', currentParams.get('fecha_especifica'));
            } else {
                // Solo aplicar filtros de año y mes si no hay fecha específica
                if ($('#ano').val()) params.set('ano', $('#ano').val());
                if ($('#mes').val()) params.set('mes', $('#mes').val());
                
                // Si no hay año ni mes, agregar parámetro "todos"
                if (!$('#ano').val() && !$('#mes').val()) {
                    params.set('todos', '1');
                }
            }
            
            // Siempre agregar el filtro de empresa si está seleccionado
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            window.location.href = '{{ route("historiales_clinicos.index") }}?' + params.toString();
        });

        // Manejar clic en el botón de filtrar por fecha - ENVIAR AL SERVIDOR
        $('#filtrarPorFecha').click(function() {
            var fechaSeleccionada = $('#fechaSeleccion').val();
            
            if (!fechaSeleccionada) {
                alert('POR FAVOR SELECCIONE UNA FECHA PARA FILTRAR');
                return;
            }
            
            // Construir URL con filtro de fecha y mantener filtro de sucursal si existe
            const params = new URLSearchParams();
            params.set('fecha_especifica', fechaSeleccionada);
            
            // Mantener el filtro de empresa/sucursal si está seleccionado
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            // Redirigir al servidor con los filtros combinados
            window.location.href = '{{ route("historiales_clinicos.index") }}?' + params.toString();
        });

        // Manejar clic en el botón de limpiar filtro de fecha - REDIRIGIR AL SERVIDOR
        $('#limpiarFiltroFecha').click(function() {
            // Obtener parámetros actuales y remover solo la fecha específica
            var currentParams = new URLSearchParams(window.location.search);
            currentParams.delete('fecha_especifica'); // Remover el filtro de fecha específica
            
            var newUrl = '{{ route("historiales_clinicos.index") }}';
            
            // Si hay otros parámetros (como sucursal), mantenerlos
            if (currentParams.toString()) {
                newUrl += '?' + currentParams.toString();
            } else {
                // Si no hay otros parámetros, ir al mes actual pero mantener sucursal si existe
                const params = new URLSearchParams();
                const now = new Date();
                params.set('ano', now.getFullYear());
                params.set('mes', (now.getMonth() + 1).toString().padStart(2, '0'));
                
                // Mantener el filtro de empresa/sucursal si está seleccionado
                if ($('#empresa_id').val()) {
                    params.set('empresa_id', $('#empresa_id').val());
                }
                
                newUrl += '?' + params.toString();
            }
            
            window.location.href = newUrl;
        });

        // Verificar si hay filtro de fecha activo y mostrar/ocultar botón de limpiar filtro
        @if(request()->filled('fecha_especifica'))
            $('#limpiarFiltroFecha').show();
        @else
            $('#limpiarFiltroFecha').hide();
        @endif

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
