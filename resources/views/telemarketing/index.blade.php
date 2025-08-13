@extends('adminlte::page')

@section('title', 'TELEMARKETING')

@php
use App\Models\MensajePredeterminado;
@endphp

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>TELEMARKETING - GESTIÓN DE CLIENTES</h1>
    </div>
</div>
@if (session('error'))
<div class="alert {{ session('tipo', 'alert-danger') }} alert-dismissible fade show" role="alert">
    <strong>{{ strtoupper(session('error')) }}</strong>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">TODOS LOS CLIENTES Y PACIENTES</h3>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editarMensajeModal">
            <i class="fas fa-edit"></i> EDITAR MENSAJE PREDETERMINADO
        </button>
    </div>
    <div class="card-body">
        {{-- Filtros --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-filter"></i> FILTROS DE BÚSQUEDA</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filtroForm">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="empresa_id" class="form-label">SUCURSAL:</label>
                                    <select name="empresa_id" id="empresa_id" class="form-control">
                                        <option value="">TODAS LAS SUCURSALES</option>
                                        @foreach($empresas ?? [] as $empresa)
                                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                                {{ strtoupper($empresa->nombre) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tipo_cliente" class="form-label">TIPO DE CLIENTE:</label>
                                    <select name="tipo_cliente" id="tipo_cliente" class="form-control">
                                        <option value="">TODOS</option>
                                        <option value="cliente" {{ request('tipo_cliente') == 'cliente' ? 'selected' : '' }}>SOLO CLIENTES</option>
                                        <option value="paciente" {{ request('tipo_cliente') == 'paciente' ? 'selected' : '' }}>SOLO PACIENTES</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_inicio" class="form-label">FECHA INICIO:</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                                    <small class="text-muted">Desde esta fecha</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_fin" class="form-label">FECHA FIN:</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                                    <small class="text-muted">Hasta esta fecha</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <div class="btn-group w-100" role="group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> FILTRAR
                                        </button>
                                        <button type="button" class="btn btn-success" id="mostrarTodosButton">
                                            <i class="fas fa-list"></i> TODOS
                                        </button>
                                        <button type="button" class="btn btn-info" id="limpiarFiltrosButton">
                                            <i class="fas fa-eraser"></i> LIMPIAR
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Filtros rápidos de fecha -->
                            <div class="row">
                                <div class="col-md-12">
                                    <small class="text-muted"><strong>FILTROS RÁPIDOS:</strong></small>
                                    <div class="btn-toolbar mt-2" role="toolbar">
                                        <div class="btn-group btn-group-sm mr-2" role="group">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('hoy')"
                                                    data-toggle="tooltip" 
                                                    title="Filtrar registros de hoy únicamente">
                                                HOY
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('ayer')"
                                                    data-toggle="tooltip" 
                                                    title="Filtrar registros de ayer únicamente">
                                                AYER
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('semana')"
                                                    data-toggle="tooltip" 
                                                    title="Desde el lunes de esta semana hasta hoy">
                                                ESTA SEMANA
                                            </button>
                                        </div>
                                        <div class="btn-group btn-group-sm mr-2" role="group">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('mes')"
                                                    data-toggle="tooltip" 
                                                    title="Desde el 1ro del mes actual hasta hoy">
                                                ESTE MES
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('mes_anterior')"
                                                    data-toggle="tooltip" 
                                                    title="Todo el mes anterior completo">
                                                MES ANTERIOR
                                            </button>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('trimestre')"
                                                    data-toggle="tooltip" 
                                                    title="Desde el inicio del trimestre actual hasta hoy">
                                                TRIMESTRE
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="setFechaRapida('año')"
                                                    data-toggle="tooltip" 
                                                    title="Desde el 1 de enero hasta hoy">
                                                ESTE AÑO
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i> 
                                            <strong>Los filtros rápidos establecen las fechas automáticamente. 
                                            Luego haga clic en "FILTRAR" para aplicar el filtro.</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Indicador de resultados y filtros activos --}}
        @if(request()->hasAny(['empresa_id', 'tipo_cliente', 'fecha_inicio', 'fecha_fin']))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-filter"></i> <strong>FILTROS ACTIVOS:</strong>
                    @if(request('empresa_id'))
                        <span class="badge badge-info ml-1">
                            SUCURSAL: {{ strtoupper($empresas->firstWhere('id', request('empresa_id'))->nombre ?? 'DESCONOCIDA') }}
                        </span>
                    @endif
                    @if(request('tipo_cliente'))
                        <span class="badge badge-primary ml-1">
                            TIPO: {{ strtoupper(request('tipo_cliente')) }}
                        </span>
                    @endif
                    @if(request('fecha_inicio'))
                        <span class="badge badge-success ml-1">
                            DESDE: {{ \Carbon\Carbon::parse(request('fecha_inicio'))->format('d/m/Y') }}
                        </span>
                    @endif
                    @if(request('fecha_fin'))
                        <span class="badge badge-warning ml-1">
                            HASTA: {{ \Carbon\Carbon::parse(request('fecha_fin'))->format('d/m/Y') }}
                        </span>
                    @endif
                    <span class="float-right">
                        <strong>{{ $clientes->count() }} resultado(s) encontrado(s)</strong>
                    </span>
                </div>
            </div>
        </div>
        @endif

        @if($clientes->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                @if(request()->hasAny(['empresa_id', 'tipo_cliente', 'fecha_inicio', 'fecha_fin']))
                    NO SE ENCONTRARON CLIENTES CON LOS FILTROS APLICADOS. 
                    <a href="{{ route('telemarketing.index') }}" class="btn btn-sm btn-primary ml-2">
                        <i class="fas fa-refresh"></i> VER TODOS
                    </a>
                @else
                    NO HAY CLIENTES REGISTRADOS.
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table id="clientesTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>APELLIDOS</th>
                            <th>TELÉFONO</th>
                            <th>TIPO</th>
                            <th>EMPRESA</th>
                            <th>ÚLTIMO PEDIDO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        <tr>
                            <td>{{ strtoupper($cliente->nombre) }}</td>
                            <td>{{ strtoupper($cliente->apellidos ?? '') }}</td>
                            <td>
                                @if($cliente->celular)
                                    <span class="badge badge-success">{{ $cliente->celular }}</span>
                                @else
                                    <span class="badge badge-secondary">SIN TELÉFONO</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $cliente->tipo == 'cliente' ? 'badge-primary' : 'badge-info' }}">
                                    {{ strtoupper($cliente->tipo) }}
                                </span>
                            </td>
                            <td>{{ $cliente->empresa ? strtoupper($cliente->empresa->nombre) : 'SIN EMPRESA' }}</td>
                            <td>
                                @if($cliente->ultimo_pedido)
                                    <small class="text-muted">{{ $cliente->ultimo_pedido->format('d/m/Y') }}</small>
                                @else
                                    <small class="text-muted">NUNCA</small>
                                @endif
                            </td>
                            <td>
                            @if($cliente->celular)
                                @if($cliente->ultimo_mensaje)
                                    {{-- Si ya se envió un mensaje antes --}}
                                    <div class="btn-group-vertical" style="width: 100%;">
                                        <button type="button" 
                                                class="btn btn-info btn-sm mb-1"
                                                data-toggle="tooltip" 
                                                title="Último mensaje: {{ $cliente->ultimo_mensaje->mensaje }}"
                                                style="font-size: 0.75rem;">
                                            <i class="fas fa-clock"></i> ÚLTIMO MENSAJE: {{ $cliente->ultimo_mensaje->fecha_envio->format('d/m/Y H:i') }}
                                        </button>
                                        <button type="button" 
                                                class="btn btn-warning btn-sm btn-enviar-mensaje"
                                                data-cliente-id="{{ $cliente->id }}"
                                                data-nombre="{{ $cliente->nombre }}"
                                                data-apellidos="{{ $cliente->apellidos ?? '' }}"
                                                data-celular="{{ $cliente->celular }}"
                                                data-tipo="{{ $cliente->tipo }}"
                                                onclick="mostrarModalMensaje('{{ $cliente->id }}', '{{ $cliente->nombre }}', '{{ $cliente->apellidos ?? '' }}', '{{ $cliente->tipo }}')">
                                            <i class="fab fa-whatsapp"></i> VOLVER A ENVIAR
                                        </button>
                                    </div>
                                @else
                                    {{-- Si no se ha enviado ningún mensaje --}}
                                    <button type="button" 
                                            class="btn btn-success btn-sm btn-enviar-mensaje mr-1"
                                            data-cliente-id="{{ $cliente->id }}"
                                            data-nombre="{{ $cliente->nombre }}"
                                            data-apellidos="{{ $cliente->apellidos ?? '' }}"
                                            data-celular="{{ $cliente->celular }}"
                                            data-tipo="{{ $cliente->tipo }}"
                                            onclick="mostrarModalMensaje('{{ $cliente->id }}', '{{ $cliente->nombre }}', '{{ $cliente->apellidos ?? '' }}', '{{ $cliente->tipo }}')">
                                        <i class="fab fa-whatsapp"></i> ENVIAR MENSAJE
                                    </button>
                                @endif
                            @endif
                                <button type="button" 
                                        class="btn btn-info btn-sm"
                                        onclick="mostrarHistorial('{{ $cliente->id }}', '{{ $cliente->nombre }}', '{{ $cliente->apellidos ?? '' }}', '{{ $cliente->tipo }}')">
                                    <i class="fas fa-history"></i> VER HISTORIAL
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal para editar mensaje predeterminado -->
<div class="modal fade" id="editarMensajeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">EDITAR MENSAJE PREDETERMINADO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="mensajePredeterminadoForm">
                    <div class="form-group">
                        <label for="mensajePredeterminado">MENSAJE PREDETERMINADO:</label>
                        <textarea class="form-control" id="mensajePredeterminado" name="mensaje" rows="10" placeholder="Escriba aquí el mensaje predeterminado...">¡Hola [NOMBRE]! 👋

Esperamos que se encuentre muy bien.

Desde [EMPRESA] queremos mantener el contacto con nuestros estimados clientes y ofrecerle nuestros servicios de óptica.

🔸 Contamos con:
• Lentes de contacto
• Marcos y cristales
• Exámenes de vista
• Reparaciones

💰 Tenemos excelentes promociones y descuentos especiales.

¿Le gustaría conocer más sobre nuestros servicios?

¡Estamos aquí para ayudarle! 👓✨

Saludos cordiales,
El equipo de [EMPRESA]</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-primary" onclick="guardarMensajePredeterminado()">GUARDAR MENSAJE</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para enviar mensaje -->
<div class="modal fade" id="enviarMensajeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ENVIAR MENSAJE DE TELEMARKETING</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="enviarMensajeForm">
                    <input type="hidden" id="clienteId" name="cliente_id">
                    <div class="form-group">
                        <label><strong>CLIENTE:</strong></label>
                        <p id="nombreCliente"></p>
                    </div>
                    <div class="form-group">
                        <label for="mensajePersonalizado">MENSAJE:</label>
                        <textarea class="form-control" id="mensajePersonalizado" name="mensaje" rows="10"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-success" onclick="enviarMensaje()">
                    <i class="fab fa-whatsapp"></i> ENVIAR POR WHATSAPP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver historial -->
<div class="modal fade" id="historialModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">HISTORIAL DEL CLIENTE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="historialLoader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>CARGANDO HISTORIAL...</p>
                </div>
                <div id="historialContent" style="display: none;">
                    <h4 id="clienteNombre" class="mb-3"></h4>
                    
                    <!-- Pestañas para Pedidos e Historiales Clínicos -->
                    <ul class="nav nav-tabs" id="historialTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pedidos-tab" data-toggle="tab" href="#pedidos" role="tab">
                                <i class="fas fa-shopping-cart"></i> PEDIDOS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="historiales-tab" data-toggle="tab" href="#historiales" role="tab">
                                <i class="fas fa-file-medical"></i> HISTORIALES CLÍNICOS
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="historialTabContent">
                        <!-- Tab de Pedidos -->
                        <div class="tab-pane fade show active" id="pedidos" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table id="pedidosHistorialTable" class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="display: none;">ID</th>
                                            <th>FECHA</th>
                                            <th>ORDEN</th>
                                            <th>ESTADO</th>
                                            <th>TOTAL</th>
                                            <th>SALDO</th>
                                            <th>ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pedidosHistorialBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab de Historiales Clínicos -->
                        <div class="tab-pane fade" id="historiales" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table id="historialesClinicoTable" class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="display: none;">ID</th>
                                            <th>FECHA</th>
                                            <th>PRÓXIMA CONSULTA</th>
                                            <th>USUARIO</th>
                                            <th>ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="historialesClinicoBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    .table th, .table td {
        text-transform: uppercase !important;
    }
    .badge {
        padding: 8px 12px;
    }
    .badge-primary {
        background-color: #007bff;
        color: white;
    }
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }
    .text-muted {
        font-size: 0.85em;
    }
    td {
        vertical-align: middle !important;
    }
    .nav-tabs .nav-link {
        text-transform: uppercase !important;
        font-weight: bold;
    }
    .nav-tabs .nav-link.active {
        background-color: #007bff;
        color: white !important;
        border-color: #007bff;
    }
    .btn-group-vertical .btn {
        border-radius: 4px !important;
        margin-bottom: 2px;
    }
    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }
    .ultimo-mensaje-info {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Estilos para filtros */
    .form-label {
        font-weight: bold;
        color: #495057;
        text-transform: uppercase;
    }
    
    .card-title {
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .btn-group .btn {
        white-space: nowrap;
    }
    
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
        }
        .btn-group .btn {
            margin-bottom: 5px;
            border-radius: 4px !important;
        }
        .btn-toolbar {
            flex-direction: column;
        }
        .btn-toolbar .btn-group {
            margin-bottom: 10px;
            margin-right: 0 !important;
        }
    }

    /* Estilos para filtros rápidos */
    .btn-toolbar .btn-group-sm .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .btn-outline-secondary:active {
        background-color: #007bff !important;
        border-color: #007bff !important;
        transform: translateY(0px);
    }
    
    .btn-outline-secondary.clicked {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: white !important;
    }
    
    /* Animación para indicar carga */
    .filtro-loading {
        opacity: 0.7;
        pointer-events: none;
    }
    
    .filtro-loading::after {
        content: ' ⏳';
    }
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('js/sucursal-cache.js') }}"></script>
<script>
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

