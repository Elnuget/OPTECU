function calculateTotal() {
    try {
        console.log('=== INICIANDO CÁLCULO DE TOTAL (ESTILO CREATE) ===');
        
        let total = 0;

        // Examen visual
        const examenVisual = parseFloat(document.getElementById('examen_visual')?.value) || 0;
        const examenVisualDescuento = parseFloat(document.getElementById('examen_visual_descuento')?.value) || 0;
        const examenVisualTotal = examenVisual * (1 - (examenVisualDescuento / 100));
        total += examenVisualTotal;
        console.log('Examen visual:', examenVisual, 'descuento:', examenVisualDescuento, 'total:', examenVisualTotal);

        // Armazones - incluir tanto el original como los campos añadidos
        const armazonPrecios = document.querySelectorAll('[name="a_precio"], [name="a_precio[]"]');
        const armazonDescuentos = document.querySelectorAll('[name="a_precio_descuento"], [name="a_precio_descuento[]"]');
        console.log('Armazones encontrados:', armazonPrecios.length);
        
        armazonPrecios.forEach((precio, index) => {
            const precioValue = parseFloat(precio.value) || 0;
            const descuento = parseFloat(armazonDescuentos[index]?.value) || 0;
            total += precioValue * (1 - (descuento / 100));
            console.log(`Armazón ${index}: precio=${precioValue}, descuento=${descuento}%, aplicado=${precioValue * (1 - (descuento / 100))}`);
        });

        // Lunas - incluir tanto el original como los campos añadidos
        const lunasPrecios = document.querySelectorAll('[name="l_precio"], [name="l_precio[]"]');
        const lunasDescuentos = document.querySelectorAll('[name="l_precio_descuento"], [name="l_precio_descuento[]"]');
        console.log('Lunas encontradas:', lunasPrecios.length);
        
        lunasPrecios.forEach((precio, index) => {
            const precioValue = parseFloat(precio.value) || 0;
            const descuento = parseFloat(lunasDescuentos[index]?.value) || 0;
            total += precioValue * (1 - (descuento / 100));
            console.log(`Luna ${index}: precio=${precioValue}, descuento=${descuento}%, aplicado=${precioValue * (1 - (descuento / 100))}`);
        });

        // Accesorios - incluir tanto el original como los campos añadidos
        const accesoriosPrecios = document.querySelectorAll('[name="d_precio"], [name="d_precio[]"]');
        const accesoriosDescuentos = document.querySelectorAll('[name="d_precio_descuento"], [name="d_precio_descuento[]"]');
        console.log('Accesorios encontrados:', accesoriosPrecios.length);
        
        accesoriosPrecios.forEach((precio, index) => {
            const precioValue = parseFloat(precio.value) || 0;
            const descuento = parseFloat(accesoriosDescuentos[index]?.value) || 0;
            total += precioValue * (1 - (descuento / 100));
            console.log(`Accesorio ${index}: precio=${precioValue}, descuento=${descuento}%, aplicado=${precioValue * (1 - (descuento / 100))}`);
        });

        // Valor compra
        const valorCompra = parseFloat(document.getElementById('valor_compra')?.value) || 0;
        total += valorCompra;
        console.log('Valor compra:', valorCompra);

        console.log('TOTAL CALCULADO ANTES DE FORMATEAR:', total);

        // Obtener el total pagado para calcular saldo
        const totalPagadoElement = document.getElementById('total_pagado');
        const totalPagado = totalPagadoElement ? parseFloat(totalPagadoElement.value) || 0 : 0;
        console.log('Total pagado:', totalPagado);

        // Calcular saldo pendiente
        const saldoPendiente = Math.max(0, total - totalPagado);
        console.log('Saldo pendiente calculado:', saldoPendiente);

        // Actualizar campos - NO usar toFixed para evitar redondeo
        const totalElement = document.getElementById('total');
        const saldoElement = document.getElementById('saldo');
        
        if (totalElement) {
            console.log('ANTES de asignar - total calculado:', total);
            console.log('ANTES de asignar - valor actual del input:', totalElement.value);
            
            // Forzar el valor exacto de múltiples maneras
            totalElement.value = total.toString();
            totalElement.setAttribute('value', total.toString());
            
            // Verificar inmediatamente después
            setTimeout(() => {
                if (totalElement.value != total.toString()) {
                    console.log('ADVERTENCIA: El valor cambió automáticamente!');
                    console.log('Valor esperado:', total.toString());
                    console.log('Valor actual:', totalElement.value);
                    // Forzar de nuevo
                    totalElement.value = total.toString();
                }
            }, 10);
            
            console.log('DESPUÉS de asignar - valor en el input:', totalElement.value);
            console.log('DESPUÉS de asignar - tipo de valor:', typeof totalElement.value);
        }
        if (saldoElement) {
            console.log('ANTES de asignar - saldo calculado:', saldoPendiente);
            console.log('ANTES de asignar - valor actual del saldo:', saldoElement.value);
            
            saldoElement.value = saldoPendiente.toString();
            saldoElement.setAttribute('value', saldoPendiente.toString());
            
            console.log('DESPUÉS de asignar - valor del saldo:', saldoElement.value);
        }
        
        console.log('=== FIN CÁLCULO DE TOTAL ===');
    } catch (error) {
        console.error('Error al calcular el total:', error);
    }
}

