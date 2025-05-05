@extends('adminlte::page')

@section('title', 'VER PRÉSTAMO')

@section('content_header')
    <h1>DETALLES DEL PRÉSTAMO</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>USUARIO:</label>
                <p class="form-control">{{ $prestamo->user->name }}</p>
            </div>

            <div class="form-group">
                <label>VALOR:</label>
                <p class="form-control">${{ number_format($prestamo->valor, 2, ',', '.') }}</p>
            </div>

            <div class="form-group">
                <label>MOTIVO:</label>
                <p class="form-control">{{ $prestamo->motivo }}</p>
            </div>

            <div class="form-group">
                <label>FECHA DE CREACIÓN:</label>
                <p class="form-control">{{ $prestamo->created_at->format('d/m/Y H:i:s') }}</p>
            </div>

            <div class="form-group">
                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">VOLVER</a>
                @can('admin')
                    <a href="{{ route('prestamos.edit', $prestamo->id) }}" class="btn btn-primary">EDITAR</a>
                    <form action="{{ route('prestamos.destroy', $prestamo->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE PRÉSTAMO?')">
                            ELIMINAR
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .form-control {
            background-color: #f8f9fa;
        }
    </style>
@stop 