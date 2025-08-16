@extends('adminlte::page')
@section('title', 'Declarantes')

@section('content_header')
<h1>Gestión de Declarantes</h1>
<p>Administración de declarantes para facturación</p>
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
        <!-- Formulario para crear/editar declarante -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-plus-circle"></i> 
                    <span id="formTitle">Agregar Nuevo Declarante</span>
                </h6>
            </div>
            <div class="card-body">
                <form id="declaranteForm" enctype="multipart/form-data">
                    <input type="hidden" id="declaranteId" name="id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ruc">RUC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ruc" name="ruc" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="firma">Certificado Digital de Firma</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="firma" name="firma" accept=".p12,.pem">
                                    <label class="custom-file-label" for="firma">Seleccionar certificado...</label>
                                </div>
                                <small class="form-text text-muted">Formatos permitidos: P12, PEM (certificados digitales)</small>
                                <div class="invalid-feedback"></div>
                                <!-- Vista previa del archivo -->
                                <div id="firmaPreview" class="mt-2" style="display: none;">
                                    <div class="border rounded p-2" style="max-width: 200px;">
                                        <div class="text-center">
                                            <i class="fas fa-certificate fa-3x text-primary mb-2"></i>
                                            <div id="firmaFileName" class="text-center small text-muted"></div>
                                            <div class="text-center small text-info">Certificado Digital</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-1 btn-block" id="removeFirma">
                                            <i class="fas fa-times"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                                <!-- Mostrar firma actual al editar -->
                                <div id="firmaActual" class="mt-2" style="display: none;">
                                    <label class="small text-muted">Certificado actual:</label>
                                    <div class="border rounded p-2" style="max-width: 200px;">
                                        <div class="text-center">
                                            <i class="fas fa-certificate fa-3x text-success mb-2"></i>
                                            <div id="firmaActualName" class="text-center small text-muted"></div>
                                            <div class="text-center small text-success">Certificado Digital</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="direccion_matriz">Dirección Matriz</label>
                                <input type="text" class="form-control" id="direccion_matriz" name="direccion_matriz">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="establecimiento">Establecimiento</label>
                                <input type="text" class="form-control" id="establecimiento" name="establecimiento" placeholder="001">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="punto_emision">Punto Emisión</label>
                                <input type="text" class="form-control" id="punto_emision" name="punto_emision" placeholder="001">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="secuencial">Secuencial</label>
                                <input type="text" class="form-control" id="secuencial" name="secuencial" placeholder="000000001">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="obligado_contabilidad">Obligado Contabilidad</label>
                                <select class="form-control" id="obligado_contabilidad" name="obligado_contabilidad">
                                    <option value="1">SÍ</option>
                                    <option value="0">NO</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success" id="submitButton">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancelEditButton" style="display: none;">
                                <i class="fas fa-times"></i> Cancelar Edición
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de declarantes -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list"></i> Lista de Declarantes
                </h6>
            </div>
            <div class="card-body p-0">
                <div id="declarantesLoading" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando declarantes...</p>
                </div>
                
                <div id="declarantesContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0" id="declarantesTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>RUC</th>
                                    <th>Firma</th>
                                    <th>Base Gravable</th>
                                    <th>IVA Débito Fiscal</th>
                                    <th>Total Facturado</th>
                                    <th>Cant. Facturas</th>
                                    <th>Establecimiento</th>
                                    <th>P. Emisión</th>
                                    <th>Obligado Cont.</th>
                                    <th>Secuencial</th>
                                    <th>Fecha Creación</th>
                                    <th width="140">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="declarantesTableBody">
                                <!-- Los datos se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="declarantesError" style="display: none;" class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="errorMessage">Error al cargar los declarantes.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar facturas del declarante -->
