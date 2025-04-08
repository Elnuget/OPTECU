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
                <div class="col-md-2">
                    <label for="filtroAno">SELECCIONAR AÑO:</label>
                    <select name="ano" class="form-control custom-select" id="filtroAno">
                        <option value="">SELECCIONE AÑO</option>
                        @for ($year = date('Y'); $year >= 2000; $year--)
                            <option value="{{ $year }}" {{ request('ano', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
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
                <div class="col-md-2 align-self-end">
                    <button type="button" class="btn btn-primary" id="actualButton">ACTUAL</button>
                </div>
            </form>

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

        // Función para actualizar el total global y la barra de progreso
        function updateGlobalTotal() {
            const totalGlobal = totalMatriz + totalRocio + totalNorte;
            const totalSpan = document.getElementById('total-ingresos-global');
            totalSpan.textContent = formatCurrency(totalGlobal);

            // Calcular porcentajes para la barra de progreso
            if (totalGlobal > 0) {
                const porcentajeMatriz = (totalMatriz / totalGlobal) * 100;
                const porcentajeRocio = (totalRocio / totalGlobal) * 100;
                const porcentajeNorte = (totalNorte / totalGlobal) * 100;

                // Actualizar barras de progreso
                const progressMatriz = document.getElementById('progress-matriz');
                const progressRocio = document.getElementById('progress-rocio');
                const progressNorte = document.getElementById('progress-norte');

                progressMatriz.style.width = porcentajeMatriz + '%';
                progressRocio.style.width = porcentajeRocio + '%';
                progressNorte.style.width = porcentajeNorte + '%';

                progressMatriz.textContent = `Matriz: ${formatCurrency(totalMatriz)}`;
                progressRocio.textContent = `Rocío: ${formatCurrency(totalRocio)}`;
                progressNorte.textContent = `Norte: ${formatCurrency(totalNorte)}`;
            }
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

        $(document).ready(function() {
            const filtroAno = document.getElementById('filtroAno');
            const filtroMes = document.getElementById('filtroMes');

            // Función para actualizar todas las tarjetas
            function updateAllCards(ano, mes) {
                fetchAndDisplayIngresosMatriz(ano, mes);
                fetchAndDisplayIngresosRocio(ano, mes);
                fetchAndDisplayIngresosNorte(ano, mes);
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

            // Event listener para el botón "Actual"
            document.getElementById('actualButton').addEventListener('click', function() {
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;

                filtroAno.value = currentYear;
                filtroMes.value = currentMonth;

                updateAllCards(currentYear, currentMonth);
            });
        });
    </script>
@stop 