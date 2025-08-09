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
                    <span class="info-box-text">PAGOS DE PRÉSTAMOS - MES ACTUAL</span>
                    <span class="info-box-number" id="summary-pagos-mes-actual">CARGANDO...</span>
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
            <h3 class="card-title">PAGOS DE PRÉSTAMOS - MES ACTUAL: <span id="summary-pagos-mes-actual" class="badge badge-warning">$0.00</span></h3>
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
                            <th>EMPRESA</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>USUARIO</th>
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
                            <th>VALOR ORIGINAL</th>
                            <th>VALOR NETO</th>
                            <th>DEDUCCIONES</th>
                            <th>CUOTAS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prestamos as $prestamo)
                            <tr data-prestamo-id="{{ $prestamo->id }}" data-original-valor="{{ $prestamo->valor }}" data-valor-neto="{{ $prestamo->valor_neto }}" data-cuotas="{{ $prestamo->cuotas }}">
                                <td>{{ $prestamo->created_at->format('Y-m-d') }}</td>
                                <td class="prestamo-usuario">{{ $prestamo->user->name }}</td>
                                <td class="prestamo-motivo">{{ $prestamo->motivo }}</td>
                                <td class="prestamo-valor-original">${{ number_format($prestamo->valor, 2, ',', '.') }}</td>
                                <td class="prestamo-valor-neto">${{ number_format($prestamo->valor_neto, 2, ',', '.') }}</td>
                                <td class="prestamo-deducciones">-</td>
                                <td class="prestamo-cuotas">{{ $prestamo->cuotas }}</td>
                                <td>
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-info mx-1 shadow" 
                                        title="Ver"
                                        onclick="window.location.href='{{ route('prestamos.show', $prestamo->id) }}'">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
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
            
            // Actualizar el resumen
            const totalPagosFiltrados = pagosFiltrados.reduce((sum, pago) => sum + parseFloat(pago.valor), 0);
            const summarySpan = document.getElementById('summary-pagos-mes-actual');
            summarySpan.textContent = formatCurrency(totalPagosFiltrados);

            // Actualizar la tabla de detalles
            const desgloseBody = document.getElementById('desglose-pagos-prestamos');
            
            if (pagosFiltrados.length > 0) {
                desgloseBody.innerHTML = pagosFiltrados.map(pago => `
                    <tr>
                        <td>${pago.fecha}</td>
                        <td>${pago.empresa}</td>
                        <td>${pago.motivo}</td>
                        <td class="text-danger">${formatCurrency(pago.valor)}</td>
                        <td>${pago.usuario}</td>
                    </tr>
                `).join('');
            } else {
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY PAGOS DE PRÉSTAMOS PARA LA EMPRESA SELECCIONADA.</td></tr>';
            }

            // Actualizar los valores netos y deducciones
            actualizarValoresNetosPrestamos();
        }

        // Genera una lista de meses/años desde Enero 2025 hasta la fecha actual


        // Función combinada para cargar todos los datos de pagos
        async function cargarDatosPagos() {
            const summarySpan = document.getElementById('summary-pagos-mes-actual');
            const desgloseBody = document.getElementById('desglose-pagos-prestamos');
            const loadingOverlay = document.getElementById('loading-overlay-pagos');

            // Mostrar estado de carga
            summarySpan.textContent = 'CARGANDO...';
            desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';
            loadingOverlay.style.display = 'flex';

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

                // Procesar pagos con empresa
                todosLosPagos = todosLosPagos.map(pago => ({
                    ...pago,
                    empresa: pago.empresa || 'N/A',
                    valorAbs: parseFloat(pago.valor || 0)
                }));

                // Guardar datos globalmente
                detallesPagosGlobal = todosLosPagos;
                pagosCargados = true;

                // Actualizar resumen
                const totalPagos = data.total_pagos || 0;
                summarySpan.textContent = formatCurrency(totalPagos);

                // Actualizar visualización con filtros aplicados
                actualizarVisualizacionPagos();

            } catch (error) {
                console.error('Error al obtener pagos de préstamos:', error);
                summarySpan.textContent = 'ERROR AL CARGAR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS DE PAGOS.</td></tr>';
            } finally {
                loadingOverlay.style.display = 'none';
            }
        }

        // Función auxiliar para normalizar texto (simple: minúsculas, sin acentos)
        function normalizarTexto(texto) {
            if (!texto) return '';
            return texto.toLowerCase()
                .replace(/[áäâà]/g, 'a')
                .replace(/[éëêè]/g, 'e')
                .replace(/[íïîì]/g, 'i')
                .replace(/[óöôò]/g, 'o')
                .replace(/[úüûù]/g, 'u');
        }

        // Función auxiliar para obtener palabras clave significativas
        function obtenerPalabrasClave(texto) {
            const textoNormalizado = normalizarTexto(texto);
            // Lista simple de stopwords (palabras comunes a ignorar)
            const stopwords = ['de', 'para', 'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'con', 'por', 'en', 'a', 'y', 'o', 'q', 'que', 'del', 'al', 'mi', 'su'];
            return textoNormalizado
                .split(/[^a-z0-9]+/) // Dividir por cualquier cosa que no sea letra o número
                .filter(palabra => palabra.length > 3 && !stopwords.includes(palabra) && isNaN(palabra)); // Ignorar stopwords, palabras cortas y números puros
        }

        // Función para calcular y actualizar los valores netos en la tabla de préstamos
        function actualizarValoresNetosPrestamos() {
            const prestamosTable = $('#prestamosTable').DataTable(); // Obtener instancia de DataTable

            prestamosTable.rows().every(function() {
                const rowNode = this.node();
                const rowData = this.data(); // Obtener datos de la fila si DataTable los tiene cacheados
                const $rowNode = $(rowNode);

                const originalValor = parseFloat($rowNode.data('original-valor'));
                const valorNetoBD = parseFloat($rowNode.data('valor-neto')) || originalValor;
                const cuotasBD = parseInt($rowNode.data('cuotas')) || 0;
                
                const usuarioNombre = $rowNode.find('td.prestamo-usuario').text().trim();
                const usuarioNombreNormalizado = normalizarTexto(usuarioNombre);
                const motivoPrestamoOriginalText = $rowNode.find('td.prestamo-motivo').text().trim();
                const palabrasClavePrestamo = obtenerPalabrasClave(motivoPrestamoOriginalText);

                const $valorNetoCell = $rowNode.find('td.prestamo-valor-neto');
                const $deduccionesCell = $rowNode.find('td.prestamo-deducciones');
                const $cuotasCell = $rowNode.find('td.prestamo-cuotas');

                // Filtrar pagos por empresa seleccionada
                const pagosFiltrados = empresaIdSeleccionada === 'todas' 
                    ? detallesPagosGlobal 
                    : detallesPagosGlobal.filter(pago => pago.empresa_id == empresaIdSeleccionada);

                let totalPagos = 0;

                let totalPagos = 0;
                let pagosDetallados = [];

                // Buscar pagos relacionados con este préstamo
                const prestamoId = $rowNode.data('prestamo-id');
                const pagosRelacionados = pagosFiltrados.filter(pago => {
                    // Si el pago tiene prestamo_id, verificar coincidencia directa
                    if (pago.prestamo_id) {
                        return pago.prestamo_id == prestamoId;
                    }
                    
                    // Si no, usar lógica de texto (compatibilidad con datos antiguos)
                    const motivoPagoNormalizado = normalizarTexto(pago.motivo);
                    
                    // Opción 1: Contiene nombre de usuario?
                    if (usuarioNombreNormalizado.length > 0 && motivoPagoNormalizado.includes(usuarioNombreNormalizado)) {
                        return true;
                    }
                    
                    // Opción 2: Comparte palabra clave?
                    if (palabrasClavePrestamo.length > 0) {
                        const palabrasClavePago = obtenerPalabrasClave(pago.motivo);
                        return palabrasClavePrestamo.some(p => palabrasClavePago.includes(p));
                    }
                    
                    return false;
                });

                // Procesar pagos relacionados
                pagosRelacionados.forEach(pago => {
                    pagosDetallados.push({
                        fecha: pago.fecha,
                        tipo: 'Pago',
                        valor: parseFloat(pago.valor),
                        motivo: pago.motivo,
                        empresa: pago.empresa,
                        usuario: pago.usuario
                    });
                });

                // Calcular total de pagos
                totalPagos = pagosDetallados.reduce((sum, d) => sum + d.valor, 0);
                
                // Calcular valor neto actualizado (valor neto original - pagos)
                const valorNetoActualizado = valorNetoOriginal - totalPagos;
                
                // Crear contenido detallado para la celda de valor neto
                let valorNetoHtml = `
                    <div class="d-flex flex-column">
                        <div><strong>NETO ORIGINAL:</strong> ${formatCurrency(valorNetoOriginal)}</div>
                        <div><strong>PAGOS${empresaIdSeleccionada !== 'todas' ? ' (' + empresaSeleccionada + ')' : ''}:</strong> ${formatCurrency(totalPagos)}</div>
                        <div class="border-top pt-1 mt-1">
                            <strong>SALDO PENDIENTE:</strong> ${formatCurrency(valorNetoActualizado)}
                        </div>
                    </div>
                `;
                
                $valorNetoCell.html(valorNetoHtml);
                
                const cuotasTotal = cuotasBD;
                
                // Cuotas pagadas = número de pagos según el filtro
                const cuotasPagadas = pagosDetallados.length;
                
                // Cuotas pendientes
                const cuotasPendientes = Math.max(0, cuotasTotal - cuotasPagadas);

                // Actualizar celda de cuotas con información detallada
                let cuotasHtml = '';
                if (cuotasTotal > 0) {
                    cuotasHtml = `
                        <div class="d-flex flex-column">
                            <div><strong>CUOTAS TOTALES:</strong> ${cuotasTotal}</div>
                            <div><strong>PAGADAS${empresaIdSeleccionada !== 'todas' ? ' (' + empresaSeleccionada + ')' : ''}:</strong> ${cuotasPagadas} de ${cuotasTotal}</div>
                            <div><strong>PENDIENTES:</strong> ${cuotasPendientes}</div>
                            <div class="progress mt-1" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                    style="width: ${Math.min(100, (cuotasPagadas/cuotasTotal)*100)}%;" 
                                    aria-valuenow="${cuotasPagadas}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="${cuotasTotal}">
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    cuotasHtml = '<span class="text-muted">SIN CUOTAS DEFINIDAS</span>';
                }
                $cuotasCell.html(cuotasHtml);

                // Construir lista HTML para pagos
                let pagosHtml = '<span class="text-muted">NINGÚN PAGO</span>';
                if (pagosDetallados.length > 0) {
                    // Ordenar pagos por fecha para mostrarlas cronológicamente
                    pagosDetallados.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

                    pagosHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.8em;">';
                    pagosDetallados.forEach(d => {
                        pagosHtml += `
                            <li class="mb-1">
                                <div><strong>${d.fecha}</strong>: ${formatCurrency(d.valor)}</div>
                                <div class="text-muted" style="font-size: 0.9em;">
                                    Empresa: ${d.empresa}<br>
                                    Motivo: ${d.motivo}<br>
                                    Usuario: ${d.usuario}
                                </div>
                            </li>`;
                    });
                    // Agregar línea separadora y total
                    pagosHtml += `
                        <li class="mt-2 pt-2 border-top">
                            <strong>TOTAL PAGOS${empresaIdSeleccionada !== 'todas' ? ' (' + empresaSeleccionada + ')' : ''}: ${formatCurrency(totalPagos)}</strong>
                        </li>
                    </ul>`;
                }

                $deduccionesCell.html(pagosHtml);
                $deduccionesCell.tooltip('dispose');
            });
        }

        $(document).ready(function() {
            // Inicializar DataTable
            var prestamosTable = $('#prestamosTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,
                "language": {
                    "url": "{{ asset('js/datatables/Spanish.json') }}"
                }
            });

            // Calcular valor neto al cambiar el valor original en el modal
            $('#valor').on('input', function() {
                const valorOriginal = parseFloat($(this).val()) || 0;
                $('#valor_neto').val(valorOriginal);
            });

            // Evento para el cambio de empresa
            $('#filtro-empresa').on('change', function() {
                empresaIdSeleccionada = $(this).val();
                empresaSeleccionada = $(this).find('option:selected').text();
                actualizarVisualizacionPagos();
            });

            // Cargar datos de pagos
            cargarDatosPagos();

            // Inicializar select2 para los combobox de usuarios
            $('#user_id').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE UN USUARIO',
                allowClear: true,
                width: '100%'
            });

            // Limpiar los formularios cuando se cierren los modales
            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
                $(this).find('select').val('').trigger('change');
            });
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
    </script>
@stop 