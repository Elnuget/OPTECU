@extends('adminlte::page')
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
                                    <th>Orden</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Base</th>
                                    <th>IVA</th>
                                    <th>Total</th>
                                    <th>XML</th>
                                    <th width="110">Acciones</th>
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
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar pedidos pendientes
    cargarPedidosPendientes();
    
    // Cargar declarantes
    cargarDeclarantes();
    
    // Cargar facturas
    cargarFacturas();
    
    // Eventos
    document.getElementById('pedidoSelect').addEventListener('change', function() {
        const pedidoId = this.value;
        if (pedidoId) {
            cargarDetallesPedido(pedidoId);
        } else {
            ocultarDetallesPedido();
        }
    });
    
    // Formulario de factura
    document.getElementById('crearFacturaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        crearFactura();
    });
    
    // Botón cancelar
    document.getElementById('cancelarFacturaBtn').addEventListener('click', function() {
        resetearFormulario();
    });
    
    // Botones de filtro
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        cargarFacturas();
    });
    
    document.getElementById('btnLimpiarFiltros').addEventListener('click', function() {
        document.getElementById('filtroDeclarante').value = '';
        document.getElementById('filtroTipoDocumento').value = '';
        document.getElementById('filtroFechaDesde').value = '';
        document.getElementById('filtroFechaHasta').value = '';
        cargarFacturas();
    });
});

