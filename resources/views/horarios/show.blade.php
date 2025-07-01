@extends('adminlte::page')

@section('title', 'VER HORARIO')

@section('content_header')
    <h1>DETALLE DEL HORARIO</h1>
    <p>INFORMACIÓN COMPLETA DEL HORARIO DE TRABAJO</p>
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
    </style>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">INFORMACIÓN DEL HORARIO - ID: {{ $horario->id }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">ID:</th>
                                <td>{{ $horario->id }}</td>
                            </tr>
                            <tr>
                                <th>EMPRESA:</th>
                                <td>{{ strtoupper($horario->empresa->nombre) }}</td>
                            </tr>
                            <tr>
                                <th>HORA DE ENTRADA:</th>
                                <td>
                                    <i class="fas fa-sign-in-alt text-success"></i>
                                    {{ \Carbon\Carbon::parse($horario->hora_entrada)->format('H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <th>HORA DE SALIDA:</th>
                                <td>
                                    <i class="fas fa-sign-out-alt text-danger"></i>
                                    {{ \Carbon\Carbon::parse($horario->hora_salida)->format('H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <th>DURACIÓN:</th>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-clock"></i> {{ $horario->duracion }} HORAS
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>FECHA DE CREACIÓN:</th>
                                <td>{{ $horario->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>ÚLTIMA MODIFICACIÓN:</th>
                                <td>{{ $horario->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="btn-group">
                        <a href="{{ route('horarios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> VOLVER AL LISTADO
                        </a>
                        <a href="{{ route('horarios.edit', $horario->id) }}" class="btn btn-success">
                            <i class="fas fa-edit"></i> EDITAR HORARIO
                        </a>
                        <form action="{{ route('horarios.destroy', $horario->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE HORARIO?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> ELIMINAR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Información de la empresa -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">INFORMACIÓN DE LA EMPRESA</h3>
                </div>
                <div class="card-body">
                    <p><strong>NOMBRE:</strong><br>{{ strtoupper($horario->empresa->nombre) }}</p>
                    @if($horario->empresa->direccion)
                        <p><strong>DIRECCIÓN:</strong><br>{{ strtoupper($horario->empresa->direccion) }}</p>
                    @endif
                    @if($horario->empresa->telefono)
                        <p><strong>TELÉFONO:</strong><br>{{ $horario->empresa->telefono }}</p>
                    @endif
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ESTADÍSTICAS</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">HORAS DIARIAS</span>
                            <span class="info-box-number">{{ $horario->duracion }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">HORAS SEMANALES</span>
                            <span class="info-box-number">{{ $horario->duracion * 7 }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">HORAS MENSUALES</span>
                            <span class="info-box-number">{{ $horario->duracion * 30 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reloj en tiempo real -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">HORA ACTUAL</h3>
                </div>
                <div class="card-body text-center">
                    <div id="reloj" class="display-4 mb-3"></div>
                    <small class="text-muted">HORA DEL SISTEMA</small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Reloj en tiempo real
    function actualizarReloj() {
        const ahora = new Date();
        const hora = ahora.toLocaleTimeString('es-ES', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        document.getElementById('reloj').textContent = hora;
    }

    // Actualizar cada segundo
    setInterval(actualizarReloj, 1000);
    
    // Ejecutar inmediatamente
    actualizarReloj();
</script>
@stop