<div class="modal fade" id="facturasDeclaranteModal" tabindex="-1" role="dialog" aria-labelledby="facturasDeclaranteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="facturasDeclaranteModalLabel">
                    <i class="fas fa-file-invoice-dollar"></i> Declaraciones - <span id="nombreDeclarante"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Información del declarante -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user-tie"></i> Información del Declarante
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>Nombre:</strong> <span id="infoNombreDeclarante"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>RUC:</strong> <span id="infoRucDeclarante"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Total Facturas:</strong> <span id="infoCantidadFacturas" class="badge badge-info"></span></p>
                            </div>
                            <div class="col-md-3">
                                <div id="facturasLoading" class="text-center" style="display: none;">
                                    <i class="fas fa-spinner fa-spin text-primary"></i> Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de totales -->
                <div id="totalesFacturas" class="card mb-3" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calculator"></i> Resumen Fiscal
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>Base Gravable:</strong><br>
                                    <span class="h6 text-info" id="totalBaseFacturas">$0.00</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>IVA Débito Fiscal:</strong><br>
                                    <span class="h5 text-success" id="totalDebitoFiscal">$0.00</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>Total Facturado:</strong><br>
                                    <span class="h5 text-primary" id="totalFacturadoFacturas">$0.00</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="bg-light p-3 rounded text-center">
                                    <strong>Cantidad:</strong><br>
                                    <span class="h6 text-secondary" id="cantidadTotalFacturas">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de facturas -->
                <div id="tablaFacturasContainer" style="display: none;">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-list"></i> Detalle de Facturas
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" id="tablaFacturasDeclarante">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Fecha</th>
                                            <th>Orden</th>
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Base</th>
                                            <th>IVA</th>
                                            <th>Total</th>
                                            <th>XML</th>
                                        </tr>
                                    </thead>
                                    <tbody id="facturasTbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay facturas -->
                <div id="noFacturasMessage" class="alert alert-info text-center" style="display: none;">
                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                    <strong>Sin facturas</strong><br>
                    Este declarante no tiene facturas registradas.
                </div>

                <!-- Error message -->
                <div id="errorFacturas" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="errorFacturasMessage">Error al cargar las facturas.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
/* Estilos para el modal de declarantes */
#declarantesModal .modal-xl {
    max-width: 1200px;
}

#declarantesTable {
    width: 100%;
}

.badge-has-cert {
    background-color: #28a745;
    color: white;
}

.badge-no-cert {
    background-color: #dc3545;
    color: white;
}

/* Estilos para el modal de facturas del declarante */
#facturasDeclaranteModal .modal-xl {
    max-width: 1200px;
}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar declarantes al iniciar la página
    cargarDeclarantes();
    
    // Configurar formulario de declarante
    const declaranteForm = document.getElementById('declaranteForm');
    declaranteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        guardarDeclarante();
    });

    // Configurar botón de cancelar edición
    document.getElementById('cancelEditButton').addEventListener('click', function() {
        resetearFormulario();
    });

    // Configurar previsualización de firma
    document.getElementById('firma').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('firmaFileName').textContent = file.name;
                document.getElementById('firmaPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Configurar botón de eliminar firma
    document.getElementById('removeFirma').addEventListener('click', function() {
        document.getElementById('firma').value = '';
        document.getElementById('firmaPreview').style.display = 'none';
    });

    // Delegación de eventos para botones de editar y eliminar
    document.getElementById('declarantesTableBody').addEventListener('click', function(e) {
        const target = e.target;
        
        // Si se hizo clic en un botón editar
        if (target.classList.contains('btn-editar') || target.closest('.btn-editar')) {
            const btn = target.classList.contains('btn-editar') ? target : target.closest('.btn-editar');
            const id = btn.dataset.id;
            editarDeclarante(id);
        }
        
        // Si se hizo clic en un botón eliminar
        if (target.classList.contains('btn-eliminar') || target.closest('.btn-eliminar')) {
            const btn = target.classList.contains('btn-eliminar') ? target : target.closest('.btn-eliminar');
            const id = btn.dataset.id;
            eliminarDeclarante(id);
        }
        
        // Si se hizo clic en un botón ver facturas
        if (target.classList.contains('btn-facturas') || target.closest('.btn-facturas')) {
            const btn = target.classList.contains('btn-facturas') ? target : target.closest('.btn-facturas');
            const id = btn.dataset.id;
            mostrarFacturasDeclarante(id);
        }
    });
});

