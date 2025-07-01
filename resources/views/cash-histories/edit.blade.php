@extends('adminlte::page')

@section('title', 'EDITAR HISTORIAL DE CAJA')

@section('content_header')
    <h1>EDITAR HISTORIAL DE CAJA</h1>
    <p>MODIFICAR REGISTRO DE MOVIMIENTO DE CAJA</p>
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

    /* Asegurar que el placeholder también esté en mayúsculas */
    input::placeholder {
        text-transform: uppercase !important;
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">EDITAR REGISTRO</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('cash-histories.update', $cashHistory) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="monto">MONTO</label>
                <input type="number" 
                       name="monto" 
                       id="monto"
                       class="form-control" 
                       step="0.01" 
                       value="{{ old('monto', $cashHistory->monto) }}" 
                       required>
                @error('monto')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="estado">ESTADO</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="">SELECCIONAR ESTADO</option>
                    <option value="Apertura" {{ old('estado', $cashHistory->estado) == 'Apertura' ? 'selected' : '' }}>APERTURA</option>
                    <option value="Cierre" {{ old('estado', $cashHistory->estado) == 'Cierre' ? 'selected' : '' }}>CIERRE</option>
                </select>
                @error('estado')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="empresa_id">EMPRESA</label>
                <select name="empresa_id" id="empresa_id" class="form-control">
                    <option value="">SIN EMPRESA</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ old('empresa_id', $cashHistory->empresa_id) == $empresa->id ? 'selected' : '' }}>
                            {{ strtoupper($empresa->nombre) }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">OPCIONAL</small>
                @error('empresa_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>USUARIO</label>
                <input type="text" 
                       class="form-control" 
                       value="{{ $cashHistory->user ? strtoupper($cashHistory->user->name) : 'USUARIO NO DISPONIBLE' }}" 
                       readonly>
                <small class="form-text text-muted">EL USUARIO NO SE PUEDE MODIFICAR</small>
            </div>

            <div class="form-group">
                <label>FECHA DE CREACIÓN</label>
                <input type="text" 
                       class="form-control" 
                       value="{{ $cashHistory->created_at->format('Y-m-d H:i') }}" 
                       readonly>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> ACTUALIZAR
                </button>
                <a href="{{ route('cash-histories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> CANCELAR
                </a>
            </div>
        </form>
    </div>
</div>
@stop
