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
        }

        #scanner-video {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            object-fit: cover;
        }

        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid #28a745;
            width: 200px;
            height: 200px;
            border-radius: 8px;
            pointer-events: none;
        }

        .scanner-overlay::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 2px solid rgba(40, 167, 69, 0.3);
            border-radius: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
                        <video id="scanner-video" autoplay muted playsinline></video>
                        <div class="scanner-overlay"></div>
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
                        <li>Espera la confirmación</li>
                        <li>¡Asistencia marcada automáticamente!</li>
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
<script src="https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
<script>
    let qrScanner = null;
    let isScanning = false;
    let cameras = [];
    let currentCameraIndex = 0;
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
            const devices = await navigator.mediaDevices.enumerateDevices();
            cameras = devices.filter(device => device.kind === 'videoinput');
            
            if (cameras.length > 0) {
                $('#camera-label').text(cameras.length + ' CÁMARA(S) DISPONIBLE(S)');
                if (cameras.length > 1) {
                    $('#switch-camera').show();
                }
            } else {
                updateStatus('NO SE ENCONTRARON CÁMARAS', 'error');
            }
        } catch (error) {
            console.error('Error obteniendo cámaras:', error);
            updateStatus('ERROR AL ACCEDER A LAS CÁMARAS', 'error');
        }
    }

    async function startScanning() {
        if (isScanning) return;

        try {
            const videoElement = document.getElementById('scanner-video');
            
            // Configurar QR Scanner
            qrScanner = new QrScanner(
                videoElement,
                result => processQRResult(result.data),
                {
                    returnDetailedScanResult: true,
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                }
            );

            await qrScanner.start();
            isScanning = true;
            
            $('#start-scan').hide();
            $('#stop-scan').show();
            $('#camera-status').text('ACTIVO');
            updateStatus('ESCÁNER ACTIVO - APUNTA LA CÁMARA AL QR', 'info');

        } catch (error) {
            console.error('Error iniciando escáner:', error);
            updateStatus('ERROR AL INICIAR LA CÁMARA: ' + error.message, 'error');
        }
    }

    function stopScanning() {
        if (!isScanning) return;

        if (qrScanner) {
            qrScanner.stop();
            qrScanner.destroy();
            qrScanner = null;
        }

        isScanning = false;
        $('#start-scan').show();
        $('#stop-scan').hide();
        $('#camera-status').text('DETENIDO');
        updateStatus('ESCÁNER DETENIDO', 'info');
    }

    function switchCamera() {
        if (!isScanning || cameras.length <= 1) return;

        currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
        
        if (qrScanner) {
            qrScanner.setCamera(cameras[currentCameraIndex].deviceId);
            $('#camera-label').text('CÁMARA: ' + (currentCameraIndex + 1) + '/' + cameras.length);
        }
    }

    async function processQRResult(data) {
        try {
            // Intentar parsear JSON
            let qrData;
            try {
                qrData = JSON.parse(data);
            } catch {
                // Si no es JSON, asumir que es solo el user_id
                qrData = { user_id: parseInt(data), type: 'asistencia' };
            }

            if (!qrData.user_id || qrData.type !== 'asistencia') {
                updateStatus('QR INVÁLIDO - NO ES UN CÓDIGO DE ASISTENCIA', 'error');
                return;
            }

            // Detener escáner temporalmente
            if (qrScanner) {
                qrScanner.stop();
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

            if (result.success) {
                updateStatus(result.message, 'success');
                addRecentScan(result);
                $('#last-action').text(result.action + ' - ' + result.hora);
                
                // Sonido de éxito (opcional)
                playSuccessSound();
            } else {
                updateStatus('ERROR: ' + result.message, 'error');
            }

        } catch (error) {
            console.error('Error procesando QR:', error);
            updateStatus('ERROR AL PROCESAR QR', 'error');
        }

        // Reanudar escáner después de 3 segundos
        setTimeout(() => {
            if (qrScanner && isScanning) {
                qrScanner.start();
            }
        }, 3000);
    }

    function updateStatus(message, type) {
        const statusElement = $('#status-message');
        statusElement.removeClass('status-success status-error status-info');
        statusElement.addClass('status-' + type);
        statusElement.text(message);
    }

    function addRecentScan(scanData) {
        const scanItem = {
            timestamp: new Date().toLocaleString(),
            user: scanData.user_name || 'USUARIO',
            action: scanData.action || 'ACCIÓN',
            hora: scanData.hora || '',
            status: scanData.success ? 'ÉXITO' : 'ERROR'
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

    function playSuccessSound() {
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
    }

    // Limpiar al salir de la página
    window.addEventListener('beforeunload', () => {
        if (qrScanner) {
            qrScanner.stop();
            qrScanner.destroy();
        }
    });
</script>
@stop
