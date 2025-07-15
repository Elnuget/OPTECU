@extends('adminlte::page')

@section('title', 'EDITAR EGRESO')

@section('content_header')
    <h1>EDITAR EGRESO</h1>
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

        /* Estilo para campos requeridos */
        .required:after {
            content: ' *';
            color: red;
        }
    </style>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('egresos.update', $egreso->id) }}" method="POST" id="egresoEditForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Valor del Egreso -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="valor" class="required">VALOR</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" 
                                       class="form-control @error('valor') is-invalid @enderror" 
                                       id="valor" 
                                       name="valor" 
                                       step="1" 
                                       min="0" 
                                       value="{{ old('valor', $egreso->valor) }}"
                                       required>
                            </div>
                            @error('valor')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Motivo del Egreso -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="motivo" class="required">MOTIVO</label>
                            <input type="text" 
                                   class="form-control @error('motivo') is-invalid @enderror" 
                                   id="motivo" 
                                   name="motivo" 
                                   value="{{ old('motivo', $egreso->motivo) }}"
                                   required>
                            @error('motivo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Sucursal -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="empresa_id">SUCURSAL</label>
                            <select name="empresa_id" 
                                    id="empresa_id" 
                                    class="form-control @error('empresa_id') is-invalid @enderror">
                                <option value="">SELECCIONE SUCURSAL</option>
                                @foreach(\App\Models\Empresa::all() as $empresa)
                                    <option value="{{ $empresa->id }}" 
                                            {{ old('empresa_id', $egreso->empresa_id) == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
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
                        <button type="submit" class="btn btn-primary">ACTUALIZAR EGRESO</button>
                        <a href="{{ route('egresos.show', $egreso->id) }}" class="btn btn-secondary">CANCELAR</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
    <script>
        $(document).ready(function() {
            // Validación del formulario
            $('#egresoEditForm').submit(function(e) {
                let valor = parseFloat($('#valor').val());
                let motivo = $('#motivo').val().trim();
                let isValid = true;

                // Validar valor
                if (isNaN(valor) || valor <= 0) {
                    alert('El valor debe ser mayor a 0');
                    isValid = false;
                }

                // Validar motivo
                if (motivo.length === 0) {
                    alert('El motivo es requerido');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Convertir texto a mayúsculas mientras se escribe
            $('#motivo').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // Formatear el valor cuando el campo pierde el foco
            $('#valor').on('blur', function() {
                let value = $(this).val();
                if (value !== '') {
                    value = parseFloat(value);
                    if (!isNaN(value)) {
                        $(this).val(Math.round(value));
                    }
                }
            });
        });
    </script>
@stop 