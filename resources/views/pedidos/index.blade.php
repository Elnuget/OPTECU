@extends('adminlte::page')
@section('title', 'Pedidos')

@section('content_header')
<h1>Pedidos</h1>
<p>Administracion de ventas</p>
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <label for="filtroAno">SELECCIONAR AÑO:</label>
                <select name="ano" class="form-control" id="filtroAno">
                    <option value="">SELECCIONE AÑO</option>
                    @for ($year = date('Y'); $year >= 2000; $year--)
                        <option value="{{ $year }}" {{ request('ano', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroMes">SELECCIONAR MES:</label>
                <select name="mes" class="form-control custom-select" id="filtroMes">
                    <option value="">SELECCIONE MES</option>
                    @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                        <option value="{{ $index + 1 }}" {{ request('mes') == ($index + 1) ? 'selected' : '' }}>{{ strtoupper($month) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="empresa_id">EMPRESA:</label>
                <select name="empresa_id" class="form-control" id="empresa_id" {{ !$isUserAdmin && $userEmpresaId ? 'disabled' : '' }}>
                    <option value="">TODAS LAS EMPRESAS</option>
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
            <div class="col-md-5 align-self-end">
                <button type="submit" class="btn btn-primary mr-2">FILTRAR</button>
                <button type="button" class="btn btn-info" id="actualButton">ACTUAL</button>
                <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
            </div>
        </form>

        {{-- Botones de acción --}}
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="btn-group">
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Crear Pedido</a>
                    <button type="button" class="btn btn-success" id="imprimirSeleccionados" disabled>
                        <i class="fas fa-print"></i> Imprimir Seleccionados
                    </button>
                    <button type="button" class="btn btn-warning" id="generarExcel" disabled>
                        <i class="fas fa-file-excel"></i> Generar Excel
                    </button>
                    <button type="button" class="btn btn-info" id="imprimirCristaleria" disabled>
                        <i class="fas fa-eye"></i> Imprimir Cristalería
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="date" class="form-control" id="fechaSeleccion" value="{{ date('Y-m-d') }}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-warning" id="seleccionarDiarios">
                            <i class="fas fa-calendar-day"></i> Seleccionar Diarios
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtro por mes (removed) --}}
        <!-- Previously here, now removed -->

        <div class="table-responsive">
            <table id="pedidosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Fecha</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Paciente</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Acciones</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                    <tr>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="pedidos_selected[]" value="{{ $pedido->id }}" class="pedido-checkbox">
                        </td>
                        <td>{{ $pedido->fecha ? $pedido->fecha->format('Y-m-d') : 'Sin fecha' }}</td>
                        <td>{{ $pedido->numero_orden }}</td>
                        <td>
                            <span style="color: 
                                {{ $pedido->fact == 'Pendiente' ? 'orange' : 
                                  ($pedido->fact == 'LISTO EN TALLER' ? 'blue' : 
                                   ($pedido->fact == 'LISTO PARA ENTREGA' ? 'purple' : 
                                    ($pedido->fact == 'ENTREGADO' ? 'green' : 'black'))) }}">
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
                                
                                <!-- Botones de cambio de estado -->
                                @can('admin')
                                    @if(strtoupper($pedido->fact) == 'PENDIENTE')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'taller']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-primary btn-sm" title="Marcar como Listo en Taller">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                        </form>
                                    @elseif(strtoupper($pedido->fact) == 'LISTO EN TALLER')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'entrega']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-info btn-sm" title="Marcar como Listo para Entrega">
                                                <i class="fas fa-box"></i>
                                            </button>
                                        </form>
                                    @elseif(strtoupper($pedido->fact) == 'LISTO PARA ENTREGA')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'entregado']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-warning btn-sm" title="Marcar como Entregado">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
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

/* Estilos para los checkboxes */
input[type="checkbox"] {
    width: 16px !important;
    height: 16px !important;
    margin: 0 !important;
    cursor: pointer !important;
    position: relative !important;
    display: inline-block !important;
}

input[type="checkbox"]:before,
input[type="checkbox"]:after {
    display: none !important;
}

.checkbox-cell {
    text-align: center !important;
    vertical-align: middle !important;
    width: 50px !important;
}
</style>
@endpush
@stop
@section('js')
@include('atajos')
@parent
<script>
    $(document).ready(function () {
        // Manejar el checkbox "Seleccionar todos"
        $('#selectAll').change(function() {
            $('.pedido-checkbox').prop('checked', this.checked);
            toggleImprimirButton();
        });

        // Si se deselecciona algún checkbox individual, deseleccionar el "Seleccionar todos"
        $(document).on('change', '.pedido-checkbox', function() {
            if (!this.checked) {
                $('#selectAll').prop('checked', false);
            } else {
                // Si todos están seleccionados, marcar el "Seleccionar todos"
                var totalCheckboxes = $('.pedido-checkbox').length;
                var checkedCheckboxes = $('.pedido-checkbox:checked').length;
                $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            }
            toggleImprimirButton();
        });

        // Función para habilitar/deshabilitar el botón de imprimir
        function toggleImprimirButton() {
            var checkedCheckboxes = $('.pedido-checkbox:checked').length;
            $('#imprimirSeleccionados').prop('disabled', checkedCheckboxes === 0);
            $('#generarExcel').prop('disabled', checkedCheckboxes === 0);
            $('#imprimirCristaleria').prop('disabled', checkedCheckboxes === 0);
        }

        // Manejar clic en el botón de seleccionar diarios
        $('#seleccionarDiarios').click(function() {
            var fechaSeleccionada = $('#fechaSeleccion').val();
            
            if (!fechaSeleccionada) {
                alert('Por favor seleccione una fecha');
                return;
            }
            
            // Desmarcar todos los checkboxes primero
            $('.pedido-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            
            var pedidosSeleccionados = 0;
            
            // Recorrer todas las filas de la tabla
            $('#pedidosTable tbody tr').each(function() {
                var fila = $(this);
                var fechaPedido = fila.find('td:nth-child(2)').text().trim(); // Columna de fecha (índice 2)
                
                // Comparar fechas
                if (fechaPedido === fechaSeleccionada) {
                    fila.find('.pedido-checkbox').prop('checked', true);
                    pedidosSeleccionados++;
                }
            });
            
            // Actualizar estado de los botones
            toggleImprimirButton();
            
            // Mostrar mensaje informativo
            if (pedidosSeleccionados > 0) {
                alert('Se seleccionaron ' + pedidosSeleccionados + ' pedidos de la fecha ' + fechaSeleccionada);
            } else {
                alert('No se encontraron pedidos para la fecha ' + fechaSeleccionada);
            }
        });

        // Manejar clic en el botón de imprimir
        $('#imprimirSeleccionados').click(function() {
            var selectedIds = [];
            $('.pedido-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Por favor seleccione al menos un pedido para imprimir');
                return;
            }
            
            // Crear formulario para envío POST
            var form = $('<form>', {
                'method': 'POST',
                'action': '{{ route("pedidos.print.post") }}',
                'target': '_blank'
            });
            
            // Agregar token CSRF
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));
            
            // Agregar IDs seleccionados
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'ids',
                'value': selectedIds.join(',')
            }));
            
            // Establecer formato de tabla por defecto
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'format',
                'value': 'table'
            }));
            
            // Agregar al body y enviar
            $('body').append(form);
            form.submit();
            form.remove();
        });

        // Manejar clic en el botón de imprimir cristalería
        $('#imprimirCristaleria').click(function() {
            var selectedIds = [];
            $('.pedido-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Por favor seleccione al menos un pedido para imprimir cristalería');
                return;
            }
            
            // Crear formulario para envío POST
            var form = $('<form>', {
                'method': 'POST',
                'action': '{{ route("pedidos.print.cristaleria") }}',
                'target': '_blank'
            });
            
            // Agregar token CSRF
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));
            
            // Agregar IDs seleccionados
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'ids',
                'value': selectedIds.join(',')
            }));
            
            // Agregar al body y enviar
            $('body').append(form);
            form.submit();
            form.remove();
        });

        // Manejar clic en el botón de generar Excel
        $('#generarExcel').click(function() {
            var selectedIds = [];
            $('.pedido-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Por favor seleccione al menos un pedido para generar Excel');
                return;
            }
            
            // Crear formulario para envío POST
            var form = $('<form>', {
                'method': 'POST',
                'action': '{{ route("pedidos.print.excel") }}',
                'target': '_blank'
            });
            
            // Agregar token CSRF
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));
            
            // Agregar IDs seleccionados
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'ids',
                'value': selectedIds.join(',')
            }));
            
            // Agregar al body y enviar
            $('body').append(form);
            form.submit();
            form.remove();
        });

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
            "order": [[2, "desc"]], // Ordenar por número de orden descendente (ahora es la columna 2)
            "paging": false, // Deshabilitar paginación
            "lengthChange": false,
            "info": false,
            "dom": 'Bfrt', // Quitar 'p' del dom para eliminar controles de paginación
            "buttons": [
                {
                    extend: 'excel',
                    text: 'Excel',
                    exportOptions: {
                        columns: [1,2,3,4,5,6,7,8,10] // Excluir la columna de checkbox (0) y acciones (9)
                    },
                    filename: 'Pedidos_' + new Date().toISOString().split('T')[0]
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    exportOptions: {
                        columns: [1,2,3,4,5,6,7,8,10] // Excluir la columna de checkbox (0) y acciones (9)
                    },
                    filename: 'Pedidos_' + new Date().toISOString().split('T')[0],
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
                "search": "Buscar:"
            },
            "initComplete": function(settings, json) {
                // Ocultar el indicador de "processing" después de la carga inicial
                $(this).DataTable().processing(false);
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
            // No cambiamos el filtro de empresa, mantenemos el valor actual
            $('#filterForm').submit();
        });

        // Botón "Mostrar Todos los Pedidos"
        $('#mostrarTodosButton').click(function() {
            $('#filtroAno').val('');
            $('#filtroMes').val('');
            
            // Solo limpiamos el filtro de empresa si el usuario es administrador o no tiene empresa asignada
            @if($isUserAdmin || !$userEmpresaId)
                $('#empresa_id').val('');
            @endif
            
            const form = $('#filterForm');
            form.append('<input type="hidden" name="todos" value="1">');
            form.submit();
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

        // Función para detectar si es dispositivo móvil
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Función para limpiar y formatear número de teléfono chileno
        function formatChileanPhone(phone) {
            // Remover todos los caracteres no numéricos
            let cleanPhone = phone.replace(/\D/g, '');
            
            // Si empieza con 56 (código de Chile), mantenerlo
            if (cleanPhone.startsWith('56')) {
                return cleanPhone;
            }
            
            // Si empieza con 9 (celular chileno), agregar código de país
            if (cleanPhone.startsWith('9') && cleanPhone.length === 9) {
                return '56' + cleanPhone;
            }
            
            // Si tiene 8 dígitos, asumir que falta el 9 inicial
            if (cleanPhone.length === 8) {
                return '569' + cleanPhone;
            }
            
            // Si no cumple ningún patrón, devolver tal como está para validación posterior
            return cleanPhone;
        }

        // Función para generar URL de WhatsApp más segura
        function generateWhatsAppURL(phoneNumber, message) {
            const formattedPhone = formatChileanPhone(phoneNumber);
            const encodedMessage = encodeURIComponent(message);
            
            if (isMobileDevice()) {
                // Para móviles, usar el esquema whatsapp://
                return `whatsapp://send?phone=${formattedPhone}&text=${encodedMessage}`;
            } else {
                // Para escritorio, usar WhatsApp Web con api.whatsapp.com (más confiable)
                return `https://api.whatsapp.com/send?phone=${formattedPhone}&text=${encodedMessage}`;
            }
        }

        // Manejar el envío del mensaje de WhatsApp con encuesta
        $('.btn-whatsapp-mensaje').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var pedidoId = button.data('pedido-id');
            var celular = button.data('celular');
            var cliente = button.data('cliente');
            var estadoActual = button.data('estado-actual');

            // Validar número de teléfono
            if (!celular || celular.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se encontró un número de teléfono válido para este cliente.'
                });
                return;
            }

            // Deshabilitar botón temporalmente para evitar múltiples clics
            button.prop('disabled', true);

            // Primero obtener la URL de la encuesta y actualizar estado
            $.ajax({
                url: '/pedidos/' + pedidoId + '/enviar-encuesta',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Crear mensaje personalizado para Chile
                        var mensajeCompleto = `¡Hola ${cliente}! 👋

¡Excelentes noticias! Sus lentes ya están listos para ser retirados en nuestra óptica. �✨

� *Detalles del pedido:*
• Orden: ${response.numero_orden || 'N/A'}
• Estado: ${response.estado || 'Listo para retiro'}

🏪 Puede pasar a recogerlos en el horario que más le convenga.

🔗 *Califica nuestro servicio:*
${response.url}

Su opinión es muy importante para nosotros. 

¡Que tenga un excelente día!`;

                        // Generar URL de WhatsApp optimizada
                        const whatsappURL = generateWhatsAppURL(celular, mensajeCompleto);
                        
                        // Abrir WhatsApp
                        const whatsappWindow = window.open(whatsappURL, '_blank');
                        
                        // Verificar si se abrió correctamente y ofrecer alternativa
                        setTimeout(() => {
                            if (!whatsappWindow || whatsappWindow.closed) {
                                // Si no se abrió, intentar con URL alternativa
                                const alternativeURL = `https://web.whatsapp.com/send?phone=${formatChileanPhone(celular)}&text=${encodeURIComponent(mensajeCompleto)}`;
                                window.open(alternativeURL, '_blank');
                            }
                        }, 1000);

                        // Actualizar el estado visual del botón solo si fue exitoso
                        button.removeClass('btn-success').addClass('btn-warning');
                        button.attr('title', 'Volver a enviar mensaje y encuesta');
                        button.find('.button-text').text('Volver a enviar');
                        button.data('estado-actual', 'enviado');

                        // Mostrar mensaje de confirmación
                        Swal.fire({
                            icon: 'success',
                            title: '¡WhatsApp Abierto!',
                            text: 'Se ha abierto WhatsApp con el mensaje y enlace de encuesta.',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al generar el enlace de encuesta';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                },
                complete: function() {
                    // Rehabilitar botón
                    button.prop('disabled', false);
                }
            });
        });
    });
</script>
@stop