function mostrarModalMensaje(clienteId, nombre, apellidos, tipo) {
    $('#clienteId').val(clienteId);
    $('#nombreCliente').text((nombre + ' ' + apellidos).toUpperCase());
    
    // Preparar mensaje predeterminado por defecto (en caso de error)
    let mensajePredeterminadoPorDefecto = `¡Hola ${nombre}! 👋

Esperamos que se encuentre muy bien.

Desde nuestra óptica queremos mantener el contacto con nuestros estimados clientes y ofrecerle nuestros servicios.

🔸 Contamos con:
• Lentes de contacto
• Marcos y cristales
• Exámenes de vista
• Reparaciones

💰 Tenemos excelentes promociones y descuentos especiales.

¿Le gustaría conocer más sobre nuestros servicios?

¡Estamos aquí para ayudarle! 👓✨

Saludos cordiales,
El equipo de Óptica`;
    
    // Obtener el mensaje del textarea como respaldo
    let mensajeDelTextarea = $('#mensajePredeterminado').val();
    
    // Intentar cargar el mensaje predeterminado desde la BD con manejo de errores mejorado
    try {
        $.ajax({
            url: '/telemarketing/configuraciones/mensaje-predeterminado',
            method: 'GET',
            timeout: 5000, // Tiempo máximo de espera de 5 segundos
            success: function(response) {
                let mensajePredeterminado;
                
                if (response && response.mensaje && response.mensaje.trim() !== '') {
                    mensajePredeterminado = response.mensaje;
                } else if (mensajeDelTextarea && mensajeDelTextarea.trim() !== '') {
                    mensajePredeterminado = mensajeDelTextarea;
                } else {
                    mensajePredeterminado = mensajePredeterminadoPorDefecto;
                }
                
                // Reemplazar variables en el mensaje
                mensajePredeterminado = mensajePredeterminado
                    .replace(/\[NOMBRE\]/g, nombre)
                    .replace(/\[APELLIDOS\]/g, apellidos)
                    .replace(/\[EMPRESA\]/g, 'Óptica');
                
                $('#mensajePersonalizado').val(mensajePredeterminado);
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar mensaje predeterminado:', status, error);
                
                // En caso de error, usar el valor del campo o el mensaje por defecto
                let mensajePredeterminado;
                
                if (mensajeDelTextarea && mensajeDelTextarea.trim() !== '') {
                    mensajePredeterminado = mensajeDelTextarea;
                } else {
                    mensajePredeterminado = mensajePredeterminadoPorDefecto;
                }
                
                // Reemplazar variables en el mensaje
                mensajePredeterminado = mensajePredeterminado
                    .replace(/\[NOMBRE\]/g, nombre)
                    .replace(/\[APELLIDOS\]/g, apellidos)
                    .replace(/\[EMPRESA\]/g, 'Óptica');
                
                $('#mensajePersonalizado').val(mensajePredeterminado);
            },
            complete: function() {
                // Mostrar el modal después de cargar el mensaje
                $('#enviarMensajeModal').modal('show');
            }
        });
    } catch (e) {
        console.error('Error al realizar la petición AJAX:', e);
        
        // Si hay un error en la ejecución de AJAX, usar mensaje por defecto
        let mensajePredeterminado = mensajeDelTextarea || mensajePredeterminadoPorDefecto;
        
        // Reemplazar variables en el mensaje
        mensajePredeterminado = mensajePredeterminado
            .replace(/\[NOMBRE\]/g, nombre)
            .replace(/\[APELLIDOS\]/g, apellidos)
            .replace(/\[EMPRESA\]/g, 'Óptica');
        
        $('#mensajePersonalizado').val(mensajePredeterminado);
        $('#enviarMensajeModal').modal('show');
    }
}

