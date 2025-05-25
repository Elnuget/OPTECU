@extends('adminlte::page')

@section('title', 'ROL DE PAGOS')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1>ROL DE PAGOS</h1>
    <p>ADMINISTRACIÓN DE ROLES DE PAGO</p>
    @include('components.sueldos.alerts')

    @php
        $empresa = \App\Models\Empresa::first();
        if ($empresa && $empresa->nombre !== 'Matriz') {
            echo '
            <div class="modal fade" id="modalRestriccion" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalRestriccionLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="modalRestriccionLabel">¡ATENCIÓN!</h5>
                        </div>
                        <div class="modal-body text-center">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5>DEBE GESTIONAR SU ROL DE PAGOS EN LA SUCURSAL MATRIZ</h5>
                            <p class="mt-3">
                                <a href="https://opticas.xyz/" class="btn btn-primary">
                                    <i class="fas fa-building mr-2"></i>IR A MATRIZ
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    $("#modalRestriccion").modal("show");
                });
            </script>
            ';
        }
    @endphp
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@stop

@section('content')
    @php
        $empresa = \App\Models\Empresa::first();
        $tipoSucursal = $empresa ? $empresa->getTipoSucursal() : 'todas';
        $users = \App\Models\User::orderBy('name')->get();
        $currentUser = auth()->user();
        
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
                        <select class="form-control" id="selectUsuario" {{ !$currentUser->is_admin ? 'disabled' : '' }}>
                            @if($currentUser->is_admin)
                                <option value="">SELECCIONE UN USUARIO</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" data-nombre="{{ $user->name }}">{{ $user->name }}</option>
                                @endforeach
                            @else
                                <option value="{{ $currentUser->id }}" data-nombre="{{ $currentUser->name }}" selected>{{ $currentUser->name }}</option>
                            @endif
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
    @if($currentUser->is_admin)
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title"> SUELDOS REGISTRADOS</h3>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSueldo">
                    <i class="fas fa-plus"></i>  AGREGAR SUELDO
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
                            <tr {{ $sueldo->descripcion === 'REGISTROCOBRO' ? 'hidden' : '' }}>
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
    @endif

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

    <!-- Modal para editar sueldo -->
    <div class="modal fade" id="modalEditarSueldo" tabindex="-1" role="dialog" aria-labelledby="modalEditarSueldoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarSueldoLabel">EDITAR SUELDO</h5>
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

    <!-- Modal para pagar sueldo -->
    <div class="modal fade" id="modalPagarSueldo" tabindex="-1" role="dialog" aria-labelledby="modalPagarSueldoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPagarSueldoLabel">PAGAR SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formPagarSueldo">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="pago_empleado">EMPLEADO:</label>
                            <input type="text" class="form-control" id="pago_empleado" readonly>
                        </div>
                        <div class="form-group">
                            <label for="pago_descripcion">DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="pago_descripcion" readonly>
                        </div>
                        <div class="form-group">
                            <label for="pago_valor">VALOR A PAGAR:</label>
                            <input type="number" step="0.01" class="form-control" id="pago_valor" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">PAGAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    @include('atajos')
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
    <script>
        // Variables globales
        let selectedUserId = null;
        let selectedUserName = null;

        // Función global para pagar sueldo
        window.abrirModalPagarSueldo = function() {
            if (!selectedUserId) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡ATENCIÓN!',
                    text: 'POR FAVOR SELECCIONE UN USUARIO PARA PAGAR SUELDO',
                    confirmButtonText: 'ENTENDIDO'
                });
                return;
            }

            const mes = $('#filtroMes').val();
            const ano = $('#filtroAno').val();
            const descripcion = `SUELDO ${mes}/${ano}`;
            const sueldoRecibir = $(`#sueldo_recibir_${selectedUserId}`).text().replace('$', '');

            $('#pago_empleado').val(selectedUserName);
            $('#pago_descripcion').val(descripcion);
            $('#pago_valor').val(parseFloat(sueldoRecibir).toFixed(2));

            $('#modalPagarSueldo').modal('show');
        };

        // Función global para agregar fila de detalle
        window.agregarFilaDetalle = function(userId) {
            const fecha = new Date();
            const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            const ano = fecha.getFullYear();
            const empleado = $('#selectUsuario option:selected').text();
            const nuevaFila = `
                <tr>
                    <td>
                        <input type="text" class="form-control form-control-sm" value="${mes}/${ano}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" value="${empleado}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm descripcion-detalle" placeholder="DESCRIPCIÓN">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm valor-detalle" value="0.00" step="0.01" min="-999999.99" max="999999.99">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-success btn-sm guardar-detalle">
                            <i class="fas fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm eliminar-detalle" style="display:none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $(`#detalles_${userId}`).append(nuevaFila);
        };

        // Función global para exportar a Excel
        window.exportarExcel = function() {
            if (!selectedUserId) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡ATENCIÓN!',
                    text: 'POR FAVOR SELECCIONE UN USUARIO PARA EXPORTAR',
                    confirmButtonText: 'ENTENDIDO'
                });
                return;
            }

            const mes = $('#filtroMes').val();
            const ano = $('#filtroAno').val();
            
            // Mostrar indicador de carga
            Swal.fire({
                title: 'GENERANDO EXCEL',
                text: 'POR FAVOR ESPERE...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Obtener los datos necesarios
            Promise.all([
                // Obtener detalles
                $.ajax({
                    url: '/detalles-sueldos/periodo',
                    method: 'GET',
                    data: { user_id: selectedUserId, mes: mes, ano: ano }
                }),
                // Obtener total de registros de cobro
                $.ajax({
                    url: '/api/sueldos/total-registros-cobro',
                    method: 'GET',
                    data: { user_id: selectedUserId, mes: mes, ano: ano }
                })
            ]).then(([detallesResponse, registrosResponse]) => {
                // ... resto del código de exportación a Excel ...
            }).catch(error => {
                console.error('Error al exportar:', error);
                Swal.fire({
                    icon: 'error',
                    title: '¡ERROR!',
                    text: 'HUBO UN ERROR AL GENERAR EL ARCHIVO EXCEL',
                    confirmButtonText: 'ENTENDIDO'
                });
            });
        };

        // Función para imprimir rol de pagos
        window.imprimirRolPagos = function() {
            if (!selectedUserId) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡ATENCIÓN!',
                    text: 'POR FAVOR SELECCIONE UN USUARIO PARA IMPRIMIR',
                    confirmButtonText: 'ENTENDIDO'
                });
                return;
            }

            // ... resto del código de impresión ...
        };

        $(document).ready(function() {
            // Inicializar usuario si no es administrador
            @if(!$currentUser->is_admin)
                selectedUserId = '{{ $currentUser->id }}';
                selectedUserName = '{{ $currentUser->name }}';
                actualizarInterfazUsuario();
                cargarRolDePagos();
                cargarDetalles(selectedUserId);
            @endif

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

                        // Actualizar la tabla de registros de cobro y el total
                        cargarRegistrosCobro();
                        
                        // Actualizar el total de registros de cobro
                        $.ajax({
                            url: '/api/sueldos/total-registros-cobro',
                            method: 'GET',
                            data: {
                                user_id: userId,
                                mes: $('#filtroMes').val(),
                                ano: $('#filtroAno').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#total_registros_' + userId).text('$' + parseFloat(response.total).toFixed(2));
                                    
                                    // Actualizar el sueldo a recibir
                                    const totalRegistros = parseFloat(response.total) || 0;
                                    const totalDetalles = parseFloat($(`#total_detalles_${userId}`).text().replace('$', '')) || 0;
                                    const sueldoRecibir = totalRegistros + totalDetalles;
                                    $(`#sueldo_recibir_${userId}`).text(`$${sueldoRecibir.toFixed(2)}`);
                                }
                            }
                        });
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
                    
                    obtenerRolPagos(selectedUserId, selectedUserName, ano, mes)
                        .then(data => {
                            // Actualizar los detalles
                            actualizarDetallesRolPagos(data);
                        });
                }
            }

            // Función para actualizar los detalles del rol de pagos
            function actualizarDetallesRolPagos(data) {
                const sueldoBase = data.sueldo_base || 0;
                const comisiones = data.comisiones || 0;
                const bonificaciones = data.bonificaciones || 0;
                const descuentos = data.descuentos || 0;
                const totalPagar = sueldoBase + comisiones + bonificaciones - descuentos;

                // Actualizar los valores en la tabla de detalles
                $(`#sueldo_base_${selectedUserId}`).text(`$${sueldoBase.toFixed(2)}`);
                $(`#comisiones_${selectedUserId}`).text(`$${comisiones.toFixed(2)}`);
                $(`#bonificaciones_${selectedUserId}`).text(`$${bonificaciones.toFixed(2)}`);
                $(`#descuentos_${selectedUserId}`).text(`$${descuentos.toFixed(2)}`);
                $(`#total_pagar_${selectedUserId}`).text(`$${totalPagar.toFixed(2)}`);
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
                                <div class="d-flex">
                                    <div class="mr-4">
                                        <strong>TOTAL VENTAS: </strong>
                                        <span id="total_${selectedUserId}">$0.00</span>
                                    </div>
                                    <div class="mr-4">
                                        <strong>TOTAL REGISTROS COBRO: </strong>
                                        <span id="total_registros_${selectedUserId}">$0.00</span>
                                    </div>
                                    <div class="mr-4">
                                        <strong>TOTAL DETALLES: </strong>
                                        <span id="total_detalles_${selectedUserId}">$0.00</span>
                                    </div>
                                    <div class="mr-4">
                                        <strong>SUELDO A RECIBIR: </strong>
                                        <span id="sueldo_recibir_${selectedUserId}">$0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-success mr-2" onclick="exportarExcel()">
                                    <i class="fas fa-file-excel"></i> EXPORTAR A EXCEL
                                </button>
                                <button class="btn btn-info mr-2" onclick="imprimirRolPagos()">
                                    <i class="fas fa-print"></i> IMPRIMIR ROL
                                </button>
                                @if($currentUser->is_admin)
                                <button class="btn btn-primary" onclick="abrirModalPagarSueldo()">
                                    <i class="fas fa-money-bill-wave"></i> PAGAR SUELDO
                                </button>
                                @endif
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
                            <div class="table-responsive mt-4">
                                <h5>DETALLES</h5>
                                <table class="table table-bordered table-detalles">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>EMPLEADO</th>
                                            <th>DESCRIPCIÓN</th>
                                            <th>VALOR</th>
                                            <th>ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalles_${selectedUserId}">
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                CARGANDO DETALLES...
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <button type="button" class="btn btn-success btn-sm" onclick="agregarFilaDetalle(${selectedUserId})">
                                                    <i class="fas fa-plus"></i> AGREGAR DETALLE
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `);
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
                if (selectedUserId) {
                    cargarRolDePagos();
                    cargarDetalles(selectedUserId); // Cargar los detalles al cambiar de usuario
                }
            });

            // Manejar cambios en los filtros
            $('#filtroMes, #filtroAno, #filtroSucursal').change(function() {
                cargarRolDePagos();
                if (selectedUserId) {
                    cargarDetalles(selectedUserId); // Cargar los detalles al cambiar los filtros
                }
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

            // Función para cargar los detalles existentes
            function cargarDetalles(userId) {
                console.log('Cargando detalles para usuario:', userId);
                if (!userId) {
                    console.log('No hay usuario seleccionado');
                    return;
                }

                const mes = $('#filtroMes').val();
                const ano = $('#filtroAno').val();
                console.log('Período:', mes, ano);

                // Mostrar indicador de carga
                const $tbody = $(`#detalles_${userId}`);
                $tbody.html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            CARGANDO DETALLES...
                        </td>
                    </tr>
                `);

                // Primero obtener el total de registros de cobro
                $.ajax({
                    url: '/api/sueldos/total-registros-cobro',
                    method: 'GET',
                    data: {
                        user_id: userId,
                        mes: mes,
                        ano: ano
                    },
                    success: function(responseRegistros) {
                        if (responseRegistros.success) {
                            const totalRegistros = parseFloat(responseRegistros.total) || 0;
                            $('#total_registros_' + userId).text('$' + totalRegistros.toFixed(2));

                            // Luego cargar los detalles
                            $.ajax({
                                url: '/detalles-sueldos/periodo',
                                method: 'GET',
                                data: {
                                    user_id: userId,
                                    mes: mes,
                                    ano: ano
                                },
                                success: function(response) {
                                    console.log('Respuesta del servidor:', response);
                                    if (response.success) {
                                        $tbody.empty();
                                        let totalDetalles = 0;

                                        if (response.data.length === 0) {
                                            $tbody.html(`
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        NO HAY DETALLES PARA ESTE PERÍODO
                                                    </td>
                                                </tr>
                                            `);
                                        } else {
                                            response.data.forEach(detalle => {
                                                console.log('Procesando detalle:', detalle);
                                                const nombreUsuario = selectedUserName;
                                                const fila = `
                                                    <tr data-detalle-id="${detalle.id}">
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" value="${detalle.mes}/${detalle.ano}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" value="${nombreUsuario}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm descripcion-detalle" value="${detalle.descripcion}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm valor-detalle" value="${detalle.valor}" readonly>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-sm eliminar-detalle" onclick="eliminarDetalle(${detalle.id})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                `;
                                                $tbody.append(fila);
                                                totalDetalles += parseFloat(detalle.valor);
                                            });
                                        }

                                        // Actualizar el total de detalles
                                        $(`#total_detalles_${userId}`).text(`$${totalDetalles.toFixed(2)}`);
                                        
                                        // Calcular y actualizar el sueldo a recibir usando el total de registros obtenido de la API
                                        const sueldoRecibir = totalRegistros + totalDetalles;
                                        $(`#sueldo_recibir_${userId}`).text(`$${sueldoRecibir.toFixed(2)}`);
                                    } else {
                                        $tbody.html(`
                                            <tr>
                                                <td colspan="5" class="text-center text-danger">
                                                    ERROR AL CARGAR LOS DETALLES
                                                </td>
                                            </tr>
                                        `);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error al cargar detalles:', error);
                                    $tbody.html(`
                                        <tr>
                                            <td colspan="5" class="text-center text-danger">
                                                ERROR AL CARGAR LOS DETALLES: ${error}
                                            </td>
                                        </tr>
                                    `);
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener total de registros:', error);
                    }
                });
            }

            // Función para eliminar un detalle
            window.eliminarDetalle = function(detalleId) {
                Swal.fire({
                    title: '¿ESTÁ SEGURO?',
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
                            url: `/detalles-sueldos/${detalleId}`,
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true
                                    });

                                    Toast.fire({
                                        icon: 'success',
                                        title: 'DETALLE ELIMINADO CORRECTAMENTE'
                                    });

                                    // Recargar los detalles
                                    cargarDetalles(selectedUserId);
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: '¡ERROR!',
                                    text: 'ERROR AL ELIMINAR EL DETALLE',
                                    showConfirmButton: true,
                                    confirmButtonText: 'ENTENDIDO',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
                    }
                });
            };

            // Actualizar la función de cargar rol de pagos para incluir los detalles
            function cargarRolDePagos() {
                if (!selectedUserId) return;

                const mes = $('#filtroMes').val();
                const ano = $('#filtroAno').val();
                
                if (mes && ano) {
                    obtenerRolPagos(selectedUserId, selectedUserName, ano, mes)
                        .then(data => {
                            actualizarDetallesRolPagos(data);
                            cargarDetalles(selectedUserId);
                        });
                }
            }

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

            // Manejar el clic en el botón guardar-detalle
            $(document).on('click', '.guardar-detalle', function() {
                const $button = $(this);
                const $row = $button.closest('tr');
                const $descripcion = $row.find('.descripcion-detalle');
                const $valor = $row.find('.valor-detalle');
                const descripcion = $descripcion.val();
                const valor = parseFloat($valor.val());
                const userId = selectedUserId;
                const mes = $('#filtroMes').val();
                const ano = $('#filtroAno').val();

                if (!descripcion || isNaN(valor)) {
                    Swal.fire({
                        icon: 'warning',
                        title: '¡ATENCIÓN!',
                        text: 'POR FAVOR COMPLETE TODOS LOS CAMPOS CORRECTAMENTE',
                        showConfirmButton: true,
                        confirmButtonText: 'ENTENDIDO',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                $.ajax({
                    url: '/detalles-sueldos',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        mes: mes,
                        ano: ano,
                        descripcion: descripcion,
                        valor: valor,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
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
                                title: 'DETALLE GUARDADO CORRECTAMENTE'
                            });

                            // Recargar los detalles
                            cargarDetalles(userId);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: 'ERROR AL GUARDAR EL DETALLE',
                            showConfirmButton: true,
                            confirmButtonText: 'ENTENDIDO',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            });

            // Manejar el envío del formulario de pago
            $('#formPagarSueldo').on('submit', function(e) {
                e.preventDefault();
                
                const fechaActual = new Date().toISOString().split('T')[0];
                const formData = {
                    user_id: selectedUserId,
                    fecha: fechaActual,
                    descripcion: $('#pago_descripcion').val(),
                    valor: parseFloat($('#pago_valor').val()),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                Swal.fire({
                    title: '¿ESTÁ SEGURO?',
                    text: `VA A PAGAR $${formData.valor.toFixed(2)} A ${selectedUserName}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'SÍ, PAGAR',
                    cancelButtonText: 'CANCELAR'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/sueldos',
                            method: 'POST',
                            data: formData,
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡ÉXITO!',
                                        text: 'PAGO REALIZADO CORRECTAMENTE',
                                        showConfirmButton: true,
                                        confirmButtonText: 'ACEPTAR'
                                    }).then(() => {
                                        $('#modalPagarSueldo').modal('hide');
                                        // Recargar solo si fue exitoso
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '¡ERROR!',
                                        text: response.mensaje || 'ERROR AL REALIZAR EL PAGO',
                                        showConfirmButton: true,
                                        confirmButtonText: 'ENTENDIDO'
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.error('Error:', xhr);
                                Swal.fire({
                                    icon: 'error',
                                    title: '¡ERROR!',
                                    text: 'ERROR AL REALIZAR EL PAGO: ' + (xhr.responseJSON?.mensaje || 'ERROR DESCONOCIDO'),
                                    showConfirmButton: true,
                                    confirmButtonText: 'ENTENDIDO'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop 