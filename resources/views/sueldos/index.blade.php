@extends('adminlte::page')

@section('title', 'SUELDOS')

@section('content_header')
    <h1>SUELDOS</h1>
    <p>ADMINISTRACIÓN DE SUELDOS</p>
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
            {{-- Resumen de totales --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL SUELDOS</span>
                            <span class="info-box-number">${{ number_format($totalSueldos, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario de filtro --}}
            <form method="GET" class="form-row mb-3" id="filterForm">
                <div class="col-md-2">
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
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label for="filtroSucursal">SELECCIONAR SUCURSAL:</label>
                    <select name="sucursal" class="form-control custom-select" id="filtroSucursal" {{ $tipoSucursal !== 'todas' ? 'disabled' : '' }}>
                        <option value="">TODAS LAS SUCURSALES</option>
                        @if($tipoSucursal === 'todas' || $tipoSucursal === 'matriz')
                            <option value="matriz">MATRIZ</option>
                        @endif
                        @if($tipoSucursal === 'todas' || $tipoSucursal === 'rocio')
                            <option value="rocio">ROCÍO</option>
                        @endif
                        @if($tipoSucursal === 'todas' || $tipoSucursal === 'norte')
                            <option value="norte">NORTE</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroUsuario">SELECCIONAR USUARIO:</label>
                    <select name="user_id" class="form-control custom-select" id="filtroUsuario">
                        <option value="">TODOS LOS USUARIOS</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="button" class="btn btn-primary" id="actualButton">ACTUAL</button>
                </div>
            </form>

            {{-- Botón Añadir Sueldo --}}
            <div class="btn-group mb-3">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearSueldoModal">
                    <i class="fas fa-plus mr-2"></i>AÑADIR SUELDO
                </button>
            </div>

            <div class="table-responsive">
                <table id="sueldosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>DESCRIPCIÓN</th>
                            <th>VALOR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sueldos as $sueldo)
                            <tr>
                                <td>{{ $sueldo->fecha->format('Y-m-d') }}</td>
                                <td>{{ $sueldo->descripcion }}</td>
                                <td>${{ number_format($sueldo->valor, 2, ',', '.') }}</td>
                                <td>
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-info mx-1 shadow" 
                                        title="Ver"
                                        data-toggle="modal" 
                                        data-target="#verSueldoModal" 
                                        data-id="{{ $sueldo->id }}"
                                        data-fecha="{{ $sueldo->fecha->format('Y-m-d') }}"
                                        data-descripcion="{{ $sueldo->descripcion }}"
                                        data-valor="{{ $sueldo->valor }}">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </button>
                                    
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" 
                                        title="Editar"
                                        data-toggle="modal" 
                                        data-target="#editarSueldoModal" 
                                        data-id="{{ $sueldo->id }}"
                                        data-fecha="{{ $sueldo->fecha->format('Y-m-d') }}"
                                        data-descripcion="{{ $sueldo->descripcion }}"
                                        data-valor="{{ $sueldo->valor }}">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        title="Eliminar"
                                        data-toggle="modal"
                                        data-target="#confirmarEliminarModal"
                                        data-id="{{ $sueldo->id }}"
                                        data-url="{{ route('sueldos.destroy', $sueldo->id) }}">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Retiros --}}
    <div class="card card-outline card-danger mb-4" id="card-retiros-total">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave mr-2"></i>
                RETIROS TOTALES DE TODAS LAS SUCURSALES: 
                <span id="total-retiros-global">CARGANDO...</span>
            </h3>
        </div>
        <div class="card-body">
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-retiros-matriz">
                    Matriz: $0
                </div>
                <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-retiros-rocio">
                    Rocío: $0
                </div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-retiros-norte">
                    Norte: $0
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjeta Plegable Retiros Matriz --}}
    <div class="card card-outline card-success card-widget collapsed-card" id="card-retiros-matriz">
        <div class="card-header">
            <h3 class="card-title">RETIROS SUCURSAL MATRIZ - TOTAL: <span id="total-retiros-matriz">CARGANDO...</span></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>USUARIO</th>
                        </tr>
                    </thead>
                    <tbody id="desglose-retiros-matriz">
                        <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="overlay dark" id="loading-overlay-retiros-matriz" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>

    {{-- Tarjeta Plegable Retiros Rocío --}}
    <div class="card card-outline card-info card-widget collapsed-card" id="card-retiros-rocio">
        <div class="card-header">
            <h3 class="card-title">RETIROS SUCURSAL ROCÍO - TOTAL: <span id="total-retiros-rocio">CARGANDO...</span></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>USUARIO</th>
                        </tr>
                    </thead>
                    <tbody id="desglose-retiros-rocio">
                        <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="overlay dark" id="loading-overlay-retiros-rocio" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>

    {{-- Tarjeta Plegable Retiros Norte --}}
    <div class="card card-outline card-warning card-widget collapsed-card" id="card-retiros-norte">
        <div class="card-header">
            <h3 class="card-title">RETIROS SUCURSAL NORTE - TOTAL: <span id="total-retiros-norte">CARGANDO...</span></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>USUARIO</th>
                        </tr>
                    </thead>
                    <tbody id="desglose-retiros-norte">
                        <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="overlay dark" id="loading-overlay-retiros-norte" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>

    <!-- Modal Crear Sueldo -->
    <div class="modal fade" id="crearSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">REGISTRAR NUEVO SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sueldos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
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
                            <input type="number" class="form-control" id="valor" name="valor" required step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="user_id">USUARIO:</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">SELECCIONE USUARIO</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-success">GUARDAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Sueldo -->
    <div class="modal fade" id="verSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">DETALLES DEL SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>FECHA:</label>
                        <p id="verFecha" class="form-control-static"></p>
                    </div>
                    <div class="form-group">
                        <label>DESCRIPCIÓN:</label>
                        <p id="verDescripcion" class="form-control-static"></p>
                    </div>
                    <div class="form-group">
                        <label>VALOR:</label>
                        <p id="verValor" class="form-control-static"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Sueldo -->
    <div class="modal fade" id="editarSueldoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">EDITAR SUELDO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarSueldo" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editFecha">FECHA:</label>
                            <input type="date" class="form-control" id="editFecha" name="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="editDescripcion">DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="editDescripcion" name="descripcion" required>
                        </div>
                        <div class="form-group">
                            <label for="editValor">VALOR:</label>
                            <input type="number" class="form-control" id="editValor" name="valor" required step="0.01" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminar -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE REGISTRO DE SUELDO?</p>
                </div>
                <div class="modal-footer">
                    <form id="eliminarForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-danger">ELIMINAR</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
    <script>
        // Variables globales para almacenar los totales y el tipo de sucursal
        let totalRetirosMatriz = 0;
        let totalRetirosRocio = 0;
        let totalRetirosNorte = 0;
        const tipoSucursal = '{{ $tipoSucursal }}';

        $(document).ready(function() {
            // Inicializar DataTable
            var sueldosTable = $('#sueldosTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,
                "info": false,
                "dom": 'Bfrt',
                "buttons": [
                    'excelHtml5',
                    'csvHtml5',
                    {
                        "extend": 'print',
                        "text": 'IMPRIMIR',
                        "autoPrint": true,
                        "exportOptions": {
                            "columns": [0, 1, 2]
                        },
                        "customize": function(win) {
                            $(win.document.body).css('font-size', '16pt');
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    },
                    {
                        "extend": 'pdfHtml5',
                        "text": 'PDF',
                        "filename": 'Sueldos.pdf',
                        "pageSize": 'LETTER',
                        "exportOptions": {
                            "columns": [0, 1, 2]
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            // Función para formatear números como moneda
            function formatCurrency(number) {
                return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
            }

            // Función para actualizar el total global de retiros y la barra de progreso
            function updateGlobalRetiros() {
                const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
                let totalGlobal = 0;

                if (tipoSucursal !== 'todas') {
                    if (tipoSucursal === 'matriz') totalGlobal = Math.abs(totalRetirosMatriz);
                    else if (tipoSucursal === 'rocio') totalGlobal = Math.abs(totalRetirosRocio);
                    else if (tipoSucursal === 'norte') totalGlobal = Math.abs(totalRetirosNorte);
                } else {
                    if (sucursal === '') {
                        totalGlobal = Math.abs(totalRetirosMatriz) + Math.abs(totalRetirosRocio) + Math.abs(totalRetirosNorte);
                    } else if (sucursal === 'matriz') {
                        totalGlobal = Math.abs(totalRetirosMatriz);
                    } else if (sucursal === 'rocio') {
                        totalGlobal = Math.abs(totalRetirosRocio);
                    } else if (sucursal === 'norte') {
                        totalGlobal = Math.abs(totalRetirosNorte);
                    }
                }

                const totalSpan = document.getElementById('total-retiros-global');
                totalSpan.textContent = formatCurrency(-totalGlobal);

                if (totalGlobal > 0) {
                    const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? Math.abs(totalRetirosMatriz) : 0) / totalGlobal) * 100;
                    const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? Math.abs(totalRetirosRocio) : 0) / totalGlobal) * 100;
                    const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? Math.abs(totalRetirosNorte) : 0) / totalGlobal) * 100;

                    const progressMatriz = document.getElementById('progress-retiros-matriz');
                    const progressRocio = document.getElementById('progress-retiros-rocio');
                    const progressNorte = document.getElementById('progress-retiros-norte');

                    progressMatriz.style.width = porcentajeMatriz + '%';
                    progressRocio.style.width = porcentajeRocio + '%';
                    progressNorte.style.width = porcentajeNorte + '%';

                    progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalRetirosMatriz : 0)}`;
                    progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalRetirosRocio : 0)}`;
                    progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalRetirosNorte : 0)}`;
                }
            }

            // Función para obtener y mostrar datos de retiros de la API Matriz
            function fetchAndDisplayRetirosMatriz(ano, mes) {
                const apiUrl = `https://opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`;
                const totalSpan = document.getElementById('total-retiros-matriz');
                const desgloseBody = document.getElementById('desglose-retiros-matriz');
                const loadingOverlay = document.getElementById('loading-overlay-retiros-matriz');

                loadingOverlay.style.display = 'flex';
                totalSpan.textContent = 'CARGANDO...';
                desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                        return response.json();
                    })
                    .then(data => {
                        const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                            const motivo = retiro.motivo.toLowerCase();
                            return !motivo.includes('deposito') && !motivo.includes('depósito');
                        }) : [];
                        
                        const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                        
                        totalRetirosMatriz = totalFiltrado;
                        totalSpan.textContent = formatCurrency(totalRetirosMatriz);
                        updateGlobalRetiros();

                        if (data.retiros && data.retiros.length > 0) {
                            desgloseBody.innerHTML = data.retiros.map(retiro => {
                                const esDeposito = retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito');
                                return `
                                    <tr ${esDeposito ? 'class="bg-light"' : ''}>
                                        <td>${retiro.fecha}</td>
                                        <td>${retiro.motivo} ${esDeposito ? '<span class="badge badge-info">DEPÓSITO</span>' : ''}</td>
                                        <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                        <td>${retiro.usuario}</td>
                                    </tr>
                                `;
                            }).join('');
                        } else {
                            desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY RETIROS REGISTRADOS</td></tr>';
                        }
                        loadingOverlay.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        totalSpan.textContent = 'ERROR';
                        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                        loadingOverlay.style.display = 'none';
                    });
            }

            // Función para obtener y mostrar datos de retiros de la API Rocío
            function fetchAndDisplayRetirosRocio(ano, mes) {
                const apiUrl = `https://escleroptica2.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`;
                const totalSpan = document.getElementById('total-retiros-rocio');
                const desgloseBody = document.getElementById('desglose-retiros-rocio');
                const loadingOverlay = document.getElementById('loading-overlay-retiros-rocio');

                loadingOverlay.style.display = 'flex';
                totalSpan.textContent = 'CARGANDO...';
                desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                        return response.json();
                    })
                    .then(data => {
                        const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                            const motivo = retiro.motivo.toLowerCase();
                            return !motivo.includes('deposito') && !motivo.includes('depósito');
                        }) : [];
                        
                        const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                        
                        totalRetirosRocio = totalFiltrado;
                        totalSpan.textContent = formatCurrency(totalRetirosRocio);
                        updateGlobalRetiros();

                        if (data.retiros && data.retiros.length > 0) {
                            desgloseBody.innerHTML = data.retiros.map(retiro => {
                                const esDeposito = retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito');
                                return `
                                    <tr ${esDeposito ? 'class="bg-light"' : ''}>
                                        <td>${retiro.fecha}</td>
                                        <td>${retiro.motivo} ${esDeposito ? '<span class="badge badge-info">DEPÓSITO</span>' : ''}</td>
                                        <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                        <td>${retiro.usuario}</td>
                                    </tr>
                                `;
                            }).join('');
                        } else {
                            desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY RETIROS REGISTRADOS</td></tr>';
                        }
                        loadingOverlay.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        totalSpan.textContent = 'ERROR';
                        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                        loadingOverlay.style.display = 'none';
                    });
            }

            // Función para obtener y mostrar datos de retiros de la API Norte
            function fetchAndDisplayRetirosNorte(ano, mes) {
                const apiUrl = `https://sucursal3.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`;
                const totalSpan = document.getElementById('total-retiros-norte');
                const desgloseBody = document.getElementById('desglose-retiros-norte');
                const loadingOverlay = document.getElementById('loading-overlay-retiros-norte');

                loadingOverlay.style.display = 'flex';
                totalSpan.textContent = 'CARGANDO...';
                desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                        return response.json();
                    })
                    .then(data => {
                        const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                            const motivo = retiro.motivo.toLowerCase();
                            return !motivo.includes('deposito') && !motivo.includes('depósito');
                        }) : [];
                        
                        const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                        
                        totalRetirosNorte = totalFiltrado;
                        totalSpan.textContent = formatCurrency(totalRetirosNorte);
                        updateGlobalRetiros();

                        if (data.retiros && data.retiros.length > 0) {
                            desgloseBody.innerHTML = data.retiros.map(retiro => {
                                const esDeposito = retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito');
                                return `
                                    <tr ${esDeposito ? 'class="bg-light"' : ''}>
                                        <td>${retiro.fecha}</td>
                                        <td>${retiro.motivo} ${esDeposito ? '<span class="badge badge-info">DEPÓSITO</span>' : ''}</td>
                                        <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                        <td>${retiro.usuario}</td>
                                    </tr>
                                `;
                            }).join('');
                        } else {
                            desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY RETIROS REGISTRADOS</td></tr>';
                        }
                        loadingOverlay.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        totalSpan.textContent = 'ERROR';
                        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                        loadingOverlay.style.display = 'none';
                    });
            }

            // Función para actualizar todas las tarjetas
            function updateAllCards(ano, mes) {
                const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
                
                if (sucursal === '' || sucursal === 'matriz' || tipoSucursal === 'todas') {
                    fetchAndDisplayRetirosMatriz(ano, mes);
                }
                if (sucursal === '' || sucursal === 'rocio' || tipoSucursal === 'todas') {
                    fetchAndDisplayRetirosRocio(ano, mes);
                }
                if (sucursal === '' || sucursal === 'norte' || tipoSucursal === 'todas') {
                    fetchAndDisplayRetirosNorte(ano, mes);
                }

                toggleSucursalCards(sucursal);
            }

            // Función para mostrar/ocultar tarjetas según la sucursal seleccionada
            function toggleSucursalCards(sucursal) {
                const allCards = {
                    'matriz': ['card-retiros-matriz'],
                    'rocio': ['card-retiros-rocio'],
                    'norte': ['card-retiros-norte']
                };

                if (tipoSucursal !== 'todas') {
                    Object.entries(allCards).forEach(([currentSucursal, cards]) => {
                        cards.forEach(cardId => {
                            const card = document.getElementById(cardId);
                            if (card) {
                                card.style.display = currentSucursal === tipoSucursal ? 'block' : 'none';
                            }
                        });
                    });
                    document.getElementById('card-retiros-total').style.display = 'none';
                } else {
                    if (sucursal === '') {
                        Object.values(allCards).flat().forEach(cardId => {
                            document.getElementById(cardId).style.display = 'block';
                        });
                        document.getElementById('card-retiros-total').style.display = 'block';
                    } else {
                        Object.entries(allCards).forEach(([currentSucursal, cards]) => {
                            cards.forEach(cardId => {
                                document.getElementById(cardId).style.display = currentSucursal === sucursal ? 'block' : 'none';
                            });
                        });
                        document.getElementById('card-retiros-total').style.display = 'none';
                    }
                }
            }

            // Event listeners
            const filtroAno = document.getElementById('filtroAno');
            const filtroMes = document.getElementById('filtroMes');
            const filtroSucursal = document.getElementById('filtroSucursal');
            const filtroUsuario = document.getElementById('filtroUsuario');

            filtroAno.addEventListener('change', function() {
                updateAllCards(this.value, filtroMes.value);
            });

            filtroMes.addEventListener('change', function() {
                updateAllCards(filtroAno.value, this.value);
            });

            if (tipoSucursal === 'todas') {
                filtroSucursal.addEventListener('change', function() {
                    updateAllCards(filtroAno.value, filtroMes.value);
                });
            }

            filtroUsuario.addEventListener('change', function() {
                updateAllCards(filtroAno.value, filtroMes.value);
            });

            document.getElementById('actualButton').addEventListener('click', function() {
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;

                filtroAno.value = currentYear;
                filtroMes.value = currentMonth;
                if (tipoSucursal === 'todas') {
                    filtroSucursal.value = '';
                }
                document.getElementById('filtroUsuario').value = '';

                updateAllCards(currentYear, currentMonth);
            });

            // Carga inicial de datos
            updateAllCards(filtroAno.value, filtroMes.value);
        });
    </script>
@stop 