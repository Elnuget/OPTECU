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
                        <strong>Fecha:</strong> {{ $factura->created_at->format('d/m/Y H:i') }}
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
                        <strong>Fecha Autorización:</strong> {{ $factura->fecha_autorizacion->format('d/m/Y H:i') }}
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
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-code"></i> Contenido del XML
        </h3>
        <div class="card-tools">
            @if($xmlContent)
            <button type="button" class="btn btn-sm btn-primary" onclick="copyXmlToClipboard()">
                <i class="fas fa-copy"></i> Copiar
            </button>
            <a href="{{ asset('storage/' . $factura->xml) }}" class="btn btn-sm btn-success" download>
                <i class="fas fa-download"></i> Descargar
            </a>
            
            {{-- Mostrar botones según el estado --}}
            @if(in_array($factura->estado, ['CREADA', 'FIRMADA']))
                <button type="button" class="btn btn-sm btn-warning" onclick="firmarYEnviar({{ $factura->id }})">
                    <i class="fas fa-certificate"></i> Firmar y Enviar al SRI
                </button>
            @elseif($factura->estado === 'RECIBIDA')
                <span class="badge badge-primary">
                    <i class="fas fa-clock"></i> Procesando Autorización...
                </span>
            @elseif($factura->estado === 'AUTORIZADA')
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Autorizada
                </span>
            @elseif($factura->estado === 'DEVUELTA')
                <button type="button" class="btn btn-sm btn-warning" onclick="firmarYEnviar({{ $factura->id }})">
                    <i class="fas fa-redo"></i> Reintentar Envío
                </button>
            @endif
            @endif
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

<!-- Modal para contraseña del certificado -->
<div class="modal fade" id="modalCertificado" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-certificate"></i> Firmar y Enviar al SRI
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCertificado">
                    <div class="form-group">
                        <label for="password_certificado">
                            <i class="fas fa-key"></i> Contraseña del Certificado Digital
                        </label>
                        <input type="password" class="form-control" id="password_certificado" 
                               name="password_certificado" required 
                               placeholder="Ingrese la contraseña del certificado .p12">
                        <small class="form-text text-muted">
                            Se requiere la contraseña del certificado digital (.p12) para firmar el documento.
                        </small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirmar_envio" required>
                            <label class="custom-control-label" for="confirmar_envio">
                                Confirmo que deseo firmar digitalmente y enviar este comprobante al SRI
                            </label>
                        </div>
                    </div>
                </form>
                
                <!-- Área de progreso -->
                <div id="progreso_firma" style="display: none;">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%">
                            <span class="progress-text">Iniciando...</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted" id="estado_proceso">Preparando firma digital...</small>
                    </div>
                </div>
                
                <!-- Área de resultados -->
                <div id="resultado_firma" style="display: none;">
                    <div class="alert" id="alert_resultado">
                        <div id="mensaje_resultado"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btn_firmar" onclick="procesarFirmaYEnvio()">
                    <i class="fas fa-certificate"></i> Firmar y Enviar
                </button>
                <button type="button" class="btn btn-success" id="btn_cerrar_exitoso" style="display: none;" data-dismiss="modal">
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
</style>
@stop

