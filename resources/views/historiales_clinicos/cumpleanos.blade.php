@extends('adminlte::page')

@section('title', 'CUMPLEAÑOS DEL MES')

@php
use App\Models\MensajePredeterminado;
@endphp

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>CUMPLEAÑOS DEL MES DE {{ strtoupper($mes_actual) }}</h1>
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
        <h3 class="card-title">PACIENTES QUE CUMPLEN AÑOS ESTE MES</h3>
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
                                <div class="col-md-4 mb-3">
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
                                <div class="col-md-4 mb-3">
                                    <label for="mes" class="form-label">MES:</label>
                                    <select name="mes" id="mes" class="form-control">
                                        <option value="">TODOS LOS MESES</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ request('mes', date('n')) == $i ? 'selected' : '' }}>
                                                {{ strtoupper(\Carbon\Carbon::create(null, $i, 1)->locale('es')->format('F')) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="año" class="form-label">AÑO NACIMIENTO:</label>
                                    <select name="año" id="año" class="form-control">
                                        <option value="">TODOS LOS AÑOS</option>
                                        @for($año = date('Y'); $año >= 1950; $año--)
                                            <option value="{{ $año }}" {{ request('año') == $año ? 'selected' : '' }}>
                                                {{ $año }}
                                            </option>
                                        @endfor
                                    </select>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Indicador de resultados y filtros activos --}}
        @if(request()->hasAny(['empresa_id', 'mes', 'año']))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-filter"></i> <strong>FILTROS ACTIVOS:</strong>
                    @if(request('empresa_id'))
                        <span class="badge badge-info ml-1">
                            SUCURSAL: {{ strtoupper($empresas->firstWhere('id', request('empresa_id'))->nombre ?? 'DESCONOCIDA') }}
                        </span>
                    @endif
                    @if(request('mes'))
                        <span class="badge badge-primary ml-1">
                            MES: {{ strtoupper(\Carbon\Carbon::create(null, request('mes'), 1)->locale('es')->format('F')) }}
                        </span>
                    @endif
                    @if(request('año'))
                        <span class="badge badge-warning ml-1">
                            AÑO: {{ request('año') }}
                        </span>
                    @endif
                    <span class="float-right">
                        <strong>{{ $cumpleaneros->count() }} resultado(s) encontrado(s)</strong>
                    </span>
                </div>
            </div>
        </div>
        @endif

        @if($cumpleaneros->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                @if(request()->hasAny(['empresa_id', 'mes', 'año']))
                    NO SE ENCONTRARON CUMPLEAÑOS CON LOS FILTROS APLICADOS.
                    <a href="{{ route('historiales_clinicos.cumpleanos') }}" class="btn btn-sm btn-primary ml-2">
                        <i class="fas fa-refresh"></i> VER TODOS
                    </a>
                @else
                    NO HAY CUMPLEAÑOS REGISTRADOS PARA ESTE MES.
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table id="cumpleanosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>DÍA</th>
                            <th>NOMBRES</th>
                            <th>APELLIDOS</th>
                            <th>SUCURSAL</th>
                            <th>EDAD</th>
                            <th>CELULAR</th>
                            <th>ÚLTIMA CONSULTA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cumpleaneros as $paciente)
                        <tr>
                            <td>
                                <span class="badge badge-primary" style="font-size: 1em;">
                                    {{ $paciente['dia_cumpleanos'] }}
                                </span>
                                <br>
                                <small class="text-muted">{{ strtoupper($paciente['dia_nombre']) }}</small>
                            </td>
                            <td>{{ strtoupper($paciente['nombres']) }}</td>
                            <td>{{ strtoupper($paciente['apellidos']) }}</td>
                            <td>
                                @if(isset($paciente['empresa_nombre']))
                                    <span class="badge badge-secondary">
                                        {{ strtoupper($paciente['empresa_nombre']) }}
                                    </span>
                                @else
                                    <span class="badge badge-light">NO ASIGNADA</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 0.9em;">
                                    CUMPLE {{ $paciente['edad_cumplir'] }}
                                </span>
                                <br>
                                <small class="text-muted">(ACTUAL: {{ $paciente['edad_actual'] }})</small>
                            </td>
                            <td>
                                @if($paciente['celular'])
                                    <span class="badge badge-success">
                                        <i class="fas fa-phone"></i> {{ $paciente['celular'] }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">SIN CELULAR</span>
                                @endif
                            </td>
                            <td>{{ $paciente['ultima_consulta'] }}</td>
                            <td>
                                <div class="btn-group">
                                    @if($paciente['celular'])
                                        @php
                                            $mesActual = now()->format('Y-m');
                                            $mensajeEnviado = \App\Models\MensajesEnviados::where('historial_id', $paciente['id'])
                                                ->where('tipo', 'cumpleanos')
                                                ->whereRaw('DATE_FORMAT(fecha_envio, "%Y-%m") = ?', [$mesActual])
                                                ->exists();
                                        @endphp
                                        
                                        <button type="button" 
                                            class="btn {{ $mensajeEnviado ? 'btn-warning' : 'btn-success' }} btn-sm btn-enviar-mensaje"
                                            data-paciente-id="{{ $paciente['id'] }}"
                                            onclick="mostrarModalMensaje(
                                                {{ $paciente['id'] }}, 
                                                '{{ $paciente['nombres'] }}',
                                                '{{ $paciente['apellidos'] }}',
                                                {{ $paciente['edad_cumplir'] }},
                                                {{ $paciente['dia_cumpleanos'] }},
                                                '{{ $paciente['empresa_nombre'] ?? 'Óptica' }}'
                                            )">
                                            <i class="fab fa-whatsapp"></i> 
                                            {{ $mensajeEnviado ? 'VOLVER A ENVIAR' : 'ENVIAR FELICITACIÓN' }}
                                        </button>
                                    @endif
                                </div>
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
<div class="modal fade" id="editarMensajeModal" tabindex="-1" role="dialog" aria-labelledby="editarMensajeModalLabel">
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
                        <label>MENSAJE DE FELICITACIÓN:</label>
                        <div class="alert alert-info mb-3">
                            <strong><i class="fas fa-info-circle"></i> VARIABLES DISPONIBLES:</strong>
                            <br>
                            <small>
                                • <code>[NOMBRE]</code> - Nombre del paciente<br>
                                • <code>[APELLIDOS]</code> - Apellidos del paciente<br>
                                • <code>[EDAD]</code> - Edad que está cumpliendo<br>
                                • <code>[DIA]</code> - Día del cumpleaños (número)<br>
                                • <code>[MES]</code> - Mes actual en texto<br>
                                • <code>[EMPRESA]</code> - Nombre de la sucursal/óptica
                            </small>
                        </div>
                        <div class="alert alert-success mb-3" style="font-size: 0.85em;">
                            <strong><i class="fas fa-eye"></i> EJEMPLO:</strong><br>
                            <em>"¡Feliz cumpleaños [NOMBRE]! 🎉 Esperamos que pases un día maravilloso cumpliendo [EDAD] años."</em><br>
                            <strong>Se convierte en:</strong><br>
                            <em>"¡Feliz cumpleaños María! 🎉 Esperamos que pases un día maravilloso cumpliendo 25 años."</em>
                        </div>
                        <textarea class="form-control" id="mensajePredeterminado" rows="8" placeholder="Ejemplo: ¡Feliz cumpleaños [NOMBRE]! 🎉 Esperamos que pases un día maravilloso cumpliendo [EDAD] años...">{{ MensajePredeterminado::obtenerMensaje('cumpleanos') ?: '¡Feliz cumpleaños [NOMBRE]! 🎉

Esperamos que tengas un día maravilloso cumpliendo [EDAD] años.

🎂 En [EMPRESA] queremos ser parte de este día especial y desearte que todos tus sueños se hagan realidad.

🎈 ¡Que disfrutes mucho tu día especial!

Con cariño,
El equipo de [EMPRESA] 👓✨' }}</textarea>
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
                <h5 class="modal-title">ENVIAR MENSAJE DE FELICITACIÓN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="enviarMensajeForm">
                    <input type="hidden" id="pacienteId">
                    <div class="form-group">
                        <label>MENSAJE PARA: <span id="nombrePaciente"></span></label>
                        <textarea class="form-control" id="mensajePersonalizado" rows="6"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-success" onclick="enviarMensaje()">
                    <i class="fab fa-whatsapp"></i> ENVIAR MENSAJE
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
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
    .text-muted {
        font-size: 0.85em;
    }
    td {
        vertical-align: middle !important;
    }
</style>
@stop

@section('js')
<script src="{{ asset('js/sucursal-cache.js') }}"></script>
<script>
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
            Swal.fire({
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
            Swal.fire({
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
    Swal.fire({
        title: 'Copiar Manualmente',
        html: `<textarea class="form-control" rows="8" readonly style="width: 100%;">${texto}</textarea>`,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar',
        width: '600px'
    });
}

function mostrarModalMensaje(pacienteId, nombrePaciente, apellidosPaciente, edadPaciente, diaPaciente, empresaNombre) {
    $('#pacienteId').val(pacienteId);
    $('#nombrePaciente').text(nombrePaciente + ' ' + apellidosPaciente);
    
    // Obtener el mensaje predeterminado y reemplazar las variables
    let mensaje = $('#mensajePredeterminado').val();
    
    // Si no hay mensaje predeterminado, usar uno por defecto
    if (!mensaje || mensaje.trim() === '') {
        mensaje = `¡Feliz cumpleaños [NOMBRE]! 🎉

Esperamos que tengas un día maravilloso cumpliendo [EDAD] años.

🎂 En [EMPRESA] queremos ser parte de este día especial y desearte que todos tus sueños se hagan realidad.

🎈 ¡Que disfrutes mucho tu día especial!

Con cariño,
El equipo de [EMPRESA] 👓✨`;
    }
    
    // Obtener el nombre del mes actual en español
    const meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];
    const mesActual = meses[new Date().getMonth()];
    
    // Reemplazar las variables en el mensaje
    mensaje = mensaje
        .replace(/\[NOMBRE\]/g, nombrePaciente)
        .replace(/\[APELLIDOS\]/g, apellidosPaciente)
        .replace(/\[EDAD\]/g, edadPaciente)
        .replace(/\[DIA\]/g, diaPaciente)
        .replace(/\[MES\]/g, mesActual)
        .replace(/\[EMPRESA\]/g, empresaNombre);
    
    $('#mensajePersonalizado').val(mensaje);
    $('#enviarMensajeModal').modal('show');
}

function guardarMensajePredeterminado() {
    const mensaje = $('#mensajePredeterminado').val();
    
    // Guardar mensaje en la base de datos en lugar de localStorage
    $.ajax({
        url: '/configuraciones/mensajes-predeterminados',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            tipo: 'cumpleanos',
            mensaje: mensaje
        },
        success: function(response) {
            $('#editarMensajeModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'El mensaje predeterminado ha sido actualizado.'
            });
        },
        error: function(xhr) {
            let mensaje = 'Error al guardar el mensaje';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                mensaje = xhr.responseJSON.error;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        }
    });
}

function enviarMensaje() {
    const pacienteId = $('#pacienteId').val();
    const mensaje = $('#mensajePersonalizado').val();
    const boton = $(`.btn-enviar-mensaje[data-paciente-id="${pacienteId}"]`);
    
    // Obtener el número de teléfono del paciente de la tabla
    const celularRow = boton.closest('tr');
    const celularText = celularRow.find('.badge-success').text();
    const celular = celularText.replace(/[^\d]/g, '');
    
    // Validar que exista el número de teléfono
    if (!celular) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró un número de teléfono válido'
        });
        return;
    }
    
    // Registrar el mensaje en la base de datos y enviar
    $.ajax({
        url: `/historiales_clinicos/${pacienteId}/enviar-mensaje`,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            mensaje: mensaje,
            tipo: 'cumpleanos',
            forzar_envio: false
        },
        success: function(response) {
            // Cerrar el modal
            $('#enviarMensajeModal').modal('hide');
            
            // Usar la función mejorada de WhatsApp
            enviarWhatsAppSeguro(celular, mensaje, function() {
                // Actualizar el botón inmediatamente
                boton.removeClass('btn-success')
                     .addClass('btn-warning')
                     .html('<i class="fab fa-whatsapp"></i> VOLVER A ENVIAR');
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Mensaje Preparado!',
                    text: 'Seleccione una opción para enviar el mensaje.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        },
        error: function(xhr) {
            let mensaje = 'Error al enviar el mensaje';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                mensaje = xhr.responseJSON.error;
            }
            
            // Si requiere confirmación para reenviar en el mismo mes
            if (xhr.responseJSON && xhr.responseJSON.requiere_confirmacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: mensaje,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, enviar de todos modos',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reenviar con forzado
                        $.ajax({
                            url: `/historiales_clinicos/${pacienteId}/enviar-mensaje`,
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                mensaje: mensaje,
                                tipo: 'cumpleanos',
                                forzar_envio: true
                            },
                            success: function(response) {
                                // Cerrar el modal
                                $('#enviarMensajeModal').modal('hide');
                                
                                // Usar la función mejorada de WhatsApp
                                enviarWhatsAppSeguro(celular, mensaje, function() {
                                    // Mostrar mensaje de éxito
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Mensaje Preparado!',
                                        text: 'Seleccione una opción para enviar el mensaje.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                });
                            },
                            error: function(xhr) {
                                let mensaje = 'Error al enviar el mensaje';
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    mensaje = xhr.responseJSON.error;
                                }
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: mensaje
                                });
                            }
                        });
                    }
                });
                return;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        }
    });
}

