@extends('adminlte::page')

@section('title', 'VER DETALLE DE SUELDO')

@section('content_header')
    <h1>DETALLE DE SUELDO</h1>
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
            <h3 class="card-title">INFORMACIÓN DEL DETALLE</h3>
            <div class="card-tools">
                <a href="{{ route('detalles-sueldo.edit', $detalleSueldo->id) }}" 
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> EDITAR
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">EMPLEADO:</th>
                            <td>{{ $detalleSueldo->user ? $detalleSueldo->user->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>PERÍODO:</th>
                            <td>{{ str_pad($detalleSueldo->mes, 2, '0', STR_PAD_LEFT) }}/{{ $detalleSueldo->ano }}</td>
                        </tr>
                        <tr>
                            <th>VALOR:</th>
                            <td class="text-success font-weight-bold">
                                ${{ number_format($detalleSueldo->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th>CREADO:</th>
                            <td>{{ $detalleSueldo->created_at ? $detalleSueldo->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>ACTUALIZADO:</th>
                            <td>{{ $detalleSueldo->updated_at ? $detalleSueldo->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>DESCRIPCIÓN:</label>
                        <div class="border p-3 bg-light">
                            {{ $detalleSueldo->descripcion }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-group text-right">
                        <a href="{{ route('sueldos.index', array_filter([
                            'anio' => $detalleSueldo->ano,
                            'mes' => $detalleSueldo->mes,
                            'usuario' => $detalleSueldo->user ? $detalleSueldo->user->name : null
                        ])) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> VOLVER AL ROL DE PAGO
                        </a>
                        <a href="{{ route('detalles-sueldo.edit', $detalleSueldo->id) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-edit"></i> EDITAR DETALLE
                        </a>
                        @can('admin')
                        <button type="button" class="btn btn-danger" 
                                data-toggle="modal" data-target="#confirmarEliminarModal">
                            <i class="fas fa-trash"></i> ELIMINAR DETALLE
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin')
    <!-- Modal Confirmar Eliminar -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE DETALLE DE SUELDO?</p>
                    <p class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        ESTA ACCIÓN NO SE PUEDE DESHACER
                    </p>
                    <div class="bg-light p-2 border-left border-warning">
                        <strong>EMPLEADO:</strong> {{ $detalleSueldo->user ? $detalleSueldo->user->name : 'N/A' }}<br>
                        <strong>PERÍODO:</strong> {{ str_pad($detalleSueldo->mes, 2, '0', STR_PAD_LEFT) }}/{{ $detalleSueldo->ano }}<br>
                        <strong>VALOR:</strong> ${{ number_format($detalleSueldo->valor, 2, ',', '.') }}<br>
                        <strong>DESCRIPCIÓN:</strong> {{ $detalleSueldo->descripcion }}
                    </div>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('detalles-sueldo.destroy', $detalleSueldo->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-danger">ELIMINAR</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan
@stop