@section('js')
<script>
    let facturaIdActual = null;

    function copyXmlToClipboard() {
        const xmlContent = document.getElementById('xmlContent');
        if (xmlContent) {
            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = xmlContent.textContent;
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            document.execCommand('copy');
            document.body.removeChild(tempTextArea);
            
            console.log('SUCCESS: XML copiado al portapapeles');
            alert('XML copiado al portapapeles');
        }
    }

    function firmarYEnviar(facturaId) {
        facturaIdActual = facturaId;
        
        // Resetear formulario
        document.getElementById('formCertificado').reset();
        document.getElementById('progreso_firma').style.display = 'none';
        document.getElementById('resultado_firma').style.display = 'none';
        document.getElementById('btn_firmar').style.display = 'inline-block';
        document.getElementById('btn_cerrar_exitoso').style.display = 'none';
        
        // Mostrar modal
        $('#modalCertificado').modal('show');
    }

    function procesarFirmaYEnvio() {
        const password = document.getElementById('password_certificado').value;
        const confirmar = document.getElementById('confirmar_envio').checked;
        
        console.log('Iniciando proceso de firma y envío', {
            password_length: password.length,
            confirmado: confirmar,
            factura_id: facturaIdActual
        });
        
        // Validaciones
        if (!password.trim()) {
            console.error('Error: Contraseña vacía');
            alert('Debe ingresar la contraseña del certificado');
            return;
        }
        
        if (!confirmar) {
            console.error('Error: No confirmado');
            alert('Debe confirmar que desea firmar y enviar el comprobante');
            return;
        }
        
        // Deshabilitar botón y mostrar progreso
        document.getElementById('btn_firmar').disabled = true;
        document.getElementById('progreso_firma').style.display = 'block';
        document.getElementById('resultado_firma').style.display = 'none';
        
        // Inicializar progreso
        actualizarProgreso(20, 'Validando certificado digital...');
        
        // Verificar token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('Error: Token CSRF no encontrado');
            mostrarResultado(false, 'Error de configuración: Token CSRF no encontrado', null);
            return;
        }
        
        // Preparar datos para envío
        const formData = new FormData();
        formData.append('password_certificado', password);
        formData.append('_token', csrfToken.getAttribute('content'));
        
        console.log('Enviando petición a:', `/facturas/${facturaIdActual}/firmar-y-enviar`);
        
        // Realizar petición AJAX
        fetch(`/facturas/${facturaIdActual}/firmar-y-enviar`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Respuesta recibida:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Datos de respuesta:', data);
            
            if (data.success) {
                // Proceso exitoso
                actualizarProgreso(100, 'Proceso completado exitosamente');
                mostrarResultado(true, data.message, data.data);
            } else {
                // Error en el proceso
                mostrarResultado(false, data.message, data.errors);
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            mostrarResultado(false, 'Error de conexión: ' + error.message, null);
        })
        .finally(() => {
            document.getElementById('btn_firmar').disabled = false;
        });
    }

    function actualizarProgreso(porcentaje, mensaje) {
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.querySelector('.progress-text');
        const estadoProceso = document.getElementById('estado_proceso');
        
        progressBar.style.width = porcentaje + '%';
        progressText.textContent = porcentaje + '%';
        estadoProceso.textContent = mensaje;
        
        // Simular progreso gradual
        if (porcentaje < 100) {
            setTimeout(() => {
                actualizarProgreso(Math.min(porcentaje + 10, 90), 'Procesando...');
            }, 1000);
        }
    }

    function mostrarResultado(exito, mensaje, datos) {
        console.log('Mostrando resultado:', { exito, mensaje, datos });
        
        document.getElementById('progreso_firma').style.display = 'none';
        document.getElementById('resultado_firma').style.display = 'block';
        
        const alertElement = document.getElementById('alert_resultado');
        const mensajeElement = document.getElementById('mensaje_resultado');
        
        if (exito) {
            alertElement.className = 'alert alert-success';
            alertElement.innerHTML = `
                <h6><i class="fas fa-check-circle"></i> Proceso Exitoso</h6>
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
            document.getElementById('btn_firmar').style.display = 'none';
            document.getElementById('btn_cerrar_exitoso').style.display = 'inline-block';
            
            console.log('SUCCESS: Factura firmada y enviada correctamente al SRI');
        } else {
            alertElement.className = 'alert alert-danger';
            alertElement.innerHTML = `
                <h6><i class="fas fa-exclamation-triangle"></i> Error en el Proceso</h6>
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
            
            console.error('ERROR: Error al firmar y enviar la factura:', mensaje, datos);
        }
    }

    // Manejar eventos del modal
    $('#modalCertificado').on('hidden.bs.modal', function () {
        facturaIdActual = null;
        document.getElementById('formCertificado').reset();
        document.getElementById('progreso_firma').style.display = 'none';
        document.getElementById('resultado_firma').style.display = 'none';
    });

    // Permitir envío con Enter en el campo de contraseña
    document.getElementById('password_certificado').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            procesarFirmaYEnvio();
        }
    });
    
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
            progressBar.style.width = progreso + '%';
            
            if (progreso <= 30) {
                progressText.textContent = 'Conectando con SRI...';
                estadoTexto.textContent = 'Estableciendo conexión con el servicio de autorización';
            } else if (progreso <= 60) {
                progressText.textContent = 'Enviando solicitud...';
                estadoTexto.textContent = 'Enviando solicitud de autorización';
            } else if (progreso <= 90) {
                progressText.textContent = 'Procesando...';
                estadoTexto.textContent = 'Procesando respuesta del SRI';
            } else {
                progressText.textContent = 'Finalizando...';
                estadoTexto.textContent = 'Completando proceso de autorización';
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
                mostrarResultadoAutorizacion(true, data.message, data.data);
            } else {
                mostrarResultadoAutorizacion(false, data.message, data.errors);
            }
        })
        .catch(error => {
            console.error('Error en autorización:', error);
            mostrarResultadoAutorizacion(false, 'Error de conexión: ' + error.message, null);
        });
    }
    
    // Función para mostrar resultado de autorización
    function mostrarResultadoAutorizacion(exito, mensaje, datos) {
        console.log('Mostrando resultado de autorización:', { exito, mensaje, datos });
        
        document.getElementById('progreso_autorizacion').style.display = 'none';
        document.getElementById('resultado_autorizacion').style.display = 'block';
        
        const alertElement = document.getElementById('alert_resultado_autorizacion');
        
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
            document.getElementById('btn_cerrar_autorizacion_exitoso').style.display = 'inline-block';
            
            // Recargar página después de 3 segundos
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            
        } else {
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
            document.getElementById('btn_autorizar').style.display = 'inline-block';
            
            console.error('ERROR: Error al autorizar la factura:', mensaje, datos);
        }
    }
    
    // Manejar eventos del modal de autorización
    $('#modalAutorizacion').on('hidden.bs.modal', function () {
    });
</script>
@stop
