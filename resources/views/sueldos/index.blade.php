@extends('adminlte::page')
@section('title', 'Sueldos')

@section('content_header')
<h1>Gestión de Roles de Pago y Sueldos</h1>
<p>Administración de roles de pago y sueldos</p>
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
    
    /* Estilos para los select activos */
    .filtro-activo {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    }
    
    /* Estilos para las tarjetas de estadísticas */
    .stat-card {
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .nav-pills .nav-link.active {
        background-color: #3c8dbc;
    }
</style>

<!-- SECCIÓN DE ROLES DE PAGO -->
<div class="card">
    <div class="card-header bg-primary">
        <h3 class="card-title">Gestión de Roles de Pago</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Formulario de filtro para roles de pago -->
        <form id="rolPagoFilterForm" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rolPagoAno">Año:</label>
                        <select name="ano" id="rolPagoAno" class="form-control">
                            <option value="">Seleccione Año</option>
                            @for ($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rolPagoMes">Mes:</label>
                        <select name="mes" id="rolPagoMes" class="form-control">
                            <option value="">Seleccione Mes</option>
                            @foreach (['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', 
                                    '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'] as $num => $nombre)
                                <option value="{{ $num }}" {{ $num == date('m') ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rolPagoUsuario">Usuario:</label>
                        <select name="user_id" id="rolPagoUsuario" class="form-control select2">
                            <option value="">Seleccione Usuario</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btnConsultarRolPago" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                    <button type="button" id="btnLimpiarRolPago" class="btn btn-secondary">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Resultado de la consulta de rol de pagos -->
        <div id="rolPagoResultado" style="display: none;">
            <ul class="nav nav-pills mb-3" id="rolPagoTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-resumen" data-toggle="pill" href="#resumen">Resumen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-pedidos" data-toggle="pill" href="#pedidos">Pedidos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-retiros" data-toggle="pill" href="#retiros">Retiros</a>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Pestaña de Resumen -->
                <div class="tab-pane fade show active" id="resumen">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> Esta es una vista simplificada que muestra solo los pedidos realizados. La información de retiros no está disponible en esta versión.
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-info stat-card">
                                <div class="inner">
                                    <h3 id="totalPedidosMonto">$0.00</h3>
                                    <p>Total en Pedidos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="small-box-footer">
                                    <span id="totalPedidosCount">0</span> pedidos realizados
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-warning stat-card" style="opacity: 0.5;">
                                <div class="inner">
                                    <h3 id="totalRetirosMonto">$0.00</h3>
                                    <p>Total en Retiros</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="small-box-footer">
                                    No disponible en esta vista
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success stat-card">
                                <div class="inner">
                                    <h3 id="totalSaldoFinal">$0.00</h3>
                                    <p>Total de Ventas</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div class="small-box-footer">
                                    Ventas totales del período
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pestaña de Pedidos -->
                <div class="tab-pane fade" id="pedidos">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Los pedidos mostrados son aquellos donde el usuario seleccionado figura como vendedor.
                    </div>
                    <div class="table-responsive">
                        <table id="tablaPedidos" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Orden</th>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody id="bodyPedidos">
                                <!-- Aquí se cargarán los pedidos dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pestaña de Retiros -->
                <div class="tab-pane fade" id="retiros">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> La información de retiros no está disponible en esta vista simplificada. Esta sección muestra solo los pedidos realizados por el usuario.
                    </div>
                    <div class="table-responsive">
                        <table id="tablaRetiros" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Motivo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody id="bodyRetiros">
                                <tr><td colspan="3" class="text-center">Datos de retiros no disponibles en esta vista</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN DE SUELDOS -->
<div class="card">
    <div class="card-header bg-success">
        <h3 class="card-title">Gestión de Sueldos</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Contenido limpio sin resumen ni notificación -->

        <!-- Botones de acción -->
        <div class="btn-group mb-3">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearSueldoModal">
                <i class="fas fa-plus"></i> Registrar Sueldo
            </button>
        </div>

        <!-- Tabla de Sueldos -->
        <div class="table-responsive">
            <table id="sueldosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Empresa</th>
                        <th>Descripción</th>
                        <th>Valor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sueldos as $sueldo)
                    <tr>
                        <td>{{ $sueldo->fecha->format('d/m/Y') }}</td>
                        <td>{{ $sueldo->user ? $sueldo->user->name : 'N/A' }}</td>
                        <td>{{ $sueldo->empresa ? $sueldo->empresa->nombre : 'N/A' }}</td>
                        <td>{{ $sueldo->descripcion }}</td>
                        <td>${{ number_format($sueldo->valor, 2, ',', '.') }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info btn-editar" 
                                    data-id="{{ $sueldo->id }}"
                                    data-toggle="modal" 
                                    data-target="#editarSueldoModal">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar" 
                                    data-id="{{ $sueldo->id }}">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Crear Sueldo -->
<div class="modal fade" id="crearSueldoModal" tabindex="-1" role="dialog" aria-labelledby="crearSueldoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="crearSueldoModalLabel">Registrar Nuevo Sueldo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCrearSueldo">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="user_id">Usuario:</label>
                            <select name="user_id" id="user_id" class="form-control select2" required>
                                <option value="">Seleccionar Usuario</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="empresa_id">Empresa:</label>
                            <select name="empresa_id" id="empresa_id" class="form-control select2">
                                <option value="">Seleccionar Empresa (Opcional)</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fecha">Fecha:</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="valor">Valor ($):</label>
                            <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Sueldo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Sueldo -->
<div class="modal fade" id="editarSueldoModal" tabindex="-1" role="dialog" aria-labelledby="editarSueldoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="editarSueldoModalLabel">Editar Sueldo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarSueldo">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_sueldo_id" name="sueldo_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_user_id">Usuario:</label>
                            <select name="user_id" id="edit_user_id" class="form-control select2" required>
                                <option value="">Seleccionar Usuario</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_empresa_id">Empresa:</label>
                            <select name="empresa_id" id="edit_empresa_id" class="form-control select2">
                                <option value="">Seleccionar Empresa (Opcional)</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_fecha">Fecha:</label>
                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_valor">Valor ($):</label>
                            <input type="number" step="0.01" class="form-control" id="edit_valor" name="valor" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_descripcion">Descripción:</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Actualizar Sueldo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="confirmarEliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea eliminar este registro de sueldo? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable con opciones básicas
    $('#sueldosTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        order: [[0, 'desc']], // Ordenar por fecha descendente
        paging: false, // Sin paginación
        searching: true, // Mantener búsqueda
        info: false, // Quitar información de paginación
        responsive: true
    });
    
    // Inicializar tablas para roles de pago (se configuran cuando hay datos)
    initializeDataTables();

    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Marcar los campos de filtro preseleccionados como activos
    if ($('#rolPagoAno').val() !== '') {
        $('#rolPagoAno').addClass('filtro-activo');
    }
    
    if ($('#rolPagoMes').val() !== '') {
        $('#rolPagoMes').addClass('filtro-activo');
    }
    
    // Manejar el botón de consulta de rol de pago
    $('#btnConsultarRolPago').click(function() {
        consultarRolPago();
    });
    
    // Manejar el botón de limpiar filtros
    $('#btnLimpiarRolPago').click(function() {
        $('#rolPagoFilterForm')[0].reset();
        $('.select2').val('').trigger('change');
        $('#rolPagoResultado').hide();
    });
    
    // Destacar los campos de filtro cuando tienen valor
    $('#rolPagoAno, #rolPagoMes').change(function() {
        if ($(this).val() !== '') {
            $(this).addClass('filtro-activo');
        } else {
            $(this).removeClass('filtro-activo');
        }
    });
    
    $('#rolPagoUsuario').on('change', function() {
        if ($(this).val() !== '') {
            $(this).next('.select2-container').addClass('filtro-activo');
        } else {
            $(this).next('.select2-container').removeClass('filtro-activo');
        }
    });

    // Filtros activos
    $('#filtroAno, #filtroMes, #filtroEmpresa').change(function() {
        if ($(this).val() !== '') {
            $(this).addClass('filtro-activo');
        } else {
            $(this).removeClass('filtro-activo');
        }
    });

    // Formulario para crear sueldo
    $('#formCrearSueldo').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: "{{ route('sueldos.store') }}",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.mensaje,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Recargar la página
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.mensaje,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr, status, error) {
                const response = xhr.responseJSON;
                Swal.fire({
                    title: 'Error',
                    text: response?.mensaje || 'Error al registrar el sueldo',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

    // Cargar datos para editar
    $('.btn-editar').click(function() {
        const sueldoId = $(this).data('id');
        
        // Limpiar formulario
        $('#formEditarSueldo')[0].reset();
        
        // Hacer petición AJAX para obtener los datos
        $.ajax({
            url: `/sueldos/${sueldoId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    const sueldo = response.data;
                    
                    // Llenar el formulario con los datos
                    $('#edit_sueldo_id').val(sueldo.id);
                    $('#edit_user_id').val(sueldo.user_id).trigger('change');
                    $('#edit_empresa_id').val(sueldo.empresa_id).trigger('change');
                    $('#edit_fecha').val(sueldo.fecha.split('T')[0]);
                    $('#edit_valor').val(sueldo.valor);
                    $('#edit_descripcion').val(sueldo.descripcion);
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.mensaje,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar los datos del sueldo',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

    // Formulario para editar sueldo
    $('#formEditarSueldo').submit(function(e) {
        e.preventDefault();
        const sueldoId = $('#edit_sueldo_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: `/sueldos/${sueldoId}`,
            type: "PUT",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.mensaje,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Cerrar modal y recargar página
                        $('#editarSueldoModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.mensaje,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr, status, error) {
                const response = xhr.responseJSON;
                Swal.fire({
                    title: 'Error',
                    text: response?.mensaje || 'Error al actualizar el sueldo',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

    // Preparar eliminación
    let sueldoIdEliminar = null;
    $('.btn-eliminar').click(function() {
        sueldoIdEliminar = $(this).data('id');
        $('#confirmarEliminarModal').modal('show');
    });

    // Confirmar eliminación
    $('#confirmarEliminar').click(function() {
        if (sueldoIdEliminar) {
            $.ajax({
                url: `/sueldos/${sueldoIdEliminar}`,
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.mensaje,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // Cerrar modal y recargar página
                            $('#confirmarEliminarModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.mensaje,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        title: 'Error',
                        text: response?.mensaje || 'Error al eliminar el sueldo',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
    
    // Función para consultar el rol de pago
    function consultarRolPago() {
        // Validar que se hayan seleccionado los filtros requeridos
        const ano = $('#rolPagoAno').val();
        const mes = $('#rolPagoMes').val();
        const userId = $('#rolPagoUsuario').val();
        
        if (!ano || !mes || !userId) {
            Swal.fire({
                title: 'Campos Incompletos',
                text: 'Por favor seleccione año, mes y usuario para consultar',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Cargando...',
            text: 'Obteniendo datos del rol de pago',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Hacer la petición AJAX para obtener los datos (usando la nueva ruta simplificada)
        // Nota: El user_id enviado se utilizará para filtrar pedidos donde el usuario figura como vendedor (campo 'usuario' en la tabla pedidos)
        $.ajax({
            url: '/sueldos/pedidos-usuario',
            type: 'GET',
            data: {
                user_id: userId,
                ano: ano,
                mes: mes
            },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    mostrarDatosRolPago(response.data);
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.mensaje || 'No se pudieron obtener los datos',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: 'Error al consultar los datos: ' + (xhr.responseJSON?.mensaje || 'Error de conexión'),
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }
    
    // Función para mostrar los datos del rol de pago (versión simplificada)
    function mostrarDatosRolPago(data) {
        // Mostrar el contenedor de resultados
        $('#rolPagoResultado').show();
        
        // Actualizar los contadores y montos del resumen (solo pedidos)
        $('#totalPedidosMonto').text('$' + formatearNumero(data.pedidos_total || 0));
        $('#totalPedidosCount').text(data.pedidos ? data.pedidos.length : 0);
        
        // No tenemos información de retiros en esta versión simplificada
        $('#totalRetirosMonto').text('$0.00');
        $('#totalRetirosCount').text('0');
        
        // El saldo final ahora es simplemente el total de pedidos
        const saldoFinal = data.pedidos_total || 0;
        $('#totalSaldoFinal').text('$' + formatearNumero(saldoFinal));
        
        // Llenar tabla de pedidos
        llenarTablaPedidos(data.pedidos || []);
        
        // Limpiar tabla de retiros ya que no tenemos esos datos
        $('#bodyRetiros').empty().append('<tr><td colspan="3" class="text-center">Datos de retiros no disponibles en esta vista</td></tr>');
        
        // Reinicializar las DataTables
        reinicializarTablas();
    }
    
    // Función para llenar la tabla de pedidos
    function llenarTablaPedidos(pedidos) {
        const tbody = $('#bodyPedidos');
        tbody.empty();
        
        if (pedidos.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">No hay pedidos para mostrar</td></tr>');
            return;
        }
        
        pedidos.forEach(function(pedido) {
            // Manejar la fecha correctamente, puede venir en diferentes formatos
            let fechaFormateada;
            try {
                const fecha = new Date(pedido.fecha);
                // Verificar si la fecha es válida
                if (!isNaN(fecha.getTime())) {
                    fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' + 
                                     (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                     fecha.getFullYear();
                } else {
                    fechaFormateada = pedido.fecha || 'N/A';
                }
            } catch (error) {
                fechaFormateada = pedido.fecha || 'N/A';
            }
            
            const fila = `
                <tr>
                    <td>${fechaFormateada}</td>
                    <td>${pedido.numero_orden || 'N/A'}</td>
                    <td>${pedido.fact || 'N/A'}</td>
                    <td>${pedido.cliente || 'N/A'}</td>
                    <td>$${formatearNumero(pedido.total)}</td>
                    <td>$${formatearNumero(pedido.saldo)}</td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
    
    // Función para llenar la tabla de retiros
    function llenarTablaRetiros(retiros) {
        const tbody = $('#bodyRetiros');
        tbody.empty();
        
        if (retiros.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center">No hay retiros para mostrar</td></tr>');
            return;
        }
        
        retiros.forEach(function(retiro) {
            // Manejar la fecha correctamente, puede venir en diferentes formatos
            let fechaFormateada;
            try {
                const fecha = new Date(retiro.fecha || retiro.created_at);
                // Verificar si la fecha es válida
                if (!isNaN(fecha.getTime())) {
                    fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' + 
                                     (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                     fecha.getFullYear();
                } else {
                    fechaFormateada = (retiro.fecha || retiro.created_at || 'N/A');
                }
            } catch (error) {
                fechaFormateada = (retiro.fecha || retiro.created_at || 'N/A');
            }
            
            const fila = `
                <tr>
                    <td>${fechaFormateada}</td>
                    <td>${retiro.motivo || 'N/A'}</td>
                    <td>$${formatearNumero(retiro.valor)}</td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
    
    // Función para formatear números con separadores de miles y 2 decimales
    function formatearNumero(numero) {
        return parseFloat(numero).toLocaleString('es-EC', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Función para inicializar las DataTables
    function initializeDataTables() {
        // Configuración común para las tablas
        const dataTableConfig = {
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            paging: false,
            searching: true,
            info: false,
            responsive: true
        };
        
        // Las tablas se inicializarán cuando se muestren datos
        window.tablaPedidosInstance = null;
        window.tablaRetirosInstance = null;
    }
    
    // Función para reinicializar las tablas después de cargar datos
    function reinicializarTablas() {
        // Destruir instancias previas si existen
        if (window.tablaPedidosInstance) {
            window.tablaPedidosInstance.destroy();
        }
        if (window.tablaRetirosInstance) {
            window.tablaRetirosInstance.destroy();
        }
        
        // Crear nuevas instancias
        window.tablaPedidosInstance = $('#tablaPedidos').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            paging: false,
            searching: true,
            info: false,
            responsive: true,
            order: [[0, 'desc']]
        });
        
        window.tablaRetirosInstance = $('#tablaRetiros').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            paging: false,
            searching: true,
            info: false,
            responsive: true,
            order: [[0, 'desc']]
        });
    }
});
</script>
@stop
