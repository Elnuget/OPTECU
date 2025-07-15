@extends('adminlte::page')

@section('title', 'DETALLE EGRESO')

@section('content_header')
    <h1>DETALLE DEL EGRESO</h1>
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
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>MOTIVO:</label>
                        <p>{{ $egreso->motivo }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>VALOR:</label>
                        <p>${{ number_format($egreso->valor, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>SUCURSAL:</label>
                        <p>{{ $egreso->empresa ? $egreso->empresa->nombre : 'SIN SUCURSAL' }}</p>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>CREADO POR:</label>
                        <p>{{ $egreso->user->name }}</p>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label>FECHA CREACIÓN:</label>
                        <p>{{ $egreso->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <a href="{{ route('egresos.index') }}" class="btn btn-secondary">VOLVER</a>
                    <a href="{{ route('egresos.edit', $egreso->id) }}" class="btn btn-primary">EDITAR</a>
                    <form action="{{ route('egresos.destroy', $egreso->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este egreso?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">ELIMINAR</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
@stop 