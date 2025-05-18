@extends('adminlte::page')

@section('title', 'SUELDOS')

@section('content_header')
    <h1>ROL DE PAGOS</h1>
    <p>ADMINISTRACIÓN DE ROLES DE PAGO</p>
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
    @php
        $empresa = \App\Models\Empresa::first();
        $tipoSucursal = $empresa ? $empresa->getTipoSucursal() : 'todas';
        $users = \App\Models\User::orderBy('name')->get();
    @endphp

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
        .btn,
        label,
        input,
        select,
        option,
        datalist,
        datalist option,
        .form-control,
        p,
        h1, h2, h3, h4, h5, h6,
        th,
        td,
        span,
        a,
        .dropdown-item,
        .alert,
        .modal-title,
        .modal-body p,
        .modal-content,
        .card-header,
        .card-footer,
        button,
        .close,
        .table thead th,
        .table tbody td,
        .dataTables_filter,
        .dataTables_info,
        .paginate_button,
        .info-box span {
            text-transform: uppercase !important;
        }
    </style>

    <div class="card">
        <div class="card-body">
            @include('components.sueldos.filtros', ['tipoSucursal' => $tipoSucursal, 'users' => $users])
            
            {{-- Contenedor para el Rol de Pagos --}}
            <div id="contenedorRolPagos" class="d-none">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>EMPLEADO: <span id="rolEmpleadoNombre"></span></h6>
                    </div>
                    <div class="col-md-6">
                        <h6>PERÍODO: <span id="rolPeriodo"></span></h6>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center bg-info">INGRESOS</th>
                                <th colspan="2" class="text-center bg-danger">EGRESOS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SUELDO BASE</td>
                                <td id="rolSueldoBase" class="text-right"></td>
                                <td>RETIROS</td>
                                <td id="rolRetiros" class="text-right"></td>
                            </tr>
                            <tr>
                                <td>COMISIÓN PEDIDOS</td>
                                <td id="rolComisionPedidos" class="text-right"></td>
                                <td>OTROS DESCUENTOS</td>
                                <td id="rolOtrosDescuentos" class="text-right"></td>
                            </tr>
                            <tr>
                                <td>OTROS INGRESOS</td>
                                <td id="rolOtrosIngresos" class="text-right"></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr class="bg-light">
                                <th>TOTAL INGRESOS</th>
                                <th id="rolTotalIngresos" class="text-right"></th>
                                <th>TOTAL EGRESOS</th>
                                <th id="rolTotalEgresos" class="text-right"></th>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-success">
                                <th colspan="3" class="text-right">TOTAL A RECIBIR</th>
                                <th id="rolTotalRecibir" class="text-right"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    <h6>DESGLOSE DE MOVIMIENTOS</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>FECHA</th>
                                    <th>TIPO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>MONTO</th>
                                </tr>
                            </thead>
                            <tbody id="rolDesglose">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-primary" id="btnImprimirRol">
                        <i class="fas fa-print"></i> IMPRIMIR
                    </button>
                </div>
            </div>

            {{-- Mensaje cuando no hay usuario seleccionado --}}
            <div id="mensajeSeleccionUsuario" class="text-center py-5">
                <h4>SELECCIONE UN USUARIO PARA GENERAR EL ROL DE PAGOS</h4>
            </div>
        </div>
    </div>

    @include('components.sueldos.retiros', ['tipoSucursal' => $tipoSucursal])
    @include('components.sueldos.pedidos', ['tipoSucursal' => $tipoSucursal])
    @include('components.sueldos.historial', ['tipoSucursal' => $tipoSucursal])

@stop

@section('js')
    @include('atajos')
    @push('js')
    <script>
        // Definir la variable tipoSucursal globalmente para que esté disponible en todos los scripts
        window.tipoSucursal = '{{ $tipoSucursal }}';
    </script>
    @endpush
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
@stop 