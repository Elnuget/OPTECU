@extends('adminlte::page')
@section('title', 'Facturas')

@section('plugins.head')
<!-- Meta tag para CSRF -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Font Awesome para iconos de WhatsApp -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@stop

@section('content_header')
<h1>Facturas</h1>
<p>Listado de facturas emitidas</p>
@if (session('error'))
    <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
        <strong>{{ session('mensaje') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Listado de Facturas</h3>
            <a href="{{ route('facturas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Factura
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filtros sencillos -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filtroDeclarante">Declarante</label>
                    <select id="filtroDeclarante" class="form-control">
                        <option value="">Todos los declarantes</option>
                        @foreach ($declarantes ?? [] as $declarante)
                            <option value="{{ $declarante->id }}">{{ $declarante->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filtroFechaDesde">Desde</label>
                    <input type="date" id="filtroFechaDesde" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filtroFechaHasta">Hasta</label>
                    <input type="date" id="filtroFechaHasta" class="form-control">
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="btnFiltrar" class="btn btn-info mr-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <button id="btnLimpiar" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Limpiar
                </button>
            </div>
        </div>

        <!-- Loading spinner -->
        <div id="facturasLoading" class="text-center p-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Cargando facturas...</p>
        </div>

        <!-- Mensaje sin facturas -->
        <div id="noFacturasMessage" class="alert alert-info" style="display: none;">
            <i class="fas fa-info-circle"></i> No se encontraron facturas con los filtros seleccionados.
        </div>

        <!-- Mensaje de error -->
        <div id="facturasError" class="alert alert-danger" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="errorFacturasMessage">Error al cargar las facturas.</span>
        </div>

        <!-- Tabla de facturas -->
        <div id="facturasContent" style="display: none;">
            <div class="table-responsive">
                <table id="facturasTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Declarante</th>
                            <th>Información</th>
                            <th>Estado</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">IVA</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">XML</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="facturasTableBody">
                        <!-- Los datos se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>

            <!-- Resumen de totales -->
            <div id="totalesFacturasResumen" class="row mt-4" style="display: none;">
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Facturas</span>
                            <span id="totalFacturas" class="info-box-number">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Subtotal</span>
                            <span id="totalSubtotal" class="info-box-number">$0.00</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">IVA</span>
                            <span id="totalIVA" class="info-box-number">$0.00</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">Total</span>
                            <span id="granTotal" class="info-box-number">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// Variables globales
const FACTURA_SHOW_URL = "{{ url('facturas') }}";

document.addEventListener('DOMContentLoaded', function() {
    // Cargar facturas al cargar la página
    cargarFacturas();
    
    // Evento al hacer clic en el botón filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        cargarFacturas();
    });
    
    // Evento al hacer clic en el botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function() {
        document.getElementById('filtroDeclarante').value = '';
        document.getElementById('filtroFechaDesde').value = '';
        document.getElementById('filtroFechaHasta').value = '';
        cargarFacturas();
    });
});

// Función para formatear el estado de la factura
function formatearEstadoFactura(estado) {
    const estados = {
        'CREADA': { class: 'badge-secondary', icon: 'fas fa-file', text: 'Creada' },
        'FIRMADA': { class: 'badge-info', icon: 'fas fa-certificate', text: 'Firmada' },
        'ENVIADA': { class: 'badge-warning', icon: 'fas fa-paper-plane', text: 'Enviada' },
        'RECIBIDA': { class: 'badge-primary', icon: 'fas fa-inbox', text: 'Recibida' },
        'AUTORIZADA': { class: 'badge-success', icon: 'fas fa-check-circle', text: 'Autorizada' },
        'DEVUELTA': { class: 'badge-danger', icon: 'fas fa-times-circle', text: 'Devuelta' },
        'NO_AUTORIZADA': { class: 'badge-dark', icon: 'fas fa-ban', text: 'No Autorizada' }
    };
    
    const estadoInfo = estados[estado] || estados['CREADA'];
    
    return `<span class="badge ${estadoInfo.class}" title="Estado: ${estadoInfo.text}">
                <i class="${estadoInfo.icon}"></i> ${estadoInfo.text}
            </span>`;
}

