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
            <x-inventario.search-filters :fecha="request('fecha')" />
            <x-inventario.toolbar />
            <x-inventario.table :inventario="$inventario" />
        </div>
    </div>
@stop

@section('js')
    @include('atajos')
    <x-inventario.scripts />
@stop

@section('js')
    @include('atajos')
    <x-inventario.scripts />
@stop
