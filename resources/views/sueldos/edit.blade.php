@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('adminlte::page')

@section('title', 'EDITAR SUELDO')

@section('content_header')
    <h1>EDITAR SUELDO</h1>
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
            <h3 class="card-title">EDITAR REGISTRO DE SUELDO</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('sueldos.update', $sueldo) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id">EMPLEADO <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required>
                                <option value="">SELECCIONAR EMPLEADO</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" 
                                        @if(old('user_id', $sueldo->user_id) == $usuario->id) selected @endif>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="empresa_id">EMPRESA</label>
                            <select class="form-control select2 @error('empresa_id') is-invalid @enderror" 
                                    id="empresa_id" name="empresa_id">
                                <option value="">SELECCIONAR EMPRESA</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" 
                                        @if(old('empresa_id', $sueldo->empresa_id) == $empresa->id) selected @endif>
                                        {{ $empresa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha">FECHA <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('fecha') is-invalid @enderror" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="{{ old('fecha', $sueldo->fecha->format('Y-m-d')) }}" 
                                   required>
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="valor">VALOR <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('valor') is-invalid @enderror" 
                                   id="valor" 
                                   name="valor" 
                                   value="{{ old('valor', $sueldo->valor) }}" 
                                   step="0.01" 
                                   min="0" 
                                   placeholder="0.00"
                                   required>
                            @error('valor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descripcion">DESCRIPCIÓN <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="INGRESE LA DESCRIPCIÓN DEL SUELDO"
                                      required>{{ old('descripcion', $sueldo->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="documento">DOCUMENTO ESCANEADO</label>
                            
                            @if($sueldo->documento)
                                <div class="mb-2">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i> DOCUMENTO ACTUAL: 
                                        <a href="{{ Storage::url($sueldo->documento) }}" target="_blank" class="btn btn-sm btn-primary ml-2">
                                            <i class="fas fa-eye"></i> VER DOCUMENTO
                                        </a>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input @error('documento') is-invalid @enderror" 
                                           id="documento" 
                                           name="documento"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <label class="custom-file-label" for="documento">
                                        {{ $sueldo->documento ? 'CAMBIAR ARCHIVO...' : 'SELECCIONAR ARCHIVO...' }}
                                    </label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                FORMATOS PERMITIDOS: PDF, JPG, JPEG, PNG. TAMAÑO MÁXIMO: 2MB
                                @if($sueldo->documento)
                                    <br>DEJAR VACÍO PARA MANTENER EL DOCUMENTO ACTUAL
                                @endif
                            </small>
                            @error('documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> ACTUALIZAR SUELDO
                    </button>
                    <a href="{{ route('sueldos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> CANCELAR
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        placeholder: 'SELECCIONAR...',
        allowClear: true
    });
    
    // Mostrar nombre del archivo seleccionado
    $('#documento').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'SELECCIONAR ARCHIVO...');
    });
});
</script>
@stop
