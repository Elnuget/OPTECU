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
                        <span class="info-box-number">${{ number_format($totales['ventas'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Saldos</span>
                        <span class="info-box-number">${{ number_format($totales['saldos'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cobrado</span>
                        <span class="info-box-number">${{ number_format($totales['cobrado'], 0, ',', '.') }}</span>
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
                <label for="empresa_id">SUCURSAL:</label>
                <select name="empresa_id" class="form-control" id="empresa_id">
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
            <div class="col-md-5 align-self-end">
                <button type="button" class="btn btn-info" id="actualButton">ACTUAL</button>
                <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
            </div>
        </form>

        {{-- Botones de acción --}}
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="btn-group">
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Crear Pedido</a>
                    <button type="button" class="btn btn-success" id="generarExcel" disabled>
                        <i class="fas fa-file-excel"></i> Generar Excel
                    </button>
                    <button type="button" class="btn btn-warning" id="imprimirEtiquetas" disabled>
                        <i class="fas fa-tags"></i> Imprimir Etiquetas
                    </button>
                    <button type="button" class="btn btn-info" id="imprimirCristaleria" disabled>
                        <i class="fas fa-eye"></i> Imprimir Cristalería
                    </button>
                    <button type="button" class="btn btn-secondary" id="imprimirInforme" disabled>
                        <i class="fas fa-print"></i> Imprimir Informe
                    </button>
                    <button type="button" class="btn btn-danger" id="filtrarReclamos">
                        <i class="fas fa-exclamation-triangle"></i> Ver Reclamos
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="date" class="form-control" id="fechaSeleccion" value="{{ request('fecha_especifica', date('Y-m-d')) }}">
                    <div class="input-group-append">
                        @if(request()->filled('fecha_especifica'))
                            <button type="button" class="btn btn-danger" id="seleccionarDiarios">
                                <i class="fas fa-filter"></i> 
                                @if(request()->filled('empresa_id'))
                                    Filtros Activos ({{ $pedidos->count() }})
                                @else
                                    Filtro Fecha ({{ $pedidos->count() }})
                                @endif
                            </button>
                            <button type="button" class="btn btn-secondary" id="limpiarFiltroFecha">
                                <i class="fas fa-times"></i> Limpiar Filtro Fecha
                            </button>
                        @else
                            <button type="button" class="btn btn-warning" id="seleccionarDiarios">
                                <i class="fas fa-calendar-day"></i> Filtrar por Fecha
                            </button>
                            <button type="button" class="btn btn-secondary" id="limpiarFiltroFecha" style="display: none;">
                                <i class="fas fa-times"></i> Limpiar Filtro
                            </button>
                        @endif
                    </div>
                </div>
                @if(request()->filled('fecha_especifica'))
                    <small class="text-info">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando pedidos del {{ \Carbon\Carbon::parse(request('fecha_especifica'))->format('d/m/Y') }}
                        @if(request()->filled('empresa_id'))
                            @php
                                $empresaSeleccionada = $empresas->firstWhere('id', request('empresa_id'));
                            @endphp
                            @if($empresaSeleccionada)
                                en <strong>{{ strtoupper($empresaSeleccionada->nombre) }}</strong>
                            @endif
                        @endif
                    </small>
                @endif
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
                        <th>Sucursal</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Usuario</th>
                        <th>Tipo de Lente</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                    <tr class="{{ !is_null($pedido->reclamo) && trim($pedido->reclamo) !== '' ? 'bg-danger-light reclamo-row' : '' }}">
                        <td class="checkbox-cell">
                            <input type="checkbox" name="pedidos_selected[]" value="{{ $pedido->id }}" class="pedido-checkbox">
                        </td>
                        <td>
                            <div>{{ $pedido->fecha ? $pedido->fecha->format('Y-m-d') : 'Sin fecha' }}</div>
                            @if($pedido->fecha_entrega)
                                <small class="text-info"><strong>Entrega:</strong><br>{{ $pedido->fecha_entrega->format('Y-m-d') }}</small>
                            @endif
                        </td>
                        <td>{{ $pedido->empresa ? strtoupper($pedido->empresa->nombre) : 'SIN EMPRESA' }}</td>
                        <td>{{ $pedido->numero_orden }}</td>
                        <td>
                            <span style="color: 
                                {{ $pedido->fact == 'Pendiente' ? 'orange' : 
                                  ($pedido->fact == 'CRISTALERIA' ? 'darkblue' : 
                                   ($pedido->fact == 'Separado' ? 'brown' : 
                                    ($pedido->fact == 'LISTO EN TALLER' ? 'blue' : 
                                     ($pedido->fact == 'Enviado' ? 'purple' : 
                                      ($pedido->fact == 'ENTREGADO' ? 'green' : 'black'))))) }}">
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
                        <td>{{ $pedido->usuario ? strtoupper($pedido->usuario) : 'SIN USUARIO' }}</td>
                        <td>
                            @if($pedido->lunas->count() > 0)
                                {{ strtoupper($pedido->lunas->first()->tipo_lente ?: 'NO ESPECIFICADO') }}
                                @if($pedido->lunas->count() > 1)
                                    <small class="text-info"><br>(+{{ $pedido->lunas->count() - 1 }} más)</small>
                                @endif
                            @else
                                <span class="text-muted">SIN LUNAS</span>
                            @endif
                        </td>
                        <td>${{ number_format($pedido->total, 0, ',', '.') }}</td>
                        <td>
                            <span style="color: {{ $pedido->saldo == 0 ? 'green' : 'red' }}">
                                ${{ number_format($pedido->saldo, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <!-- Grupo de acciones principales -->
                                <div class="btn-group me-1" role="group">
                                    <a href="{{ route('pedidos.show', $pedido->id) }}"
                                        class="btn btn-outline-info btn-sm" 
                                        title="Ver Detalles del Pedido"
                                        data-toggle="tooltip">
                                        <i class="fas fa-eye me-1"></i>
                                        <span class="d-none d-md-inline">Ver</span>
                                    </a>
                                    <a href="{{ route('pedidos.edit', $pedido->id) }}"
                                        class="btn btn-outline-primary btn-sm" 
                                        title="Editar Pedido"
                                        data-toggle="tooltip">
                                        <i class="fas fa-edit me-1"></i>
                                        <span class="d-none d-md-inline">Editar</span>
                                    </a>
                                </div>

                                <!-- Grupo de acciones financieras -->
                                <div class="btn-group me-1" role="group">
                                    <a href="{{ route('pagos.create', ['pedido_id' => $pedido->id]) }}"
                                        class="btn btn-success btn-sm" 
                                        title="Registrar Pago"
                                        data-toggle="tooltip">
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        <span class="d-none d-lg-inline">Pago</span>
                                    </a>
                                </div>
                                
                                <!-- Botones de cambio de estado -->
                                <div class="me-1">
                                    @if($pedido->fact == 'Pendiente')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'cristaleria']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-secondary btn-sm estado-btn" 
                                                title="Cambiar a Estado: Cristalería"
                                                data-toggle="tooltip">
                                                <i class="fas fa-glasses me-1"></i>
                                                <span class="d-none d-xl-inline">Cristalería</span>
                                            </button>
                                        </form>
                                    @elseif($pedido->fact == 'CRISTALERIA')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'separado']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-secondary btn-sm estado-btn" 
                                                title="Cambiar a Estado: Separado"
                                                data-toggle="tooltip">
                                                <i class="fas fa-hand-paper me-1"></i>
                                                <span class="d-none d-xl-inline">Separar</span>
                                            </button>
                                        </form>
                                    @elseif($pedido->fact == 'Separado')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'taller']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-primary btn-sm estado-btn" 
                                                title="Cambiar a Estado: Listo en Taller"
                                                data-toggle="tooltip">
                                                <i class="fas fa-tools me-1"></i>
                                                <span class="d-none d-xl-inline">Taller</span>
                                            </button>
                                        </form>
                                    @elseif($pedido->fact == 'LISTO EN TALLER')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'enviado']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-info btn-sm estado-btn" 
                                                title="Cambiar a Estado: Enviado"
                                                data-toggle="tooltip">
                                                <i class="fas fa-shipping-fast me-1"></i>
                                                <span class="d-none d-xl-inline">Enviar</span>
                                            </button>
                                        </form>
                                    @elseif($pedido->fact == 'Enviado')
                                        <form action="{{ route('pedidos.update-state', ['id' => $pedido->id, 'state' => 'entregado']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success btn-sm estado-btn" 
                                                title="Cambiar a Estado: Entregado"
                                                data-toggle="tooltip">
                                                <i class="fas fa-check-double me-1"></i>
                                                <span class="d-none d-xl-inline">Entregar</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Botón de eliminar (solo admin) -->
                                @can('admin')
                                    <div class="me-1">
                                        <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            data-toggle="modal"
                                            data-target="#confirmarEliminarModal" 
                                            data-id="{{ $pedido->id }}"
                                            data-url="{{ route('pedidos.destroy', $pedido->id) }}"
                                            title="Eliminar Pedido"
                                            data-toggle="tooltip">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                @endcan

                                <!-- Botón de Reclamo -->
                                <div class="me-1">
                                    @if(is_null($pedido->reclamo) || trim($pedido->reclamo) === '')
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-reclamo" 
                                            title="Agregar Reclamo del Cliente" 
                                            data-pedido-id="{{ $pedido->id }}"
                                            data-cliente="{{ $pedido->cliente }}"
                                            data-toggle="tooltip">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <span class="d-none d-lg-inline">Reclamo</span>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-warning btn-sm btn-quitar-reclamo" 
                                            title="Quitar Reclamo Existente" 
                                            data-pedido-id="{{ $pedido->id }}"
                                            data-cliente="{{ $pedido->cliente }}"
                                            data-toggle="tooltip">
                                            <i class="fas fa-times-circle me-1"></i>
                                            <span class="d-none d-lg-inline">Quitar</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br />
    </div>
</div>

{{-- Modal de confirmación de eliminación --}}
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

{{-- Modal para agregar reclamo --}}
<div class="modal fade" id="reclamoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Reclamo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="reclamoForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cliente-reclamo"><strong>Cliente:</strong></label>
                        <p id="cliente-reclamo" class="form-control-plaintext"></p>
                    </div>
                    <div class="form-group">
                        <label for="reclamo"><strong>Descripción del Reclamo:</strong></label>
                        <textarea 
                            id="reclamo" 
                            name="reclamo" 
                            class="form-control" 
                            rows="5" 
                            placeholder="Describa detalladamente el reclamo del cliente..."
                            maxlength="1000"
                            required></textarea>
                        <small class="form-text text-muted">
                            Mínimo 10 caracteres, máximo 1000 caracteres. 
                            <span id="contador-caracteres">0/1000</span>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Guardar Reclamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mb-3">
    <a href="{{ route('pedidos.inventario-historial') }}" class="btn btn-info">
        Ver Historial de Inventario
    </a>
</div>

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
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

/* Estilos para filas con reclamos */
.reclamo-row {
    background-color: #f8d7da !important; /* Fondo rojo claro */
    border-left: 4px solid #dc3545 !important; /* Borde izquierdo rojo más fuerte */
}

.reclamo-row:hover {
    background-color: #f5c6cb !important; /* Fondo un poco más oscuro al hacer hover */
}

.bg-danger-light {
    background-color: #f8d7da !important;
}

/* Asegurar que el texto sea legible en las filas con reclamo */
.reclamo-row td {
    color: #721c24 !important;
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

/* Estilos adicionales para el botón de reclamo */
.btn-reclamo {
    transition: all 0.2s ease;
}

.btn-reclamo:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Estilos para el botón de quitar reclamo */
.btn-quitar-reclamo {
    transition: all 0.2s ease;
}

.btn-quitar-reclamo:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Estilos para el modal de reclamo */
#reclamoModal .modal-content {
    border-radius: 8px;
}

#reclamoModal .modal-header {
    background-color: #dc3545;
    color: white;
}

#reclamoModal .modal-header .close {
    color: white;
    opacity: 1;
}

#reclamoModal .modal-header .close:hover {
    opacity: 0.8;
}

#reclamo {
    resize: vertical;
    min-height: 120px;
}

/* Estilos para el contador de caracteres del reclamo */
#contador-caracteres {
    font-weight: bold;
}

/* Estilos mejorados para los botones de acciones */
.btn-group .btn {
    border-radius: 0.25rem !important;
    margin-right: 2px;
    transition: all 0.2s ease-in-out;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

/* Botones de estado con animación */
.estado-btn {
    position: relative;
    transition: all 0.3s ease;
}

.estado-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.estado-btn:active {
    transform: translateY(0);
}

/* Mejoras para botones outline */
.btn-outline-info:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Responsive para botones de acciones */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        border-radius: 0.25rem !important;
    }
    
    .d-flex.flex-wrap {
        flex-direction: column !important;
        gap: 5px !important;
    }
}

/* Estilos para tooltips */
.tooltip {
    font-size: 0.875rem;
}

/* Espaciado mejorado para acciones */
.gap-1 {
    gap: 0.25rem !important;
}

/* Iconos con mejor espaciado */
.me-1 {
    margin-right: 0.25rem !important;
}

/* Botones con tamaño consistente */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

/* Mejora para el contenedor de acciones */
.d-flex.flex-wrap {
    min-height: 40px;
    align-items: center;
}

/* Estilo especial para botones de estado activos */
.estado-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.estado-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Estilos para el botón de filtrar reclamos */
#filtrarReclamos {
    transition: all 0.3s ease;
    position: relative;
}

