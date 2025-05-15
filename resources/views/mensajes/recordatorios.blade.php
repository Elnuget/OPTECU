@extends('adminlte::page')

@section('title', 'RECORDATORIOS DE CONSULTA')

@php
use App\Models\MensajePredeterminado;
@endphp

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>RECORDATORIOS DE CONSULTAS - {{ strtoupper($mes_actual) }}</h1>
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
        <h3 class="card-title">CONSULTAS PROGRAMADAS PARA {{ strtoupper($mes_actual) }}</h3>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editarMensajeModal">
            <i class="fas fa-edit"></i> EDITAR MENSAJE PREDETERMINADO
        </button>
    </div>
    <div class="card-body">
        @if($consultas->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> NO HAY CONSULTAS PROGRAMADAS PARA {{ $mes_actual }}.
            </div>
        @else
            <div class="table-responsive">
                <table id="consultasTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA CONSULTA</th>
                            <th>NOMBRES</th>
                            <th>APELLIDOS</th>
                            <th>DÍAS RESTANTES</th>
                            <th>CELULAR</th>
                            <th>ÚLTIMA CONSULTA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consultas as $consulta)
                        <tr>
                            <td>
                                <span class="badge badge-primary" style="font-size: 1em;">
                                    {{ $consulta['fecha_consulta'] }}
                                </span>
                            </td>
                            <td>{{ strtoupper($consulta['nombres']) }}</td>
                            <td>{{ strtoupper($consulta['apellidos']) }}</td>
                            <td>
                                <span class="badge {{ $consulta['dias_restantes'] <= 3 ? 'badge-danger' : 'badge-info' }}" style="font-size: 0.9em;">
                                    {{ $consulta['dias_restantes'] }} DÍAS
                                </span>
                            </td>
                            <td>
                                @if($consulta['celular'])
                                    <span class="badge badge-success">
                                        <i class="fas fa-phone"></i> {{ $consulta['celular'] }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">SIN CELULAR</span>
                                @endif
                            </td>
                            <td>{{ $consulta['ultima_consulta'] }}</td>
                            <td>
                                <div class="btn-group">
                                    @if($consulta['celular'])
                                        @php
                                            $mesActual = now()->format('Y-m');
                                            $mensajeEnviado = \App\Models\MensajesEnviados::where('historial_id', $consulta['id'])
                                                ->where('tipo', 'consulta')
                                                ->whereRaw('DATE_FORMAT(fecha_envio, "%Y-%m") = ?', [$mesActual])
                                                ->exists();
                                        @endphp
                                        
                                        <button type="button" 
                                            class="btn {{ $mensajeEnviado ? 'btn-warning' : 'btn-success' }} btn-sm btn-enviar-mensaje"
                                            data-paciente-id="{{ $consulta['id'] }}"
                                            onclick="mostrarModalMensaje({{ $consulta['id'] }}, '{{ $consulta['nombres'] }}', '{{ $consulta['fecha_consulta'] }}')">
                                            <i class="fab fa-whatsapp"></i> 
                                            {{ $mensajeEnviado ? 'VOLVER A ENVIAR' : 'ENVIAR RECORDATORIO' }}
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
<div class="modal fade" id="editarMensajeModal" tabindex="-1" role="dialog">
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
                        <label>MENSAJE DE RECORDATORIO:</label>
                        <textarea class="form-control" id="mensajePredeterminado" rows="6">{{ MensajePredeterminado::obtenerMensaje('consulta') ?: 'Estimado/a [NOMBRE],

Le recordamos su cita programada para el [FECHA].

Por favor, confirme su asistencia.

¡Le esperamos!' }}</textarea>
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
                <h5 class="modal-title">ENVIAR RECORDATORIO DE CONSULTA</h5>
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
function mostrarModalMensaje(pacienteId, nombrePaciente, fechaConsulta) {
    $('#pacienteId').val(pacienteId);
    $('#nombrePaciente').text(nombrePaciente);
    let mensaje = $('#mensajePredeterminado').val()
        .replace('[NOMBRE]', nombrePaciente)
        .replace('[FECHA]', fechaConsulta);
    $('#mensajePersonalizado').val(mensaje);
    $('#enviarMensajeModal').modal('show');
}

function guardarMensajePredeterminado() {
    const mensaje = $('#mensajePredeterminado').val();
    
    // Guardar mensaje en la base de datos
    $.ajax({
        url: '/configuraciones/mensajes-predeterminados',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            tipo: 'consulta',
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
    
    // Obtener el número de teléfono del paciente de la tabla
    const celularRow = $(`button[data-paciente-id="${pacienteId}"]`).closest('tr');
    const celular = celularRow.find('.badge-success').text().replace(/[^\d]/g, '');
    
    // Validar que exista el número de teléfono
    if (!celular) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró un número de teléfono válido'
        });
        return;
    }

    // Registrar el mensaje como enviado
    $.ajax({
        url: `/historiales_clinicos/${pacienteId}/enviar-mensaje`,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            mensaje: mensaje,
            tipo: 'consulta',
            forzar_envio: false
        },
        success: function(response) {
            // Actualizar el botón inmediatamente
            const boton = $(`button[data-paciente-id="${pacienteId}"]`);
            boton.removeClass('btn-success')
                .addClass('btn-warning')
                .html('<i class="fab fa-whatsapp"></i> VOLVER A ENVIAR');
            
            // Abrir WhatsApp en nueva pestaña
            window.open(response.url, '_blank');
            
            // Cerrar el modal
            $('#enviarMensajeModal').modal('hide');
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡Mensaje Enviado!',
                text: 'Se ha abierto WhatsApp Web con el mensaje.'
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
                                tipo: 'consulta',
                                forzar_envio: true
                            },
                            success: function(response) {
                                // Abrir WhatsApp en nueva pestaña
                                window.open(response.url, '_blank');
                                
                                // Cerrar el modal
                                $('#enviarMensajeModal').modal('hide');
                                
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Mensaje Enviado!',
                                    text: 'Se ha abierto WhatsApp Web con el mensaje.'
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
    // No necesitamos verificar mensajes enviados aquí ya que lo hacemos en el servidor con @php
});
</script>
@stop 