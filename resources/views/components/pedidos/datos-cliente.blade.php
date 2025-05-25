@props(['pedido'])

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Datos Personales</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fact" class="form-label">Factura</label>
                <input type="text" class="form-control" id="fact" name="fact"
                       value="{{ $pedido->fact }}">
            </div>
            <div class="col-md-6">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="cliente" name="cliente"
                       value="{{ $pedido->cliente }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cedula" class="form-label">Cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula"
                       value="{{ $pedido->cedula }}">
            </div>
            <div class="col-md-6">
                <label for="paciente" class="form-label">Paciente</label>
                <input type="text" class="form-control" id="paciente" name="paciente" 
                       value="{{ $pedido->paciente }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="examen_visual" class="form-label">Examen Visual</label>
                <input type="number" class="form-control form-control-sm" id="examen_visual" name="examen_visual"
                       value="{{ $pedido->examen_visual }}" step="0.01" oninput="calculateTotal()">
            </div>
            <div class="col-md-3">
                <label for="celular" class="form-label">Celular</label>
                <input type="text" class="form-control" id="celular" name="celular"
                       value="{{ $pedido->celular }}">
            </div>
            <div class="col-md-6">
                <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                       value="{{ $pedido->correo_electronico }}">
            </div>
        </div>
    </div>
</div> 