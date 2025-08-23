@extends('adminlte::page')

@section('title', 'SUELDOS')

@section('content_header')
    <h1>SUELDOS</h1>
    
    @include('sueldos.components.rol-pago-filters')
    
    {{-- Solo mostrar el título de administración a los administradores --}}
    @if(Auth::user() && Auth::user()->is_admin)
        <p>ADMINISTRACIÓN DE SUELDOS</p>
    @else
        <p>CONSULTA DE ROL DE PAGO</p>
    @endif
    
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

    {{-- Solo mostrar la tabla de administración de sueldos a los administradores --}}
    @if(Auth::user() && Auth::user()->is_admin)
        @include('sueldos.components.tabla-sueldos')
    @endif

    @include('sueldos.components.modal-confirmar-eliminar')
    @include('sueldos.components.modal-confirmar-eliminar-detalle')
@stop

@section('js')
@include('sueldos.components.scripts')
@stop
