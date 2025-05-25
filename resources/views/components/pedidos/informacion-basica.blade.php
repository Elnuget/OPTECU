@props(['pedido', 'usuarios'])

<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Información Básica</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" class="form-control" 
                    value="{{ $pedido->fecha ? $pedido->fecha->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-6">
                <label for="numero_orden">Número de Orden</label>
                <input type="number" name="numero_orden" class="form-control" value="{{ $pedido->numero_orden }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="usuario">Usuario</label>
                @can('admin')
                    <select name="usuario" class="form-control" id="usuario">
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->name }}" {{ $pedido->usuario == $usuario->name ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control" value="{{ $pedido->usuario }}" readonly>
                    <input type="hidden" name="usuario" value="{{ $pedido->usuario }}">
                @endcan
            </div>
        </div>
    </div>
</div> 