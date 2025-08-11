/**
 * Script para manejar los combobox personalizados
 */

// Variable global para controlar si estamos en modo edición
window.editMode = true;

$(document).ready(function() {
    initializeCustomComboboxes();
    
    // Inicializar event listeners para medidas de lunas
    setTimeout(() => {
        agregarEventListenersMedidas();
        agregarEventListenersMaterial();
    }, 500);
    
    // Delegación de eventos para los elementos de la lista desplegable
    $(document).on('click', '.armazon-option', function(e) {
        e.preventDefault();
        
        const $this = $(this);
        const id = $this.data('id');
        const code = $this.data('code');
        const place = $this.data('place');
        const date = $this.data('date');
        const empresa = $this.data('empresa') || 'Sin empresa';
        
        // Construir el texto incluyendo la empresa, o usar el texto completo del elemento si no hay datos individuales
        let text;
        if (code && place && date) {
            text = `${code} - ${place} - ${date} - ${empresa}`;
        } else {
            // Usar el texto completo del elemento (para casos donde se usa inventarioData.items)
            text = $this.text().trim();
        }
        
        // Obtener el grupo de entrada al que pertenece esta opción
        const $inputGroup = $this.closest('.input-group');
        const $input = $inputGroup.find('.armazon-search');
        const $hiddenInput = $inputGroup.find('.armazon-id');
        
        // Guardar el ID anterior para poder restaurar una unidad al inventario
        const anteriorId = $hiddenInput.val();
        
        // Actualizar el valor visible y el ID oculto
        $input.val(text);
        $hiddenInput.val(id);
        
        // Cerrar el dropdown
        $inputGroup.find('.dropdown-menu').removeClass('show');
        
        // Solo actualizar el inventario si estamos en modo edición interactiva, no al cargar el formulario
        if (window.editMode && anteriorId !== id) {
            // Si había un armazón anterior seleccionado, restaurar su unidad
            if (anteriorId) {
                restaurarUnidadInventario(anteriorId);
            }
            
            // Si se seleccionó un nuevo armazón, restar una unidad
            if (id) {
                restarUnidadInventario(id);
            }
        }
    });
    
    // Manejar la apertura/cierre del dropdown
    $(document).on('click', '.armazon-dropdown-btn', function(e) {
        e.preventDefault();
        const $menu = $(this).next('.dropdown-menu');
        $menu.toggleClass('show');
    });
    
    // Cerrar los dropdowns cuando se hace clic fuera de ellos
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.input-group').length) {
            $('.armazon-dropdown').removeClass('show');
        }
    });
    
    // Filtrar las opciones mientras se escribe
    $(document).on('input', '.armazon-search', function() {
        const searchTerm = $(this).val().toLowerCase();
        const $dropdown = $(this).closest('.input-group').find('.armazon-dropdown');
        const $options = $dropdown.find('.armazon-option');
        
        $options.each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Mostrar el dropdown si hay texto
        if (searchTerm.length > 0) {
            $dropdown.addClass('show');
        }
    });
    
    // Inicializar botón para agregar armazón - COMENTADO para evitar conflictos con edit.blade.php
    // $('#add-armazon').on('click', function(e) {
    //     e.preventDefault();
    //     addArmazon();
    // });
    
    // Delegación de eventos para eliminar armazón
    $('#armazones-container').on('click', '.remove-armazon', function(e) {
        e.preventDefault();
        
        // Obtener el ID del armazón a eliminar
        const $hiddenInput = $(this).closest('.armazon-section').find('.armazon-id');
        const inventarioId = $hiddenInput.val();
        
        // Restaurar unidad al inventario si hay un ID válido y estamos en modo edición
        if (window.editMode && inventarioId) {
            restaurarUnidadInventario(inventarioId);
        }
        
        // Eliminar la sección del armazón
        $(this).closest('.armazon-section').remove();
        
        // Recalcular total
        calculateTotal();
        
        // Mostrar mensaje de éxito
        Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'Armazón eliminado correctamente',
            timer: 1500,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
    });
    
    // Agregar evento al formulario para desactivar editMode al enviar
    $('form').on('submit', function() {
        window.editMode = false;
        console.log('Formulario enviado - editMode desactivado');
        return true;
    });
});

