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
                @php
                    $tieneArchivo = $factura->declarante && !empty($factura->declarante->firma);
                    $rutaArchivo = $tieneArchivo ? public_path('uploads/firmas/' . $factura->declarante->firma) : '';
                    $existeArchivo = $tieneArchivo && file_exists($rutaArchivo);
                    $extension = $tieneArchivo ? strtolower(pathinfo($factura->declarante->firma, PATHINFO_EXTENSION)) : '';
                    $esExtensionValida = in_array($extension, ['p12', 'pfx']);
                    $tieneCertificadoP12 = $existeArchivo && $esExtensionValida;
                    
                    // Información de depuración
                    $debug = [
                        'tiene_archivo' => $tieneArchivo,
                        'ruta_archivo' => $rutaArchivo,
                        'existe_archivo' => $existeArchivo ? 'SÍ' : 'NO',
                        'extension' => $extension,
                        'es_extension_valida' => $esExtensionValida ? 'SÍ' : 'NO',
                        'tiene_certificado_p12_vista' => $tieneCertificadoP12 ? 'SÍ' : 'NO',
                        'tiene_certificado_p12_modelo' => $factura->declarante && $factura->declarante->tiene_certificado_p12 ? 'SÍ' : 'NO',
                    ];
                @endphp

                <!-- Botón de firmar y enviar removido por solicitud del usuario -->
            @elseif($factura->estado === 'RECIBIDA')
                <button type="button" class="btn btn-sm btn-info" onclick="procesarAutorizacionDirecta({{ $factura->id }})">
                    <i class="fas fa-check"></i> Solicitar Autorización
                </button>
                <div id="estado_autorizacion_proceso" style="display: none;" class="mt-2">
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Solicitando autorización al SRI...
                    </div>
                </div>
            @elseif($factura->estado === 'AUTORIZADA')
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Autorizada
                </span>
            @elseif($factura->estado === 'NO_AUTORIZADA')
                <span class="badge badge-dark">
                    <i class="fas fa-ban"></i> No Autorizada por el SRI
                </span>
            @elseif($factura->estado === 'DEVUELTA')
                <!-- Botón de reintentar envío removido por solicitud del usuario -->
            @endif
            @endif
            
            {{-- Botón de procesamiento según el estado --}}
            @if(in_array($factura->estado, ['CREADA', 'FIRMADA']))
                <button type="button" class="btn btn-sm btn-warning" onclick="abrirModalPythonProcesar()">
                    <i class="fas fa-cogs"></i> Procesar con Python
                </button>
            @elseif($factura->estado === 'RECIBIDA')
                <button type="button" class="btn btn-sm btn-info" onclick="procesarAutorizacionDirecta({{ $factura->id }})">
                    <i class="fas fa-check"></i> Autorizar
                </button>
                <div id="estado_autorizacion_proceso" style="display: none;" class="mt-2">
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Solicitando autorización al SRI...
                    </div>
                </div>
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

