{{-- Formulario de filtro --}}
<form method="GET" class="form-row mb-3" id="filterForm">
    <div class="col-md-2">
        <label for="filtroAno">SELECCIONAR AÑO:</label>
        <select name="ano" class="form-control custom-select" id="filtroAno">
            <option value="">SELECCIONE AÑO</option>
            @php
                $currentYear = date('Y');
                $selectedYear = request('ano', $currentYear);
            @endphp
            @for ($year = date('Y'); $year >= 2000; $year--)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endfor
        </select>
    </div>
    <div class="col-md-2">
        <label for="filtroMes">SELECCIONAR MES:</label>
        <select name="mes" class="form-control custom-select" id="filtroMes">
            <option value="">SELECCIONE MES</option>
            @php
                $currentMonth = date('n');
                $selectedMonth = request('mes', $currentMonth);
            @endphp
            @foreach (['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'] as $index => $month)
                <option value="{{ $index + 1 }}" {{ $selectedMonth == ($index + 1) ? 'selected' : '' }}>
                    {{ $month }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="filtroSucursal">SELECCIONAR SUCURSAL:</label>
        <select name="sucursal" class="form-control custom-select" id="filtroSucursal" {{ $tipoSucursal !== 'todas' ? 'disabled' : '' }}>
            <option value="">TODAS LAS SUCURSALES</option>
            @if($tipoSucursal === 'todas' || $tipoSucursal === 'matriz')
                <option value="matriz">MATRIZ</option>
            @endif
            @if($tipoSucursal === 'todas' || $tipoSucursal === 'rocio')
                <option value="rocio">ROCÍO</option>
            @endif
            @if($tipoSucursal === 'todas' || $tipoSucursal === 'norte')
                <option value="norte">NORTE</option>
            @endif
        </select>
    </div>
    <div class="col-md-2">
        <label for="filtroUsuario">SELECCIONAR USUARIO:</label>
        <select name="user_id" class="form-control custom-select" id="filtroUsuario">
            <option value="">TODOS LOS USUARIOS</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 align-self-end">
        <button type="button" class="btn btn-primary" id="actualButton">ACTUAL</button>
    </div>
</form>

{{-- Botón Añadir Sueldo --}}
<div class="btn-group mb-3">
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearSueldoModal">
        <i class="fas fa-plus mr-2"></i>AÑADIR SUELDO
    </button>
</div> 