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
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h5><i class="fas fa-receipt"></i> Información del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Número de Orden:</strong><br>
                            <span class="h4 text-primary">{{ $pedido->numero_orden }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Cliente:</strong><br>
                            {{ $pedido->cliente }}
                        </div>
                        <div class="col-md-3">
                            <strong>Cédula:</strong><br>
                            {{ $pedido->cedula ?? 'No especificado' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total del Pedido:</strong><br>
                            ${{ number_format($pedido->total, 2) }}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>Medio de Pago Original:</strong><br>
                            @if($pedido->pagos && $pedido->pagos->count() > 0)
                                @php
                                    $primerPago = $pedido->pagos->first();
                                @endphp
                                <span class="badge badge-info">
                                    {{ $primerPago->mediodepago->medio_de_pago ?? 'No especificado' }}
                                </span>
                                <small class="text-muted d-block">
                                    Monto: ${{ number_format($primerPago->pago, 2) }}
                                    @if($pedido->pagos->count() > 1)
                                        <br><em>(+ {{ $pedido->pagos->count() - 1 }} pago(s) adicional(es))</em>
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">Sin pagos registrados</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha del Pedido:</strong><br>
                            {{ $pedido->fecha ? $pedido->fecha->format('d/m/Y') : 'No especificada' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Correo Electrónico:</strong><br>
                            {{ $pedido->correo_electronico ?? 'No especificado' }}
                            @if(!$pedido->correo_electronico)
                                <br><small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Sin correo registrado - No se enviará XML por email
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="medio_pago_xml">Medio de Pago para XML <span class="text-danger">*</span></label>
                                <select id="medio_pago_xml" name="medio_pago_xml" class="form-control" required>
                                    <option value="">Seleccione un medio de pago</option>
                                    @foreach ($mediosPago as $medio)
                                        <option value="{{ $medio->id }}" 
                                            @if($pedido->pagos && $pedido->pagos->count() > 0 && $pedido->pagos->first()->mediodepago_id == $medio->id) 
                                                selected 
                                            @endif>
                                            {{ $medio->medio_de_pago }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Este medio de pago se incluirá en el XML de la factura
                                </small>
                                <div class="invalid-feedback" id="medio_pago_xml-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correo_cliente">Correo Electrónico del Cliente</label>
                                <input type="email" 
                                       id="correo_cliente" 
                                       name="correo_cliente" 
                                       class="form-control" 
                                       value="{{ $pedido->correo_electronico ?? '' }}"
                                       placeholder="cliente@ejemplo.com">
                                <small class="form-text text-muted">
                                    <i class="fas fa-envelope"></i> 
                                    Se enviará la factura a este correo
                                </small>
                                <div class="invalid-feedback" id="correo_cliente-error"></div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Este número de orden se usará como secuencial en la clave de acceso del SRI
                    </small>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No se ha especificado un pedido para facturar.
                <br><small>Se generará un secuencial automático para la clave de acceso del SRI.</small>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="medio_pago_xml">Medio de Pago para XML <span class="text-danger">*</span></label>
                        <select id="medio_pago_xml" name="medio_pago_xml" class="form-control" required>
                            <option value="">Seleccione un medio de pago</option>
                            @foreach ($mediosPago as $medio)
                                <option value="{{ $medio->id }}">{{ $medio->medio_de_pago }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Este medio de pago se incluirá en el XML de la factura
                        </small>
                        <div class="invalid-feedback" id="medio_pago_xml-error"></div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="row">
                <div class="col-md-8">
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="password_certificado">
                            <i class="fas fa-lock text-warning"></i> Contraseña del Certificado Digital <span class="text-danger">*</span>
                        </label>
                        <input type="password" id="password_certificado" name="password_certificado" class="form-control" 
                               placeholder="Ingrese la contraseña de su certificado P12">
                        <small class="form-text text-info">
                            <i class="fas fa-shield-alt"></i> Su certificado personal del declarante seleccionado.<br>
                            <i class="fas fa-info-circle"></i> No se almacena - se usa solo para esta factura.
                        </small>
                        <div class="invalid-feedback" id="password_certificado-error"></div>
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
                                        <input class="form-check-input" type="checkbox" id="incluir_examen" name="incluir_examen" checked>
                                        <label class="form-check-label" for="incluir_examen">
                                            Examen Visual
                                        </label>
                                        <div class="mt-2" id="precio_examen_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_examen" name="precio_examen" 
                                                   placeholder="Precio examen" step="0.01" min="0"
                                                   value="{{ isset($pedido) && $pedido->examen_visual ? $pedido->examen_visual : '' }}">
                                            <small class="form-text text-muted">IVA: 0% (Exento)</small>
                                            <small class="form-text text-info">Examen visual</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Armazón/Accesorios -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_armazon" name="incluir_armazon" checked>
                                        <label class="form-check-label" for="incluir_armazon">
                                            Armazón/Accesorios
                                        </label>
                                        <div class="mt-2" id="precio_armazon_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_armazon" name="precio_armazon" 
                                                   placeholder="Precio armazón" step="0.01" min="0"
                                                   value="{{ isset($pedido) && $pedido->inventarios ? $pedido->inventarios->sum(function($inv) { return $inv->pivot->precio * (1 - ($inv->pivot->descuento / 100)); }) : '' }}">
                                            <small class="form-text text-muted">IVA: 15% (incluido en el precio)</small>
                                            <small class="form-text text-info">
                                                <strong>Lo que ingresa:</strong> Suma total con IVA incluido<br>
                                                <span id="desglose_armazon" style="display: none;">
                                                    <strong>Subtotal:</strong> $<span id="subtotal_armazon">0.00</span> | 
                                                    <strong>IVA:</strong> $<span id="iva_armazon">0.00</span> | 
                                                    <strong>Total:</strong> $<span id="total_armazon">0.00</span>
                                                </span>
                                            </small>
                                            <small class="form-text text-info">{{ isset($pedido) && $pedido->inventarios ? $pedido->inventarios->count() . ' artículo(s)' : 'Sin artículos' }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lunas -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_luna" name="incluir_luna" checked>
                                        <label class="form-check-label" for="incluir_luna">
                                            Lunas
                                        </label>
                                        <div class="mt-2" id="precio_luna_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_luna" name="precio_luna" 
                                                   placeholder="Precio luna" step="0.01" min="0"
                                                   value="{{ isset($pedido) && $pedido->lunas ? $pedido->lunas->sum(function($luna) { return $luna->l_precio * (1 - ($luna->l_precio_descuento / 100)); }) : '' }}">
                                            <small class="form-text text-muted">IVA: 15% (incluido en el precio)</small>
                                            <small class="form-text text-info">
                                                <strong>Lo que ingresa:</strong> Suma total con IVA incluido<br>
                                                <span id="desglose_luna" style="display: none;">
                                                    <strong>Subtotal:</strong> $<span id="subtotal_luna">0.00</span> | 
                                                    <strong>IVA:</strong> $<span id="iva_luna">0.00</span> | 
                                                    <strong>Total:</strong> $<span id="total_luna">0.00</span>
                                                </span>
                                            </small>
                                            <small class="form-text text-info">{{ isset($pedido) && $pedido->lunas ? $pedido->lunas->count() . ' luna(s)' : 'Sin lunas' }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Compra Rápida -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluir_compra_rapida" name="incluir_compra_rapida" checked>
                                        <label class="form-check-label" for="incluir_compra_rapida">
                                            Compra Rápida
                                        </label>
                                        <div class="mt-2" id="precio_compra_rapida_container">
                                            <input type="number" class="form-control form-control-sm" id="precio_compra_rapida" name="precio_compra_rapida" 
                                                   placeholder="Precio compra rápida" step="0.01" min="0"
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        // Agregar event listeners para los inputs de precio
        $('#precio_examen, #precio_armazon, #precio_luna, #precio_compra_rapida').on('input change', function() {
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
            
            // Armazón - 15% IVA (incluido en el precio, se extrae)
            const precioArmazonConIva = parseFloat($('#precio_armazon').val()) || 0;
            if ($('#incluir_armazon').is(':checked') && precioArmazonConIva > 0) {
                const precioArmazonSinIva = precioArmazonConIva / 1.15;
                const ivaArmazon = precioArmazonSinIva * 0.15;
                subtotal += precioArmazonSinIva;
                iva += ivaArmazon;
                
                // Mostrar desglose individual del armazón
                $('#subtotal_armazon').text(precioArmazonSinIva.toFixed(2));
                $('#iva_armazon').text(ivaArmazon.toFixed(2));
                $('#total_armazon').text(precioArmazonConIva.toFixed(2));
                $('#desglose_armazon').show();
            } else {
                $('#desglose_armazon').hide();
            }
            
            // Luna - 15% IVA (incluido en el precio, se extrae)
            const precioLunaConIva = parseFloat($('#precio_luna').val()) || 0;
            if ($('#incluir_luna').is(':checked') && precioLunaConIva > 0) {
                const precioLunaSinIva = precioLunaConIva / 1.15;
                const ivaLuna = precioLunaSinIva * 0.15;
                subtotal += precioLunaSinIva;
                iva += ivaLuna;
                
                // Mostrar desglose individual de la luna
                $('#subtotal_luna').text(precioLunaSinIva.toFixed(2));
                $('#iva_luna').text(ivaLuna.toFixed(2));
                $('#total_luna').text(precioLunaConIva.toFixed(2));
                $('#desglose_luna').show();
            } else {
                $('#desglose_luna').hide();
            }
            
            // Compra Rápida - 0% IVA (exento)
            const precioCompraRapida = parseFloat($('#precio_compra_rapida').val()) || 0;
            if ($('#incluir_compra_rapida').is(':checked') && precioCompraRapida >= 0) {
                subtotal += precioCompraRapida;
                // No se suma IVA para compra rápida (0%)
            }
            
            total = subtotal + iva;
            
            // Mostrar valores calculados totales
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
            
            // Validar que hay contraseña del certificado (solo si es requerida)
            const declaranteId = $('#declarante_id').val();
            const tienePasswordGuardada = declaranteId && declarantesPasswords[declaranteId];
            
            if (!tienePasswordGuardada && !$('#password_certificado').val()) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe ingresar la contraseña del certificado.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validar que hay un medio de pago seleccionado
            if (!$('#medio_pago_xml').val()) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe seleccionar un medio de pago para el XML.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Limpiar errores previos
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Deshabilitar botón de guardar
            $('#guardarFactura').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando con Python API...');
            
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
                        // Detectar si hubo reintento automático
                        let mensajeReintento = '';
                        let iconoMensaje = 'success';
                        
                        if (response.message && response.message.includes('reintento automático')) {
                            mensajeReintento = `
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Información:</strong> El SRI rechazó el primer intento por número secuencial duplicado. 
                                    El sistema generó automáticamente un nuevo secuencial único y la factura fue procesada exitosamente.
                                </div>
                            `;
                            iconoMensaje = 'warning';
                        }
                        
                        // Mostrar mensaje de éxito con información del XML
                        Swal.fire({
                            title: '¡Éxito!',
                            html: `
                                <p>${response.message}</p>
                                ${mensajeReintento}
                                <div class="text-left mt-3">
                                    <p><strong>Subtotal:</strong> $${response.data.subtotal.toFixed(2)}</p>
                                    <p><strong>IVA:</strong> $${response.data.iva.toFixed(2)}</p>
                                    <p><strong>Total:</strong> $${response.data.total.toFixed(2)}</p>
                                    ${response.data.clave_acceso ? `<p><strong>Clave de Acceso:</strong> ${response.data.clave_acceso}</p>` : ''}
                                    ${response.data.estado ? `<p><strong>Estado:</strong> <span class="badge badge-info">${response.data.estado}</span></p>` : ''}
                                </div>
                            `,
                            icon: iconoMensaje,
                            confirmButtonText: 'Ver Factura',
                            width: '600px'
                        }).then((result) => {
                            // Redirigir a la vista show de la factura creada
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                window.location.href = "{{ route('facturas.index') }}";
                            }
                        });
                    } else {
                        // Mostrar mensaje de error
                        let mensajeError = response.message;
                        let detallesError = '';
                        
                        // Si hay información específica del SRI
                        if (response.errors && Array.isArray(response.errors)) {
                            detallesError = '<br><br><strong>Detalles:</strong><ul>';
                            response.errors.forEach(function(error) {
                                detallesError += `<li>${error}</li>`;
                            });
                            detallesError += '</ul>';
                        }
                        
                        Swal.fire({
                            title: 'Error al Procesar Factura',
                            html: mensajeError + detallesError,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            width: '500px'
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
        
        // Información de contraseñas guardadas por declarante
        const declarantesPasswords = @json($declarantesPasswords);
        
        // Función para mostrar/ocultar campo de contraseña
        function togglePasswordField() {
            const declaranteId = $('#declarante_id').val();
            const passwordField = $('#password_certificado');
            const passwordGroup = passwordField.closest('.form-group');
            
            if (declaranteId && declarantesPasswords[declaranteId]) {
                // El declarante tiene contraseña guardada - ocultar campo
                passwordGroup.hide();
                passwordField.removeAttr('required').val(''); // Quitar required y limpiar valor
                $('#password_certificado-info').remove(); // Quitar mensaje anterior si existe
                
                // Agregar mensaje informativo
                passwordGroup.after(`
                    <div id="password_certificado-info" class="alert alert-info">
                        <i class="fas fa-check-circle text-success"></i> 
                        <strong>Contraseña del certificado:</strong> Ya está guardada para este declarante.
                        <br><small class="text-muted">No es necesario ingresarla nuevamente.</small>
                    </div>
                `);
            } else if (declaranteId) {
                // El declarante NO tiene contraseña guardada - mostrar campo
                passwordGroup.show();
                passwordField.attr('required', 'required'); // Agregar required
                $('#password_certificado-info').remove(); // Quitar mensaje informativo
            } else {
                // No hay declarante seleccionado - ocultar campo
                passwordGroup.hide();
                passwordField.removeAttr('required').val('');
                $('#password_certificado-info').remove();
            }
        }
        
        // Ejecutar al cambiar declarante
        $('#declarante_id').on('change', togglePasswordField);
        
        // Ejecutar al cargar la página
        $(document).ready(function() {
            togglePasswordField();
        });
        
        // Inicializar cálculos
        calcularTotales();
        
        // Recalcular después de cargar los datos
        setTimeout(calcularTotales, 100);
    });
</script>
@stop
