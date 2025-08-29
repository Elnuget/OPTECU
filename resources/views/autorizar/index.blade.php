@extends('adminlte::page')
@section('title', 'Autorizar Factura')

@section('content_header')
<h1>Autorizar Factura #{{ $factura->id }}</h1>
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<!-- Información de la factura -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-certificate"></i> Información de Autorización
                </h3>
                <div class="card-tools">
                    @if($factura->clave_acceso && in_array($factura->estado, ['FIRMADA', 'ENVIADA', 'RECIBIDA']))
                        <button type="button" 
                                class="btn btn-sm btn-warning mr-2" 
                                onclick="autorizarFactura({{ $factura->id }})"
                                id="btnAutorizar">
                            <i class="fas fa-sync-alt"></i> Consultar Autorización SRI
                        </button>
                    @endif
                    <a href="{{ route('facturas.show', $factura->id) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Factura
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>ID Factura:</strong> {{ $factura->id }}
                    </div>
                    <div class="col-md-3">
                        <strong>Estado Actual:</strong> 
                        @php
                            $estados = [
                                'CREADA' => ['class' => 'secondary', 'icon' => 'file', 'text' => 'Creada'],
                                'FIRMADA' => ['class' => 'info', 'icon' => 'certificate', 'text' => 'Firmada'],
                                'ENVIADA' => ['class' => 'warning', 'icon' => 'paper-plane', 'text' => 'Enviada'],
                                'RECIBIDA' => ['class' => 'primary', 'icon' => 'inbox', 'text' => 'Recibida'],
                                'AUTORIZADA' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Autorizada'],
                                'DEVUELTA' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Devuelta'],
                                'NO_AUTORIZADA' => ['class' => 'dark', 'icon' => 'ban', 'text' => 'No Autorizada']
                            ];
                            $estadoInfo = $estados[$factura->estado ?? 'CREADA'] ?? $estados['CREADA'];
                        @endphp
                        <span class="badge badge-{{ $estadoInfo['class'] }}">
                            <i class="fas fa-{{ $estadoInfo['icon'] }}"></i> {{ $estadoInfo['text'] }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <strong>Declarante:</strong> {{ $factura->declarante->nombre ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong> 
                        @if($factura->created_at)
                            {{ $factura->created_at->format('d/m/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clave de Acceso -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key"></i> Clave de Acceso
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="clave_acceso">Clave de Acceso de la Factura:</label>
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="clave_acceso" 
                               value="{{ $factura->clave_acceso ?? 'No disponible' }}" 
                               readonly>
                        <div class="input-group-append">
                            @if($factura->clave_acceso)
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="copiarClaveAcceso()"
                                        title="Copiar al portapapeles">
                                    <i class="fas fa-copy"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    @if($factura->clave_acceso)
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Esta es la clave de acceso única generada para esta factura.
                        </small>
                    @else
                        <small class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            La clave de acceso no ha sido generada aún para esta factura.
                        </small>
                    @endif
                </div>
                
                @if($factura->numero_autorizacion)
                <div class="form-group">
                    <label for="numero_autorizacion">Número de Autorización SRI:</label>
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="numero_autorizacion" 
                               value="{{ $factura->numero_autorizacion }}" 
                               readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="copiarAutorizacion()"
                                    title="Copiar al portapapeles">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-success">
                        <i class="fas fa-check-circle"></i> 
                        Factura autorizada por el SRI.
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Información adicional de autorización -->
@if($factura->estado_sri || $factura->fecha_autorizacion || $factura->mensajes_sri)
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info"></i> Información del SRI
                </h3>
            </div>
            <div class="card-body">
                @if($factura->estado_sri)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Estado SRI:</strong>
                        <span class="badge badge-{{ $factura->estado_sri === 'AUTORIZADA' ? 'success' : ($factura->estado_sri === 'RECIBIDA' ? 'primary' : 'danger') }}">
                            {{ $factura->estado_sri }}
                        </span>
                    </div>
                    @if($factura->fecha_autorizacion)
                    <div class="col-md-8">
                        <strong>Fecha de Autorización:</strong>
                        @if(is_object($factura->fecha_autorizacion) && method_exists($factura->fecha_autorizacion, 'format'))
                            {{ $factura->fecha_autorizacion->format('d/m/Y H:i:s') }}
                        @else
                            {{ $factura->fecha_autorizacion }}
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                
                @if($factura->mensajes_sri)
                <div class="alert alert-info">
                    <h6><i class="fas fa-comments"></i> Mensajes del SRI:</h6>
                    @php
                        $mensajes = $factura->mensajes_sri_procesados ?? (
                            is_string($factura->mensajes_sri) ? json_decode($factura->mensajes_sri, true) : $factura->mensajes_sri
                        );
                        if (!is_array($mensajes)) $mensajes = [$factura->mensajes_sri];
                    @endphp
                    <ul class="mb-0">
                        @foreach($mensajes as $mensaje)
                            @if(is_array($mensaje))
                                <li>
                                    <strong>{{ $mensaje['mensaje'] ?? 'Mensaje sin descripción' }}</strong>
                                    @if(isset($mensaje['identificador']))
                                        <br><small>Código: {{ $mensaje['identificador'] }}</small>
                                    @endif
                                    @if(isset($mensaje['informacionAdicional']))
                                        <br><small>Detalle: {{ $mensaje['informacionAdicional'] }}</small>
                                    @endif
                                </li>
                            @else
                                <li>{{ is_string($mensaje) ? $mensaje : 'Mensaje no válido' }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- XML Autorizado -->
@if($factura->estado === 'AUTORIZADA' && $factura->xml_autorizado)
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-code"></i> XML Autorizado por el SRI
                </h3>
                <div class="card-tools">
                    <button type="button" 
                            class="btn btn-sm btn-success" 
                            onclick="descargarXMLAutorizado()"
                            title="Descargar XML autorizado">
                        <i class="fas fa-download"></i> Descargar XML
                    </button>
                    <button type="button" 
                            class="btn btn-sm btn-info" 
                            onclick="copiarXMLAutorizado()"
                            title="Copiar XML al portapapeles">
                        <i class="fas fa-copy"></i> Copiar XML
                    </button>
                    <button class="btn btn-tool" type="button" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Factura Autorizada:</strong> Este XML ha sido oficialmente autorizado por el SRI y contiene la firma digital válida.
                </div>
                
                <div class="form-group">
                    <label for="xml_autorizado_content">Contenido del XML Autorizado:</label>
                    <textarea id="xml_autorizado_content" 
                              class="form-control" 
                              rows="15" 
                              readonly 
                              style="font-family: monospace; font-size: 12px;">{{ $factura->xml_autorizado }}</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Tamaño del XML:</strong> {{ number_format(strlen($factura->xml_autorizado)) }} caracteres
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-certificate"></i> 
                            <strong>Estado:</strong> Autorizado por SRI
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@stop

@section('css')
<style>
    .card-outline.card-warning {
        border-top: 3px solid #ffc107;
    }
    
    .card-outline.card-info {
        border-top: 3px solid #17a2b8;
    }
    
    .card-outline.card-success {
        border-top: 3px solid #28a745;
    }
    
    .input-group .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #ced4da;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }
</style>
@stop

@section('js')
<script>
    function autorizarFactura(facturaId) {
        const btnAutorizar = document.getElementById('btnAutorizar');
        const iconoOriginal = btnAutorizar.innerHTML;
        
        // Deshabilitar botón y mostrar spinner
        btnAutorizar.disabled = true;
        btnAutorizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Consultando...';
        
        // Realizar petición AJAX
        fetch(`/autorizar/${facturaId}/consultar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensaje según el estado
                if (data.data.estado === 'AUTORIZADA') {
                    alert('¡Factura autorizada exitosamente!\n\nNúmero de autorización: ' + data.data.numeroAutorizacion);
                    // Recargar la página para mostrar los nuevos datos
                    window.location.reload();
                } else if (data.data.estado === 'EN_PROCESO') {
                    alert('La factura está en proceso de autorización. Intente consultar nuevamente en unos minutos.');
                } else if (data.data.estado === 'DEVUELTA') {
                    // Verificar si es un caso de "no encontrada" basándose en los mensajes
                    let esNoEncontrada = false;
                    if (data.data.mensajes && data.data.mensajes.length > 0) {
                        esNoEncontrada = data.data.mensajes.some(mensaje => 
                            mensaje.identificador === 'NO_ENCONTRADA'
                        );
                    }
                    
                    if (esNoEncontrada) {
                        alert('No se encontró información de autorización para esta factura.\n\n' +
                              'Posibles causas:\n' +
                              '• La factura no ha sido enviada al SRI\n' +
                              '• La clave de acceso no es válida\n' +
                              '• El SRI aún no ha procesado la factura');
                    } else {
                        // Mostrar mensajes de error del SRI
                        let mensajeCompleto = 'La factura fue devuelta por el SRI.\n\nEstado: ' + data.data.estado;
                        
                        if (data.data.mensajes && data.data.mensajes.length > 0) {
                            mensajeCompleto += '\n\nMensajes del SRI:';
                            data.data.mensajes.forEach((mensaje, index) => {
                                mensajeCompleto += '\n' + (index + 1) + '. ' + mensaje.mensaje;
                                if (mensaje.informacionAdicional) {
                                    mensajeCompleto += '\n   Detalle: ' + mensaje.informacionAdicional;
                                }
                            });
                        }
                        
                        alert(mensajeCompleto);
                    }
                } else {
                    // Otros estados (NO_AUTORIZADA, etc.)
                    let mensajeCompleto = 'La factura no ha sido autorizada.\n\nEstado: ' + data.data.estado;
                    
                    if (data.data.mensajes && data.data.mensajes.length > 0) {
                        mensajeCompleto += '\n\nMensajes del SRI:';
                        data.data.mensajes.forEach((mensaje, index) => {
                            mensajeCompleto += '\n' + (index + 1) + '. ' + mensaje.mensaje;
                            if (mensaje.informacionAdicional) {
                                mensajeCompleto += '\n   Detalle: ' + mensaje.informacionAdicional;
                            }
                        });
                    }
                    
                    alert(mensajeCompleto);
                }
            } else {
                alert('Error al consultar autorización: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al consultar la autorización. Por favor, intente nuevamente.');
        })
        .finally(() => {
            // Restaurar botón
            btnAutorizar.disabled = false;
            btnAutorizar.innerHTML = iconoOriginal;
        });
    }
    
    function copiarClaveAcceso() {
        const claveAcceso = document.getElementById('clave_acceso');
        if (claveAcceso && claveAcceso.value && claveAcceso.value !== 'No disponible') {
            navigator.clipboard.writeText(claveAcceso.value).then(function() {
                // Mostrar feedback visual
                const boton = event.target.closest('button');
                const iconoOriginal = boton.innerHTML;
                boton.innerHTML = '<i class="fas fa-check text-success"></i>';
                boton.classList.add('btn-success');
                boton.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    boton.innerHTML = iconoOriginal;
                    boton.classList.remove('btn-success');
                    boton.classList.add('btn-outline-secondary');
                }, 1500);
                
                // Mostrar toast o alerta
                alert('Clave de acceso copiada al portapapeles');
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                alert('Error al copiar la clave de acceso');
            });
        } else {
            alert('No hay clave de acceso disponible para copiar');
        }
    }
    
    function copiarAutorizacion() {
        const numeroAutorizacion = document.getElementById('numero_autorizacion');
        if (numeroAutorizacion && numeroAutorizacion.value) {
            navigator.clipboard.writeText(numeroAutorizacion.value).then(function() {
                // Mostrar feedback visual
                const boton = event.target.closest('button');
                const iconoOriginal = boton.innerHTML;
                boton.innerHTML = '<i class="fas fa-check text-success"></i>';
                boton.classList.add('btn-success');
                boton.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    boton.innerHTML = iconoOriginal;
                    boton.classList.remove('btn-success');
                    boton.classList.add('btn-outline-secondary');
                }, 1500);
                
                // Mostrar toast o alerta
                alert('Número de autorización copiado al portapapeles');
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                alert('Error al copiar el número de autorización');
            });
        }
    }
    
    function descargarXMLAutorizado() {
        const xmlContent = document.getElementById('xml_autorizado_content');
        if (xmlContent && xmlContent.value) {
            const blob = new Blob([xmlContent.value], { type: 'application/xml' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'factura_{{ $factura->id }}_autorizada_sri.xml';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            alert('XML autorizado descargado exitosamente');
        } else {
            alert('No hay XML autorizado disponible para descargar');
        }
    }
    
    function copiarXMLAutorizado() {
        const xmlContent = document.getElementById('xml_autorizado_content');
        if (xmlContent && xmlContent.value) {
            navigator.clipboard.writeText(xmlContent.value).then(function() {
                // Mostrar feedback visual en el botón
                const boton = event.target.closest('button');
                const iconoOriginal = boton.innerHTML;
                boton.innerHTML = '<i class="fas fa-check text-success"></i> Copiado';
                boton.classList.add('btn-success');
                boton.classList.remove('btn-info');
                
                setTimeout(function() {
                    boton.innerHTML = iconoOriginal;
                    boton.classList.remove('btn-success');
                    boton.classList.add('btn-info');
                }, 2000);
                
                alert('XML autorizado copiado al portapapeles');
            }).catch(function(err) {
                console.error('Error al copiar XML: ', err);
                alert('Error al copiar el XML autorizado');
            });
        } else {
            alert('No hay XML autorizado disponible para copiar');
        }
    }
</script>
@stop
