@extends('adminlte::page')

@section('title', 'AÑADIR PAGO')

@section('content_header')
@if(session('error'))
<div class="alert {{session('tipo')}} alert-dismissible fade show" role="alert">
    <strong>{{session('error')}}</strong> {{session('mensaje')}}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>ERRORES DE VALIDACIÓN:</strong>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
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
    strong,
    .select2-selection__rendered {
        text-transform: uppercase !important;
    }

    /* Asegurar que el placeholder también esté en mayúsculas */
    input::placeholder {
        text-transform: uppercase !important;
    }
</style>

<br>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">AÑADIR PAGO</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="COLLAPSE">
                <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="REMOVE">
                <i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="col-md-6">
            <form role="form" action="{{ route('pagos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label>SELECCIONE UN MEDIO DE PAGO <span class="text-danger">*</span></label>
                    <select name="mediodepago_id" required class="form-control {{ $errors->has('mediodepago_id') ? 'is-invalid' : '' }}">
                        <option value="">SELECCIONAR EL MÉTODO DE PAGO</option>
                        @foreach($mediosdepago as $medioDePago)
                            <option value="{{ $medioDePago->id }}" {{ old('mediodepago_id') == $medioDePago->id ? 'selected' : '' }}>
                                {{ strtoupper($medioDePago->medio_de_pago) }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->has('mediodepago_id'))
                        <div class="invalid-feedback">
                            {{ $errors->first('mediodepago_id') }}
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label>SELECCIONE UN PEDIDO <span class="text-danger">*</span></label>
                    <select name="pedido_id" id="pedido_id" required class="form-control {{ $errors->has('pedido_id') ? 'is-invalid' : '' }}">
                        <option value="">SELECCIONAR EL PEDIDO</option>
                        @foreach($pedidos as $pedido)
                            <option value="{{ $pedido->id }}" 
                                   data-saldo="{{ number_format($pedido->saldo, 2, '.', '') }}" 
                                   {{ (isset($selectedPedidoId) && $selectedPedidoId == $pedido->id) || old('pedido_id') == $pedido->id ? 'selected' : '' }}>
                                ORDEN: {{ $pedido->numero_orden }} - CLIENTE: {{ $pedido->cliente }} - SALDO: ${{ number_format($pedido->saldo, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->has('pedido_id'))
                        <div class="invalid-feedback">
                            {{ $errors->first('pedido_id') }}
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label>SALDO <span class="text-danger">*</span></label>
                    <input name="saldo" id="saldo" required type="text" class="form-control" value="{{ old('saldo') }}" readonly>
                    <small class="form-text text-muted">SALDO PENDIENTE DEL PEDIDO SELECCIONADO</small>
                </div>
                
                <div class="form-group">
                    <label>PAGO <span class="text-danger">*</span></label>
                    <input name="pago" 
                           id="pago"
                           required 
                           type="number" 
                           step="0.01"
                           min="0.01"
                           class="form-control {{ $errors->has('pago') ? 'is-invalid' : '' }}" 
                           placeholder="0.00"
                           value="{{ old('pago') }}"
                           onblur="validarMonto(this)"
                           oninput="formatearDecimales(this)">
                    @if($errors->has('pago'))
                        <div class="invalid-feedback">
                            {{ $errors->first('pago') }}
                        </div>
                    @endif
                    <small class="form-text text-muted">INGRESE EL MONTO DEL PAGO (ACEPTA DECIMALES HASTA 2 POSICIONES)</small>
                </div>
                
                <div class="form-group">
                    <label>FECHA DE CREACIÓN <span class="text-danger">*</span></label>
                    <input name="created_at" 
                           type="datetime-local" 
                           class="form-control {{ $errors->has('created_at') ? 'is-invalid' : '' }}" 
                           value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}">
                    @if($errors->has('created_at'))
                        <div class="invalid-feedback">
                            {{ $errors->first('created_at') }}
                        </div>
                    @endif
                    <small class="form-text text-muted">FECHA Y HORA DEL PAGO</small>
                </div>

                <div class="form-group">
                    <label>FOTO (OPCIONAL)</label>
                    <input name="foto" 
                           type="file" 
                           class="form-control-file {{ $errors->has('foto') ? 'is-invalid' : '' }}" 
                           accept="image/*">
                    @if($errors->has('foto'))
                        <div class="invalid-feedback">
                            {{ $errors->first('foto') }}
                        </div>
                    @endif
                    <small class="form-text text-muted">FORMATOS PERMITIDOS: JPEG, PNG, JPG, GIF. TAMAÑO MÁXIMO: 2MB</small>
                </div>

                <br>

                <div class="form-group">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal">
                        <i class="fas fa-save"></i> AÑADIR PAGO
                    </button>
                    <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> VOLVER A PEDIDOS
                    </a>
                </div>

                <div class="modal fade" id="modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">CONFIRMAR CREACIÓN</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>¿ESTÁ SEGURO QUE DESEA GUARDAR ESTE NUEVO PAGO?</p>
                                <p><small>EL SALDO DEL PEDIDO SE ACTUALIZARÁ AUTOMÁTICAMENTE</small></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                                    <i class="fas fa-times"></i> CANCELAR
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> GUARDAR PAGO
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-footer">
        AÑADIR PAGO
    </div>
</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pedidoSelect = document.getElementById('pedido_id');
        const saldoInput = document.getElementById('saldo');
        const pagoInput = document.getElementById('pago');

        // Function to update saldo based on selected pedido
        function updateSaldo() {
            const selectedOption = pedidoSelect.options[pedidoSelect.selectedIndex];
            const saldo = selectedOption.getAttribute('data-saldo') || '';
            saldoInput.value = saldo;
        }

        // Event listener for changes in pedido selection
        pedidoSelect.addEventListener('change', updateSaldo);

        // Initialize saldo if a pedido is pre-selected
        updateSaldo();
    });

    // Formatear decimales mientras se escribe
    function formatearDecimales(element) {
        let valor = element.value;
        
        // Si está vacío, no hacer nada
        if (!valor) return;
        
        // Remover caracteres no numéricos excepto punto
        valor = valor.replace(/[^0-9.]/g, '');
        
        // Asegurar que solo haya un punto decimal
        const partes = valor.split('.');
        if (partes.length > 2) {
            valor = partes[0] + '.' + partes.slice(1).join('');
        }
        
        // Limitar decimales a 2 posiciones
        if (partes.length === 2 && partes[1].length > 2) {
            valor = partes[0] + '.' + partes[1].substring(0, 2);
        }
        
        element.value = valor;
    }

    // Validar el monto del pago contra el saldo
    function validarMonto(element) {
        const saldoInput = document.getElementById('saldo');
        const montoInput = element;
        
        // Limpiar y convertir a números
        const saldoTexto = saldoInput.value.replace(/[^0-9.]/g, '');
        const saldo = parseFloat(saldoTexto) || 0;
        const monto = parseFloat(montoInput.value) || 0;
        
        // Formatear con 2 decimales si hay valor
        if (montoInput.value && monto > 0) {
            montoInput.value = monto.toFixed(2);
        }
        
        // Validar que el monto sea mayor a cero
        if (monto <= 0 && montoInput.value) {
            alert('ADVERTENCIA: EL MONTO DEL PAGO DEBE SER MAYOR A CERO');
            montoInput.value = '';
            montoInput.focus();
            return false;
        }
        
        // Validar que el monto no sea mayor al saldo
        if (monto > saldo && saldo > 0) {
            alert('ADVERTENCIA: EL MONTO DEL PAGO ($' + monto.toFixed(2) + ') NO PUEDE SER MAYOR AL SALDO PENDIENTE ($' + saldo.toFixed(2) + ')');
            montoInput.value = saldo.toFixed(2);
            return false;
        }
        
        return true;
    }
</script>
@stop

