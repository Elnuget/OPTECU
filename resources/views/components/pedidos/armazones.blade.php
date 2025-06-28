@props(['pedido', 'inventarioItems', 'filtroMes' => null, 'filtroAno' => null])

<div class="card">
    <div class="card-header">
        @php
            $nombresMeses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $mesTexto = $filtroMes ? ($nombresMeses[(int)$filtroMes] ?? date('F')) : date('F');
            $anoTexto = $filtroAno ?? date('Y');
        @endphp
        <h3 class="card-title">Armazón o Accesorio ({{ $mesTexto }} {{ $anoTexto }})</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="armazones-container">
            @foreach($pedido->inventarios as $index => $inventario)
            <div class="armazon-section mb-3">
                @if($index > 0)
                    <hr>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <label>Armazón o Accesorio ({{ $mesTexto }} {{ $anoTexto }})</label>
                        
                        <div class="input-group">
                            <input type="text" 
                                class="form-control armazon-search" 
                                placeholder="Buscar armazón o accesorio..." 
                                data-selected-id="{{ $inventario->id }}"
                                value="{{ $inventario->codigo }} - {{ $inventario->lugar }} - {{ $inventario->fecha ? \Carbon\Carbon::parse($inventario->fecha)->format('d/m/Y') : 'Sin fecha' }}">
                                
                            <input type="hidden" 
                                name="a_inventario_id[]" 
                                value="{{ $inventario->id }}" 
                                class="armazon-id">
                                
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary dropdown-toggle armazon-dropdown-btn" type="button" 
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-menu armazon-dropdown" style="max-height: 300px; overflow-y: auto; width: 100%;">
                                    @foreach($inventarioItems as $item)
                                        <a class="dropdown-item armazon-option" href="#" 
                                           data-id="{{ $item->id }}" 
                                           data-code="{{ $item->codigo }}"
                                           data-place="{{ $item->lugar }}"
                                           data-date="{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha' }}">
                                            {{ $item->codigo }} - {{ $item->lugar }} - {{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        @if($inventarioItems->isEmpty())
                            <div class="text-danger mt-1">
                                <small><i class="fas fa-exclamation-triangle"></i> No hay artículos disponibles para este mes</small>
                            </div>
                        @else
                            <small class="form-text text-muted">
                                {{ $inventarioItems->count() }} artículo(s) disponible(s) de {{ $mesTexto }} {{ $anoTexto }} y asignados a este pedido
                            </small>
                        @endif
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label>Precio</label>
                        <input type="number" name="a_precio[]" class="form-control" 
                            value="{{ $inventario->pivot->precio }}" step="0.01" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-6">
                        <label>Descuento (%)</label>
                        <input type="number" name="a_precio_descuento[]" class="form-control" 
                            value="{{ $inventario->pivot->descuento }}" min="0" max="100" oninput="calculateTotal()">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-8">
                        <label>Foto Armazón (Opcional)</label>
                        <input type="file" name="a_foto[]" class="form-control form-control-sm" accept="image/*">
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
                    </div>
                    <div class="col-md-4">
                        @if(isset($inventario->pivot->foto) && $inventario->pivot->foto)
                            <label>Foto Actual</label><br>
                            <img src="{{ asset($inventario->pivot->foto) }}" alt="Foto Armazón" 
                                 class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                            <br><small class="text-muted">Subir nueva foto para reemplazar</small>
                        @endif
                    </div>
                </div>
                @if($index > 0)
                    <div class="row mt-2">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-danger btn-sm remove-armazon">
                                <i class="fas fa-times"></i> Eliminar Armazón o Accesorio
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="button" class="btn btn-success" id="add-armazon">
                    <i class="fas fa-plus"></i> Agregar Armazón o Accesorio
                </button>
            </div>
        </div>
    </div>
</div> 