// Función para cargar declarantes
function cargarDeclarantes() {
    // Mostrar indicador de carga
    document.getElementById('declarantesLoading').style.display = 'block';
    document.getElementById('declarantesContent').style.display = 'none';
    document.getElementById('declarantesError').style.display = 'none';
    
    // Hacer petición AJAX
    fetch('/pedidos/declarantes/listar', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderizarDeclarantes(data.declarantes || data.data);
                document.getElementById('declarantesContent').style.display = 'block';
            } else {
                document.getElementById('errorMessage').textContent = data.message || 'Error al cargar declarantes';
                document.getElementById('declarantesError').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Error de conexión: ' + error.message;
            document.getElementById('declarantesError').style.display = 'block';
        })
        .finally(() => {
            document.getElementById('declarantesLoading').style.display = 'none';
        });
}

// Función para renderizar declarantes en la tabla
function renderizarDeclarantes(declarantes) {
    const tbody = document.getElementById('declarantesTableBody');
    tbody.innerHTML = '';
    
    if (!declarantes || declarantes.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = '<td colspan="13" class="text-center">No hay declarantes registrados</td>';
        tbody.appendChild(emptyRow);
        return;
    }
    
    declarantes.forEach((declarante, index) => {
        const row = document.createElement('tr');
        
        // Formatear valores numéricos
        const baseGravable = parseFloat(declarante.base_gravable || 0).toFixed(2);
        const ivaDebito = parseFloat(declarante.iva_debito || 0).toFixed(2);
        const totalFacturado = parseFloat(declarante.total_facturado || 0).toFixed(2);
        
        // Crear badge para certificado
        const certificadoBadge = declarante.firma 
            ? `<span class="badge badge-has-cert"><i class="fas fa-check"></i> Sí</span>`
            : `<span class="badge badge-no-cert"><i class="fas fa-times"></i> No</span>`;
        
        // Crear badge para obligado contabilidad
        const obligadoContabilidadBadge = declarante.obligado_contabilidad 
            ? `<span class="badge badge-has-cert"><i class="fas fa-check"></i> Sí</span>`
            : `<span class="badge badge-no-cert"><i class="fas fa-times"></i> No</span>`;
        
        // Construir botones de acción
        const acciones = `
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary btn-editar" data-id="${declarante.id}" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-info btn-facturas" data-id="${declarante.id}" title="Ver facturas">
                    <i class="fas fa-file-invoice-dollar"></i>
                </button>
                <button type="button" class="btn btn-danger btn-eliminar" data-id="${declarante.id}" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        // Construir HTML de la fila
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${declarante.nombre || 'N/A'}</td>
            <td>${declarante.ruc || 'N/A'}</td>
            <td class="text-center">${certificadoBadge}</td>
            <td class="text-right">$${numberFormat(baseGravable)}</td>
            <td class="text-right">$${numberFormat(ivaDebito)}</td>
            <td class="text-right">$${numberFormat(totalFacturado)}</td>
            <td class="text-center">${declarante.cantidad_facturas || 0}</td>
            <td>${declarante.establecimiento || 'N/A'}</td>
            <td>${declarante.punto_emision || 'N/A'}</td>
            <td class="text-center">${obligadoContabilidadBadge}</td>
            <td>${declarante.secuencial ? ('000' + declarante.secuencial).slice(-9) : '000000000'}</td>
            <td>${formatearFecha(declarante.created_at)}</td>
            <td>${acciones}</td>
        `;
        
        tbody.appendChild(row);
    });
}