#filtrarReclamos.active {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
}

#filtrarReclamos:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

#filtrarReclamos.active::after {
    content: " (Activo)";
    font-size: 0.8em;
}

/* Mejora visual para botones agrupados */
.btn-group .btn:not(:last-child) {
    border-right: 1px solid rgba(0,0,0,0.1);
}

/* Colores específicos para cada tipo de acción */
.btn-outline-info {
    border-color: #17a2b8;
    color: #17a2b8;
}

.btn-outline-primary {
    border-color: #007bff;
    color: #007bff;
}

/* Estilo para el botón de pago */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}
</style>
@endpush
@stop
@section('js')
@include('atajos')
@parent
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function () {
        // Inicializar tooltips de Bootstrap
        $('[data-toggle="tooltip"]').tooltip({
            placement: 'top',
            trigger: 'hover'
        });

        // Verificar si hay filtro de fecha activo y mostrar/ocultar botón de limpiar filtro
        @if(request()->filled('fecha_especifica'))
            $('#limpiarFiltroFecha').show();
        @else
            $('#limpiarFiltroFecha').hide();
        @endif

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
            $('#generarExcel').prop('disabled', checkedCheckboxes === 0);
            $('#imprimirEtiquetas').prop('disabled', checkedCheckboxes === 0);
            $('#imprimirCristaleria').prop('disabled', checkedCheckboxes === 0);
            $('#imprimirInforme').prop('disabled', checkedCheckboxes === 0);
        }

        // Manejar clic en el botón de filtrar por fecha - ENVIAR AL SERVIDOR
        $('#seleccionarDiarios').click(function() {
            var fechaSeleccionada = $('#fechaSeleccion').val();
            
            if (!fechaSeleccionada) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha Requerida',
                    text: 'Por favor seleccione una fecha para filtrar'
                });
                return;
            }
            
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Filtrando pedidos...',
                text: 'Cargando pedidos del ' + fechaSeleccionada,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Construir URL con filtro de fecha y mantener filtro de sucursal si existe
            const params = new URLSearchParams();
            params.set('fecha_especifica', fechaSeleccionada);
            
            // Mantener el filtro de empresa/sucursal si está seleccionado
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            // Redirigir al servidor con los filtros combinados
            window.location.href = '{{ route("pedidos.index") }}?' + params.toString();
        });

        // Manejar clic en el botón de limpiar filtro de fecha - REDIRIGIR AL SERVIDOR
        $('#limpiarFiltroFecha').click(function() {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Limpiando filtro...',
                text: 'Volviendo a mostrar pedidos por mes',
                allowOutsideClick: false,
                timer: 1000,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Obtener parámetros actuales y remover solo la fecha específica
            var currentParams = new URLSearchParams(window.location.search);
            currentParams.delete('fecha_especifica'); // Remover el filtro de fecha específica
            
            var newUrl = '{{ route("pedidos.index") }}';
            
            // Si hay otros parámetros (como sucursal), mantenerlos
            if (currentParams.toString()) {
                newUrl += '?' + currentParams.toString();
            } else {
                // Si no hay otros parámetros, ir al mes actual pero mantener sucursal si existe
                const params = new URLSearchParams();
                const now = new Date();
                params.set('ano', now.getFullYear());
                params.set('mes', now.getMonth() + 1);
                
                // Mantener el filtro de empresa/sucursal si está seleccionado
                if ($('#empresa_id').val()) {
                    params.set('empresa_id', $('#empresa_id').val());
                }
                
                newUrl += '?' + params.toString();
            }
            
            window.location.href = newUrl;
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

        // Manejar clic en el botón de imprimir informe
        $('#imprimirInforme').click(function() {
            var selectedIds = [];
            $('.pedido-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Por favor seleccione al menos un pedido para imprimir el informe');
                return;
            }
            
            // Crear formulario para envío GET (usar la ruta existente)
            var form = $('<form>', {
                'method': 'GET',
                'action': '{{ route("pedidos.print") }}',
                'target': '_blank'
            });
            
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

        // Manejar clic en el botón de imprimir etiquetas (antigua funcionalidad de generar excel)
        $('#imprimirEtiquetas').click(function() {
            var selectedIds = [];
            $('.pedido-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Por favor seleccione al menos un pedido para imprimir etiquetas');
                return;
            }
            
            // Crear formulario para envío POST
            var form = $('<form>', {
                'method': 'POST',
                'action': '{{ route("pedidos.print.etiquetas") }}',
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

        // Manejar clic en el botón de generar Excel (usar la función del PedidosController)
        $('#generarExcel').click(function() {
            var selectedIds = [];
            $('.pedido-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Por favor seleccione al menos un pedido para generar Excel');
                return;
            }
            
            // Crear formulario para envío POST usando la ruta downloadExcel del PedidosController
            var form = $('<form>', {
                'method': 'POST',
                'action': '{{ route("pedidos.download.excel") }}',
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
            "order": [[3, "desc"]], // Ordenar por número de orden descendente (ahora es la columna 3)
            "paging": false, // Deshabilitar paginación
            "lengthChange": false,
            "info": false,
            "dom": 'frt', // Quitar 'p' del dom para eliminar controles de paginación y 'B' para quitar botones
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
                "search": "Buscar:"
            }
        });

        // Manejar cambios en los filtros - Filtrado automático
        $('#filtroAno, #filtroMes, #empresa_id').change(function() {
            const params = new URLSearchParams();
            
            // Mantener el filtro de fecha específica si está activo
            var currentParams = new URLSearchParams(window.location.search);
            if (currentParams.has('fecha_especifica')) {
                params.set('fecha_especifica', currentParams.get('fecha_especifica'));
            } else {
                // Solo aplicar filtros de año y mes si no hay fecha específica
                if ($('#filtroAno').val()) params.set('ano', $('#filtroAno').val());
                if ($('#filtroMes').val()) params.set('mes', $('#filtroMes').val());
                
                // Si no hay año ni mes, agregar parámetro "todos"
                if (!$('#filtroAno').val() && !$('#filtroMes').val()) {
                    params.set('todos', '1');
                }
            }
            
            // Siempre agregar el filtro de empresa si está seleccionado
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            window.location.href = '{{ route("pedidos.index") }}?' + params.toString();
        });

        // Botón "Actual"
        $('#actualButton').click(function() {
            const now = new Date();
            const params = new URLSearchParams();
            params.set('ano', now.getFullYear());
            params.set('mes', now.getMonth() + 1);
            
            // Mantener el filtro de empresa si existe
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            window.location.href = '{{ route("pedidos.index") }}?' + params.toString();
        });

        // Botón "Mostrar Todos los Pedidos"
        $('#mostrarTodosButton').click(function() {
            const params = new URLSearchParams();
            params.set('todos', '1');
            
            // Mantener el filtro de empresa si existe
            if ($('#empresa_id').val()) {
                params.set('empresa_id', $('#empresa_id').val());
            }
            
            window.location.href = '{{ route("pedidos.index") }}?' + params.toString();
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

        // Manejar el cambio de estado de pedidos
        $(document).on('submit', 'form[action*="update-state"]', function(e) {
            e.preventDefault();
            var form = $(this);
            var button = form.find('button[type="submit"]');
            var originalText = button.html();
            var originalTitle = button.attr('title');
            
            // Agregar clase de loading y deshabilitar el botón
            button.addClass('loading')
                  .prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin me-1"></i><span class="d-none d-xl-inline">Procesando...</span>')
                  .attr('title', 'Procesando cambio de estado...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Cambiar a estado de éxito temporalmente
                    button.removeClass('loading')
                          .html('<i class="fas fa-check me-1"></i><span class="d-none d-xl-inline">¡Éxito!</span>')
                          .attr('title', 'Estado actualizado correctamente');
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Estado Actualizado!',
                        text: 'El estado del pedido se ha actualizado correctamente.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    }).then(() => {
                        // Recargar la página para mostrar los cambios
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    let errorMessage = 'Error al actualizar el estado del pedido';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 419) {
                        errorMessage = 'Sesión expirada. Por favor, recarga la página e intenta nuevamente.';
                    }
                    
                    // Mostrar estado de error temporalmente
                    button.removeClass('loading')
                          .html('<i class="fas fa-exclamation-triangle me-1"></i><span class="d-none d-xl-inline">Error</span>')
                          .attr('title', 'Error al procesar');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        timer: 4000
                    });
                    
                    // Restaurar el botón después de un breve delay
                    setTimeout(() => {
                        button.prop('disabled', false)
                              .html(originalText)
                              .attr('title', originalTitle);
                    }, 2000);
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

        // Manejar el modal de reclamos
        $('.btn-reclamo').click(function() {
            var pedidoId = $(this).data('pedido-id');
            var cliente = $(this).data('cliente');
            
            // Configurar el modal
            $('#cliente-reclamo').text(cliente);
            $('#reclamoForm').data('pedido-id', pedidoId);
            $('#reclamo').val('');
            $('#contador-caracteres').text('0/1000');
            
            // Mostrar el modal
            $('#reclamoModal').modal('show');
        });

        // Manejar el botón de quitar reclamo
        $('.btn-quitar-reclamo').click(function() {
            var pedidoId = $(this).data('pedido-id');
            var cliente = $(this).data('cliente');
            
            // Confirmar la eliminación del reclamo
            Swal.fire({
                title: '¿Quitar Reclamo?',
                html: `¿Está seguro que desea quitar el reclamo del pedido de <strong>${cliente}</strong>?<br><br><small class="text-warning">Esta acción no se puede deshacer.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, quitar reclamo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceder a eliminar el reclamo
                    $.ajax({
                        url: '/pedidos/' + pedidoId + '/quitar-reclamo',
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Reclamo Eliminado!',
                                    text: 'El reclamo se ha eliminado correctamente.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Recargar la página para actualizar la vista
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error al eliminar el reclamo';
                            
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage
                            });
                        }
                    });
                }
            });
        });

        // Contador de caracteres para el textarea del reclamo
        $('#reclamo').on('input', function() {
            var length = $(this).val().length;
            $('#contador-caracteres').text(length + '/1000');
            
            // Cambiar color si se acerca al límite
            if (length > 900) {
                $('#contador-caracteres').addClass('text-danger').removeClass('text-muted');
            } else {
                $('#contador-caracteres').addClass('text-muted').removeClass('text-danger');
            }
        });

        // Manejar el envío del formulario de reclamo
        $('#reclamoForm').on('submit', function(e) {
            e.preventDefault();
            var pedidoId = $(this).data('pedido-id');
            var reclamo = $('#reclamo').val().trim();
            
            if (reclamo.length < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Descripción insuficiente',
                    text: 'El reclamo debe tener al menos 10 caracteres.'
                });
                return;
            }
            
            // Enviar el reclamo al servidor
            $.ajax({
                url: '/pedidos/' + pedidoId + '/reclamo',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    reclamo: reclamo
                },
                success: function(response) {
                    $('#reclamoModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Reclamo Registrado!',
                        text: 'El reclamo se ha guardado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recargar la página para mostrar los cambios
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Error al guardar el reclamo';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        });

        // Variables para el filtro de reclamos
        var filtroReclamosActivo = false;
        var todasLasFilas = [];
        
        // Guardar todas las filas al cargar la página
        $(document).ready(function() {
            todasLasFilas = $('#pedidosTable tbody tr').toArray();
        });

        // Manejar el botón de filtrar reclamos
        $('#filtrarReclamos').click(function() {
            var button = $(this);
            var tabla = $('#pedidosTable tbody');
            
            if (!filtroReclamosActivo) {
                // Activar filtro de reclamos
                filtroReclamosActivo = true;
                
                // Cambiar estado visual del botón
                button.addClass('active')
                      .html('<i class="fas fa-eye-slash"></i> Ocultar Reclamos')
                      .attr('title', 'Ocultar pedidos con reclamos y mostrar todos');
                
                // Ocultar todas las filas que NO tienen reclamos
                tabla.find('tr').each(function() {
                    var fila = $(this);
                    var tieneReclamo = fila.hasClass('reclamo-row') || fila.hasClass('bg-danger-light');
                    
                    if (!tieneReclamo) {
                        fila.hide();
                    }
                });
                
                // Verificar si hay reclamos para mostrar
                var filasConReclamo = tabla.find('tr.reclamo-row:visible, tr.bg-danger-light:visible').length;
                
                if (filasConReclamo === 0) {
                    // Si no hay reclamos, mostrar mensaje
                    var mensajeNoReclamos = '<tr id="no-reclamos-message"><td colspan="12" class="text-center text-muted py-4">' +
                                          '<i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>' +
                                          '<strong>¡Excelente!</strong><br>' +
                                          'No hay pedidos con reclamos en este momento.' +
                                          '</td></tr>';
                    tabla.append(mensajeNoReclamos);
                }
                
                // Mostrar notificación
                Swal.fire({
                    icon: filasConReclamo > 0 ? 'info' : 'success',
                    title: filasConReclamo > 0 ? 'Mostrando Reclamos' : '¡Sin Reclamos!',
                    text: filasConReclamo > 0 ? 
                          `Se encontraron ${filasConReclamo} pedido(s) con reclamos.` : 
                          'No hay pedidos con reclamos actualmente.',
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
                
            } else {
                // Desactivar filtro de reclamos
                filtroReclamosActivo = false;
                
                // Cambiar estado visual del botón
                button.removeClass('active')
                      .html('<i class="fas fa-exclamation-triangle"></i> Ver Reclamos')
                      .attr('title', 'Mostrar solo pedidos con reclamos');
                
                // Remover mensaje de "no reclamos" si existe
                $('#no-reclamos-message').remove();
                
                // Mostrar todas las filas originales
                tabla.find('tr').show();
                
                // Mostrar notificación
                Swal.fire({
                    icon: 'info',
                    title: 'Mostrando Todos',
                    text: 'Se están mostrando todos los pedidos nuevamente.',
                    timer: 2000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            }
            
            // Actualizar el contador de DataTables si existe
            if (pedidosTable && pedidosTable.page) {
                pedidosTable.draw(false);
            }
        });

        // Asegurar que el filtro se mantenga después de operaciones de DataTables
        if (typeof pedidosTable !== 'undefined') {
            pedidosTable.on('draw', function() {
                if (filtroReclamosActivo) {
                    // Reaplica el filtro después de un redraw de DataTables
                    setTimeout(function() {
                        $('#pedidosTable tbody tr').each(function() {
                            var fila = $(this);
                            var tieneReclamo = fila.hasClass('reclamo-row') || fila.hasClass('bg-danger-light');
                            
                            if (!tieneReclamo && fila.attr('id') !== 'no-reclamos-message') {
                                fila.hide();
                            }
                        });
                    }, 100);
                }
            });
        }
        $('#reclamoForm').on('submit', function(e) {
            e.preventDefault();
            
            var pedidoId = $(this).data('pedido-id');
            var reclamo = $('#reclamo').val().trim();
            var submitButton = $(this).find('button[type="submit"]');
            var originalText = submitButton.text();
            
            // Validación del lado del cliente
            if (reclamo.length < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Reclamo muy corto',
                    text: 'El reclamo debe tener al menos 10 caracteres'
                });
                return;
            }
            
            // Deshabilitar botón durante el envío
            submitButton.prop('disabled', true).text('Guardando...');
            
            $.ajax({
                url: '/pedidos/' + pedidoId + '/agregar-reclamo',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    reclamo: reclamo
                },
                success: function(response) {
                    if (response.success) {
                        $('#reclamoModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Reclamo Guardado!',
                            text: 'El reclamo se ha registrado correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Recargar la página para actualizar la vista
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al guardar el reclamo';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors && xhr.responseJSON.errors.reclamo) {
                            errorMessage = xhr.responseJSON.errors.reclamo[0];
                        }
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                },
                complete: function() {
                    // Rehabilitar botón
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        });
    });
</script>
@stop