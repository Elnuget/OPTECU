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
                    @include('components.sueldos.filters', ['tipoSucursal' => $tipoSucursal])
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="contenedorRoles">
                <!-- Aquí se cargarán dinámicamente los roles -->
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
            // Manejar el cambio de usuario
            $('#selectUsuario').change(function() {
                const userId = $(this).val();
                const nombre = $(this).find('option:selected').data('nombre');
                const contenedor = $('#contenedorRoles');
                
                // Limpiar el contenedor
                contenedor.empty();
                
                if (userId) {
                    // Agregar el componente de rol para el usuario seleccionado
                    contenedor.append(`
                        <div class="rol-usuario" id="rol_${userId}">
                            <h4>${nombre}</h4>
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
                                    <tbody id="desglose_${userId}">
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <span class="text-muted">SELECCIONE UN PERÍODO PARA VER EL ROL</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">
                                                <strong>PERÍODO: </strong>
                                                <span id="periodo_${userId}">-</span>
                                            </td>
                                            <td colspan="2" class="text-right">
                                                <strong>TOTAL: </strong>
                                                <span id="total_${userId}">$0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `);
                    
                    // Si ya hay un período seleccionado, cargar los datos
                    const mes = $('#filtroMes').val();
                    const ano = $('#filtroAno').val();
                    if (mes && ano) {
                        obtenerRolPagos(userId, nombre, ano, mes);
                    }
                }
            });
        });
    </script>
@stop 