// Función para cargar facturas
function cargarFacturas() {
    // Obtener valores de filtros
    const declaranteId = document.getElementById('filtroDeclarante').value;
    const fechaDesde = document.getElementById('filtroFechaDesde').value;
    const fechaHasta = document.getElementById('filtroFechaHasta').value;
    
    // Construir URL con parámetros
    let url = '{{ route("facturas.listar") }}';
    const params = new URLSearchParams();
    
    if (declaranteId) params.append('declarante_id', declaranteId);
    if (fechaDesde) params.append('fecha_desde', fechaDesde);
    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Mostrar indicador de carga
    document.getElementById('facturasLoading').style.display = 'block';
    document.getElementById('facturasContent').style.display = 'none';
    document.getElementById('facturasError').style.display = 'none';
    document.getElementById('noFacturasMessage').style.display = 'none';
    document.getElementById('totalesFacturasResumen').style.display = 'none';
    
    // Hacer petición
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const facturas = data.data;
                
                if (facturas && facturas.length > 0) {
                    // Renderizar facturas
                    renderizarFacturas(facturas);
                    
                    // Mostrar tabla
                    document.getElementById('facturasContent').style.display = 'block';
                    
                    // Calcular totales
                    calcularTotalesFacturas(facturas);
                } else {
                    // Mostrar mensaje de no facturas
                    document.getElementById('noFacturasMessage').style.display = 'block';
                }
            } else {
                // Mostrar error
                document.getElementById('errorFacturasMessage').textContent = data.message || 'Error al cargar facturas';
                document.getElementById('facturasError').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorFacturasMessage').textContent = 'Error de conexión';
            document.getElementById('facturasError').style.display = 'block';
        })
        .finally(() => {
            document.getElementById('facturasLoading').style.display = 'none';
        });
}

