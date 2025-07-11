@extends('adminlte::page')

@section('title', 'HORARIOS')

@section('content_header')
    <h1>HORARIOS</h1>
    <p>CONTROL DE HORARIOS DE TRABAJO</p>
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
                    <form method="GET" action="{{ route('horarios.index') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="empresa_id">SUCURSAL</label>
                                <select name="empresa_id" class="form-control">
                                    <option value="">TODAS LAS SUCURSALES</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ strtoupper($empresa->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">FILTRAR</button>
                                    <a href="{{ route('horarios.index') }}" class="btn btn-secondary">LIMPIAR</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mb-3">
                <a type="button" class="btn btn-success" href="{{ route('horarios.create') }}">
                    <i class="fas fa-plus"></i> CREAR HORARIO
                </a>
            </div>

            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>EMPRESA</th>
                            <th>HORA ENTRADA</th>
                            <th>HORA SALIDA</th>
                            <th>DURACIÓN</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($horarios as $horario)
                            <tr>
                                <td>{{ $horario->id }}</td>
                                <td>{{ strtoupper($horario->empresa->nombre) }}</td>
                                <td>{{ \Carbon\Carbon::parse($horario->hora_entrada)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($horario->hora_salida)->format('H:i') }}</td>
                                <td>{{ $horario->duracion }} HORAS</td>
                                <td>
                                    <div class="btn-group">
                                        <a type="button" class="btn btn-sm btn-info" href="{{ route('horarios.show', $horario->id) }}">
                                            <i class="fas fa-eye"></i> VER
                                        </a>
                                        <a type="button" class="btn btn-sm btn-success" href="{{ route('horarios.edit', $horario->id) }}">
                                            <i class="fas fa-edit"></i> EDITAR
                                        </a>
                                        <form action="{{ route('horarios.destroy', $horario->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE HORARIO?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> ELIMINAR
                                            </button>
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
                {{ $horarios->links() }}
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
