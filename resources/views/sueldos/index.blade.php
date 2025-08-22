@extends('adminlte::page')

@section('title', 'SUELDOS')

@section('content_header')
    <h1>SUELDOS</h1>
    
    @include('sueldos.components.rol-pago-filters')
    
    <p>ADMINISTRACIÓN DE SUELDOS</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong>{{ session('mensaje') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ session('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('content')
    @include('sueldos.components.styles')

    @include('sueldos.components.tabla-sueldos')

    @include('sueldos.components.modal-confirmar-eliminar')
    @include('sueldos.components.modal-confirmar-eliminar-detalle')
@stop

@section('js')
@include('sueldos.components.scripts')
@stop
