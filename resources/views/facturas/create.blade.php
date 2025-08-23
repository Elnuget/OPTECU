@extends('adminlte::page')
@section('title', 'Crear Factura')

@section('content_header')
<h1>Crear Factura</h1>
<p>Seleccione los elementos del pedido que desea incluir en la factura</p>
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
                <i class="fas fa-info-circle"></i> Creando factura para el pedido <strong>#{{ $pedido->numero_orden }}</strong> - Cliente: <strong>{{ $pedido->cliente }}</strong>
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No se ha especificado un pedido para facturar.
            </div>
            @endif
            
            <div class="row">
                <div class="col-md-12">
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
            </div>

            @if(isset($pedido) && $pedido)
            <!-- Sección para seleccionar elementos del pedido -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Elementos a Facturar</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Examen Visual -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_examen" name="incluir_examen" onchange="calcularTotales()" checked>
                                        <label class="form-check-label" for="incluir_examen">
                                            Examen Visual
                                        </label>
                                        <div class="mt-2" id="precio_examen_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_examen" name="precio_examen" 
                                                   placeholder="Precio examen" step="0.01" min="0" onchange="calcularTotales()"
                                                   value="{{ isset($pedido) && $pedido->examen_visual ? $pedido->examen_visual : '' }}">
                                            <small class="form-text text-muted">IVA: 0% (Exento)</small>
                                            <small class="form-text text-info">Examen visual</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Armazón/Accesorios -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_armazon" name="incluir_armazon" onchange="calcularTotales()" checked>
                                        <label class="form-check-label" for="incluir_armazon">
                                            Armazón/Accesorios
                                        </label>
                                        <div class="mt-2" id="precio_armazon_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_armazon" name="precio_armazon" 
                                                   placeholder="Precio armazón" step="0.01" min="0" onchange="calcularTotales()"
                                                   value="{{ isset($pedido) && $pedido->inventarios ? $pedido->inventarios->sum(function($inv) { return $inv->pivot->precio * (1 - ($inv->pivot->descuento / 100)); }) : '' }}">
                                            <small class="form-text text-muted">IVA: 15%</small>
                                            <small class="form-text text-info">{{ isset($pedido) && $pedido->inventarios ? $pedido->inventarios->count() . ' artículo(s)' : 'Sin artículos' }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lunas -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_luna" name="incluir_luna" onchange="calcularTotales()" checked>
                                        <label class="form-check-label" for="incluir_luna">
                                            Lunas
                                        </label>
                                        <div class="mt-2" id="precio_luna_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_luna" name="precio_luna" 
                                                   placeholder="Precio luna" step="0.01" min="0" onchange="calcularTotales()"
                                                   value="{{ isset($pedido) && $pedido->lunas ? $pedido->lunas->sum(function($luna) { return $luna->l_precio * (1 - ($luna->l_precio_descuento / 100)); }) : '' }}">
                                            <small class="form-text text-muted">IVA: 15%</small>
                                            <small class="form-text text-info">{{ isset($pedido) && $pedido->lunas ? $pedido->lunas->count() . ' luna(s)' : 'Sin lunas' }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Compra Rápida -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_compra_rapida" name="incluir_compra_rapida" onchange="calcularTotales()" checked>
                                        <label class="form-check-label" for="incluir_compra_rapida">
                                            Compra Rápida
                                        </label>
                                        <div class="mt-2" id="precio_compra_rapida_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_compra_rapida" name="precio_compra_rapida" 
                                                   placeholder="Precio compra rápida" step="0.01" min="0" onchange="calcularTotales()"
                                                   value="{{ isset($pedido) && $pedido->valor_compra ? $pedido->valor_compra : '' }}">
                                            <small class="form-text text-muted">IVA: 0% (Exento)</small>
                                            <small class="form-text text-info">{{ isset($pedido) && $pedido->motivo_compra ? $pedido->motivo_compra : 'Servicio de compra rápida' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
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
                    <div class="info-box bg-warning">
                        <div class="info-box-content">
                            <span class="info-box-text">IVA</span>
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
                            <i class="fas fa-file-alt"></i> Generar Factura (XML)
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
        // Mostrar/ocultar campos de precio según los checkboxes
        $('#incluir_examen').on('change', function() {
            $('#precio_examen_container').toggle(this.checked);
            if (!this.checked) {
                $('#precio_examen').val('{{ isset($pedido) && $pedido->examen_visual ? $pedido->examen_visual : "" }}');
            }
            calcularTotales();
        });
        
        $('#incluir_armazon').on('change', function() {
            $('#precio_armazon_container').toggle(this.checked);
            if (!this.checked) {
                $('#precio_armazon').val('');
            }
            calcularTotales();
        });
        
        $('#incluir_luna').on('change', function() {
            $('#precio_luna_container').toggle(this.checked);
            if (!this.checked) {
                $('#precio_luna').val('');
            }
            calcularTotales();
        });
        
        $('#incluir_compra_rapida').on('change', function() {
            $('#precio_compra_rapida_container').toggle(this.checked);
            if (!this.checked) {
                $('#precio_compra_rapida').val('{{ isset($pedido) && $pedido->valor_compra ? $pedido->valor_compra : "0" }}');
            }
            calcularTotales();
        });
        
        // Función para calcular totales según las reglas de IVA
        function calcularTotales() {
            let subtotal = 0;
            let iva = 0;
            let total = 0;
            
            // Examen Visual - 0% IVA (exento)
            const precioExamen = parseFloat($('#precio_examen').val()) || 0;
            if ($('#incluir_examen').is(':checked') && precioExamen > 0) {
                subtotal += precioExamen;
                // No se suma IVA para examen visual (0%)
            }
            
            // Armazón - 15% IVA
            const precioArmazon = parseFloat($('#precio_armazon').val()) || 0;
            if ($('#incluir_armazon').is(':checked') && precioArmazon > 0) {
                subtotal += precioArmazon;
                iva += precioArmazon * 0.15;
            }
            
            // Luna - 15% IVA
            const precioLuna = parseFloat($('#precio_luna').val()) || 0;
            if ($('#incluir_luna').is(':checked') && precioLuna > 0) {
                subtotal += precioLuna;
                iva += precioLuna * 0.15;
            }
            
            // Compra Rápida - 0% IVA (exento)
            const precioCompraRapida = parseFloat($('#precio_compra_rapida').val()) || 0;
            if ($('#incluir_compra_rapida').is(':checked') && precioCompraRapida >= 0) {
                subtotal += precioCompraRapida;
                // No se suma IVA para compra rápida (0%)
            }
            
            total = subtotal + iva;
            
            // Mostrar valores calculados
            $('#subtotalCalculado').text('$' + subtotal.toFixed(2));
            $('#ivaCalculado').text('$' + iva.toFixed(2));
            $('#totalCalculado').text('$' + total.toFixed(2));
        }
        
        // Enviar formulario de factura
        $('#facturaForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validar que al menos un elemento esté seleccionado
            if (!$('#incluir_examen').is(':checked') && 
                !$('#incluir_armazon').is(':checked') && 
                !$('#incluir_luna').is(':checked') && 
                !$('#incluir_compra_rapida').is(':checked')) {
                
                Swal.fire({
                    title: 'Error',
                    text: 'Debe seleccionar al menos un elemento para facturar.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validar que hay un declarante seleccionado
            if (!$('#declarante_id').val()) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe seleccionar un declarante.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Limpiar errores previos
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Deshabilitar botón de guardar
            $('#guardarFactura').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando XML...');
            
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
                        // Mostrar mensaje de éxito con información del XML
                        Swal.fire({
                            title: '¡Éxito!',
                            html: `
                                <p>${response.message}</p>
                                <p><strong>Archivo XML:</strong> ${response.data.xml_path}</p>
                                <p><strong>Subtotal:</strong> $${response.data.subtotal.toFixed(2)}</p>
                                <p><strong>IVA:</strong> $${response.data.iva.toFixed(2)}</p>
                                <p><strong>Total:</strong> $${response.data.total.toFixed(2)}</p>
                            `,
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
                        $('#guardarFactura').prop('disabled', false).html('<i class="fas fa-file-alt"></i> Generar Factura (XML)');
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
                            text: 'Ha ocurrido un error al generar la factura.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    
                    // Habilitar botón de guardar
                    $('#guardarFactura').prop('disabled', false).html('<i class="fas fa-file-alt"></i> Generar Factura (XML)');
                }
            });
        });
        
        // Inicializar cálculos
        calcularTotales();
        
        // Recalcular después de cargar los datos
        setTimeout(calcularTotales, 100);
    });
</script>
@stop
