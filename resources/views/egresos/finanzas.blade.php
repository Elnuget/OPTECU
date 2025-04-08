@extends('adminlte::page')

@section('title', 'FINANZAS')

@section('content_header')
    <h1>FINANZAS</h1>
    <p>ADMINISTRACIÓN DE FINANZAS</p>
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

    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-dollar-sign mr-2"></i>
                INFORMACIÓN FINANCIERA
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" class="form-row mb-3" id="filterForm">
                <div class="col-md-3">
                    <label for="filtroAno">SELECCIONAR AÑO:</label>
                    <select name="ano" class="form-control custom-select" id="filtroAno">
                        <option value="">SELECCIONE AÑO</option>
                        @for ($year = date('Y'); $year >= 2000; $year--)
                            <option value="{{ $year }}" {{ request('ano', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroMes">SELECCIONAR MES:</label>
                    <select name="mes" class="form-control custom-select" id="filtroMes">
                        <option value="">SELECCIONE MES</option>
                        @foreach (['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ request('mes', date('n')) == ($index + 1) ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroSucursal">SELECCIONAR SUCURSAL:</label>
                    <select name="sucursal" class="form-control custom-select" id="filtroSucursal">
                        <option value="">TODAS LAS SUCURSALES</option>
                        <option value="matriz">MATRIZ</option>
                        <option value="rocio">ROCÍO</option>
                        <option value="norte">NORTE</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary btn-block" id="actualButton">ACTUAL</button>
                </div>
            </form>

            {{-- Tarjetas de Resumen --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ingresos Totales</span>
                            <span class="info-box-number" id="summary-ingresos-global">CARGANDO...</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Retiros Totales</span>
                            <span class="info-box-number" id="summary-retiros-global">CARGANDO...</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-purple">
                        <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" id="summary-egresos-global-text">Egresos Totales</span>
                            <span class="info-box-number" id="summary-egresos-global">CARGANDO...</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ganancia Neta</span>
                            <span class="info-box-number" id="summary-ganancia-neta">CARGANDO...</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Fin Tarjetas de Resumen --}}

            {{-- Tarjeta de Ingresos Totales --}}
            <div class="card card-outline card-danger mb-4" id="card-ingresos-total">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        INGRESOS TOTALES DE TODAS LAS SUCURSALES: 
                        <span id="total-ingresos-global">CARGANDO...</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-matriz">
                            Matriz: $0
                        </div>
                        <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-rocio">
                            Rocío: $0
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-norte">
                            Norte: $0
                        </div>
                    </div>
                </div>
            </div>
            {{-- Fin Tarjeta de Ingresos Totales --}}

            {{-- Tarjeta Plegable Ingresos Matriz --}}
            <div class="card card-outline card-success card-widget collapsed-card" id="card-ingresos-matriz">
                <div class="card-header">
                    <h3 class="card-title">INGRESOS SUCURSAL MATRIZ - TOTAL: <span id="total-ingresos-matriz">CARGANDO...</span></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;"> 
                    <h5>DESGLOSE POR MEDIO DE PAGO:</h5>
                    <ul class="list-group" id="desglose-ingresos-matriz">
                        <li class="list-group-item">CARGANDO DATOS...</li>
                    </ul>
                </div>
                 <div class="overlay dark" id="loading-overlay-matriz" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>
            {{-- Fin Tarjeta Plegable --}}

            {{-- Tarjeta Plegable Ingresos Rocio --}}
            <div class="card card-outline card-info card-widget collapsed-card" id="card-ingresos-rocio">
                <div class="card-header">
                    <h3 class="card-title">INGRESOS SUCURSAL ROCIO - TOTAL: <span id="total-ingresos-rocio">CARGANDO...</span></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <h5>DESGLOSE POR MEDIO DE PAGO:</h5>
                    <ul class="list-group" id="desglose-ingresos-rocio">
                        <li class="list-group-item">CARGANDO DATOS...</li>
                    </ul>
                </div>
                 <div class="overlay dark" id="loading-overlay-rocio" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>
            {{-- Fin Tarjeta Plegable Rocio --}}

            {{-- Tarjeta Plegable Ingresos Norte --}}
            <div class="card card-outline card-warning card-widget collapsed-card" id="card-ingresos-norte">
                <div class="card-header">
                    <h3 class="card-title">INGRESOS SUCURSAL NORTE - TOTAL: <span id="total-ingresos-norte">CARGANDO...</span></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <h5>DESGLOSE POR MEDIO DE PAGO:</h5>
                    <ul class="list-group" id="desglose-ingresos-norte">
                        <li class="list-group-item">CARGANDO DATOS...</li>
                    </ul>
                </div>
                 <div class="overlay dark" id="loading-overlay-norte" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>
            {{-- Fin Tarjeta Plegable Norte --}}

            {{-- Tarjeta de Retiros Totales --}}
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

            {{-- Tarjeta de Egresos Totales --}}
            <div class="card card-outline card-purple mb-4" id="card-egresos-total">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-arrow-down mr-2"></i>
                        EGRESOS TOTALES DE TODAS LAS SUCURSALES: 
                        <span id="total-egresos-global">CARGANDO...</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-egresos-matriz">
                            Matriz: $0
                        </div>
                        <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-egresos-rocio">
                            Rocío: $0
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-egresos-norte">
                            Norte: $0
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta Plegable Egresos Matriz --}}
            <div class="card card-outline card-success card-widget collapsed-card" id="card-egresos-matriz">
                <div class="card-header">
                    <h3 class="card-title">EGRESOS SUCURSAL MATRIZ - TOTAL: <span id="total-egresos-matriz">CARGANDO...</span></h3>
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
                            <tbody id="desglose-egresos-matriz">
                                <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="overlay dark" id="loading-overlay-egresos-matriz" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>

            {{-- Tarjeta Plegable Egresos Rocío --}}
            <div class="card card-outline card-info card-widget collapsed-card" id="card-egresos-rocio">
                <div class="card-header">
                    <h3 class="card-title">EGRESOS SUCURSAL ROCÍO - TOTAL: <span id="total-egresos-rocio">CARGANDO...</span></h3>
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
                            <tbody id="desglose-egresos-rocio">
                                <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="overlay dark" id="loading-overlay-egresos-rocio" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>

            {{-- Tarjeta Plegable Egresos Norte --}}
            <div class="card card-outline card-warning card-widget collapsed-card" id="card-egresos-norte">
                <div class="card-header">
                    <h3 class="card-title">EGRESOS SUCURSAL NORTE - TOTAL: <span id="total-egresos-norte">CARGANDO...</span></h3>
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
                            <tbody id="desglose-egresos-norte">
                                <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="overlay dark" id="loading-overlay-egresos-norte" style="display: none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle mr-2"></i>
                AQUÍ SE MOSTRARÁN LAS FINANZAS Y ESTADÍSTICAS FINANCIERAS DE LA EMPRESA
            </div>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
    <script>
        // Variables globales para almacenar los totales
        let totalMatriz = 0;
        let totalRocio = 0;
        let totalNorte = 0;
        let totalRetirosMatriz = 0;
        let totalRetirosRocio = 0;
        let totalRetirosNorte = 0;
        let totalEgresosMatriz = 0;
        let totalEgresosRocio = 0;
        let totalEgresosNorte = 0;

        // Función para actualizar el total global y la barra de progreso
        function updateGlobalTotal() {
            const sucursal = document.getElementById('filtroSucursal').value;
            let totalGlobal = 0;

            if (sucursal === '') {
                totalGlobal = totalMatriz + totalRocio + totalNorte;
            } else if (sucursal === 'matriz') {
                totalGlobal = totalMatriz;
            } else if (sucursal === 'rocio') {
                totalGlobal = totalRocio;
            } else if (sucursal === 'norte') {
                totalGlobal = totalNorte;
            }

            const totalSpan = document.getElementById('total-ingresos-global');
            const summarySpan = document.getElementById('summary-ingresos-global');
            totalSpan.textContent = formatCurrency(totalGlobal);
            summarySpan.textContent = formatCurrency(totalGlobal);

            // Calcular porcentajes para la barra de progreso
            if (totalGlobal > 0) {
                const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? totalMatriz : 0) / totalGlobal) * 100;
                const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? totalRocio : 0) / totalGlobal) * 100;
                const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? totalNorte : 0) / totalGlobal) * 100;

                // Actualizar barras de progreso
                const progressMatriz = document.getElementById('progress-matriz');
                const progressRocio = document.getElementById('progress-rocio');
                const progressNorte = document.getElementById('progress-norte');

                progressMatriz.style.width = porcentajeMatriz + '%';
                progressRocio.style.width = porcentajeRocio + '%';
                progressNorte.style.width = porcentajeNorte + '%';

                progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalMatriz : 0)}`;
                progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalRocio : 0)}`;
                progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalNorte : 0)}`;
            }
            updateGananciaNeta();
        }

        // Función para formatear números como moneda
        function formatCurrency(number) {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
        }

        // Función para obtener y mostrar datos de la API Matriz
        function fetchAndDisplayIngresosMatriz(ano, mes) {
            const apiUrl = `https://opticas.xyz/api/pagos/totales?ano=${ano}&mes=${mes}`;
            const totalSpan = document.getElementById('total-ingresos-matriz');
            const desgloseList = document.getElementById('desglose-ingresos-matriz');
            const loadingOverlay = document.getElementById('loading-overlay-matriz');

            // Mostrar overlay de carga
            loadingOverlay.style.display = 'flex'; 
            totalSpan.textContent = 'CARGANDO...';
            desgloseList.innerHTML = '<li class="list-group-item">CARGANDO DATOS...</li>';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la red o respuesta no válida');
                    }
                    return response.json();
                })
                .then(data => {
                    totalMatriz = parseFloat(data.total_pagos) || 0;
                    // Actualizar el total en el encabezado
                    totalSpan.textContent = formatCurrency(totalMatriz);
                    updateGlobalTotal();

                    // Limpiar y llenar el desglose
                    desgloseList.innerHTML = ''; // Limpiar contenido anterior
                    if (data.desglose_por_medio && data.desglose_por_medio.length > 0) {
                        data.desglose_por_medio.forEach(item => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                            listItem.innerHTML = `
                                ${item.medio_de_pago.toUpperCase()}
                                <span class="badge badge-primary badge-pill">${formatCurrency(item.total || 0)}</span>
                            `;
                            desgloseList.appendChild(listItem);
                        });
                    } else {
                        desgloseList.innerHTML = '<li class="list-group-item">NO HAY DATOS DE DESGLOSE DISPONIBLES.</li>';
                    }
                    loadingOverlay.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error al obtener datos de la API:', error);
                    totalSpan.textContent = 'ERROR';
                    desgloseList.innerHTML = '<li class="list-group-item text-danger">ERROR AL CARGAR LOS DATOS.</li>';
                    loadingOverlay.style.display = 'none';
                });
        }

        // Función para obtener y mostrar datos de la API Rocío
        function fetchAndDisplayIngresosRocio(ano, mes) {
            const apiUrl = `https://escleroptica2.opticas.xyz/api/pagos/totales?ano=${ano}&mes=${mes}`;
            const totalSpan = document.getElementById('total-ingresos-rocio');
            const desgloseList = document.getElementById('desglose-ingresos-rocio');
            const loadingOverlay = document.getElementById('loading-overlay-rocio');

            // Mostrar overlay de carga
            loadingOverlay.style.display = 'flex';
            totalSpan.textContent = 'CARGANDO...';
            desgloseList.innerHTML = '<li class="list-group-item">CARGANDO DATOS...</li>';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la red o respuesta no válida');
                    }
                    return response.json();
                })
                .then(data => {
                    totalRocio = parseFloat(data.total_pagos) || 0;
                    // Actualizar el total en el encabezado
                    totalSpan.textContent = formatCurrency(totalRocio);
                    updateGlobalTotal();

                    // Limpiar y llenar el desglose
                    desgloseList.innerHTML = '';
                    if (data.desglose_por_medio && data.desglose_por_medio.length > 0) {
                        data.desglose_por_medio.forEach(item => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                            listItem.innerHTML = `
                                ${item.medio_de_pago.toUpperCase()}
                                <span class="badge badge-info badge-pill">${formatCurrency(item.total || 0)}</span>
                            `;
                            desgloseList.appendChild(listItem);
                        });
                    } else {
                        desgloseList.innerHTML = '<li class="list-group-item">NO HAY DATOS DE DESGLOSE DISPONIBLES.</li>';
                    }
                    loadingOverlay.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error al obtener datos de la API:', error);
                    totalSpan.textContent = 'ERROR';
                    desgloseList.innerHTML = '<li class="list-group-item text-danger">ERROR AL CARGAR LOS DATOS.</li>';
                    loadingOverlay.style.display = 'none';
                });
        }

        // Función para obtener y mostrar datos de la API Norte
        function fetchAndDisplayIngresosNorte(ano, mes) {
            const apiUrl = `https://sucursal3.opticas.xyz/api/pagos/totales?ano=${ano}&mes=${mes}`;
            const totalSpan = document.getElementById('total-ingresos-norte');
            const desgloseList = document.getElementById('desglose-ingresos-norte');
            const loadingOverlay = document.getElementById('loading-overlay-norte');

            // Mostrar overlay de carga
            loadingOverlay.style.display = 'flex';
            totalSpan.textContent = 'CARGANDO...';
            desgloseList.innerHTML = '<li class="list-group-item">CARGANDO DATOS...</li>';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la red o respuesta no válida');
                    }
                    return response.json();
                })
                .then(data => {
                    totalNorte = parseFloat(data.total_pagos) || 0;
                    // Actualizar el total en el encabezado
                    totalSpan.textContent = formatCurrency(totalNorte);
                    updateGlobalTotal();

                    // Limpiar y llenar el desglose
                    desgloseList.innerHTML = '';
                    if (data.desglose_por_medio && data.desglose_por_medio.length > 0) {
                        data.desglose_por_medio.forEach(item => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                            listItem.innerHTML = `
                                ${item.medio_de_pago.toUpperCase()}
                                <span class="badge badge-warning badge-pill">${formatCurrency(item.total || 0)}</span>
                            `;
                            desgloseList.appendChild(listItem);
                        });
                    } else {
                        desgloseList.innerHTML = '<li class="list-group-item">NO HAY DATOS DE DESGLOSE DISPONIBLES.</li>';
                    }
                    loadingOverlay.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error al obtener datos de la API:', error);
                    totalSpan.textContent = 'ERROR';
                    desgloseList.innerHTML = '<li class="list-group-item text-danger">ERROR AL CARGAR LOS DATOS.</li>';
                    loadingOverlay.style.display = 'none';
                });
        }

        // Función para actualizar el total global de retiros y la barra de progreso
        function updateGlobalRetiros() {
            const sucursal = document.getElementById('filtroSucursal').value;
            let totalGlobal = 0;

            if (sucursal === '') {
                totalGlobal = Math.abs(totalRetirosMatriz) + Math.abs(totalRetirosRocio) + Math.abs(totalRetirosNorte);
            } else if (sucursal === 'matriz') {
                totalGlobal = Math.abs(totalRetirosMatriz);
            } else if (sucursal === 'rocio') {
                totalGlobal = Math.abs(totalRetirosRocio);
            } else if (sucursal === 'norte') {
                totalGlobal = Math.abs(totalRetirosNorte);
            }

            const totalSpan = document.getElementById('total-retiros-global');
            const summarySpan = document.getElementById('summary-retiros-global');
            totalSpan.textContent = formatCurrency(-totalGlobal);
            summarySpan.textContent = formatCurrency(totalGlobal);

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
            updateGananciaNeta();
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
                    totalRetirosMatriz = parseFloat(data.retiro_total) || 0;
                    totalSpan.textContent = formatCurrency(totalRetirosMatriz);
                    updateGlobalRetiros();

                    if (data.retiros && data.retiros.length > 0) {
                        desgloseBody.innerHTML = data.retiros.map(retiro => `
                            <tr>
                                <td>${retiro.fecha}</td>
                                <td>${retiro.motivo}</td>
                                <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                <td>${retiro.usuario}</td>
                            </tr>
                        `).join('');
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
                    totalRetirosRocio = parseFloat(data.retiro_total) || 0;
                    totalSpan.textContent = formatCurrency(totalRetirosRocio);
                    updateGlobalRetiros();

                    if (data.retiros && data.retiros.length > 0) {
                        desgloseBody.innerHTML = data.retiros.map(retiro => `
                            <tr>
                                <td>${retiro.fecha}</td>
                                <td>${retiro.motivo}</td>
                                <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                <td>${retiro.usuario}</td>
                            </tr>
                        `).join('');
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
                    totalRetirosNorte = parseFloat(data.retiro_total) || 0;
                    totalSpan.textContent = formatCurrency(totalRetirosNorte);
                    updateGlobalRetiros();

                    if (data.retiros && data.retiros.length > 0) {
                        desgloseBody.innerHTML = data.retiros.map(retiro => `
                            <tr>
                                <td>${retiro.fecha}</td>
                                <td>${retiro.motivo}</td>
                                <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                <td>${retiro.usuario}</td>
                            </tr>
                        `).join('');
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

        // Función para calcular y mostrar la Ganancia Neta Global
        function updateGananciaNeta() {
            const sucursal = document.getElementById('filtroSucursal').value;
            let ingresosGlobal = 0;
            let retirosGlobal = 0;
            let egresosGlobal = 0;

            if (sucursal === '') {
                ingresosGlobal = totalMatriz + totalRocio + totalNorte;
                retirosGlobal = Math.abs(totalRetirosMatriz) + Math.abs(totalRetirosRocio) + Math.abs(totalRetirosNorte);
                egresosGlobal = Math.abs(totalEgresosMatriz) + Math.abs(totalEgresosRocio) + Math.abs(totalEgresosNorte);
            } else if (sucursal === 'matriz') {
                ingresosGlobal = totalMatriz;
                retirosGlobal = Math.abs(totalRetirosMatriz);
                egresosGlobal = Math.abs(totalEgresosMatriz);
            } else if (sucursal === 'rocio') {
                ingresosGlobal = totalRocio;
                retirosGlobal = Math.abs(totalRetirosRocio);
                egresosGlobal = Math.abs(totalEgresosRocio);
            } else if (sucursal === 'norte') {
                ingresosGlobal = totalNorte;
                retirosGlobal = Math.abs(totalRetirosNorte);
                egresosGlobal = Math.abs(totalEgresosNorte);
            }

            const gananciaNeta = ingresosGlobal - retirosGlobal - egresosGlobal;
            const summarySpan = document.getElementById('summary-ganancia-neta');
            summarySpan.textContent = formatCurrency(gananciaNeta);
        }

        // Función para actualizar el total global de egresos y la barra de progreso
        function updateGlobalEgresos() {
            const sucursal = document.getElementById('filtroSucursal').value;
            let totalGlobal = 0;

            if (sucursal === '') {
                totalGlobal = Math.abs(totalEgresosMatriz) + Math.abs(totalEgresosRocio) + Math.abs(totalEgresosNorte);
            } else if (sucursal === 'matriz') {
                totalGlobal = Math.abs(totalEgresosMatriz);
            } else if (sucursal === 'rocio') {
                totalGlobal = Math.abs(totalEgresosRocio);
            } else if (sucursal === 'norte') {
                totalGlobal = Math.abs(totalEgresosNorte);
            }

            const totalSpan = document.getElementById('total-egresos-global');
            const summarySpan = document.getElementById('summary-egresos-global');
            totalSpan.textContent = formatCurrency(-totalGlobal);
            summarySpan.textContent = formatCurrency(totalGlobal);

            if (totalGlobal > 0) {
                const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? Math.abs(totalEgresosMatriz) : 0) / totalGlobal) * 100;
                const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? Math.abs(totalEgresosRocio) : 0) / totalGlobal) * 100;
                const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? Math.abs(totalEgresosNorte) : 0) / totalGlobal) * 100;

                const progressMatriz = document.getElementById('progress-egresos-matriz');
                const progressRocio = document.getElementById('progress-egresos-rocio');
                const progressNorte = document.getElementById('progress-egresos-norte');

                progressMatriz.style.width = porcentajeMatriz + '%';
                progressRocio.style.width = porcentajeRocio + '%';
                progressNorte.style.width = porcentajeNorte + '%';

                progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalEgresosMatriz : 0)}`;
                progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalEgresosRocio : 0)}`;
                progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalEgresosNorte : 0)}`;
            }
            updateGananciaNeta();
        }

        // Función para obtener y mostrar datos de egresos de la API Matriz
        function fetchAndDisplayEgresosMatriz(ano, mes) {
            const apiUrl = `https://opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`;
            const totalSpan = document.getElementById('total-egresos-matriz');
            const desgloseBody = document.getElementById('desglose-egresos-matriz');
            const loadingOverlay = document.getElementById('loading-overlay-egresos-matriz');

            loadingOverlay.style.display = 'flex';
            totalSpan.textContent = 'CARGANDO...';
            desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                    return response.json();
                })
                .then(data => {
                    totalEgresosMatriz = parseFloat(data.total_egresos) || 0;
                    totalSpan.textContent = formatCurrency(totalEgresosMatriz);
                    updateGlobalEgresos();

                    if (data.egresos && data.egresos.length > 0) {
                        desgloseBody.innerHTML = data.egresos.map(egreso => `
                            <tr>
                                <td>${egreso.fecha}</td>
                                <td>${egreso.motivo}</td>
                                <td class="text-danger">${formatCurrency(egreso.valor)}</td>
                                <td>${egreso.usuario}</td>
                            </tr>
                        `).join('');
                    } else {
                        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY EGRESOS REGISTRADOS</td></tr>';
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

        // Función para obtener y mostrar datos de egresos de la API Rocío
        function fetchAndDisplayEgresosRocio(ano, mes) {
            const apiUrl = `https://escleroptica2.opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`;
            const totalSpan = document.getElementById('total-egresos-rocio');
            const desgloseBody = document.getElementById('desglose-egresos-rocio');
            const loadingOverlay = document.getElementById('loading-overlay-egresos-rocio');

            loadingOverlay.style.display = 'flex';
            totalSpan.textContent = 'CARGANDO...';
            desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                    return response.json();
                })
                .then(data => {
                    totalEgresosRocio = parseFloat(data.total_egresos) || 0;
                    totalSpan.textContent = formatCurrency(totalEgresosRocio);
                    updateGlobalEgresos();

                    if (data.egresos && data.egresos.length > 0) {
                        desgloseBody.innerHTML = data.egresos.map(egreso => `
                            <tr>
                                <td>${egreso.fecha}</td>
                                <td>${egreso.motivo}</td>
                                <td class="text-danger">${formatCurrency(egreso.valor)}</td>
                                <td>${egreso.usuario}</td>
                            </tr>
                        `).join('');
                    } else {
                        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY EGRESOS REGISTRADOS</td></tr>';
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

        // Función para obtener y mostrar datos de egresos de la API Norte
        function fetchAndDisplayEgresosNorte(ano, mes) {
            const apiUrl = `https://sucursal3.opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`;
            const totalSpan = document.getElementById('total-egresos-norte');
            const desgloseBody = document.getElementById('desglose-egresos-norte');
            const loadingOverlay = document.getElementById('loading-overlay-egresos-norte');

            loadingOverlay.style.display = 'flex';
            totalSpan.textContent = 'CARGANDO...';
            desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                    return response.json();
                })
                .then(data => {
                    totalEgresosNorte = parseFloat(data.total_egresos) || 0;
                    totalSpan.textContent = formatCurrency(totalEgresosNorte);
                    updateGlobalEgresos();

                    if (data.egresos && data.egresos.length > 0) {
                        desgloseBody.innerHTML = data.egresos.map(egreso => `
                            <tr>
                                <td>${egreso.fecha}</td>
                                <td>${egreso.motivo}</td>
                                <td class="text-danger">${formatCurrency(egreso.valor)}</td>
                                <td>${egreso.usuario}</td>
                            </tr>
                        `).join('');
                    } else {
                        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY EGRESOS REGISTRADOS</td></tr>';
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

        $(document).ready(function() {
            const filtroAno = document.getElementById('filtroAno');
            const filtroMes = document.getElementById('filtroMes');
            const filtroSucursal = document.getElementById('filtroSucursal');

            // Función para mostrar/ocultar tarjetas según la sucursal seleccionada
            function toggleSucursalCards(sucursal) {
                const allCards = {
                    'matriz': ['card-ingresos-matriz', 'card-retiros-matriz', 'card-egresos-matriz'],
                    'rocio': ['card-ingresos-rocio', 'card-retiros-rocio', 'card-egresos-rocio'],
                    'norte': ['card-ingresos-norte', 'card-retiros-norte', 'card-egresos-norte']
                };

                // Mostrar/ocultar tarjetas según la selección
                if (sucursal === '') {
                    // Mostrar todas las tarjetas y el resumen global
                    Object.values(allCards).flat().forEach(cardId => {
                        document.getElementById(cardId).style.display = 'block';
                    });
                    document.getElementById('card-ingresos-total').style.display = 'block';
                    document.getElementById('card-retiros-total').style.display = 'block';
                    document.getElementById('card-egresos-total').style.display = 'block';
                } else {
                    // Ocultar todas las tarjetas primero
                    Object.values(allCards).flat().forEach(cardId => {
                        document.getElementById(cardId).style.display = 'none';
                    });
                    // Mostrar solo las tarjetas de la sucursal seleccionada
                    allCards[sucursal].forEach(cardId => {
                        document.getElementById(cardId).style.display = 'block';
                    });
                    // Ocultar las tarjetas de resumen global
                    document.getElementById('card-ingresos-total').style.display = 'none';
                    document.getElementById('card-retiros-total').style.display = 'none';
                    document.getElementById('card-egresos-total').style.display = 'none';
                }
            }

            // Función para actualizar todas las tarjetas
            function updateAllCards(ano, mes) {
                const sucursal = filtroSucursal.value;
                
                if (sucursal === '' || sucursal === 'matriz') {
                    fetchAndDisplayIngresosMatriz(ano, mes);
                    fetchAndDisplayRetirosMatriz(ano, mes);
                    fetchAndDisplayEgresosMatriz(ano, mes);
                }
                if (sucursal === '' || sucursal === 'rocio') {
                    fetchAndDisplayIngresosRocio(ano, mes);
                    fetchAndDisplayRetirosRocio(ano, mes);
                    fetchAndDisplayEgresosRocio(ano, mes);
                }
                if (sucursal === '' || sucursal === 'norte') {
                    fetchAndDisplayIngresosNorte(ano, mes);
                    fetchAndDisplayRetirosNorte(ano, mes);
                    fetchAndDisplayEgresosNorte(ano, mes);
                }

                toggleSucursalCards(sucursal);
            }

            // Carga inicial de datos
            updateAllCards(filtroAno.value, filtroMes.value);

            // Event listener para cambio de año
            filtroAno.addEventListener('change', function() {
                updateAllCards(this.value, filtroMes.value);
            });

            // Event listener para cambio de mes
            filtroMes.addEventListener('change', function() {
                updateAllCards(filtroAno.value, this.value);
            });

            // Event listener para cambio de sucursal
            filtroSucursal.addEventListener('change', function() {
                updateAllCards(filtroAno.value, filtroMes.value);
            });

            // Event listener para el botón "Actual"
            document.getElementById('actualButton').addEventListener('click', function() {
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;

                filtroAno.value = currentYear;
                filtroMes.value = currentMonth;
                filtroSucursal.value = ''; // Resetear el filtro de sucursal

                updateAllCards(currentYear, currentMonth);
            });
        });
    </script>
@stop 