// Función para renderizar facturas en la tabla
function renderizarFacturas(facturas) {
    const tbody = document.getElementById('facturasTableBody');
    tbody.innerHTML = '';
    
    facturas.forEach((factura, index) => {
        const row = document.createElement('tr');
        
        // Formatear valores
        const monto = parseFloat(factura.monto || 0);
        const iva = parseFloat(factura.iva || 0);
        const total = monto + iva;
        
        // XML badge
        const xmlBadge = factura.xml 
            ? `<span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>`
            : `<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>`;
        
        // Tipo de documento
        const tipoBadge = factura.tipo === 'venta'
            ? `<span class="badge badge-primary">Venta</span>`
            : `<span class="badge badge-info">Compra</span>`;
        
        // Estado de la factura
        const estadoBadge = formatearEstadoFactura(factura.estado || 'CREADA');
        
        // Información del pedido
        const numeroPedido = factura.pedido ? (factura.pedido.numero_orden || 'N/A') : 'N/A';
        const celularPedidoDisplay = factura.pedido ? (factura.pedido.celular || 'N/A') : 'N/A';
        const celularPedido = factura.pedido ? (factura.pedido.celular || '') : '';
        const correoPedido = factura.pedido ? (factura.pedido.correo_electronico || 'N/A') : 'N/A';
        const clientePedido = factura.pedido ? factura.pedido.cliente : 'N/A';
        
        // Columna de información combinada
        const informacionCompleta = `
            <div class="d-flex flex-column">
                <div class="mb-1">
                    <strong><i class="fas fa-user text-primary"></i> ${clientePedido}</strong>
                </div>
                <div class="mb-1">
                    <small><i class="fas fa-mobile-alt text-success"></i> ${celularPedidoDisplay}</small>
                </div>
                <div>
                    <small><i class="fas fa-envelope text-warning"></i> ${correoPedido}</small>
                </div>
            </div>
        `;
        
        // Acciones
        const showUrl = `${FACTURA_SHOW_URL}/${factura.id}`;
        const pdfUrl = `${FACTURA_SHOW_URL}/${factura.id}/pdf`;
        const emailPedido = factura.pedido ? (factura.pedido.correo_electronico || '') : '';
        
        // Limpiar número de WhatsApp (quitar espacios, guiones, etc.) - usar celularPedido ya declarado arriba
        const whatsappNumber = celularPedido.replace(/[^\d]/g, '');
        
        const acciones = `
            <div class="btn-group btn-group-sm" role="group">
                <a href="${showUrl}" class="btn btn-info" title="Ver detalle de la factura #${factura.id}">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="${pdfUrl}" target="_blank" class="btn btn-secondary" title="Ver PDF de la factura #${factura.id}">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                ${emailPedido ? `
                <button type="button" class="btn btn-success btn-enviar-email" 
                        data-id="${factura.id}" 
                        data-email="${emailPedido}" 
                        data-pdf-url="${pdfUrl}"
                        title="Enviar PDF por email a ${emailPedido}">
                    <i class="fas fa-envelope"></i> Email
                </button>
                ` : `
                <button type="button" class="btn btn-secondary" disabled 
                        title="No hay correo asociado al pedido">
                    <i class="fas fa-envelope-open"></i> Sin Email
                </button>
                `}
                ${whatsappNumber ? `
                <button type="button" class="btn btn-warning btn-enviar-whatsapp" 
                        data-id="${factura.id}" 
                        data-numero="${whatsappNumber}" 
                        data-pdf-url="${pdfUrl}"
                        data-cliente="${factura.pedido ? factura.pedido.cliente : 'Cliente'}"
                        title="Enviar enlace por WhatsApp a ${celularPedidoDisplay}">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </button>
                ` : `
                <button type="button" class="btn btn-secondary" disabled 
                        title="No hay número de WhatsApp asociado al pedido">
                    <i class="fab fa-whatsapp"></i> Sin WhatsApp
                </button>
                `}
                <button type="button" class="btn btn-danger btn-eliminar" data-id="${factura.id}" title="Eliminar factura #${factura.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        row.innerHTML = `
            <td><strong>${factura.id}</strong></td>
            <td>${formatearFecha(factura.created_at)}</td>
            <td>${factura.declarante ? factura.declarante.nombre : 'N/A'}</td>
            <td>${informacionCompleta}</td>
            <td>${estadoBadge}</td>
            <td class="text-right">$${numberFormat(monto)}</td>
            <td class="text-right">$${numberFormat(iva)}</td>
            <td class="text-right">$${numberFormat(total)}</td>
            <td class="text-center">${xmlBadge}</td>
            <td>${acciones}</td>
        `;
        
        tbody.appendChild(row);
    });
    
    // Agregar eventos a los botones de eliminar
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            confirmarEliminar(id);
        });
    });
    
    // Agregar eventos a los botones de enviar email
    document.querySelectorAll('.btn-enviar-email').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const email = this.dataset.email;
            const pdfUrl = this.dataset.pdfUrl;
            enviarFacturaPorEmail(id, email, pdfUrl);
        });
    });
    
    // Agregar eventos a los botones de WhatsApp
    document.querySelectorAll('.btn-enviar-whatsapp').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const numero = this.dataset.numero;
            const pdfUrl = this.dataset.pdfUrl;
            const cliente = this.dataset.cliente;
            enviarFacturaPorWhatsApp(id, numero, pdfUrl, cliente);
        });
    });
}

// Función para calcular totales
function calcularTotalesFacturas(facturas) {
    let totalSubtotal = 0;
    let totalIVA = 0;
    let granTotal = 0;
    
    facturas.forEach(factura => {
        const monto = parseFloat(factura.monto || 0);
        const iva = parseFloat(factura.iva || 0);
        
        totalSubtotal += monto;
        totalIVA += iva;
        granTotal += (monto + iva);
    });
    
    // Mostrar totales
    document.getElementById('totalFacturas').textContent = facturas.length;
    document.getElementById('totalSubtotal').textContent = '$' + numberFormat(totalSubtotal);
    document.getElementById('totalIVA').textContent = '$' + numberFormat(totalIVA);
    document.getElementById('granTotal').textContent = '$' + numberFormat(granTotal);
    
    // Mostrar sección de totales
    document.getElementById('totalesFacturasResumen').style.display = 'block';
}

// Función para confirmar eliminación
function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta factura?')) {
        eliminarFactura(id);
    }
}

// Función para enviar factura por email
function enviarFacturaPorEmail(facturaId, email, pdfUrl) {
    // Verificar que SweetAlert esté disponible, sino usar confirm nativo
    const confirmar = typeof Swal !== 'undefined' 
        ? () => Swal.fire({
            title: 'Confirmar envío',
            text: `¿Desea enviar el PDF de la factura #${facturaId} al correo ${email}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'Cancelar'
        })
        : () => Promise.resolve({ isConfirmed: confirm(`¿Desea enviar el PDF de la factura #${facturaId} al correo ${email}?`) });
    
    confirmar().then((result) => {
        if (!result.isConfirmed) return;
        
        // Encontrar el botón y mostrar indicador de carga
        const btnElement = document.querySelector(`[data-id="${facturaId}"].btn-enviar-email`);
        if (!btnElement) {
            console.error('No se encontró el botón de enviar email');
            return;
        }
        
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btnElement.disabled = true;
        
        // Preparar datos para envío
        const formData = new FormData();
        formData.append('factura_id', facturaId);
        formData.append('email', email);
        formData.append('pdf_url', pdfUrl);
        
        // Realizar petición
        fetch('{{ route("facturas.enviar-email") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Éxito
                const mensaje = `✅ PDF de la factura #${facturaId} enviado correctamente a ${email}`;
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Enviado correctamente',
                        text: mensaje,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(mensaje);
                }
                
                // Cambiar temporalmente el icono a éxito
                btnElement.innerHTML = '<i class="fas fa-check"></i> Enviado';
                btnElement.classList.remove('btn-success');
                btnElement.classList.add('btn-info');
                
                // Restaurar después de 3 segundos
                setTimeout(() => {
                    btnElement.innerHTML = originalHtml;
                    btnElement.classList.remove('btn-info');
                    btnElement.classList.add('btn-success');
                    btnElement.disabled = false;
                }, 3000);
                
            } else {
                // Error del servidor
                const mensajeError = data.message || 'Error desconocido al enviar el email';
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error al enviar',
                        text: mensajeError,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(`❌ ${mensajeError}`);
                }
                
                btnElement.innerHTML = originalHtml;
                btnElement.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error al enviar email:', error);
            
            const mensajeError = 'Error de conexión al enviar el email. Verifique su conexión a internet.';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error de conexión',
                    text: mensajeError,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                alert(`❌ ${mensajeError}`);
            }
            
            btnElement.innerHTML = originalHtml;
            btnElement.disabled = false;
        });
    });
}

