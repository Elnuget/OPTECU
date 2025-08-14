<!-- Tarjeta de Rol de Pago con Filtros -->
<div class="card card-info mb-3">
    <div class="card-header">
        <h3 class="card-title">ROL DE PAGO</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="rolDePagoForm" method="GET" action="{{ route('sueldos.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="anio">AÑO</label>
                        <select class="form-control" id="anio" name="anio">
                            @for ($i = date('Y'); $i >= date('Y')-5; $i--)
                                <option value="{{ $i }}" {{ request('anio') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="mes">MES</label>
                        <select class="form-control" id="mes" name="mes">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('mes') == $i ? 'selected' : '' }}>{{ strtoupper(date('F', mktime(0, 0, 0, $i, 1))) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="usuario">USUARIO</label>
                        <select class="form-control select2" id="usuario" name="usuario">
                            <option value="">TODOS LOS USUARIOS</option>
                            @foreach($usuariosConPedidos ?? [] as $nombreUsuario)
                                <option value="{{ $nombreUsuario }}" {{ request('usuario') == $nombreUsuario ? 'selected' : '' }}>{{ $nombreUsuario }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group" style="padding-top: 32px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> BUSCAR
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if(isset($pedidos) && count($pedidos) > 0)
            @include('sueldos.components.estadisticas-resumen', ['pedidos' => $pedidos, 'retirosCaja' => $retirosCaja ?? null])
            @include('sueldos.components.pedidos-por-sucursal', ['pedidos' => $pedidos])
            @include('sueldos.components.retiros-caja', ['retirosCaja' => $retirosCaja ?? null])
            @include('sueldos.components.tabla-pedidos', ['pedidos' => $pedidos])
        @else
            <div class="alert alert-info mt-3">
                NO SE ENCONTRARON PEDIDOS PARA LOS FILTROS SELECCIONADOS
            </div>
        @endif
    </div>
</div>