// Inicializar los combobox personalizados existentes
function initializeCustomComboboxes() {
    $('.armazon-search').each(function() {
        const $this = $(this);
        const selectedId = $this.data('selected-id');
        
        // Guardar el ID inicial como atributo de datos en el input oculto
        if (selectedId) {
            $this.closest('.input-group').find('.armazon-id').data('anterior-id', selectedId);
        }
    });
    
    console.log('Comboboxes personalizados inicializados - editMode:', window.editMode);
}

// Función global para añadir un nuevo armazón
window.addArmazon = function() {
    console.log('window.addArmazon() iniciada');
    
    const container = document.getElementById('armazones-container');
    if (!container) {
        console.error('No se encontró el contenedor armazones-container');
        return;
    }
    console.log('Contenedor encontrado:', container);
    
    let options = '';
    
    // Intentar obtener opciones del primer dropdown existente
    const firstDropdown = document.querySelector('.armazon-dropdown');
    if (firstDropdown) {
        console.log('Primer dropdown encontrado, copiando opciones');
        options = Array.from(firstDropdown.querySelectorAll('.armazon-option')).map(opt => {
            const id = opt.getAttribute('data-id');
            const code = opt.getAttribute('data-code');
            const place = opt.getAttribute('data-place');
            const date = opt.getAttribute('data-date');
            const empresa = opt.getAttribute('data-empresa') || 'Sin empresa';
            const text = opt.textContent.trim();
            
            return `<a class="dropdown-item armazon-option" href="#" 
                      data-id="${id}" 
                      data-code="${code}"
                      data-place="${place}"
                      data-date="${date}"
                      data-empresa="${empresa}">
                      ${text}
                   </a>`;
        }).join('');
        } else {
            console.log('No se encontró dropdown existente, usando datos de window.inventarioData');
            
            // Usar los datos filtrados si están disponibles (modo edición con empresa seleccionada)
            let datosParaUsar = window.inventarioData?.items || [];
            if (window.inventarioData?.itemsFiltrados) {
                datosParaUsar = window.inventarioData.itemsFiltrados;
                console.log('Usando datos filtrados:', datosParaUsar.length, 'items');
            }
            
            // Usar los nuevos datos con información de empresa
            if (datosParaUsar.length > 0) {
                options = datosParaUsar.map(item => {
                    return `<a class="dropdown-item armazon-option" href="#" 
                              data-id="${item.id}">
                              ${item.display}
                           </a>`;
                }).join('');
            } else if (window.inventarioItems && window.inventarioItems.length > 0) {
                // Fallback a los datos originales (sin empresa)
                console.log('Usando fallback a inventarioItems sin empresa');
                options = window.inventarioItems.map(item => {
                    const fecha = item.fecha ? new Date(item.fecha).toLocaleDateString('es-ES') : 'Sin fecha';
                    const empresa = item.empresa ? item.empresa.nombre : 'Sin empresa';
                    const text = `${item.codigo} - ${item.lugar} - ${fecha} - ${empresa}`;
                    
                    return `<a class="dropdown-item armazon-option" href="#" 
                              data-id="${item.id}" 
                              data-code="${item.codigo}"
                              data-place="${item.lugar}"
                              data-date="${fecha}"
                              data-empresa="${empresa}">
                              ${text}
                           </a>`;
                }).join('');
            } else {
                console.warn('No hay items de inventario disponibles');
                options = '<a class="dropdown-item disabled" href="#">No hay artículos disponibles</a>';
            }
        }    // Obtener información del mes y año
    const nombresMeses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    let mesTexto, anoTexto;
    if (window.filtroMes && window.filtroAno) {
        mesTexto = nombresMeses[window.filtroMes - 1] || 'Mes actual';
        anoTexto = window.filtroAno;
    } else {
        const currentDate = new Date();
        mesTexto = nombresMeses[currentDate.getMonth()];
        anoTexto = currentDate.getFullYear();
    }
    
    // Determinar qué datos usar
    let datosParaUsar = [];
    if (window.inventarioData?.itemsFiltrados) {
        datosParaUsar = window.inventarioData.itemsFiltrados;
    } else if (window.inventarioData?.items) {
        datosParaUsar = window.inventarioData.items;
    } else if (window.inventarioItems) {
        datosParaUsar = window.inventarioItems;
    }
    
    const hasOptions = datosParaUsar.length > 0;
    const optionsCount = hasOptions ? datosParaUsar.length : 0;

    const template = `
        <div class="armazon-section mb-3">
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <label>Armazón o Accesorio (${mesTexto} ${anoTexto})</label>
                    <div class="input-group">
                        <input type="text" 
                            class="form-control armazon-search" 
                            placeholder="Buscar armazón o accesorio...">
                            
                        <input type="hidden" 
                            name="a_inventario_id[]" 
                            value="" 
                            class="armazon-id">
                            
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary dropdown-toggle armazon-dropdown-btn" type="button" 
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu armazon-dropdown" style="max-height: 300px; overflow-y: auto; width: 100%;">
                                ${options}
                            </div>
                        </div>
                    </div>
                    ${hasOptions ? 
                        `<small class="form-text text-muted">${optionsCount} artículo(s) disponible(s) de ${mesTexto} ${anoTexto} y asignados a este pedido</small>` : 
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
                <div class="col-md-8">
                    <label>Foto Armazón (Opcional)</label>
                    <input type="file" name="a_foto[]" class="form-control form-control-sm" accept="image/*">
                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
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
    
    container.insertAdjacentHTML('beforeend', template);
    calculateTotal();
}; 

// Funciones para actualizar el inventario
function restaurarUnidadInventario(inventarioId) {
    if (!inventarioId) return;
    
    console.log('Restaurando unidad al inventario:', inventarioId, '- editMode:', window.editMode);
    
    // Si no estamos en modo edición, no actualizar el inventario
    if (!window.editMode) {
        console.log('Operación cancelada: no estamos en modo edición');
        return;
    }
    
    // Llamar a la API para restaurar la unidad
    fetch(`/api/inventario/restaurar/${inventarioId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error al restaurar el inventario');
        }
        return response.json();
    })
    .then(data => {
        console.log('Unidad restaurada exitosamente:', data);
    })
    .catch(error => {
        console.error('Error al restaurar unidad:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo restaurar la unidad al inventario',
                timer: 3000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        }
    });
}

