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

        .rol-usuario {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
    </style>

    <div class="card">
        <div class="card-body">
            {{-- Filtros de fecha y sucursal --}}
            <div class="form-row mb-4">
                <div class="col-md-3">
                    <label for="filtroAno">SELECCIONAR AÑO:</label>
                    <select name="ano" class="form-control custom-select" id="filtroAno">
                        <option value="">SELECCIONE AÑO</option>
                        @php
                            $currentYear = date('Y');
                            $selectedYear = request('ano', $currentYear);
                        @endphp
                        @for ($year = date('Y'); $year >= 2000; $year--)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroMes">SELECCIONAR MES:</label>
                    <select name="mes" class="form-control custom-select" id="filtroMes">
                        <option value="">SELECCIONE MES</option>
                        @php
                            $currentMonth = date('n');
                            $selectedMonth = request('mes', $currentMonth);
                        @endphp
                        @foreach (['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ $selectedMonth == ($index + 1) ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroSucursal">SELECCIONAR SUCURSAL:</label>
                    <select name="sucursal" class="form-control custom-select" id="filtroSucursal" {{ $tipoSucursal !== 'todas' ? 'disabled' : '' }}>
                        <option value="">TODAS LAS SUCURSALES</option>
                        @if($tipoSucursal === 'todas' || $tipoSucursal === 'matriz')
                            <option value="matriz" {{ request('sucursal') == 'matriz' ? 'selected' : '' }}>MATRIZ</option>
                        @endif
                        @if($tipoSucursal === 'todas' || $tipoSucursal === 'rocio')
                            <option value="rocio" {{ request('sucursal') == 'rocio' ? 'selected' : '' }}>ROCÍO</option>
                        @endif
                        @if($tipoSucursal === 'todas' || $tipoSucursal === 'norte')
                            <option value="norte" {{ request('sucursal') == 'norte' ? 'selected' : '' }}>NORTE</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="button" class="btn btn-primary btn-block" id="actualButton">
                        <i class="fas fa-sync-alt"></i> ACTUAL
                    </button>
                </div>
            </div>

            {{-- Roles de pago para cada usuario --}}
            @foreach($users as $user)
            <div class="rol-usuario" id="rol-usuario-{{ $user->id }}">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>EMPLEADO: <span class="text-primary">{{ $user->name }}</span></h5>
                        <h6>PERÍODO: <span class="text-secondary" id="periodo_{{ $user->id }}"></span></h6>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5>TOTAL DE PEDIDOS: <span class="text-success" id="total_{{ $user->id }}"></span></h5>
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
                        <tbody id="desglose_{{ $user->id }}">
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-primary btn-imprimir" data-user="{{ $user->id }}">
                        <i class="fas fa-print"></i> IMPRIMIR
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@stop

@section('js')
    @include('atajos')
    @push('js')
    <script>
        window.tipoSucursal = '{{ $tipoSucursal }}';
        
        // Función para cargar los datos de cada usuario
        function cargarDatosUsuario(userId, nombre) {
            const ano = document.getElementById('filtroAno').value;
            const mes = document.getElementById('filtroMes').value;
            const sucursal = document.getElementById('filtroSucursal').value;
            
            // Aquí deberás adaptar tu lógica existente para cargar los datos
            // usando el ID del usuario y los filtros seleccionados
            obtenerRolPagos(userId, nombre, ano, mes, sucursal);
        }

        // Cuando el documento esté listo
        $(document).ready(function() {
            // Cargar datos para cada usuario
            @foreach($users as $user)
                cargarDatosUsuario({{ $user->id }}, '{{ $user->name }}');
            @endforeach

            // Manejar cambios en los filtros
            $('#filtroAno, #filtroMes, #filtroSucursal').change(function() {
                @foreach($users as $user)
                    cargarDatosUsuario({{ $user->id }}, '{{ $user->name }}');
                @endforeach
            });

            // Manejar el botón actual
            $('#actualButton').click(function() {
                const now = new Date();
                $('#filtroAno').val(now.getFullYear());
                $('#filtroMes').val(now.getMonth() + 1);
                if (window.tipoSucursal === 'todas') {
                    $('#filtroSucursal').val('');
                }
                
                @foreach($users as $user)
                    cargarDatosUsuario({{ $user->id }}, '{{ $user->name }}');
                @endforeach
            });

            // Manejar clicks en botones de imprimir
            $('.btn-imprimir').click(function() {
                const userId = $(this).data('user');
                imprimirRolPagos(userId);
            });
        });
    </script>
    @endpush
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
@stop 