@props(['items', 'empresas'])

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

{{-- Mostrar solo los artículos únicos que realmente existen, ordenados por número --}}
@foreach($uniqueItems->sortBy('numero') as $item)
    <tr @if($item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id }}">
        <x-inventario.table-row-content :item="$item" :empresas="$empresas" />
    </tr>
@endforeach

{{-- Mostrar los artículos duplicados al final --}}
@foreach($duplicateItems->sortBy('numero') as $item)
    <tr @if($item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id }}" class="duplicate-row">
        <x-inventario.table-row-content :item="$item" :empresas="$empresas" />
    </tr>
@endforeach 