function restarUnidadInventario(inventarioId) {
    if (!inventarioId) return;
    
    console.log('Restando unidad del inventario:', inventarioId, '- editMode:', window.editMode);
    
    // Si no estamos en modo edición, no actualizar el inventario
    if (!window.editMode) {
        console.log('Operación cancelada: no estamos en modo edición');
        return;
    }
    
    // Llamar a la API para restar la unidad
    fetch(`/api/inventario/restar/${inventarioId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error al restar del inventario');
        }
        return response.json();
    })
    .then(data => {
        console.log('Unidad restada exitosamente:', data);
    })
    .catch(error => {
        console.error('Error al restar unidad:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo restar la unidad del inventario',
                timer: 3000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        }
    });
}

/**
 * Funciones para formatear medidas de lunas automáticamente
 */

// Función para formatear las medidas de lunas automáticamente (global)
function formatearMedidasLunasEdit() {
    // Buscar todas las secciones de lunas
    document.querySelectorAll('.luna-section').forEach((seccion, index) => {
        formatearMedidasLunasSeccionEdit(seccion);
    });
}

// Función para formatear las medidas de lunas en una sección específica (global)
function formatearMedidasLunasSeccionEdit(seccion) {
    // Obtener valores de los campos de esta sección específica
    const odEsfera = seccion.querySelector('[name="od_esfera[]"]')?.value?.trim() || '';
    const odCilindro = seccion.querySelector('[name="od_cilindro[]"]')?.value?.trim() || '';
    const odEje = seccion.querySelector('[name="od_eje[]"]')?.value?.trim() || '';
    const oiEsfera = seccion.querySelector('[name="oi_esfera[]"]')?.value?.trim() || '';
    const oiCilindro = seccion.querySelector('[name="oi_cilindro[]"]')?.value?.trim() || '';
    const oiEje = seccion.querySelector('[name="oi_eje[]"]')?.value?.trim() || '';
    const add = seccion.querySelector('[name="add[]"]')?.value?.trim() || '';
    const dp = seccion.querySelector('[name="dp[]"]')?.value?.trim() || '';
    
    // Formatear valores con signos apropiados
    const formatearValor = (valor) => {
        if (!valor) return '';
        const num = parseFloat(valor.replace(/[+\-]/g, ''));
        if (isNaN(num)) return valor;
        if (valor.includes('-') || num < 0) return `-${Math.abs(num).toFixed(2)}`;
        return `+${num.toFixed(2)}`;
    };
    
    // Construir la cadena de medidas
    let medidaCompleta = '';
    
    // OD
    if (odEsfera || odCilindro || odEje) {
        medidaCompleta += 'OD: ';
        if (odEsfera) medidaCompleta += formatearValor(odEsfera) + ' ';
        if (odCilindro) medidaCompleta += formatearValor(odCilindro) + ' ';
        if (odEje) medidaCompleta += (odEje.includes('X') ? odEje : `X${odEje.replace('°', '')}`) + '° ';
    }
    
    // OI
    if (oiEsfera || oiCilindro || oiEje) {
        if (medidaCompleta) medidaCompleta += '/ ';
        medidaCompleta += 'OI: ';
        if (oiEsfera) medidaCompleta += formatearValor(oiEsfera) + ' ';
        if (oiCilindro) medidaCompleta += formatearValor(oiCilindro) + ' ';
        if (oiEje) medidaCompleta += (oiEje.includes('X') ? oiEje : `X${oiEje.replace('°', '')}`) + '° ';
    }
    
    // ADD
    if (add) {
        if (medidaCompleta) medidaCompleta += ' ';
        medidaCompleta += `ADD: ${formatearValor(add)}`;
    }
    
    // DP
    if (dp) {
        if (medidaCompleta) medidaCompleta += ' ';
        medidaCompleta += `DP: ${dp}`;
    }
    
    // Actualizar el campo oculto de esta sección
    const campoMedida = seccion.querySelector('.l-medida-hidden');
    if (campoMedida) {
        campoMedida.value = medidaCompleta.trim();
    }
}

// Event listeners para los campos de medidas de lunas (global)
function agregarEventListenersMedidas() {
    const camposMedidas = [
        '[name="od_esfera[]"]',
        '[name="od_cilindro[]"]', 
        '[name="od_eje[]"]',
        '[name="oi_esfera[]"]',
        '[name="oi_cilindro[]"]',
        '[name="oi_eje[]"]',
        '[name="add[]"]',
        '[name="dp[]"]'
    ];
    
    camposMedidas.forEach(selector => {
        document.querySelectorAll(selector).forEach(campo => {
            // Remover listeners existentes
            campo.removeEventListener('input', formatearMedidasLunasEdit);
            campo.removeEventListener('blur', formatearMedidasLunasEdit);
            
            // Agregar nuevos listeners
            campo.addEventListener('input', formatearMedidasLunasEdit);
            campo.addEventListener('blur', formatearMedidasLunasEdit);
        });
    });
}

// Event listeners para los campos de material OD/OI (global)
function agregarEventListenersMaterial() {
    const camposMaterial = [
        '[name="material_od[]"]',
        '[name="material_oi[]"]'
    ];
    
    camposMaterial.forEach(selector => {
        document.querySelectorAll(selector).forEach(campo => {
            // Remover listeners existentes
            campo.removeEventListener('input', formatearMaterialEdit);
            campo.removeEventListener('blur', formatearMaterialEdit);
            
            // Agregar nuevos listeners
            campo.addEventListener('input', formatearMaterialEdit);
            campo.addEventListener('blur', formatearMaterialEdit);
        });
    });
}

// Función para formatear material de una sección específica (edit mode)
function formatearMaterialEdit(event) {
    const campo = event.target;
    const seccion = campo.closest('.luna-section') || campo.closest('.card-body');
    
    if (seccion) {
        formatearMaterialSeccionEdit(seccion);
    }
}

// Función para formatear material en una sección específica (edit mode)
function formatearMaterialSeccionEdit(seccion) {
    const materialOD = seccion.querySelector('[name="material_od[]"]')?.value?.trim() || '';
    const materialOI = seccion.querySelector('[name="material_oi[]"]')?.value?.trim() || '';
    const materialUnificado = seccion.querySelector('[name="material[]"]');
    
    if (materialUnificado) {
        let materialTexto = '';
        if (materialOD || materialOI) {
            const partes = [];
            if (materialOD) partes.push(`OD: ${materialOD}`);
            if (materialOI) partes.push(`OI: ${materialOI}`);
            materialTexto = partes.join(' | ');
        }
        materialUnificado.value = materialTexto;
    }
}

// Hacer las funciones disponibles globalmente
window.formatearMedidasLunasEdit = formatearMedidasLunasEdit;
window.formatearMedidasLunasSeccionEdit = formatearMedidasLunasSeccionEdit;
window.agregarEventListenersMedidas = agregarEventListenersMedidas;
window.formatearMaterialEdit = formatearMaterialEdit;
window.formatearMaterialSeccionEdit = formatearMaterialSeccionEdit;
window.agregarEventListenersMaterial = agregarEventListenersMaterial;