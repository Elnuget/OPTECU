@props(['items'])

@php
    // Agrupar items por número para detectar duplicados
    $itemsByNumber = $items->groupBy('numero');
    // Obtener números duplicados
    $duplicateNumbers = $itemsByNumber->filter(function($group) {
        return $group->count() > 1;
    })->keys();
    
    // Separar items únicos y duplicados
    $uniqueItems = $items->filter(function($item) use ($duplicateNumbers) {
        return !$duplicateNumbers->contains($item->numero);
    });
    $duplicateItems = $items->filter(function($item) use ($duplicateNumbers) {
        return $duplicateNumbers->contains($item->numero);
    })->sortBy('numero');
@endphp

{{-- Mostrar primero las filas del 1 al 14 que no están duplicadas --}}
@for($n = 1; $n <= 14; $n++)
    @php
        $item = $uniqueItems->firstWhere('numero', $n);
    @endphp
    <tr @if($item && $item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id ?? '' }}">
        <td class="editable text-center" data-field="numero">
            @if($item)
                <span class="display-value">{{ $item->numero }}</span>
                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->numero }}">
            @else
                <span class="display-value">{{ $n }}</span>
            @endif
        </td>
        <td class="editable text-center" data-field="lugar">
            <span class="display-value">{{ $item->lugar ?? $items->first()->lugar }}</span>
            @if($item)
                <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->lugar }}">
            @endif
        </td>
        <td class="editable text-center" data-field="columna">
            <span class="display-value">{{ $item->columna ?? $items->first()->columna }}</span>
            @if($item)
                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->columna }}">
            @endif
        </td>
        <td class="editable" data-field="codigo">
            <span class="display-value">{{ $item->codigo ?? '-' }}</span>
            @if($item)
                <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->codigo }}">
            @endif
        </td>
        <td class="editable text-center" data-field="cantidad">
            <span class="display-value">{{ $item->cantidad ?? '-' }}</span>
            @if($item)
                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->cantidad }}">
            @endif
        </td>
        @can('admin')
        <td class="text-center">
            @if($item)
                <div class="btn-group">
                    <form action="{{ route('inventario.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                onclick="return confirm('¿Está seguro de que desea eliminar este artículo?')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        @endcan
    </tr>
@endfor

{{-- Mostrar los artículos duplicados al final --}}
@foreach($duplicateItems->sortBy('numero') as $item)
    <tr @if($item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id }}" class="duplicate-row">
        <x-inventario.table-row-content :item="$item" />
    </tr>
@endforeach

{{-- Mostrar artículos con número mayor a 14 que no están duplicados --}}
@foreach($uniqueItems->filter(function($item){ return $item->numero > 14; })->sortBy('numero') as $item)
    <tr @if($item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id }}">
        <x-inventario.table-row-content :item="$item" />
    </tr>
@endforeach 