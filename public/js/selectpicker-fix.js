/**
 * Script para solucionar problemas con Bootstrap-Select
 */

$(document).ready(function() {
    // Verificar que Bootstrap Select esté disponible
    if (!$.fn.selectpicker) {
        console.error('ERROR: Bootstrap Select no está disponible!');
        return;
    }
    
    console.log('Bootstrap Select está disponible');
    
    // Inicializar todos los selectpickers existentes
    $('.selectpicker').selectpicker({
        noneSelectedText: 'Seleccione un elemento',
        liveSearch: true,
        liveSearchPlaceholder: 'Buscar...',
        style: 'btn-light',
        size: 10,
        width: '100%'
    });
    
    console.log('Selectpickers inicializados');
    
    // Verificar que todos los selectpickers tengan la clase correcta
    $('.selectpicker').each(function(index) {
        var $this = $(this);
        var id = $this.attr('id') || index;
        
        console.log('Verificando selectpicker #' + id);
        
        if (!$this.parent().hasClass('bootstrap-select')) {
            console.log('Selectpicker #' + id + ' no inicializado correctamente, intentando reinicializar');
            
            try {
                $this.selectpicker('destroy');
                $this.selectpicker();
            } catch (e) {
                console.error('Error al reinicializar selectpicker #' + id, e);
            }
        }
    });
    
    // Reinicializar selectpickers cuando se muestre un card colapsado
    $('.card').on('shown.bs.collapse', function() {
        setTimeout(function() {
            $('.selectpicker').selectpicker('refresh');
        }, 100);
    });
});

// Función global para añadir un nuevo armazón simplificada
window.addArmazon = function() {
    const container = document.getElementById('armazones-container');
    if (!container) return;
    
    const firstSelect = document.querySelector('[name="a_inventario_id[]"]');
    if (!firstSelect) return;
    
    const options = Array.from(firstSelect.options).map(opt => {
        return `<option value="${opt.value}">${opt.text}</option>`;
    }).join('');
    
    const template = `
        <div class="armazon-section mb-3">
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <label>Armazón o Accesorio</label>
                    <select name="a_inventario_id[]" class="form-control">
                        <option value="">Seleccione un armazón o accesorio</option>
                        ${options}
                    </select>
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