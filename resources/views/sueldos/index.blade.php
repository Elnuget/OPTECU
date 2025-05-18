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

            @include('components.sueldos.filtros', ['tipoSucursal' => $tipoSucursal, 'users' => $users])
            @include('components.sueldos.tabla', ['sueldos' => $sueldos])
        </div>
    </div>

    @include('components.sueldos.retiros', ['tipoSucursal' => $tipoSucursal])
    @include('components.sueldos.pedidos', ['tipoSucursal' => $tipoSucursal])
    @include('components.sueldos.historial', ['tipoSucursal' => $tipoSucursal])
    @include('components.sueldos.modales', ['users' => $users])

@stop

@section('js')
    @include('atajos')
    @push('js')
    <script>
        // Definir la variable tipoSucursal globalmente para que esté disponible en todos los scripts
        window.tipoSucursal = '{{ $tipoSucursal }}';
    </script>
    @endpush
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
@stop 