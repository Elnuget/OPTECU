@extends('adminlte::page')

@section('title', 'EDITAR DETALLE DE SUELDO')

@section('content_header')
    <h1>EDITAR DETALLE DE SUELDO</h1>
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
            <h3 class="card-title">EDITAR DETALLE DE SUELDO</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('detalles-sueldo.update', $detalleSueldo->id) }}" method="POST">
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
                                        @if(old('user_id', $detalleSueldo->user_id) == $usuario->id) selected @endif>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="mes">MES <span class="text-danger">*</span></label>
                            <select class="form-control @error('mes') is-invalid @enderror" 
                                    id="mes" name="mes" required>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" 
                                        @if(old('mes', $detalleSueldo->mes) == $i) selected @endif>
                                        {{ strtoupper(date('F', mktime(0, 0, 0, $i, 1))) }}
                                    </option>
                                @endfor
                            </select>
                            @error('mes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ano">AÑO <span class="text-danger">*</span></label>
                            <select class="form-control @error('ano') is-invalid @enderror" 
                                    id="ano" name="ano" required>
                                @for ($i = date('Y'); $i >= date('Y')-5; $i--)
                                    <option value="{{ $i }}" 
                                        @if(old('ano', $detalleSueldo->ano) == $i) selected @endif>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            @error('ano')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="descripcion">DESCRIPCIÓN <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3" 
                                      placeholder="DESCRIPCIÓN DEL DETALLE DE SUELDO" required>{{ old('descripcion', $detalleSueldo->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="valor">VALOR <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('valor') is-invalid @enderror" 
                                       id="valor" name="valor" 
                                       placeholder="0.00" 
                                       value="{{ old('valor', $detalleSueldo->valor) }}" required>
                                @error('valor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-right">
                    <a href="{{ route('sueldos.index', array_filter([
                        'anio' => $detalleSueldo->ano,
                        'mes' => $detalleSueldo->mes,
                        'usuario' => $detalleSueldo->user ? $detalleSueldo->user->name : null
                    ])) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> CANCELAR
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> ACTUALIZAR DETALLE
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#user_id').select2({
                theme: 'bootstrap4',
                placeholder: "SELECCIONAR EMPLEADO",
                allowClear: false
            });
        });
    </script>
@stop
