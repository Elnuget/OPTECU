@props(['columna', 'items'])

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
                    @endphp
                    <div class="badge bg-danger text-white">
                        {{ $articulosAgotadosColumna }} agotados
                    </div>
                </div>
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 10%">Número</th>
                        <th style="width: 15%">Lugar</th>
                        <th style="width: 10%">Columna</th>
                        <th style="width: 35%">Código</th>
                        <th style="width: 10%">Cantidad</th>
                        @can('admin')
                        <th style="width: 10%">Acciones</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @if(Str::startsWith(strtoupper($items->first()->lugar), 'SOPORTE'))
                        <x-inventario.table-soporte-rows :items="$items" />
                    @else
                        <x-inventario.table-regular-rows :items="$items" />
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div> 