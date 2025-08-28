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
            <div class="card-header bg-info text-white" style="cursor: pointer;" data-toggle="collapse" data-target="#formularioDeclarante" aria-expanded="false" aria-controls="formularioDeclarante">
                <h6 class="card-title mb-0">
                    <i class="fas fa-plus-circle"></i> 
                    <span id="formTitle">Agregar Nuevo Declarante</span>
                    <i class="fas fa-chevron-down float-right" id="chevronIcon"></i>
                </h6>
            </div>
            <div class="card-body collapse" id="formularioDeclarante">
                <form id="declaranteForm" enctype="multipart/form-data">
                    @csrf
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
                                <label for="firma">Certificado Digital P12 <span class="text-info">(SRI Ecuador)</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="firma" name="firma">
                                    <label class="custom-file-label" for="firma">Seleccionar certificado P12...</label>
                                </div>
                                <small class="form-text text-muted">
                                    <strong>Formato recomendado P12/PFX:</strong> Certificados del SRI Ecuador.
                                    <br>
                                    <i class="fas fa-info-circle text-info"></i> 
                                    <strong>Descarga desde:</strong> <a href="https://srienlinea.sri.gob.ec/certificados/" target="_blank">Portal SRI</a>
                                </small>
                                <div class="invalid-feedback"></div>
                                <!-- Vista previa del archivo -->
                                <div id="firmaPreview" class="mt-2" style="display: none;">
                                    <div class="border rounded p-2" style="max-width: 200px;">
                                        <div class="text-center">
                                            <i class="fas fa-certificate fa-3x text-primary mb-2"></i>
                                            <div id="firmaFileName" class="text-center small text-muted"></div>
                                            <div class="text-center small text-info">Certificado P12</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-1 btn-block" id="removeFirma">
                                            <i class="fas fa-times"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                                <!-- Mostrar firma actual al editar -->
                                <div id="firmaActual" class="mt-2" style="display: none;">
                                    <label class="small text-muted">Certificado P12 actual:</label>
                                    <div class="border rounded p-2" style="max-width: 200px;">
                                        <div class="text-center">
                                            <i class="fas fa-certificate fa-3x text-success mb-2"></i>
                                            <div id="firmaActualName" class="text-center small text-muted"></div>
                                            <div class="text-center small text-success">Certificado P12</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="password_certificado">Contraseña del Certificado <span class="text-info">(Opcional)</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_certificado" name="password_certificado" placeholder="Contraseña del certificado P12">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle text-info"></i> 
                                    <strong>Opcional:</strong> Si guarda la contraseña, no se le pedirá cada vez que facture.
                                    <br>
                                    <i class="fas fa-shield-alt text-success"></i> 
                                    <strong>Segura:</strong> La contraseña se guarda encriptada en la base de datos.
                                </small>
                                <div class="invalid-feedback"></div>
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
                    <i class="fas fa-id-card"></i> Lista de Declarantes
                </h6>
            </div>
            <div class="card-body">
                <div id="declarantesLoading" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando declarantes...</p>
                </div>
                
                <div id="declarantesContent" style="display: none;">
                    <div class="row" id="declarantesCardContainer">
                        <!-- Los datos de las tarjetas se cargarán aquí dinámicamente -->
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
/* Estilos para el formulario plegable */
.card-header[data-toggle="collapse"] {
    transition: all 0.3s ease;
}

.card-header[data-toggle="collapse"]:hover {
    background-color: #17a2b8 !important;
}

#chevronIcon {
    transition: transform 0.3s ease;
}

#chevronIcon.collapsed {
    transform: rotate(-90deg);
}

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

/* Estilos para las tarjetas de declarantes */
#declarantesCardContainer .card {
    transition: all 0.3s ease;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

#declarantesCardContainer .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25);
}

#declarantesCardContainer .border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

#declarantesCardContainer .badge {
    margin-right: 5px;
    display: inline-block;
    margin-bottom: 5px;
    padding: 5px 8px;
}

#declarantesCardContainer .card-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding: 0.75rem;
}
</style>
<!-- Incluir SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
@stop