function duplicateLunas() {
    const container = document.querySelector('#lunas-container .card-body');
    const template = `
        <div class="luna-section mt-4">
            <hr>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm remove-luna" onclick="this.closest('.luna-section').remove(); calculateTotal();">
                    <i class="fas fa-times"></i> Eliminar
                </button>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Prescripción/Medidas de Lunas</label>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th width="10%">Ojo</th>
                                    <th width="20%">Esfera</th>
                                    <th width="20%">Cilindro</th>
                                    <th width="15%">Eje</th>
                                    <th width="15%">ADD</th>
                                    <th width="20%">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="align-middle text-center"><strong>OD</strong></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="od_esfera[]" placeholder="Ej: +2.00"></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="od_cilindro[]" placeholder="Ej: -1.50"></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="od_eje[]" placeholder="Ej: 90°"></td>
                                    <td rowspan="2" class="align-middle">
                                        <input type="text" class="form-control form-control-sm medida-input" name="add[]" placeholder="Ej: +2.00">
                                    </td>
                                    <td rowspan="2" class="align-middle">
                                        <textarea class="form-control form-control-sm" name="l_detalle[]" rows="3" placeholder="Detalles adicionales"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="align-middle text-center"><strong>OI</strong></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="oi_esfera[]" placeholder="Ej: +1.75"></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="oi_cilindro[]" placeholder="Ej: -1.25"></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="oi_eje[]" placeholder="Ej: 85°"></td>
                                </tr>
                                <tr>
                                    <td class="text-center"><strong>DP</strong></td>
                                    <td><input type="text" class="form-control form-control-sm medida-input" name="dp[]" placeholder="Ej: 62"></td>
                                    <td colspan="4">
                                        <input type="hidden" name="l_medida[]" class="l-medida-hidden">
                                        <small class="text-muted">Distancia Pupilar</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Formato de ejemplo:</strong> OD: +2.00 -1.50 X90° / OI: +1.75 -1.25 X85° ADD: +2.00 DP: 62
                    </small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Lente</label>
                    <input type="text" class="form-control" name="tipo_lente[]" 
                           list="tipo_lente_options" value=""
                           placeholder="Seleccione o escriba un tipo de lente">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Material</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-sm">OD (Ojo Derecho)</label>
                            <input type="text" class="form-control form-control-sm material-input" name="material_od[]" list="material_options" placeholder="Material OD">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-sm">OI (Ojo Izquierdo)</label>
                            <input type="text" class="form-control form-control-sm material-input" name="material_oi[]" list="material_options" placeholder="Material OI">
                        </div>
                    </div>
                    <input type="hidden" name="material[]" class="material-hidden">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filtro</label>
                    <input type="text" class="form-control" name="filtro[]" 
                           list="filtro_options" value=""
                           placeholder="Seleccione o escriba un filtro">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Precio Lunas</label>
                    <input type="number" class="form-control input-sm" name="l_precio[]"
                           value="0" step="0.01" oninput="calculateTotal()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Desc. Lunas (%)</label>
                    <input type="number" class="form-control input-sm" name="l_precio_descuento[]"
                           value="0" min="0" max="100" oninput="calculateTotal()">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto Lunas (Opcional)</label>
                    <input type="file" class="form-control form-control-sm" name="l_foto[]" accept="image/*">
                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
    
    // Agregar event listeners para los nuevos campos de medidas
    setTimeout(() => {
        if (typeof agregarEventListenersMedidas === 'function') {
            agregarEventListenersMedidas();
        }
        if (typeof agregarEventListenersMaterial === 'function') {
            agregarEventListenersMaterial();
        }
    }, 100);
}

function duplicateArmazon() {
    console.log('Función duplicateArmazon llamada');
    const container = document.getElementById('armazones-container');
    if (!container) {
        console.error('No se encontró el contenedor de armazones');
        return;
    }

    // Obtener el primer select con los datos actualizados
    const firstSelect = document.querySelector('[name="a_inventario_id[]"]');
    if (!firstSelect) {
        console.error('No se encontró el primer select de inventario');
        return;
    }

    // Crear un nuevo elemento select y copiar las opciones del primero
    const options = Array.from(firstSelect.options).map(opt => {
        return `<option value="${opt.value}">${opt.text}</option>`;
    }).join('');

    // Obtener el mes y año actual
    const currentDate = new Date();
    const currentMonth = currentDate.toLocaleString('es-ES', { month: 'long' });
    const currentYear = currentDate.getFullYear();

    // Determinar si hay opciones disponibles
    const hasOptions = firstSelect.options.length > 1; // Considerando que siempre hay una opción vacía
    const optionsCount = firstSelect.options.length - 1;

    const template = `
        <div class="armazon-section mb-3">
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <label>Armazón o Accesorio (${currentMonth} ${currentYear})</label>
                    <select name="a_inventario_id[]" class="form-control selectpicker" 
                        data-live-search="true"
                        title="Seleccione un armazón o accesorio">
                        <option value="">Seleccione un armazón o accesorio</option>
                        ${options}
                    </select>
                    ${hasOptions ? 
                        `<small class="form-text text-muted">${optionsCount} artículo(s) disponible(s)</small>` : 
                        `<div class="text-danger mt-1">
                            <small><i class="fas fa-exclamation-triangle"></i> No hay artículos disponibles para este mes</small>
                         </div>`
                    }
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Precio</label>
                    <input type="number" name="a_precio[]" class="form-control" 
                        value="0" step="0.01" oninput="calculateTotal()">
                </div>
                <div class="col-md-6">
                    <label>Descuento (%)</label>
                    <input type="number" name="a_precio_descuento[]" class="form-control" 
                        value="0" min="0" max="100" oninput="calculateTotal()">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-danger btn-sm remove-armazon">
                        <i class="fas fa-times"></i> Eliminar Armazón o Accesorio
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Insertar el template
    container.insertAdjacentHTML('beforeend', template);
    console.log('Template insertado');

    // Inicializar el nuevo selectpicker
    try {
        $('.selectpicker').selectpicker('refresh');
        console.log('Selectpickers refrescados');
    } catch (error) {
        console.error('Error al refrescar selectpickers:', error);
    }

    // Mostrar notificación de éxito
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: 'Se ha agregado un nuevo armazón/accesorio',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

// Función para restaurar el inventario
async function restaurarInventario(inventarioId) {
    try {
        const response = await fetch(`/api/inventario/restaurar/${inventarioId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('Error al restaurar el inventario');
        }
        
        const result = await response.json();
        
        // Mostrar notificación de éxito
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Se ha restaurado la unidad al inventario',
            timer: 1500,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
        
        return result;
    } catch (error) {
        console.error('Error:', error);
        // Mostrar notificación de error
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al restaurar la unidad al inventario',
            timer: 2000,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
        throw error;
    }
}

// Event Listeners - Simplificados como en create
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Configurando event listeners como en create');

    // Event listeners para precios (campos individuales y arrays)
    ['examen_visual', 'a_precio', 'l_precio', 'd_precio', 'valor_compra'].forEach(id => {
        const element = document.getElementById(id);
        if(element){
            element.addEventListener('input', calculateTotal);
            console.log(`Event listener agregado para: ${id}`);
        }
    });

    // Event listeners para descuentos (campos individuales y arrays)
    ['examen_visual_descuento', 'a_precio_descuento', 'l_precio_descuento', 'd_precio_descuento'].forEach(id => {
        const element = document.getElementById(id);
        if(element){
            element.addEventListener('input', calculateTotal);
            console.log(`Event listener agregado para descuento: ${id}`);
        }
    });

    // Event delegation para campos dinámicos (arrays)
    const armazonesContainer = document.getElementById('armazones-container');
    if (armazonesContainer) {
        armazonesContainer.addEventListener('input', function(e) {
            if (e.target.matches('[name="a_precio[]"], [name="a_precio_descuento[]"]')) {
                console.log('Cambio en armazón dinámico:', e.target.name, e.target.value);
                calculateTotal();
            }
        });
    }

    const lunasContainer = document.getElementById('lunas-container');
    if (lunasContainer) {
        lunasContainer.addEventListener('input', function(e) {
            if (e.target.matches('[name="l_precio[]"], [name="l_precio_descuento[]"]')) {
                console.log('Cambio en luna dinámica:', e.target.name, e.target.value);
                calculateTotal();
            }
        });
    }

    const accesoriosContainer = document.getElementById('accesorios-container');
    if (accesoriosContainer) {
        accesoriosContainer.addEventListener('input', function(e) {
            if (e.target.matches('[name="d_precio[]"], [name="d_precio_descuento[]"]')) {
                console.log('Cambio en accesorio dinámico:', e.target.name, e.target.value);
                calculateTotal();
            }
        });
    }

    // Calcular total inicial
    setTimeout(() => {
        console.log('Calculando total inicial...');
        
        // Activar detección de redondeo
        detectarRedondeoAutomatico();
        
        calculateTotal();
        
        // Agregar listener para detectar cambios no deseados en el total
        const totalElement = document.getElementById('total');
        if (totalElement) {
            totalElement.addEventListener('change', function() {
                console.log('CAMBIO DETECTADO en total - Nuevo valor:', this.value);
            });
            
            totalElement.addEventListener('input', function() {
                console.log('INPUT DETECTADO en total - Nuevo valor:', this.value);
            });
            
            // Usar MutationObserver para detectar cambios automáticos
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        console.log('MUTACIÓN DETECTADA en value del total:', totalElement.value);
                    }
                });
            });
            
            observer.observe(totalElement, {
                attributes: true,
                attributeOldValue: true
            });
        }
    }, 100);

    // Hacer que todo el header sea clickeable
    document.querySelectorAll('.card-header').forEach(header => {
        header.addEventListener('click', function(e) {
            // Si el clic no fue en un botón dentro del header
            if (!e.target.closest('.btn-tool')) {
                const collapseButton = this.querySelector('.btn-tool');
                if (collapseButton) {
                    collapseButton.click();
                }
            }
        });
    });

    // Inicializar selectpicker solo si no ha sido inicializado en otra parte
    try {
        if ($.fn && $.fn.selectpicker && $('.selectpicker').length > 0 && !$('.selectpicker').hasClass('selectpicker-initialized')) {
            console.log('Inicializando selectpicker desde pedidos.js');
            $('.selectpicker').addClass('selectpicker-initialized');
            $('.selectpicker').selectpicker();
        } else {
            console.log('Selectpicker ya inicializado o no disponible');
        }
    } catch (error) {
        console.error('Error al inicializar selectpicker en pedidos.js:', error);
    }

    // Manejar el botón de agregar armazón (solo si no estamos en modo edición)
    const addButton = document.getElementById('add-armazon');
    if (addButton && !window.editMode) {
        console.log('Botón de agregar encontrado - configurando event listener para crear');
        addButton.addEventListener('click', function(e) {
            console.log('Botón de agregar clickeado - modo crear');
            e.preventDefault();
            duplicateArmazon();
        });
    } else if (addButton && window.editMode) {
        console.log('Botón de agregar encontrado pero estamos en modo edición - saltando configuración');
    } else {
        console.error('No se encontró el botón de agregar armazón');
    }

    // Manejar eliminación de armazones/accesorios
    const container = document.getElementById('armazones-container');
    if (container) {
        console.log('Contenedor de armazones encontrado');
        container.addEventListener('click', async function(e) {
            console.log('Click en el contenedor de armazones');
            const removeButton = e.target.closest('.remove-armazon');
            if (removeButton) {
                console.log('Botón de eliminar clickeado');
                e.preventDefault();
                const section = removeButton.closest('.armazon-section');
                const select = section.querySelector('[name="a_inventario_id[]"]');
                const inventarioId = select ? select.value : null;
                
                if (inventarioId) {
                    try {
                        const result = await Swal.fire({
                            title: '¿Está seguro?',
                            text: "Se eliminará el armazón/accesorio y se restaurará la unidad al inventario",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        });

                        if (result.isConfirmed) {
                            await restaurarInventario(inventarioId);
                            section.remove();
                            calculateTotal();
                        }
                    } catch (error) {
                        console.error('Error al eliminar:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al eliminar el armazón/accesorio',
                            timer: 2000,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }
                } else {
                    section.remove();
                    calculateTotal();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Se ha eliminado el armazón/accesorio',
                        timer: 1500,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                }
            }
        });
    } else {
        console.error('No se encontró el contenedor de armazones');
    }
}); 

// Función para detectar redondeo automático
function detectarRedondeoAutomatico() {
    const totalElement = document.getElementById('total');
    if (!totalElement) return;
    
    // Interceptar cualquier intento de cambiar el valor
    let valorOriginal = totalElement.value;
    
    Object.defineProperty(totalElement, 'value', {
        get: function() {
            return this.getAttribute('value') || '';
        },
        set: function(newValue) {
            console.log('INTENTO DE CAMBIAR VALOR DEL TOTAL:');
            console.log('Valor anterior:', this.getAttribute('value'));
            console.log('Valor nuevo:', newValue);
            console.log('Stack trace:', new Error().stack);
            
            this.setAttribute('value', newValue.toString());
            // Disparar evento personalizado
            this.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });
}

// Solo configurar event listeners básicos
function configurarEventListenersCalculoTotal() {
    // Configurar event listeners para campos dinámicos nuevos
    document.querySelectorAll('[name*="precio"], [name*="descuento"]').forEach(field => {
        if (!field.hasAttribute('data-listener-added')) {
            console.log('Agregando listener a campo:', field.name || field.id);
            field.addEventListener('input', function(e) {
                console.log('CAMBIO en campo:', e.target.name || e.target.id, 'Valor:', e.target.value);
                calculateTotal();
            });
            field.setAttribute('data-listener-added', 'true');
        }
    });
    
    // Verificar si hay listeners no deseados en campos de precio
    document.querySelectorAll('[name*="precio"]').forEach(field => {
        // Clonar el elemento para eliminar todos los event listeners
        if (field.hasAttribute('data-cleaned')) return;
        
        console.log('Verificando listeners en:', field.name || field.id);
        // Marcar como verificado
        field.setAttribute('data-cleaned', 'true');
    });
}

// Ejecutar cada 2 segundos para campos dinámicos
setInterval(configurarEventListenersCalculoTotal, 2000);