// Función para enviar factura por WhatsApp
function enviarFacturaPorWhatsApp(facturaId, numero, pdfUrl, cliente) {
    // Limpiar número (quitar espacios, guiones, paréntesis, etc.)
    let numeroLimpio = numero.replace(/[^\d+]/g, '');
    
    // Si el número empieza con 0, quitarlo (común en Ecuador)
    if (numeroLimpio.startsWith('0')) {
        numeroLimpio = numeroLimpio.substring(1);
    }
    
    // Si no tiene código de país, agregar código de Ecuador (+593)
    if (!numeroLimpio.startsWith('+') && !numeroLimpio.startsWith('593')) {
        numeroLimpio = '593' + numeroLimpio;
    }
    
    // Quitar el + si existe
    numeroLimpio = numeroLimpio.replace('+', '');
    
    // Validar que el número tenga al menos 10 dígitos
    if (!numeroLimpio || numeroLimpio.length < 10) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Número inválido',
                text: `El número de WhatsApp "${numero}" no es válido. Debe tener al menos 10 dígitos.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`❌ El número de WhatsApp "${numero}" no es válido`);
        }
        return;
    }
    
    // Formatear número para mostrar
    const numeroFormateado = numeroLimpio.startsWith('593') 
        ? `+${numeroLimpio.substring(0,3)} ${numeroLimpio.substring(3,5)} ${numeroLimpio.substring(5,8)} ${numeroLimpio.substring(8)}`
        : `+${numeroLimpio}`;
    
    // Preparar el mensaje de WhatsApp
    const nombreCliente = cliente || 'Cliente';
    const nombreEmpresa = '{{ config("app.name", "OPTECU") }}';
    const fechaActual = new Date().toLocaleDateString('es-ES');

    const mensaje = `¡Hola ${nombreCliente}! 👋

📄 Su factura #${facturaId} de ${nombreEmpresa} está lista.

📅 Fecha: ${fechaActual}
🔗 Enlace directo: ${pdfUrl}

📋 Puede ver, descargar e imprimir su factura desde el enlace.

¡Gracias por confiar en nosotros! 🙏

---
💼 ${nombreEmpresa} - Sistema de Facturación Electrónica
📱 Este es un mensaje automático.`;

    // Mostrar modal de confirmación con el mensaje completo
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿Enviar factura #${facturaId} por WhatsApp?`,
            html: `
                <div style="text-align: left; margin-bottom: 15px;">
                    <strong>📱 Número de destino:</strong> ${numeroFormateado}<br>
                    <strong>👤 Cliente:</strong> ${cliente || 'N/A'}
                </div>
                <div class="mensaje-whatsapp">
${mensaje}
                </div>
                <div style="font-size: 12px; color: #6c757d; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> Al confirmar, se abrirá WhatsApp con este mensaje pre-llenado.
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25d366',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Sí, enviar por WhatsApp',
            cancelButtonText: 'Cancelar',
            width: '600px',
            customClass: {
                popup: 'swal-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarMensajeWhatsApp();
            }
        });
    } else {
        // Fallback sin Swal
        if (confirm(`¿Desea enviar el enlace de la factura #${facturaId} por WhatsApp al número ${numeroFormateado}?\n\nMensaje:\n${mensaje}`)) {
            enviarMensajeWhatsApp();
        }
    }

    // Función interna para enviar el mensaje
    function enviarMensajeWhatsApp() {
        // Construir URL de WhatsApp
        const mensajeCodificado = encodeURIComponent(mensaje);
        const whatsappUrl = `https://wa.me/${numeroLimpio}?text=${mensajeCodificado}`;

        // Abrir WhatsApp en nueva ventana
        try {
            window.open(whatsappUrl, '_blank');

            // Feedback visual de éxito
            const btnElement = document.querySelector(`[data-id="${facturaId}"].btn-enviar-whatsapp`);
            if (btnElement) {
                const originalHtml = btnElement.innerHTML;
                const originalClass = btnElement.className;

                // Cambiar temporalmente a éxito
                btnElement.innerHTML = '<i class="fas fa-check"></i> Enviado';
                btnElement.className = btnElement.className.replace('btn-warning', 'btn-success');

                // Mostrar mensaje de éxito
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'WhatsApp abierto',
                        text: `Se abrió WhatsApp para enviar la factura #${facturaId} al número ${numero}`,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(`✅ WhatsApp abierto para enviar factura #${facturaId}`);
                }

                // Restaurar después de 3 segundos
                setTimeout(() => {
                    btnElement.innerHTML = originalHtml;
                    btnElement.className = originalClass;
                }, 3000);
            }

        } catch (error) {
            console.error('Error al abrir WhatsApp:', error);

            // Ofrecer alternativa de copiar enlace
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error al abrir WhatsApp',
                    html: `No se pudo abrir WhatsApp automáticamente.<br><br>¿Desea copiar el enlace de la factura al portapapeles?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-copy"></i> Copiar enlace',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        copiarAlPortapapeles(pdfUrl).then(() => {
                            Swal.fire({
                                title: 'Enlace copiado',
                                text: 'El enlace de la factura se ha copiado al portapapeles. Puede pegarlo en WhatsApp manualmente.',
                                icon: 'success',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }).catch(() => {
                            Swal.fire({
                                title: 'Enlace de la factura',
                                html: `<p>No se pudo copiar automáticamente. Copie este enlace:</p><br><input type="text" value="${pdfUrl}" readonly style="width: 100%; padding: 5px; border: 1px solid #ddd;" onclick="this.select()">`,
                                icon: 'info',
                                confirmButtonText: 'OK'
                            });
                        });
                    }
                });
            } else {
                const copiarEnlace = confirm('No se pudo abrir WhatsApp. ¿Desea copiar el enlace de la factura?');
                if (copiarEnlace) {
                    copiarAlPortapapeles(pdfUrl).then(() => {
                        alert('✅ Enlace copiado al portapapeles');
                    }).catch(() => {
                        prompt('Copie este enlace:', pdfUrl);
                    });
                }
            }
        }
    }
}

// Función auxiliar para copiar texto al portapapeles
function copiarAlPortapapeles(texto) {
    if (navigator.clipboard && window.isSecureContext) {
        // Usar API moderna del portapapeles
        return navigator.clipboard.writeText(texto);
    } else {
        // Fallback para navegadores más antiguos
        return new Promise((resolve, reject) => {
            const textArea = document.createElement('textarea');
            textArea.value = texto;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                textArea.remove();
                resolve();
            } catch (error) {
                textArea.remove();
                reject(error);
            }
        });
    }
}

// Función para eliminar factura
function eliminarFactura(id) {
    fetch('{{ url("facturas") }}/' + id, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Factura eliminada correctamente');
            cargarFacturas(); // Recargar facturas
        } else {
            alert(data.message || 'Error al eliminar la factura');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

// Función para formatear números
function numberFormat(value) {
    return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para formatear fechas
function formatearFecha(fechaStr) {
    if (!fechaStr) return 'N/A';
    
    const fecha = new Date(fechaStr);
    const dia = fecha.getDate().toString().padStart(2, '0');
    const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
    const anio = fecha.getFullYear();
    
    return `${dia}/${mes}/${anio}`;
}
</script>
@stop
@section('title', 'Facturas')

@section('content_header')
<h1>Gestión de Facturas</h1>
<p>Administración de facturas y documentos fiscales</p>
@if (session('error'))
    <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
        <strong>{{ session('mensaje') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif @stop

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
    .btn {
        text-transform: uppercase !important;
    }
</style>

<div class="card">
    <div class="card-body">
        <!-- Formulario para crear factura -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-file-invoice"></i> Crear Nueva Factura
                </h6>
            </div>
            <div class="card-body">
                <form id="crearFacturaForm">
                    <input type="hidden" id="factPedidoId" name="pedido_id">
                    
                    <!-- Selección de pedido -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-shopping-cart"></i> Seleccionar Pedido
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pedidoSelect">Pedido <span class="text-danger">*</span></label>
                                        <select class="form-control" id="pedidoSelect" required>
                                            <option value="">Seleccione un pedido...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div id="detallesLoading" class="text-center mt-4" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando detalles...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del pedido -->
                    <div class="card mb-3" id="infoPedidoCard" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Información del Pedido
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Cliente:</strong> <span id="factCliente"></span></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Total Original:</strong> $<span id="factTotal"></span></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Fecha:</strong> <span id="factFecha"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose de productos -->
                    <div id="detallesProductos" style="display: none;">
                        <!-- Inventarios/Accesorios -->
                        <div class="card mb-3" id="cardInventarios" style="display: none;">
                            <div class="card-header bg-info text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-box"></i> Armazones y Accesorios
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Código</th>
                                                <th>Precio Base</th>
                                                <th>Descuento</th>
                                                <th>Precio Final</th>
                                                <th>Base</th>
                                                <th>IVA</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaInventarios">
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr class="font-weight-bold">
                                                <td colspan="4">SUBTOTAL ARMAZONES:</td>
                                                <td id="subtotalBaseInventarios">$0.00</td>
                                                <td id="subtotalIvaInventarios">$0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lunas -->
                        <div class="card mb-3" id="cardLunas" style="display: none;">
                            <div class="card-header bg-success text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-eye"></i> Lunas
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Medida</th>
                                                <th>Tipo</th>
                                                <th>Material</th>
                                                <th>Precio</th>
                                                <th>Desc.</th>
                                                <th>Final</th>
                                                <th>Base</th>
                                                <th>IVA</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaLunas">
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr class="font-weight-bold">
                                                <td colspan="6">SUBTOTAL LUNAS:</td>
                                                <td id="subtotalBaseLunas">$0.00</td>
                                                <td id="subtotalIvaLunas">$0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Totales -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-calculator"></i> Totales de la Factura
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="bg-light p-3 rounded text-center">
                                            <strong>Base Total:</strong><br>
                                            <span class="h4 text-primary" id="totalBaseCalculado">$0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-light p-3 rounded text-center">
                                            <strong>IVA Total:</strong><br>
                                            <span class="h4 text-warning" id="totalIvaCalculado">$0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-light p-3 rounded text-center">
                                            <strong>Monto Total:</strong><br>
                                            <span class="h4 text-success" id="montoTotalCalculado">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de factura -->
                    <div class="card mb-3" id="datosFacturaCard" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-file-alt"></i> Datos de la Factura
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="declaranteSelect">Declarante <span class="text-danger">*</span></label>
                                        <select class="form-control" id="declaranteSelect" name="declarante_id" required>
                                            <option value="">Seleccione un declarante...</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipoFactura">Tipo de Documento <span class="text-danger">*</span></label>
                                        <select class="form-control" id="tipoFactura" name="tipo" required>
                                            <option value="">Seleccione el tipo...</option>
                                            <option value="factura">Factura</option>
                                            <option value="nota_venta">Nota de Venta</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="montoFactura">Monto (Base) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="montoFactura" name="monto" required readonly>
                                        <small class="text-muted">Este campo se calcula automáticamente</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ivaFactura">IVA <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="ivaFactura" name="iva" required readonly>
                                        <small class="text-muted">Este campo se calcula automáticamente</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="xmlRuta">Ruta del XML (opcional)</label>
                                        <input type="text" class="form-control" id="xmlRuta" name="xml" placeholder="Ej: facturas/factura_123.xml">
                                        <small class="form-text text-muted">Ruta donde se almacenará el archivo XML de la factura</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success" id="guardarFacturaBtn">
                                        <i class="fas fa-save"></i> Crear Factura
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancelarFacturaBtn">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de facturas -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list"></i> Listado de Facturas
                </h6>
            </div>
            <div class="card-body">
                <!-- Filtros de búsqueda -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroDeclarante">Declarante:</label>
                            <select class="form-control" id="filtroDeclarante">
                                <option value="">Todos los declarantes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroTipoDocumento">Tipo de Documento:</label>
                            <select class="form-control" id="filtroTipoDocumento">
                                <option value="">Todos los tipos</option>
                                <option value="factura">Facturas</option>
                                <option value="nota_venta">Notas de Venta</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroFechaDesde">Desde:</label>
                            <input type="date" class="form-control" id="filtroFechaDesde">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroFechaHasta">Hasta:</label>
                            <input type="date" class="form-control" id="filtroFechaHasta">
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12 text-right">
                        <button type="button" class="btn btn-primary" id="btnFiltrar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                            <i class="fas fa-eraser"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Resumen de totales -->
                <div id="totalesFacturasResumen" class="card mb-3" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calculator"></i> Resumen Fiscal
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>Base Gravable:</strong><br>
                                    <span class="h6 text-info" id="totalBaseFacturasResumen">$0.00</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>IVA Débito Fiscal:</strong><br>
                                    <span class="h5 text-success" id="totalDebitoFiscalResumen">$0.00</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>Total Facturado:</strong><br>
                                    <span class="h5 text-primary" id="totalFacturadoFacturasResumen">$0.00</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>Cantidad:</strong><br>
                                    <span class="h6 text-secondary" id="cantidadTotalFacturasResumen">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de facturas -->
                <div id="facturasLoading" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando facturas...</p>
                </div>
                
                <div id="facturasContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0" id="facturasTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Declarante</th>
                                    <th>Información</th>
                                    <th>Base</th>
                                    <th>IVA</th>
                                    <th>Total</th>
                                    <th>XML</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="facturasTableBody">
                                <!-- Los datos se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="facturasError" style="display: none;" class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="errorFacturasMessage">Error al cargar las facturas.</span>
                </div>
                
                <!-- Mensaje si no hay facturas -->
                <div id="noFacturasMessage" class="alert alert-info text-center" style="display: none;">
                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                    <strong>Sin facturas</strong><br>
                    No hay facturas que coincidan con los criterios de búsqueda.
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
/* Estilos para el modal de crear factura */
#crearFacturaModal .modal-xl {
    max-width: 1200px;
}

/* Estilos para la tabla de facturas */
#facturasTable {
    width: 100%;
}

/* Estilos para botones de acción de facturas */
.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.btn-enviar-email:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-enviar-email .fas {
    margin-right: 2px;
}

/* Estilos para botón WhatsApp */
.btn-enviar-whatsapp {
    background-color: #25d366 !important;
    border-color: #25d366 !important;
    color: white !important;
}

.btn-enviar-whatsapp:hover {
    background-color: #1da851 !important;
    border-color: #1da851 !important;
    color: white !important;
}

.btn-enviar-whatsapp:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-enviar-whatsapp .fab {
    margin-right: 2px;
}

/* Animación para spinner */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spinner.fa-spin {
    animation: spin 1s linear infinite;
}

/* Mejoras visuales para botones */
.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

/* Tooltip personalizado */
.btn[title]:hover {
    position: relative;
}

/* Responsive para botones en pantallas pequeñas */
@media (max-width: 768px) {
    .btn-group-sm .btn {
        font-size: 0.65rem;
        padding: 0.2rem 0.3rem;
    }
    
    .btn-group-sm .btn .fas,
    .btn-group-sm .btn .fab {
        margin-right: 1px;
        font-size: 0.8rem;
    }
    
    /* Ocultar texto en móviles, solo mostrar iconos */
    .btn-group-sm .btn {
        white-space: nowrap;
        overflow: hidden;
    }
}

/* Mejora visual para el grupo de botones */
.btn-group-sm {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group-sm .btn {
    transition: all 0.2s ease-in-out;
}

.btn-group-sm .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Estilos para el modal de WhatsApp */
.swal-wide {
    width: 90% !important;
    max-width: 700px !important;
}

.swal2-html-container {
    text-align: left !important;
}

.swal2-html-container strong {
    color: #495057;
}

.swal2-html-container .mensaje-whatsapp {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.4;
    color: #495057;
    white-space: pre-line;
    max-height: 300px;
    overflow-y: auto;
}

/* Estilos responsive para móviles */
@media (max-width: 768px) {
    .swal-wide {
        width: 95% !important;
        max-width: none !important;
    }
    
    .swal2-html-container .mensaje-whatsapp {
        font-size: 12px;
        padding: 10px;
        max-height: 200px;
    }
}
</style>
@stop


