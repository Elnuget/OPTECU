@extends('adminlte::page')

@section('title', 'CAJA')

@section('content_header')
    <h1>CAJA</h1>
    <p>ADMINISTRACIÓN DE CAJA</p>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ strtoupper(session('success')) }}
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

    <div class="card">
        <div class="card-body">
            <!-- Add date filter form -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <form action="{{ route('caja.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="date" name="fecha_filtro" class="form-control" 
                                   value="{{ $fechaFiltro }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">FILTRAR</button>
                                <a href="{{ route('caja.index') }}" class="btn btn-secondary">LIMPIAR</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL EN CAJA</span>
                            <span class="info-box-number">${{ number_format($totalCaja, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario para nuevo movimiento -->
            <div class="mb-4">
                <h4>RETIRO</h4>
                <form action="{{ route('caja.store') }}" method="POST" class="row">
                    @csrf
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>VALOR</label>
                            <input type="number" name="valor" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>MOTIVO</label>
                            <input type="text" name="motivo" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">REGISTRAR</button>
                        </div>
                    </div>
                    <input type="hidden" name="user_email" value="{{ Auth::user()->email }}">
                </form>
            </div>

            @can('admin')
            <!-- Formulario para cuadrar caja (solo administrador) -->
            <div class="mb-4">
                <h4>CUADRAR CAJA</h4>
                <form action="{{ route('caja.store') }}" method="POST" class="row" id="formCuadrarCaja">
                    @csrf
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>VALOR</label>
                            <input type="number" name="valor" id="valorCuadre" class="form-control" step="0.01" min="0.01" required>
                            <small class="form-text text-muted">SOLO VALORES POSITIVOS</small>
                            <input type="hidden" name="is_positive" value="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>MOTIVO</label>
                            <input type="text" name="motivo" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-block">AGREGAR</button>
                        </div>
                    </div>
                    <input type="hidden" name="user_email" value="{{ Auth::user()->email }}">
                </form>
            </div>
            @endcan

            <div class="table-responsive">
                <table id="cajaTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FECHA</th>
                            <th>MOTIVO</th>
                            <th>USUARIO</th>
                            <th>VALOR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $movimiento)
                            <tr @if($movimiento->valor < 0) style="background-color: #ffebee;" @endif>
                                <td>{{ $movimiento->id }}</td>
                                <td>{{ $movimiento->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ strtoupper($movimiento->motivo) }}</td>
                                <td>{{ strtoupper($movimiento->user->name) }}</td>
                                <td>${{ number_format($movimiento->valor, 2, ',', '.') }}</td>
                                <td>
                                    @can('admin')
                                    <form action="{{ route('caja.destroy', $movimiento->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" 
                                                onclick="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE MOVIMIENTO?')">
                                            <i class="fa fa-lg fa-fw fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#cajaTable').DataTable({
                "order": [[0, "desc"]],
                "paging": false,     // Disable pagination
                "info": false,       // Remove "Showing X of Y entries" text
                "searching": false,  // Remove search box
                "dom": 'Bfrt',      // Modified to remove pagination and info elements
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

            // Validación para el formulario de cuadrar caja
            $('#formCuadrarCaja').submit(function(e) {
                var valor = parseFloat($('#valorCuadre').val());
                if (valor <= 0) {
                    e.preventDefault();
                    alert('EL VALOR DEBE SER POSITIVO PARA CUADRAR CAJA');
                    return false;
                }
                return true;
            });
        });
    </script>
@stop
