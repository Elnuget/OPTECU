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
            <div class="col-md-4 align-self-end">
                <button type="button" class="btn btn-primary" id="actualButton">Actual</button>
                <button type="button" class="btn btn-success" id="mostrarTodosButton">Mostrar Todos los Pedidos</button>
            </div>
        </form>

        {{-- Botones de acción --}}
        <div class="btn-group mb-3">
            <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Crear Pedido</a>
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
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('pedidos.show', $pedido->id) }}"
                                    class="btn btn-xs btn-default text-primary mx-1 shadow" title="Ver">
                                    <i class="fa fa-lg fa-fw fa-eye"></i>
                                </a>
                                @can('admin')
                                    <a href="{{ route('pedidos.edit', $pedido->id) }}"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </a>
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
                                <!-- Botón de Aprobar -->
                                @can('admin')
                                    @if(strtoupper($pedido->fact) == 'PENDIENTE')
                                        <form action="{{ route('pedidos.approve', $pedido->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-warning btn-sm" title="Aprobar">
                                                <i class="fas fa-check"></i>
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
</style>
@endpush
@stop
@section('js')
@include('atajos')
@parent
<script>
    $(document).ready(function () {
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
            $('#filterForm').submit();
        });

        // Botón "Mostrar Todos los Pedidos"
        $('#mostrarTodosButton').click(function() {
            $('#filtroAno').val('');
            $('#filtroMes').val('');
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

        // Manejar el envío del mensaje de WhatsApp con encuesta
        $('.btn-whatsapp-mensaje').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var pedidoId = button.data('pedido-id');
            var celular = button.data('celular');
            var cliente = button.data('cliente');
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
                        // Construir mensaje con saludo personalizado al cliente
                        var mensajeSaludo = "Estimado(a) cliente " + cliente + ",";
                        var mensajeLentes = "Le informamos que sus lentes recetados ya están listos para ser recogidos en ESCLERÓPTICA. Puede pasar a retirarlos cuando le sea más conveniente. ¡Lo esperamos pronto!";
                        
                        // El enlace debe ir por separado, para que WhatsApp lo reconozca como clicable
                        var mensajeEncuesta = "\n\nNos gustaría conocer su opinión. Por favor, complete nuestra breve encuesta de satisfacción:";
                        
                        // Crear el mensaje completo con texto amigable para el enlace
                        var mensajeCompleto = encodeURIComponent(
                            mensajeSaludo + "\n\n" + 
                            mensajeLentes + "\n" + 
                            mensajeEncuesta + "\n\n" + 
                            "Copia y pega el siguiente enlace en tu navegador para completar la encuesta:" + "\n" + 
                            response.url
                        );
                        
                        // Abrir WhatsApp con el mensaje completo
                        window.open("https://api.whatsapp.com/send?phone=593" + celular + "&text=" + mensajeCompleto, '_blank');

                        // Actualizar el estado visual del botón solo si fue exitoso
                        button.removeClass('btn-success').addClass('btn-warning');
                        button.attr('title', 'Volver a enviar mensaje y encuesta');
                        button.find('.button-text').text('Volver a enviar');
                        button.data('estado-actual', 'enviado');
                    }
                },
                error: function(xhr) {
                    alert('Error al generar el enlace de la encuesta: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido'));
                }
            });
        });
    });
</script>
@stop