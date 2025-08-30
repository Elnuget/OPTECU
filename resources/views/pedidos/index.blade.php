@extends('adminlte::page')
@section('title', 'Pedidos')

@section('content_header')
<h1>Pedidos</h1>
<p>Administracion de ventas</p>
@if (session('error'))
    <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
        <strong>{{ session('mensaje') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif @stop

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
        .btn {
            text-transform: uppercase !important;
        }
        
        /* Estilos para el botón mostrar todos */
        #mostrarTodosButton {
            position: relative;
        }
        
        .modo-todos-activo #mostrarTodosButton {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
        
        .filtro-empresa-activo {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        }
    </style>

<div class="card">
    <div class="card-body">
        {{-- Resumen de totales --}}
        @can('admin')
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Ventas</span>
                        <span class="info-box-number">${{ number_format($totales['ventas'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Saldos</span>
                        <span class="info-box-number">${{ number_format($totales['saldos'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cobrado</span>
                        <span class="info-box-number">${{ number_format($totales['cobrado'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        {{-- Agregar formulario de filtro --}}
        <form method="GET" class="form-row mb-3" id="filterForm">
            <div class="col-md-2">
                <label for="filtroAno">Seleccionar Año:</label>
                <select name="ano" class="form-control" id="filtroAno">
                    <option value="">Seleccione Año</option>
                    @for ($year = date('Y'); $year >= 2000; $year--)
                        <option value="{{ $year }}" {{ request('ano', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroMes">Seleccionar Mes:</label>
                <select name="mes" class="form-control custom-select" id="filtroMes">
                    <option value="">Seleccione Mes</option>
                    @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                        <option value="{{ $index + 1 }}" {{ request('mes') == ($index + 1) ? 'selected' : '' }}>{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroEmpresa">Seleccionar Empresa:</label>
                <select name="empresa_id" class="form-control" id="filtroEmpresa">
                    <option value="">Todas las Empresas</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="button" class="btn btn-primary" id="actualButton">Actual</button>
                <button type="button" class="btn btn-success" id="mostrarTodosButton">Mostrar Todos los Pedidos</button>
            </div>
        </form>

        {{-- Indicador de modo "Mostrar todos" --}}
        @if(request('todos') == '1')
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Modo: Mostrar Todos los Pedidos</strong> - Estás viendo todos los pedidos sin filtros de fecha.
            <a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-outline-primary ml-3">
                <i class="fas fa-filter"></i> Volver a Filtros
            </a>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        {{-- Botones de acción --}}
        <div class="btn-group mb-3">
            <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Crear Pedido</a>
            @can('admin')
                <a href="{{ route('declarantes.index') }}" class="btn btn-info">
                    <i class="fas fa-file-alt"></i> Declarantes
                </a>
                <a href="{{ route('facturas.index') }}" class="btn btn-warning">
                    <i class="fas fa-file-invoice"></i> Facturas
                </a>
            @endcan
        </div>

        {{-- Filtro por mes (removed) --}}
        <!-- Previously here, now removed -->

        <div class="table-responsive">
            <table id="pedidosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Orden</th>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Paciente</th>
                        <th>Empresa</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Acciones</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->fecha ? $pedido->fecha->format('Y-m-d') : 'Sin fecha' }}</td>
                        <td>{{ $pedido->numero_orden }}</td>
                        <td>
                            <span style="color: {{ $pedido->fact == 'Pendiente' ? 'orange' : ($pedido->fact == 'Aprobado' ? 'green' : 'black') }}">
                                {{ $pedido->fact }}
                            </span>
                        </td>
                        <td>{{ $pedido->cliente }}</td>
                        <td>
                            {{ $pedido->celular }}
                            @if($pedido->celular)
                                <button 
                                    class="btn {{ trim($pedido->encuesta) === 'enviado' ? 'btn-warning' : 'btn-success' }} btn-sm ml-1 btn-whatsapp-mensaje"
                                    data-pedido-id="{{ $pedido->id }}"
                                    data-celular="{{ ltrim($pedido->celular, '0') }}"
                                    data-cliente="{{ $pedido->cliente }}"
                                    data-paciente="{{ $pedido->paciente }}"
                                    data-estado-actual="{{ trim($pedido->encuesta) }}"
                                    title="{{ trim($pedido->encuesta) === 'enviado' ? 'Volver a enviar mensaje y encuesta' : 'Enviar mensaje y encuesta' }}">
                                    <i class="fab fa-whatsapp"></i>
                                    <span class="button-text">
                                        {{ trim($pedido->encuesta) === 'enviado' ? 'Volver a enviar' : 'Enviar' }}
                                    </span>
                                </button>
                            @endif
                        </td>
                        <td>{{ $pedido->paciente }}</td>
                        <td>{{ $pedido->empresa ? $pedido->empresa->nombre : 'Sin empresa' }}</td>
                        <td>{{ $pedido->total }}</td>
                        <td>
                            <span style="color: {{ $pedido->saldo == 0 ? 'green' : 'red' }}">
                                {{ $pedido->saldo }}
                            </span>
                        </td>                        <td>
                            <div class="btn-group">
                                <a href="{{ route('pedidos.show', $pedido->id) }}"
                                    class="btn btn-xs btn-default text-primary mx-1 shadow" title="Ver">
                                    <i class="fa fa-lg fa-fw fa-eye"></i>
                                </a>
                                <a href="{{ route('pedidos.edit', $pedido->id) }}"
                                    class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                    <i class="fa fa-lg fa-fw fa-pen"></i>
                                </a>
                                @can('admin')
                                    <a class="btn btn-xs btn-default text-danger mx-1 shadow" href="#" data-toggle="modal"
                                        data-target="#confirmarEliminarModal" data-id="{{ $pedido->id }}"
                                        data-url="{{ route('pedidos.destroy', $pedido->id) }}">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </a>
                                @endcan
                                <!-- Botón de Pago -->
                                <a href="{{ route('pagos.create', ['pedido_id' => $pedido->id]) }}"
                                    class="btn btn-success btn-sm" title="Añadir Pago">
                                    <i class="fas fa-money-bill-wave"></i>
                                </a>
                                <!-- Botón de Crear Factura -->
                                @can('admin')
                                    @if(strtoupper($pedido->fact) == 'PENDIENTE')
                                        <a href="{{ route('facturas.create', ['pedido_id' => $pedido->id]) }}" 
                                           class="btn btn-warning btn-sm btn-crear-factura"
                                           title="Crear Factura"
                                           data-pedido-id="{{ $pedido->id }}">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </td>
                        <td>{{ $pedido->usuario }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br />
    </div>
</div>

{{-- Agregar el modal de confirmación después de la tabla --}}
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este pedido?</p>
            </div>
            <div class="modal-footer">
                <form id="eliminarForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>







<div class="mb-3">
    <a href="{{ route('pedidos.inventario-historial') }}" class="btn btn-info">
        Ver Historial de Inventario
    </a>
</div>

@push('css')
<style>
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating input {
    display: none;
}

.rating label {
    cursor: pointer;
    font-size: 30px;
    color: #ddd;
    padding: 5px;
}

.rating input:checked ~ label {
    color: #ffd700;
}

.rating label:hover,
.rating label:hover ~ label {
    color: #ffd700;
}

/* Estilos para el botón de WhatsApp */
.btn-whatsapp-mensaje {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-whatsapp-mensaje .button-text {
    font-size: 0.875rem;
}

/* Estilos para tablas y formularios */

/* Estilos para controles de formulario */
.form-control {
    border-radius: 0.375rem;
    font-size: 0.9rem;
}

.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    font-size: 0.875em;
    color: #dc3545;
}

/* Botones de acción en la tabla */
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.2rem;
}

.btn-editar-declarante:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-eliminar-declarante:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Estilos para certificados digitales */
.custom-file-label::after {
    content: "Examinar";
}

.cert-type {
    font-weight: 600;
    color: #495057;
}

.firma-thumbnail {
    max-width: 50px;
    max-height: 50px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    object-fit: cover;
}

.firma-preview-large {
    max-width: 200px;
    max-height: 150px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    object-fit: contain;
}

/* Estilos para la tabla de declarantes con certificados */
#declarantesTable .firma-cell {
    text-align: center;
    vertical-align: middle;
}

.archivo-info {
    font-size: 0.8em;
    color: #6c757d;
    word-break: break-all;
}

.sin-archivo {
    color: #6c757d;
    font-style: italic;
    font-size: 0.9em;
}

/* Iconos para certificados */
.fa-certificate, .fa-key {
    margin-bottom: 5px;
}

.cert-icon-container {
    padding: 10px;
    border-radius: 8px;
    background-color: #f8f9fa;
}

/* Estilos para tarjetas y tablas */
.card {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

/* Estilos para tablas */
.table-sm td,
.table-sm th {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.table thead th {
    border-top: none;
    font-weight: 600;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}

/* Loading state */
#detallesLoading i {
    color: #ffc107;
}

/* Botón de crear factura en la tabla */
.btn-crear-factura:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Estilos para insignias y componentes de carga */
.badge {
    font-size: 0.75em;
}

/* Botones de acción */
.btn-action:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush
@stop
@section('js')
@include('atajos')
@parent
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Verificar que SweetAlert2 esté disponible
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 no está cargado');
            alert('Error: SweetAlert2 no está disponible. Por favor, recargue la página.');
            return;
        }

        // Función de fallback para mostrar alertas
        function mostrarAlerta(config) {
            if (typeof Swal !== 'undefined') {
                Swal.fire(config);
            } else {
                // Fallback a alert nativo si SweetAlert2 no está disponible
                var mensaje = config.text || config.title || 'Operación completada';
                if (config.icon === 'success') {
                    alert('✓ ' + mensaje);
                } else if (config.icon === 'error') {
                    alert('✗ ' + mensaje);
                } else if (config.icon === 'warning') {
                    alert('⚠ ' + mensaje);
                } else {
                    alert(mensaje);
                }
            }
        }

        // Función de confirmación con fallback
        function mostrarConfirmacion(config) {
            return new Promise((resolve) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire(config).then((result) => {
                        resolve(result);
                    });
                } else {
                    // Fallback a confirm nativo
                    var mensaje = config.text || config.title || '¿Está seguro?';
                    var resultado = confirm(mensaje);
                    resolve({ isConfirmed: resultado });
                }
            });
        }
        // Configurar el modal antes de mostrarse
        $('#confirmarEliminarModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Botón que activó el modal
            var url = button.data('url'); // Extraer la URL del atributo data-url
            var modal = $(this);
            modal.find('#eliminarForm').attr('action', url); // Actualizar la acción del formulario
        });

        // Inicializar DataTable con nueva configuración
        var pedidosTable = $('#pedidosTable').DataTable({
            "processing": true,
            "scrollX": true,
            "order": [[1, "desc"]], // Ordenar por número de orden descendente
            "paging": false, // Deshabilitar paginación
            "lengthChange": false,
            "info": false,
            "dom": 'Bfrt', // Quitar 'p' del dom para eliminar controles de paginación
            "buttons": [
                {
                    extend: 'excel',
                    text: 'Excel',
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7,9]
                    },
                    filename: 'Pedidos_' + new Date().toISOString().split('T')[0]
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7,9]
                    },
                    filename: 'Pedidos_' + new Date().toISOString().split('T')[0],
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                }
            ],
            "language": {
                "url": "{{ asset('js/datatables/Spanish.json') }}",
                "search": "Buscar:"
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

        // Botón "Mostrar Todos los Pedidos"
        $('#mostrarTodosButton').click(function() {
            // Limpiar todos los filtros antes de redirigir
            $('#filtroAno').val('');
            $('#filtroMes').val('');
            $('#filtroEmpresa').val('');
            
            // Redirigir a la página con parámetro todos=1
            window.location.href = '{{ route("pedidos.index", ["todos" => "1"]) }}';
        });

        // Auto-submit cuando cambie el filtro de empresa
        $('#filtroEmpresa').change(function() {
            // Si estamos en modo "mostrar todos", mantener ese parámetro
            if (window.location.search.includes('todos=1')) {
                const empresaId = $(this).val();
                let url = '{{ route("pedidos.index") }}?todos=1';
                if (empresaId) {
                    url += '&empresa_id=' + empresaId;
                }
                window.location.href = url;
            } else {
                $('#filterForm').submit();
            }
        });

        // Cargar sucursal por defecto desde localStorage
        function cargarSucursalPorDefecto() {
            // No cargar sucursal por defecto si estamos en modo "mostrar todos"
            if (window.location.search.includes('todos=1')) {
                console.log('Modo "mostrar todos" activo, no se carga sucursal por defecto');
                return;
            }
            
            // Usar la nueva clase SucursalCache si está disponible
            if (window.SucursalCache) {
                SucursalCache.preseleccionarEnSelect('filtroEmpresa', true);
            } else {
                // Fallback al método anterior
                try {
                    const sucursalData = localStorage.getItem('sucursal_abierta');
                    if (sucursalData && !window.location.search.includes('empresa_id=')) {
                        const sucursal = JSON.parse(sucursalData);
                        const empresaSelect = document.getElementById('filtroEmpresa');
                        if (empresaSelect) {
                            const option = empresaSelect.querySelector(`option[value="${sucursal.id}"]`);
                            if (option) {
                                empresaSelect.value = sucursal.id;
                                empresaSelect.style.borderColor = '#28a745';
                                empresaSelect.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
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
        
        // Marcar visualmente si estamos en modo "mostrar todos"
        if (window.location.search.includes('todos=1')) {
            $('body').addClass('modo-todos-activo');
            $('#mostrarTodosButton')
                .removeClass('btn-success')
                .addClass('btn-warning')
                .html('<i class="fas fa-check-circle"></i> Mostrando Todos');
        }

        // Auto-submit cuando cambien los filtros de año y mes
        $('#filtroAno, #filtroMes').change(function() {
            // Si estamos en modo "mostrar todos" y se selecciona año o mes, 
            // salir del modo "todos" y aplicar filtros normales
            if (window.location.search.includes('todos=1')) {
                $('#filterForm').attr('action', '{{ route("pedidos.index") }}');
            }
            $('#filterForm').submit();
        });

        // Configurar el modal de eliminación
        $('#confirmarEliminarModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var url = button.data('url');
            var modal = $(this);
            modal.find('#eliminarForm').attr('action', url);
        });

        // Manejar el envío del formulario de eliminación
        $('#eliminarForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#confirmarEliminarModal').modal('hide');
                    // Recargar la página o actualizar la tabla
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Error al eliminar el pedido');
                }
            });
        });

        // Función mejorada para envío seguro de WhatsApp
        function enviarWhatsAppSeguro(telefono, mensaje, callback) {
            // Limpiar el número de teléfono
            var numeroLimpio = telefono.toString().replace(/[^\d]/g, '');
            
            // Asegurar que tenga el código de país (Ecuador: 593)
            if (!numeroLimpio.startsWith('593')) {
                // Si empieza con 0, quitarlo y agregar 593
                if (numeroLimpio.startsWith('0')) {
                    numeroLimpio = '593' + numeroLimpio.substring(1);
                } else {
                    numeroLimpio = '593' + numeroLimpio;
                }
            }
            
            // Codificar el mensaje de forma segura
            var mensajeCodificado = encodeURIComponent(mensaje);
            
            // Crear URLs para diferentes casos
            var urlWeb = `https://web.whatsapp.com/send?phone=${numeroLimpio}&text=${mensajeCodificado}`;
            var urlApi = `https://api.whatsapp.com/send?phone=${numeroLimpio}&text=${mensajeCodificado}`;
            var urlWa = `https://wa.me/${numeroLimpio}?text=${mensajeCodificado}`;
            
            // Mostrar modal de selección de método de envío
            var modalHtml = `
                <div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">ENVIAR MENSAJE DE WHATSAPP</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Número:</strong> +${numeroLimpio}</p>
                                <p><strong>Vista previa del mensaje:</strong></p>
                                <div class="alert alert-info" style="max-height: 200px; overflow-y: auto; white-space: pre-wrap; font-size: 0.9em;">${mensaje}</div>
                                <p>Seleccione cómo desea enviar el mensaje:</p>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <button type="button" class="btn btn-success btn-block" onclick="abrirWhatsApp('${urlWa}')">
                                            <i class="fab fa-whatsapp"></i> WhatsApp Oficial
                                        </button>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <button type="button" class="btn btn-info btn-block" onclick="abrirWhatsApp('${urlWeb}')">
                                            <i class="fab fa-whatsapp"></i> WhatsApp Web
                                        </button>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <button type="button" class="btn btn-secondary btn-block" onclick="copiarMensaje('${numeroLimpio}', \`${mensaje.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)">
                                            <i class="fas fa-copy"></i> Copiar Mensaje y Número
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal anterior si existe
            $('#whatsappModal').remove();
            
            // Agregar modal al DOM
            $('body').append(modalHtml);
            
            // Mostrar modal
            $('#whatsappModal').modal('show');
            
            // Ejecutar callback si se proporciona
            if (callback) callback();
        }
        
        // Función para abrir WhatsApp
        window.abrirWhatsApp = function(url) {
            $('#whatsappModal').modal('hide');
            
            // Intentar abrir la URL
            var ventana = window.open(url, '_blank');
            
            // Verificar si se abrió correctamente
            setTimeout(function() {
                if (!ventana || ventana.closed || typeof ventana.closed == 'undefined') {
                    // Si no se pudo abrir, mostrar alerta
                    mostrarAlerta({
                        icon: 'warning',
                        title: 'Bloqueador de Ventanas',
                        html: `
                            <p>No se pudo abrir WhatsApp automáticamente.</p>
                            <p>Por favor, haga clic en el siguiente enlace:</p>
                            <a href="${url}" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp"></i> Abrir WhatsApp
                            </a>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                }
            }, 1000);
        }
        
        // Función para copiar mensaje y número
        window.copiarMensaje = function(numero, mensaje) {
            var textoCompleto = `Número: +${numero}\n\nMensaje:\n${mensaje}`;
            
            // Intentar copiar al portapapeles
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textoCompleto).then(() => {
                    $('#whatsappModal').modal('hide');
                    mostrarAlerta({
                        icon: 'success',
                        title: '¡Copiado!',
                        text: 'El número y mensaje han sido copiados al portapapeles.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(() => {
                    mostrarTextoParaCopiar(textoCompleto);
                });
            } else {
                mostrarTextoParaCopiar(textoCompleto);
            }
        }
        
        // Función para mostrar texto para copiar manualmente
        function mostrarTextoParaCopiar(texto) {
            $('#whatsappModal').modal('hide');
            mostrarAlerta({
                title: 'Copiar Manualmente',
                html: `<textarea class="form-control" rows="8" readonly style="width: 100%;">${texto}</textarea>`,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar',
                width: '600px'
            });
        }

        // Manejar el envío del mensaje de WhatsApp con encuesta
        $('.btn-whatsapp-mensaje').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var pedidoId = button.data('pedido-id');
            var celular = button.data('celular');
            var paciente = button.data('paciente');
            var estadoActual = button.data('estado-actual');

            // Primero obtener la URL de la encuesta y actualizar estado
            $.ajax({
                url: '/pedidos/' + pedidoId + '/enviar-encuesta',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Construir mensaje con saludo personalizado al paciente
                        var mensajeSaludo = "Estimado(a) paciente " + paciente + ",";
                        var mensajeLentes = "Le informamos que sus lentes recetados ya están listos para ser recogidos en ESCLERÓPTICA 👀👁. Puede pasar a retirarlos cuando le sea más conveniente. ¡Lo esperamos pronto! Muchas gracias por confiar en nosotros. 🤓👓😊";
                        
                        // Verificar si hay URL de encuesta en la respuesta
                        var mensajeEncuesta = "";
                        if (response.url && response.url.trim() !== '') {
                            // La URL viene en el campo 'url' según el controlador
                            var textoEnlace = response.texto_amigable || "➡️ *CLICK AQUÍ PARA COMPLETAR LA ENCUESTA* ⬅️";
                            mensajeEncuesta = "\n\nNos gustaría conocer su opinión. Por favor, complete nuestra breve encuesta de satisfacción:\n\n" + textoEnlace + "\n" + response.url;
                        } else if (response.encuesta_url && response.encuesta_url.trim() !== '') {
                            // Fallback por si cambia en el futuro
                            mensajeEncuesta = "\n\nNos gustaría conocer su opinión. Por favor, complete nuestra breve encuesta de satisfacción:\n" + response.encuesta_url;
                        } else {
                            // Si no hay URL, generar mensaje alternativo
                            console.warn('No se encontró URL de encuesta en la respuesta:', response);
                            mensajeEncuesta = "\n\nNos gustaría conocer su opinión sobre nuestro servicio. ¡Gracias por confiar en ESCLERÓPTICA!";
                        }
                        
                        // Crear el mensaje completo
                        var mensajeCompleto = mensajeSaludo + "\n\n" + mensajeLentes + mensajeEncuesta;
                        
                        // Debug: mostrar en consola para verificar
                        console.log('Respuesta del servidor:', response);
                        console.log('Mensaje completo:', mensajeCompleto);
                        
                        // Usar la función mejorada de WhatsApp
                        enviarWhatsAppSeguro(celular, mensajeCompleto, function() {
                            // Actualizar el estado visual del botón
                            button.removeClass('btn-success').addClass('btn-warning');
                            button.attr('title', 'Volver a enviar mensaje y encuesta');
                            button.find('.button-text').text('Volver a enviar');
                            button.data('estado-actual', 'enviado');
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error en la petición:', xhr);
                    
                    var mensajeError = 'Error al generar el enlace de la encuesta';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensajeError = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        mensajeError = xhr.responseJSON.error;
                    } else if (xhr.status === 0) {
                        mensajeError = 'No se pudo conectar con el servidor. Verifique su conexión a internet.';
                    } else if (xhr.status === 404) {
                        mensajeError = 'La ruta para generar la encuesta no fue encontrada.';
                    } else if (xhr.status === 500) {
                        mensajeError = 'Error interno del servidor al generar la encuesta.';
                    }
                    
                    mostrarAlerta({
                        icon: 'error',
                        title: 'Error',
                        text: mensajeError,
                        footer: 'Código de error: ' + (xhr.status || 'desconocido')
                    });
                }
            });
        });

        // Función que se ha movido a la vista de declarantes

        // Función para mostrar los declarantes en la tabla
        // La función para mostrar declarantes se ha movido a la vista de declarantes

        // Función para mostrar errores
        function mostrarError(mensaje) {
            $('#errorMessage').text(mensaje);
            $('#declarantesLoading').hide();
            $('#declarantesError').show();
        }

        // Limpiar el modal cuando se cierre
        $('#declarantesModal').on('hidden.bs.modal', function () {
            $('#declarantesLoading').hide();
            $('#declarantesContent').hide();
            $('#declarantesError').hide();
            $('#declarantesTableBody').empty();
            limpiarFormulario();
        });

        // Manejar el envío del formulario de declarante
        $('#declaranteForm').on('submit', function(e) {
            e.preventDefault();
            
            // Crear FormData para manejar archivos
            var formData = new FormData();
            formData.append('nombre', $('#nombre').val().trim());
            formData.append('ruc', $('#ruc').val().trim());
            
            // Agregar archivo si existe
            var archivoFirma = $('#firma')[0].files[0];
            if (archivoFirma) {
                formData.append('firma', archivoFirma);
            }

            var declaranteId = $('#declaranteId').val();
            var url = declaranteId ? 
                '{{ route("pedidos.declarantes.update", ":id") }}'.replace(':id', declaranteId) : 
                '{{ route("pedidos.declarantes.store") }}';
            
            // Para PUT requests, necesitamos usar _method
            if (declaranteId) {
                formData.append('_method', 'PUT');
            }

            // Limpiar errores previos
            $('.form-control, .custom-file-input').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $('#submitButton').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: url,
                method: 'POST', // Siempre POST para FormData
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        limpiarFormulario();
                        cargarDeclarantes();
                    } else {
                        mostrarErroresFormulario(response.errors || {});
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        mostrarErroresFormulario(xhr.responseJSON.errors || {});
                    } else {
                        mostrarAlerta({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al procesar la solicitud'
                        });
                    }
                },
                complete: function() {
                    $('#submitButton').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                }
            });
        });

        // Manejar cancelar edición
        $('#cancelEditButton').on('click', function() {
            limpiarFormulario();
        });

        // Manejar eliminación de declarantes (usando delegación de eventos)
        $(document).on('click', '.btn-eliminar-declarante', function() {
            var button = $(this);
            var id = button.data('id');
            var nombre = button.data('nombre');

            mostrarConfirmacion({
                title: '¿Está seguro?',
                text: `¿Desea eliminar al declarante "${nombre}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarDeclarante(id);
                }
            });
        });

        // Función para eliminar declarante
        function eliminarDeclarante(id) {
            $.ajax({
                url: '{{ route("pedidos.declarantes.destroy", ":id") }}'.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        cargarDeclarantes();
                    } else {
                        mostrarAlerta({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al eliminar el declarante'
                        });
                    }
                },
                error: function(xhr) {
                    mostrarAlerta({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al eliminar el declarante'
                    });
                }
            });
        }

        // Manejar el botón de mostrar facturas
        $(document).on('click', '.btn-mostrar-facturas', function() {
            // Redireccionar a la página de facturas
            window.location.href = '{{ route("facturas.index") }}';
            
            // Mostrar modal
            $('#facturasDeclaranteModal').modal('show');
            
            // Cargar facturas
            cargarFacturasDeclarante(id);
        });

        // Función para cargar las facturas de un declarante
        function cargarFacturasDeclarante(declaranteId) {
            // Mostrar loading
            $('#facturasLoading').show();
            $('#totalesFacturas').hide();
            $('#tablaFacturasContainer').hide();
            $('#noFacturasMessage').hide();
            $('#errorFacturas').hide();

            $.ajax({
                url: '/pedidos/declarantes/' + declaranteId + '/facturas',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        mostrarFacturasDeclarante(response);
                    } else {
                        mostrarErrorFacturas(response.message || 'Error al cargar las facturas');
                    }
                },
                error: function(xhr) {
                    var mensaje = 'Error al cargar las facturas';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    mostrarErrorFacturas(mensaje);
                },
                complete: function() {
                    $('#facturasLoading').hide();
                }
            });
        }

        // Función para mostrar las facturas en el modal
        function mostrarFacturasDeclarante(data) {
            var declarante = data.declarante;
            var facturas = data.facturas;
            var totales = data.totales;

            // Llenar información del declarante
            $('#infoRucDeclarante').text(declarante.ruc || 'N/A');
            $('#infoCantidadFacturas').text(totales.cantidad_facturas);

            // Mostrar totales
            $('#totalBaseFacturas').text('$' + parseFloat(totales.total_base).toFixed(2));
            $('#totalIvaFacturas').text('$' + parseFloat(totales.total_iva).toFixed(2));
            $('#totalDebitoFiscal').text('$' + parseFloat(totales.total_iva).toFixed(2)); // IVA Débito Fiscal = solo el IVA
            $('#totalFacturadoFacturas').text('$' + parseFloat(totales.total_facturado).toFixed(2)); // Total facturado (Base + IVA)
            $('#cantidadTotalFacturas').text(totales.cantidad_facturas);

            if (facturas.length === 0) {
                $('#noFacturasMessage').show();
            } else {
                // Llenar tabla de facturas
                var tbody = $('#facturasTbody');
                tbody.empty();

                $.each(facturas, function(index, factura) {
                    var xmlCell = factura.xml ? 
                        `<span class="badge badge-success" title="${factura.xml}">
                            <i class="fas fa-file-code"></i> XML
                        </span>` : 
                        `<span class="badge badge-secondary">Sin XML</span>`;

                    var tipoClass = factura.tipo.toLowerCase() === 'factura' ? 'badge-primary' : 'badge-info';

                    var fila = `
                        <tr>
                            <td>${factura.id}</td>
                            <td>${factura.fecha}</td>
                            <td>${factura.numero_orden}</td>
                            <td>${factura.cliente}</td>
                            <td><span class="badge ${tipoClass}">${factura.tipo}</span></td>
                            <td class="text-right">$${parseFloat(factura.monto).toFixed(2)}</td>
                            <td class="text-right">$${parseFloat(factura.iva).toFixed(2)}</td>
                            <td class="text-right font-weight-bold">$${parseFloat(factura.total).toFixed(2)}</td>
                            <td class="text-center">${xmlCell}</td>
                        </tr>
                    `;
                    tbody.append(fila);
                });

                $('#totalesFacturas').show();
                $('#tablaFacturasContainer').show();
            }
        }

        // Función para mostrar error al cargar facturas
        function mostrarErrorFacturas(mensaje) {
            $('#errorFacturasMessage').text(mensaje);
            $('#errorFacturas').show();
        }

        // Limpiar modal de facturas al cerrarlo
        $('#facturasDeclaranteModal').on('hidden.bs.modal', function() {
            $('#facturasTbody').empty();
            $('#totalesFacturas').hide();
            $('#tablaFacturasContainer').hide();
            $('#noFacturasMessage').hide();
            $('#errorFacturas').hide();
            $('#facturasLoading').hide();
        });

        // Función para limpiar el formulario
        function limpiarFormulario() {
            $('#declaranteForm')[0].reset();
            $('#declaranteId').val('');
            $('#formTitle').text('Agregar Nuevo Declarante');
            $('#submitButton').html('<i class="fas fa-save"></i> Guardar');
            $('#cancelEditButton').hide();
            $('.form-control, .custom-file-input').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('.custom-file-label').text('Seleccionar certificado...');
            $('#firmaPreview').hide();
            $('#firmaActual').hide();
        }

        // Función para mostrar errores del formulario
        function mostrarErroresFormulario(errors) {
            $.each(errors, function(campo, mensajes) {
                var input = $('#' + campo);
                if (input.length) {
                    input.addClass('is-invalid');
                    input.siblings('.invalid-feedback').text(mensajes[0] || mensajes);
                }
            });
        }

        // Manejar el cambio de archivo de certificado
        $('#firma').on('change', function() {
            var file = this.files[0];
            var label = $(this).next('.custom-file-label');
            var preview = $('#firmaPreview');
            var fileName = $('#firmaFileName');
            
            if (file) {
                // Validar extensión
                var extension = file.name.split('.').pop().toLowerCase();
                if (!['p12', 'pem'].includes(extension)) {
                    mostrarAlerta({
                        icon: 'error',
                        title: 'Formato no válido',
                        text: 'Solo se permiten archivos de certificados digitales (.p12 o .pem)',
                    });
                    $(this).val('');
                    label.text('Seleccionar certificado...');
                    return;
                }
                
                // Validar tamaño (máximo 5MB para certificados)
                if (file.size > 5 * 1024 * 1024) {
                    mostrarAlerta({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: 'El certificado no puede ser mayor a 5MB',
                    });
                    $(this).val('');
                    label.text('Seleccionar certificado...');
                    return;
                }
                
                label.text(file.name);
                fileName.text(file.name);
                preview.show();
            } else {
                label.text('Seleccionar certificado...');
                preview.hide();
            }
        });

        // Manejar el botón de eliminar certificado
        $('#removeFirma').on('click', function() {
            $('#firma').val('');
            $('.custom-file-label').text('Seleccionar certificado...');
            $('#firmaPreview').hide();
        });

        // Función para mostrar información del certificado
        window.mostrarCertificado = function(url, nombre, extension) {
            var iconClass = extension === 'p12' ? 'fa-certificate text-primary' : 'fa-key text-success';
            var certType = extension === 'p12' ? 'Certificado P12' : 'Certificado PEM';
            
            mostrarAlerta({
                title: `Certificado de ${nombre}`,
                html: `
                    <div class="text-center">
                        <i class="fas ${iconClass} fa-4x mb-3"></i>
                        <h5>${certType}</h5>
                        <p class="text-muted">Certificado digital para firma electrónica</p>
                        <a href="${url}" download class="btn btn-primary">
                            <i class="fas fa-download"></i> Descargar Certificado
                        </a>
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: false,
                width: '400px'
            });
        };

        // Manejar la edición de declarantes para mostrar firma actual
        $(document).on('click', '.btn-editar-declarante', function() {
            var button = $(this);
            var id = button.data('id');
            var nombre = button.data('nombre');
            var ruc = button.data('ruc');
            var firma = button.data('firma');

            // Llenar el formulario
            $('#declaranteId').val(id);
            $('#nombre').val(nombre);
            $('#ruc').val(ruc);

            // Mostrar certificado actual si existe
            if (firma) {
                var extension = firma.split('.').pop().toLowerCase();
                $('#firmaActualName').text(firma);
                $('#firmaActual').show();
            } else {
                $('#firmaActual').hide();
            }

            // Cambiar el título y botón
            $('#formTitle').text('Editar Declarante');
            $('#submitButton').html('<i class="fas fa-save"></i> Actualizar');
            $('#cancelEditButton').show();

            // Scroll al formulario
            $('#declaranteForm')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Manejar el botón de crear factura
        $(document).on('click', '.btn-crear-factura', function() {
            // Obtener el ID del pedido del botón
            var pedidoId = $(this).data('pedido-id');
            
            // Redireccionar a la página de creación de facturas
            // Si hay un pedidoId, lo pasamos como parámetro
            if (pedidoId) {
                window.location.href = '{{ route("facturas.create") }}?pedido_id=' + pedidoId;
            } else {
                window.location.href = '{{ route("facturas.create") }}';
            }
        });

        // Función para cargar los detalles del pedido con cálculos
        function cargarDetallesPedido(pedidoId) {
            // Mostrar loading
            $('#detallesLoading').show();
            $('#detallesProductos').hide();
            
            $.ajax({
                url: '/pedidos/' + pedidoId + '/detalles-factura',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        mostrarDetallesPedido(response.detalles);
                        
                        // Llenar los campos automáticamente
                        var totales = response.detalles.totales;
                        $('#montoFactura').val(totales.base_total.toFixed(2));
                        $('#ivaFactura').val(totales.iva_total.toFixed(2));
                        
                        // Ocultar loading y mostrar detalles
                        $('#detallesLoading').hide();
                        $('#detallesProductos').show();
                    } else {
                        mostrarAlerta({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al cargar los detalles del pedido'
                        });
                        $('#detallesLoading').hide();
                    }
                },
                error: function(xhr) {
                    var mensaje = 'Error al cargar los detalles del pedido';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    mostrarAlerta({
                        icon: 'error',
                        title: 'Error',
                        text: mensaje
                    });
                    $('#detallesLoading').hide();
                }
            });
        }

        // Función para mostrar los detalles en las tablas
        function mostrarDetallesPedido(detalles) {
            // Limpiar tablas
            $('#tablaInventarios').empty();
            $('#tablaLunas').empty();
            
            var totalBaseInventarios = 0;
            var totalIvaInventarios = 0;
            var totalBaseLunas = 0;
            var totalIvaLunas = 0;

            // Mostrar inventarios si existen
            if (detalles.inventarios && detalles.inventarios.length > 0) {
                $('#cardInventarios').show();
                
                $.each(detalles.inventarios, function(index, item) {
                    var fila = `
                        <tr>
                            <td>${item.codigo}</td>
                            <td>$${item.precio_original.toFixed(2)}</td>
                            <td>${item.descuento}%</td>
                            <td>$${item.precio_con_descuento.toFixed(2)}</td>
                            <td>$${item.base.toFixed(2)}</td>
                            <td>$${item.iva.toFixed(2)}</td>
                        </tr>
                    `;
                    $('#tablaInventarios').append(fila);
                    
                    totalBaseInventarios += item.base;
                    totalIvaInventarios += item.iva;
                });
                
                $('#subtotalBaseInventarios').text('$' + totalBaseInventarios.toFixed(2));
                $('#subtotalIvaInventarios').text('$' + totalIvaInventarios.toFixed(2));
            } else {
                $('#cardInventarios').hide();
            }

            // Mostrar lunas si existen
            if (detalles.lunas && detalles.lunas.length > 0) {
                $('#cardLunas').show();
                
                $.each(detalles.lunas, function(index, item) {
                    var fila = `
                        <tr>
                            <td>${item.medida || 'N/A'}</td>
                            <td>${item.tipo_lente || 'N/A'}</td>
                            <td>${item.material || 'N/A'}</td>
                            <td>$${item.precio_original.toFixed(2)}</td>
                            <td>${item.descuento}%</td>
                            <td>$${item.precio_con_descuento.toFixed(2)}</td>
                            <td>$${item.base.toFixed(2)}</td>
                            <td>$${item.iva.toFixed(2)}</td>
                        </tr>
                    `;
                    $('#tablaLunas').append(fila);
                    
                    totalBaseLunas += item.base;
                    totalIvaLunas += item.iva;
                });
                
                $('#subtotalBaseLunas').text('$' + totalBaseLunas.toFixed(2));
                $('#subtotalIvaLunas').text('$' + totalIvaLunas.toFixed(2));
            } else {
                $('#cardLunas').hide();
            }

            // Actualizar totales generales
            var totalBase = totalBaseInventarios + totalBaseLunas;
            var totalIva = totalIvaInventarios + totalIvaLunas;
            var montoTotal = totalBase + totalIva;

            $('#totalBaseCalculado').text('$' + totalBase.toFixed(2));
            $('#totalIvaCalculado').text('$' + totalIva.toFixed(2));
            $('#montoTotalCalculado').text('$' + montoTotal.toFixed(2));
        }

        // Función para cargar declarantes en el select
        function cargarDeclarantesSelect() {
            var select = $('#declaranteSelect');
            select.html('<option value="">Cargando declarantes...</option>');

            $.ajax({
                url: '{{ route("pedidos.declarantes.listar") }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.data) {
                        select.html('<option value="">Seleccione un declarante...</option>');
                        $.each(response.data, function(index, declarante) {
                            select.append(`<option value="${declarante.id}">${declarante.nombre} - ${declarante.ruc}</option>`);
                        });
                    } else {
                        select.html('<option value="">No hay declarantes disponibles</option>');
                    }
                },
                error: function(xhr) {
                    select.html('<option value="">Error al cargar declarantes</option>');
                    console.error('Error al cargar declarantes:', xhr);
                }
            });
        }

        // Manejar el guardado de la factura
        $('#guardarFacturaBtn').on('click', function() {
            var button = $(this);
            var form = $('#crearFacturaForm');

            // Validar formulario
            if (!validarFormularioFactura()) {
                return;
            }

            // Preparar datos
            var formData = {
                pedido_id: $('#factPedidoId').val(),
                declarante_id: $('#declaranteSelect').val(),
                tipo: $('#tipoFactura').val(),
                monto: $('#montoFactura').val(),
                iva: $('#ivaFactura').val(),
                xml: $('#xmlRuta').val()
            };

            // Deshabilitar botón
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

            // Enviar petición
            $.ajax({
                url: '{{ route("pedidos.crear-factura") }}',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta({
                            icon: 'success',
                            title: '¡Factura Creada!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        });

                        // Cerrar modal
                        $('#crearFacturaModal').modal('hide');

                        // Recargar la página para reflejar los cambios
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        mostrarErroresFormularioFactura(response.errors || {});
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        mostrarErroresFormularioFactura(xhr.responseJSON.errors || {});
                    } else {
                        mostrarAlerta({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al crear la factura'
                        });
                    }
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-save"></i> Crear Factura');
                }
            });
        });

        // Función para validar formulario de factura
        function validarFormularioFactura() {
            var valid = true;
            var campos = [
                { id: 'declaranteSelect', mensaje: 'Debe seleccionar un declarante' },
                { id: 'tipoFactura', mensaje: 'Debe seleccionar un tipo de documento' },
                { id: 'montoFactura', mensaje: 'Debe ingresar el monto' },
                { id: 'ivaFactura', mensaje: 'Debe ingresar el IVA' }
            ];

            // Limpiar errores previos
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $.each(campos, function(index, campo) {
                var input = $('#' + campo.id);
                if (!input.val() || input.val().trim() === '') {
                    input.addClass('is-invalid');
                    input.siblings('.invalid-feedback').text(campo.mensaje);
                    valid = false;
                }
            });

            // Validar que el monto y IVA sean números positivos
            var monto = parseFloat($('#montoFactura').val());
            var iva = parseFloat($('#ivaFactura').val());

            if (isNaN(monto) || monto <= 0) {
                $('#montoFactura').addClass('is-invalid');
                $('#montoFactura').siblings('.invalid-feedback').text('El monto debe ser mayor a 0');
                valid = false;
            }

            if (isNaN(iva) || iva < 0) {
                $('#ivaFactura').addClass('is-invalid');
                $('#ivaFactura').siblings('.invalid-feedback').text('El IVA debe ser mayor o igual a 0');
                valid = false;
            }

            return valid;
        }

        // Función para mostrar errores del formulario de factura
        function mostrarErroresFormularioFactura(errors) {
            $.each(errors, function(campo, mensajes) {
                var input = $('#' + campo) || $('#' + campo + 'Factura') || $('#' + campo + 'Select');
                if (input.length) {
                    input.addClass('is-invalid');
                    input.siblings('.invalid-feedback').text(mensajes[0] || mensajes);
                }
            });
        }

        // Calcular IVA automáticamente cuando cambie el monto
        $('#montoFactura').on('input', function() {
            var monto = parseFloat($(this).val()) || 0;
            var iva = (monto * 0.12).toFixed(2);
            $('#ivaFactura').val(iva);
        });

        // Limpiar modal al cerrarlo
        $('#crearFacturaModal').on('hidden.bs.modal', function() {
            $('#crearFacturaForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#declaranteSelect').html('<option value="">Seleccione un declarante...</option>');
            
            // Limpiar tablas y secciones de detalles
            $('#tablaInventarios').empty();
            $('#tablaLunas').empty();
            $('#detallesProductos').hide();
            $('#detallesLoading').hide();
            $('#cardInventarios').hide();
            $('#cardLunas').hide();
            
            // Limpiar totales
            $('#subtotalBaseInventarios').text('$0.00');
            $('#subtotalIvaInventarios').text('$0.00');
            $('#subtotalBaseLunas').text('$0.00');
            $('#subtotalIvaLunas').text('$0.00');
            $('#totalBaseCalculado').text('$0.00');
            $('#totalIvaCalculado').text('$0.00');
            $('#montoTotalCalculado').text('$0.00');
        });
    });
</script>
@stop