// Función para cargar pedidos pendientes
function cargarPedidosPendientes() {
    fetch('/admin/pedidos/pendientes-facturar')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pedidos = data.pedidos;
                const select = document.getElementById('pedidoSelect');
                
                // Limpiar select
                select.innerHTML = '<option value="">Seleccione un pedido...</option>';
                
                // Agregar opciones
                pedidos.forEach(pedido => {
                    const option = document.createElement('option');
                    option.value = pedido.id;
                    option.textContent = `Orden #${pedido.orden} - ${pedido.nombre_cliente} - $${numberFormat(pedido.total)}`;
                    select.appendChild(option);
                });
                
                // Mensaje si no hay pedidos
                if (pedidos.length === 0) {
                    const option = document.createElement('option');
                    option.disabled = true;
                    option.textContent = 'No hay pedidos pendientes de facturar';
                    select.appendChild(option);
                }
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Error al cargar pedidos pendientes',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

// Función para cargar declarantes
function cargarDeclarantes() {
    fetch('/admin/declarantes/listar')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const declarantes = data.declarantes;
                const selectFactura = document.getElementById('declaranteSelect');
                const selectFiltro = document.getElementById('filtroDeclarante');
                
                // Limpiar selects
                selectFactura.innerHTML = '<option value="">Seleccione un declarante...</option>';
                selectFiltro.innerHTML = '<option value="">Todos los declarantes</option>';
                
                // Agregar opciones
                declarantes.forEach(declarante => {
                    // Para el select de factura
                    const optionFactura = document.createElement('option');
                    optionFactura.value = declarante.id;
                    optionFactura.textContent = `${declarante.nombre} (${declarante.ruc})`;
                    selectFactura.appendChild(optionFactura);
                    
                    // Para el select de filtro
                    const optionFiltro = document.createElement('option');
                    optionFiltro.value = declarante.id;
                    optionFiltro.textContent = `${declarante.nombre} (${declarante.ruc})`;
                    selectFiltro.appendChild(optionFiltro);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Función para cargar detalles de un pedido
function cargarDetallesPedido(pedidoId) {
    // Mostrar indicador de carga
    document.getElementById('detallesLoading').style.display = 'block';
    document.getElementById('infoPedidoCard').style.display = 'none';
    document.getElementById('detallesProductos').style.display = 'none';
    document.getElementById('datosFacturaCard').style.display = 'none';
    
    // Hacer petición
    fetch(`/admin/pedidos/${pedidoId}/detalles`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pedido = data.pedido;
                const inventarios = data.inventarios || [];
                const lunas = data.lunas || [];
                
                // Actualizar campo oculto con el ID del pedido
                document.getElementById('factPedidoId').value = pedidoId;
                
                // Actualizar información del pedido
                document.getElementById('factCliente').textContent = pedido.nombre_cliente;
                document.getElementById('factTotal').textContent = numberFormat(pedido.total);
                document.getElementById('factFecha').textContent = formatearFecha(pedido.created_at);
                
                // Inicializar totales
                let totalBase = 0;
                let totalIva = 0;
                
                // Procesar inventarios (armazones y accesorios)
                if (inventarios.length > 0) {
                    const tbody = document.getElementById('tablaInventarios');
                    tbody.innerHTML = '';
                    
                    let subtotalBaseInventarios = 0;
                    let subtotalIvaInventarios = 0;
                    
                    inventarios.forEach(item => {
                        // Calcular valores
                        const precioBase = parseFloat(item.precio);
                        const descuento = parseFloat(item.descuento || 0);
                        const precioFinal = precioBase - descuento;
                        const base = precioFinal / 1.12; // Asumiendo IVA del 12%
                        const iva = precioFinal - base;
                        
                        // Actualizar subtotales
                        subtotalBaseInventarios += base;
                        subtotalIvaInventarios += iva;
                        
                        // Crear fila
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.codigo}</td>
                            <td class="text-right">$${numberFormat(precioBase)}</td>
                            <td class="text-right">$${numberFormat(descuento)}</td>
                            <td class="text-right">$${numberFormat(precioFinal)}</td>
                            <td class="text-right">$${numberFormat(base)}</td>
                            <td class="text-right">$${numberFormat(iva)}</td>
                        `;
                        
                        tbody.appendChild(row);
                    });
                    
                    // Actualizar subtotales
                    document.getElementById('subtotalBaseInventarios').textContent = '$' + numberFormat(subtotalBaseInventarios);
                    document.getElementById('subtotalIvaInventarios').textContent = '$' + numberFormat(subtotalIvaInventarios);
                    document.getElementById('cardInventarios').style.display = 'block';
                    
                    // Agregar a los totales generales
                    totalBase += subtotalBaseInventarios;
                    totalIva += subtotalIvaInventarios;
                } else {
                    document.getElementById('cardInventarios').style.display = 'none';
                }
                
                // Procesar lunas
                if (lunas.length > 0) {
                    const tbody = document.getElementById('tablaLunas');
                    tbody.innerHTML = '';
                    
                    let subtotalBaseLunas = 0;
                    let subtotalIvaLunas = 0;
                    
                    lunas.forEach(luna => {
                        // Calcular valores
                        const precio = parseFloat(luna.precio);
                        const descuento = parseFloat(luna.descuento || 0);
                        const precioFinal = precio - descuento;
                        const base = precioFinal / 1.12; // Asumiendo IVA del 12%
                        const iva = precioFinal - base;
                        
                        // Actualizar subtotales
                        subtotalBaseLunas += base;
                        subtotalIvaLunas += iva;
                        
                        // Crear fila
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${luna.medida || 'N/A'}</td>
                            <td>${luna.tipo || 'N/A'}</td>
                            <td>${luna.material || 'N/A'}</td>
                            <td class="text-right">$${numberFormat(precio)}</td>
                            <td class="text-right">$${numberFormat(descuento)}</td>
                            <td class="text-right">$${numberFormat(precioFinal)}</td>
                            <td class="text-right">$${numberFormat(base)}</td>
                            <td class="text-right">$${numberFormat(iva)}</td>
                        `;
                        
                        tbody.appendChild(row);
                    });
                    
                    // Actualizar subtotales
                    document.getElementById('subtotalBaseLunas').textContent = '$' + numberFormat(subtotalBaseLunas);
                    document.getElementById('subtotalIvaLunas').textContent = '$' + numberFormat(subtotalIvaLunas);
                    document.getElementById('cardLunas').style.display = 'block';
                    
                    // Agregar a los totales generales
                    totalBase += subtotalBaseLunas;
                    totalIva += subtotalIvaLunas;
                } else {
                    document.getElementById('cardLunas').style.display = 'none';
                }
                
                // Actualizar totales
                document.getElementById('totalBaseCalculado').textContent = '$' + numberFormat(totalBase);
                document.getElementById('totalIvaCalculado').textContent = '$' + numberFormat(totalIva);
                document.getElementById('montoTotalCalculado').textContent = '$' + numberFormat(totalBase + totalIva);
                
                // Actualizar campos del formulario
                document.getElementById('montoFactura').value = totalBase.toFixed(2);
                document.getElementById('ivaFactura').value = totalIva.toFixed(2);
                
                // Mostrar secciones
                document.getElementById('infoPedidoCard').style.display = 'block';
                document.getElementById('detallesProductos').style.display = 'block';
                document.getElementById('datosFacturaCard').style.display = 'block';
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Error al cargar detalles del pedido',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        })
        .finally(() => {
            document.getElementById('detallesLoading').style.display = 'none';
        });
}

// Función para ocultar detalles del pedido
function ocultarDetallesPedido() {
    document.getElementById('infoPedidoCard').style.display = 'none';
    document.getElementById('detallesProductos').style.display = 'none';
    document.getElementById('datosFacturaCard').style.display = 'none';
}

// Función para crear factura
function crearFactura() {
    // Validar formulario
    const pedidoId = document.getElementById('factPedidoId').value;
    const declaranteId = document.getElementById('declaranteSelect').value;
    const tipoFactura = document.getElementById('tipoFactura').value;
    
    if (!pedidoId) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar un pedido',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    if (!declaranteId) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar un declarante',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        document.getElementById('declaranteSelect').classList.add('is-invalid');
        return;
    }
    
    if (!tipoFactura) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar un tipo de documento',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        document.getElementById('tipoFactura').classList.add('is-invalid');
        return;
    }
    
    // Obtener datos del formulario
    const formData = new FormData(document.getElementById('crearFacturaForm'));
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Procesando',
        text: 'Creando factura...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar petición
    fetch('/admin/facturas/crear', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Éxito',
                text: data.message || 'Factura creada correctamente',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            
            // Resetear formulario
            resetearFormulario();
            
            // Actualizar listados
            cargarPedidosPendientes();
            cargarFacturas();
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Error al crear factura',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            
            // Mostrar errores específicos
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = data.errors[key][0];
                        }
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error de conexión',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

// Función para cargar facturas
function cargarFacturas() {
    // Obtener valores de filtros
    const declaranteId = document.getElementById('filtroDeclarante').value;
    const tipoDocumento = document.getElementById('filtroTipoDocumento').value;
    const fechaDesde = document.getElementById('filtroFechaDesde').value;
    const fechaHasta = document.getElementById('filtroFechaHasta').value;
    
    // Construir URL con parámetros
    let url = '/admin/facturas/listar';
    const params = new URLSearchParams();
    
    if (declaranteId) params.append('declarante_id', declaranteId);
    if (tipoDocumento) params.append('tipo', tipoDocumento);
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
                const facturas = data.facturas;
                
                if (facturas.length > 0) {
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
        const monto = parseFloat(factura.monto);
        const iva = parseFloat(factura.iva);
        const total = monto + iva;
        
        // XML badge
        const xmlBadge = factura.xml 
            ? `<span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>`
            : `<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>`;
        
        // Tipo de documento
        const tipoBadge = factura.tipo === 'factura'
            ? `<span class="badge badge-primary">Factura</span>`
            : `<span class="badge badge-info">Nota de venta</span>`;
        
        // Acciones
        const acciones = `
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-info btn-ver" data-id="${factura.id}" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-danger btn-eliminar" data-id="${factura.id}" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${formatearFecha(factura.created_at)}</td>
            <td>${factura.declarante ? factura.declarante.nombre : 'N/A'}</td>
            <td>${factura.pedido ? factura.pedido.orden : 'N/A'}</td>
            <td>${factura.pedido ? factura.pedido.nombre_cliente : 'N/A'}</td>
            <td>${tipoBadge}</td>
            <td class="text-right">$${numberFormat(monto)}</td>
            <td class="text-right">$${numberFormat(iva)}</td>
            <td class="text-right">$${numberFormat(total)}</td>
            <td class="text-center">${xmlBadge}</td>
            <td>${acciones}</td>
        `;
        
        tbody.appendChild(row);
    });
    
    // Agregar eventos a los botones
    document.querySelectorAll('.btn-ver').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            verDetalleFactura(id);
        });
    });
    
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            eliminarFactura(id);
        });
    });
}

