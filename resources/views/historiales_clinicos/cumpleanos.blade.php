@extends('adminlte::page')

@section('title', 'CUMPLEAÑOS DEL MES')

@php
use App\Models\MensajePredeterminado;
@endphp

@section('content_header')
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
        @if($cumpleaneros->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> NO HAY CUMPLEAÑOS REGISTRADOS PARA ESTE MES.
            </div>
        @else
            <div class="table-responsive">
                <table id="cumpleanosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>DÍA</th>
                            <th>NOMBRES</th>
                            <th>APELLIDOS</th>
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
                                            onclick="mostrarModalMensaje({{ $paciente['id'] }}, '{{ $paciente['nombres'] }}')">
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
    <div class="modal-dialog" role="document">
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
                        <textarea class="form-control" id="mensajePredeterminado" rows="6">{{ MensajePredeterminado::obtenerMensaje('cumpleanos') ?: '¡Feliz cumpleaños [NOMBRE]! 🎉🎂

En este día tan especial queremos desearte toda la felicidad del mundo. Que este nuevo año de vida esté lleno de alegría, salud y muchas bendiciones.

🎁 Como agradecimiento por confiar en nosotros, te recordamos que siempre tendrás un descuento especial en tu próxima visita.

¡Que disfrutes mucho tu día! 🥳✨

Con cariño,
El equipo de Óptica' }}</textarea>
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
    <div class="modal-dialog" role="document">
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

function mostrarModalMensaje(pacienteId, nombrePaciente) {
    $('#pacienteId').val(pacienteId);
    $('#nombrePaciente').text(nombrePaciente);
    
    // Mensaje predeterminado personalizado para Chile
    let mensajePredeterminado = $('#mensajePredeterminado').val();
    if (!mensajePredeterminado || mensajePredeterminado.trim() === '') {
        mensajePredeterminado = `¡Feliz cumpleaños ${nombrePaciente}! 🎉🎂

En este día tan especial queremos desearte toda la felicidad del mundo. Que este nuevo año de vida esté lleno de alegría, salud y muchas bendiciones.

🎁 Como agradecimiento por confiar en nosotros, te recordamos que siempre tendrás un descuento especial en tu próxima visita.

¡Que disfrutes mucho tu día! 🥳✨

Con cariño,
El equipo de Óptica`;
    }
    
    $('#mensajePersonalizado').val(mensajePredeterminado);
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
                text: 'El mensaje predeterminado ha sido actualizado.',
                timer: 2000,
                timerProgressBar: true
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
    const celularRow = boton.closest('tr');
    const celularBadge = celularRow.find('.badge-success');
    
    if (celularBadge.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró un número de teléfono válido para este paciente.'
        });
        return;
    }
    
    const celular = celularBadge.text().replace(/[^\d]/g, '');
    
    // Deshabilitar botón temporalmente
    const botonEnviar = $('#enviarMensajeModal .btn-success');
    botonEnviar.prop('disabled', true);

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
            // Actualizar el botón inmediatamente
            boton.removeClass('btn-success')
                 .addClass('btn-warning')
                 .html('<i class="fab fa-whatsapp"></i> VOLVER A ENVIAR');
            
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
            
            // Cerrar el modal
            $('#enviarMensajeModal').modal('hide');
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡WhatsApp Abierto!',
                text: 'Se ha abierto WhatsApp con el mensaje de felicitación.',
                timer: 3000,
                timerProgressBar: true
            });
        },
        error: function(xhr) {
            let errorMessage = 'Error al enviar el mensaje';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            
            // Si requiere confirmación para reenviar en el mismo mes
            if (xhr.responseJSON && xhr.responseJSON.requiere_confirmacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: errorMessage,
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
                                // Generar URL de WhatsApp optimizada
                                const whatsappURL = generateWhatsAppURL(celular, mensaje);
                                
                                // Abrir WhatsApp
                                const whatsappWindow = window.open(whatsappURL, '_blank');
                                
                                // Verificar si se abrió correctamente
                                setTimeout(() => {
                                    if (!whatsappWindow || whatsappWindow.closed) {
                                        const alternativeURL = `https://web.whatsapp.com/send?phone=${formatChileanPhone(celular)}&text=${encodeURIComponent(mensaje)}`;
                                        window.open(alternativeURL, '_blank');
                                    }
                                }, 1000);
                                
                                // Cerrar el modal
                                $('#enviarMensajeModal').modal('hide');
                                
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡WhatsApp Abierto!',
                                    text: 'Se ha abierto WhatsApp con el mensaje de felicitación.',
                                    timer: 3000,
                                    timerProgressBar: true
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
                text: errorMessage
            });
        },
        complete: function() {
            // Rehabilitar botón
            botonEnviar.prop('disabled', false);
        }
    });
}

// Cargar mensaje predeterminado al iniciar la página
$(document).ready(function() {
    // Agregar el token CSRF si no existe
    if (!$('meta[name="csrf-token"]').length) {
        $('head').append('<meta name="csrf-token" content="{{ csrf_token() }}">');
    }
});
</script>
@stop 