@extends('adminlte::page')
@section('title', 'Autorizar Factura')

@section('content_header')
<h1>Autorizar Factura #{{ $factura->id }}</h1>
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
</script>
@stop
