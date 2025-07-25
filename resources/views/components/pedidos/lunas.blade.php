@props(['pedido'])

<div id="lunas-container" class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Lunas</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @foreach($pedido->lunas as $index => $luna)
            <div class="luna-section {{ $index > 0 ? 'mt-4' : '' }}">
                @if($index > 0)
                    <hr>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-danger btn-sm remove-luna" onclick="this.closest('.luna-section').remove(); calculateTotal();">
                            <i class="fas fa-times"></i> Eliminar
                        </button>
                    </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Prescripción/Medidas de Lunas</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">Ojo</th>
                                        <th width="20%">Esfera</th>
                                        <th width="20%">Cilindro</th>
                                        <th width="15%">Eje</th>
                                        <th width="15%">ADD</th>
                                        <th width="20%">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Parseamos los datos existentes de l_medida si existen
                                        $medidaData = [];
                                        if ($luna->l_medida) {
                                            // Intentar extraer los valores de la cadena existente
                                            preg_match('/OD:\s*([+\-]?\d*\.?\d*)\s*([+\-]?\d*\.?\d*)\s*X?(\d*)°?/', $luna->l_medida, $odMatches);
                                            preg_match('/OI:\s*([+\-]?\d*\.?\d*)\s*([+\-]?\d*\.?\d*)\s*X?(\d*)°?/', $luna->l_medida, $oiMatches);
                                            preg_match('/ADD:\s*([+\-]?\d*\.?\d*)/', $luna->l_medida, $addMatches);
                                            preg_match('/DP:\s*(\d+)/', $luna->l_medida, $dpMatches);
                                            
                                            $medidaData = [
                                                'od_esfera' => $odMatches[1] ?? '',
                                                'od_cilindro' => $odMatches[2] ?? '',
                                                'od_eje' => $odMatches[3] ?? '',
                                                'oi_esfera' => $oiMatches[1] ?? '',
                                                'oi_cilindro' => $oiMatches[2] ?? '',
                                                'oi_eje' => $oiMatches[3] ?? '',
                                                'add' => $addMatches[1] ?? '',
                                                'dp' => $dpMatches[1] ?? ''
                                            ];
                                        }
                                    @endphp
                                    <tr>
                                        <td class="align-middle text-center"><strong>OD</strong></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="od_esfera[]" 
                                                   placeholder="Ej: +2.00" value="{{ $medidaData['od_esfera'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="od_cilindro[]" 
                                                   placeholder="Ej: -1.50" value="{{ $medidaData['od_cilindro'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="od_eje[]" 
                                                   placeholder="Ej: 90°" value="{{ $medidaData['od_eje'] ? $medidaData['od_eje'] . '°' : '' }}"></td>
                                        <td rowspan="2" class="align-middle">
                                            <input type="text" class="form-control form-control-sm medida-input" name="add[]" 
                                                   placeholder="Ej: +2.00" value="{{ $medidaData['add'] ?? '' }}">
                                        </td>
                                        <td rowspan="2" class="align-middle">
                                            <textarea class="form-control form-control-sm" name="l_detalle[]" rows="3" 
                                                      placeholder="Detalles adicionales">{{ $luna->l_detalle }}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle text-center"><strong>OI</strong></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="oi_esfera[]" 
                                                   placeholder="Ej: +1.75" value="{{ $medidaData['oi_esfera'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="oi_cilindro[]" 
                                                   placeholder="Ej: -1.25" value="{{ $medidaData['oi_cilindro'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="oi_eje[]" 
                                                   placeholder="Ej: 85°" value="{{ $medidaData['oi_eje'] ? $medidaData['oi_eje'] . '°' : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong>DP</strong></td>
                                        <td><input type="text" class="form-control form-control-sm medida-input" name="dp[]" 
                                                   placeholder="Ej: 62" value="{{ $medidaData['dp'] ?? '' }}"></td>
                                        <td colspan="4">
                                            <input type="hidden" name="l_medida[]" value="{{ $luna->l_medida }}" class="l-medida-hidden">
                                            <small class="text-muted">Distancia Pupilar</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Formato de ejemplo:</strong> OD: +2.00 -1.50 X90° / OI: +1.75 -1.25 X85° ADD: +2.00 DP: 62
                        </small>
                    </div>
                </div>

                {{-- Campo Tipo de Receta --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label"><strong>Tipo de Receta</strong></label>
                        <select class="form-control" name="tipo[]">
                            <option value="">Seleccionar...</option>
                            <option value="CERCA" {{ $luna->tipo == 'CERCA' ? 'selected' : '' }}>CERCA</option>
                            <option value="LEJOS" {{ $luna->tipo == 'LEJOS' ? 'selected' : '' }}>LEJOS</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Lente</label>
                        <input type="text" class="form-control" name="tipo_lente[]" 
                               list="tipo_lente_options" value="{{ $luna->tipo_lente }}"
                               placeholder="Seleccione o escriba un tipo de lente">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Material</label>
                        @php
                            // Parsear los datos existentes de material si están en formato "OD: valor | OI: valor"
                            $materialData = [];
                            if ($luna->material && strpos($luna->material, 'OD:') !== false) {
                                preg_match('/OD:\s*([^|]+)/', $luna->material, $odMatches);
                                preg_match('/OI:\s*(.+)/', $luna->material, $oiMatches);
                                $materialData = [
                                    'od' => trim($odMatches[1] ?? ''),
                                    'oi' => trim($oiMatches[1] ?? '')
                                ];
                            } else {
                                // Si no está en el nuevo formato, usar el valor existente para ambos ojos (retrocompatibilidad)
                                $materialData = [
                                    'od' => $luna->material ?? '',
                                    'oi' => $luna->material ?? ''
                                ];
                            }
                        @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-sm">OD (Ojo Derecho)</label>
                                <input type="text" class="form-control form-control-sm material-input" name="material_od[]" 
                                       list="material_options" value="{{ $materialData['od'] }}" placeholder="Material OD">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-sm">OI (Ojo Izquierdo)</label>
                                <input type="text" class="form-control form-control-sm material-input" name="material_oi[]" 
                                       list="material_options" value="{{ $materialData['oi'] }}" placeholder="Material OI">
                            </div>
                        </div>
                        <input type="hidden" name="material[]" value="{{ $luna->material }}" class="material-hidden">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtro</label>
                        <input type="text" class="form-control" name="filtro[]" 
                               list="filtro_options" value="{{ $luna->filtro }}"
                               placeholder="Seleccione o escriba un filtro">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Precio Lunas</label>
                        <input type="number" class="form-control input-sm" name="l_precio[]"
                               value="{{ $luna->l_precio }}" step="0.01" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desc. Lunas (%)</label>
                        <input type="number" class="form-control input-sm" name="l_precio_descuento[]"
                               value="{{ $luna->l_precio_descuento }}" min="0" max="100" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Foto Lunas (Opcional)</label>
                        <input type="file" class="form-control form-control-sm" name="l_foto[]" accept="image/*">
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
                    </div>
                    <div class="col-md-2">
                        @if(isset($luna->foto) && $luna->foto)
                            <label>Foto Actual</label><br>
                            <img src="{{ asset($luna->foto) }}" alt="Foto Luna" 
                                 class="img-thumbnail" style="max-width: 80px; max-height: 80px;">
                            <br><small class="text-muted">Nueva foto reemplazará</small>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-success" onclick="duplicateLunas()">Agregar más Lunas</button>
    </div>
</div>

@push('datalists')
<datalist id="tipo_lente_options">
    <option value="Monofocal">
    <option value="Bifocal">
    <option value="Progresivo">
    <option value="Ocupacional">
    <option value="Contacto">
</datalist>

<datalist id="material_options">
    <option value="Policarbonato">
    <option value="CR-39">
    <option value="Cristal">
    <option value="1.56">
    <option value="1.61">
    <option value="1.67">
    <option value="1.74">
    <option value="GX7">
    <option value="Crizal">
</datalist>

<datalist id="filtro_options">
    <option value="Antireflejo">
    <option value="UV">
    <option value="Filtro azul AR verde">
    <option value="Filtro azul AR azul">
    <option value="Fotocromatico">
    <option value="Blancas">
    <option value="Fotocromatico AR">
    <option value="Fotocromatico filtro azul">
    <option value="Fotocromatico a colores">
    <option value="Tinturado">
    <option value="Polarizado">
    <option value="Transitions">
</datalist>
@endpush 