function guardarMensajePredeterminado() {
    const mensaje = $('#mensajePredeterminado').val();
    
    // Validar que el mensaje no esté vacío
    if (!mensaje || mensaje.trim() === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El mensaje no puede estar vacío'
        });
        return;
    }
    
    // Deshabilitar el botón mientras se procesa
    const botonGuardar = $('#editarMensajeModal .btn-primary');
    botonGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    // Guardar mensaje en la base de datos
    try {
        $.ajax({
            url: '/configuraciones/mensajes-predeterminados',
            method: 'POST',
            timeout: 10000, // 10 segundos de timeout
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                tipo: 'telemarketing',
                mensaje: mensaje
            },
            success: function(response) {
                $('#editarMensajeModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'El mensaje predeterminado ha sido actualizado.',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            error: function(xhr, status, errorThrown) {
                console.error('Error al guardar mensaje:', status, errorThrown);
                
                let mensaje = 'Error al guardar el mensaje';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    mensaje = xhr.responseJSON.error;
                } else if (status === 'timeout') {
                    mensaje = 'El servidor tardó demasiado en responder. Intente nuevamente.';
                } else if (status === 'error') {
                    mensaje = 'Error en la conexión. Verifique su conexión a internet.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje
                });
            },
            complete: function() {
                // Restaurar el botón
                botonGuardar.prop('disabled', false).html('GUARDAR MENSAJE');
            }
        });
    } catch (e) {
        console.error('Error en la ejecución de AJAX:', e);
        Swal.fire({
            icon: 'error',
            title: 'Error Inesperado',
            text: 'Ocurrió un error al intentar guardar el mensaje.'
        });
        botonGuardar.prop('disabled', false).html('GUARDAR MENSAJE');
    }
}

