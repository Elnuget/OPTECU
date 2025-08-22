@extends('adminlte::page')

@section('title', 'PRÉSTAMOS')

@section('content_header')
    <h1>PRÉSTAMOS</h1>
    <p>ADMINISTRACIÓN DE PRÉSTAMOS</p>
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
        .close {
            text-transform: uppercase !important;
        }
    </style>

    {{-- Tarjetas de Resumen de Cuotas Individuales de Préstamos --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL SUMA DE CUOTAS INDIVIDUALES DE PRÉSTAMOS ACTIVOS</span>
                    <span class="info-box-number" id="summary-cuotas-total">CARGANDO...</span>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                    <span class="progress-description" id="summary-cuotas-detalle">CALCULANDO SUMA DE CUOTAS INDIVIDUALES POR SUCURSAL...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Resumen por Sucursal --}}
    <div class="row mb-4" id="cuotas-por-sucursal">
        {{-- Las tarjetas de sucursales se generarán dinámicamente --}}
    </div>
    {{-- Fin Tarjetas de Resumen --}}

    {{-- Filtro de Empresa --}}
    <div class="card card-outline card-purple mb-4">
        <div class="card-header">
            <h3 class="card-title">FILTROS</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtro-empresa">FILTRAR POR EMPRESA:</label>
                        <select class="form-control" id="filtro-empresa">
                            <option value="todas">TODAS LAS EMPRESAS</option>
                            @foreach($empresas as $emp)
                                <option value="{{ $emp->id }}" data-nombre="{{ $emp->nombre }}">{{ $emp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Fin Filtro de Empresa --}}

    {{-- Tarjeta Plegable Pagos de Préstamos --}}
    <div class="card card-outline card-purple card-widget collapsed-card" id="card-pagos-prestamos">
        <div class="card-header">
            <h3 class="card-title">PAGOS DE PRÉSTAMOS: <span id="summary-pagos-total-badge" class="badge badge-warning">$0.00</span></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>USUARIO</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>EMPRESA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="desglose-pagos-prestamos">
                        <tr><td colspan="6" class="text-center">CARGANDO DATOS...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="overlay dark" id="loading-overlay-pagos" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>
    {{-- Fin Tarjeta Plegable Pagos de Préstamos --}}

    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">LISTA DE PRÉSTAMOS ACTIVOS (VALORES NETOS CALCULADOS)</h3>
        </div>
        <div class="card-body">
            {{-- Botón Añadir Préstamo --}}
            <div class="btn-group mb-3">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearPrestamoModal">
                    <i class="fas fa-plus mr-2"></i>AÑADIR PRÉSTAMO
                </button>
            </div>

            <div class="table-responsive">
                <table id="prestamosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>USUARIO</th>
                            <th>MOTIVO</th>
                            <th>VALORES</th>
                            <th>CUOTAS</th>
                            <th>EMPRESA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prestamos as $prestamo)
                            <tr data-prestamo-id="{{ $prestamo->id }}" data-original-valor="{{ $prestamo->valor }}" data-valor-neto="{{ $prestamo->valor_neto }}" data-cuotas="{{ $prestamo->cuotas }}" data-empresa-id="{{ $prestamo->empresa_id }}" data-saldo-pendiente="{{ $prestamo->saldo_pendiente }}" data-valor-cuota="{{ $prestamo->valor_cuota }}">
                                <td>{{ $prestamo->created_at->format('Y-m-d') }}</td>
                                <td class="prestamo-usuario">{{ $prestamo->user->name }}</td>
                                <td class="prestamo-motivo">{{ $prestamo->motivo }}</td>
                                <td class="prestamo-valores">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">ORIGINAL: ${{ number_format($prestamo->valor, 2, ',', '.') }}</small>
                                        <span class="font-weight-bold">NETO: ${{ number_format($prestamo->valor_neto, 2, ',', '.') }}</span>
                                        <small class="text-info prestamo-deducciones">DEDUCCIONES: CARGANDO...</small>
                                    </div>
                                </td>
                                <td class="prestamo-cuotas">{{ $prestamo->cuotas }}</td>
                                <td class="prestamo-empresa">{{ $prestamo->empresa->nombre ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                            class="btn btn-xs btn-default text-info mx-1 shadow" 
                                            title="Ver"
                                            onclick="window.location.href='{{ route('prestamos.show', $prestamo->id) }}'">
                                            <i class="fa fa-lg fa-fw fa-eye"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-xs btn-default text-success mx-1 shadow"
                                            title="Añadir Pago"
                                            onclick="abrirModalPago({{ $prestamo->id }}, '{{ $prestamo->user->name }}', {{ $prestamo->saldo_pendiente ?: 0 }}, {{ $prestamo->valor_cuota ?: 0 }})"
                                            data-toggle="modal" 
                                            data-target="#crearPagoModal">
                                            <i class="fa fa-lg fa-fw fa-money-bill"></i>
                                        </button>
                                        @can('admin')
                                        <button type="button"
                                            class="btn btn-xs btn-default text-primary mx-1 shadow"
                                            title="Editar"
                                            onclick="window.location.href='{{ route('prestamos.edit', $prestamo->id) }}'">
                                            <i class="fa fa-lg fa-fw fa-pen"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-xs btn-default text-danger mx-1 shadow"
                                            onclick="eliminarPrestamo({{ $prestamo->id }})"
                                            title="Eliminar">
                                            <i class="fa fa-lg fa-fw fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabla de Préstamos Pagados --}}
    <div class="card mt-4">
        <div class="card-header bg-success">
            <h3 class="card-title">LISTA DE PRÉSTAMOS PAGADOS (COMPLETAMENTE LIQUIDADOS)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="prestamosPagadosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>USUARIO</th>
                            <th>MOTIVO</th>
                            <th>VALORES</th>
                            <th>CUOTAS</th>
                            <th>EMPRESA</th>
                            <th>FECHA LIQUIDACIÓN</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Los préstamos pagados se moverán aquí dinámicamente --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Fin Tabla de Préstamos Pagados --}}

    <!-- Modal Crear Préstamo -->
    <div class="modal fade" id="crearPrestamoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CREAR PRÉSTAMO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('prestamos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">USUARIO:</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">SELECCIONE UN USUARIO</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="empresa_id">SUCURSAL:</label>
                            <select name="empresa_id" id="empresa_id" class="form-control" required>
                                <option value="">SELECCIONE UNA SUCURSAL</option>
                                @foreach(\App\Models\Empresa::orderBy('nombre')->get() as $empresa)
                                    <option value="{{ $empresa->id }}" {{ auth()->user()->empresa_id == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="valor">VALOR:</label>
                            <input type="number" class="form-control" id="valor" name="valor" required step="0.01" min="0">
                            <small class="text-muted">Este valor será utilizado como valor neto.</small>
                            <input type="hidden" id="valor_neto" name="valor_neto">
                        </div>
                        <div class="form-group">
                            <label for="cuotas">CUOTAS:</label>
                            <input type="number" class="form-control" id="cuotas" name="cuotas" required min="1" value="1">
                        </div>
                        <div class="form-group">
                            <label for="motivo">MOTIVO:</label>
                            <input type="text" class="form-control" id="motivo" name="motivo" required maxlength="255">
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
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE PRÉSTAMO?</p>
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

    <!-- Modal Crear Pago de Préstamo -->
    <div class="modal fade" id="crearPagoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">REGISTRAR PAGO DE PRÉSTAMO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('pago-prestamos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="prestamo_id" id="pago_prestamo_id">
                        <input type="hidden" name="empresa_id" id="pago_empresa_id" value="{{ auth()->user()->empresa_id }}">
                        <input type="hidden" name="user_id" id="pago_user_id" value="{{ auth()->id() }}">
                        
                        <div class="alert alert-info">
                            <p class="mb-0"><strong>USUARIO:</strong> <span id="pago_nombre_usuario"></span></p>
                            <p class="mb-0"><strong>SALDO PENDIENTE:</strong> <span id="pago_saldo_pendiente"></span></p>
                            <p class="mb-0"><strong>VALOR SUGERIDO DE CUOTA:</strong> <span id="pago_valor_cuota"></span></p>
                        </div>

                        <div class="form-group">
                            <label for="valor">VALOR DEL PAGO:</label>
                            <input type="number" class="form-control" id="pago_valor" name="valor" required step="0.01" min="0.01">
                        </div>

                        <div class="form-group">
                            <label for="fecha_pago">FECHA DE PAGO:</label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label for="motivo">MOTIVO/DESCRIPCIÓN:</label>
                            <input type="text" class="form-control" id="pago_motivo" name="motivo" required maxlength="255" placeholder="ABONO A PRÉSTAMO">
                        </div>

                        <div class="form-group">
                            <label for="observaciones">OBSERVACIONES (OPCIONAL):</label>
                            <textarea class="form-control" id="pago_observaciones" name="observaciones" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="estado">ESTADO:</label>
                            <select name="estado" id="pago_estado" class="form-control" required>
                                <option value="pagado">PAGADO</option>
                                <option value="pendiente">PENDIENTE</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-success">REGISTRAR PAGO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
    {{-- Cargar Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    
    {{-- Cargar Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        let detallesPagosGlobal = [];
        let pagosCargados = false;
        let empresaSeleccionada = 'TODAS';
        let empresaIdSeleccionada = 'todas';
        let prestamosTableRef = null;
        let prestamosPagadosTableRef = null;
        let empresasData = @json($empresas);

        // Función para formatear números como moneda
        function formatCurrency(number) {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
        }

        // Función para calcular cuotas individuales de préstamos activos
        function calcularCuotasIndividuales() {
            const cuotasPorEmpresa = {};
            let totalGeneralCuotasIndividuales = 0;

            // Inicializar contadores por empresa
            empresasData.forEach(empresa => {
                cuotasPorEmpresa[empresa.id] = {
                    nombre: empresa.nombre,
                    valorCuotasIndividuales: 0,
                    prestamosActivos: 0
                };
            });

            // Recorrer TODAS las filas de préstamos ACTIVOS (sin filtro)
            if (prestamosTableRef) {
                prestamosTableRef.rows().every(function() {
                    const rowNode = this.node();
                    const $rowNode = $(rowNode);

                    const originalValor = parseFloat($rowNode.data('original-valor')) || 0;
                    const totalCuotas = parseInt($rowNode.data('cuotas')) || 1;
                    const empresaId = $rowNode.data('empresa-id');

                    if (empresaId && cuotasPorEmpresa[empresaId]) {
                        // Valor de cada cuota individual = valor del préstamo ÷ número de cuotas
                        const valorCuotaIndividual = originalValor / totalCuotas;

                        cuotasPorEmpresa[empresaId].valorCuotasIndividuales += valorCuotaIndividual;
                        cuotasPorEmpresa[empresaId].prestamosActivos += 1;
                        
                        totalGeneralCuotasIndividuales += valorCuotaIndividual;
                    }
                });
            }

            return {
                porEmpresa: cuotasPorEmpresa,
                totalGeneral: totalGeneralCuotasIndividuales
            };
        }

        // Función para actualizar la visualización de cuotas individuales
        function actualizarVisualizacionCuotas() {
            const datosCuotas = calcularCuotasIndividuales();
            
            // Actualizar total general
            const summarySpan = document.getElementById('summary-cuotas-total');
            const summaryDetalle = document.getElementById('summary-cuotas-detalle');
            
            if (summarySpan) {
                summarySpan.textContent = formatCurrency(datosCuotas.totalGeneral);
            }
            
            // Generar detalle por sucursal
            const sucursalesContainer = document.getElementById('cuotas-por-sucursal');
            if (sucursalesContainer) {
                let htmlSucursales = '';
                let detalleTexto = '';
                
                Object.entries(datosCuotas.porEmpresa).forEach(([empresaId, datos]) => {
                    if (datos.prestamosActivos > 0) {
                        const porcentaje = datosCuotas.totalGeneral > 0 ? 
                            (datos.valorCuotasIndividuales / datosCuotas.totalGeneral * 100).toFixed(1) : 0;
                        
                        htmlSucursales += `
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">${datos.nombre}</span>
                                        <span class="info-box-number">${formatCurrency(datos.valorCuotasIndividuales)}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: ${porcentaje}%"></div>
                                        </div>
                                        <span class="progress-description">
                                            SUMA DE CUOTAS INDIVIDUALES EN ${datos.prestamosActivos} PRÉSTAMOS
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        detalleTexto += `${datos.nombre}: ${formatCurrency(datos.valorCuotasIndividuales)} (${datos.prestamosActivos} préstamos) | `;
                    }
                });
                
                // Si no hay préstamos activos
                if (Object.values(datosCuotas.porEmpresa).every(datos => datos.prestamosActivos === 0)) {
                    htmlSucursales = `
                        <div class="col-md-12 mb-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">SIN PRÉSTAMOS ACTIVOS</span>
                                    <span class="info-box-number">$0.00</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">NO HAY PRÉSTAMOS ACTIVOS EN NINGUNA SUCURSAL</span>
                                </div>
                            </div>
                        </div>
                    `;
                    detalleTexto = 'NO HAY PRÉSTAMOS ACTIVOS';
                }
                
                sucursalesContainer.innerHTML = htmlSucursales;
                
                if (summaryDetalle) {
                    summaryDetalle.textContent = detalleTexto.slice(0, -3) || 'NO HAY DATOS DISPONIBLES';
                }
            }
        }

        // Función para filtrar pagos por empresa
        function filtrarPagosPorEmpresa(pagos, empresaId) {
            if (empresaId === 'todas' || !empresaId) {
                return pagos;
            }
            return pagos.filter(pago => pago.empresa_id == empresaId);
        }

        // Función para actualizar la visualización de pagos
        function actualizarVisualizacionPagos() {
            const pagosFiltrados = filtrarPagosPorEmpresa(detallesPagosGlobal, empresaIdSeleccionada);
            
            const totalPagosFiltrados = pagosFiltrados.reduce((sum, pago) => sum + parseFloat(pago.valor), 0);
            const summarySpan = document.getElementById('summary-pagos-total');
            const summaryBadge = document.getElementById('summary-pagos-total-badge');
            const totalFormatted = formatCurrency(totalPagosFiltrados);
            
            if (summarySpan) summarySpan.textContent = totalFormatted;
            if (summaryBadge) summaryBadge.textContent = totalFormatted;

            const desgloseBody = document.getElementById('desglose-pagos-prestamos');
            
            if (desgloseBody) {
                if (pagosFiltrados.length > 0) {
                    desgloseBody.innerHTML = pagosFiltrados.map(pago => `
                        <tr>
                            <td>${pago.fecha}</td>
                            <td>${pago.usuario}</td>
                            <td>${pago.motivo}</td>
                            <td>$${parseFloat(pago.valor).toFixed(2)}</td>
                            <td>${pago.empresa || 'N/A'}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" 
                                        title="Editar Pago"
                                        onclick="abrirModalEditarPago(${pago.id}, ${pago.prestamo_id})">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </button>
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-danger mx-1 shadow" 
                                        title="Eliminar Pago"
                                        onclick="confirmarEliminarPago(${pago.id})">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="6" class="text-center">NO HAY PAGOS DE PRÉSTAMOS PARA LA EMPRESA SELECCIONADA.</td></tr>';
                }
            }

            actualizarValoresNetosPrestamos();
        }

        // Función combinada para cargar todos los datos de pagos
        async function cargarDatosPagos() {
            const summarySpan = document.getElementById('summary-pagos-total-badge');
            const desgloseBody = document.getElementById('desglose-pagos-prestamos');
            const loadingOverlay = document.getElementById('loading-overlay-pagos');
            const summaryCuotas = document.getElementById('summary-cuotas-total');
            const summaryCuotasDetalle = document.getElementById('summary-cuotas-detalle');

            if (summarySpan) summarySpan.textContent = 'CARGANDO...';
            if (summaryCuotas) summaryCuotas.textContent = 'CARGANDO...';
            if (summaryCuotasDetalle) summaryCuotasDetalle.textContent = 'CALCULANDO CUOTAS...';
            if (desgloseBody) desgloseBody.innerHTML = '<tr><td colspan="6" class="text-center">CARGANDO DATOS...</td></tr>';
            if (loadingOverlay) loadingOverlay.style.display = 'flex';

            // Mostrar estado de carga en las deducciones
            $('.prestamo-deducciones').text('CARGANDO...');

            try {
                const response = await fetch('/api/prestamos/pagos-locales', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const data = await response.json();
                let todosLosPagos = data.pagos || [];

                todosLosPagos = todosLosPagos.map(pago => ({
                    ...pago,
                    empresa: pago.empresa || 'N/A',
                    valorAbs: parseFloat(pago.valor || 0),
                    id: pago.id || 0,
                    prestamo_id: pago.prestamo_id || 0
                }));

                detallesPagosGlobal = todosLosPagos;
                pagosCargados = true;

                console.log('Datos de pagos cargados:', detallesPagosGlobal.length, 'registros');

                const totalPagos = data.total_pagos || 0;
                if (summarySpan) summarySpan.textContent = formatCurrency(totalPagos);

                // Actualizar visualización después de cargar los datos
                actualizarVisualizacionPagos();
                
                // Esperar un momento antes de actualizar los valores netos para asegurar que el DOM esté listo
                setTimeout(() => {
                    actualizarValoresNetosPrestamos();
                    // Actualizar también las cuotas después de procesar los préstamos
                    setTimeout(() => {
                        actualizarVisualizacionCuotas();
                    }, 100);
                }, 200);

            } catch (error) {
                console.error('Error al obtener pagos de préstamos:', error);
                if (summarySpan) summarySpan.textContent = 'ERROR';
                if (summaryCuotas) summaryCuotas.textContent = 'ERROR AL CARGAR';
                if (summaryCuotasDetalle) summaryCuotasDetalle.textContent = 'ERROR AL CALCULAR CUOTAS';
                if (desgloseBody) desgloseBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">ERROR AL CARGAR LOS DATOS DE PAGOS.</td></tr>';
                
                // Mostrar error en las deducciones
                $('.prestamo-deducciones').text('ERROR AL CARGAR');
            } finally {
                if (loadingOverlay) loadingOverlay.style.display = 'none';
            }
        }

        // Función auxiliar para normalizar texto
        function normalizarTexto(texto) {
            if (!texto) return '';
            return texto.toLowerCase()
                .replace(/[áäâà]/g, 'a')
                .replace(/[éëêè]/g, 'e')
                .replace(/[íïîì]/g, 'i')
                .replace(/[óöôò]/g, 'o')
                .replace(/[úüûù]/g, 'u');
        }

        // Función auxiliar para obtener palabras clave
        function obtenerPalabrasClave(texto) {
            const textoNormalizado = normalizarTexto(texto);
            const stopwords = ['de', 'para', 'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'con', 'por', 'en', 'a', 'y', 'o', 'q', 'que', 'del', 'al', 'mi', 'su'];
            return textoNormalizado
                .split(/[^a-z0-9]+/)
                .filter(palabra => palabra.length > 3 && !stopwords.includes(palabra) && isNaN(palabra));
        }

        // Función para calcular y actualizar los valores netos en la tabla de préstamos
        function actualizarValoresNetosPrestamos() {
            if (!prestamosTableRef || !prestamosPagadosTableRef) {
                console.warn('Las tablas aún no están inicializadas');
                return;
            }

            if (!detallesPagosGlobal || detallesPagosGlobal.length === 0) {
                console.warn('Los datos de pagos aún no están cargados');
                // Mostrar "CARGANDO..." en las celdas de deducciones
                $('.prestamo-deducciones').text('CARGANDO...');
                return;
            }

            try {
                prestamosTableRef.rows().every(function() {
                    const rowNode = this.node();
                    const $rowNode = $(rowNode);

                    const originalValor = parseFloat($rowNode.data('original-valor')) || 0;
                    const cuotasBD = parseInt($rowNode.data('cuotas')) || 0;
                    
                    const usuarioNombre = $rowNode.find('td.prestamo-usuario').text().trim();
                    const usuarioNombreNormalizado = normalizarTexto(usuarioNombre);
                    const motivoPrestamoOriginalText = $rowNode.find('td.prestamo-motivo').text().trim();
                    const palabrasClavePrestamo = obtenerPalabrasClave(motivoPrestamoOriginalText);

                    const $valoresCell = $rowNode.find('td.prestamo-valores');
                    const $cuotasCell = $rowNode.find('td.prestamo-cuotas');

                    const pagosFiltrados = empresaIdSeleccionada === 'todas' 
                        ? detallesPagosGlobal 
                        : detallesPagosGlobal.filter(pago => pago.empresa_id == empresaIdSeleccionada);

                    let pagosDetallados = [];

                    const prestamoId = $rowNode.data('prestamo-id');
                    const pagosRelacionados = pagosFiltrados.filter(pago => {
                        if (pago.prestamo_id) {
                            return pago.prestamo_id == prestamoId;
                        }
                        const motivoPagoNormalizado = normalizarTexto(pago.motivo);
                        if (usuarioNombreNormalizado.length > 0 && motivoPagoNormalizado.includes(usuarioNombreNormalizado)) {
                            return true;
                        }
                        if (palabrasClavePrestamo.length > 0) {
                            return palabrasClavePrestamo.some(clave => motivoPagoNormalizado.includes(clave));
                        }
                        return false;
                    });

                    pagosRelacionados.forEach(pago => {
                        pagosDetallados.push({
                            fecha: pago.fecha,
                            motivo: pago.motivo,
                            valor: parseFloat(pago.valor) || 0,
                            usuario: pago.usuario
                        });
                    });

                    const totalPagosAplicados = pagosDetallados.reduce((sum, d) => sum + d.valor, 0);
                    
                    const valorNetoActualizado = originalValor - totalPagosAplicados;
                    
                    // Si el préstamo está completamente pagado (valor neto <= 0), moverlo a la tabla de pagados
                    if (valorNetoActualizado <= 0 && pagosDetallados.length > 0) {
                        moverPrestamoAPagados($rowNode, pagosDetallados);
                        this.remove();
                        return;
                    }
                    
                    let valoresHtml = `
                        <div class="d-flex flex-column">
                            <small class="text-muted" title="Valor original del préstamo">ORIGINAL: ${formatCurrency(originalValor)}</small>
                            <strong class="text-success" title="Valor neto actual">NETO: ${formatCurrency(valorNetoActualizado)}</strong>
                            <small class="text-danger prestamo-deducciones" title="Total de pagos/deducciones aplicados">DEDUCCIONES: ${formatCurrency(totalPagosAplicados)}</small>
                        </div>
                    `;
                    
                    $valoresCell.html(valoresHtml);
                    
                    const cuotasTotal = cuotasBD;
                    const cuotasPagadas = pagosDetallados.length;
                    const cuotasPendientes = Math.max(0, cuotasTotal - cuotasPagadas);

                    let cuotasHtml = '';
                    if (cuotasTotal > 0) {
                        cuotasHtml = `
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge badge-primary">TOTAL: ${cuotasTotal}</span>
                                <span class="badge badge-success mt-1">PAGADAS: ${cuotasPagadas}</span>
                                <span class="badge badge-warning mt-1">PENDIENTES: ${cuotasPendientes}</span>
                            </div>
                        `;
                    } else {
                        cuotasHtml = '<span class="text-muted">SIN CUOTAS DEFINIDAS</span>';
                    }
                    $cuotasCell.html(cuotasHtml);

                    let pagosTooltipHtml = 'Ningún pago registrado.';
                    if (pagosDetallados.length > 0) {
                        pagosDetallados.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
                        pagosTooltipHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.8em; text-align: left;">';
                        pagosDetallados.forEach(d => {
                            pagosTooltipHtml += `<li><strong>${d.fecha}:</strong> ${formatCurrency(d.valor)} - ${d.motivo.substring(0, 20)}...</li>`;
                        });
                        pagosTooltipHtml += '</ul>';
                    }

                    // Usar setTimeout para asegurar que el DOM esté actualizado antes de agregar tooltips
                    setTimeout(() => {
                        const $deduccionesElement = $valoresCell.find('.prestamo-deducciones');
                        if ($deduccionesElement.length > 0) {
                            $deduccionesElement.attr('data-toggle', 'tooltip')
                                             .attr('data-html', 'true')
                                             .attr('title', pagosTooltipHtml);
                            
                            // Inicializar tooltip si está disponible
                            if (typeof $deduccionesElement.tooltip === 'function') {
                                $deduccionesElement.tooltip();
                            }
                        }
                    }, 50);
                });
                
                // Redibujar las tablas para reflejar los cambios
                prestamosTableRef.draw();
                prestamosPagadosTableRef.draw();
                
            } catch (error) {
                console.error('Error al actualizar valores netos de préstamos:', error);
                $('.prestamo-deducciones').text('ERROR AL CALCULAR');
            }
        }

        // Función para mover un préstamo a la tabla de pagados
        function moverPrestamoAPagados($rowNode, pagosDetallados) {
            const prestamoId = $rowNode.data('prestamo-id');
            const originalValor = parseFloat($rowNode.data('original-valor'));
            const cuotasBD = parseInt($rowNode.data('cuotas')) || 0;
            
            const fechaCreacion = $rowNode.find('td').eq(0).text();
            const usuario = $rowNode.find('td.prestamo-usuario').text();
            const motivo = $rowNode.find('td.prestamo-motivo').text();
            const empresa = $rowNode.find('td.prestamo-empresa').text();
            
            const totalPagosAplicados = pagosDetallados.reduce((sum, d) => sum + d.valor, 0);
            const valorNetoFinal = originalValor - totalPagosAplicados;
            
            // Encontrar la fecha del último pago como fecha de liquidación
            const fechasPagos = pagosDetallados.map(p => new Date(p.fecha)).sort((a, b) => b - a);
            const fechaLiquidacion = fechasPagos.length > 0 ? fechasPagos[0].toISOString().split('T')[0] : fechaCreacion;
            
            const cuotasPagadas = pagosDetallados.length;
            
            let valoresHtml = `
                <div class="d-flex flex-column">
                    <small class="text-muted">ORIGINAL: ${formatCurrency(originalValor)}</small>
                    <strong class="text-success">NETO: ${formatCurrency(valorNetoFinal)}</strong>
                    <small class="text-success">PAGADO: ${formatCurrency(totalPagosAplicados)}</small>
                    <span class="badge badge-success mt-1">LIQUIDADO</span>
                </div>
            `;
            
            let cuotasHtml = '';
            if (cuotasBD > 0) {
                cuotasHtml = `
                    <div class="d-flex flex-column align-items-start">
                        <span class="badge badge-primary">TOTAL: ${cuotasBD}</span>
                        <span class="badge badge-success mt-1">PAGADAS: ${cuotasPagadas}</span>
                        <span class="badge badge-success mt-1">COMPLETADO</span>
                    </div>
                `;
            } else {
                cuotasHtml = '<span class="badge badge-success">COMPLETADO</span>';
            }
            
            let accionesHtml = `
                <div class="btn-group">
                    <button type="button" 
                        class="btn btn-xs btn-default text-info mx-1 shadow" 
                        title="Ver"
                        onclick="window.location.href='/prestamos/${prestamoId}'">
                        <i class="fa fa-lg fa-fw fa-eye"></i>
                    </button>
                    <span class="btn btn-xs btn-default text-muted mx-1" title="Préstamo Liquidado">
                        <i class="fa fa-lg fa-fw fa-check-circle"></i>
                    </span>
                </div>
            `;
            
            // Agregar la fila a la tabla de préstamos pagados
            prestamosPagadosTableRef.row.add([
                fechaCreacion,
                usuario,
                motivo,
                valoresHtml,
                cuotasHtml,
                empresa,
                fechaLiquidacion,
                accionesHtml
            ]);
            
            // Actualizar las cuotas después de mover el préstamo
            setTimeout(() => {
                actualizarVisualizacionCuotas();
            }, 50);
        }

        $(document).ready(function() {
            prestamosTableRef = $('#prestamosTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,
                "language": {
                    "url": "{{ asset('js/datatables/Spanish.json') }}"
                },
                "columnDefs": [
                    { "targets": [5], "searchable": true, "visible": true } 
                ]
            });

            prestamosPagadosTableRef = $('#prestamosPagadosTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,
                "language": {
                    "url": "{{ asset('js/datatables/Spanish.json') }}"
                },
                "columnDefs": [
                    { "targets": [5], "searchable": true, "visible": true } 
                ]
            });

            $('#valor').on('input', function() {
                const valorIngresado = parseFloat($(this).val()) || 0;
                $('#valor_neto').val(valorIngresado);
            });
            
            // Al enviar el formulario, asegurar que valor_neto = valor
            $('form[action="{{ route('prestamos.store') }}"]').on('submit', function() {
                const valor = parseFloat($('#valor').val()) || 0;
                $('#valor_neto').val(valor);
            });

            $('#filtro-empresa').on('change', function() {
                empresaIdSeleccionada = $(this).val();
                empresaSeleccionada = $(this).find('option:selected').text();
                
                if (empresaIdSeleccionada === 'todas') {
                    prestamosTableRef.columns(5).search('').draw();
                    prestamosPagadosTableRef.columns(5).search('').draw();
                } else {
                    prestamosTableRef.columns(5).search('^' + empresaSeleccionada + '$', true, false).draw();
                    prestamosPagadosTableRef.columns(5).search('^' + empresaSeleccionada + '$', true, false).draw();
                }

                actualizarVisualizacionPagos();
            });

            // Cargar datos después de un pequeño retraso para asegurar que las tablas estén listas
            setTimeout(function() {
                cargarDatosPagos();
            }, 100);

            // Inicializar Select2 solo si está disponible
            if (typeof $.fn.select2 !== 'undefined') {
                $('#user_id, #empresa_id').select2({
                    theme: 'bootstrap4',
                    placeholder: 'SELECCIONE UNA OPCIÓN',
                    allowClear: true,
                    width: '100%'
                });
            } else {
                console.warn('Select2 no está disponible. Los selectores funcionarán como elementos HTML normales.');
            }

            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
                
                // Limpiar selects con Select2 si está disponible
                if (typeof $.fn.select2 !== 'undefined') {
                    $(this).find('select').val('').trigger('change');
                } else {
                    // Fallback para selects normales
                    $(this).find('select').val('');
                }
                
                $(this).find('input[name="_method"]').remove();
                
                // Restaurar action por defecto para el modal de pago
                if ($(this).attr('id') === 'crearPagoModal') {
                    $(this).find('form').attr('action', '{{ route('pago-prestamos.store') }}');
                    $(this).find('.modal-title').text('REGISTRAR PAGO DE PRÉSTAMO');
                    $(this).find('button[type="submit"]').text('REGISTRAR PAGO');
                }
            });

            if (window.SucursalCache) {
                SucursalCache.preseleccionarEnSelect('filtro-empresa');
                $('#filtro-empresa').trigger('change');
            }

            // Actualizar cuotas inicialmente (para mostrar datos básicos antes de cargar pagos)
            setTimeout(() => {
                actualizarVisualizacionCuotas();
            }, 300);
        });

        function eliminarPrestamo(id) {
            if (confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE PRÉSTAMO?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '/prestamos/' + id;
                
                var tokenField = document.createElement('input');
                tokenField.type = 'hidden';
                tokenField.name = '_token';
                tokenField.value = '{{ csrf_token() }}';
                
                var methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(tokenField);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para abrir el modal de pago y configurar los valores iniciales
        function abrirModalPago(prestamoId, nombreUsuario, saldoPendiente, valorCuota) {
            // Usar el valor del atributo data-saldo-pendiente si está disponible
            const $fila = $(`tr[data-prestamo-id="${prestamoId}"]`);
            const saldoPendienteActual = $fila.data('saldo-pendiente') || saldoPendiente || 0;
            const valorCuotaActual = $fila.data('valor-cuota') || valorCuota || saldoPendienteActual;
            
            // Configurar modal para nuevo pago
            $('#crearPagoModal').find('form').attr('action', '{{ route('pago-prestamos.store') }}');
            $('#crearPagoModal').find('.modal-title').text('REGISTRAR PAGO DE PRÉSTAMO');
            $('#crearPagoModal').find('button[type="submit"]').text('REGISTRAR PAGO');
            
            $('#pago_prestamo_id').val(prestamoId);
            $('#pago_nombre_usuario').text(nombreUsuario);
            $('#pago_saldo_pendiente').text(formatCurrency(saldoPendienteActual));
            $('#pago_valor_cuota').text(formatCurrency(valorCuotaActual));
            
            // Establecer el valor del pago como el valor de la cuota, limitado al saldo pendiente
            const valorPagoSugerido = Math.min(valorCuotaActual, saldoPendienteActual);
            $('#pago_valor').attr('max', saldoPendienteActual).val(valorPagoSugerido);
            $('#pago_motivo').val('ABONO A PRÉSTAMO - CUOTA');
            $('#fecha_pago').val('{{ date('Y-m-d') }}');
            $('#pago_observaciones').val('');
            $('#pago_estado').val('pagado');
            
            // Remover cualquier campo _method que pudiera existir de una edición anterior
            $('#crearPagoModal form').find('input[name="_method"]').remove();
            
            // Si está seleccionada una empresa específica, usarla
            if (empresaIdSeleccionada !== 'todas') {
                $('#pago_empresa_id').val(empresaIdSeleccionada);
            } else {
                // Obtener el ID de la empresa del préstamo
                const empresaId = $fila.data('empresa-id');
                $('#pago_empresa_id').val(empresaId);
            }
        }
        
        // Función para abrir el modal de edición de pago
        async function abrirModalEditarPago(pagoId, prestamoId) {
            try {
                // Mostrar loader
                $('#loading-overlay-pagos').show();
                
                // Obtener datos del pago
                const response = await fetch(`/pago-prestamos/${pagoId}/edit`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Error al obtener datos del pago');
                }
                
                const pago = await response.json();
                
                // Configurar modal para edición
                const $modal = $('#crearPagoModal');
                $modal.find('form').attr('action', `/pago-prestamos/${pagoId}`);
                $modal.find('form').append('<input type="hidden" name="_method" value="PUT">');
                $modal.find('.modal-title').text('EDITAR PAGO DE PRÉSTAMO');
                $modal.find('button[type="submit"]').text('ACTUALIZAR PAGO');
                
                // Rellenar datos del pago
                $('#pago_prestamo_id').val(pago.prestamo_id);
                $('#pago_empresa_id').val(pago.empresa_id);
                $('#pago_nombre_usuario').text(pago.usuario || 'N/A');
                $('#pago_saldo_pendiente').text('PAGO EXISTENTE');
                $('#pago_valor_cuota').text('EDITANDO PAGO EXISTENTE');
                $('#pago_valor').val(pago.valor);
                $('#pago_motivo').val(pago.motivo);
                $('#fecha_pago').val(pago.fecha_pago);
                $('#pago_observaciones').val(pago.observaciones || '');
                $('#pago_estado').val(pago.estado || 'pagado');
                
                // Abrir el modal
                $modal.modal('show');
                
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar los datos del pago');
            } finally {
                $('#loading-overlay-pagos').hide();
            }
        }
        
        // Función para confirmar eliminación de un pago
        function confirmarEliminarPago(pagoId) {
            if (confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE PAGO DE PRÉSTAMO?')) {
                // Crear formulario para enviar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/pago-prestamos/${pagoId}`;
                form.style.display = 'none';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@stop 