// Función para guardar un declarante (crear o actualizar)
function guardarDeclarante() {
    // Obtener formulario y crear FormData
    const form = document.getElementById('declaranteForm');
    const formData = new FormData(form);
    const declaranteId = document.getElementById('declaranteId').value;
    const esEdicion = !!declaranteId;
    
    // URL para la petición
    const url = esEdicion ? `/declarantes/${declaranteId}` : '/declarantes';
    
    // Realizar petición
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Éxito',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            resetearFormulario();
            cargarDeclarantes();
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Ocurrió un error al guardar',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            
            // Mostrar errores en formulario
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
            text: 'Error de conexión: ' + error.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

// Función para editar un declarante
function editarDeclarante(id) {
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cargando',
        text: 'Obteniendo datos del declarante',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar petición
    fetch(`/declarantes/${id}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            Swal.close();
            if (data.success) {
                // Llenar formulario con datos
                const declarante = data.declarante;
                document.getElementById('declaranteId').value = declarante.id;
                document.getElementById('nombre').value = declarante.nombre;
                document.getElementById('ruc').value = declarante.ruc;
                
                // Llenar los campos nuevos
                if (document.getElementById('direccion_matriz')) {
                    document.getElementById('direccion_matriz').value = declarante.direccion_matriz || '';
                }
                if (document.getElementById('establecimiento')) {
                    document.getElementById('establecimiento').value = declarante.establecimiento || '';
                }
                if (document.getElementById('punto_emision')) {
                    document.getElementById('punto_emision').value = declarante.punto_emision || '';
                }
                if (document.getElementById('secuencial')) {
                    document.getElementById('secuencial').value = declarante.secuencial || '';
                }
                if (document.getElementById('obligado_contabilidad')) {
                    document.getElementById('obligado_contabilidad').value = declarante.obligado_contabilidad ? '1' : '0';
                }
                
                // Actualizar UI
                document.getElementById('formTitle').textContent = 'Editar Declarante';
                document.getElementById('submitButton').innerHTML = '<i class="fas fa-save"></i> Actualizar';
                document.getElementById('cancelEditButton').style.display = 'inline-block';
                
                // Si tiene firma, mostrar info
                if (declarante.firma) {
                    document.getElementById('firmaActualName').textContent = declarante.firma;
                    document.getElementById('firmaActual').style.display = 'block';
                }
                
                // Scroll hacia formulario
                document.querySelector('.card-header').scrollIntoView({behavior: 'smooth'});
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudo cargar el declarante',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

// Función para eliminar un declarante
function eliminarDeclarante(id) {
    // Confirmar eliminación
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción no se puede deshacer. Se eliminarán también todas las facturas asociadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar petición
            fetch(`/declarantes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Eliminado',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    cargarDeclarantes();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo eliminar el declarante',
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

// Función para mostrar facturas de un declarante
function mostrarFacturasDeclarante(id) {
    // Limpiar modal
    document.getElementById('nombreDeclarante').textContent = '';
    document.getElementById('infoNombreDeclarante').textContent = '';
    document.getElementById('infoRucDeclarante').textContent = '';
    document.getElementById('infoCantidadFacturas').textContent = '';
    document.getElementById('facturasTbody').innerHTML = '';
    document.getElementById('facturasLoading').style.display = 'block';
    document.getElementById('tablaFacturasContainer').style.display = 'none';
    document.getElementById('totalesFacturas').style.display = 'none';
    document.getElementById('noFacturasMessage').style.display = 'none';
    document.getElementById('errorFacturas').style.display = 'none';
    
    // Mostrar modal
    $('#facturasDeclaranteModal').modal('show');
    
    // Cargar datos
    fetch(`/declarantes/${id}/facturas`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const declarante = data.declarante;
                const facturas = data.facturas;
                
                // Actualizar info del declarante
                document.getElementById('nombreDeclarante').textContent = declarante.nombre;
                document.getElementById('infoNombreDeclarante').textContent = declarante.nombre;
                document.getElementById('infoRucDeclarante').textContent = declarante.ruc;
                document.getElementById('infoCantidadFacturas').textContent = facturas.length;
                
                if (facturas.length > 0) {
                    // Actualizar resumen
                    let totalBase = 0;
                    let totalIva = 0;
                    let totalFacturado = 0;
                    
                    // Renderizar facturas
                    const tbody = document.getElementById('facturasTbody');
                    facturas.forEach((factura, index) => {
                        // Sumar totales
                        totalBase += parseFloat(factura.monto);
                        totalIva += parseFloat(factura.iva);
                        totalFacturado += parseFloat(factura.monto) + parseFloat(factura.iva);
                        
                        // Crear fila
                        const row = document.createElement('tr');
                        
                        // XML badge
                        const xmlBadge = factura.xml 
                            ? `<span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>`
                            : `<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>`;
                        
                        // Tipo de documento
                        const tipoBadge = factura.tipo === 'factura'
                            ? `<span class="badge badge-primary">Factura</span>`
                            : `<span class="badge badge-info">Nota de venta</span>`;
                        
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${formatearFecha(factura.created_at)}</td>
                            <td>${factura.pedido ? factura.pedido.orden : 'N/A'}</td>
                            <td>${factura.pedido ? factura.pedido.nombre_cliente : 'N/A'}</td>
                            <td>${tipoBadge}</td>
                            <td class="text-right">$${numberFormat(factura.monto)}</td>
                            <td class="text-right">$${numberFormat(factura.iva)}</td>
                            <td class="text-right">$${numberFormat(parseFloat(factura.monto) + parseFloat(factura.iva))}</td>
                            <td class="text-center">${xmlBadge}</td>
                        `;
                        
                        tbody.appendChild(row);
                    });
                    
                    // Actualizar totales
                    document.getElementById('totalBaseFacturas').textContent = '$' + numberFormat(totalBase.toFixed(2));
                    document.getElementById('totalDebitoFiscal').textContent = '$' + numberFormat(totalIva.toFixed(2));
                    document.getElementById('totalFacturadoFacturas').textContent = '$' + numberFormat(totalFacturado.toFixed(2));
                    document.getElementById('cantidadTotalFacturas').textContent = facturas.length;
                    
                    // Mostrar tabla y totales
                    document.getElementById('tablaFacturasContainer').style.display = 'block';
                    document.getElementById('totalesFacturas').style.display = 'block';
                } else {
                    // Mostrar mensaje de no facturas
                    document.getElementById('noFacturasMessage').style.display = 'block';
                }
            } else {
                // Mostrar error
                document.getElementById('errorFacturasMessage').textContent = data.message || 'Error al cargar facturas';
                document.getElementById('errorFacturas').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorFacturasMessage').textContent = 'Error de conexión';
            document.getElementById('errorFacturas').style.display = 'block';
        })
        .finally(() => {
            document.getElementById('facturasLoading').style.display = 'none';
        });
}

// Función para resetear el formulario
function resetearFormulario() {
    document.getElementById('declaranteForm').reset();
    document.getElementById('declaranteId').value = '';
    document.getElementById('formTitle').textContent = 'Agregar Nuevo Declarante';
    document.getElementById('submitButton').innerHTML = '<i class="fas fa-save"></i> Guardar';
    document.getElementById('cancelEditButton').style.display = 'none';
    document.getElementById('firmaActual').style.display = 'none';
    document.getElementById('firmaPreview').style.display = 'none';
    
    // Valores predeterminados para campos nuevos
    if (document.getElementById('establecimiento')) {
        document.getElementById('establecimiento').value = '001';
    }
    if (document.getElementById('punto_emision')) {
        document.getElementById('punto_emision').value = '001';
    }
    if (document.getElementById('secuencial')) {
        document.getElementById('secuencial').value = '000000001';
    }
    if (document.getElementById('obligado_contabilidad')) {
        document.getElementById('obligado_contabilidad').value = '1'; // Por defecto "SÍ"
    }
    
    // Eliminar validaciones
    const invalidInputs = document.querySelectorAll('.is-invalid');
    invalidInputs.forEach(input => {
        input.classList.remove('is-invalid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
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
