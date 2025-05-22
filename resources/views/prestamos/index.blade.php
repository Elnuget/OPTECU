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

    {{-- Tarjetas de Resumen de Egresos --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">EGRESOS (PRÉSTAMOS DESDE ENE 2025)</span>
                    <span class="info-box-number" id="summary-egresos-mes-actual">CARGANDO...</span>
                </div>
            </div>
        </div>
    </div>
    {{-- Fin Tarjetas de Resumen --}}

    {{-- Filtro de Sucursal --}}
    <div class="card card-outline card-purple mb-4">
        <div class="card-header">
            <h3 class="card-title">FILTROS</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtro-sucursal">FILTRAR POR SUCURSAL:</label>
                        <select class="form-control" id="filtro-sucursal">
                            <option value="TODAS">TODAS LAS SUCURSALES</option>
                            <option value="MATRIZ">MATRIZ</option>
                            <option value="ROCÍO">ROCÍO</option>
                            <option value="NORTE">NORTE</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Fin Filtro de Sucursal --}}

    {{-- Tarjeta Plegable Egresos Mes Actual --}}
    <div class="card card-outline card-purple card-widget collapsed-card" id="card-egresos-mes-actual">
        <div class="card-header">
            <h3 class="card-title">DETALLE EGRESOS (PRÉSTAMOS DESDE ENE 2025)</h3>
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
                            <th>SUCURSAL</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>USUARIO</th>
                        </tr>
                    </thead>
                    <tbody id="desglose-egresos-mes-actual">
                        <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="overlay dark" id="loading-overlay-egresos-mes" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>
    {{-- Fin Tarjeta Plegable Egresos Mes Actual --}}

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
        let detallesEgresosGlobal = [];
        let egresosCargados = false;
        let sucursalSeleccionada = 'TODAS';

        // Función para formatear números como moneda
        function formatCurrency(number) {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
        }

        // Función para filtrar egresos por sucursal
        function filtrarEgresosPorSucursal(egresos, sucursal) {
            if (sucursal === 'TODAS') {
                return egresos;
            }
            return egresos.filter(egreso => egreso.sucursal === sucursal);
        }

        // Función para actualizar la visualización de egresos
        function actualizarVisualizacionEgresos() {
            const egresosFiltrados = filtrarEgresosPorSucursal(detallesEgresosGlobal, sucursalSeleccionada);
            
            // Actualizar el resumen
            const totalEgresosFiltrados = egresosFiltrados.reduce((sum, egreso) => sum + egreso.valorAbs, 0);
            const summarySpan = document.getElementById('summary-egresos-mes-actual');
            summarySpan.textContent = formatCurrency(totalEgresosFiltrados);

            // Actualizar la tabla de detalles
            const desgloseBody = document.getElementById('desglose-egresos-mes-actual');
            
            if (egresosFiltrados.length > 0) {
                desgloseBody.innerHTML = egresosFiltrados.map(egreso => `
                    <tr>
                        <td>${egreso.fecha}</td>
                        <td>${egreso.sucursal}</td>
                        <td>${egreso.motivo}</td>
                        <td class="text-danger">${formatCurrency(egreso.valor)}</td>
                        <td>${egreso.usuario}</td>
                    </tr>
                `).join('');
            } else {
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY EGRESOS DE PRÉSTAMOS PARA LA SUCURSAL SELECCIONADA.</td></tr>';
            }

            // Actualizar los valores netos y deducciones
            actualizarValoresNetosPrestamos();
        }

        // Genera una lista de meses/años desde Enero 2025 hasta la fecha actual
        function getMonthsToFetch() {
            const months = [];
            const startYear = 2025;
            const startMonth = 1; // Enero
            const currentDate = new Date();
            const endYear = currentDate.getFullYear();
            const endMonth = currentDate.getMonth() + 1;

            for (let year = startYear; year <= endYear; year++) {
                const monthStart = (year === startYear) ? startMonth : 1;
                const monthEnd = (year === endYear) ? endMonth : 12;
                for (let month = monthStart; month <= monthEnd; month++) {
                    months.push({ year, month });
                }
            }
            // Asegurarse de que al menos el mes actual se incluya si estamos antes de 2025
            if (months.length === 0 && endYear < startYear) {
                 months.push({ year: endYear, month: endMonth });
            } else if (months.length === 0 && endYear === startYear && endMonth < startMonth) {
                 months.push({ year: endYear, month: endMonth });
            }
            return months;
        }

        // Función para obtener y sumar egresos (resumen filtrado por préstamo)
        async function fetchAndDisplayEgresosSummary(monthsToFetch) {
             const urls = [];
             const sucursales = [
                { domain: 'opticas.xyz', name: 'MATRIZ' },
                { domain: 'escleroptica2.opticas.xyz', name: 'ROCÍO' },
                { domain: 'sucursal3.opticas.xyz', name: 'NORTE' }
            ];

            monthsToFetch.forEach(({ year, month }) => {
                sucursales.forEach(suc => {
                    urls.push(`https://${suc.domain}/api/egresos?ano=${year}&mes=${month}`);
                });
            });

            const summarySpan = document.getElementById('summary-egresos-mes-actual');
            summarySpan.textContent = 'CARGANDO...';

             try {
                const results = await Promise.all(urls.map(url => fetch(url).then(resp => resp.ok ? resp.json() : {total_egresos: 0, egresos: []}).catch(() => ({ total_egresos: 0, egresos: [] }))));
                let totalEgresosPrestamo = 0;
                results.forEach(data => {
                    // Sumar solo los egresos cuyo motivo incluya "prestamo"
                    if(data.egresos && data.egresos.length > 0) {
                        const egresosFiltrados = data.egresos.filter(egreso =>
                            egreso.motivo.toLowerCase().includes('prestamo')
                        );
                        totalEgresosPrestamo += egresosFiltrados.reduce((sum, egreso) => sum + parseFloat(egreso.valor || 0), 0);
                    }
                });
                summarySpan.textContent = formatCurrency(totalEgresosPrestamo);
            } catch (error) {
                console.error('Error al obtener egresos (resumen préstamo):', error);
                summarySpan.textContent = 'ERROR';
            }
        }

        // Función para obtener y mostrar detalles de egresos (tabla filtrada por préstamo)
        async function fetchAndDisplayDetallesEgresos(monthsToFetch) {
             const urls = [];
             const sucursales = [
                { domain: 'opticas.xyz', name: 'MATRIZ' },
                { domain: 'escleroptica2.opticas.xyz', name: 'ROCÍO' },
                { domain: 'sucursal3.opticas.xyz', name: 'NORTE' }
            ];

             monthsToFetch.forEach(({ year, month }) => {
                sucursales.forEach(suc => {
                     urls.push({ url: `https://${suc.domain}/api/egresos?ano=${year}&mes=${month}`, sucursal: suc.name });
                });
            });

            const desgloseBody = document.getElementById('desglose-egresos-mes-actual');
            const loadingOverlay = document.getElementById('loading-overlay-egresos-mes');

            desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';
            loadingOverlay.style.display = 'flex';

            try {
                 const results = await Promise.all(urls.map(item => fetch(item.url)
                                            .then(resp => resp.ok ? resp.json() : {egresos: []})
                                            .then(data => ({ ...data, sucursal: item.sucursal }))
                                            .catch(() => ({ egresos: [], sucursal: item.sucursal }))));

                let todosLosEgresos = [];
                results.forEach(data => {
                    if (data.egresos && data.egresos.length > 0) {
                        const egresosConSucursal = data.egresos.map(egreso => ({
                            ...egreso,
                            sucursal: data.sucursal,
                            valorAbs: parseFloat(egreso.valor || 0)
                        }));
                        todosLosEgresos = todosLosEgresos.concat(egresosConSucursal);
                    }
                });

                // Filtrar por motivo que contenga "prestamo"
                const egresosFiltrados = todosLosEgresos.filter(egreso =>
                    egreso.motivo.toLowerCase().includes('prestamo')
                );

                detallesEgresosGlobal = egresosFiltrados;
                egresosCargados = true;
                actualizarVisualizacionEgresos();

                 // Ordenar por fecha descendente
                 egresosFiltrados.sort((a, b) => new Date(b.fecha + ' ' + (b.hora || '00:00:00')) - new Date(a.fecha + ' ' + (a.hora || '00:00:00')));

                if (egresosFiltrados.length > 0) {
                    desgloseBody.innerHTML = egresosFiltrados.map(egreso => `
                        <tr>
                            <td>${egreso.fecha}</td>
                            <td>${egreso.sucursal}</td>
                            <td>${egreso.motivo}</td>
                            <td class="text-danger">${formatCurrency(egreso.valor)}</td>
                            <td>${egreso.usuario}</td>
                        </tr>
                    `).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY EGRESOS DE PRÉSTAMOS REGISTRADOS DESDE ENE 2025.</td></tr>';
                }
            } catch (error) {
                console.error('Error al obtener detalles de egresos (préstamo):', error);
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DETALLES DE EGRESOS DE PRÉSTAMOS.</td></tr>';
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

                // Filtrar deducciones por sucursal seleccionada
                const egresosFiltrados = sucursalSeleccionada === 'TODAS' 
                    ? detallesEgresosGlobal 
                    : detallesEgresosGlobal.filter(egreso => egreso.sucursal === sucursalSeleccionada);

                let totalDeducciones = 0;
                let deduccionesDetalladas = [];

                // Función interna para verificar relación y guardar deducción
                const verificarYGuardarDeduccion = (item, tipo) => {
                    const motivoItemNormalizado = normalizarTexto(item.motivo);
                    let relacionado = false;

                    // Opción 1: Contiene nombre de usuario?
                    if (usuarioNombreNormalizado.length > 0 && motivoItemNormalizado.includes(usuarioNombreNormalizado)) {
                        relacionado = true;
                    }
                    // Opción 2: Comparte palabra clave?
                    if (!relacionado && palabrasClavePrestamo.length > 0) {
                        const palabrasClaveItem = obtenerPalabrasClave(item.motivo);
                        if (palabrasClavePrestamo.some(p => palabrasClaveItem.includes(p))) {
                            relacionado = true;
                        }
                    }

                    if (relacionado) {
                        deduccionesDetalladas.push({
                            fecha: item.fecha,
                            tipo: tipo,
                            valor: item.valorAbs,
                            motivo: item.motivo,
                            sucursal: item.sucursal
                        });
                    }
                };

                // Procesar Egresos filtrados
                egresosFiltrados.forEach(egreso => {
                    verificarYGuardarDeduccion(egreso, 'Egreso');
                });

                // Calcular total de deducciones
                totalDeducciones = deduccionesDetalladas.reduce((sum, d) => sum + d.valor, 0);
                
                // Obtener valor neto original de la base de datos
                const valorNetoOriginal = valorNetoBD;
                
                // Calcular valor neto actualizado (valor neto original - deducciones)
                const valorNetoActualizado = valorNetoOriginal - totalDeducciones;
                
                // Crear contenido detallado para la celda de valor neto
                let valorNetoHtml = `
                    <div class="d-flex flex-column">
                        <div><strong>NETO ORIGINAL:</strong> ${formatCurrency(valorNetoOriginal)}</div>
                        <div><strong>DEDUCCIONES${sucursalSeleccionada !== 'TODAS' ? ' (' + sucursalSeleccionada + ')' : ''}:</strong> ${formatCurrency(totalDeducciones)}</div>
                        <div class="border-top pt-1 mt-1">
                            <strong>NETO ACTUAL:</strong> ${formatCurrency(valorNetoActualizado)}
                        </div>
                    </div>
                `;
                
                $valorNetoCell.html(valorNetoHtml);
                
                const cuotasTotal = cuotasBD;
                
                // Cuotas pagadas = número de deducciones según el filtro
                const cuotasPagadas = deduccionesDetalladas.length;
                
                // Cuotas pendientes
                const cuotasPendientes = Math.max(0, cuotasTotal - cuotasPagadas);

                // Actualizar celda de cuotas con información detallada
                let cuotasHtml = '';
                if (cuotasTotal > 0) {
                    cuotasHtml = `
                        <div class="d-flex flex-column">
                            <div><strong>CUOTAS TOTALES:</strong> ${cuotasTotal}</div>
                            <div><strong>PAGADAS${sucursalSeleccionada !== 'TODAS' ? ' (' + sucursalSeleccionada + ')' : ''}:</strong> ${cuotasPagadas} de ${cuotasTotal}</div>
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

                // Construir lista HTML para deducciones
                let deduccionesHtml = '<span class="text-muted">NINGUNA</span>';
                if (deduccionesDetalladas.length > 0) {
                    // Ordenar deducciones por fecha para mostrarlas cronológicamente
                    deduccionesDetalladas.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

                    deduccionesHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.8em;">';
                    deduccionesDetalladas.forEach(d => {
                        deduccionesHtml += `
                            <li class="mb-1">
                                <div><strong>${d.fecha}</strong> (${d.tipo}): ${formatCurrency(d.valor)}</div>
                                <div class="text-muted" style="font-size: 0.9em;">
                                    Sucursal: ${d.sucursal}<br>
                                    Motivo: ${d.motivo}
                                </div>
                            </li>`;
                    });
                    // Agregar línea separadora y total
                    deduccionesHtml += `
                        <li class="mt-2 pt-2 border-top">
                            <strong>TOTAL DEDUCCIONES${sucursalSeleccionada !== 'TODAS' ? ' (' + sucursalSeleccionada + ')' : ''}: ${formatCurrency(totalDeducciones)}</strong>
                        </li>
                    </ul>`;
                }

                $deduccionesCell.html(deduccionesHtml);
                $deduccionesCell.tooltip('dispose');
            });
        }

        $(document).ready(function() {
            // Inicializar DataTable
            var prestamosTable = $('#prestamosTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            // Calcular valor neto al cambiar el valor original en el modal
            $('#valor').on('input', function() {
                const valorOriginal = parseFloat($(this).val()) || 0;
                $('#valor_neto').val(valorOriginal);
            });

            // Evento para el cambio de sucursal
            $('#filtro-sucursal').on('change', function() {
                sucursalSeleccionada = $(this).val();
                actualizarVisualizacionEgresos();
            });

            // Obtener lista de meses a consultar (desde Ene 2025 hasta actual)
            const monthsToFetch = getMonthsToFetch();

            // Cargar datos de egresos para el rango de fechas
            fetchAndDisplayEgresosSummary(monthsToFetch);
            fetchAndDisplayDetallesEgresos(monthsToFetch);

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