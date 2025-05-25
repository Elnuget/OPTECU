@extends('adminlte::page')

@section('title', 'ROL DE PAGOS')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1>ROL DE PAGOS</h1>
    <p>ADMINISTRACIÓN DE ROLES DE PAGO</p>
    @include('components.sueldos.alerts')
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@stop

@section('content')
    @php
        $empresa = \App\Models\Empresa::first();
        $tipoSucursal = $empresa ? $empresa->getTipoSucursal() : 'todas';
        $users = \App\Models\User::orderBy('name')->get();
        
        // Obtener mes y año actual
        $mesActual = date('m');
        $anoActual = date('Y');
    @endphp

    @include('components.sueldos.styles')
    
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="selectUsuario">SELECCIONAR USUARIO:</label>
                        <select class="form-control" id="selectUsuario">
                            <option value="">SELECCIONE UN USUARIO</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" data-nombre="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filtroMes">MES:</label>
                                <select class="form-control" id="filtroMes">
                                    <option value="01" {{ $mesActual == '01' ? 'selected' : '' }}>ENERO</option>
                                    <option value="02" {{ $mesActual == '02' ? 'selected' : '' }}>FEBRERO</option>
                                    <option value="03" {{ $mesActual == '03' ? 'selected' : '' }}>MARZO</option>
                                    <option value="04" {{ $mesActual == '04' ? 'selected' : '' }}>ABRIL</option>
                                    <option value="05" {{ $mesActual == '05' ? 'selected' : '' }}>MAYO</option>
                                    <option value="06" {{ $mesActual == '06' ? 'selected' : '' }}>JUNIO</option>
                                    <option value="07" {{ $mesActual == '07' ? 'selected' : '' }}>JULIO</option>
                                    <option value="08" {{ $mesActual == '08' ? 'selected' : '' }}>AGOSTO</option>
                                    <option value="09" {{ $mesActual == '09' ? 'selected' : '' }}>SEPTIEMBRE</option>
                                    <option value="10" {{ $mesActual == '10' ? 'selected' : '' }}>OCTUBRE</option>
                                    <option value="11" {{ $mesActual == '11' ? 'selected' : '' }}>NOVIEMBRE</option>
                                    <option value="12" {{ $mesActual == '12' ? 'selected' : '' }}>DICIEMBRE</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filtroAno">AÑO:</label>
                                <select class="form-control" id="filtroAno">
                                    @for($i = $anoActual - 2; $i <= $anoActual; $i++)
                                        <option value="{{ $i }}" {{ $i == $anoActual ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filtroSucursal">SUCURSAL:</label>
                                <select class="form-control" id="filtroSucursal">
                                    <option value="">TODAS</option>
                                    <option value="matriz">MATRIZ</option>
                                    <option value="rocio">ROCÍO</option>
                                    <option value="norte">NORTE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="contenedorRoles">
                <div class="text-center text-muted">
                    <i class="fas fa-user-clock fa-3x mb-3"></i>
                    <p>SELECCIONE UN USUARIO PARA VER SU ROL DE PAGOS</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nueva tabla de sueldos registrados -->
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">DETALLE SUELDOS REGISTRADOS</h3>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSueldo">
                    <i class="fas fa-plus"></i> DETALLE AGREGAR SUELDO
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>EMPLEADO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>VALOR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sueldos = \App\Models\Sueldo::with('user')
                                ->orderBy('fecha', 'desc')
                                ->get();
                        @endphp
                        
                        @foreach($sueldos as $sueldo)
                            <tr>
                                <td>{{ $sueldo->fecha->format('d/m/Y') }}</td>
                                <td>{{ $sueldo->user->name }}</td>
                                <td>{{ $sueldo->descripcion }}</td>
                                <td>${{ number_format($sueldo->valor, 2) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editarSueldo({{ $sueldo->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarSueldo({{ $sueldo->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para agregar sueldo -->
    <div class="modal fade" id="modalAgregarSueldo" tabindex="-1" role="dialog" aria-labelledby="modalAgregarSueldoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarSueldoLabel">AGREGAR DETALLE NUEVO SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAgregarSueldo">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="usuario">EMPLEADO:</label>
                            <select class="form-control" id="usuario" name="user_id" required>
                                <option value="">SELECCIONE UN EMPLEADO</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha">FECHA:</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                        </div>
                        <div class="form-group">
                            <label for="valor">VALOR:</label>
                            <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">GUARDAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar sueldo -->
    <div class="modal fade" id="modalEditarSueldo" tabindex="-1" role="dialog" aria-labelledby="modalEditarSueldoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarSueldoLabel">EDITAR DETALLE SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarSueldo">
                    <input type="hidden" id="editar_sueldo_id" name="sueldo_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editar_usuario">EMPLEADO:</label>
                            <select class="form-control" id="editar_usuario" name="user_id" required>
                                <option value="">SELECCIONE UN EMPLEADO</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editar_fecha">FECHA:</label>
                            <input type="date" class="form-control" id="editar_fecha" name="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_descripcion">DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="editar_descripcion" name="descripcion" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_valor">VALOR:</label>
                            <input type="number" step="0.01" class="form-control" id="editar_valor" name="valor" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">GUARDAR CAMBIOS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Nueva tarjeta para Registros de Cobro -->
    <div class="card mt-4" hidden>
        <div class="card-header">
            <h3 class="card-title">REGISTROS DE COBRO</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtroUsuarioCobro">FILTRAR POR USUARIO:</label>
                        <select class="form-control" id="filtroUsuarioCobro">
                            <option value="">TODOS LOS USUARIOS</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaInicioCobro">FECHA INICIO:</label>
                        <input type="date" class="form-control" id="fechaInicioCobro">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaFinCobro">FECHA FIN:</label>
                        <input type="date" class="form-control" id="fechaFinCobro">
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>EMPLEADO</th>
                            <th>VALOR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaRegistrosCobro">
                        <tr>
                            <td colspan="4" class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> CARGANDO REGISTROS...
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><strong>TOTAL:</strong></td>
                            <td colspan="2"><strong id="totalRegistrosCobro">$0.00</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    @include('atajos')
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
    <script>
        $(document).ready(function() {
            let selectedUserId = null;
            let selectedUserName = null;

            // Agregar el evento para manejar cambios en los valores editables
            $(document).on('change', '.valor-editable', async function() {
                const $input = $(this);
                const valor = parseFloat($input.val());
                const fecha = $input.data('fecha');
                const userId = $('#selectUsuario').val();

                // Validaciones más estrictas
                if (!userId) {
                    Swal.fire({
                        icon: 'warning',
                        title: '¡ATENCIÓN!',
                        text: 'POR FAVOR SELECCIONE UN USUARIO',
                        showConfirmButton: true,
                        confirmButtonText: 'ENTENDIDO',
                        confirmButtonColor: '#ffc107'
                    });
                    $input.val('0.00').focus();
                    return;
                }

                try {
                    const response = await fetch('/sueldos/guardar-valor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({
                            fecha: fecha,
                            valor: valor,
                            user_id: userId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Mostrar notificación de éxito
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'VALOR GUARDADO CORRECTAMENTE'
                        });

                        // Actualizar la tabla de registros de cobro
                        cargarRegistrosCobro();
                    } else {
                        throw new Error(data.mensaje || 'ERROR AL GUARDAR EL VALOR');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡ERROR!',
                        text: error.message || 'ERROR AL GUARDAR EL VALOR',
                        showConfirmButton: true,
                        confirmButtonText: 'ENTENDIDO',
                        confirmButtonColor: '#dc3545'
                    });
                    $input.val('0.00').focus();
                }
            });

            // Función para cargar los datos del rol
            function cargarRolDePagos() {
                if (!selectedUserId) return;

                const mes = $('#filtroMes').val();
                const ano = $('#filtroAno').val();
                
                if (mes && ano) {
                    // Mostrar indicador de carga
                    const tbody = $(`#desglose_${selectedUserId}`);
                    tbody.html(`
                        <tr>
                            <td colspan="5" class="text-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                CARGANDO DATOS...
                            </td>
                        </tr>
                    `);
                    
                    obtenerRolPagos(selectedUserId, selectedUserName, ano, mes);
                }
            }

            // Función para actualizar la interfaz cuando se selecciona un usuario
            function actualizarInterfazUsuario() {
                const contenedor = $('#contenedorRoles');
                contenedor.empty();

                if (selectedUserId) {
                    contenedor.append(`
                        <div class="rol-usuario" id="rol_${selectedUserId}">
                            <h4>${selectedUserName}</h4>
                            <div class="mb-3 d-flex justify-content-between">
                                <div>
                                    <strong>PERÍODO: </strong>
                                    <span id="periodo_${selectedUserId}">-</span>
                                </div>
                                <div>
                                    <strong>TOTAL VENTAS: </strong>
                                    <span id="total_${selectedUserId}">$0.00</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-movimientos">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>MOVIMIENTOS</th>
                                            <th>PEDIDOS</th>
                                            <th>RETIROS</th>
                                            <th>VALOR</th>
                                        </tr>
                                    </thead>
                                    <tbody id="desglose_${selectedUserId}">
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                CARGANDO DATOS...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `);
                    
                    cargarRolDePagos();
                } else {
                    contenedor.html(`
                        <div class="text-center text-muted">
                            <i class="fas fa-user-clock fa-3x mb-3"></i>
                            <p>SELECCIONE UN USUARIO PARA VER SU ROL DE PAGOS</p>
                        </div>
                    `);
                }
            }

            // Manejar el cambio de usuario
            $('#selectUsuario').change(function() {
                selectedUserId = $(this).val();
                selectedUserName = $(this).find('option:selected').data('nombre');
                actualizarInterfazUsuario();
            });

            // Manejar cambios en los filtros
            $('#filtroMes, #filtroAno, #filtroSucursal').change(function() {
                cargarRolDePagos();
            });

            // Manejar el envío del formulario de agregar sueldo
            $('#formAgregarSueldo').on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    user_id: $('#usuario').val(),
                    fecha: $('#fecha').val(),
                    descripcion: $('#descripcion').val(),
                    valor: $('#valor').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: '{{ route("sueldos.store") }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            title: '¡ÉXITO!',
                            text: 'SUELDO REGISTRADO CORRECTAMENTE',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'ERROR',
                            text: 'HUBO UN ERROR AL REGISTRAR EL SUELDO',
                            icon: 'error'
                        });
                    }
                });

                $('#modalAgregarSueldo').modal('hide');
            });

            // Limpiar formulario cuando se cierra el modal
            $('#modalAgregarSueldo').on('hidden.bs.modal', function() {
                $('#formAgregarSueldo')[0].reset();
            });

            // Función para cargar datos en el modal de edición
            function cargarDatosEdicion(id) {
                $.ajax({
                    url: `/sueldos/${id}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const sueldo = response.data;
                            $('#editar_sueldo_id').val(sueldo.id);
                            $('#editar_usuario').val(sueldo.user_id);
                            $('#editar_fecha').val(sueldo.fecha.split('T')[0]);
                            $('#editar_descripcion').val(sueldo.descripcion);
                            $('#editar_valor').val(sueldo.valor);
                            $('#modalEditarSueldo').modal('show');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'ERROR',
                            text: 'NO SE PUDO CARGAR LA INFORMACIÓN DEL SUELDO',
                            icon: 'error'
                        });
                    }
                });
            }

            // Función para editar sueldo
            function editarSueldo(id) {
                cargarDatosEdicion(id);
            }

            // Manejar el envío del formulario de edición
            $('#formEditarSueldo').on('submit', function(e) {
                e.preventDefault();
                
                const id = $('#editar_sueldo_id').val();
                const formData = {
                    user_id: $('#editar_usuario').val(),
                    fecha: $('#editar_fecha').val(),
                    descripcion: $('#editar_descripcion').val(),
                    valor: $('#editar_valor').val(),
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'PUT'
                };

                $.ajax({
                    url: `/sueldos/${id}`,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            title: '¡ÉXITO!',
                            text: 'SUELDO ACTUALIZADO CORRECTAMENTE',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'ERROR',
                            text: 'HUBO UN ERROR AL ACTUALIZAR EL SUELDO',
                            icon: 'error'
                        });
                    }
                });

                $('#modalEditarSueldo').modal('hide');
            });

            // Función para eliminar sueldo
            function eliminarSueldo(id) {
                Swal.fire({
                    title: '¿ESTÁS SEGURO?',
                    text: 'ESTA ACCIÓN NO SE PUEDE DESHACER',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'SÍ, ELIMINAR',
                    cancelButtonText: 'CANCELAR'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/sueldos/${id}`,
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                _method: 'DELETE'
                            },
                            success: function() {
                                Swal.fire({
                                    title: '¡ELIMINADO!',
                                    text: 'EL SUELDO HA SIDO ELIMINADO CORRECTAMENTE',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'ERROR',
                                    text: 'NO SE PUDO ELIMINAR EL SUELDO',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            }

            // Hacer las funciones disponibles globalmente
            window.editarSueldo = editarSueldo;
            window.eliminarSueldo = eliminarSueldo;

            // Función para cargar los registros de cobro
            function cargarRegistrosCobro() {
                const userId = $('#filtroUsuarioCobro').val();
                const fechaInicio = $('#fechaInicioCobro').val();
                const fechaFin = $('#fechaFinCobro').val();
                
                let url = '/api/sueldos/registros-cobro?';
                
                if (userId) url += `user_id=${userId}&`;
                if (fechaInicio) url += `fecha_inicio=${fechaInicio}&`;
                if (fechaFin) url += `fecha_fin=${fechaFin}&`;
                
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const registros = response.data;
                            let html = '';
                            
                            if (registros.length === 0) {
                                html = `
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            NO SE ENCONTRARON REGISTROS
                                        </td>
                                    </tr>
                                `;
                            } else {
                                registros.forEach(registro => {
                                    const fecha = new Date(registro.fecha).toLocaleDateString();
                                    html += `
                                        <tr>
                                            <td>${fecha}</td>
                                            <td>${registro.user.name}</td>
                                            <td>$${parseFloat(registro.valor).toFixed(2)}</td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="eliminarSueldo(${registro.id})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            }
                            
                            $('#tablaRegistrosCobro').html(html);
                            $('#totalRegistrosCobro').text(`$${parseFloat(response.total).toFixed(2)}`);
                        } else {
                            Swal.fire({
                                title: 'ERROR',
                                text: 'ERROR AL CARGAR LOS REGISTROS',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'ERROR',
                            text: 'ERROR AL CARGAR LOS REGISTROS',
                            icon: 'error'
                        });
                    }
                });
            }

            // Cargar registros al iniciar
            cargarRegistrosCobro();

            // Manejar cambios en los filtros
            $('#filtroUsuarioCobro, #fechaInicioCobro, #fechaFinCobro').change(function() {
                cargarRegistrosCobro();
            });
        });
    </script>
@stop 