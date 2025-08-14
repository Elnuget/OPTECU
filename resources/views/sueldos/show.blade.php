@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('adminlte::page')

@section('title', 'VER SUELDO')

@section('content_header')
    <h1>DETALLE DEL SUELDO</h1>
@stop

@section('content')
    <style>
        /* Convertir todo el texto a mayúsculas */
        body, 
        .content-wrapper, 
        .main-header, 
        .main-sidebar, 
        .card-title,
        .btn,
        label,
        input,
        select,
        option,
        .form-control,
        p,
        h1, h2, h3, h4, h5, h6,
        th,
        td,
        span,
        a,
        .alert,
        .modal-title,
        .modal-body p,
        .card-header,
        .card-footer,
        button {
            text-transform: uppercase !important;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INFORMACIÓN DEL SUELDO</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>EMPLEADO:</strong></label>
                        <p class="form-control-plaintext">{{ $sueldo->user->name }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>EMPRESA:</strong></label>
                        <p class="form-control-plaintext">
                            {{ $sueldo->empresa ? $sueldo->empresa->nombre : 'SIN EMPRESA' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>FECHA:</strong></label>
                        <p class="form-control-plaintext">{{ $sueldo->fecha->format('d/m/Y') }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>VALOR:</strong></label>
                        <p class="form-control-plaintext">${{ number_format($sueldo->valor, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><strong>DESCRIPCIÓN:</strong></label>
                        <p class="form-control-plaintext">{{ $sueldo->descripcion }}</p>
                    </div>
                </div>
            </div>

            @if($sueldo->documento)
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><strong>DOCUMENTO ADJUNTO:</strong></label>
                        <div class="mt-2">
                            <a href="{{ Storage::url($sueldo->documento) }}" target="_blank" 
                               class="btn btn-primary">
                                <i class="fas fa-file"></i> VER DOCUMENTO
                            </a>
                            <a href="{{ Storage::url($sueldo->documento) }}" download 
                               class="btn btn-success ml-2">
                                <i class="fas fa-download"></i> DESCARGAR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>FECHA DE CREACIÓN:</strong></label>
                        <p class="form-control-plaintext">{{ $sueldo->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>ÚLTIMA ACTUALIZACIÓN:</strong></label>
                        <p class="form-control-plaintext">{{ $sueldo->updated_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            @can('admin')
            <a href="{{ route('sueldos.edit', $sueldo) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> EDITAR
            </a>
            @endcan
            <a href="{{ route('sueldos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> VOLVER A LA LISTA
            </a>
        </div>
    </div>
@stop
