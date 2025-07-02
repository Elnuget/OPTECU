@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    @push('css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    @endpush

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    @endpush

    <h1>Inventario</h1>
    <p>Administración de Artículos</p>
    @if(!auth()->user()->is_admin && auth()->user()->empresa_id)
        @php
            $empresaUsuario = $empresas->where('id', auth()->user()->empresa_id)->first();
        @endphp
        @if($empresaUsuario)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Usted está viendo el inventario de su empresa: <strong>{{ $empresaUsuario->nombre }}</strong>
            </div>
        @endif
    @elseif(request('empresa_id'))
        @php
            $empresaSeleccionada = $empresas->where('id', request('empresa_id'))->first();
        @endphp
        @if($empresaSeleccionada)
            <div class="alert alert-info">
                <i class="fas fa-building"></i> Mostrando inventario de la empresa: <strong>{{ $empresaSeleccionada->nombre }}</strong>
            </div>
        @endif
    @endif
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong> {{ session('mensaje') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('content')
    <x-inventario.styles />
    
    <div class="card">
        <div class="card-body">
            <x-inventario.search-filters :fecha="request('fecha')" :empresas="$empresas" />
            <x-inventario.toolbar />
            <x-inventario.table :inventario="$inventario" />
        </div>
    </div>
@stop

@section('js')
    @include('atajos')
    <x-inventario.scripts />
@stop
