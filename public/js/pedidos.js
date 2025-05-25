function calculateTotal() {
    // Obtener el total pagado
    const totalPagado = parseFloat(document.getElementById('total_pagado').value) || 0;

    // Calcular nuevo total
    let newTotal = 0;

    // Sumar examen visual
    const examenVisual = parseFloat(document.getElementById('examen_visual').value) || 0;
    const examenVisualDescuento = parseFloat(document.getElementById('examen_visual_descuento').value) || 0;
    const examenVisualTotal = examenVisual * (1 - (examenVisualDescuento / 100));
    newTotal += examenVisualTotal;

    // Sumar armazones
    document.querySelectorAll('.armazon-section').forEach(section => {
        const precio = parseFloat(section.querySelector('[name="a_precio[]"]').value) || 0;
        const descuento = parseFloat(section.querySelector('[name="a_precio_descuento[]"]').value) || 0;
        const precioFinal = precio * (1 - (descuento / 100));
        newTotal += precioFinal;
    });

    // Sumar lunas
    document.querySelectorAll('.luna-section').forEach(section => {
        const precio = parseFloat(section.querySelector('[name="l_precio[]"]').value) || 0;
        const descuento = parseFloat(section.querySelector('[name="l_precio_descuento[]"]').value) || 0;
        const precioFinal = precio * (1 - (descuento / 100));
        newTotal += precioFinal;
    });

    // Sumar compra rápida
    const valorCompra = parseFloat(document.getElementById('valor_compra').value) || 0;
    newTotal += valorCompra;

    // Redondear a 2 decimales
    newTotal = Math.round(newTotal * 100) / 100;

    // Calcular saldo pendiente (nuevo total menos pagos realizados)
    const newSaldo = Math.max(0, newTotal - totalPagado);

    // Actualizar los campos
    document.getElementById('total').value = newTotal.toFixed(2);
    document.getElementById('saldo').value = newSaldo.toFixed(2);
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
                <div class="col-md-6">
                    <label class="form-label">Lunas Medidas</label>
                    <input type="text" class="form-control" name="l_medida[]" value="">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lunas Detalle</label>
                    <input type="text" class="form-control" name="l_detalle[]" value="">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Tipo de Lente</label>
                    <input type="text" class="form-control" name="tipo_lente[]" 
                           list="tipo_lente_options" value=""
                           placeholder="Seleccione o escriba un tipo de lente">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Material</label>
                    <input type="text" class="form-control" name="material[]" 
                           list="material_options" value=""
                           placeholder="Seleccione o escriba un material">
                </div>
                <div class="col-md-4">
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
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Campos que afectan al total
    const fields = [
        'examen_visual',
        'examen_visual_descuento',
        'valor_compra',
        'total'
    ];
    
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('input', function() {
                if (field === 'total') {
                    // Si se modifica el total manualmente, recalcular solo el saldo
                    const total = parseFloat(this.value) || 0;
                    const totalPagado = parseFloat(document.getElementById('total_pagado').value) || 0;
                    const newSaldo = Math.max(0, total - totalPagado);
                    document.getElementById('saldo').value = newSaldo.toFixed(2);
                } else {
                    // Para otros campos, calcular todo
                    calculateTotal();
                }
            });
        }
    });

    // Event delegation para precios y descuentos de armazones
    document.getElementById('armazones-container').addEventListener('input', function(e) {
        if (e.target.matches('[name="a_precio[]"], [name="a_precio_descuento[]"]')) {
            calculateTotal();
        }
    });

    // Event delegation para precios y descuentos de lunas
    document.getElementById('lunas-container').addEventListener('input', function(e) {
        if (e.target.matches('[name="l_precio[]"], [name="l_precio_descuento[]"]')) {
            calculateTotal();
        }
    });

    // Calcular total inicial
    calculateTotal();

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

    // Inicializar selectpicker
    $('.selectpicker').selectpicker();
}); 