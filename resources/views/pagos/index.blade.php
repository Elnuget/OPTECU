@extends('adminlte::page')

@section('title', 'PAGOS')

@section('content_header')
    <h1>PAGOS</h1>
    <p>ADMINISTRACIÓN DE PAGOS</p>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-info-circle"></i> IMPORTANTE:</strong>
        Los pagos se filtran por la fecha del pedido asociado, no por la fecha de creación del pago. 
        Esto garantiza que los totales coincidan con la vista de pedidos.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
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
        
        .tc-button, .tarjeta-button {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .tc-status-pendiente, .tarjeta-status-pendiente {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        
        .tc-status-recibido, .tarjeta-status-recibido {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }
        
        .btn-toolbar .btn-group {
            margin-bottom: 0.5rem;
        }

        /* Estilo para el filtro de empresa activo */
        .filtro-empresa-activo,
        #filtroEmpresa[style*="border-color: #28a745"] {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
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
                    @elseif(request()->filled('empresa') && request()->filled('ano') && request()->filled('mes'))
                        @php
                            $empresaSeleccionada = $empresas->firstWhere('id', request('empresa'));
                            $nombreMes = strtoupper(date('F', mktime(0, 0, 0, request('mes'), 1)));
                        @endphp
                        <small class="text-success d-block mt-2">
                            <i class="fas fa-building"></i> 
                            MOSTRANDO PAGOS DE <strong>{{ $empresaSeleccionada ? $empresaSeleccionada->nombre : 'SUCURSAL SELECCIONADA' }}</strong> 
                            DEL MES {{ $nombreMes }} {{ request('ano') }}
                            <br>
                            <i class="fas fa-sync-alt"></i> 
                            <em>Los totales coinciden con la vista de pedidos del mismo período (filtrados por fecha del pedido)</em>
                        </small>
                    @endif
                </div>
            </div>

            {{-- Botones de Filtro Tarjetas y Añadir Pago --}}
            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group mr-2" role="group" aria-label="Grupo Añadir">
                    <a type="button" class="btn btn-success" href="{{ route('pagos.create') }}">
                        <i class="fas fa-plus"></i> AÑADIR PAGO
                    </a>
                </div>
                
                <div class="btn-group mr-2" role="group" aria-label="Grupo Filtros Tarjetas">
                    <button type="button" class="btn btn-warning" id="filtrarTarjetasPendientes">
                        <i class="fas fa-credit-card"></i> TARJETAS PENDIENTES
                        @php
                            $tarjetasPendientes = $pagos->filter(function($pago) {
                                return in_array($pago->mediodepago->medio_de_pago, ['Tarjeta Crédito', 'Tarjeta Débito', 'Tarjeta Banco']) && !$pago->TC;
                            })->count();
                        @endphp
                        <span class="badge badge-light">{{ $tarjetasPendientes }}</span>
                    </button>
                    
                    <button type="button" class="btn btn-info" id="filtrarTarjetasRecibidas">
                        <i class="fas fa-check-circle"></i> TARJETAS RECIBIDAS
                        @php
                            $tarjetasRecibidas = $pagos->filter(function($pago) {
                                return in_array($pago->mediodepago->medio_de_pago, ['Tarjeta Crédito', 'Tarjeta Débito', 'Tarjeta Banco']) && $pago->TC;
                            })->count();
                        @endphp
                        <span class="badge badge-light">{{ $tarjetasRecibidas }}</span>
                    </button>
                    
                    <button type="button" class="btn btn-secondary" id="limpiarFiltroTarjetas" style="display: none;">
                        <i class="fas fa-times"></i> LIMPIAR FILTRO TARJETAS
                    </button>
                </div>
                
                <div class="btn-group" role="group" aria-label="Grupo Acciones Masivas Tarjetas">
                    <button type="button" class="btn btn-outline-success" id="marcarTodasTarjetasRecibidas" style="display: none;">
                        <i class="fas fa-check-double"></i> MARCAR TODAS COMO RECIBIDAS
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
                            <td>FECHA DE PEDIDO</td> <!-- Cambiado para mostrar fecha del pedido -->
                            <td>ORDEN ASOCIADA</td>
                            <td>CLIENTE ASOCIADO</td>
                            <td>EMPRESA</td>
                            <!-- Removed Paciente column -->
                            <td>MÉTODO DE PAGO</td>
                            <td>ESTADO TARJETA</td>
                            <td>PAGO</td>
                            <td style="display: none;">TC</td>
                            <td>ACCIONES</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagos as $index => $pago)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($pago->pedido->fecha)->format('Y-m-d') }}</td> <!-- Fecha del Pedido -->
                                <td>{{ $pago->pedido->numero_orden }}</td> <!-- Orden Asociada -->
                                <td>{{ $pago->pedido->cliente }}</td> <!-- Cliente Asociado -->
                                <td>{{ $pago->pedido->empresa ? $pago->pedido->empresa->nombre : 'N/A' }}</td> <!-- Empresa Asociada -->
                                <!-- Removed Paciente data -->
                                <td>{{ $pago->mediodepago->medio_de_pago }}</td>
                                <td>
                                    @if(in_array($pago->mediodepago->medio_de_pago, ['Tarjeta Crédito', 'Tarjeta Débito', 'Tarjeta Banco']))
                                        @if($pago->TC)
                                            <span class="badge badge-success">RECIBIDO</span>
                                        @else
                                            <span class="badge badge-warning">PENDIENTE</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
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

                                    @if(!$pago->TC && in_array($pago->mediodepago->medio_de_pago, ['Tarjeta Crédito', 'Tarjeta Débito', 'Tarjeta Banco']))
                                    <button class="btn btn-xs btn-warning mx-1 shadow tarjeta-button" 
                                        data-id="{{ $pago->id }}"
                                        data-status="pending"
                                        data-tipo="{{ $pago->mediodepago->medio_de_pago }}"
                                        onclick="updateTC({{ $pago->id }}, this)">
                                        PENDIENTE
                                    </button>
                                    @elseif($pago->TC && in_array($pago->mediodepago->medio_de_pago, ['Tarjeta Crédito', 'Tarjeta Débito', 'Tarjeta Banco']))
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
            const tipoTarjeta = $(button).data('tipo') || 'Tarjeta';
            if (!confirm(`¿Está seguro de marcar este pago de ${tipoTarjeta} como recibido?`)) {
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
                        
                        // Actualizar el badge de estado
                        $(button).closest('tr').find('td:nth-child(7)').html('<span class="badge badge-success">RECIBIDO</span>');
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

            // Función para cargar sucursal por defecto desde localStorage
            function cargarSucursalPorDefecto() {
                // Verificar si ya hay el parámetro empresa en la URL para evitar bucle infinito
                const urlParams = new URLSearchParams(window.location.search);
                const tieneEmpresaEnUrl = urlParams.has('empresa');
                
                // Solo preseleccionar si no hay empresa ya seleccionada en la URL
                if (tieneEmpresaEnUrl) {
                    return;
                }

                // Usar la nueva clase SucursalCache si está disponible
                if (window.SucursalCache) {
                    // Usar auto-submit solo si no hay empresa en URL
                    SucursalCache.preseleccionarEnSelect('filtroEmpresa', true);
                } else {
                    // Fallback al método anterior
                    try {
                        const sucursalData = localStorage.getItem('sucursal_abierta');
                        if (sucursalData) {
                            const sucursal = JSON.parse(sucursalData);
                            const empresaSelect = document.getElementById('filtroEmpresa');
                            if (empresaSelect) {
                                const option = empresaSelect.querySelector(`option[value="${sucursal.id}"]`);
                                if (option) {
                                    empresaSelect.value = sucursal.id;
                                    empresaSelect.style.borderColor = '#28a745';
                                    empresaSelect.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
                                    
                                    // Aplicar filtro del mes actual automáticamente al seleccionar sucursal
                                    const currentDate = new Date();
                                    const currentYear = currentDate.getFullYear();
                                    const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                                    
                                    $('#filtroAno').val(currentYear);
                                    $('#filtroMes').val(currentMonth);
                                    
                                    console.log('Sucursal preseleccionada con filtro de mes actual:', sucursal.nombre);
                                    $('#filterForm').submit();
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Error al cargar sucursal por defecto:', e);
                    }
                }
            }

            // Cargar sucursal por defecto al inicializar
            cargarSucursalPorDefecto();

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
                    "targets": [8], // Índice de la columna TC
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
                    "url": "{{ asset('js/datatables/Spanish.json') }}"
                }
            });

            // NUEVA FUNCIONALIDAD: Filtros para Todas las Tarjetas
            let filtroTarjetasActivo = false;

            // Filtrar Tarjetas Pendientes
            $('#filtrarTarjetasPendientes').click(function() {
                console.log('Iniciando filtro de tarjetas pendientes...');
                filtrarTarjetas('pendientes');
                mostrarBotonesTarjetas(true);
                $('#limpiarFiltroTarjetas').show();
            });

            // Filtrar Tarjetas Recibidas
            $('#filtrarTarjetasRecibidas').click(function() {
                console.log('Iniciando filtro de tarjetas recibidas...');
                filtrarTarjetas('recibidas');
                mostrarBotonesTarjetas(false);
                $('#limpiarFiltroTarjetas').show();
            });

            // Limpiar filtro Tarjetas
            $('#limpiarFiltroTarjetas').click(function() {
                // Recargar la página para limpiar todos los filtros
                location.reload();
            });

            // Marcar todas las Tarjetas como recibidas
            $('#marcarTodasTarjetasRecibidas').click(function() {
                if (!confirm('¿Está seguro de marcar TODAS las tarjetas pendientes como recibidas?')) {
                    return;
                }

                const botonesPendientes = $('.tarjeta-button[data-status="pending"]');
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
                            
                            // Actualizar el badge de estado
                            button.closest('tr').find('td:nth-child(7)').html('<span class="badge badge-success">RECIBIDO</span>');
                        }
                    });
                    
                    promesas.push(promesa);
                });

                Promise.all(promesas).then(function() {
                    // Actualizar los contadores
                    location.reload();
                }).catch(function() {
                    alert('Error al procesar algunas tarjetas');
                });
            });

            function filtrarTarjetas(tipo) {
                console.log('Aplicando filtro:', tipo);
                
                // Limpiar filtros anteriores y mostrar todas las filas
                pagosTable.search('').columns().search('').draw();
                
                let filasVisibles = 0;
                
                // Procesar cada fila después de un pequeño delay
                setTimeout(function() {
                    pagosTable.rows().every(function() {
                        const row = this.node();
                        const $row = $(row);
                        
                        // Obtener el método de pago (columna 6)
                        const metodoPago = $row.find('td:eq(5)').text().trim();
                        console.log('Método de pago encontrado:', metodoPago);
                        
                        // Verificar si es una tarjeta (cualquier tipo)
                        const esTarjeta = metodoPago.toLowerCase().includes('tarjeta');
                        
                        if (!esTarjeta) {
                            $row.hide();
                            return;
                        }
                        
                        let mostrar = false;
                        
                        if (tipo === 'pendientes') {
                            // Buscar si tiene botón PENDIENTE
                            const tienePendiente = $row.find('button:contains("PENDIENTE")').length > 0;
                            mostrar = tienePendiente;
                        } else if (tipo === 'recibidas') {
                            // Buscar si tiene botón RECIBIDO o badge RECIBIDO
                            const tieneRecibido = $row.find('button:contains("RECIBIDO")').length > 0 || 
                                                 $row.find('.badge:contains("RECIBIDO")').length > 0;
                            mostrar = tieneRecibido;
                        }
                        
                        if (mostrar) {
                            $row.show();
                            filasVisibles++;
                        } else {
                            $row.hide();
                        }
                    });
                    
                    console.log(`Filtro completado. Filas visibles: ${filasVisibles}`);
                }, 200);
                
                filtroTarjetasActivo = true;
            }

            function mostrarBotonesTarjetas(mostrarMarcarTodos) {
                if (mostrarMarcarTodos) {
                    $('#marcarTodasTarjetasRecibidas').show();
                } else {
                    $('#marcarTodasTarjetasRecibidas').hide();
                }
            }
        });
    </script>
@stop
