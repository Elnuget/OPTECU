@props(['fecha' => null])

<form method="GET" class="mb-3">
    <div class="row mb-3">
        <div class="col-md-10">
            <label for="filtroFecha">SELECCIONAR FECHA:</label>
            <input type="month" name="fecha" class="form-control"
                   value="{{ $fecha ?? now()->format('Y-m') }}" />
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button class="btn btn-primary form-control" type="submit">FILTRAR</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-10">
            <label for="busqueda">BUSCAR ARTÍCULO:</label>
            <input type="text" id="busquedaGlobal" class="form-control" placeholder="BUSCAR POR NÚMERO, CÓDIGO O LUGAR...">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-secondary form-control" id="buscarExpandir">
                <i class="fas fa-search"></i> BUSCAR
            </button>
        </div>
    </div>
</form> 