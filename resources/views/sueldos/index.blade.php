@extends('adminlte::page')

@section('title', 'ROL DE PAGOS')

@section('content_header')
    <h1>ROL DE PAGOS</h1>
    <p>ADMINISTRACIÓN DE ROLES DE PAGO</p>
    @include('components.sueldos.alerts')
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
@stop

@section('js')
    @include('atajos')
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
    <script>
        $(document).ready(function() {
            let selectedUserId = null;
            let selectedUserName = null;

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
                            <div class="table-responsive">
                                <table class="table table-bordered table-movimientos">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>MOVIMIENTOS</th>
                                            <th>SUCURSAL</th>
                                            <th>PEDIDOS</th>
                                            <th>RETIROS</th>
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
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">
                                                <strong>PERÍODO: </strong>
                                                <span id="periodo_${selectedUserId}">-</span>
                                            </td>
                                            <td colspan="2" class="text-right">
                                                <strong>TOTAL: </strong>
                                                <span id="total_${selectedUserId}">$0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
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
        });
    </script>
@stop 