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
                    <label>SELECCIONE UN MEDIO DE PAGO</label>
                    <select name="mediodepago_id" required class="form-control">
                        <option value="">SELECCIONAR EL MÉTODO DE PAGO</option>
                        @foreach($mediosdepago as $medioDePago)
                            <option value="{{ $medioDePago->id }}">{{ strtoupper($medioDePago->medio_de_pago) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>SELECCIONE UN PEDIDO</label>
                    <select name="pedido_id" id="pedido_id" required class="form-control">
                        <option value="">SELECCIONAR EL PEDIDO</option>
                        @foreach($pedidos as $pedido)
                            <option value="{{ $pedido->id }}" data-saldo="{{ $pedido->saldo }}" {{ isset($selectedPedidoId) && $selectedPedidoId == $pedido->id ? 'selected' : '' }}>
                                ORDEN: {{ $pedido->numero_orden }} - CLIENTE: {{ $pedido->cliente }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>SALDO</label>
                    <input name="saldo" id="saldo" required type="text" class="form-control" value="{{ old('saldo') }}" readonly>
                </div>
                
                <div class="form-group">
                    <label>PAGO</label>
                    <input name="pago" 
                           required 
                           type="text" 
                           pattern="^\d*\.?\d{0,2}$"
                           class="form-control" 
                           placeholder="0.00"
                           onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                </div>
                
                <div class="form-group">
                    <label>FECHA DE CREACIÓN</label>
                    <input name="created_at" type="datetime-local" class="form-control" 
                           value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}">
                </div>

                <div class="form-group">
                    <label>FOTO (OPCIONAL)</label>
                    <input name="foto" type="file" class="form-control-file" accept="image/*">
                    <small class="form-text text-muted">FORMATOS PERMITIDOS: JPEG, PNG, JPG, GIF. TAMAÑO MÁXIMO: 2MB</small>
                </div>

                <br>

                <button type="button" class="btn btn-primary pull-left" data-toggle="modal" data-target="#modal">
                    AÑADIR PAGO
                </button>
                <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
                    CANCELAR
                </a>

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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">CANCELAR</button>
                                <button type="submit" class="btn btn-primary">GUARDAR</button>
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
</script>
@stop