function enviarMensaje() {
    const clienteId = $('#clienteId').val();
    const mensaje = $('#mensajePersonalizado').val();
    const boton = $(`.btn-enviar-mensaje[data-cliente-id="${clienteId}"]`);
    
    console.log('Enviando mensaje a cliente ID:', clienteId);
    
    // Validar mensaje
    if (!mensaje || mensaje.trim() === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor, ingrese un mensaje antes de enviar.'
        });
        return;
    }

    // Obtener número de teléfono del botón de la tabla
    const celular = boton.data('celular');
    
    if (!celular) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró un número de teléfono válido para este cliente.'
        });
        return;
    }
    
    // Deshabilitar botón temporalmente
    const botonEnviar = $('#enviarMensajeModal .btn-success');
    botonEnviar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

    try {
        // Registrar el mensaje en la base de datos y enviar
        $.ajax({
            url: `/telemarketing/${clienteId}/enviar-mensaje`,
            method: 'POST',
            timeout: 10000, // 10 segundos de timeout
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                mensaje: mensaje,
                tipo: 'telemarketing' // Siempre enviamos un tipo
            },
            success: function(response) {
                // Buscar el contenedor de botones del cliente
                const filaCliente = $(`.btn-enviar-mensaje[data-cliente-id="${clienteId}"]`).closest('td');
                
                // Actualizar los botones para mostrar que ya se envió un mensaje
                const now = new Date();
                const fechaFormateada = now.toLocaleDateString('es-CL') + ' ' + now.toLocaleTimeString('es-CL', {hour: '2-digit', minute: '2-digit'});
                
                // Crear nuevo HTML para los botones
                const nuevosBotones = `
                    <div class="btn-group-vertical" style="width: 100%;">
                        <button type="button" 
                                class="btn btn-info btn-sm mb-1"
                                data-toggle="tooltip" 
                                title="Último mensaje: ${mensaje.substring(0, 100)}..."
                                style="font-size: 0.75rem;">
                            <i class="fas fa-clock"></i> ÚLTIMO MENSAJE: ${fechaFormateada}
                        </button>
                        <button type="button" 
                                class="btn btn-warning btn-sm btn-enviar-mensaje"
                                data-cliente-id="${clienteId}"
                                data-nombre="${boton.data('nombre')}"
                                data-apellidos="${boton.data('apellidos')}"
                                data-celular="${boton.data('celular')}"
                                data-tipo="${boton.data('tipo')}"
                                onclick="mostrarModalMensaje('${clienteId}', '${boton.data('nombre')}', '${boton.data('apellidos')}', '${boton.data('tipo')}')">
                            <i class="fab fa-whatsapp"></i> VOLVER A ENVIAR
                        </button>
                    </div>
                    <button type="button" 
                            class="btn btn-info btn-sm"
                            onclick="mostrarHistorial('${clienteId}', '${boton.data('nombre')}', '${boton.data('apellidos')}', '${boton.data('tipo')}')">
                        <i class="fas fa-history"></i> VER HISTORIAL
                    </button>
                `;
                
                // Reemplazar el contenido de la celda
                filaCliente.html(nuevosBotones);
                
                // Inicializar tooltips para los nuevos elementos
                $('[data-toggle="tooltip"]').tooltip();
                
                // Generar URL de WhatsApp optimizada
                const whatsappURL = generateWhatsAppURL(celular, mensaje);
                
                // Abrir WhatsApp
                const whatsappWindow = window.open(whatsappURL, '_blank');
                
                // Verificar si se abrió correctamente y ofrecer alternativa
                setTimeout(() => {
                    if (!whatsappWindow || whatsappWindow.closed) {
                        // Si no se abrió, intentar con URL alternativa
                        const alternativeURL = `https://web.whatsapp.com/send?phone=${formatChileanPhone(celular)}&text=${encodeURIComponent(mensaje)}`;
                        window.open(alternativeURL, '_blank');
                    }
                }, 1000);
                
                // Cerrar el modal primero para evitar problemas
                $('#enviarMensajeModal').modal('hide');
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡WhatsApp Abierto!',
                    text: 'Se ha abierto WhatsApp con el mensaje de telemarketing.',
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function(xhr, status, error) {
                console.error('Error al enviar mensaje:', status, error);
                let errorMessage = 'Error al enviar el mensaje';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (status === 'timeout') {
                    errorMessage = 'El servidor tardó demasiado en responder. Intente nuevamente.';
                } else if (status === 'error') {
                    errorMessage = 'Error en la conexión. Verifique su conexión a internet.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                
                // Restaurar el botón
                botonEnviar.html('<i class="fab fa-whatsapp"></i> ENVIAR POR WHATSAPP');
            },
            complete: function() {
                // Rehabilitar botón
                botonEnviar.prop('disabled', false);
                
                // Intentar actualizar el mensaje predeterminado para el próximo uso
                // (Lo hacemos después de haber completado la acción principal para evitar bloqueos)
                try {
                    $.ajax({
                        url: '/telemarketing/configuraciones/mensaje-predeterminado',
                        method: 'GET',
                        timeout: 5000,
                        success: function(response) {
                            if (response && response.mensaje) {
                                // Lo guardamos para futuras referencias pero no hacemos nada más
                                console.log('Mensaje predeterminado actualizado para próximo uso');
                            }
                        },
                        error: function(xhr, status, err) {
                            console.log('No se pudo actualizar el mensaje predeterminado:', status);
                        }
                    });
                } catch (e) {
                    console.error('Error al intentar actualizar mensaje predeterminado:', e);
                }
            }
        });
    } catch (e) {
        console.error('Error en la ejecución de AJAX:', e);
        Swal.fire({
            icon: 'error',
            title: 'Error Inesperado',
            text: 'Ocurrió un error al intentar enviar el mensaje.'
        });
        botonEnviar.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> ENVIAR POR WHATSAPP');
    }
}