@section('js')
<!-- Incluir SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar declarantes al iniciar la página
    cargarDeclarantes();
    
    // Configurar el ícono chevron para el formulario plegable
    $('#formularioDeclarante').on('show.bs.collapse', function () {
        $('#chevronIcon').removeClass('collapsed');
    });
    
    $('#formularioDeclarante').on('hide.bs.collapse', function () {
        $('#chevronIcon').addClass('collapsed');
    });
    
    // Establecer el estado inicial del chevron (colapsado)
    $('#chevronIcon').addClass('collapsed');
    
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
    document.getElementById('declarantesCardContainer').addEventListener('click', function(e) {
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
    fetch('/declarantes', {
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

// Función para renderizar declarantes en tarjetas
function renderizarDeclarantes(declarantes) {
    const container = document.getElementById('declarantesCardContainer');
    container.innerHTML = '';
    
    if (!declarantes || declarantes.length === 0) {
        container.innerHTML = '<div class="col-12 text-center p-4"><div class="alert alert-info">No hay declarantes registrados</div></div>';
        return;
    }
    
    declarantes.forEach((declarante, index) => {
        // Crear badge para certificado P12
        const certificadoBadge = declarante.firma 
            ? `<span class="badge badge-has-cert"><i class="fas fa-check"></i> Certificado P12: Sí</span>`
            : `<span class="badge badge-no-cert"><i class="fas fa-times"></i> Certificado P12: No</span>`;
        
        // Crear badge para obligado contabilidad
        const obligadoContabilidadBadge = declarante.obligado_contabilidad 
            ? `<span class="badge badge-has-cert"><i class="fas fa-check"></i> Obligado Contabilidad: Sí</span>`
            : `<span class="badge badge-no-cert"><i class="fas fa-times"></i> Obligado Contabilidad: No</span>`;
        
        // Crear columna para la tarjeta
        const cardCol = document.createElement('div');
        cardCol.className = 'col-lg-4 col-md-6 mb-4';
        
        // Construir HTML de la tarjeta
        cardCol.innerHTML = `
            <div class="card h-100 border-left-info">
                <div class="card-header bg-gradient-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-tie mr-1"></i> ${declarante.nombre || 'Sin nombre'}
                    </h6>
                </div>
                <div class="card-body pb-0">
                    <div class="row mb-2">
                        <div class="col-12">
                            <p class="mb-1"><strong>RUC:</strong> ${declarante.ruc || 'N/A'}</p>
                            <p class="mb-1"><strong>Establecimiento:</strong> ${declarante.establecimiento || 'N/A'}</p>
                            <p class="mb-1"><strong>Punto de Emisión:</strong> ${declarante.punto_emision || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="mb-2">
                        ${certificadoBadge} ${obligadoContabilidadBadge}
                    </div>
                </div>
                <div class="card-footer bg-light text-center">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary btn-editar" data-id="${declarante.id}" title="Editar">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button type="button" class="btn btn-sm btn-info btn-facturas" data-id="${declarante.id}" title="Ver facturas">
                            <i class="fas fa-file-invoice-dollar"></i> Facturas
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="${declarante.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(cardCol);
    });
}

// Función para guardar un declarante (crear o actualizar)
function guardarDeclarante() {
    // Obtener formulario y crear FormData
    const form = document.getElementById('declaranteForm');
    const formData = new FormData(form);
    const declaranteId = document.getElementById('declaranteId').value;
    const esEdicion = !!declaranteId;
    
    // Depuración: mostrar todos los datos del FormData
    console.log('Datos que se enviarán:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
    }
    
    // URL para la petición
    const url = esEdicion ? `/declarantes/${declaranteId}` : '/declarantes';
    
    // Si es edición, agregar el método PUT en el FormData
    if (esEdicion) {
        formData.append('_method', 'PUT');
    }
    
    // Asegurarnos de que el token CSRF esté en el FormData
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData.append('_token', csrfToken);
    
    // Realizar petición
    fetch(url, {
        method: 'POST', // Siempre POST, el _method se encarga de la simulación PUT
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Respuesta del servidor:', response);
        
        // Para errores 422, queremos obtener los detalles de validación
        if (response.status === 422) {
            return response.json().then(errData => {
                console.log('Datos de error 422:', errData);
                throw { 
                    status: 422, 
                    errors: errData.errors || {}, 
                    message: 'Error de validación', 
                    details: errData.message || 'Hay errores en el formulario'
                };
            });
        }
        
        if (!response.ok) {
            // Intentar obtener un mensaje detallado si está disponible
            return response.text().then(text => {
                try {
                    const jsonData = JSON.parse(text);
                    throw new Error(jsonData.message || 'Error en la respuesta del servidor: ' + response.status);
                } catch (e) {
                    throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
            });
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
        console.error('Error completo:', error);
        
        // Si es un error de validación (422)
        if (error.status === 422 && error.errors) {
            console.log('Detalles de errores de validación:', error.errors);
            
            let errorMessages = '';
            Object.keys(error.errors).forEach(key => {
                errorMessages += `• ${key}: ${error.errors[key].join(', ')}\n`;
            });
            
            Swal.fire({
                title: 'Error de validación',
                text: 'Hay errores en el formulario. Por favor, revise los campos marcados.',
                html: `<div class="text-left">Los siguientes campos tienen errores:<br><pre>${errorMessages}</pre></div>`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            
            // Mostrar errores en el formulario
            Object.keys(error.errors).forEach(key => {
                const input = document.getElementById(key);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = error.errors[key][0];
                    } else {
                        console.warn(`No se encontró elemento de feedback para el campo ${key}`);
                    }
                } else {
                    console.warn(`No se encontró el elemento de entrada con ID ${key}`);
                }
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión: ' + (error.message || 'Desconocido'),
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
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

                if (document.getElementById('obligado_contabilidad')) {
                    document.getElementById('obligado_contabilidad').value = declarante.obligado_contabilidad ? '1' : '0';
                }
                
                // Actualizar UI
                document.getElementById('formTitle').textContent = 'Editar Declarante';
                document.getElementById('submitButton').innerHTML = '<i class="fas fa-save"></i> Actualizar';
                document.getElementById('cancelEditButton').style.display = 'inline-block';
                
                // Expandir el formulario si está colapsado
                $('#formularioDeclarante').collapse('show');
                
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
    
    // Colapsar el formulario
    $('#formularioDeclarante').collapse('hide');
    
    // Resetear el campo de archivo (no se resetea automáticamente)
    const firmaInput = document.getElementById('firma');
    if (firmaInput) {
        firmaInput.value = '';
        // Resetear también la etiqueta del archivo seleccionado
        const fileLabel = firmaInput.nextElementSibling;
        if (fileLabel) {
            fileLabel.textContent = 'Seleccionar certificado P12...';
        }
    }
    
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

// Función para toggle de contraseña
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password_certificado');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el ícono
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }
});
</script>
@stop
