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
                    {{-- Mostrar botón de consultar autorización siempre --}}
                    <button type="button" 
                            class="btn btn-sm btn-warning mr-2" 
                            onclick="autorizarFactura({{ $factura->id }})"
                            id="btnAutorizar"
                            @if(!$factura->clave_acceso)
                                title="La factura no tiene clave de acceso generada aún"
                            @endif>
                        <i class="fas fa-sync-alt"></i> Consultar Autorización SRI
                    </button>
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

<!-- Modal para mostrar resultados de autorización -->
<div class="modal fade" id="modalResultadoAutorizacion" tabindex="-1" role="dialog" aria-labelledby="modalResultadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title" id="modalResultadoLabel">
                    <i class="fas fa-info-circle"></i> Resultado de Autorización SRI
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalResultadoBody">
                <!-- El contenido se llenará dinámicamente -->
            </div>
            <div class="modal-footer" id="modalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnCerrarModal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnVerFactura" style="display: none;" onclick="irAFactura()">
                    <i class="fas fa-eye"></i> Ver Factura
                </button>
            </div>
        </div>
    </div>
</div>

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
    
    /* Estilos para el modal de resultado */
    .modal-header.modal-success {
        background-color: #28a745;
        color: white;
        border-bottom: 1px solid #1e7e34;
    }
    
    .modal-header.modal-warning {
        background-color: #ffc107;
        color: #212529;
        border-bottom: 1px solid #e0a800;
    }
    
    .modal-header.modal-danger {
        background-color: #dc3545;
        color: white;
        border-bottom: 1px solid #bd2130;
    }
    
    .modal-header.modal-info {
        background-color: #17a2b8;
        color: white;
        border-bottom: 1px solid #117a8b;
    }
    
    .modal-header .close {
        color: inherit;
        opacity: 0.8;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
    
    .mensaje-sri {
        padding: 10px;
        margin: 5px 0;
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .mensaje-sri.error {
        border-left-color: #dc3545;
        background-color: #f8d7da;
    }
    
    .mensaje-sri.warning {
        border-left-color: #ffc107;
        background-color: #fff3cd;
    }
    
    .mensaje-sri.info {
        border-left-color: #17a2b8;
        background-color: #d1ecf1;
    }
</style>
@stop

@section('js')
<script>
    let facturaIdGlobal = {{ $factura->id }};
    let debeRedirigir = false;

    function autorizarFactura(facturaId) {
        const btnAutorizar = document.getElementById('btnAutorizar');
        const iconoOriginal = btnAutorizar.innerHTML;
        
        // Verificar si hay clave de acceso
        const claveAcceso = document.getElementById('clave_acceso').value;
        if (!claveAcceso || claveAcceso === 'No disponible') {
            mostrarModalResultado('warning', 'Clave de Acceso Requerida', 
                '<p>La factura no tiene clave de acceso generada.</p>' +
                '<p>Para consultar la autorización en el SRI, la factura debe:</p>' +
                '<ul>' +
                '<li>Estar firmada digitalmente</li>' +
                '<li>Tener una clave de acceso válida</li>' +
                '<li>Haber sido enviada al SRI</li>' +
                '</ul>', false);
            return;
        }
        
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
                procesarResultadoAutorizacion(data.data);
            } else {
                mostrarModalResultado('danger', 'Error en Consulta', 
                    `<p><strong>Error al consultar autorización:</strong></p>
                     <p>${data.message}</p>`, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarModalResultado('danger', 'Error de Conexión', 
                `<p><strong>Error de conexión al consultar la autorización.</strong></p>
                 <p>Por favor, intente nuevamente.</p>
                 <small class="text-muted">Detalle técnico: ${error.message}</small>`, false);
        })
        .finally(() => {
            // Restaurar botón
            btnAutorizar.disabled = false;
            btnAutorizar.innerHTML = iconoOriginal;
        });
    }
    
    function procesarResultadoAutorizacion(data) {
        const estado = data.estado;
        let tipoModal, titulo, contenido, mostrarBotonFactura = false;
        
        if (estado === 'AUTORIZADA') {
            tipoModal = 'success';
            titulo = '¡Factura Autorizada Exitosamente!';
            contenido = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle"></i> Autorización Completada</h6>
                    <p>La factura ha sido oficialmente autorizada por el SRI.</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Número de Autorización:</strong><br>
                        <code>${data.numeroAutorizacion || 'N/A'}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Fecha de Autorización:</strong><br>
                        ${data.fechaAutorizacion || 'N/A'}
                    </div>
                </div>
            `;
            // Agregar mensajes del SRI siempre
            contenido += generarMensajesSRI(data.mensajes);
            mostrarBotonFactura = true;
            debeRedirigir = true;
            
        } else if (estado === 'EN_PROCESO') {
            tipoModal = 'info';
            titulo = 'Factura en Proceso';
            contenido = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-clock"></i> Procesando</h6>
                    <p>La factura está en proceso de autorización por parte del SRI.</p>
                    <p><strong>Recomendación:</strong> Intente consultar nuevamente en unos minutos.</p>
                </div>
            `;
            // Agregar mensajes del SRI siempre
            contenido += generarMensajesSRI(data.mensajes);
            
        } else if (estado === 'DEVUELTA') {
            // Verificar si es un caso de "no encontrada"
            let esNoEncontrada = false;
            if (data.mensajes && data.mensajes.length > 0) {
                esNoEncontrada = data.mensajes.some(mensaje => 
                    mensaje.identificador === 'NO_ENCONTRADA'
                );
            }
            
            if (esNoEncontrada) {
                tipoModal = 'warning';
                titulo = 'Factura No Encontrada';
                contenido = `
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> No se encontró información</h6>
                        <p>No se encontró información de autorización para esta factura en el SRI.</p>
                    </div>
                    <p><strong>Posibles causas:</strong></p>
                    <ul>
                        <li>La factura no ha sido enviada al SRI</li>
                        <li>La clave de acceso no es válida</li>
                        <li>El SRI aún no ha procesado la factura</li>
                    </ul>
                `;
            } else {
                tipoModal = 'danger';
                titulo = 'Factura Devuelta por SRI';
                contenido = `
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-times-circle"></i> Factura Devuelta</h6>
                        <p>La factura fue devuelta por el SRI.</p>
                        <p><strong>Estado:</strong> ${estado}</p>
                    </div>
                `;
            }
            // Agregar mensajes del SRI siempre
            contenido += generarMensajesSRI(data.mensajes);
            
        } else {
            // Otros estados (NO_AUTORIZADA, etc.)
            tipoModal = 'danger';
            titulo = 'Factura No Autorizada';
            contenido = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-ban"></i> No Autorizada</h6>
                    <p>La factura no ha sido autorizada por el SRI.</p>
                    <p><strong>Estado:</strong> ${estado}</p>
                </div>
            `;
            // Agregar mensajes del SRI siempre
            contenido += generarMensajesSRI(data.mensajes);
        }
        
        // Agregar información adicional del SRI si está disponible
        if (data.ambiente || data.comprobante) {
            contenido += '<hr><div class="mt-3"><h6>Información Adicional del SRI:</h6>';
            if (data.ambiente) {
                contenido += `<p><strong>Ambiente:</strong> ${data.ambiente === '1' ? 'PRUEBAS' : 'PRODUCCIÓN'}</p>`;
            }
            if (data.comprobante && data.comprobante.length > 0) {
                contenido += `<p><strong>XML Autorizado:</strong> Disponible (${data.comprobante.length} caracteres)</p>`;
            }
            contenido += '</div>';
        }
        
        mostrarModalResultado(tipoModal, titulo, contenido, mostrarBotonFactura);
    }
    
    function generarMensajesSRI(mensajes) {
        let html = '<div class="mt-3"><h6><i class="fas fa-comments"></i> Respuesta del SRI:</h6>';
        
        if (!mensajes || mensajes.length === 0) {
            html += `
                <div class="mensaje-sri info">
                    <strong>Sin mensajes específicos del SRI</strong>
                    <br><small>El SRI procesó la consulta pero no devolvió mensajes adicionales.</small>
                </div>
            `;
        } else {
            mensajes.forEach((mensaje, index) => {
                const tipoClase = mensaje.tipo === 'ERROR' ? 'error' : 
                                 mensaje.tipo === 'WARNING' ? 'warning' : 'info';
                
                html += `
                    <div class="mensaje-sri ${tipoClase}">
                        <strong>${index + 1}. ${mensaje.mensaje || 'Mensaje sin descripción'}</strong>
                        ${mensaje.identificador ? `<br><small><strong>Código:</strong> ${mensaje.identificador}</small>` : ''}
                        ${mensaje.informacionAdicional ? `<br><small><strong>Detalle:</strong> ${mensaje.informacionAdicional}</small>` : ''}
                        ${mensaje.tipo ? `<br><small><strong>Tipo:</strong> <span class="badge badge-${tipoClase === 'error' ? 'danger' : (tipoClase === 'warning' ? 'warning' : 'info')}">${mensaje.tipo}</span></small>` : ''}
                    </div>
                `;
            });
        }
        
        html += '</div>';
        return html;
    }
    
    function mostrarModalResultado(tipo, titulo, contenido, mostrarBotonFactura) {
        // Configurar header del modal
        const modalHeader = document.getElementById('modalHeader');
        const modalLabel = document.getElementById('modalResultadoLabel');
        const modalBody = document.getElementById('modalResultadoBody');
        const btnVerFactura = document.getElementById('btnVerFactura');
        
        // Limpiar clases previas
        modalHeader.className = 'modal-header modal-' + tipo;
        
        // Configurar título con icono apropiado
        let icono = '';
        switch(tipo) {
            case 'success': icono = 'fas fa-check-circle'; break;
            case 'warning': icono = 'fas fa-exclamation-triangle'; break;
            case 'danger': icono = 'fas fa-times-circle'; break;
            case 'info': icono = 'fas fa-info-circle'; break;
        }
        
        modalLabel.innerHTML = `<i class="${icono}"></i> ${titulo}`;
        modalBody.innerHTML = contenido;
        
        // Mostrar/ocultar botón de ver factura
        if (mostrarBotonFactura) {
            btnVerFactura.style.display = 'inline-block';
        } else {
            btnVerFactura.style.display = 'none';
        }
        
        // Mostrar modal
        $('#modalResultadoAutorizacion').modal('show');
    }
    
    function irAFactura() {
        window.location.href = '{{ route("facturas.show", $factura->id) }}';
    }
    
    // Manejar el cierre del modal
    $('#modalResultadoAutorizacion').on('hidden.bs.modal', function () {
        if (debeRedirigir) {
            setTimeout(() => {
                window.location.href = '{{ route("facturas.show", $factura->id) }}';
            }, 500);
        }
    });
    
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
                mostrarModalResultado('success', 'Copiado', 
                    '<p>Clave de acceso copiada al portapapeles exitosamente.</p>', false);
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                mostrarModalResultado('danger', 'Error', 
                    '<p>Error al copiar la clave de acceso.</p>', false);
            });
        } else {
            mostrarModalResultado('warning', 'Sin Datos', 
                '<p>No hay clave de acceso disponible para copiar.</p>', false);
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
                
                // Mostrar confirmación en modal
                mostrarModalResultado('success', 'Copiado', 
                    '<p>Número de autorización copiado al portapapeles exitosamente.</p>', false);
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                mostrarModalResultado('danger', 'Error', 
                    '<p>Error al copiar el número de autorización.</p>', false);
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
            
            mostrarModalResultado('success', 'Descarga Completada', 
                '<p>XML autorizado descargado exitosamente.</p>', false);
        } else {
            mostrarModalResultado('warning', 'Sin Datos', 
                '<p>No hay XML autorizado disponible para descargar.</p>', false);
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
                
                mostrarModalResultado('success', 'Copiado', 
                    '<p>XML autorizado copiado al portapapeles exitosamente.</p>', false);
            }).catch(function(err) {
                console.error('Error al copiar XML: ', err);
                mostrarModalResultado('danger', 'Error', 
                    '<p>Error al copiar el XML autorizado.</p>', false);
            });
        } else {
            mostrarModalResultado('warning', 'Sin Datos', 
                '<p>No hay XML autorizado disponible para copiar.</p>', false);
        }
    }
</script>
@stop
