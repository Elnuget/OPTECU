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
                <form method="GET" class="form-inline" id="filtroForm">
                    <div class="form-group mr-2">
                        <label for="empresa_id" class="mr-2">EMPRESA:</label>
                        <select name="empresa_id" id="empresa_id" class="form-control" {{ (!isset($isUserAdmin) || !$isUserAdmin) && isset($userEmpresaId) && $userEmpresaId ? 'disabled' : '' }}>
                            <option value="">TODAS LAS EMPRESAS</option>
                            @foreach($empresas ?? [] as $empresa)
                                <option value="{{ $empresa->id }}" {{ (isset($userEmpresaId) && $userEmpresaId == $empresa->id) || request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                    {{ strtoupper($empresa->nombre) }}
                                </option>
                            @endforeach
                        </select>
                        @if((!isset($isUserAdmin) || !$isUserAdmin) && isset($userEmpresaId) && $userEmpresaId)
                            <input type="hidden" name="empresa_id" value="{{ $userEmpresaId }}">
                        @endif
                    </div>
                    <div class="form-group mr-2">
                        <label for="tipo_cliente" class="mr-2">TIPO:</label>
                        <select name="tipo_cliente" id="tipo_cliente" class="form-control">
                            <option value="">TODOS</option>
                            <option value="cliente" {{ request('tipo_cliente') == 'cliente' ? 'selected' : '' }}>SOLO CLIENTES</option>
                            <option value="paciente" {{ request('tipo_cliente') == 'paciente' ? 'selected' : '' }}>SOLO PACIENTES</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">FILTRAR</button>
                    <button type="button" class="btn btn-success" id="mostrarTodosButton">MOSTRAR TODOS</button>
                </form>
            </div>
        </div>

        @if($clientes->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> NO HAY CLIENTES REGISTRADOS.
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
                                            <th>FECHA</th>
                                            <th>ORDEN</th>
                                            <th>ESTADO</th>
                                            <th>TOTAL</th>
                                            <th>SALDO</th>
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
                                            <th>FECHA</th>
                                            <th>MOTIVO CONSULTA</th>
                                            <th>PRÓXIMA CONSULTA</th>
                                            <th>USUARIO</th>
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
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
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
                            <td>${pedido.fecha}</td>
                            <td>${pedido.numero_orden}</td>
                            <td><span style="color: ${estadoColor}">${pedido.fact}</span></td>
                            <td>$${pedido.total_formatted}</td>
                            <td><span style="color: ${pedido.saldo == 0 ? 'green' : 'red'}">$${pedido.saldo_formatted}</span></td>
                        </tr>
                    `);
                });
            } else {
                pedidosBody.append('<tr><td colspan="5" class="text-center text-muted">NO HAY PEDIDOS REGISTRADOS</td></tr>');
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
                            <td>${historial.fecha}</td>
                            <td>${historial.motivo_consulta}</td>
                            <td>${proximaConsulta}</td>
                            <td>${historial.usuario}</td>
                        </tr>
                    `);
                });
            } else {
                historialesBody.append('<tr><td colspan="4" class="text-center text-muted">NO HAY HISTORIALES CLÍNICOS REGISTRADOS</td></tr>');
            }
            
            $('#historialLoader').hide();
            $('#historialContent').show();
        },
        error: function() {
            $('#pedidosHistorialBody').html('<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR PEDIDOS</td></tr>');
            $('#historialesClinicoBody').html('<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR HISTORIALES</td></tr>');
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
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
    
    // Botón Mostrar Todos
    $('#mostrarTodosButton').click(function() {
        $('#tipo_cliente').val('');
        
        // Solo limpiamos el filtro de empresa si el usuario es administrador o no tiene empresa asignada
        @if(isset($isUserAdmin) && ($isUserAdmin || !isset($userEmpresaId) || !$userEmpresaId))
            $('#empresa_id').val('');
        @endif
        
        $('#filtroForm').submit();
    });
    
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
});
</script>
@stop
