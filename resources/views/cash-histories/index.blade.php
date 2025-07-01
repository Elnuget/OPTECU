@extends('adminlte::page')

@section('title', 'HISTORIAL DE CAJA')

@section('content_header')
    <h1>HISTORIAL DE CAJA</h1>
    <p>REGISTRO HISTÓRICO DE MOVIMIENTOS DE CAJA</p>
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
    .close,
    .dataTables_info,
    .dataTables_length,
    .dataTables_filter,
    .paginate_button,
    div.dt-buttons,
    .sorting,
    .sorting_asc,
    .sorting_desc {
        text-transform: uppercase !important;
    }

    /* Asegurar que el placeholder también esté en mayúsculas */
    input::placeholder,
    .dataTables_filter input::placeholder {
        text-transform: uppercase !important;
    }
</style>

<div class="row mb-3">
    <div class="col-md-4">
        <form action="{{ route('cash-histories.index') }}" method="GET" class="form-inline">
            <div class="input-group">
                <input type="date" name="fecha_filtro" class="form-control" value="{{ request('fecha_filtro', now()->format('Y-m-d')) }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">FILTRAR</button>
                    <a href="{{ route('cash-histories.index') }}" class="btn btn-secondary">LIMPIAR</a>
                </div>
            </div>
        </form>
    </div>
</div>



<div class="table-responsive mt-4">
    <table id="cashHistoryTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>FECHA</th>
                <th>USUARIO</th>
                <th>MONTO</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashHistories as $history)
                <tr>
                    <td>{{ $history->id }}</td>
                    <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $history->user ? strtoupper($history->user->name) : 'USUARIO NO DISPONIBLE' }}</td>
                    <td>${{ number_format($history->monto, 2) }}</td>
                    <td>{{ strtoupper($history->estado) }}</td>
                    <td>
                        <a href="{{ route('cash-histories.edit', $history) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('cash-histories.destroy', $history) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE REGISTRO?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#cashHistoryTable').DataTable({
                "order": [[0, "desc"]],
                "buttons": [
                    {
                        extend: 'excel',
                        text: 'EXCEL'
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF'
                    },
                    {
                        extend: 'print',
                        text: 'IMPRIMIR'
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop
