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

    {{-- Tarjetas de Resumen de Pagos --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL PAGOS DE PRÉSTAMOS</span>
                    <span class="info-box-number" id="summary-pagos-total">CARGANDO...</span>
                </div>
            </div>
        </div>
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
                        </tr>
                    </thead>
                    <tbody id="desglose-pagos-prestamos">
                        <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
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
            <h3 class="card-title">LISTA DE PRÉSTAMOS (VALORES NETOS CALCULADOS)</h3>
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
                            <label for="valor">VALOR:</label>
                            <input type="number" class="form-control" id="valor" name="valor" required step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="valor_neto">VALOR NETO:</label>
                            <input type="number" class="form-control" id="valor_neto" name="valor_neto" required step="0.01" min="0">
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
    <script>
        let detallesPagosGlobal = [];
        let pagosCargados = false;
        let empresaSeleccionada = 'TODAS';
        let empresaIdSeleccionada = 'todas';

        // Función para formatear números como moneda
        function formatCurrency(number) {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
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
                        </tr>
                    `).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY PAGOS DE PRÉSTAMOS PARA LA EMPRESA SELECCIONADA.</td></tr>';
                }
            }

            actualizarValoresNetosPrestamos();
        }

        // Función combinada para cargar todos los datos de pagos
        async function cargarDatosPagos() {
            const summarySpan = document.getElementById('summary-pagos-total');
            const desgloseBody = document.getElementById('desglose-pagos-prestamos');
            const loadingOverlay = document.getElementById('loading-overlay-pagos');

            if (summarySpan) summarySpan.textContent = 'CARGANDO...';
            if (desgloseBody) desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';
            if (loadingOverlay) loadingOverlay.style.display = 'flex';

            try {
                const response = await fetch('/api/prestamos/pagos-locales', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }

                const data = await response.json();
                let todosLosPagos = data.pagos || [];

                todosLosPagos = todosLosPagos.map(pago => ({
                    ...pago,
                    empresa: pago.empresa || 'N/A',
                    valorAbs: parseFloat(pago.valor || 0)
                }));

                detallesPagosGlobal = todosLosPagos;
                pagosCargados = true;

                const totalPagos = data.total_pagos || 0;
                if (summarySpan) summarySpan.textContent = formatCurrency(totalPagos);

                actualizarVisualizacionPagos();

            } catch (error) {
                console.error('Error al obtener pagos de préstamos:', error);
                if (summarySpan) summarySpan.textContent = 'ERROR AL CARGAR';
                if (desgloseBody) desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS DE PAGOS.</td></tr>';
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
            const prestamosTable = $('#prestamosTable').DataTable();

            prestamosTable.rows().every(function() {
                const rowNode = this.node();
                const $rowNode = $(rowNode);

                const originalValor = parseFloat($rowNode.data('original-valor'));
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
                        valor: parseFloat(pago.valor),
                        usuario: pago.usuario
                    });
                });

                const totalPagosAplicados = pagosDetallados.reduce((sum, d) => sum + d.valor, 0);
                
                const valorNetoActualizado = originalValor - totalPagosAplicados;
                
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

                $valoresCell.find('.prestamo-deducciones').attr('data-toggle', 'tooltip').attr('data-html', 'true').attr('title', pagosTooltipHtml).tooltip();
            });
        }

        $(document).ready(function() {
            var prestamosTable = $('#prestamosTable').DataTable({
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
                const valorOriginal = parseFloat($(this).val()) || 0;
                $('#valor_neto').val(valorOriginal);
            });

            $('#filtro-empresa').on('change', function() {
                empresaIdSeleccionada = $(this).val();
                empresaSeleccionada = $(this).find('option:selected').text();
                
                if (empresaIdSeleccionada === 'todas') {
                    prestamosTable.columns(5).search('').draw();
                } else {
                    prestamosTable.columns(5).search('^' + empresaSeleccionada + '$', true, false).draw();
                }

                actualizarVisualizacionPagos();
            });

            cargarDatosPagos();

            $('#user_id').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE UN USUARIO',
                allowClear: true,
                width: '100%'
            });

            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
                $(this).find('select').val('').trigger('change');
            });

            if (window.SucursalCache) {
                SucursalCache.preseleccionarEnSelect('filtro-empresa');
                $('#filtro-empresa').trigger('change');
            }
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
            
            $('#pago_prestamo_id').val(prestamoId);
            $('#pago_nombre_usuario').text(nombreUsuario);
            $('#pago_saldo_pendiente').text(formatCurrency(saldoPendienteActual));
            $('#pago_valor_cuota').text(formatCurrency(valorCuotaActual));
            
            // Establecer el valor del pago como el valor de la cuota, limitado al saldo pendiente
            const valorPagoSugerido = Math.min(valorCuotaActual, saldoPendienteActual);
            $('#pago_valor').attr('max', saldoPendienteActual).val(valorPagoSugerido);
            $('#pago_motivo').val('ABONO A PRÉSTAMO - CUOTA');
            
            // Si está seleccionada una empresa específica, usarla
            if (empresaIdSeleccionada !== 'todas') {
                $('#pago_empresa_id').val(empresaIdSeleccionada);
            } else {
                // Obtener el ID de la empresa del préstamo
                const empresaId = $fila.data('empresa-id');
                $('#pago_empresa_id').val(empresaId);
            }
        }
    </script>
@stop 