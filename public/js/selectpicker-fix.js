/**
 * Script para manejar los combobox personalizados
 */

$(document).ready(function() {
    initializeCustomComboboxes();
    
    // Delegación de eventos para los elementos de la lista desplegable
    $(document).on('click', '.armazon-option', function(e) {
        e.preventDefault();
        
        const $this = $(this);
        const id = $this.data('id');
        const code = $this.data('code');
        const place = $this.data('place');
        const date = $this.data('date');
        const text = `${code} - ${place} - ${date}`;
        
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
        
        // Si había un armazón anterior seleccionado, restaurar su unidad
        if (anteriorId && anteriorId !== id) {
            restaurarUnidadInventario(anteriorId);
        }
        
        // Si se seleccionó un nuevo armazón, restar una unidad
        if (id) {
            restarUnidadInventario(id);
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
    
    // Inicializar botón para agregar armazón
    $('#add-armazon').on('click', function(e) {
        e.preventDefault();
        addArmazon();
    });
    
    // Delegación de eventos para eliminar armazón
    $('#armazones-container').on('click', '.remove-armazon', function(e) {
        e.preventDefault();
        
        // Obtener el ID del armazón a eliminar
        const $hiddenInput = $(this).closest('.armazon-section').find('.armazon-id');
        const inventarioId = $hiddenInput.val();
        
        // Restaurar unidad al inventario si hay un ID válido
        if (inventarioId) {
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
}

// Función global para añadir un nuevo armazón
window.addArmazon = function() {
    const container = document.getElementById('armazones-container');
    if (!container) return;
    
    // Obtener todas las opciones del primer selector para duplicarlas
    const firstDropdown = document.querySelector('.armazon-dropdown');
    if (!firstDropdown) return;
    
    const options = Array.from(firstDropdown.querySelectorAll('.armazon-option')).map(opt => {
        const id = opt.getAttribute('data-id');
        const code = opt.getAttribute('data-code');
        const place = opt.getAttribute('data-place');
        const date = opt.getAttribute('data-date');
        const text = opt.textContent.trim();
        
        return `<a class="dropdown-item armazon-option" href="#" 
                  data-id="${id}" 
                  data-code="${code}"
                  data-place="${place}"
                  data-date="${date}">
                  ${text}
               </a>`;
    }).join('');
    
    const template = `
        <div class="armazon-section mb-3">
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <label>Armazón o Accesorio</label>
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
                        <i class="fas fa-times"></i> Eliminar
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
    
    console.log('Restaurando unidad al inventario:', inventarioId);
    
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
    
    console.log('Restando unidad del inventario:', inventarioId);
    
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