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

        /* Estilos específicos para filtros TC */
        .btn-group .badge {
            font-size: 0.7em;
            margin-left: 5px;
        }
        
        .tc-button {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .tc-status-pendiente {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        
        .tc-status-recibido {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }
        
        .btn-toolbar .btn-group {
            margin-bottom: 0.5rem;
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
                    <select name="empresa" class="form-control custom-select" id="filtroEmpresa">
                        @if(isset($isAdmin) && $isAdmin)
                            <option value="">TODAS LAS SUCURSALES</option>
                        @else
                            <option value="">MIS SUCURSALES</option>
                        @endif
                        @foreach($empresas ?? [] as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa') == $empresa->id ? 'selected' : '' }}>
                                {{ strtoupper($empresa->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="button" class="btn btn-primary mr-2" id="actualButton">ACTUAL</button>
                    <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
                </div>
            </form>

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
                                    @if(request()->filled('empresa'))
                                        FILTROS ACTIVOS ({{ $pagos->count() }})
                                    @else
                                        FILTRO FECHA ({{ $pagos->count() }})
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
                            MOSTRANDO PAGOS DEL {{ \Carbon\Carbon::parse(request('fecha_especifica'))->format('d/m/Y') }}
                            @if(request()->filled('empresa'))
                                @php
                                    $empresaSeleccionada = $empresas->firstWhere('id', request('empresa'));
                                @endphp
                                @if($empresaSeleccionada)
                                    EN <strong>{{ strtoupper($empresaSeleccionada->nombre) }}</strong>
                                @endif
                            @endif
                        </small>
                    @endif
                </div>
            </div>

            {{-- Botones de Filtro TC y Añadir Pago --}}
            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group mr-2" role="group" aria-label="Grupo Añadir">
                    <a type="button" class="btn btn-success" href="{{ route('pagos.create') }}">
                        <i class="fas fa-plus"></i> AÑADIR PAGO
                    </a>
                </div>
                
                <div class="btn-group mr-2" role="group" aria-label="Grupo Filtros TC">
                    <button type="button" class="btn btn-warning" id="filtrarTCPendientes">
                        <i class="fas fa-credit-card"></i> TC PENDIENTES
                        @php
                            $tcPendientes = $pagos->filter(function($pago) {
                                return $pago->mediodepago->medio_de_pago === 'Tarjeta Crédito' && !$pago->TC;
                            })->count();
                        @endphp
                        <span class="badge badge-light">{{ $tcPendientes }}</span>
                    </button>
                    
                    <button type="button" class="btn btn-info" id="filtrarTCRecibidos">
                        <i class="fas fa-check-circle"></i> TC RECIBIDOS
                        @php
                            $tcRecibidos = $pagos->filter(function($pago) {
                                return $pago->mediodepago->medio_de_pago === 'Tarjeta Crédito' && $pago->TC;
                            })->count();
                        @endphp
                        <span class="badge badge-light">{{ $tcRecibidos }}</span>
                    </button>
                    
                    <button type="button" class="btn btn-secondary" id="limpiarFiltroTC" style="display: none;">
                        <i class="fas fa-times"></i> LIMPIAR FILTRO TC
                    </button>
                </div>
                
                <div class="btn-group" role="group" aria-label="Grupo Acciones Masivas TC">
                    <button type="button" class="btn btn-outline-success" id="marcarTodosTCRecibidos" style="display: none;">
                        <i class="fas fa-check-double"></i> MARCAR TODOS COMO RECIBIDOS
                    </button>
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
                            <td>ESTADO TC</td>
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
                                <td>
                                    @if($pago->mediodepago->medio_de_pago === 'Tarjeta Crédito')
                                        @if($pago->TC)
                                            <span class="badge badge-success">RECIBIDO</span>
                                        @else
                                            <span class="badge badge-warning">PENDIENTE</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>${{ number_format($pago->pedido->saldo, 2, ',', '.') }}</td> <!-- Updated to access saldo from pedido -->
                                <td>${{ number_format($pago->pago, 2, ',', '.') }}</td>
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
                        $(button).closest('tr').find('td:nth-child(9)').text('SÍ');
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
                $('#filtroEmpresa').val('');
                
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
                $('#filtroEmpresa').val('');
                
                $('#filterForm').submit();
            });

            // Manejar cambios en los filtros
            $('#filtroAno, #filtroMes, #metodo_pago, #filtroEmpresa').change(function() {
                $('#filterForm').submit();
            });

            // NUEVA FUNCIONALIDAD: Filtro por fecha específica
            $('#filtrarPorFecha').click(function() {
                const fecha = $('#fechaSeleccion').val();
                if (!fecha) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha requerida',
                        text: 'Por favor selecciona una fecha para filtrar.',
                        showConfirmButton: true,
                        timer: 3000
                    });
                    return;
                }

                // Obtener parámetros actuales
                const urlParams = new URLSearchParams(window.location.search);
                const empresa = urlParams.get('empresa') || '';
                
                // Construir URL con filtro de fecha específica
                const baseUrl = window.location.href.split('?')[0];
                let newUrl = baseUrl + '?fecha_especifica=' + encodeURIComponent(fecha);
                
                // Mantener filtro de empresa si existe
                if (empresa) {
                    newUrl += '&empresa=' + encodeURIComponent(empresa);
                }
                
                window.location.href = newUrl;
            });

            // Limpiar filtro de fecha específica
            $('#limpiarFiltroFecha').click(function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete('fecha_especifica');
                
                const baseUrl = window.location.href.split('?')[0];
                const newUrl = baseUrl + (urlParams.toString() ? '?' + urlParams.toString() : '');
                
                window.location.href = newUrl;
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
                    "targets": [8], // Índice de la columna TC (ahora es la 8va)
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
                            "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8]
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
                            "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            // NUEVA FUNCIONALIDAD: Filtros para Tarjetas de Crédito
            let filtroTCActivo = false;

            // Filtrar TC Pendientes
            $('#filtrarTCPendientes').click(function() {
                filtrarTarjetasCredito('pendientes');
                mostrarBotonesTC(true);
                $('#limpiarFiltroTC').show();
            });

            // Filtrar TC Recibidos
            $('#filtrarTCRecibidos').click(function() {
                filtrarTarjetasCredito('recibidos');
                mostrarBotonesTC(false);
                $('#limpiarFiltroTC').show();
            });

            // Limpiar filtro TC
            $('#limpiarFiltroTC').click(function() {
                pagosTable.search('').columns().search('').draw();
                filtroTCActivo = false;
                $('#limpiarFiltroTC').hide();
                $('#marcarTodosTCRecibidos').hide();
            });

            // Marcar todos los TC como recibidos
            $('#marcarTodosTCRecibidos').click(function() {
                if (!confirm('¿Está seguro de marcar TODOS los pagos con tarjeta de crédito pendientes como recibidos?')) {
                    return;
                }

                const botonesPendientes = $('.tc-button[data-status="pending"]');
                let promesas = [];

                botonesPendientes.each(function() {
                    const pagoId = $(this).data('id');
                    const button = $(this);
                    
                    const promesa = $.ajax({
                        url: `/pagos/${pagoId}/update-tc`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    }).then(function(response) {
                        if (response.success) {
                            // Cambiar el botón a "RECIBIDO"
                            button.replaceWith(`
                                <button class="btn btn-xs btn-success mx-1 shadow" disabled>
                                    RECIBIDO
                                </button>
                            `);
                            
                            // Actualizar la celda oculta de TC
                            button.closest('tr').find('td:nth-child(9)').text('SÍ');
                        }
                    });
                    
                    promesas.push(promesa);
                });

                Promise.all(promesas).then(function() {
                    // Actualizar los contadores
                    location.reload();
                }).catch(function() {
                    alert('Error al procesar algunos pagos');
                });
            });

            function filtrarTarjetasCredito(tipo) {
                // Resetear búsqueda anterior
                pagosTable.search('').columns().search('').draw();
                
                if (tipo === 'pendientes') {
                    // Mostrar solo filas con TC pendiente (botón PENDIENTE visible)
                    pagosTable.column(5).search('TARJETA CRÉDITO', true, false).draw();
                    
                    // Después del filtro inicial, ocultar filas con TC recibido
                    setTimeout(function() {
                        pagosTable.rows().every(function() {
                            const row = this.node();
                            const tcButton = $(row).find('.tc-button[data-status="pending"]');
                            const recibidoButton = $(row).find('button:contains("RECIBIDO")');
                            
                            if (recibidoButton.length > 0) {
                                $(row).hide();
                            } else if (tcButton.length === 0) {
                                // Si no es tarjeta de crédito, también ocultar
                                const metodoPago = $(row).find('td:eq(5)').text().trim();
                                if (metodoPago !== 'TARJETA CRÉDITO') {
                                    $(row).hide();
                                }
                            }
                        });
                    }, 100);
                    
                } else if (tipo === 'recibidos') {
                    // Mostrar solo filas con TC recibido
                    pagosTable.column(5).search('TARJETA CRÉDITO', true, false).draw();
                    
                    // Después del filtro inicial, ocultar filas con TC pendiente
                    setTimeout(function() {
                        pagosTable.rows().every(function() {
                            const row = this.node();
                            const tcButton = $(row).find('.tc-button[data-status="pending"]');
                            const recibidoButton = $(row).find('button:contains("RECIBIDO")');
                            
                            if (tcButton.length > 0) {
                                $(row).hide();
                            } else if (recibidoButton.length === 0) {
                                // Si no es tarjeta de crédito recibida, también ocultar
                                const metodoPago = $(row).find('td:eq(5)').text().trim();
                                if (metodoPago !== 'TARJETA CRÉDITO') {
                                    $(row).hide();
                                }
                            }
                        });
                    }, 100);
                }
                
                filtroTCActivo = true;
            }

            function mostrarBotonesTC(mostrarMarcarTodos) {
                if (mostrarMarcarTodos) {
                    $('#marcarTodosTCRecibidos').show();
                } else {
                    $('#marcarTodosTCRecibidos').hide();
                }
            }
        });
    </script>
@stop
