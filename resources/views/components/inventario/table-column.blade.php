@props(['columna', 'items', 'empresas'])

<div class="col-md-6 mb-4">
    <div class="card h-100">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-columns"></i> Columna {{ $columna }}
                </div>
                <div class="d-flex">
                    <div class="badge badge-light mr-2">
                        {{ $items->count() }} artículos
                    </div>
                    <div class="badge badge-success mr-2">
                        Total: {{ $items->sum('cantidad') }}
                    </div>
                    @php
                        $articulosAgotadosColumna = $items->where('cantidad', 0)->count();
                        // Calcular el siguiente número disponible
                        $ultimoNumero = $items->max('numero');
                        $siguienteNumero = $ultimoNumero + 1;
                        // Obtener el lugar de la primera fila
                        $lugarPredeterminado = $items->first()->lugar;
                    @endphp
                    <div class="badge bg-danger text-white">
                        {{ $articulosAgotadosColumna }} agotados
                    </div>
                </div>
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover inventario-table mb-0" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 10%">Número</th>
                        <th style="width: 15%">Lugar</th>
                        <th style="width: 10%">Columna</th>
                        <th style="width: 25%">Código</th>
                        <th style="width: 15%">Empresa</th>
                        <th style="width: 10%">Cantidad</th>
                        <th style="width: 15%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(Str::startsWith(strtoupper($items->first()->lugar), 'SOPORTE'))
                        <x-inventario.table-soporte-rows :items="$items" :empresas="$empresas" />
                    @else
                        <x-inventario.table-regular-rows :items="$items" :empresas="$empresas" />
                    @endif
                    
                    <!-- Nueva fila para agregar artículo -->
                    <tr class="new-row" data-columna="{{ $columna }}" data-lugar="{{ $lugarPredeterminado }}" style="display: none;">
                        <td class="text-center" data-field="numero">
                            <span class="display-value">{{ $siguienteNumero }}</span>
                        </td>
                        <td class="text-center" data-field="lugar">
                            <span class="display-value">{{ $lugarPredeterminado }}</span>
                        </td>
                        <td class="text-center" data-field="columna">
                            <span class="display-value">{{ $columna }}</span>
                        </td>
                        <td class="editable" data-field="codigo">
                            <span class="display-value">-</span>
                            <input type="text" class="form-control edit-input" style="display: none;">
                        </td>
                        <td class="editable text-center" data-field="empresa_id">
                            <span class="display-value">N/A</span>
                            <select class="form-control edit-input" style="display: none;">
                                <option value="">Seleccionar empresa</option>
                                @foreach($empresas ?? [] as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="editable text-center" data-field="cantidad">
                            <span class="display-value">-</span>
                            <input type="number" class="form-control edit-input" style="display: none;" value="1">
                        </td>
                        <td class="text-center">
                            <span class="text-muted">-</span>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-center">
                            <button type="button" class="btn btn-sm btn-success add-row-btn" data-columna="{{ $columna }}">
                                <i class="fas fa-plus"></i> AGREGAR ARTÍCULO
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div> 