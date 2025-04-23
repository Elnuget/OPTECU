@extends('adminlte::page')

@section('title', 'FINANZAS')

@section('content_header')
    <h1>FINANZAS</h1>
    <p>ADMINISTRACIÓN DE FINANZAS</p>
@stop

@section('content')
    @php
        $empresa = \App\Models\Empresa::first();
        $tipoSucursal = $empresa ? $empresa->getTipoSucursal() : 'todas';
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

            {{-- Tarjeta de Clasificación de Retiros --}}
            <div class="card card-outline card-danger mb-4" id="card-clasificacion-retiros">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        CLASIFICACIÓN DE RETIROS
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>POR SUCURSAL</h5>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-clasificacion-retiros-matriz">
                                    Matriz: $0
                                </div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-clasificacion-retiros-rocio">
                                    Rocío: $0
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-clasificacion-retiros-norte">
                                    Norte: $0
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>POR MOTIVO</h5>
                            <div class="row" id="desglose-clasificacion-retiros">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-spinner fa-spin"></i> CARGANDO DATOS...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Clasificación de Egresos --}}
            <div class="card card-outline card-purple mb-4" id="card-clasificacion-egresos">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        CLASIFICACIÓN DE EGRESOS
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>POR SUCURSAL</h5>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-clasificacion-egresos-matriz">
                                    Matriz: $0
                                </div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-clasificacion-egresos-rocio">
                                    Rocío: $0
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-clasificacion-egresos-norte">
                                    Norte: $0
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>POR MOTIVO</h5>
                            <div class="row" id="desglose-clasificacion-egresos">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-spinner fa-spin"></i> CARGANDO DATOS...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
        // Variables globales para almacenar los totales y el tipo de sucursal
        let totalMatriz = 0;
        let totalRocio = 0;
        let totalNorte = 0;
        let totalRetirosMatriz = 0;
        let totalRetirosRocio = 0;
        let totalRetirosNorte = 0;
        let totalEgresosMatriz = 0;
        let totalEgresosRocio = 0;
        let totalEgresosNorte = 0;
        const tipoSucursal = '{{ $tipoSucursal }}';

        $(document).ready(function() {
            const filtroAno = document.getElementById('filtroAno');
            const filtroMes = document.getElementById('filtroMes');
            const filtroSucursal = document.getElementById('filtroSucursal');

            // Si no es 'todas', establecer y bloquear el filtro de sucursal
            if (tipoSucursal !== 'todas') {
                filtroSucursal.value = tipoSucursal;
                filtroSucursal.disabled = true;
                
                // Ocultar las tarjetas que no corresponden a la sucursal
                const allCards = {
                    'matriz': ['card-ingresos-matriz', 'card-retiros-matriz', 'card-egresos-matriz'],
                    'rocio': ['card-ingresos-rocio', 'card-retiros-rocio', 'card-egresos-rocio'],
                    'norte': ['card-ingresos-norte', 'card-retiros-norte', 'card-egresos-norte']
                };

                Object.entries(allCards).forEach(([sucursal, cards]) => {
                    cards.forEach(cardId => {
                        const card = document.getElementById(cardId);
                        if (card) {
                            card.style.display = sucursal === tipoSucursal ? 'block' : 'none';
                        }
                    });
                });

                // Ocultar las barras de progreso de otras sucursales
                if (tipoSucursal !== 'todas') {
                    document.getElementById('card-ingresos-total').style.display = 'none';
                    document.getElementById('card-retiros-total').style.display = 'none';
                    document.getElementById('card-egresos-total').style.display = 'none';
                }
            }

            // Función para actualizar todas las tarjetas
            function updateAllCards(ano, mes) {
                const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : filtroSucursal.value;
                
                if (sucursal === '' || sucursal === 'matriz' || tipoSucursal === 'todas') {
                    fetchAndDisplayIngresosMatriz(ano, mes);
                    fetchAndDisplayRetirosMatriz(ano, mes);
                    fetchAndDisplayEgresosMatriz(ano, mes);
                }
                if (sucursal === '' || sucursal === 'rocio' || tipoSucursal === 'todas') {
                    fetchAndDisplayIngresosRocio(ano, mes);
                    fetchAndDisplayRetirosRocio(ano, mes);
                    fetchAndDisplayEgresosRocio(ano, mes);
                }
                if (sucursal === '' || sucursal === 'norte' || tipoSucursal === 'todas') {
                    fetchAndDisplayIngresosNorte(ano, mes);
                    fetchAndDisplayRetirosNorte(ano, mes);
                    fetchAndDisplayEgresosNorte(ano, mes);
                }

                toggleSucursalCards(sucursal);
                
                // Actualizar todas las clasificaciones cada vez que se cambie la sucursal
                setTimeout(() => {
                    actualizarClasificacionRetiros();
                    actualizarClasificacionEgresos();
                }, 1000); // Dar tiempo a que se carguen los datos
            }

            // Modificar la función toggleSucursalCards para respetar el tipo de sucursal global
            function toggleSucursalCards(sucursal) {
                const allCards = {
                    'matriz': ['card-ingresos-matriz', 'card-retiros-matriz', 'card-egresos-matriz'],
                    'rocio': ['card-ingresos-rocio', 'card-retiros-rocio', 'card-egresos-rocio'],
                    'norte': ['card-ingresos-norte', 'card-retiros-norte', 'card-egresos-norte']
                };

                if (tipoSucursal !== 'todas') {
                    // Si hay una sucursal específica configurada, solo mostrar sus tarjetas
                    Object.entries(allCards).forEach(([currentSucursal, cards]) => {
                        cards.forEach(cardId => {
                            const card = document.getElementById(cardId);
                            if (card) {
                                card.style.display = currentSucursal === tipoSucursal ? 'block' : 'none';
                            }
                        });
                    });

                    // Ocultar las tarjetas de totales globales
                    document.getElementById('card-ingresos-total').style.display = 'none';
                    document.getElementById('card-retiros-total').style.display = 'none';
                    document.getElementById('card-egresos-total').style.display = 'none';
                } else {
                    // Comportamiento normal para MATRIZ o sin empresa configurada
                    if (sucursal === '') {
                        Object.values(allCards).flat().forEach(cardId => {
                            document.getElementById(cardId).style.display = 'block';
                        });
                        document.getElementById('card-ingresos-total').style.display = 'block';
                        document.getElementById('card-retiros-total').style.display = 'block';
                        document.getElementById('card-egresos-total').style.display = 'block';
                    } else {
                        Object.entries(allCards).forEach(([currentSucursal, cards]) => {
                            cards.forEach(cardId => {
                                document.getElementById(cardId).style.display = currentSucursal === sucursal ? 'block' : 'none';
                            });
                        });
                        document.getElementById('card-ingresos-total').style.display = 'none';
                        document.getElementById('card-retiros-total').style.display = 'none';
                        document.getElementById('card-egresos-total').style.display = 'none';
                    }
                }
            }

            // Carga inicial de datos
            updateAllCards(filtroAno.value, filtroMes.value);

            // Event listeners
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

            document.getElementById('actualButton').addEventListener('click', function() {
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;

                filtroAno.value = currentYear;
                filtroMes.value = currentMonth;
                if (tipoSucursal === 'todas') {
                    filtroSucursal.value = '';
                }

                updateAllCards(currentYear, currentMonth);
            });
        });

        // Modificar las funciones de actualización para respetar el tipo de sucursal
        function updateGlobalTotal() {
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            let totalGlobal = 0;

            if (tipoSucursal !== 'todas') {
                // Si hay una sucursal específica configurada, solo mostrar sus totales
                if (tipoSucursal === 'matriz') totalGlobal = totalMatriz;
                else if (tipoSucursal === 'rocio') totalGlobal = totalRocio;
                else if (tipoSucursal === 'norte') totalGlobal = totalNorte;
            } else {
                // Comportamiento normal para MATRIZ o sin empresa configurada
                if (sucursal === '') {
                    totalGlobal = totalMatriz + totalRocio + totalNorte;
                } else if (sucursal === 'matriz') {
                    totalGlobal = totalMatriz;
                } else if (sucursal === 'rocio') {
                    totalGlobal = totalRocio;
                } else if (sucursal === 'norte') {
                    totalGlobal = totalNorte;
                }
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
                    updateClasificacionRetiros();
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
                    updateClasificacionRetiros();
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
                    updateClasificacionRetiros();
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
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            let totalGlobal = 0;

            if (tipoSucursal !== 'todas') {
                // Si hay una sucursal específica configurada, solo mostrar sus totales
                if (tipoSucursal === 'matriz') totalGlobal = Math.abs(totalRetirosMatriz);
                else if (tipoSucursal === 'rocio') totalGlobal = Math.abs(totalRetirosRocio);
                else if (tipoSucursal === 'norte') totalGlobal = Math.abs(totalRetirosNorte);
            } else {
                // Comportamiento normal para MATRIZ o sin empresa configurada
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
                    // Filtrar los retiros que no son depósitos para el cálculo del total
                    const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                        const motivo = retiro.motivo.toLowerCase();
                        return !motivo.includes('deposito') && !motivo.includes('depósito');
                    }) : [];
                    
                    // Calcular el total solo con los retiros filtrados
                    const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                    
                    totalRetirosMatriz = totalFiltrado; // Usar el total filtrado sin depósitos
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
                    updateClasificacionRetiros();
                    // Ya no llamamos a updateClasificacionRetirosPorMotivo aquí,
                    // se hará centralizadamente desde actualizarClasificacionRetiros
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
                    // Filtrar los retiros que no son depósitos para el cálculo del total
                    const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                        const motivo = retiro.motivo.toLowerCase();
                        return !motivo.includes('deposito') && !motivo.includes('depósito');
                    }) : [];
                    
                    // Calcular el total solo con los retiros filtrados
                    const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                    
                    totalRetirosRocio = totalFiltrado; // Usar el total filtrado sin depósitos
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
                    updateClasificacionRetiros();
                    // Ya no llamamos a updateClasificacionRetirosPorMotivo aquí,
                    // se hará centralizadamente desde actualizarClasificacionRetiros
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
                    // Filtrar los retiros que no son depósitos para el cálculo del total
                    const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                        const motivo = retiro.motivo.toLowerCase();
                        return !motivo.includes('deposito') && !motivo.includes('depósito');
                    }) : [];
                    
                    // Calcular el total solo con los retiros filtrados
                    const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                    
                    totalRetirosNorte = totalFiltrado; // Usar el total filtrado sin depósitos
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
                    updateClasificacionRetiros();
                    // Ya no llamamos a updateClasificacionRetirosPorMotivo aquí,
                    // se hará centralizadamente desde actualizarClasificacionRetiros
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
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            let ingresosGlobal = 0;
            let retirosGlobal = 0;
            let egresosGlobal = 0;

            if (tipoSucursal !== 'todas') {
                // Si hay una sucursal específica configurada, solo mostrar sus totales
                if (tipoSucursal === 'matriz') ingresosGlobal = totalMatriz;
                else if (tipoSucursal === 'rocio') ingresosGlobal = totalRocio;
                else if (tipoSucursal === 'norte') ingresosGlobal = totalNorte;

                if (tipoSucursal === 'matriz') retirosGlobal = Math.abs(totalRetirosMatriz);
                else if (tipoSucursal === 'rocio') retirosGlobal = Math.abs(totalRetirosRocio);
                else if (tipoSucursal === 'norte') retirosGlobal = Math.abs(totalRetirosNorte);

                if (tipoSucursal === 'matriz') egresosGlobal = Math.abs(totalEgresosMatriz);
                else if (tipoSucursal === 'rocio') egresosGlobal = Math.abs(totalEgresosRocio);
                else if (tipoSucursal === 'norte') egresosGlobal = Math.abs(totalEgresosNorte);
            } else {
                // Comportamiento normal para MATRIZ o sin empresa configurada
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
            }

            const gananciaNeta = ingresosGlobal - retirosGlobal - egresosGlobal;
            const summarySpan = document.getElementById('summary-ganancia-neta');
            summarySpan.textContent = formatCurrency(gananciaNeta);
        }

        // Función para actualizar el total global de egresos y la barra de progreso
        function updateGlobalEgresos() {
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            let totalGlobal = 0;

            if (tipoSucursal !== 'todas') {
                // Si hay una sucursal específica configurada, solo mostrar sus totales
                if (tipoSucursal === 'matriz') totalGlobal = Math.abs(totalEgresosMatriz);
                else if (tipoSucursal === 'rocio') totalGlobal = Math.abs(totalEgresosRocio);
                else if (tipoSucursal === 'norte') totalGlobal = Math.abs(totalEgresosNorte);
            } else {
                // Comportamiento normal para MATRIZ o sin empresa configurada
                if (sucursal === '') {
                    totalGlobal = Math.abs(totalEgresosMatriz) + Math.abs(totalEgresosRocio) + Math.abs(totalEgresosNorte);
                } else if (sucursal === 'matriz') {
                    totalGlobal = Math.abs(totalEgresosMatriz);
                } else if (sucursal === 'rocio') {
                    totalGlobal = Math.abs(totalEgresosRocio);
                } else if (sucursal === 'norte') {
                    totalGlobal = Math.abs(totalEgresosNorte);
                }
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
                    updateClasificacionEgresos();
                    // Ya no llamamos a updateClasificacionEgresosPorMotivo aquí,
                    // se hará centralizadamente desde actualizarClasificacionEgresos
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
                    updateClasificacionEgresos();
                    // Ya no llamamos a updateClasificacionEgresosPorMotivo aquí,
                    // se hará centralizadamente desde actualizarClasificacionEgresos
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
                    updateClasificacionEgresos();
                    // Ya no llamamos a updateClasificacionEgresosPorMotivo aquí,
                    // se hará centralizadamente desde actualizarClasificacionEgresos
                })
                .catch(error => {
                    console.error('Error:', error);
                    totalSpan.textContent = 'ERROR';
                    desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                    loadingOverlay.style.display = 'none';
                });
        }

        // Función para actualizar la clasificación de retiros
        function updateClasificacionRetiros() {
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            let totalGlobal = Math.abs(totalRetirosMatriz) + Math.abs(totalRetirosRocio) + Math.abs(totalRetirosNorte);

            if (totalGlobal > 0) {
                const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? Math.abs(totalRetirosMatriz) : 0) / totalGlobal) * 100;
                const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? Math.abs(totalRetirosRocio) : 0) / totalGlobal) * 100;
                const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? Math.abs(totalRetirosNorte) : 0) / totalGlobal) * 100;

                const progressMatriz = document.getElementById('progress-clasificacion-retiros-matriz');
                const progressRocio = document.getElementById('progress-clasificacion-retiros-rocio');
                const progressNorte = document.getElementById('progress-clasificacion-retiros-norte');

                progressMatriz.style.width = porcentajeMatriz + '%';
                progressRocio.style.width = porcentajeRocio + '%';
                progressNorte.style.width = porcentajeNorte + '%';

                progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalRetirosMatriz : 0)}`;
                progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalRetirosRocio : 0)}`;
                progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalRetirosNorte : 0)}`;
            }
        }

        // Función para actualizar la clasificación de egresos
        function updateClasificacionEgresos() {
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            let totalGlobal = Math.abs(totalEgresosMatriz) + Math.abs(totalEgresosRocio) + Math.abs(totalEgresosNorte);

            if (totalGlobal > 0) {
                const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? Math.abs(totalEgresosMatriz) : 0) / totalGlobal) * 100;
                const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? Math.abs(totalEgresosRocio) : 0) / totalGlobal) * 100;
                const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? Math.abs(totalEgresosNorte) : 0) / totalGlobal) * 100;

                const progressMatriz = document.getElementById('progress-clasificacion-egresos-matriz');
                const progressRocio = document.getElementById('progress-clasificacion-egresos-rocio');
                const progressNorte = document.getElementById('progress-clasificacion-egresos-norte');

                progressMatriz.style.width = porcentajeMatriz + '%';
                progressRocio.style.width = porcentajeRocio + '%';
                progressNorte.style.width = porcentajeNorte + '%';

                progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalEgresosMatriz : 0)}`;
                progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalEgresosRocio : 0)}`;
                progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalEgresosNorte : 0)}`;
            }
        }

        // Función para clasificar los motivos de retiros
        function clasificarMotivoRetiro(motivo) {
            motivo = motivo.toLowerCase();
            
            if (motivo.includes('deposito') || motivo.includes('depósito')) {
                return 'DEPÓSITOS';
            } else if (motivo.includes('luz') || motivo.includes('servicios') || motivo.includes('internet')) {
                return 'SERVICIOS';
            } else if (motivo.includes('almuerzo') || motivo.includes('ceviche') || motivo.includes('heladeria') || 
                      motivo.includes('gaseosa') || motivo.includes('alimentacion') || motivo.includes('hogar alimentacion')) {
                return 'ALIMENTACIÓN';
            } else if (motivo.includes('papel') || motivo.includes('caramelos') || motivo.includes('fruta')) {
                return 'SUMINISTROS';
            } else if (motivo.includes('bisel') || motivo.includes('importvision') || motivo.includes('santa fe')) {
                return 'INSUMOS ÓPTICOS';
            } else if (motivo.includes('parqueadero') || motivo.includes('pasaje') || motivo.includes('gasolina')) {
                return 'TRANSPORTE';
            } else if (motivo.includes('suelda') || motivo.includes('sueldo') || motivo.includes('abraham')) {
                return 'PAGOS';
            } else if (motivo.includes('prestamo') || motivo.includes('tarjeta')) {
                return 'PRÉSTAMOS';
            } else {
                return 'OTROS';
            }
        }

        // Función para obtener el icono según la categoría
        function getIconoPorCategoria(categoria) {
            const iconos = {
                'DEPÓSITOS': 'fa-money-bill-wave',
                'SERVICIOS': 'fa-bolt',
                'ALIMENTACIÓN': 'fa-utensils',
                'SUMINISTROS': 'fa-box',
                'INSUMOS ÓPTICOS': 'fa-glasses',
                'TRANSPORTE': 'fa-car',
                'PAGOS': 'fa-hand-holding-usd',
                'PAGOS DE PERSONAL': 'fa-user-tie',
                'PRÉSTAMOS': 'fa-credit-card',
                'PRÉSTAMOS Y TARJETAS': 'fa-credit-card',
                'FAMILIA': 'fa-users',
                'OTROS': 'fa-ellipsis-h'
            };
            return iconos[categoria] || 'fa-ellipsis-h';
        }

        // Función para actualizar la clasificación de retiros por motivo
        function updateClasificacionRetirosPorMotivo(retiros) {
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            const clasificacion = {
                'DEPÓSITOS': 0,
                'SERVICIOS': 0,
                'ALIMENTACIÓN': 0,
                'SUMINISTROS': 0,
                'INSUMOS ÓPTICOS': 0,
                'TRANSPORTE': 0,
                'PAGOS': 0,
                'OTROS': 0
            };

            // Filtrar retiros según la sucursal seleccionada y excluir depósitos del cálculo del total
            let retirosAMostrar = retiros.filter(retiro => {
                if (sucursal === '') return true;
                return retiro.sucursal === sucursal;
            });

            // Para la visualización mostraremos todos, pero marcaremos los depósitos
            retirosAMostrar.forEach(retiro => {
                const categoria = clasificarMotivoRetiro(retiro.motivo);
                clasificacion[categoria] += Math.abs(parseFloat(retiro.valor));
            });

            const desgloseContainer = document.getElementById('desglose-clasificacion-retiros');
            desgloseContainer.innerHTML = '';

            let categoriasConDatos = 0;
            let totalRetiros = 0;

            // Calcular el total según la sucursal seleccionada (ya excluyendo depósitos)
            if (sucursal === '') {
                totalRetiros = Math.abs(totalRetirosMatriz) + Math.abs(totalRetirosRocio) + Math.abs(totalRetirosNorte);
            } else if (sucursal === 'matriz') {
                totalRetiros = Math.abs(totalRetirosMatriz);
            } else if (sucursal === 'rocio') {
                totalRetiros = Math.abs(totalRetirosRocio);
            } else if (sucursal === 'norte') {
                totalRetiros = Math.abs(totalRetirosNorte);
            }

            // Ordenar las categorías por monto de mayor a menor
            const categoriasOrdenadas = Object.entries(clasificacion)
                .sort(([, a], [, b]) => b - a);

            // Crear un objeto para almacenar los detalles por categoría
            const detallesPorCategoria = {};
            retirosAMostrar.forEach(retiro => {
                const categoria = clasificarMotivoRetiro(retiro.motivo);
                if (!detallesPorCategoria[categoria]) {
                    detallesPorCategoria[categoria] = [];
                }
                detallesPorCategoria[categoria].push({
                    motivo: retiro.motivo,
                    valor: Math.abs(parseFloat(retiro.valor)),
                    esDeposito: retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito')
                });
            });

            categoriasOrdenadas.forEach(([categoria, total]) => {
                if (total > 0) {
                    // Para los depósitos, usar un estilo especial
                    const esCategoriaDeDEpositos = categoria === 'DEPÓSITOS';
                    
                    categoriasConDatos++;
                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-3';

                    // Crear el contenido de los detalles
                    let detallesHTML = '';
                    if (detallesPorCategoria[categoria]) {
                        const detallesOrdenados = detallesPorCategoria[categoria]
                            .sort((a, b) => b.valor - a.valor)
                            .slice(0, 3); // Mostrar solo los 3 más altos

                        detallesHTML = `
                            <div class="mt-2 small">
                                ${detallesOrdenados.map(detalle => `
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>${detalle.motivo.substring(0, 20)}${detalle.motivo.length > 20 ? '...' : ''}</span>
                                        <span>${formatCurrency(detalle.valor)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    }

                    col.innerHTML = `
                        <div class="card h-100 ${esCategoriaDeDEpositos ? 'border-info' : ''}">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas ${getIconoPorCategoria(categoria)} fa-2x ${esCategoriaDeDEpositos ? 'text-info' : 'text-danger'}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">${categoria} ${esCategoriaDeDEpositos ? '<span class="badge badge-info">NO CONTABILIZADO</span>' : ''}</h6>
                                        <small class="text-muted">${formatCurrency(total)}</small>
                                        <div class="progress mt-2" style="height: 5px;">
                                            <div class="progress-bar ${esCategoriaDeDEpositos ? 'bg-info' : 'bg-danger'}" role="progressbar" 
                                                style="width: ${esCategoriaDeDEpositos ? '100' : (total / totalRetiros) * 100}%" 
                                                aria-valuenow="${esCategoriaDeDEpositos ? '100' : (total / totalRetiros) * 100}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        ${detallesHTML}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    desgloseContainer.appendChild(col);
                }
            });

            // Agregar tarjeta de total (ya excluyendo depósitos)
            const totalCol = document.createElement('div');
            totalCol.className = 'col-12 mt-3';
            totalCol.innerHTML = `
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0">TOTAL RETIROS ${sucursal ? `- ${sucursal.toUpperCase()}` : ''}</h5>
                                <small>${categoriasConDatos} categorías (DEPÓSITOS NO INCLUIDOS EN EL TOTAL)</small>
                            </div>
                            <div class="text-right">
                                <h4 class="mb-0">${formatCurrency(totalRetiros)}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            desgloseContainer.appendChild(totalCol);

            // Actualizar visibilidad de la tarjeta
            const cardRetiros = document.getElementById('card-clasificacion-retiros');
            if (tipoSucursal !== 'todas') {
                cardRetiros.style.display = tipoSucursal === sucursal ? 'block' : 'none';
            } else {
                cardRetiros.style.display = 'block';
            }
        }

        // Función para clasificar los motivos de egresos
        function clasificarMotivoEgreso(motivo) {
            motivo = motivo.toLowerCase();
            
            // Verificar si el motivo comienza con un nombre de persona
            if (/^(abraham|wendy|rogger|carlos|maría|jose|juan|ana|luis|pedro|jorge|laura|sofia)/i.test(motivo)) {
                // Si está junto a "pago" o "sueldo", es pago de personal
                if (motivo.includes("pago") || motivo.includes("sueldo")) {
                    return 'PAGOS DE PERSONAL';
                }
                // De lo contrario, es familia
                return 'FAMILIA';
            } else if (motivo.includes('deposito') || motivo.includes('depósito')) {
                return 'DEPÓSITOS';
            } else if (motivo.includes('luz') || motivo.includes('servicios') || motivo.includes('internet')) {
                return 'SERVICIOS';
            } else if (motivo.includes('almuerzo') || motivo.includes('ceviche') || 
                      motivo.includes('heladeria') || motivo.includes('gaseosa') || 
                      motivo.includes('alimentacion') || motivo.includes('hogar alimentacion')) {
                return 'ALIMENTACIÓN';
            } else if (motivo.includes('papel') || motivo.includes('caramelos') || 
                      motivo.includes('fruta') || motivo.includes('suministros')) {
                return 'SUMINISTROS';
            } else if (motivo.includes('bisel') || motivo.includes('importvision') || 
                      motivo.includes('santa fe') || motivo.includes('médicos') || 
                      motivo.includes('medicos') || motivo.includes('ópticos')) {
                return 'INSUMOS ÓPTICOS';
            } else if (motivo.includes('parqueadero') || motivo.includes('pasaje') || 
                      motivo.includes('gasolina')) {
                return 'TRANSPORTE';
            } else if (motivo.includes('suelda') || motivo.includes('sueldo') || 
                      motivo.includes('pago')) {
                return 'PAGOS DE PERSONAL';
            } else if (motivo.includes('prestamo') || motivo.includes('tarjeta de credito') || 
                      motivo.includes('tarjeta madre') || motivo.includes('tarjeta wendy')) {
                return 'PRÉSTAMOS Y TARJETAS';
            } else {
                return 'OTROS';
            }
        }

        // Función para actualizar la clasificación de egresos por motivo
        function updateClasificacionEgresosPorMotivo(egresos) {
            const sucursal = tipoSucursal !== 'todas' ? tipoSucursal : document.getElementById('filtroSucursal').value;
            const clasificacion = {
                'DEPÓSITOS': 0,
                'SERVICIOS': 0,
                'ALIMENTACIÓN': 0,
                'SUMINISTROS': 0,
                'INSUMOS ÓPTICOS': 0,
                'TRANSPORTE': 0,
                'PAGOS DE PERSONAL': 0,
                'PRÉSTAMOS Y TARJETAS': 0,
                'FAMILIA': 0,
                'OTROS': 0
            };

            // Filtrar egresos según la sucursal seleccionada
            const egresosAMostrar = egresos.filter(egreso => {
                if (sucursal === '') return true;
                return egreso.sucursal === sucursal;
            });

            egresosAMostrar.forEach(egreso => {
                const categoria = clasificarMotivoEgreso(egreso.motivo);
                clasificacion[categoria] += Math.abs(parseFloat(egreso.valor));
            });

            const desgloseContainer = document.getElementById('desglose-clasificacion-egresos');
            desgloseContainer.innerHTML = '';

            let categoriasConDatos = 0;
            let totalEgresos = 0;

            // Calcular el total según la sucursal seleccionada
            if (sucursal === '') {
                totalEgresos = Math.abs(totalEgresosMatriz) + Math.abs(totalEgresosRocio) + Math.abs(totalEgresosNorte);
            } else if (sucursal === 'matriz') {
                totalEgresos = Math.abs(totalEgresosMatriz);
            } else if (sucursal === 'rocio') {
                totalEgresos = Math.abs(totalEgresosRocio);
            } else if (sucursal === 'norte') {
                totalEgresos = Math.abs(totalEgresosNorte);
            }

            // Ordenar las categorías por monto de mayor a menor
            const categoriasOrdenadas = Object.entries(clasificacion)
                .sort(([, a], [, b]) => b - a);

            // Crear un objeto para almacenar los detalles por categoría
            const detallesPorCategoria = {};
            egresosAMostrar.forEach(egreso => {
                const categoria = clasificarMotivoEgreso(egreso.motivo);
                if (!detallesPorCategoria[categoria]) {
                    detallesPorCategoria[categoria] = [];
                }
                detallesPorCategoria[categoria].push({
                    motivo: egreso.motivo,
                    valor: Math.abs(parseFloat(egreso.valor))
                });
            });

            categoriasOrdenadas.forEach(([categoria, total]) => {
                if (total > 0) {
                    categoriasConDatos++;
                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-3';

                    // Crear el contenido de los detalles
                    let detallesHTML = '';
                    if (detallesPorCategoria[categoria]) {
                        const detallesOrdenados = detallesPorCategoria[categoria]
                            .sort((a, b) => b.valor - a.valor)
                            .slice(0, 3); // Mostrar solo los 3 más altos

                        detallesHTML = `
                            <div class="mt-2 small">
                                ${detallesOrdenados.map(detalle => `
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>${detalle.motivo.substring(0, 20)}${detalle.motivo.length > 20 ? '...' : ''}</span>
                                        <span>${formatCurrency(detalle.valor)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    }

                    col.innerHTML = `
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas ${getIconoPorCategoria(categoria)} fa-2x text-purple"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">${categoria}</h6>
                                        <small class="text-muted">${formatCurrency(total)}</small>
                                        <div class="progress mt-2" style="height: 5px;">
                                            <div class="progress-bar bg-purple" role="progressbar" 
                                                style="width: ${(total / totalEgresos) * 100}%" 
                                                aria-valuenow="${(total / totalEgresos) * 100}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        ${detallesHTML}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    desgloseContainer.appendChild(col);
                }
            });

            // Agregar tarjeta de total
            const totalCol = document.createElement('div');
            totalCol.className = 'col-12 mt-3';
            totalCol.innerHTML = `
                <div class="card bg-purple text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0">TOTAL EGRESOS ${sucursal ? `- ${sucursal.toUpperCase()}` : ''}</h5>
                                <small>${categoriasConDatos} categorías</small>
                            </div>
                            <div class="text-right">
                                <h4 class="mb-0">${formatCurrency(totalEgresos)}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            desgloseContainer.appendChild(totalCol);

            // Actualizar visibilidad de la tarjeta
            const cardEgresos = document.getElementById('card-clasificacion-egresos');
            if (tipoSucursal !== 'todas') {
                cardEgresos.style.display = tipoSucursal === sucursal ? 'block' : 'none';
            } else {
                cardEgresos.style.display = 'block';
            }
        }

        // Nueva función para actualizar clasificación de retiros con datos de todas las APIs
        function actualizarClasificacionRetiros() {
            const ano = document.getElementById('filtroAno').value;
            const mes = document.getElementById('filtroMes').value;
            
            // Mostrar mensaje de carga en el contenedor de clasificación de retiros
            const desgloseContainer = document.getElementById('desglose-clasificacion-retiros');
            desgloseContainer.innerHTML = `
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-spinner fa-spin"></i> CARGANDO DATOS DE CLASIFICACIÓN...
                        </div>
                    </div>
                </div>
            `;
            
            // Obtener datos de retiros de todas las sucursales
            Promise.all([
                fetch(`https://opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`).then(r => r.json()).catch(() => ({ retiros: [] })),
                fetch(`https://escleroptica2.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`).then(r => r.json()).catch(() => ({ retiros: [] })),
                fetch(`https://sucursal3.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`).then(r => r.json()).catch(() => ({ retiros: [] }))
            ]).then(([dataMatriz, dataRocio, dataNorte]) => {
                // Agregar la información de sucursal a cada retiro
                const retirosMatriz = dataMatriz.retiros ? dataMatriz.retiros.map(r => ({...r, sucursal: 'matriz'})) : [];
                const retirosRocio = dataRocio.retiros ? dataRocio.retiros.map(r => ({...r, sucursal: 'rocio'})) : [];
                const retirosNorte = dataNorte.retiros ? dataNorte.retiros.map(r => ({...r, sucursal: 'norte'})) : [];
                
                // Combinar todos los retiros
                const todosLosRetiros = [...retirosMatriz, ...retirosRocio, ...retirosNorte];
                
                // Actualizar clasificación por motivo
                updateClasificacionRetirosPorMotivo(todosLosRetiros);
            }).catch(error => {
                console.error('Error al obtener datos de retiros:', error);
                desgloseContainer.innerHTML = `
                    <div class="col-12">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle"></i> ERROR AL CARGAR LOS DATOS DE CLASIFICACIÓN
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // Nueva función para actualizar clasificación de egresos con datos de todas las APIs
        function actualizarClasificacionEgresos() {
            const ano = document.getElementById('filtroAno').value;
            const mes = document.getElementById('filtroMes').value;
            
            // Mostrar mensaje de carga en el contenedor de clasificación de egresos
            const desgloseContainer = document.getElementById('desglose-clasificacion-egresos');
            desgloseContainer.innerHTML = `
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-spinner fa-spin"></i> CARGANDO DATOS DE CLASIFICACIÓN...
                        </div>
                    </div>
                </div>
            `;
            
            // Obtener datos de egresos de todas las sucursales
            Promise.all([
                fetch(`https://opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`).then(r => r.json()).catch(() => ({ egresos: [] })),
                fetch(`https://escleroptica2.opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`).then(r => r.json()).catch(() => ({ egresos: [] })),
                fetch(`https://sucursal3.opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`).then(r => r.json()).catch(() => ({ egresos: [] }))
            ]).then(([dataMatriz, dataRocio, dataNorte]) => {
                // Agregar la información de sucursal a cada egreso
                const egresosMatriz = dataMatriz.egresos ? dataMatriz.egresos.map(e => ({...e, sucursal: 'matriz'})) : [];
                const egresosRocio = dataRocio.egresos ? dataRocio.egresos.map(e => ({...e, sucursal: 'rocio'})) : [];
                const egresosNorte = dataNorte.egresos ? dataNorte.egresos.map(e => ({...e, sucursal: 'norte'})) : [];
                
                // Combinar todos los egresos
                const todosLosEgresos = [...egresosMatriz, ...egresosRocio, ...egresosNorte];
                
                // Actualizar clasificación por motivo usando la función específica para egresos
                updateClasificacionEgresosPorMotivo(todosLosEgresos);
            }).catch(error => {
                console.error('Error al obtener datos de egresos:', error);
                desgloseContainer.innerHTML = `
                    <div class="col-12">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle"></i> ERROR AL CARGAR LOS DATOS DE CLASIFICACIÓN
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    </script>
@stop 