// Función de testing para verificar que los filtros funcionan (SIN AUTO-SUBMIT)
function testFiltrosRapidos() {
    console.log('=== TEST DE FILTROS RÁPIDOS ===');
    console.log('setFechaRapida disponible:', typeof setFechaRapida);
    console.log('jQuery disponible:', typeof $);
    console.log('SweetAlert disponible:', typeof Swal);
    
    const botones = $('.btn-toolbar button[onclick*="setFechaRapida"]');
    console.log('Botones encontrados:', botones.length);
    
    botones.each(function(index) {
        const onclick = $(this).attr('onclick');
        console.log(`Botón ${index + 1}: ${onclick}`);
    });
    
    console.log('ℹ️  Los filtros rápidos solo establecen fechas - el usuario debe hacer clic en FILTRAR');
    console.log('✅ Configuración anti-reload lista');
}

// Función para establecer fechas rápidas
function setFechaRapida(periodo) {
    console.log('Aplicando filtro rápido:', periodo);
    
    try {
        // Encontrar el botón que fue clickeado para darle feedback visual
        if (typeof event !== 'undefined' && event.target) {
            const botonClickeado = event.target;
            
            // Agregar clase de "clicked" temporalmente
            $(botonClickeado).addClass('clicked');
            setTimeout(() => {
                $(botonClickeado).removeClass('clicked');
            }, 1000);
        }
        
        var hoy = new Date();
        var fechaInicio, fechaFin;
        
        // Configurar fechas según el período seleccionado
        switch(periodo) {
            case 'hoy':
                fechaInicio = fechaFin = hoy.toISOString().split('T')[0];
                break;
                
            case 'ayer':
                var ayer = new Date(hoy);
                ayer.setDate(hoy.getDate() - 1);
                fechaInicio = fechaFin = ayer.toISOString().split('T')[0];
                break;
                
            case 'semana':
                // Desde el lunes de esta semana hasta hoy
                var inicioSemana = new Date(hoy);
                var dia = inicioSemana.getDay();
                var diferencia = dia === 0 ? 6 : dia - 1; // Si es domingo (0), retroceder 6 días
                inicioSemana.setDate(hoy.getDate() - diferencia);
                fechaInicio = inicioSemana.toISOString().split('T')[0];
                fechaFin = hoy.toISOString().split('T')[0];
                break;
                
            case 'mes':
                // Desde el primer día del mes actual hasta hoy
                fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                fechaFin = hoy.toISOString().split('T')[0];
                break;
                
            case 'mes_anterior':
                // Todo el mes anterior completo
                var mesAnterior = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
                var finMesAnterior = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
                fechaInicio = mesAnterior.toISOString().split('T')[0];
                fechaFin = finMesAnterior.toISOString().split('T')[0];
                break;
                
            case 'trimestre':
                // Desde el inicio del trimestre actual hasta hoy
                var mesActual = hoy.getMonth();
                var inicioTrimestre = Math.floor(mesActual / 3) * 3;
                fechaInicio = new Date(hoy.getFullYear(), inicioTrimestre, 1).toISOString().split('T')[0];
                fechaFin = hoy.toISOString().split('T')[0];
                break;
                
            case 'año':
                // Desde el 1 de enero del año actual hasta hoy
                fechaInicio = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                fechaFin = hoy.toISOString().split('T')[0];
                break;
                
            default:
                console.error('Período no reconocido:', periodo);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Período no reconocido: ' + periodo,
                        timer: 3000
                    });
                }
                return;
        }
        
        // Validar que las fechas sean válidas
        if (!fechaInicio || !fechaFin) {
            console.error('Error al calcular fechas para el período:', periodo);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron calcular las fechas para el período seleccionado.',
                    timer: 3000
                });
            }
            return;
        }
        
        // Establecer las fechas en los campos
        $('#fecha_inicio').val(fechaInicio);
        $('#fecha_fin').val(fechaFin);
        
        // Mostrar notificación del filtro aplicado
        const periodoTexto = {
            'hoy': 'HOY',
            'ayer': 'AYER',
            'semana': 'ESTA SEMANA',
            'mes': 'ESTE MES',
            'mes_anterior': 'MES ANTERIOR',
            'trimestre': 'ESTE TRIMESTRE',
            'año': 'ESTE AÑO'
        };
        
        // Formatear fechas para mostrar
        const fechaInicioFormateada = new Date(fechaInicio).toLocaleDateString('es-CL');
        const fechaFinFormateada = new Date(fechaFin).toLocaleDateString('es-CL');
        
        // Mostrar toast de confirmación si SweetAlert está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: `📅 ${periodoTexto[periodo]}`,
                text: `${fechaInicioFormateada} → ${fechaFinFormateada} - Presione "FILTRAR" para aplicar`,
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                background: '#d4edda',
                color: '#155724'
            });
        }
        
        // NO aplicar filtro automáticamente - el usuario debe hacer clic en "FILTRAR"
        console.log('Fechas establecidas. El usuario debe hacer clic en FILTRAR para aplicar el filtro.');
        
    } catch (error) {
        console.error('Error en setFechaRapida:', error);
        alert('Error al aplicar el filtro rápido: ' + error.message);
    }
}

