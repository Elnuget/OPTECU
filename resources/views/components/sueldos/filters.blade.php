{{-- Filtros de fecha y sucursal --}}
<div class="form-row mb-4">
    <div class="col-md-3">
        <label for="filtroAno">SELECCIONAR AÑO:</label>
        <select name="ano" class="form-control custom-select" id="filtroAno">
            <option value="">SELECCIONE AÑO</option>
            @php
                $currentYear = date('Y');
                $selectedYear = request('ano', $currentYear);
            @endphp
            @for ($year = $currentYear; $year >= 2000; $year--)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <label for="filtroMes">SELECCIONAR MES:</label>
        <select name="mes" class="form-control custom-select" id="filtroMes">
            <option value="">SELECCIONE MES</option>
            @php
                $currentMonth = date('n');
                $selectedMonth = request('mes', $currentMonth);
                $meses = [
                    'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
                    'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
                ];
            @endphp
            @foreach ($meses as $index => $mes)
                <option value="{{ $index + 1 }}" {{ $selectedMonth == ($index + 1) ? 'selected' : '' }}>
                    {{ $mes }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="filtroSucursal">SELECCIONAR SUCURSAL:</label>
        <select name="sucursal" class="form-control custom-select" id="filtroSucursal" 
                {{ $tipoSucursal !== 'todas' ? 'disabled' : '' }}>
            <option value="">TODAS LAS SUCURSALES</option>
            @if($tipoSucursal === 'todas' || $tipoSucursal === 'matriz')
                <option value="matriz" {{ request('sucursal') == 'matriz' ? 'selected' : '' }}>
                    MATRIZ
                </option>
            @endif
            @if($tipoSucursal === 'todas' || $tipoSucursal === 'rocio')
                <option value="rocio" {{ request('sucursal') == 'rocio' ? 'selected' : '' }}>
                    ROCÍO
                </option>
            @endif
            @if($tipoSucursal === 'todas' || $tipoSucursal === 'norte')
                <option value="norte" {{ request('sucursal') == 'norte' ? 'selected' : '' }}>
                    NORTE
                </option>
            @endif
        </select>
    </div>

    <div class="col-md-3 align-self-end">
        <button type="button" class="btn btn-primary btn-block" id="actualButton">
            <i class="fas fa-sync-alt"></i> ACTUAL
        </button>
    </div>
</div> 