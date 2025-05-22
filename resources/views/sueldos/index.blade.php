@extends('adminlte::page')

@section('title', 'ROL DE PAGOS')

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
        /* Estilos base */
        body, .content-wrapper, .main-header, .main-sidebar, .card-title,
        .info-box-text, .info-box-number, .custom-select, .btn, label,
        input, select, option, datalist, datalist option, .form-control,
        p, h1, h2, h3, h4, h5, h6, th, td, span, a, .dropdown-item,
        .alert, .modal-title, .modal-body p, .modal-content, .card-header,
        .card-footer, button, .close, .table thead th, .table tbody td,
        .dataTables_filter, .dataTables_info, .paginate_button,
        .info-box span {
            text-transform: uppercase !important;
        }

        .table-movimientos th {
            background-color: #f4f6f9;
            vertical-align: middle !important;
        }

        .table-movimientos td {
            vertical-align: middle !important;
        }

        .badge-apertura {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
        }

        .badge-cierre {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
        }

        .hora-movimiento {
            font-size: 0.9em;
            color: #6c757d;
            margin-left: 10px;
        }

        .sucursal-badge {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .sucursal-matriz { background-color: #007bff; }
        .sucursal-rocio { background-color: #28a745; }
        .sucursal-norte { background-color: #17a2b8; }
    </style>

    <div class="card">
        <div class="card-body">
            @include('components.sueldos.filtros', ['tipoSucursal' => $tipoSucursal, 'users' => $users])
            
            {{-- Contenedor para el Rol de Pagos --}}
            <div id="contenedorRolPagos" class="d-none">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>EMPLEADO: <span id="rolEmpleadoNombre" class="text-primary"></span></h5>
                        <h6>PERÍODO: <span id="rolPeriodo" class="text-secondary"></span></h6>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5>TOTAL DE PEDIDOS: <span id="rolTotalRecibir" class="text-success"></span></h5>
                    </div>
                </div>

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
                        <tbody id="rolDesglose">
                        </tbody>
                    </table>
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

@stop

@section('js')
    @include('atajos')
    @push('js')
    <script>
        window.tipoSucursal = '{{ $tipoSucursal }}';
    </script>
    @endpush
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
@stop 