function mostrarHistorial(clienteId, nombre, apellidos, tipo) {
    $('#clienteNombre').text((nombre + ' ' + apellidos).toUpperCase());
    $('#historialLoader').show();
    $('#historialContent').hide();
    $('#historialModal').modal('show');
    
    // Hacer la solicitud AJAX para obtener el historial
    $.ajax({
        url: `/telemarketing/${clienteId}/historial`,
        method: 'GET',
        data: {
            tipo: tipo
        },
        success: function(response) {
            // Llenar la tabla de pedidos
            const pedidosBody = $('#pedidosHistorialBody');
            pedidosBody.empty();
            
            if (response.pedidos && response.pedidos.length > 0) {
                response.pedidos.forEach(function(pedido) {
                    const estadoColor = pedido.fact === 'Pendiente' ? 'orange' : 
                                       (pedido.fact === 'LISTO EN TALLER' ? 'blue' : 
                                        (pedido.fact === 'LISTO PARA ENTREGA' ? 'purple' : 
                                         (pedido.fact === 'ENTREGADO' ? 'green' : 'black')));
                    
                    pedidosBody.append(`
                        <tr>
                            <td style="display: none;">${pedido.id}</td>
                            <td>${pedido.fecha}</td>
                            <td>${pedido.numero_orden}</td>
                            <td><span style="color: ${estadoColor}">${pedido.fact}</span></td>
                            <td>$${pedido.total_formatted}</td>
                            <td><span style="color: ${pedido.saldo == 0 ? 'green' : 'red'}">$${pedido.saldo_formatted}</span></td>
                            <td>
                                <a href="/Pedidos/${pedido.id}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> VER
                                </a>
                            </td>
                        </tr>
                    `);
                });
            } else {
                pedidosBody.append('<tr><td colspan="7" class="text-center text-muted">NO HAY PEDIDOS REGISTRADOS</td></tr>');
            }
            
            // Llenar la tabla de historiales clínicos
            const historialesBody = $('#historialesClinicoBody');
            historialesBody.empty();
            
            if (response.historiales && response.historiales.length > 0) {
                response.historiales.forEach(function(historial) {
                    const proximaConsulta = historial.proxima_consulta ? 
                        new Date(historial.proxima_consulta).toLocaleDateString('es-CL') : 
                        'NO PROGRAMADA';
                    
                    historialesBody.append(`
                        <tr>
                            <td style="display: none;">${historial.id}</td>
                            <td>${historial.fecha}</td>
                            <td>${proximaConsulta}</td>
                            <td>${historial.usuario}</td>
                            <td>
                                <a href="/historiales_clinicos/${historial.id}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> VER
                                </a>
                            </td>
                        </tr>
                    `);
                });
            } else {
                historialesBody.append('<tr><td colspan="5" class="text-center text-muted">NO HAY HISTORIALES CLÍNICOS REGISTRADOS</td></tr>');
            }
            
            $('#historialLoader').hide();
            $('#historialContent').show();
        },
        error: function() {
            $('#pedidosHistorialBody').html('<tr><td colspan="7" class="text-center text-danger">ERROR AL CARGAR PEDIDOS</td></tr>');
            $('#historialesClinicoBody').html('<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR HISTORIALES</td></tr>');
            $('#historialLoader').hide();
            $('#historialContent').show();
        }
    });
}

