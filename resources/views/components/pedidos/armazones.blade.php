@props(['pedido', 'inventarioItems'])

<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Armazón o Accesorio</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="armazones-container">
            @foreach($pedido->inventarios as $index => $inventario)
            <div class="armazon-section mb-3">
                @if($index > 0)
                    <hr>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <label>Armazón o Accesorio (Inventario)</label>
                        <select name="a_inventario_id[]" class="form-control selectpicker" data-live-search="true">
                            <option value="">Seleccione un armazón o accesorio</option>
                            @foreach($inventarioItems as $item)
                                <option value="{{ $item->id }}" 
                                    {{ $inventario->id == $item->id ? 'selected' : '' }}>
                                    {{ $item->codigo }} - {{ $item->lugar ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label>Precio</label>
                        <input type="number" name="a_precio[]" class="form-control" 
                            value="{{ $inventario->pivot->precio }}" step="0.01" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-6">
                        <label>Descuento (%)</label>
                        <input type="number" name="a_precio_descuento[]" class="form-control" 
                            value="{{ $inventario->pivot->descuento }}" min="0" max="100" oninput="calculateTotal()">
                    </div>
                </div>
                @if($index > 0)
                    <div class="row mt-2">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-danger btn-sm remove-armazon">
                                <i class="fas fa-times"></i> Eliminar Armazón o Accesorio
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="button" class="btn btn-success" id="add-armazon">
                    <i class="fas fa-plus"></i> Agregar Armazón o Accesorio
                </button>
            </div>
        </div>
    </div>
</div> 