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
                <h3 class="card-title">SUELDOS REGISTRADOS</h3>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSueldo">
                    <i class="fas fa-plus"></i> AGREGAR SUELDO
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
                    <h5 class="modal-title" id="modalAgregarSueldoLabel">AGREGAR NUEVO SUELDO</h5>
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

            // Función para manejar el cambio en los valores editables
            $(document).on('change', '.valor-editable', function() {
                const valor = $(this).val();
                const rowId = $(this).data('row-id');
                // Aquí puedes agregar la lógica para guardar el valor
                console.log('Valor cambiado:', valor, 'para la fila:', rowId);
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
        });

        function editarSueldo(id) {
            Swal.fire({
                title: 'EDITAR SUELDO',
                text: '¿Estás seguro de que deseas editar este registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, editar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí puedes agregar la lógica para editar el sueldo
                    console.log('Editando sueldo:', id);
                }
            });
        }

        function eliminarSueldo(id) {
            Swal.fire({
                title: '¿ESTÁS SEGURO?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí puedes agregar la lógica para eliminar el sueldo
                    console.log('Eliminando sueldo:', id);
                }
            });
        }
    </script>
@stop 