// Cargar mensaje predeterminado al iniciar la página
$(document).ready(function() {
    // Agregar el token CSRF si no existe
    if (!$('meta[name="csrf-token"]').length) {
        $('head').append('<meta name="csrf-token" content="{{ csrf_token() }}">');
    }
    
    // Preseleccionar sucursal desde caché y aplicar automáticamente
    if (window.SucursalCache) {
        // Verificar si ya hay parámetros de filtro en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const filtrosAplicados = urlParams.has('empresa_id') || urlParams.has('tipo_cliente') || 
                                urlParams.has('fecha_inicio') || urlParams.has('fecha_fin');
        
        if (!filtrosAplicados) {
            // Solo preseleccionar y aplicar si no hay filtros previos
            const sucursal = SucursalCache.obtener();
            if (sucursal) {
                $('#empresa_id').val(sucursal.id);
                
                // Solo aplicar automáticamente si:
                // 1. Existe la sucursal en el caché
                // 2. No venimos de una búsqueda anterior (evita bucle de recargas)
                // 3. No estamos en modo "mostrar todos"
                if (!document.referrer.includes(window.location.pathname) && 
                    !SucursalCache.esModoMostrarTodos()) {
                    console.log("Aplicando filtro de sucursal automáticamente:", sucursal.nombre);
                    
                    // Marcar que este submit es automático para evitar problemas
                    sessionStorage.setItem('filtro_auto_aplicado', 'true');
                    $('#filtroForm').submit();
                    return; // Importante: detenemos la ejecución aquí para evitar doble carga
                }
            }
        }
    }
    
    // Evitar bucle de recargas al volver atrás
    $('#empresa_id').on('change', function() {
        // Al cambiar manualmente, NO enviar el formulario automáticamente
        // El usuario debe hacer clic en el botón FILTRAR
    });
    
    // Inicializar DataTable
    $('#clientesTable').DataTable({
        "order": [[0, "asc"]],
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "{{ asset('js/datatables/Spanish.json') }}"
        }
    });
    
    // Botón Mostrar Todos
    $('#mostrarTodosButton').click(function() {
        $('#tipo_cliente').val('');
        $('#fecha_inicio').val('');
        $('#fecha_fin').val('');
        $('#filtroForm').submit();
    });

    // Botón Limpiar Filtros
    $('#limpiarFiltrosButton').click(function() {
        $('#empresa_id').val(''); // Limpiar también la empresa seleccionada
        $('#tipo_cliente').val('');
        $('#fecha_inicio').val('');
        $('#fecha_fin').val('');
        // Eliminar el indicador visual si existe
        $('.auto-filter-indicator').remove();
    });
    
    // Agregar un indicador visual si se ha preseleccionado la sucursal automáticamente
    if (window.SucursalCache && SucursalCache.obtener()) {
        const empresaSelect = $('#empresa_id');
        if (empresaSelect.val()) {
            const label = empresaSelect.parent().find('label');
            if (label.length) {
                // Agregar un indicador visual en el label
                if (!label.find('.auto-filter-indicator').length) {
                    label.append(' <span class="auto-filter-indicator badge badge-pill badge-success" style="font-size: 0.7rem;" title="Sucursal preseleccionada automáticamente">Auto</span>');
                }
            }
        }
    }

    // Validación de fechas (sin auto-submit)
    $('#fecha_inicio, #fecha_fin').on('change', function() {
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $('#fecha_fin').val();
        
        if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas Inválidas',
                text: 'La fecha de inicio no puede ser mayor que la fecha de fin.',
                confirmButtonText: 'Entendido'
            });
            $(this).val('');
        }
    });

    // Funcionalidad de botones deshabilitada para evitar auto-submits
    // Los botones de filtros rápidos solo establecen fechas, el usuario debe hacer clic en FILTRAR
    
    // Cargar mensaje predeterminado desde el servidor
    try {
        $.ajax({
            url: '/telemarketing/configuraciones/mensaje-predeterminado',
            method: 'GET',
            timeout: 5000,
            success: function(response) {
                if (response && response.mensaje) {
                    $('#mensajePredeterminado').val(response.mensaje);
                }
            },
            error: function(xhr, status, error) {
                console.warn('No se pudo cargar el mensaje predeterminado:', status, error);
                // No hacemos nada más, usaremos el valor por defecto del textarea
            }
        });
    } catch (e) {
        console.error('Error al intentar cargar mensaje predeterminado:', e);
    }
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Función para destacar el filtro rápido activo
    function actualizarFiltrosRapidosActivos() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        if (!fechaInicio || !fechaFin) {
            // Remover todas las clases activas si no hay fechas
            $('.btn-toolbar .btn').removeClass('btn-primary').addClass('btn-outline-secondary');
            return;
        }
        
        const hoy = new Date();
        const fechaHoy = hoy.toISOString().split('T')[0];
        
        // Remover clases activas primero
        $('.btn-toolbar .btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        
        // Verificar qué filtro corresponde a las fechas actuales
        if (fechaInicio === fechaFin && fechaInicio === fechaHoy) {
            $('button[onclick="setFechaRapida(\'hoy\')"]').removeClass('btn-outline-secondary').addClass('btn-primary');
        } else if (fechaInicio === fechaFin) {
            const ayer = new Date(hoy);
            ayer.setDate(hoy.getDate() - 1);
            if (fechaInicio === ayer.toISOString().split('T')[0]) {
                $('button[onclick="setFechaRapida(\'ayer\')"]').removeClass('btn-outline-secondary').addClass('btn-primary');
            }
        } else {
            // Verificar otros períodos...
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            if (fechaInicio === inicioMes && fechaFin === fechaHoy) {
                $('button[onclick="setFechaRapida(\'mes\')"]').removeClass('btn-outline-secondary').addClass('btn-primary');
            }
            
            const inicioAño = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
            if (fechaInicio === inicioAño && fechaFin === fechaHoy) {
                $('button[onclick="setFechaRapida(\'año\')"]').removeClass('btn-outline-secondary').addClass('btn-primary');
            }
        }
    }
    
    // Llamar la función al cargar la página y cuando cambien las fechas (sin auto-submit)
    actualizarFiltrosRapidosActivos();
    $('#fecha_inicio, #fecha_fin').on('change', actualizarFiltrosRapidosActivos);
    
    // Event listeners de filtros rápidos DESHABILITADOS para evitar conflictos
    // Solo se usará el onclick de los botones
    /*
    $('.btn-toolbar button[onclick*="setFechaRapida"]').each(function() {
        const $button = $(this);
        const onclick = $button.attr('onclick');
        if (onclick) {
            const match = onclick.match(/setFechaRapida\('([^']+)'\)/);
            if (match) {
                const periodo = match[1];
                $button.off('click.filtroRapido').on('click.filtroRapido', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Ejecutando filtro rápido via event listener:', periodo);
                    setFechaRapida(periodo);
                });
            }
        }
    });
    */
    
    // Verificación final: asegurar que la función setFechaRapida esté disponible globalmente
    if (typeof window.setFechaRapida === 'undefined') {
        console.log('Definiendo setFechaRapida globalmente...');
        window.setFechaRapida = setFechaRapida;
    }
    
    console.log('Script de telemarketing cargado correctamente. setFechaRapida disponible:', typeof setFechaRapida);
    
    // Ejecutar test después de un breve delay para asegurar que todo esté cargado
    setTimeout(() => {
        testFiltrosRapidos();
    }, 1000);
});

// Asegurar que la función esté disponible globalmente
if (typeof window.setFechaRapida === 'undefined') {
    window.setFechaRapida = setFechaRapida;
}
</script>
@stop
