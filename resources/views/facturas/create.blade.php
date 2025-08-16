@extends('adminlte::page')
@section('title', 'Crear Factura')

@section('content_header')
<h1>Crear Factura</h1>
<p>Complete los campos para crear una nueva factura</p>
@if (session('error'))
    <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
        <strong>{{ session('mensaje') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form id="facturaForm">
            @csrf
            
            @if(isset($pedido) && $pedido)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Creando factura para el pedido <strong>#{{ $pedido->num_orden }}</strong>
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
            </div>
            @endif
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="declarante_id">Declarante <span class="text-danger">*</span></label>
                        <select id="declarante_id" name="declarante_id" class="form-control" required>
                            <option value="">Seleccione un declarante</option>
                            @foreach ($declarantes as $declarante)
                                <option value="{{ $declarante->id }}">{{ $declarante->nombre }} ({{ $declarante->ruc }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="declarante_id-error"></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="numero">Número de factura <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="numero" name="numero" required>
                        <div class="invalid-feedback" id="numero-error"></div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback" id="fecha-error"></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="total">Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="total" name="total" step="0.01" min="0" required>
                        </div>
                        <div class="invalid-feedback" id="total-error"></div>
                        <small class="form-text text-muted">El IVA (12%) y el subtotal se calcularán automáticamente</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" class="form-control">
                            <option value="pendiente">Pendiente</option>
                            <option value="pagada">Pagada</option>
                            <option value="anulada">Anulada</option>
                        </select>
                        <div class="invalid-feedback" id="estado-error"></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" class="form-control">
                            <option value="venta">Venta</option>
                            <option value="compra">Compra</option>
                        </select>
                        <div class="invalid-feedback" id="tipo-error"></div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Subtotal</span>
                            <span class="info-box-number" id="subtotalCalculado">$0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">IVA (12%)</span>
                            <span class="info-box-number" id="ivaCalculado">$0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">Total</span>
                            <span class="info-box-number" id="totalCalculado">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="guardarFactura">
                            <i class="fas fa-save"></i> Guardar Factura
                        </button>
                        <a href="{{ route('facturas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
@include('atajos')
@parent
<script>
    $(document).ready(function () {
        // Calcular valores automáticamente cuando cambie el total
        $('#total').on('input', function() {
            calcularValores();
        });
        
        function calcularValores() {
            const total = parseFloat($('#total').val()) || 0;
            const iva = total * 0.12;
            const subtotal = total - iva;
            
            // Mostrar valores calculados
            $('#subtotalCalculado').text('$' + subtotal.toFixed(2));
            $('#ivaCalculado').text('$' + iva.toFixed(2));
            $('#totalCalculado').text('$' + total.toFixed(2));
        }
        
        // Enviar formulario de factura
        $('#facturaForm').on('submit', function(e) {
            e.preventDefault();
            
            // Limpiar errores previos
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Deshabilitar botón de guardar
            $('#guardarFactura').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            
            // Obtener datos del formulario
            const formData = $(this).serialize();
            
            // Enviar petición AJAX
            $.ajax({
                url: "{{ route('facturas.store') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            // Redirigir a la lista de facturas
                            window.location.href = "{{ route('facturas.index') }}";
                        });
                    } else {
                        // Mostrar mensaje de error
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        
                        // Habilitar botón de guardar
                        $('#guardarFactura').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Factura');
                    }
                },
                error: function(xhr) {
                    // Mostrar errores de validación
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        
                        // Mostrar cada error en su campo correspondiente
                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#' + field + '-error').text(messages[0]);
                        });
                    } else {
                        // Mostrar mensaje de error general
                        Swal.fire({
                            title: 'Error',
                            text: 'Ha ocurrido un error al guardar la factura.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    
                    // Habilitar botón de guardar
                    $('#guardarFactura').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Factura');
                }
            });
        });
        
        // Inicializar cálculos
        calcularValores();
        
        // Si hay un pedido_id, cargar los datos del pedido
        @if(isset($pedido) && $pedido)
        // Si el pedido tiene un total, mostrarlo en el formulario
        @if(isset($pedido->total) && $pedido->total > 0)
        $('#total').val({{ $pedido->total }}).trigger('input');
        @endif
        
        // Si el pedido tiene una empresa asociada, seleccionarla
        @if(isset($pedido->empresa_id) && $pedido->empresa_id)
        // Aquí se podría implementar la selección de empresa si es necesario
        @endif
        @endif
    });
</script>
@stop
