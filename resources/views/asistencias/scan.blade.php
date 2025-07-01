@extends('adminlte::page')

@section('title', 'ESCANEAR QR')

@section('content_header')
    <h1>ESCANEAR QR</h1>
    <p>ESCÁNER DE CÓDIGOS QR PARA ASISTENCIAS</p>
@stop

@section('content')
    <style>
        /* Convertir todo el texto a mayúsculas */
        body, 
        .content-wrapper, 
        .main-header, 
        .main-sidebar, 
        .card-title,
        .info-box-text,
        .info-box-number,
        .custom-select,
        .btn,
        label,
        input,
        select,
        option,
        .form-control,
        p,
        h1, h2, h3, h4, h5, h6,
        th,
        td,
        span,
        a,
        .dropdown-item,
        .alert,
        .modal-title,
        .modal-body p,
        .modal-content,
        .card-header,
        .card-footer,
        button,
        .close {
            text-transform: uppercase !important;
        }

        #scanner-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            min-height: 400px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #scanner-video {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            object-fit: cover;
        }

        /* Estilos para Html5Qrcode */
        #scanner-container video {
            border-radius: 8px;
        }

        #scanner-container > div {
            width: 100% !important;
            border-radius: 8px;
        }

        .camera-controls {
            text-align: center;
            margin: 20px 0;
        }

        .status-message {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .recent-scans {
            max-height: 300px;
            overflow-y: auto;
        }

        .scan-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
    </style>

    <div class="row">
        <!-- Escáner -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CÁMARA ESCÁNER</h3>
                </div>
                <div class="card-body">
                    <div id="scanner-container">
                        <video id="scanner-video" autoplay muted playsinline style="display: none;"></video>
                    </div>

                    <div class="camera-controls">
                        <button type="button" id="start-scan" class="btn btn-success">
                            <i class="fas fa-camera"></i> INICIAR ESCÁNER
                        </button>
                        <button type="button" id="stop-scan" class="btn btn-danger" style="display: none;">
                            <i class="fas fa-stop"></i> DETENER ESCÁNER
                        </button>
                        <button type="button" id="switch-camera" class="btn btn-info" style="display: none;">
                            <i class="fas fa-sync-alt"></i> CAMBIAR CÁMARA
                        </button>
                    </div>

                    <div id="status-message" class="status-message status-info">
                        PRESIONA "INICIAR ESCÁNER" PARA COMENZAR
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de información -->
        <div class="col-md-4">
            <!-- Estado actual -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">ESTADO DEL ESCÁNER</h3>
                </div>
                <div class="card-body">
                    <div id="scanner-status">
                        <p><strong>ESTADO:</strong> <span id="camera-status">DETENIDO</span></p>
                        <p><strong>CÁMARA:</strong> <span id="camera-label">NO DETECTADA</span></p>
                        <p><strong>ÚLTIMA ACCIÓN:</strong> <span id="last-action">NINGUNA</span></p>
                    </div>
                </div>
            </div>

            <!-- Resumen del usuario escaneado -->
            <div class="card" id="user-summary" style="display: none;">
                <div class="card-header bg-success">
                    <h3 class="card-title">RESUMEN DEL USUARIO</h3>
                </div>
                <div class="card-body">
                    <div id="user-info">
                        <p><strong>NOMBRE:</strong> <span id="user-name">-</span></p>
                        <p><strong>USUARIO:</strong> <span id="user-username">-</span></p>
                        <p><strong>EMPRESA:</strong> <span id="user-empresa">-</span></p>
                        <hr>
                        <h5>ESTADÍSTICAS DE PEDIDOS:</h5>
                        <p><strong>TOTAL PEDIDOS:</strong> <span id="total-pedidos" class="badge badge-primary">0</span></p>
                        <p><strong>TOTAL VENTAS:</strong> <span id="total-ventas" class="badge badge-success">$0.00</span></p>
                        <hr>
                        <h6>ESTE MES:</h6>
                        <p><strong>PEDIDOS:</strong> <span id="pedidos-mes" class="badge badge-info">0</span></p>
                        <p><strong>VENTAS:</strong> <span id="ventas-mes" class="badge badge-warning">$0.00</span></p>
                        <div id="ultimo-pedido-info">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instrucciones -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">INSTRUCCIONES</h3>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Presiona "INICIAR ESCÁNER"</li>
                        <li>Permite el acceso a la cámara</li>
                        <li>Apunta la cámara al código QR</li>
                        <li>El sistema evaluará automáticamente:</li>
                        <ul>
                            <li><strong>Sin empresa:</strong> Siempre presente</li>
                            <li><strong>Con empresa:</strong> Evalúa horario</li>
                            <li><strong>+10 min tarde:</strong> Marca atraso</li>
                            <li><strong>Salida pendiente:</strong> Registra salida anterior</li>
                        </ul>
                        <li>¡Asistencia procesada automáticamente!</li>
                    </ol>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ACCIONES RÁPIDAS</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('asistencias.mi-qr') }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-qrcode"></i> VER MI QR
                    </a>
                    <a href="{{ route('asistencias.index') }}" class="btn btn-secondary btn-block mb-2">
                        <i class="fas fa-list"></i> VER ASISTENCIAS
                    </a>
                    <button type="button" class="btn btn-warning btn-block" onclick="location.reload()">
                        <i class="fas fa-refresh"></i> REINICIAR ESCÁNER
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Registro de escaneos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ÚLTIMOS ESCANEOS</h3>
                </div>
                <div class="card-body">
                    <div id="recent-scans" class="recent-scans">
                        <p class="text-muted">NO HAY ESCANEOS RECIENTES</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrCode = null;
    let isScanning = false;
    let cameras = [];
    let currentCameraId = null;
    let recentScans = [];

    $(document).ready(function() {
        // Verificar soporte de cámara
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            updateStatus('TU NAVEGADOR NO SOPORTA ACCESO A LA CÁMARA', 'error');
            return;
        }

        // Obtener cámaras disponibles
        getCameras();

        // Event listeners
        $('#start-scan').click(startScanning);
        $('#stop-scan').click(stopScanning);
        $('#switch-camera').click(switchCamera);
    });

    async function getCameras() {
        try {
            cameras = await Html5Qrcode.getCameras();
            
            if (cameras && cameras.length > 0) {
                currentCameraId = cameras[0].id;
                $('#camera-label').text(cameras.length + ' CÁMARA(S) DISPONIBLE(S)');
                if (cameras.length > 1) {
                    $('#switch-camera').show();
                }
                console.log('Cámaras encontradas:', cameras);
            } else {
                updateStatus('NO SE ENCONTRARON CÁMARAS', 'error');
            }
        } catch (error) {
            console.error('Error obteniendo cámaras:', error);
            updateStatus('ERROR AL ACCEDER A LAS CÁMARAS: ' + error.message, 'error');
        }
    }

    async function startScanning() {
        if (isScanning || !currentCameraId) return;

        try {
            html5QrCode = new Html5Qrcode("scanner-container");
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            await html5QrCode.start(
                currentCameraId,
                config,
                (decodedText, decodedResult) => {
                    console.log('QR detectado:', decodedText);
                    processQRResult(decodedText);
                },
                (errorMessage) => {
                    // Error de escaneo (normal cuando no hay QR visible)
                    // console.log('Escaneando...', errorMessage);
                }
            );

            isScanning = true;
            
            $('#start-scan').hide();
            $('#stop-scan').show();
            $('#camera-status').text('ACTIVO');
            updateStatus('ESCÁNER ACTIVO - APUNTA LA CÁMARA AL QR', 'info');
            
            // Ocultar el video element ya que Html5Qrcode maneja su propio elemento
            $('#scanner-video').hide();

        } catch (error) {
            console.error('Error iniciando escáner:', error);
            updateStatus('ERROR AL INICIAR LA CÁMARA: ' + error.message, 'error');
        }
    }

    async function stopScanning() {
        if (!isScanning || !html5QrCode) return;

        try {
            await html5QrCode.stop();
            html5QrCode.clear();
            html5QrCode = null;
            
            isScanning = false;
            $('#start-scan').show();
            $('#stop-scan').hide();
            $('#camera-status').text('DETENIDO');
            updateStatus('ESCÁNER DETENIDO', 'info');
            
            // Mostrar nuevamente el elemento video
            $('#scanner-video').show();
            
        } catch (error) {
            console.error('Error deteniendo escáner:', error);
        }
    }

    async function switchCamera() {
        if (!isScanning || cameras.length <= 1) return;

        try {
            // Detener el escáner actual
            await stopScanning();
            
            // Cambiar a la siguiente cámara
            const currentIndex = cameras.findIndex(cam => cam.id === currentCameraId);
            const nextIndex = (currentIndex + 1) % cameras.length;
            currentCameraId = cameras[nextIndex].id;
            
            $('#camera-label').text('CÁMARA: ' + (nextIndex + 1) + '/' + cameras.length);
            
            // Reiniciar con la nueva cámara
            setTimeout(() => {
                startScanning();
            }, 500);
            
        } catch (error) {
            console.error('Error cambiando cámara:', error);
        }
    }

    async function processQRResult(data) {
        if (!data) return;
        
        try {
            console.log('Procesando QR:', data);
            
            // Intentar parsear JSON
            let qrData;
            try {
                qrData = JSON.parse(data);
                console.log('QR parseado como JSON:', qrData);
            } catch {
                // Si no es JSON, asumir que es solo el user_id
                qrData = { user_id: parseInt(data), type: 'asistencia' };
                console.log('QR parseado como ID simple:', qrData);
            }

            if (!qrData.user_id) {
                updateStatus('QR INVÁLIDO - NO CONTIENE ID DE USUARIO', 'error');
                return;
            }

            if (qrData.type && qrData.type !== 'asistencia') {
                updateStatus('QR INVÁLIDO - NO ES UN CÓDIGO DE ASISTENCIA', 'error');
                return;
            }

            // Detener escáner temporalmente para evitar múltiples lecturas
            if (html5QrCode && isScanning) {
                await html5QrCode.stop();
            }

            updateStatus('PROCESANDO QR...', 'info');

            // Enviar datos al servidor
            const response = await fetch('{{ route("asistencias.procesar-qr") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    user_id: qrData.user_id,
                    qr_data: data
                })
            });

            const result = await response.json();
            console.log('Respuesta del servidor:', result);

            if (result.success) {
                updateStatus(result.message, 'success');
                addRecentScan(result);
                $('#last-action').text(result.action + ' - ' + result.hora);
                
                // Mostrar resumen del usuario
                showUserSummary(result);
                
                // Sonido de éxito
                playSuccessSound();
            } else {
                updateStatus('ERROR: ' + result.message, 'error');
                addRecentScan({
                    user_name: 'ERROR',
                    action: 'ERROR',
                    hora: new Date().toLocaleTimeString(),
                    success: false
                });
                // Ocultar resumen en caso de error
                $('#user-summary').hide();
            }

        } catch (error) {
            console.error('Error procesando QR:', error);
            updateStatus('ERROR AL PROCESAR QR: ' + error.message, 'error');
        }

        // Reanudar escáner después de 3 segundos
        setTimeout(async () => {
            if (html5QrCode && currentCameraId) {
                try {
                    const config = {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    };

                    await html5QrCode.start(
                        currentCameraId,
                        config,
                        (decodedText, decodedResult) => {
                            processQRResult(decodedText);
                        },
                        (errorMessage) => {
                            // Error de escaneo normal
                        }
                    );
                    
                    isScanning = true;
                    updateStatus('ESCÁNER REACTIVADO - LISTO PARA SIGUIENTE QR', 'info');
                } catch (error) {
                    console.error('Error reactivando escáner:', error);
                }
            }
        }, 3000);
    }

    function updateStatus(message, type) {
        const statusElement = $('#status-message');
        statusElement.removeClass('status-success status-error status-info');
        statusElement.addClass('status-' + type);
        statusElement.text(message);
        console.log('Status:', type, message);
    }

    function addRecentScan(scanData) {
        const scanItem = {
            timestamp: new Date().toLocaleString(),
            user: scanData.user_name || 'USUARIO',
            action: scanData.action || 'ACCIÓN',
            hora: scanData.hora || '',
            status: scanData.success !== false ? 'ÉXITO' : 'ERROR'
        };

        recentScans.unshift(scanItem);
        if (recentScans.length > 10) {
            recentScans.pop();
        }

        updateRecentScansDisplay();
    }

    function updateRecentScansDisplay() {
        const container = $('#recent-scans');
        
        if (recentScans.length === 0) {
            container.html('<p class="text-muted">NO HAY ESCANEOS RECIENTES</p>');
            return;
        }

        let html = '';
        recentScans.forEach(scan => {
            html += `
                <div class="scan-item">
                    <strong>${scan.user}</strong> - ${scan.action}
                    <br>
                    <small>${scan.timestamp} - ${scan.hora}</small>
                    <span class="badge badge-${scan.status === 'ÉXITO' ? 'success' : 'danger'} float-right">
                        ${scan.status}
                    </span>
                </div>
            `;
        });

        container.html(html);
    }

    function showUserSummary(result) {
        if (!result.pedidos_info) return;

        const pedidosInfo = result.pedidos_info;
        
        // Actualizar información básica del usuario
        $('#user-name').text(result.user_name || 'N/A');
        $('#user-username').text(result.user_username || 'N/A');
        $('#user-empresa').text(result.empresa || 'SIN EMPRESA');
        
        // Actualizar estadísticas de pedidos
        $('#total-pedidos').text(pedidosInfo.total_pedidos || 0);
        $('#total-ventas').text('$' + (pedidosInfo.total_ventas || '0.00'));
        $('#pedidos-mes').text(pedidosInfo.total_pedidos_mes || 0);
        $('#ventas-mes').text('$' + (pedidosInfo.total_ventas_mes || '0.00'));
        
        // Actualizar información del último pedido
        const ultimoPedidoDiv = $('#ultimo-pedido-info');
        if (pedidosInfo.ultimo_pedido) {
            const ultimo = pedidosInfo.ultimo_pedido;
            ultimoPedidoDiv.html(`
                <hr>
                <h6>ÚLTIMO PEDIDO:</h6>
                <p><strong>FECHA:</strong> ${ultimo.fecha}</p>
                <p><strong>ORDEN:</strong> ${ultimo.numero_orden}</p>
                <p><strong>CLIENTE:</strong> ${ultimo.cliente}</p>
                <p><strong>TOTAL:</strong> <span class="badge badge-primary">$${ultimo.total}</span></p>
            `);
        } else {
            ultimoPedidoDiv.html(`
                <hr>
                <p class="text-muted">NO HAY PEDIDOS REGISTRADOS</p>
            `);
        }
        
        // Mostrar el panel de resumen
        $('#user-summary').show();
        
        // Auto-ocultar después de 15 segundos
        setTimeout(() => {
            $('#user-summary').fadeOut();
        }, 15000);
    }

    function playSuccessSound() {
        try {
            // Crear sonido de éxito simple
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (error) {
            console.log('No se pudo reproducir sonido:', error);
        }
    }

    // Limpiar al salir de la página
    window.addEventListener('beforeunload', async () => {
        if (html5QrCode && isScanning) {
            try {
                await html5QrCode.stop();
                html5QrCode.clear();
            } catch (error) {
                console.error('Error limpiando escáner:', error);
            }
        }
    });
</script>
@stop
