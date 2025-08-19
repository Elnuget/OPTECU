@props(['pedido'])

<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Compra Rápida</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="valor_compra" class="form-label">Valor de Compra</label>
                <input type="number" class="form-control input-sm" id="valor_compra" name="valor_compra" 
                       value="{{ $pedido->valor_compra }}" step="0.01">
            </div>
            <div class="col-md-6">
                <label for="motivo_compra" class="form-label">Motivo de Compra</label>
                <input type="text" class="form-control" id="motivo_compra" name="motivo_compra" 
                       list="motivo_compra_options" placeholder="Seleccione o escriba un motivo" 
                       value="{{ $pedido->motivo_compra }}">
                <datalist id="motivo_compra_options">
                    <option value="Líquidos">
                    <option value="Accesorios">
                    <option value="Estuches">
                    <option value="Otros">
                </datalist>
            </div>
        </div>
    </div>
</div> 