// Cargar mensaje predeterminado al iniciar la página
$(document).ready(function() {
    // Agregar el token CSRF si no existe
    if (!$('meta[name="csrf-token"]').length) {
        $('head').append('<meta name="csrf-token" content="{{ csrf_token() }}">');
    }
    
    // Configurar AJAX para usar el token CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // No necesitamos verificar mensajes enviados aquí ya que lo hacemos en el servidor con @php
    
    // Preseleccionar sucursal desde caché y aplicar automáticamente
    if (window.SucursalCache) {
        // Verificar si ya hay parámetros de filtro en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const filtrosAplicados = urlParams.has('empresa_id') || urlParams.has('mes') || urlParams.has('año');
        
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
    
    // Botón Mostrar Todos
    $('#mostrarTodosButton').click(function() {
        window.location.href = '{{ route("historiales_clinicos.cumpleanos") }}?todos=1';
    });

    // Botón Limpiar Filtros
    $('#limpiarFiltrosButton').click(function() {
        $('#empresa_id').val('');
        $('#mes').val('{{ date("n") }}'); // Establecer mes actual como predeterminado
        $('#año').val('');
        // Eliminar el indicador visual si existe
        $('.auto-filter-indicator').remove();
        $('#filtroForm').submit();
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
});
</script>
@stop 