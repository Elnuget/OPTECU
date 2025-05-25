@props(['inventario'])

<div class="row">
    @php
        $inventarioPorLugar = $inventario->groupBy('lugar');
    @endphp

    @foreach($inventarioPorLugar as $lugar => $itemsPorLugar)
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary cursor-pointer" data-toggle="collapse" 
                     data-target="#collapse{{ Str::slug($lugar) }}" aria-expanded="false">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-white mb-0">
                            <i class="fas fa-warehouse"></i> {{ $lugar }}
                        </h3>
                        <div class="d-flex align-items-center">
                            @php
                                $articulosAgotados = $itemsPorLugar->where('cantidad', 0)->count();
                            @endphp

                            <span class="badge badge-light mr-3">
                                {{ $itemsPorLugar->count() }} artículos
                            </span>
                            <div class="badge bg-danger text-white mr-3">
                                {{ $articulosAgotados }} agotados
                            </div>
                            <i class="fas fa-chevron-down text-white transition-icon"></i>
                        </div>
                    </div>
                </div>
                <div id="collapse{{ Str::slug($lugar) }}" class="collapse">
                    <div class="card-body">
                        <div class="row">
                            @php
                                $itemsPorColumna = $itemsPorLugar->groupBy('columna')->sortKeys();
                            @endphp

                            @foreach($itemsPorColumna as $columna => $items)
                                <x-inventario.table-column :columna="$columna" :items="$items" />
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div> 