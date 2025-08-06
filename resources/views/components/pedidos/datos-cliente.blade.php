@props(['pedido', 'empresas' => [], 'userEmpresaId' => null, 'isUserAdmin' => false])

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Datos Personales</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fact" class="form-label">ESTADO</label>
                <select class="form-control" id="fact" name="fact">
                    <option value="Pendiente" {{ $pedido->fact == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="CRISTALERIA" {{ $pedido->fact == 'CRISTALERIA' ? 'selected' : '' }}>Cristalería</option>
                    <option value="Separado" {{ $pedido->fact == 'Separado' ? 'selected' : '' }}>Separado</option>
                    <option value="LISTO EN TALLER" {{ $pedido->fact == 'LISTO EN TALLER' ? 'selected' : '' }}>Listo en Taller</option>
                    <option value="Enviado" {{ $pedido->fact == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                    <option value="ENTREGADO" {{ $pedido->fact == 'ENTREGADO' ? 'selected' : '' }}>Entregado</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="empresa_id" class="form-label">Empresa *</label>
                <select class="form-control" id="empresa_id" name="empresa_id" required>
                    <option value="">Seleccione una empresa</option>
                    @if(isset($empresas))
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ $pedido->empresa_id == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nombre }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="cliente" name="cliente"
                       value="{{ $pedido->cliente }}">
            </div>
            <div class="col-md-6">
                <label for="cedula" class="form-label">RUT</label>
                <input type="text" class="form-control" id="cedula" name="cedula"
                       value="{{ $pedido->cedula }}">
            </div>
            <div class="col-md-6">
                <label for="paciente" class="form-label">Paciente</label>
                <input type="text" class="form-control" id="paciente" name="paciente" 
                       value="{{ $pedido->paciente }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="examen_visual" class="form-label">Examen Visual</label>
                <input type="number" class="form-control form-control-sm" id="examen_visual" name="examen_visual"
                       value="{{ $pedido->examen_visual }}" step="0.01" oninput="calculateTotal()">
            </div>
            <div class="col-md-3">
                <label for="celular" class="form-label">Celular</label>
                <input type="text" class="form-control" id="celular" name="celular"
                       value="{{ $pedido->celular }}">
            </div>
            <div class="col-md-3">
                <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                       value="{{ $pedido->correo_electronico }}">
            </div>
            <div class="col-md-3">
                <label for="empresa_id" class="form-label">SUCURSAL</label>
                <select name="empresa_id" id="empresa_id" class="form-control" {{ !$isUserAdmin && count($empresas) <= 1 ? 'disabled' : '' }}>
                    <option value="">Seleccione una empresa...</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ $pedido->empresa_id == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nombre }}
                        </option>
                    @endforeach
                </select>
                @if(!$isUserAdmin && count($empresas) <= 1 && $userEmpresaId)
                    <input type="hidden" name="empresa_id" value="{{ $userEmpresaId }}">
                    <small class="form-text text-muted">Solo tiene acceso a esta empresa</small>
                @elseif(!$isUserAdmin && count($empresas) > 1)
                    <small class="form-text text-muted">Seleccione entre sus empresas asociadas</small>
                @endif
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion"
                       value="{{ $pedido->direccion }}">
            </div>
        </div>

        {{-- Nuevos campos: Método de envío y Fecha de entrega --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="metodo_envio" class="form-label">Método de Envío</label>
                <select class="form-control" id="metodo_envio" name="metodo_envio">
                    <option value="">Seleccione método de envío...</option>
                    <option value="TIENDA" {{ $pedido->metodo_envio == 'TIENDA' ? 'selected' : '' }}>TIENDA</option>
                    <option value="CORREOS DE CHILE" {{ $pedido->metodo_envio == 'CORREOS DE CHILE' ? 'selected' : '' }}>CORREOS DE CHILE</option>
                    <option value="CHILEXPRESS" {{ $pedido->metodo_envio == 'CHILEXPRESS' ? 'selected' : '' }}>CHILEXPRESS</option>
                    <option value="STARKEN" {{ $pedido->metodo_envio == 'STARKEN' ? 'selected' : '' }}>STARKEN</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega"
                       value="{{ $pedido->fecha_entrega ? $pedido->fecha_entrega->format('Y-m-d') : '' }}">
            </div>
        </div>
    </div>
</div> 