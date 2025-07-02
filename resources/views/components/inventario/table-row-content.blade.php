@props(['item'])

<td class="editable text-center" data-field="numero">
    <span class="display-value">{{ $item->numero }}</span>
    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->numero }}">
</td>
<td class="editable text-center" data-field="lugar">
    <span class="display-value">{{ $item->lugar }}</span>
    <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->lugar }}">
</td>
<td class="editable text-center" data-field="columna">
    <span class="display-value">{{ $item->columna }}</span>
    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->columna }}">
</td>
<td class="editable" data-field="codigo">
    <span class="display-value">{{ $item->codigo }}</span>
    <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->codigo }}">
</td>
<td class="text-center">
    <span class="display-value">{{ $item->empresa ? $item->empresa->nombre : 'N/A' }}</span>
</td>
<td class="editable text-center" data-field="cantidad">
    <span class="display-value">{{ $item->cantidad }}</span>
    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->cantidad }}">
</td>
<td class="text-center">
    <div class="btn-group">
        @can('admin')
        <form action="{{ route('inventario.destroy', $item->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                    onclick="return confirm('¿Está seguro de que desea eliminar este artículo?')">>
                <i class="fa fa-trash"></i>
            </button>
        </form>
        @else
        <span class="text-muted">-</span>
        @endcan
    </div>
</td>