<!-- Modal para procesar con Python API -->
<div class="modal fade" id="modalPythonProcesar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cogs"></i> Procesar con Python API
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="password_certificado">Contraseña del Certificado P12:</label>
                    <input type="password" class="form-control" id="password_certificado" 
                           placeholder="Ingrese la contraseña del certificado" required>
                    <small class="form-text text-muted">
                        Esta contraseña será utilizada para firmar digitalmente el XML.
                    </small>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="confirmar_python_proceso" required>
                        <label class="custom-control-label" for="confirmar_python_proceso">
                            Confirmo que deseo generar, firmar y enviar este comprobante al SRI usando Python API
                        </label>
                    </div>
                </div>

                <!-- Área de progreso -->
                <div id="progreso_python" style="display: none;">
                    <div class="text-center mb-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="progress-text mt-2"><strong>Procesando...</strong></div>
                    </div>
                    <div class="text-muted text-center">
                        <small id="estado_python">Iniciando proceso...</small>
                    </div>
                </div>

                <!-- Área de resultado -->
                <div id="resultado_python" style="display: none;">
                    <div id="alert_resultado_python" class="alert">
                        <!-- El contenido se llenará dinámicamente -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btn_procesar_python" onclick="procesarConPython()">
                    <i class="fas fa-cogs"></i> Procesar
                </button>
                <button type="button" class="btn btn-success" id="btn_cerrar_python_exitoso" style="display: none;" data-dismiss="modal">
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
<!-- Librería Forge.js para firma digital -->
<script src="https://cdn.jsdelivr.net/npm/node-forge@1.3.1/dist/forge.min.js"></script>
<!-- Librería propia para firma digital -->
<script src="{{ asset('js/firma-digital-xades.js') }}"></script>
<script>
    let facturaIdActual = null;
    let facturaIdAutorizacion = null;

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

    // Función removida por solicitud del usuario
    /*
    function firmarYEnviar(facturaId) {
        // Funcionalidad de firma removida
    }
    */

    // Función removida por solicitud del usuario
    /*
    function procesarFirmaYEnvio() {
        // Obtener elementos de manera segura
        const passwordInput = document.getElementById('password_certificado');
        const confirmarCheckbox = document.getElementById('confirmar_envio');
        const btnFirmar = document.getElementById('btn_firmar');
        const progresoFirma = document.getElementById('progreso_firma');
        const resultadoFirma = document.getElementById('resultado_firma');
        
        if (!passwordInput || !confirmarCheckbox) {
            console.error('Error: No se encontraron elementos del formulario');
            alert('Error al cargar el formulario. Por favor, recargue la página.');
            return;
        }
        
        const password = passwordInput.value;
        const confirmar = confirmarCheckbox.checked;
        
        console.log('Iniciando proceso de firma digital con JavaScript', {
            password_length: password.length,
            confirmado: confirmar,
            factura_id: facturaIdActual
        });
        
        // Validaciones
        if (!password.trim()) {
            console.error('Error: Contraseña vacía');
            alert('Debe ingresar la contraseña del certificado del declarante');
            return;
        }
        // Funcionalidad de firma removida
    }
    */

    // Todas las funciones relacionadas con firma han sido removidas por solicitud del usuario
    /*
    // ... resto del código de firma comentado ...

    // Nueva función para enviar solicitud al backend usando el certificado del declarante
    async function enviarSolicitudFirmaConCertificadoDeclarante(password) {
        try {
            actualizarProgreso(30, 'Enviando solicitud de firma al servidor...');
            
            const response = await fetch(`/facturas/${facturaIdActual}/firmar-con-certificado-declarante`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    password_certificado: password
                })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                // Si el error está relacionado con el certificado, mostrar información más detallada
                if (errorData.message && errorData.message.toLowerCase().includes('certificado')) {
                    // Mostrar información de depuración adicional
                    const infoDebug = document.querySelector('#debugInfo');
                    if (infoDebug) {
                        const debugButton = document.querySelector('[data-target="#debugInfo"]');
                        if (debugButton) {
                            debugButton.click(); // Mostrar automáticamente la info de depuración
                        }
                    }
                    
                    const ruta = document.querySelector('.text-info') ? document.querySelector('.text-info').textContent : 'public/uploads/firmas/';
                    throw new Error(`Error con el certificado digital: ${errorData.message}.\n\nPosibles causas:\n1. El archivo no existe físicamente en el servidor\n2. Los permisos del archivo son incorrectos\n3. La ruta del archivo ha cambiado\n\nRuta esperada: ${ruta}`);
                }
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                actualizarProgreso(100, 'Proceso completado exitosamente');
                mostrarResultado(true, data.message, data.data);
            } else {
                mostrarResultado(false, data.message, data.errors);
            }
            
        } catch (error) {
            console.error('Error en firma con certificado del declarante:', error);
            mostrarResultado(false, 'Error de conexión: ' + error.message, null);
        } finally {
            document.getElementById('btn_firmar').disabled = false;
        }
    }

    function actualizarProgreso(porcentaje, mensaje) {
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.querySelector('.progress-text');
        const estadoProceso = document.getElementById('estado_proceso');
        
        // Verificar si los elementos existen antes de modificarlos
        if (progressBar) progressBar.style.width = porcentaje + '%';
        if (progressText) progressText.textContent = porcentaje + '%';
        if (estadoProceso) estadoProceso.textContent = mensaje;
        
        // Solo logs importantes, no de progreso intermedio
        if (porcentaje === 0 || porcentaje === 100) {
            console.log(`Proceso: ${porcentaje === 100 ? 'Completado' : 'Iniciado'} - ${mensaje}`);
        }
    }

    function mostrarResultado(exito, mensaje, datos) {
        console.log('Mostrando resultado:', { exito, mensaje, datos });
        
        // Obtener referencias a los elementos DOM de forma segura
        const progresoFirma = document.getElementById('progreso_firma');
        const resultadoFirma = document.getElementById('resultado_firma');
        const alertElement = document.getElementById('alert_resultado');
        const mensajeElement = document.getElementById('mensaje_resultado');
        
        // Verificar que los elementos existan antes de manipularlos
        if (progresoFirma) progresoFirma.style.display = 'none';
        if (resultadoFirma) resultadoFirma.style.display = 'block';
        
        // Si no hay elemento de alerta, mostrar mensaje en consola y salir
        if (!alertElement) {
            console.error('Error: No se encontró el elemento alert_resultado');
            alert(`${exito ? 'Éxito' : 'Error'}: ${mensaje}`);
            return;
        }
        
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
        // Verificar si el proceso fue exitoso
        const btnCerrarExitoso = document.getElementById('btn_cerrar_exitoso');
        const procesoExitoso = btnCerrarExitoso && btnCerrarExitoso.style.display !== 'none';
        
        facturaIdActual = null;
        document.getElementById('formCertificado').reset();
        document.getElementById('progreso_firma').style.display = 'none';
        document.getElementById('resultado_firma').style.display = 'none';
        
        // Si el proceso fue exitoso, recargar la página
        if (procesoExitoso) {
            window.location.reload();
        }
    });

    // Permitir envío con Enter en el campo de contraseña
    document.getElementById('password_certificado').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            procesarFirmaYEnvio();
        }
    });
    
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
                document.getElementById('btn_cerrar_autorizacion_exitoso').style.display = 'inline-block';
                
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
                document.getElementById('btn_autorizar').style.display = 'inline-block';
                
                console.error('ERROR: Error al autorizar la factura:', mensaje, datos);
            }
        }
    }
    
    // Función removida por solicitud del usuario
    /*
    // Nueva función para firmar y enviar usando el servidor
    function firmarYEnviarServidor(facturaId) {
        // Funcionalidad de firma removida
    }
    */
    
    // Manejar eventos del modal de autorización
    $('#modalAutorizacion').on('hidden.bs.modal', function () {
    });
    
    // NUEVAS FUNCIONES PARA PROCESAMIENTO CON PYTHON API
    function abrirModalPythonProcesar() {
        // Limpiar el modal
        document.getElementById('password_certificado').value = '';
        document.getElementById('confirmar_python_proceso').checked = false;
        document.getElementById('progreso_python').style.display = 'none';
        document.getElementById('resultado_python').style.display = 'none';
        document.getElementById('btn_procesar_python').style.display = 'inline-block';
        document.getElementById('btn_cerrar_python_exitoso').style.display = 'none';
        
        // Mostrar el modal
        $('#modalPythonProcesar').modal('show');
    }
    
    function procesarConPython() {
        const password = document.getElementById('password_certificado').value;
        const confirmado = document.getElementById('confirmar_python_proceso').checked;
        
        if (!password) {
            alert('Por favor ingrese la contraseña del certificado');
            return;
        }
        
        if (!confirmado) {
            alert('Por favor confirme el procesamiento');
            return;
        }
        
        // Mostrar progreso y ocultar botón
        document.getElementById('progreso_python').style.display = 'block';
        document.getElementById('btn_procesar_python').style.display = 'none';
        document.getElementById('resultado_python').style.display = 'none';
        
        // Actualizar progreso
        actualizarProgresoP(20, 'Conectando con Python API...');
        
        // Realizar petición AJAX
        fetch(`{{ route('facturas.procesar-python', $factura->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            mostrarResultadoPython(data);
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarResultadoPython({
                success: false,
                message: 'Error de conexión con el servidor',
                error: error.message
            });
        });
    }
    
    function actualizarProgresoP(porcentaje, estado) {
        const progressBar = document.querySelector('#progreso_python .progress-bar');
        const estadoText = document.getElementById('estado_python');
        
        progressBar.style.width = porcentaje + '%';
        estadoText.textContent = estado;
    }
    
    function mostrarResultadoPython(respuesta) {
        // Ocultar progreso y mostrar resultado
        document.getElementById('progreso_python').style.display = 'none';
        document.getElementById('resultado_python').style.display = 'block';
        
        const alertElement = document.getElementById('alert_resultado_python');
        
        if (respuesta.success) {
            alertElement.className = 'alert alert-success';
            alertElement.innerHTML = `
                <h6><i class="fas fa-check-circle"></i> Procesamiento Exitoso</h6>
                <p>${respuesta.message}</p>
            `;
            
            if (respuesta.data) {
                let detalles = '<hr><small><strong>Detalles del procesamiento:</strong><br>';
                if (respuesta.data.clave_acceso) {
                    detalles += `<strong>Clave de Acceso:</strong> ${respuesta.data.clave_acceso}<br>`;
                }
                if (respuesta.data.estado_sri) {
                    detalles += `<strong>Estado SRI:</strong> ${respuesta.data.estado_sri}<br>`;
                }
                if (respuesta.data.numero_autorizacion) {
                    detalles += `<strong>Número de Autorización:</strong> ${respuesta.data.numero_autorizacion}<br>`;
                }
                if (respuesta.data.fecha_autorizacion) {
                    detalles += `<strong>Fecha de Autorización:</strong> ${respuesta.data.fecha_autorizacion}<br>`;
                }
                detalles += '</small>';
                alertElement.innerHTML += detalles;
            }
            
            // Mostrar botón de cerrar exitoso
            document.getElementById('btn_cerrar_python_exitoso').style.display = 'inline-block';
            
            // Recargar página después de 3 segundos
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            
        } else {
            alertElement.className = 'alert alert-danger';
            alertElement.innerHTML = `
                <h6><i class="fas fa-times-circle"></i> Error en el Procesamiento</h6>
                <p>${respuesta.message}</p>
            `;
            
            if (respuesta.error) {
                alertElement.innerHTML += `<hr><small><strong>Detalles del error:</strong> ${respuesta.error}</small>`;
            }
            
            // Mostrar botón para reintentar
            document.getElementById('btn_procesar_python').style.display = 'inline-block';
        }
    }
</script>
@stop
