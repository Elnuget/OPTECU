@extends('adminlte::page')

@section('title', 'ROL DE PAGOS')

@section('content_header')
    <h1>ROL DE PAGOS</h1>
    <p>ADMINISTRACIÓN DE ROLES DE PAGO</p>
    @include('components.sueldos.alerts')
@stop

@section('content')
    @php
        $empresa = \App\Models\Empresa::first();
        $tipoSucursal = $empresa ? $empresa->getTipoSucursal() : 'todas';
        $users = \App\Models\User::orderBy('name')->get();
    @endphp

    @include('components.sueldos.styles')
    @include('components.sueldos.filters', ['tipoSucursal' => $tipoSucursal])
    
    <div class="card">
        <div class="card-body">
            @foreach($users as $user)
                @include('components.sueldos.user-rol', [
                    'user' => $user,
                    'tipoSucursal' => $tipoSucursal
                ])
            @endforeach
        </div>
    </div>
@stop

@section('js')
    @include('atajos')
    @include('components.sueldos.scripts.init')
    @include('components.sueldos.scripts.funciones')
    @include('components.sueldos.scripts.api')
@stop 