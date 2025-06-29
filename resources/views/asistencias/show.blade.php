@extends('adminlte::page')

@section('title', 'DETALLES ASISTENCIA')

@section('content_header')
    <h1>DETALLES DE ASISTENCIA</h1>
    <p>INFORMACIÓN COMPLETA DEL REGISTRO</p>
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

        .info-label {
            font-weight: bold;
            color: #495057;
        }
        
        .info-value {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">INFORMACIÓN DE ASISTENCIA</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">ID DE ASISTENCIA:</label>
                                <div class="info-value">{{ sprintf('%06d', $asistencia->id) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">USUARIO:</label>
                                <div class="info-value">{{ strtoupper($asistencia->user->name) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">FECHA:</label>
                                <div class="info-value">{{ $asistencia->fecha_hora->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">DÍA DE LA SEMANA:</label>
                                <div class="info-value">{{ strtoupper($asistencia->fecha_hora->locale('es')->isoFormat('dddd')) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">HORA DE ENTRADA:</label>
                                <div class="info-value">
                                    {{ $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : 'NO REGISTRADA' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">HORA DE SALIDA:</label>
                                <div class="info-value">
                                    {{ $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : 'NO REGISTRADA' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">ESTADO:</label>
                                <div class="info-value">
                                    <span class="badge badge-{{ $asistencia->estado == 'presente' ? 'success' : ($asistencia->estado == 'tardanza' ? 'warning' : 'danger') }} badge-lg">
                                        {{ strtoupper($asistencia->estado_formateado) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">HORAS TRABAJADAS:</label>
                                <div class="info-value">
                                    {{ $asistencia->horas_trabajadas ?? 'NO CALCULADO' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">LLEGADA TARDE:</label>
                                <div class="info-value">
                                    {{ $asistencia->esLlegadaTarde() ? 'SÍ' : 'NO' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">FECHA DE REGISTRO:</label>
                                <div class="info-value">{{ $asistencia->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    @if($asistencia->updated_at != $asistencia->created_at)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="info-label">ÚLTIMA MODIFICACIÓN:</label>
                                <div class="info-value">{{ $asistencia->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel de Acciones y Estadísticas -->
        <div class="col-md-4">
            <!-- Acciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ACCIONES</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('asistencias.edit', $asistencia->id) }}" class="btn btn-success mb-2">
                            <i class="fas fa-edit"></i> EDITAR ASISTENCIA
                        </a>
                        <a href="{{ route('asistencias.index') }}" class="btn btn-secondary mb-2">
                            <i class="fas fa-list"></i> VOLVER AL LISTADO
                        </a>
                        <form action="{{ route('asistencias.destroy', $asistencia->id) }}" method="POST" 
                              onsubmit="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTA ASISTENCIA?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> ELIMINAR ASISTENCIA
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Información del Usuario -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">INFORMACIÓN DEL USUARIO</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="info-label">NOMBRE COMPLETO:</label>
                        <div class="info-value">{{ strtoupper($asistencia->user->name) }}</div>
                    </div>
                    <div class="form-group">
                        <label class="info-label">USUARIO:</label>
                        <div class="info-value">{{ strtoupper($asistencia->user->user) }}</div>
                    </div>
                    <div class="form-group">
                        <label class="info-label">EMAIL:</label>
                        <div class="info-value">{{ $asistencia->user->email }}</div>
                    </div>
                    <div class="form-group">
                        <label class="info-label">ESTADO:</label>
                        <div class="info-value">
                            <span class="badge badge-{{ $asistencia->user->active ? 'success' : 'danger' }}">
                                {{ $asistencia->user->active ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($asistencia->estado == 'tardanza')
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">OBSERVACIÓN</h3>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-exclamation-triangle"></i> ESTE EMPLEADO LLEGÓ TARDE</p>
                </div>
            </div>
            @endif

            @if($asistencia->estado == 'ausente')
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title">OBSERVACIÓN</h3>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-times-circle"></i> EMPLEADO AUSENTE</p>
                </div>
            </div>
            @endif
        </div>
    </div>
@stop
