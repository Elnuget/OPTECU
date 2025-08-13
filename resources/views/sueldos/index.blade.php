@extends('adminlte::page')
@section('title', 'Sueldos')

@section('content_header')
<h1>Gestión de Sueldos</h1>
<p>Administración de sueldos y pagos</p>
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
</style>

<div class="card">
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

    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
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
});
</script>
@stop
