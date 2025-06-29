@extends('adminlte::page')

@section('title', 'EDITAR ASISTENCIA')

@section('content_header')
    <h1>EDITAR ASISTENCIA</h1>
    <p>MODIFICAR REGISTRO DE ASISTENCIA</p>
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

            <form action="{{ route('asistencias.update', $asistencia->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id" class="required">USUARIO</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">SELECCIONAR USUARIO</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" 
                                        {{ (old('user_id', $asistencia->user_id) == $usuario->id) ? 'selected' : '' }}>
                                        {{ strtoupper($usuario->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_hora" class="required">FECHA</label>
                            <input type="date" name="fecha_hora" id="fecha_hora" class="form-control" 
                                   value="{{ old('fecha_hora', $asistencia->fecha_hora->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora_entrada">HORA DE ENTRADA</label>
                            <input type="time" name="hora_entrada" id="hora_entrada" class="form-control" 
                                   value="{{ old('hora_entrada', $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora_salida">HORA DE SALIDA</label>
                            <input type="time" name="hora_salida" id="hora_salida" class="form-control" 
                                   value="{{ old('hora_salida', $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : '') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado" class="required">ESTADO</label>
                            <select name="estado" id="estado" class="form-control" required>
                                <option value="">SELECCIONAR ESTADO</option>
                                <option value="presente" {{ old('estado', $asistencia->estado) == 'presente' ? 'selected' : '' }}>PRESENTE</option>
                                <option value="ausente" {{ old('estado', $asistencia->estado) == 'ausente' ? 'selected' : '' }}>AUSENTE</option>
                                <option value="tardanza" {{ old('estado', $asistencia->estado) == 'tardanza' ? 'selected' : '' }}>TARDANZA</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">ACTUALIZAR ASISTENCIA</button>
                    <a href="{{ route('asistencias.index') }}" class="btn btn-secondary">CANCELAR</a>
                    <a href="{{ route('asistencias.show', $asistencia->id) }}" class="btn btn-info">VER DETALLES</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Auto-determinar estado basado en hora de entrada
        $('#hora_entrada').on('change', function() {
            const horaEntrada = $(this).val();
            const estadoSelect = $('#estado');
            
            if (horaEntrada) {
                if (horaEntrada > '08:00') {
                    estadoSelect.val('tardanza');
                } else {
                    estadoSelect.val('presente');
                }
            }
        });

        // Validar que hora de salida sea posterior a hora de entrada
        $('#hora_salida').on('change', function() {
            const horaEntrada = $('#hora_entrada').val();
            const horaSalida = $(this).val();
            
            if (horaEntrada && horaSalida && horaSalida <= horaEntrada) {
                alert('LA HORA DE SALIDA DEBE SER POSTERIOR A LA HORA DE ENTRADA');
                $(this).val('');
            }
        });
    });
</script>
@stop
