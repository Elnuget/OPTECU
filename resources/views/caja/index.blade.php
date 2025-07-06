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
                <div class="col-md-8">
                    <form action="{{ route('caja.index') }}" method="GET" class="form-inline">
                        <div class="input-group mr-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text">FECHA:</span>
                            </div>
                            <input type="date" name="fecha_filtro" class="form-control" 
                                   value="{{ $fechaFiltro != 'todos' ? $fechaFiltro : '' }}">
                        </div>
                        
                        <div class="input-group mr-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text">EMPRESA:</span>
                            </div>
                            <select name="empresa_filtro" class="form-control">
                                @if($currentUser->empresa_id && !$currentUser->is_admin)
                                    <option value="{{ $currentUser->empresa_id }}" selected>
                                        {{ strtoupper($currentUser->empresa->nombre) }}
                                    </option>
                                @else
                                    <option value="todas" {{ $empresaFiltro == 'todas' ? 'selected' : '' }}>TODAS LAS EMPRESAS</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ $empresaFiltro == $empresa->id ? 'selected' : '' }}>
                                            {{ strtoupper($empresa->nombre) }}
                                        </option>
                                    @endforeach
                                    <option value="sin_empresa" {{ $empresaFiltro == 'sin_empresa' ? 'selected' : '' }}>SIN EMPRESA ASIGNADA</option>
                                @endif
                            </select>
                        </div>
                        
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">FILTRAR</button>
                            <a href="{{ route('caja.index') }}" class="btn btn-secondary">LIMPIAR</a>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('caja.index', ['mostrar_todos' => 1]) }}" class="btn btn-info">
                        <i class="fas fa-list"></i> MOSTRAR TODOS LOS MOVIMIENTOS
                    </a>
                </div>
            </div>

            @if($currentUser->is_admin)
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL EN CAJA</span>
                            <span class="info-box-number">${{ number_format($totalCaja, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tarjetas por empresa -->
            <div class="row mb-4">
                @foreach($totalesPorEmpresa as $item)
                <div class="col-md-4 mb-3">
                    <div class="info-box {{ $item['total'] >= 0 ? 'bg-info' : 'bg-warning' }}">
                        <span class="info-box-icon">
                            <i class="fas fa-building"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ strtoupper($item['empresa']->nombre) }}</span>
                            <span class="info-box-number">${{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
                
                @if($totalSinEmpresa != 0)
                <div class="col-md-4 mb-3">
                    <div class="info-box {{ $totalSinEmpresa >= 0 ? 'bg-secondary' : 'bg-danger' }}">
                        <span class="info-box-icon">
                            <i class="fas fa-question-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">SIN EMPRESA ASIGNADA</span>
                            <span class="info-box-number">${{ number_format($totalSinEmpresa, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Formulario para nuevo movimiento -->
            <div class="mb-4">
                <h4>RETIRO</h4>
                <form action="{{ route('caja.store') }}" method="POST" class="row">
                    @csrf
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>VALOR</label>
                            <input type="number" name="valor" class="form-control" step="1" required>
                            <small class="form-text text-muted">SOLO NÚMEROS ENTEROS (SIN CENTAVOS)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>MOTIVO</label>
                            <input type="text" name="motivo" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>EMPRESA</label>
                            @if($currentUser->empresa_id && !$currentUser->is_admin)
                                <input type="hidden" name="empresa_id" value="{{ $currentUser->empresa_id }}">
                                <input type="text" class="form-control" value="{{ strtoupper($currentUser->empresa->nombre) }}" readonly>
                            @else
                                <select name="empresa_id" class="form-control">
                                    <option value="">SELECCIONAR EMPRESA</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ $currentUser->empresa_id == $empresa->id ? 'selected' : '' }}>
                                            {{ strtoupper($empresa->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>VALOR</label>
                            <input type="number" name="valor" id="valorCuadre" class="form-control" step="1" min="1" required>
                            <small class="form-text text-muted">SOLO VALORES POSITIVOS ENTEROS</small>
                            <input type="hidden" name="is_positive" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>MOTIVO</label>
                            <input type="text" name="motivo" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>EMPRESA</label>
                            @if($currentUser->empresa_id && !$currentUser->is_admin)
                                <input type="hidden" name="empresa_id" value="{{ $currentUser->empresa_id }}">
                                <input type="text" class="form-control" value="{{ strtoupper($currentUser->empresa->nombre) }}" readonly>
                            @else
                                <select name="empresa_id" class="form-control">
                                    <option value="">SELECCIONAR EMPRESA</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ $currentUser->empresa_id == $empresa->id ? 'selected' : '' }}>
                                            {{ strtoupper($empresa->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
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
                            <th>EMPRESA</th>
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
                                <td>{{ $movimiento->user ? strtoupper($movimiento->user->name) : 'N/A' }}</td>
                                <td>{{ $movimiento->empresa ? strtoupper($movimiento->empresa->nombre) : 'N/A' }}</td>
                                <td>${{ number_format($movimiento->valor, 0, ',', '.') }}</td>
                                <td>
                                    @can('admin')
                                    <button type="button" class="btn btn-xs btn-primary mr-1" 
                                            onclick="editarMovimiento({{ $movimiento->id }})"
                                            title="Editar">
                                        <i class="fa fa-lg fa-fw fa-edit"></i>
                                    </button>
                                    <form action="{{ route('caja.destroy', $movimiento->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" 
                                                onclick="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE MOVIMIENTO?')"
                                                title="Eliminar">
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

    <!-- Modal para editar movimiento -->
    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarModalLabel">EDITAR MOVIMIENTO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editarForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>VALOR</label>
                            <input type="number" id="edit_valor" name="valor" class="form-control" step="1" required>
                        </div>
                        <div class="form-group">
                            <label>MOTIVO</label>
                            <input type="text" id="edit_motivo" name="motivo" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>EMPRESA</label>
                            @if($currentUser->empresa_id && !$currentUser->is_admin)
                                <input type="hidden" id="edit_empresa_id_hidden" name="empresa_id">
                                <input type="text" id="edit_empresa_readonly" class="form-control" readonly>
                            @else
                                <select id="edit_empresa_id" name="empresa_id" class="form-control">
                                    <option value="">SELECCIONAR EMPRESA</option>
                                </select>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>USUARIO</label>
                            <input type="text" id="edit_usuario" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>FECHA</label>
                            <input type="text" id="edit_fecha" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                    </div>
                </form>
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
                var valor = parseInt($('#valorCuadre').val());
                if (valor <= 0 || isNaN(valor)) {
                    e.preventDefault();
                    alert('EL VALOR DEBE SER UN NÚMERO ENTERO POSITIVO PARA CUADRAR CAJA');
                    return false;
                }
                return true;
            });
        });

        // Función para editar movimiento
        function editarMovimiento(id) {
            $.ajax({
                url: '/caja/' + id + '/edit',
                type: 'GET',
                success: function(response) {
                    // Llenar el formulario con los datos
                    $('#edit_valor').val(response.caja.valor);
                    $('#edit_motivo').val(response.caja.motivo);
                    $('#edit_usuario').val(response.caja.user ? response.caja.user.name : 'N/A');
                    $('#edit_fecha').val(new Date(response.caja.created_at).toLocaleString());
                    
                    @if($currentUser->empresa_id && !$currentUser->is_admin)
                        // Si el usuario no es admin y pertenece a una empresa, mostrar campo readonly
                        $('#edit_empresa_id_hidden').val(response.caja.empresa_id || '');
                        $('#edit_empresa_readonly').val(response.caja.empresa ? response.caja.empresa.nombre.toUpperCase() : 'N/A');
                    @else
                        // Si es admin o no pertenece a empresa, llenar el select
                        $('#edit_empresa_id').empty();
                        $('#edit_empresa_id').append('<option value="">SELECCIONAR EMPRESA</option>');
                        response.empresas.forEach(function(empresa) {
                            var selected = empresa.id == response.caja.empresa_id ? 'selected' : '';
                            $('#edit_empresa_id').append('<option value="' + empresa.id + '" ' + selected + '>' + empresa.nombre.toUpperCase() + '</option>');
                        });
                    @endif
                    
                    // Configurar la acción del formulario
                    $('#editarForm').attr('action', '/caja/' + id);
                    
                    // Mostrar el modal
                    $('#editarModal').modal('show');
                },
                error: function(xhr) {
                    alert('ERROR AL CARGAR LOS DATOS DEL MOVIMIENTO');
                }
            });
        }
    </script>
@stop