// Función para calcular totales de facturas
function calcularTotalesFacturas(facturas) {
    let totalBase = 0;
    let totalIva = 0;
    let totalFacturado = 0;
    
    facturas.forEach(factura => {
        totalBase += parseFloat(factura.monto);
        totalIva += parseFloat(factura.iva);
        totalFacturado += parseFloat(factura.monto) + parseFloat(factura.iva);
    });
    
    // Actualizar elementos HTML
    document.getElementById('totalBaseFacturasResumen').textContent = '$' + numberFormat(totalBase.toFixed(2));
    document.getElementById('totalDebitoFiscalResumen').textContent = '$' + numberFormat(totalIva.toFixed(2));
    document.getElementById('totalFacturadoFacturasResumen').textContent = '$' + numberFormat(totalFacturado.toFixed(2));
    document.getElementById('cantidadTotalFacturasResumen').textContent = facturas.length;
    
    // Mostrar resumen
    document.getElementById('totalesFacturasResumen').style.display = 'block';
}

// Función para ver detalle de una factura
function verDetalleFactura(id) {
    // Implementar según necesidades
    alert('Función para ver detalle de la factura ' + id);
}

// Función para eliminar una factura
function eliminarFactura(id) {
    // Confirmar eliminación
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar petición
            fetch(`/admin/facturas/eliminar/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Eliminada',
                        text: data.message || 'Factura eliminada correctamente',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    
                    // Recargar facturas y pedidos
                    cargarFacturas();
                    cargarPedidosPendientes();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Error al eliminar la factura',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

// Función para resetear el formulario de crear factura
function resetearFormulario() {
    document.getElementById('crearFacturaForm').reset();
    document.getElementById('pedidoSelect').selectedIndex = 0;
    ocultarDetallesPedido();
    
    // Limpiar clases de validación
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

// Función de utilidad para formatear números
function numberFormat(number) {
    return parseFloat(number).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Función de utilidad para formatear fechas
function formatearFecha(fechaStr) {
    if (!fechaStr) return 'N/A';
    const fecha = new Date(fechaStr);
    return fecha.toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>
@stop
