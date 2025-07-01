@extends('adminlte::page')

@section('title', 'ASISTENCIAS')

@section('content_header')
    <h1>ASISTENCIAS</h1>
    <p>CONTROL DE ASISTENCIA DE EMPLEADOS</p>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ strtoupper(session('success')) }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ strtoupper(session('error')) }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
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

    <div class="card">
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('asistencias.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="user_id">USUARIO</label>
                                <select name="user_id" class="form-control">
                                    <option value="">TODOS LOS USUARIOS</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ request('user_id') == $usuario->id ? 'selected' : '' }}>
                                            {{ strtoupper($usuario->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="empresa_id">EMPRESA</label>
                                <select name="empresa_id" class="form-control">
                                    <option value="">TODAS LAS EMPRESAS</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ strtoupper($empresa->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="fecha">FECHA</label>
                                <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="estado">ESTADO</label>
                                <select name="estado" class="form-control">
                                    <option value="">TODOS LOS ESTADOS</option>
                                    <option value="presente" {{ request('estado') == 'presente' ? 'selected' : '' }}>PRESENTE</option>
                                    <option value="ausente" {{ request('estado') == 'ausente' ? 'selected' : '' }}>AUSENTE</option>
                                    <option value="tardanza" {{ request('estado') == 'tardanza' ? 'selected' : '' }}>TARDANZA</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">FILTRAR</button>
                                    <a href="{{ route('asistencias.index') }}" class="btn btn-secondary">LIMPIAR</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mb-3">
                <a type="button" class="btn btn-success" href="{{ route('asistencias.create') }}">REGISTRAR ASISTENCIA</a>
                <a type="button" class="btn btn-info" href="{{ route('asistencias.reporte') }}">VER REPORTE</a>
                <div class="btn-group" role="group">
                    <a type="button" class="btn btn-primary" href="{{ route('asistencias.mi-qr') }}">
                        <i class="fas fa-qrcode"></i> MI QR
                    </a>
                    <a type="button" class="btn btn-secondary" href="{{ route('asistencias.scan') }}">
                        <i class="fas fa-camera"></i> ESCANEAR QR
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>USUARIO</th>
                            <th>EMPRESA</th>
                            <th>FECHA</th>
                            <th>HORA ENTRADA</th>
                            <th>HORA SALIDA</th>
                            <th>ESTADO</th>
                            <th>HORAS TRABAJADAS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asistencias as $asistencia)
                            <tr>
                                <td>{{ $asistencia->id }}</td>
                                <td>{{ strtoupper($asistencia->user->name) }}</td>
                                <td>{{ $asistencia->user->empresa ? strtoupper($asistencia->user->empresa->nombre) : '-' }}</td>
                                <td>{{ $asistencia->fecha_hora->format('d/m/Y') }}</td>
                                <td>{{ $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '-' }}</td>
                                <td>{{ $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $asistencia->estado == 'presente' ? 'success' : ($asistencia->estado == 'tardanza' ? 'warning' : 'danger') }}">
                                        {{ strtoupper($asistencia->estado_formateado) }}
                                    </span>
                                </td>
                                <td>{{ $asistencia->horas_trabajadas ?? '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a type="button" class="btn btn-sm btn-info" href="{{ route('asistencias.show', $asistencia->id) }}">VER</a>
                                        <a type="button" class="btn btn-sm btn-success" href="{{ route('asistencias.edit', $asistencia->id) }}">EDITAR</a>
                                        <form action="{{ route('asistencias.destroy', $asistencia->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTA ASISTENCIA?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">ELIMINAR</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $asistencias->links() }}
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            "pageLength": 25,
            "order": [[ 0, "desc" ]]
        });
    });
</script>
@stop
