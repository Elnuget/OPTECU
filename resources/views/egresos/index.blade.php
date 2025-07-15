@extends('adminlte::page')

@section('title', 'EGRESOS')

@section('content_header')
    <h1>EGRESOS</h1>
    <p>ADMINISTRACIÓN DE EGRESOS</p>
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
            @can('admin')
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-danger">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL EGRESOS</span>
                            <span class="info-box-number">${{ number_format($totales['egresos'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            {{-- Agregar formulario de filtro --}}
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

            {{-- Botón Añadir Egreso --}}
            <div class="btn-group mb-3">
                <a type="button" class="btn btn-success" href="{{ route('egresos.create') }}">AÑADIR EGRESO</a>
                <button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#pagarSueldoModal">
                    <i class="fas fa-money-bill-wave mr-2"></i>PAGAR SUELDO
                </button>
            </div>

            <div class="table-responsive">
                <table id="egresosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>USUARIO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($egresos as $egreso)
                            <tr>
                                <td>{{ $egreso->created_at->format('Y-m-d') }}</td>
                                <td>{{ $egreso->motivo }}</td>
                                <td>${{ number_format($egreso->valor, 0, ',', '.') }}</td>
                                <td>{{ $egreso->user->name }}</td>
                                <td>
                                    <a href="{{ route('egresos.show', $egreso->id) }}"
                                        class="btn btn-xs btn-default text-info mx-1 shadow" title="Ver">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </a>
                                    @can('admin')
                                    <a href="{{ route('egresos.edit', $egreso->id) }}"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </a>

                                    <a class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        href="#"
                                        data-toggle="modal"
                                        data-target="#confirmarEliminarModal"
                                        data-id="{{ $egreso->id }}"
                                        data-url="{{ route('egresos.destroy', $egreso->id) }}">
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
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE EGRESO?</p>
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

    <!-- Modal Pagar Sueldo -->
    <div class="modal fade" id="pagarSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PAGAR SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('egresos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mes_pedidos">MES PARA CONSULTAR PEDIDOS:</label>
                                    <select name="mes_pedidos" id="mes_pedidos" class="form-control">
                                        <option value="">SELECCIONE MES</option>
                                        @php
                                            $currentMonth = date('n');
                                        @endphp
                                        @foreach (['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'] as $index => $month)
                                            <option value="{{ $index + 1 }}" {{ $currentMonth == ($index + 1) ? 'selected' : '' }}>
                                                {{ $month }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ano_pedidos">AÑO PARA CONSULTAR PEDIDOS:</label>
                                    <select name="ano_pedidos" id="ano_pedidos" class="form-control">
                                        <option value="">SELECCIONE AÑO</option>
                                        @php
                                            $currentYear = date('Y');
                                        @endphp
                                        @for ($year = date('Y'); $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="usuario">SELECCIONAR USUARIO:</label>
                            <select name="usuario" id="usuario" class="form-control" required>
                                <option value="">SELECCIONE UN USUARIO</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group text-center">
                            <button type="button" class="btn btn-info" id="btnConsultarPedidos">
                                <i class="fas fa-search mr-2"></i>CONSULTAR PEDIDOS
                            </button>
                        </div>
                        
                        <!-- Información de pedidos del usuario -->
                        <div id="infoPedidos" class="card card-info" style="display: none;">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><i class="fas fa-shopping-cart mr-2"></i>INFORMACIÓN DE PEDIDOS DEL USUARIO</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box bg-primary">
                                            <span class="info-box-icon"><i class="fas fa-list-ol"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">TOTAL PEDIDOS</span>
                                                <span class="info-box-number" id="totalPedidos">0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">VALOR TOTAL</span>
                                                <span class="info-box-number">$<span id="valorTotal">0</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="valor">VALOR DEL SUELDO:</label>
                            <input type="number" class="form-control" id="valor" name="valor" required step="1" min="0">
                        </div>
                        <input type="hidden" name="motivo" value="PAGO DE SUELDO">
                        <input type="hidden" name="mes_pedidos" id="hidden_mes_pedidos">
                        <input type="hidden" name="ano_pedidos" id="hidden_ano_pedidos">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">PAGAR SUELDO</button>
                    </div>
                </form>
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
            var egresosTable = $('#egresosTable').DataTable({
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
                        "filename": 'Egresos.pdf',
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

            // Inicializar select2 para los combobox
            $('#usuario').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE UN USUARIO',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#pagarSueldoModal')
            });

            $('#mes_pedidos').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE MES',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#pagarSueldoModal')
            });

            $('#ano_pedidos').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE AÑO',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#pagarSueldoModal')
            });

            // Limpiar el formulario cuando se cierre el modal
            $('#pagarSueldoModal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
                $('#usuario').val('').trigger('change');
                $('#infoPedidos').hide();
            });

            // Función para obtener pedidos por usuario
            function obtenerPedidosUsuario() {
                var usuarioId = $('#usuario').val();
                var mes = $('#mes_pedidos').val();
                var ano = $('#ano_pedidos').val();

                console.log('Usuario ID:', usuarioId);
                console.log('Mes:', mes);
                console.log('Año:', ano);

                // Validar que todos los campos estén llenos
                if (!usuarioId) {
                    alert('POR FAVOR SELECCIONE UN USUARIO');
                    return;
                }
                if (!mes) {
                    alert('POR FAVOR SELECCIONE UN MES');
                    return;
                }
                if (!ano) {
                    alert('POR FAVOR SELECCIONE UN AÑO');
                    return;
                }

                // Mostrar loading en el botón
                $('#btnConsultarPedidos').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>CONSULTANDO...');

                console.log('Iniciando AJAX...');

                $.ajax({
                    url: '{{ route("egresos.pedidos-usuario") }}',
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: {
                        usuario_id: usuarioId,
                        mes: mes,
                        ano: ano,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Respuesta exitosa:', response);
                        $('#totalPedidos').text(response.total_pedidos);
                        $('#valorTotal').text(new Intl.NumberFormat('es-CO', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(response.total_valor));
                        $('#infoPedidos').show();
                        
                        // Restaurar botón
                        $('#btnConsultarPedidos').prop('disabled', false).html('<i class="fas fa-search mr-2"></i>CONSULTAR PEDIDOS');
                    },
                    error: function(xhr, status, error) {
                        console.log('Error AJAX:', xhr.responseText);
                        console.log('Status:', status);
                        console.log('Error:', error);
                        $('#infoPedidos').hide();
                        alert('ERROR AL OBTENER LA INFORMACIÓN DE PEDIDOS: ' + xhr.responseText);
                        
                        // Restaurar botón
                        $('#btnConsultarPedidos').prop('disabled', false).html('<i class="fas fa-search mr-2"></i>CONSULTAR PEDIDOS');
                    }
                });
            }

            // Evento del botón consultar - versión simplificada
            $(document).on('click', '#btnConsultarPedidos', function() {
                console.log('Botón consultar clickeado');
                obtenerPedidosUsuario();
            });

            // Limpiar información cuando cambien mes y año (pero no cuando cambie usuario)
            $('#mes_pedidos, #ano_pedidos').on('change', function() {
                $('#infoPedidos').hide();
            });

            // Función para obtener el último sueldo del usuario
            function obtenerUltimoSueldo(usuarioId) {
                if (!usuarioId) return;
                
                $.ajax({
                    url: '{{ route("egresos.ultimo-sueldo-usuario") }}',
                    method: 'GET',
                    data: { usuario_id: usuarioId },
                    success: function(response) {
                        if (response.ultimo_sueldo) {
                            $('#valor').val(response.ultimo_sueldo);
                            console.log('Último sueldo autocompleted:', response.ultimo_sueldo);
                        } else {
                            $('#valor').val('');
                            console.log('No hay sueldo anterior para este usuario');
                        }
                    },
                    error: function() {
                        console.log('Error al obtener el último sueldo');
                        $('#valor').val('');
                    }
                });
            }

            // Evento cuando cambia el usuario - autocompletar último sueldo
            $('#usuario').on('change', function() {
                var usuarioId = $(this).val();
                obtenerUltimoSueldo(usuarioId);
                $('#infoPedidos').hide(); // Ocultar info de pedidos cuando cambie usuario
            });

            // Función para actualizar campos ocultos antes de enviar
            function actualizarCamposOcultos() {
                var mes = $('#mes_pedidos').val();
                var ano = $('#ano_pedidos').val();
                
                $('#hidden_mes_pedidos').val(mes);
                $('#hidden_ano_pedidos').val(ano);
                
                console.log('Campos ocultos actualizados - Mes:', mes, 'Año:', ano);
            }

            // Actualizar campos ocultos cuando cambien los selects
            $('#mes_pedidos, #ano_pedidos').on('change', function() {
                actualizarCamposOcultos();
            });

            // Actualizar campos ocultos antes de enviar el formulario
            $('form[action="{{ route('egresos.store') }}"]').on('submit', function() {
                actualizarCamposOcultos();
                return true; // Continuar con el envío
            });

            // Inicializar campos ocultos al abrir el modal
            $('#pagarSueldoModal').on('shown.bs.modal', function() {
                actualizarCamposOcultos();
            });
        });
    </script>
@stop 