@extends('adminlte::page')
@section('title', 'Ver Factura XML')

@section('content_header')
<h1>Factura #{{ $factura->id }} - XML</h1>
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<!-- Información de la factura -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Información de la Factura
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>ID:</strong> {{ $factura->id }}
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong> 
                        @if($factura->created_at)
                            {{ $factura->created_at->format('d/m/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="col-md-3">
                        <strong>Declarante:</strong> {{ $factura->declarante->nombre ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong> 
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
                </div>
                @if($factura->estado_sri || $factura->numero_autorizacion)
                <hr>
                <div class="row">
                    @if($factura->estado_sri)
                    <div class="col-md-3">
                        <strong>Estado SRI:</strong> 
                        <span class="badge badge-{{ $factura->estado_sri === 'AUTORIZADA' ? 'success' : ($factura->estado_sri === 'RECIBIDA' ? 'primary' : 'danger') }}">
                            {{ $factura->estado_sri }}
                        </span>
                    </div>
                    @endif
                    @if($factura->numero_autorizacion)
                    <div class="col-md-6">
                        <strong>Autorización SRI:</strong> {{ $factura->numero_autorizacion }}
                    </div>
                    @endif
                    @if($factura->fecha_autorizacion)
                    <div class="col-md-3">
                        <strong>Fecha Autorización:</strong> 
                        @if(is_object($factura->fecha_autorizacion) && method_exists($factura->fecha_autorizacion, 'format'))
                            {{ $factura->fecha_autorizacion->format('d/m/Y H:i') }}
                        @else
                            {{ $factura->fecha_autorizacion }}
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                @if($factura->mensajes_sri)
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Mensajes del SRI:</strong>
                        <div class="alert alert-dark mt-2" style="color: white; background-color: #343a40; border-color: #495057; max-height: none;">
                            @php
                                $mensajes = $factura->mensajes_sri_procesados ?? (
                                    is_string($factura->mensajes_sri) ? json_decode($factura->mensajes_sri, true) : $factura->mensajes_sri
                                );
                                if (!is_array($mensajes)) $mensajes = [$factura->mensajes_sri];
                            @endphp
                            @foreach($mensajes as $mensaje)
                                <div class="mb-3 p-3" style="border-left: 4px solid #ffffff; background-color: rgba(255,255,255,0.1); border-radius: 4px;">
                                    @if(is_array($mensaje))
                                        <div style="color: white; font-weight: bold; font-size: 16px; margin-bottom: 8px;">
                                            • {{ $mensaje['mensaje'] ?? 'Mensaje sin descripción' }}
                                        </div>
                                        @if(isset($mensaje['identificador']))
                                            <div style="color: #f8f9fa; font-size: 13px; margin-bottom: 6px;">
                                                <strong>Código:</strong> {{ $mensaje['identificador'] }}
                                            </div>
                                        @endif
                                        @if(isset($mensaje['informacionAdicional']))
                                            <div style="color: #f8f9fa; font-size: 13px; margin-bottom: 8px; 
                                                       word-wrap: break-word; 
                                                       white-space: pre-wrap; 
                                                       overflow-wrap: break-word; 
                                                       max-width: 100%; 
                                                       line-height: 1.4;">
                                                <strong>Detalle:</strong> {{ $mensaje['informacionAdicional'] }}
                                            </div>
                                        @endif
                                        @if(isset($mensaje['tipo']))
                                            <div style="margin-top: 10px;">
                                                <span class="badge badge-{{ $mensaje['tipo'] === 'ERROR' ? 'danger' : 'warning' }}" style="font-size: 12px; padding: 4px 8px;">
                                                    {{ $mensaje['tipo'] }}
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <div style="color: white; font-weight: bold; font-size: 16px; 
                                                   word-wrap: break-word; 
                                                   white-space: pre-wrap; 
                                                   overflow-wrap: break-word;">
                                            • {{ is_string($mensaje) ? $mensaje : 'Mensaje no válido' }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @if($factura->observaciones)
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Observaciones:</strong>
                        <div class="alert alert-warning mt-2" style="margin-bottom: 0;">
                            <div style="word-wrap: break-word; 
                                       white-space: pre-wrap; 
                                       overflow-wrap: break-word; 
                                       max-width: 100%; 
                                       line-height: 1.4;">
                                <i class="fas fa-exclamation-triangle"></i> {{ $factura->observaciones }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-code"></i> Contenido del XML
            @if(isset($xmlType))
                <span class="badge badge-info ml-2">
                    @if($xmlType === 'autorizado')
                        <i class="fas fa-certificate"></i> XML Autorizado
                    @elseif($xmlType === 'firmado')
                        <i class="fas fa-signature"></i> XML Firmado
                    @else
                        <i class="fas fa-file-code"></i> XML Original
                    @endif
                </span>
                @if($xmlType === 'firmado')
                    <small class="text-muted ml-2">
                        <i class="fas fa-info-circle"></i> Mostrando XML firmado (prioridad sobre otros tipos)
                    </small>
                @endif
            @endif
        </h3>
        <div class="card-tools">
            {{-- Botones de Copiar y Descargar removidos por solicitud del usuario --}}
            
            {{-- Mostrar botón de autorizar siempre --}}
            <a href="{{ route('autorizar.index', $factura->id) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-certificate"></i> Autorizar
            </a>
            
            {{-- Mostrar badges informativos según el estado --}}
            @if($factura->estado === 'DEVUELTA')
                <span class="badge badge-warning ml-2">
                    <i class="fas fa-exclamation-triangle"></i> Devuelta por SRI - Requiere autorización
                </span>
            @elseif($factura->estado === 'AUTORIZADA')
                <span class="badge badge-success ml-2">
                    <i class="fas fa-check-circle"></i> Autorizada
                </span>
            @elseif($factura->estado === 'NO_AUTORIZADA')
                <span class="badge badge-dark ml-2">
                    <i class="fas fa-ban"></i> No Autorizada por el SRI
                </span>
            @endif
            
            <a href="{{ route('facturas.pdf', $factura->id) }}" class="btn btn-sm btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            
            <a href="{{ route('facturas.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($xmlContent)
            <div style="max-height: 600px; overflow-y: auto; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px;">
                <pre style="margin: 0; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.4; white-space: pre-wrap; word-wrap: break-word;" id="xmlContent">{{ $xmlContent }}</pre>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se pudo cargar el contenido del archivo XML.
            </div>
        @endif
    </div>
</div>

<!-- Modal para contraseña del certificado - REMOVIDO por solicitud del usuario -->

<!-- Modal de Autorización -->
<div class="modal fade" id="modalAutorizacion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Solicitar Autorización al SRI
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="confirmar_autorizacion" required>
                        <label class="custom-control-label" for="confirmar_autorizacion">
                            Confirmo que deseo solicitar la autorización de este comprobante al SRI
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        Se enviará una solicitud de autorización al SRI para este comprobante.
                    </small>
                </div>

                <!-- Área de progreso -->
                <div id="progreso_autorizacion" style="display: none;">
                    <div class="text-center mb-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="progress-text mt-2"><strong>Iniciando proceso...</strong></div>
                    </div>
                    <div class="text-muted text-center">
                        <small id="estado_autorizacion">Preparando solicitud de autorización</small>
                    </div>
                </div>

                <!-- Área de resultado -->
                <div id="resultado_autorizacion" style="display: none;">
                    <div id="alert_resultado_autorizacion" class="alert">
                        <!-- El contenido se llenará dinámicamente -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-info" id="btn_autorizar" onclick="procesarAutorizacion()">
                    <i class="fas fa-check"></i> Solicitar Autorización
                </button>
                <button type="button" class="btn btn-success" id="btn_cerrar_autorizacion_exitoso" style="display: none;" data-dismiss="modal">
                    <i class="fas fa-check"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    /* Estilos simples para texto plano */
    .xml-container {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.4;
    }
    
    /* Estilos para el modal de certificado */
    #modalCertificado .modal-header {
        background-color: #ffc107;
        color: #212529;
        border-bottom: 1px solid #dee2e6;
    }
    
    #modalCertificado .modal-header .close {
        color: #212529;
        opacity: 0.8;
    }
    
    #modalCertificado .modal-header .close:hover {
        opacity: 1;
    }
    
    .progress-text {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        color: #fff;
        text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
    }
    
    .progress {
        position: relative;
        height: 30px;
    }
    
    #estado_proceso {
        font-style: italic;
        color: #6c757d;
    }
    
    .btn[disabled] {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* Animación para el botón de firmar */
    .btn-warning:not([disabled]):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    
    /* Estilos para alertas de resultado */
    #resultado_firma .alert {
        margin-bottom: 0;
    }
    
    #resultado_firma .alert h6 {
        margin-bottom: 10px;
        font-weight: bold;
    }
    
    #resultado_firma .alert p {
        margin-bottom: 5px;
    }
    
    #resultado_firma .alert hr {
        margin: 10px 0;
        border-top-color: rgba(0,0,0,0.2);
    }
    
    #resultado_firma .alert small {
        color: rgba(0,0,0,0.7);
    }
    
    #resultado_firma .alert ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    /* Estilo para cuando no hay certificado */
    .btn-secondary[disabled] {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .text-muted.d-block {
        font-size: 0.875em;
        margin-top: 4px;
    }
</style>
@stop

<!-- Librerías necesarias -->
@section('plugins.Tempusdominus', true)

@section('js')
<script>
    let facturaIdActual = null;
    let facturaIdAutorizacion = null;

    // Función copyXmlToClipboard() eliminada por solicitud del usuario

    // Función para procesar autorización directamente sin modal
    function procesarAutorizacionDirecta(facturaId) {
        console.log('Iniciando autorización directa para factura:', facturaId);
        
        // Mostrar indicador de proceso
        const estadoProceso = document.getElementById('estado_autorizacion_proceso');
        if (estadoProceso) {
            estadoProceso.style.display = 'block';
        }
        
        // Deshabilitar el botón
        const btnAutorizar = event.target;
        btnAutorizar.disabled = true;
        btnAutorizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Solicitando...';
        
        // Enviar solicitud directa
        fetch(`{{ url('/facturas') }}/${facturaId}/autorizar-sri`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta de autorización directa:', data);
            
            // Ocultar indicador de proceso
            if (estadoProceso) {
                estadoProceso.style.display = 'none';
            }
            
            if (data.success) {
                if (data.authorized) {
                    // Autorizada exitosamente
                    alert('¡Factura autorizada exitosamente!\n\n' + data.message);
                    window.location.reload(); // Recargar para mostrar nuevo estado
                } else {
                    // No autorizada pero proceso exitoso
                    let mensaje = 'La factura NO fue autorizada por el SRI:\n\n' + data.message;
                    if (data.data && data.data.motivo_rechazo) {
                        if (Array.isArray(data.data.motivo_rechazo)) {
                            mensaje += '\n\nMotivos:\n• ' + data.data.motivo_rechazo.join('\n• ');
                        } else {
                            mensaje += '\n\nMotivo: ' + data.data.motivo_rechazo;
                        }
                    }
                    alert(mensaje);
                    window.location.reload(); // Recargar para mostrar nuevo estado
                }
            } else {
                // Error en el proceso
                alert('Error al procesar autorización:\n\n' + data.message);
                // Rehabilitar botón para reintentar
                btnAutorizar.disabled = false;
                btnAutorizar.innerHTML = '<i class="fas fa-check"></i> Solicitar Autorización';
            }
        })
        .catch(error => {
            console.error('Error en autorización directa:', error);
            
            // Ocultar indicador de proceso
            if (estadoProceso) {
                estadoProceso.style.display = 'none';
            }
            
            alert('Error de conexión al solicitar autorización:\n\n' + error.message);
            
            // Rehabilitar botón para reintentar
            btnAutorizar.disabled = false;
            btnAutorizar.innerHTML = '<i class="fas fa-check"></i> Solicitar Autorización';
        });
    }

    // Función para mostrar modal de autorización
    function autorizarComprobante(facturaId) {
        facturaIdAutorizacion = facturaId;
        
        // Resetear formulario
        document.getElementById('confirmar_autorizacion').checked = false;
        document.getElementById('progreso_autorizacion').style.display = 'none';
        document.getElementById('resultado_autorizacion').style.display = 'none';
        document.getElementById('btn_autorizar').style.display = 'inline-block';
        document.getElementById('btn_cerrar_autorizacion_exitoso').style.display = 'none';
        
        // Mostrar modal
        $('#modalAutorizacion').modal('show');
    }
    // Función para procesar autorización
    function procesarAutorizacion() {
        const confirmar = document.getElementById('confirmar_autorizacion').checked;
        
        console.log('Iniciando proceso de autorización', {
            confirmado: confirmar,
            factura_id: facturaIdAutorizacion
        });
        
        // Validaciones
        if (!confirmar) {
            console.error('Error: No confirmado para autorización');
            alert('Debe confirmar que desea solicitar la autorización del comprobante');
            return;
        }
        
        // Ocultar botón y mostrar progreso
        document.getElementById('btn_autorizar').style.display = 'none';
        document.getElementById('progreso_autorizacion').style.display = 'block';
        
        // Simular progreso
        let progreso = 0;
        const progressBar = document.querySelector('#progreso_autorizacion .progress-bar');
        const progressText = document.querySelector('#progreso_autorizacion .progress-text');
        const estadoTexto = document.getElementById('estado_autorizacion');
        
        const intervalo = setInterval(() => {
            progreso += 10;
            if (progressBar) progressBar.style.width = progreso + '%';
            
            if (progreso <= 30) {
                if (progressText) progressText.textContent = 'Conectando con SRI...';
                if (estadoTexto) estadoTexto.textContent = 'Estableciendo conexión con el servicio de autorización';
            } else if (progreso <= 60) {
                if (progressText) progressText.textContent = 'Enviando solicitud...';
                if (estadoTexto) estadoTexto.textContent = 'Enviando solicitud de autorización';
            } else if (progreso <= 90) {
                if (progressText) progressText.textContent = 'Procesando...';
                if (estadoTexto) estadoTexto.textContent = 'Procesando respuesta del SRI';
            } else {
                if (progressText) progressText.textContent = 'Finalizando...';
                if (estadoTexto) estadoTexto.textContent = 'Completando proceso de autorización';
            }
            
            if (progreso >= 100) {
                clearInterval(intervalo);
                enviarSolicitudAutorizacion();
            }
        }, 200);
    }
    
    // Función para enviar solicitud de autorización
    function enviarSolicitudAutorizacion() {
        fetch(`{{ url('/facturas') }}/${facturaIdAutorizacion}/autorizar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta de autorización:', data);
            
            if (data.success) {
                if (data.authorized) {
                    // Autorizada exitosamente
                    mostrarResultadoAutorizacion(true, data.message, data.data);
                } else {
                    // No autorizada (proceso exitoso pero rechazada)
                    mostrarResultadoAutorizacion(false, data.message, data.data, true);
                }
            } else {
                // Error en el proceso
                mostrarResultadoAutorizacion(false, data.message, data.errors);
            }
        })
        .catch(error => {
            console.error('Error en autorización:', error);
            mostrarResultadoAutorizacion(false, 'Error de conexión: ' + error.message, null);
        });
    }
    
    // Función para mostrar resultado de autorización
    function mostrarResultadoAutorizacion(exito, mensaje, datos, noAutorizada = false) {
        console.log('Mostrando resultado de autorización:', { exito, mensaje, datos, noAutorizada });
        
        const progresoAutorizacion = document.getElementById('progreso_autorizacion');
        const resultadoAutorizacion = document.getElementById('resultado_autorizacion');
        const alertElement = document.getElementById('alert_resultado_autorizacion');
        
        if (progresoAutorizacion) progresoAutorizacion.style.display = 'none';
        if (resultadoAutorizacion) resultadoAutorizacion.style.display = 'block';
        
        if (!alertElement) {
            alert(`${exito ? 'Éxito' : 'Error'}: ${mensaje}`);
            return;
        }
        
        if (exito) {
            alertElement.className = 'alert alert-success';
            alertElement.innerHTML = `
                <h6><i class="fas fa-check-circle"></i> Autorización Exitosa</h6>
                <p>${mensaje}</p>
            `;
            
            if (datos) {
                let detalles = '<hr><small>';
                if (datos.estado_sri) {
                    detalles += `<strong>Estado SRI:</strong> ${datos.estado_sri}<br>`;
                }
                if (datos.numero_autorizacion) {
                    detalles += `<strong>Autorización:</strong> ${datos.numero_autorizacion}<br>`;
                }
                if (datos.fecha_autorizacion) {
                    detalles += `<strong>Fecha:</strong> ${datos.fecha_autorizacion}<br>`;
                }
                detalles += '</small>';
                alertElement.innerHTML += detalles;
            }
            
            // Mostrar botón de cerrar exitoso
            const btnCerrarExitoso = document.getElementById('btn_cerrar_autorizacion_exitoso');
            if (btnCerrarExitoso) btnCerrarExitoso.style.display = 'inline-block';
            
            // Recargar página después de 3 segundos
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            
        } else {
            // Determinar si es un rechazo (no autorizada) o un error de proceso
            if (noAutorizada) {
                alertElement.className = 'alert alert-warning';
                alertElement.innerHTML = `
                    <h6><i class="fas fa-exclamation-triangle"></i> Factura NO Autorizada</h6>
                    <p>${mensaje}</p>
                    <small class="text-muted">El proceso se completó correctamente, pero el SRI no autorizó la factura.</small>
                `;
                
                if (datos && datos.motivo_rechazo) {
                    let motivos = '<hr><small><strong>Motivos del rechazo:</strong><ul>';
                    if (Array.isArray(datos.motivo_rechazo)) {
                        datos.motivo_rechazo.forEach(motivo => {
                            motivos += `<li>${motivo}</li>`;
                        });
                    } else {
                        motivos += `<li>${datos.motivo_rechazo}</li>`;
                    }
                    motivos += '</ul></small>';
                    alertElement.innerHTML += motivos;
                }
                
                if (datos && datos.estado_sri) {
                    alertElement.innerHTML += `<hr><small><strong>Estado SRI:</strong> ${datos.estado_sri}</small>`;
                }
                
                // Mostrar botón de cerrar (no de reintentar)
                const btnCerrarExitoso = document.getElementById('btn_cerrar_autorizacion_exitoso');
                if (btnCerrarExitoso) btnCerrarExitoso.style.display = 'inline-block';
                
                // Recargar página después de 5 segundos para mostrar el nuevo estado
                setTimeout(() => {
                    window.location.reload();
                }, 5000);
                
            } else {
                // Error en el proceso
                alertElement.className = 'alert alert-danger';
                alertElement.innerHTML = `
                    <h6><i class="fas fa-times-circle"></i> Error en Autorización</h6>
                    <p>${mensaje}</p>
                `;
                
                if (datos && Array.isArray(datos)) {
                    let errores = '<hr><small><strong>Detalles:</strong><ul>';
                    datos.forEach(error => {
                        errores += `<li>${error}</li>`;
                    });
                    errores += '</ul></small>';
                    alertElement.innerHTML += errores;
                }
                
                // Mostrar botón para reintentar
                const btnAutorizar = document.getElementById('btn_autorizar');
                if (btnAutorizar) btnAutorizar.style.display = 'inline-block';
                
                console.error('ERROR: Error al autorizar la factura:', mensaje, datos);
            }
        }
    }
    
    // Función para verificar autorización
    function verificarAutorizacion(facturaId) {
        console.log('Verificando autorización para factura:', facturaId);
        
        // Mostrar indicador de proceso
        const estadoVerificacion = document.getElementById('estado_verificacion_proceso');
        if (estadoVerificacion) {
            estadoVerificacion.style.display = 'block';
        }
        
        // Deshabilitar botones
        const botones = document.querySelectorAll('button');
        const botonesOriginales = [];
        botones.forEach(btn => {
            botonesOriginales.push({
                element: btn,
                disabled: btn.disabled,
                innerHTML: btn.innerHTML
            });
            btn.disabled = true;
        });
        
        // Enviar solicitud
        fetch(`{{ url('/facturas') }}/${facturaId}/verificar-autorizacion`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta de verificación:', data);
            
            // Ocultar indicador de proceso
            if (estadoVerificacion) {
                estadoVerificacion.style.display = 'none';
            }
            
            // Restaurar botones
            botonesOriginales.forEach(item => {
                item.element.disabled = item.disabled;
                item.element.innerHTML = item.innerHTML;
            });
            
            if (data.success) {
                const mensaje = data.data.estado_actual !== data.data.estado_anterior 
                    ? `Estado actualizado de "${data.data.estado_anterior}" a "${data.data.estado_actual}"`
                    : `Estado confirmado: "${data.data.estado_actual}"`;
                
                alert(`✅ Verificación exitosa\n\n${mensaje}\n\nEstado SRI: ${data.data.estado_sri}`);
                
                // Recargar la página para mostrar los cambios
                window.location.reload();
            } else {
                alert(`❌ Error en verificación:\n\n${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error en verificación:', error);
            
            // Ocultar indicador de proceso
            if (estadoVerificacion) {
                estadoVerificacion.style.display = 'none';
            }
            
            // Restaurar botones
            botonesOriginales.forEach(item => {
                item.element.disabled = item.disabled;
                item.element.innerHTML = item.innerHTML;
            });
            
            alert(`❌ Error de conexión:\n\n${error.message}`);
        });
    }
</script>
@stop
