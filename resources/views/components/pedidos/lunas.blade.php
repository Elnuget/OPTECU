@props(['pedido'])

<div id="lunas-container" class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Lunas</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @foreach($pedido->lunas as $index => $luna)
            <div class="luna-section {{ $index > 0 ? 'mt-4' : '' }}">
                @if($index > 0)
                    <hr>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-danger btn-sm remove-luna" onclick="this.closest('.luna-section').remove(); calculateTotal();">
                            <i class="fas fa-times"></i> Eliminar
                        </button>
                    </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Lunas Medidas</label>
                        <input type="text" class="form-control" name="l_medida[]" value="{{ $luna->l_medida }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lunas Detalle</label>
                        <input type="text" class="form-control" name="l_detalle[]" value="{{ $luna->l_detalle }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Lente</label>
                        <input type="text" class="form-control" name="tipo_lente[]" 
                               list="tipo_lente_options" value="{{ $luna->tipo_lente }}"
                               placeholder="Seleccione o escriba un tipo de lente">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Material</label>
                        <input type="text" class="form-control" name="material[]" 
                               list="material_options" value="{{ $luna->material }}"
                               placeholder="Seleccione o escriba un material">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtro</label>
                        <input type="text" class="form-control" name="filtro[]" 
                               list="filtro_options" value="{{ $luna->filtro }}"
                               placeholder="Seleccione o escriba un filtro">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Precio Lunas</label>
                        <input type="number" class="form-control input-sm" name="l_precio[]"
                               value="{{ $luna->l_precio }}" step="0.01" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desc. Lunas (%)</label>
                        <input type="number" class="form-control input-sm" name="l_precio_descuento[]"
                               value="{{ $luna->l_precio_descuento }}" min="0" max="100" oninput="calculateTotal()">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-success" onclick="duplicateLunas()">Agregar más Lunas</button>
    </div>
</div>

@push('datalists')
<datalist id="tipo_lente_options">
    <option value="Monofocal">
    <option value="Bifocal">
    <option value="Progresivo">
    <option value="Ocupacional">
    <option value="Contacto">
</datalist>

<datalist id="material_options">
    <option value="Policarbonato">
    <option value="CR-39">
    <option value="Cristal">
    <option value="1.56">
    <option value="1.61">
    <option value="1.67">
    <option value="1.74">
    <option value="GX7">
    <option value="Crizal">
</datalist>

<datalist id="filtro_options">
    <option value="Antireflejo">
    <option value="UV">
    <option value="Filtro azul AR verde">
    <option value="Filtro azul AR azul">
    <option value="Fotocromatico">
    <option value="Blancas">
    <option value="Fotocromatico AR">
    <option value="Fotocromatico filtro azul">
    <option value="Fotocromatico a colores">
    <option value="Tinturado">
    <option value="Polarizado">
    <option value="Transitions">
</datalist>
@endpush 