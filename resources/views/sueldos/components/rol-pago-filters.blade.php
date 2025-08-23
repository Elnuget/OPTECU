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
                                <option value="{{ $i }}" {{ (request('anio') ?: date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="mes">MES</label>
                        <select class="form-control" id="mes" name="mes">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (request('mes') ?: date('m')) == $i ? 'selected' : '' }}>{{ strtoupper(date('F', mktime(0, 0, 0, $i, 1))) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="usuario">USUARIO</label>
                        @if(Auth::user() && !Auth::user()->is_admin)
                            {{-- Usuario no administrador: solo puede ver su propio rol de pago --}}
                            <input type="hidden" name="usuario" value="{{ Auth::user()->name }}">
                            <select class="form-control select2" id="usuario" disabled>
                                <option value="{{ Auth::user()->name }}" selected>{{ Auth::user()->name }}</option>
                            </select>
                            <small class="text-muted">Solo puedes consultar tu propio rol de pago</small>
                        @else
                            {{-- Usuario administrador: puede ver todos los usuarios --}}
                            <select class="form-control select2" id="usuario" name="usuario">
                                <option value="">TODOS LOS USUARIOS</option>
                                @foreach($usuariosConPedidos ?? [] as $nombreUsuario)
                                    <option value="{{ $nombreUsuario }}" {{ request('usuario') == $nombreUsuario ? 'selected' : '' }}>{{ $nombreUsuario }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group" style="padding-top: 32px;">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-search"></i> BUSCAR
                        </button>
                        @if(request()->hasAny(['anio', 'mes', 'usuario']) && request()->isMethod('get') && (request('anio') || request('mes') || request('usuario')))
                        <a href="{{ route('sueldos.imprimir-rol-pago', [
                            'anio' => request('anio'),
                            'mes' => request('mes'),
                            'usuario' => request('usuario')
                        ]) }}" target="_blank" class="btn btn-success btn-block">
                            <i class="fas fa-print"></i> IMPRIMIR ROL
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if(request()->hasAny(['anio', 'mes', 'usuario']) && request()->isMethod('get') && (request('anio') || request('mes') || request('usuario')))
            <!-- Sección de Detalles de Sueldo para el Usuario/Período Seleccionado -->
            <div class="card mt-3">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="fas fa-user-tie"></i> DETALLES DE SUELDO 
                        @if(request('usuario'))
                            - {{ strtoupper(request('usuario')) }}
                        @endif
                        ({{ str_pad(request('mes') ?: date('m'), 2, '0', STR_PAD_LEFT) }}/{{ request('anio') ?: date('Y') }})
                    </h3>
                    <div class="card-tools">
                        @if(Route::has('detalles-sueldo.create'))
                        <a href="{{ route('detalles-sueldo.create', [
                            'usuario' => request('usuario'),
                            'mes' => request('mes') ?: date('m'),
                            'anio' => request('anio') ?: date('Y')
                        ]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> AGREGAR DETALLE
                        </a>
                        @endif
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($detallesSueldo) && count($detallesSueldo) > 0)

                        <!-- Tabla de Detalles -->
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>FECHA CREACIÓN</th>
                                        <th>EMPLEADO</th>
                                        <th>DESCRIPCIÓN</th>
                                        <th>VALOR</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detallesSueldo as $detalle)
                                    <tr>
                                        <td>{{ $detalle->created_at ? $detalle->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>{{ $detalle->user ? $detalle->user->name : 'N/A' }}</td>
                                        <td>{{ $detalle->descripcion }}</td>
                                        <td>${{ number_format($detalle->valor, 2, ',', '.') }}</td>
                                        <td>
                                            @if(Route::has('detalles-sueldo.show'))
                                            <a href="{{ route('detalles-sueldo.show', $detalle->id) }}" 
                                               class="btn btn-xs btn-outline-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endif
                                            @can('admin')
                                            @if(Route::has('detalles-sueldo.edit'))
                                            <a href="{{ route('detalles-sueldo.edit', $detalle->id) }}" 
                                               class="btn btn-xs btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            @if(Route::has('detalles-sueldo.destroy'))
                                            <a href="#" class="btn btn-xs btn-outline-danger" 
                                               data-toggle="modal" data-target="#confirmarEliminarDetalleModal"
                                               data-id="{{ $detalle->id }}" 
                                               data-url="{{ route('detalles-sueldo.destroy', $detalle->id) }}" 
                                               title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            @endif
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-success">
                                        <th colspan="3">TOTAL</th>
                                        <th>${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</th>
                                        <th>{{ $detallesSueldo->count() }} REG.</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            NO SE ENCONTRARON DETALLES DE SUELDO PARA 
                            @if(request('usuario'))
                                {{ strtoupper(request('usuario')) }} EN
                            @else
                                LOS FILTROS SELECCIONADOS EN
                            @endif
                            {{ str_pad(request('mes') ?: date('m'), 2, '0', STR_PAD_LEFT) }}/{{ request('anio') ?: date('Y') }}
                        </div>
                        @if(Route::has('detalles-sueldo.create'))
                        <div class="text-center">
                            <a href="{{ route('detalles-sueldo.create', [
                                'usuario' => request('usuario'),
                                'mes' => request('mes') ?: date('m'),
                                'anio' => request('anio') ?: date('Y')
                            ]) }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> AGREGAR PRIMER DETALLE
                            </a>
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Resumen de Estadísticas -->
            @if(isset($pedidos) && count($pedidos) > 0)
                <div class="row mt-3">
                    <!-- Total de Ventas -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>${{ number_format($pedidos->sum('total'), 2, ',', '.') }}</h3>
                                <p>TOTAL DE VENTAS</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total de Saldo -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>${{ number_format($pedidos->sum('saldo'), 2, ',', '.') }}</h3>
                                <p>SALDO PENDIENTE</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Retiros de Caja -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>$-{{ number_format(isset($retirosCaja) ? abs($retirosCaja->sum('valor')) : 0, 2, ',', '.') }}</h3>
                                <p>RETIROS DE CAJA</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cash-register"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cantidad de Pedidos -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $pedidos->count() }}</h3>
                                <p>PEDIDOS REALIZADOS</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Balance Neto (Ventas - Retiros - Total Detalles) -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box" style="background-color: #6f42c1; color: white;">
                            <div class="inner">
                                <h3>${{ number_format($pedidos->sum('total') + (isset($retirosCaja) ? $retirosCaja->sum('valor') : 0) - (isset($detallesSueldo) ? $detallesSueldo->sum('valor') : 0), 2, ',', '.') }}</h3>
                                <p>BALANCE NETO (CON DETALLES)</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Calificaciones de Pedidos -->
            @include('sueldos.components.calificaciones-pedidos')

            <!-- Historial de Caja (Aperturas y Cierres) -->
            @include('sueldos.components.historial-caja', ['historialCaja' => $historialCaja ?? null])

            @if(isset($pedidos) && count($pedidos) > 0)
                @include('sueldos.components.pedidos-por-sucursal', ['pedidos' => $pedidos])
                @include('sueldos.components.retiros-caja', ['retirosCaja' => $retirosCaja ?? null])
                @include('sueldos.components.tabla-pedidos', ['pedidos' => $pedidos])
            @else
                <div class="alert alert-info mt-3">
                    NO SE ENCONTRARON PEDIDOS PARA LOS FILTROS SELECCIONADOS
                </div>
            @endif
        @else
            <div class="alert alert-secondary mt-3">
                <i class="fas fa-info-circle"></i> SELECCIONE LOS FILTROS Y PRESIONE "BUSCAR" PARA VER LOS RESULTADOS DEL ROL DE PAGO
            </div>
        @endif
    </div>
</div>
