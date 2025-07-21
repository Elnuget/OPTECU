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
    <div class="col-md-8">
        <form action="{{ route('cash-histories.index') }}" method="GET" class="form-inline">
            <div class="input-group mr-2">
                <input type="date" name="fecha_filtro" class="form-control" value="{{ request('fecha_filtro', now()->format('Y-m-d')) }}">
            </div>
            
            <div class="input-group mr-2">
                @if($currentUser->is_admin)
                    {{-- Si es admin, puede seleccionar cualquier empresa --}}
                    <select name="empresa_id" class="form-control">
                        <option value="">TODAS LAS SUCURSALES</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id', '') == $empresa->id ? 'selected' : '' }}>
                                {{ strtoupper($empresa->nombre) }}
                            </option>
                        @endforeach
                    </select>
                @else
                    {{-- Para usuarios no admin, mostrar sus empresas asignadas --}}
                    <select name="empresa_id" class="form-control">
                        <option value="">MIS SUCURSALES</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id', '') == $empresa->id ? 'selected' : '' }}>
                                {{ strtoupper($empresa->nombre) }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            
            <div class="input-group">
                <button type="submit" class="btn btn-primary">FILTRAR</button>
                <a href="{{ route('cash-histories.index') }}" class="btn btn-secondary">LIMPIAR</a>
            </div>
        </form>
    </div>
    
    @if(Auth::user()->is_admin)
    <div class="col-md-4 text-right">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#adminCajaModal">
            <i class="fas fa-cash-register mr-1"></i> ABRIR/CERRAR CAJA
        </button>
    </div>
    @endif
</div>

<div class="table-responsive mt-4">
    <table id="cashHistoryTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>FECHA</th>
                <th>USUARIO</th>
                <th>EMPRESA</th>
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
                    <td>{{ $history->empresa ? strtoupper($history->empresa->nombre) : 'NO ASIGNADA' }}</td>
                    <td>${{ number_format($history->monto, 0, ',', '.') }}</td>
                    <td>{{ strtoupper($history->estado) }}</td>
                    <td>
                        @if($currentUser->is_admin)
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
                        @else
                            <span class="text-muted">SIN PERMISOS</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal para abrir/cerrar caja (solo para administradores) -->
<div class="modal fade" id="adminCajaModal" tabindex="-1" role="dialog" aria-labelledby="adminCajaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminCajaModalLabel">ABRIR/CERRAR CAJA</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="adminCajaForm" action="{{ route('cash-histories.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="empresa_select">SELECCIONAR SUCURSAL</label>
                        <select name="empresa_id" id="empresa_select" class="form-control" required>
                            <option value="">SELECCIONE UNA EMPRESA</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ strtoupper($empresa->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>SALDO ACTUAL EN CAJA</label>
                        <div class="alert alert-info">
                            <h4 class="text-center" id="caja_value">$0</h4>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado_select">OPERACIÓN</label>
                        <select name="estado" id="estado_select" class="form-control" required>
                            <option value="">SELECCIONE UNA OPERACIÓN</option>
                            <option value="Apertura">APERTURA DE CAJA</option>
                            <option value="Cierre">CIERRE DE CAJA</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto_admin">MONTO</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="1" name="monto" id="monto_admin" class="form-control" required readonly>
                        </div>
                        <small class="form-text text-muted">EL MONTO SE CARGA AUTOMÁTICAMENTE SEGÚN LA EMPRESA SELECCIONADA (SOLO NÚMEROS ENTEROS)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                <button type="submit" form="adminCajaForm" class="btn btn-success">CONFIRMAR</button>
            </div>
        </div>
    </div>
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

            // Código para gestionar el modal de caja para administradores
            const empresaSelect = $('#empresa_select');
            const estadoSelect = $('#estado_select');
            const montoInput = $('#monto_admin');
            const cajaValueDisplay = $('#caja_value');

            // Cargar el valor de caja cuando se selecciona una empresa
            empresaSelect.on('change', function() {
                const empresaId = $(this).val();
                if (empresaId) {
                    // Verificar el estado de la caja para esta empresa
                    $.ajax({
                        url: '{{ route("cash-histories.checkStatus") }}',
                        type: 'GET',
                        data: { empresa_id: empresaId },
                        success: function(response) {
                            // Actualizar el estado de la caja y el valor
                            estadoSelect.val(response.estado);
                            cajaValueDisplay.text('$' + response.valor);
                            montoInput.val(response.valor);
                            
                            // Habilitar/deshabilitar campos según el estado
                            if (response.estado === 'Cierre') {
                                estadoSelect.find('option[value="Apertura"]').prop('disabled', true);
                                estadoSelect.find('option[value="Cierre"]').prop('disabled', false);
                            } else {
                                estadoSelect.find('option[value="Apertura"]').prop('disabled', false);
                                estadoSelect.find('option[value="Cierre"]').prop('disabled', true);
                            }
                        },
                        error: function() {
                            alert('Error al obtener el estado de la caja');
                        }
                    });
                } else {
                    cajaValueDisplay.text('$0');
                    montoInput.val('');
                }
            });
        });
    </script>
@stop
