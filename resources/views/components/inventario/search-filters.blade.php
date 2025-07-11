@props(['fecha' => null, 'empresas' => []])

<form method="GET" class="mb-3">
    <div class="row mb-3">
        <div class="col-md-5">
            <label for="filtroFecha">SELECCIONAR FECHA:</label>
            <input type="month" name="fecha" id="filtroFecha" class="form-control"
                   value="{{ $fecha ?? now()->format('Y-m') }}" />
        </div>
        <div class="col-md-5">
            <label for="empresa_id">SUCURSAL:</label>
            <select name="empresa_id" id="empresa_id" class="form-control" {{ !auth()->user()->is_admin && auth()->user()->empresa_id ? 'readonly disabled' : '' }}>
                @if(auth()->user()->is_admin || !auth()->user()->empresa_id)
                    <option value="">TODAS LAS SUCURSALES</option>
                @endif
                @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}" 
                        {{ request('empresa_id') == $empresa->id || 
                          (!auth()->user()->is_admin && auth()->user()->empresa_id == $empresa->id) ? 'selected' : '' }}>
                        {{ $empresa->nombre }}
                    </option>
                @endforeach
            </select>
            @if(!auth()->user()->is_admin && auth()->user()->empresa_id)
                <input type="hidden" name="empresa_id" value="{{ auth()->user()->empresa_id }}">
            @endif
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