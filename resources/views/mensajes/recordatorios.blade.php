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
                                    <label for="anio" class="form-label">AÑO:</label>
                                    <select name="anio" id="anio" class="form-control">
                                        @for($anio = now()->addYear()->year; $anio >= now()->subYears(2)->year; $anio--)
                                            <option value="{{ $anio }}" {{ request('anio', date('Y')) == $anio ? 'selected' : '' }}>
                                                {{ $anio }}
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
        @if(request()->hasAny(['empresa_id', 'mes', 'anio']))
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
                    @if(request('anio'))
                        <span class="badge badge-warning ml-1">
                            AÑO: {{ request('anio') }}
                        </span>
                    @endif
                    <span class="float-right">
                        <strong>{{ $consultas->count() }} resultado(s) encontrado(s)</strong>
                    </span>
                </div>
            </div>
        </div>
        @endif
        
        @if($consultas->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                @if(request()->hasAny(['empresa_id', 'mes', 'anio']))
                    NO SE ENCONTRARON CONSULTAS PROGRAMADAS CON LOS FILTROS APLICADOS.
                    <a href="{{ route('mensajes.recordatorios') }}" class="btn btn-sm btn-primary ml-2">
                        <i class="fas fa-refresh"></i> VER TODOS
                    </a>
                @else
                    NO HAY CONSULTAS PROGRAMADAS PARA {{ $mes_actual }}.
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table id="consultasTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA CONSULTA</th>
                            <th>NOMBRES</th>
                            <th>APELLIDOS</th>
                            <th>SUCURSAL</th>
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
                                @if(isset($consulta['empresa_nombre']))
                                    <span class="badge badge-secondary">
                                        {{ strtoupper($consulta['empresa_nombre']) }}
                                    </span>
                                @else
                                    <span class="badge badge-light">NO ASIGNADA</span>
                                @endif
                            </td>
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
                        <textarea class="form-control" id="mensajePredeterminado" rows="6">{{ MensajePredeterminado::obtenerMensaje('consulta') ?: 'Estimado/a [NOMBRE] 👋

Le recordamos que tiene una cita programada en nuestra óptica para el [FECHA].

📅 *Detalles de su cita:*
• Fecha: [FECHA]
• Motivo: Control oftalmológico

Por favor, confirme su asistencia respondiendo a este mensaje.

Si necesita reagendar su cita, no dude en contactarnos.

¡Le esperamos! 👓✨

Saludos cordiales,
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
<script src="{{ asset('js/sucursal-cache.js') }}"></script>
<script>
// Función para detectar si es dispositivo móvil
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Función para limpiar y formatear número de teléfono ecuatoriano
function formatEcuadorianPhone(phone) {
    // Remover todos los caracteres no numéricos
    let cleanPhone = phone.replace(/\D/g, '');
    
    // Si empieza con 593 (código de Ecuador), mantenerlo
    if (cleanPhone.startsWith('593')) {
        return cleanPhone;
    }
    
    // Si empieza con 09 (celular ecuatoriano), agregar código de país
    if (cleanPhone.startsWith('09') && cleanPhone.length === 10) {
        return '593' + cleanPhone.substring(1); // Quitar el 0 inicial
    }
    
    // Si empieza con 9 (celular ecuatoriano sin el 0), agregar código de país
    if (cleanPhone.startsWith('9') && cleanPhone.length === 9) {
        return '593' + cleanPhone;
    }
    
    // Si tiene 8 dígitos y empieza con números válidos de Ecuador, agregar código completo
    if (cleanPhone.length === 8 && /^[2-7]/.test(cleanPhone)) {
        return '593' + cleanPhone;
    }
    
    // Si no cumple ningún patrón, devolver tal como está para validación posterior
    return cleanPhone;
}

// Función para generar URL de WhatsApp más segura
function generateWhatsAppURL(phoneNumber, message) {
    const formattedPhone = formatEcuadorianPhone(phoneNumber);
    const encodedMessage = encodeURIComponent(message);
    
    if (isMobileDevice()) {
        // Para móviles, usar el esquema whatsapp://
        return `whatsapp://send?phone=${formattedPhone}&text=${encodedMessage}`;
    } else {
        // Para escritorio, usar WhatsApp Web con api.whatsapp.com (más confiable)
        return `https://api.whatsapp.com/send?phone=${formattedPhone}&text=${encodedMessage}`;
    }
}

function mostrarModalMensaje(pacienteId, nombrePaciente, fechaConsulta) {
    $('#pacienteId').val(pacienteId);
    $('#nombrePaciente').text(nombrePaciente);
    
    // Crear mensaje personalizado para Ecuador
    let mensaje = $('#mensajePredeterminado').val();
    if (!mensaje || mensaje.trim() === '') {
        mensaje = `Estimado/a ${nombrePaciente} 👋

Le recordamos que tiene una cita programada en nuestra óptica para el ${fechaConsulta}.

📅 *Detalles de su cita:*
• Fecha: ${fechaConsulta}
• Motivo: Control oftalmológico

Por favor, confirme su asistencia respondiendo a este mensaje.

Si necesita reagendar su cita, no dude en contactarnos.

¡Le esperamos! 👓✨

Saludos cordiales,
El equipo de Óptica`;
    } else {
        mensaje = mensaje
            .replace('[NOMBRE]', nombrePaciente)
            .replace('[FECHA]', fechaConsulta);
    }
    
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
    
    // Validar mensaje
    if (!mensaje || mensaje.trim() === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor, ingrese un mensaje antes de enviar.'
        });
        return;
    }
    
    // Obtener el número de teléfono del paciente de la tabla
    const celularRow = $(`button[data-paciente-id="${pacienteId}"]`).closest('tr');
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
            
            // Generar URL de WhatsApp optimizada
            const whatsappURL = generateWhatsAppURL(celular, mensaje);
            
            // Abrir WhatsApp
            const whatsappWindow = window.open(whatsappURL, '_blank');
            
            // Verificar si se abrió correctamente y ofrecer alternativa
            setTimeout(() => {
                if (!whatsappWindow || whatsappWindow.closed) {
                    // Si no se abrió, intentar con URL alternativa
                    const alternativeURL = `https://web.whatsapp.com/send?phone=${formatEcuadorianPhone(celular)}&text=${encodeURIComponent(mensaje)}`;
                    window.open(alternativeURL, '_blank');
                }
            }, 1000);
            
            // Cerrar el modal
            $('#enviarMensajeModal').modal('hide');
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡WhatsApp Abierto!',
                text: 'Se ha abrido WhatsApp con el mensaje de recordatorio.',
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
                                tipo: 'consulta',
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
                                        const alternativeURL = `https://web.whatsapp.com/send?phone=${formatEcuadorianPhone(celular)}&text=${encodeURIComponent(mensaje)}`;
                                        window.open(alternativeURL, '_blank');
                                    }
                                }, 1000);
                                
                                // Cerrar el modal
                                $('#enviarMensajeModal').modal('hide');
                                
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡WhatsApp Abierto!',
                                    text: 'Se ha abierto WhatsApp con el mensaje de recordatorio.',
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
    
    // Preseleccionar sucursal desde caché y aplicar automáticamente
    if (window.SucursalCache) {
        // Verificar si ya hay parámetros de filtro en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const filtrosAplicados = urlParams.has('empresa_id') || urlParams.has('mes') || urlParams.has('anio');
        
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
        window.location.href = '{{ route("mensajes.recordatorios") }}?todos=1';
    });

    // Botón Limpiar Filtros
    $('#limpiarFiltrosButton').click(function() {
        $('#empresa_id').val('');
        $('#mes').val('{{ date("n") }}'); // Establecer mes actual como predeterminado
        $('#anio').val('{{ date("Y") }}'); // Establecer año actual como predeterminado
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