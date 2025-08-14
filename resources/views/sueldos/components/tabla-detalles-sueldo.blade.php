<!-- Tabla de Detalles de Sueldo -->
<div class="card mt-4">
    <div class="card-header bg-primary">
        <h3 class="card-title">DETALLES DE SUELDO</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @if(isset($detallesSueldo) && count($detallesSueldo) > 0)
            {{-- Resumen de Detalles de Sueldo --}}
            <div class="row mb-3">
                <!-- Total de Detalles -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</h3>
                            <p>TOTAL DETALLES</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Cantidad de Detalles -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $detallesSueldo->count() }}</h3>
                            <p>CANTIDAD DETALLES</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Promedio por Detalle -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>${{ number_format($detallesSueldo->count() > 0 ? $detallesSueldo->sum('valor') / $detallesSueldo->count() : 0, 2, ',', '.') }}</h3>
                            <p>PROMEDIO</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Botón Añadir Detalle -->
                <div class="col-lg-3 col-6">
                    <div class="d-flex align-items-center h-100">
                        @can('admin')
                        @if(Route::has('detalles-sueldo.create'))
                        <a type="button" class="btn btn-success btn-lg btn-block" href="{{ route('detalles-sueldo.create') }}">
                            <i class="fas fa-plus"></i> AGREGAR DETALLE
                        </a>
                        @else
                        <button type="button" class="btn btn-success btn-lg btn-block" disabled title="Ruta no configurada">
                            <i class="fas fa-plus"></i> AGREGAR DETALLE
                        </button>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Detalles por Usuario --}}
            <div class="card mb-3">
                <div class="card-header bg-secondary">
                    <h3 class="card-title">DETALLES POR USUARIO</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>USUARIO</th>
                                <th>CANTIDAD</th>
                                <th>TOTAL</th>
                                <th>PROMEDIO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $detallesPorUsuario = $detallesSueldo->groupBy('user_id');
                            @endphp
                            
                            @foreach($detallesPorUsuario as $userId => $detallesUsuario)
                                @php
                                    $nombreUsuario = 'USUARIO DESCONOCIDO';
                                    if ($userId && $detallesUsuario->first()->user) {
                                        $nombreUsuario = $detallesUsuario->first()->user->name;
                                    }
                                    $totalUsuario = $detallesUsuario->sum('valor');
                                    $cantidadUsuario = $detallesUsuario->count();
                                @endphp
                                <tr>
                                    <td>{{ $nombreUsuario }}</td>
                                    <td>{{ $cantidadUsuario }}</td>
                                    <td>${{ number_format($totalUsuario, 2, ',', '.') }}</td>
                                    <td>${{ number_format($cantidadUsuario > 0 ? $totalUsuario / $cantidadUsuario : 0, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-secondary">
                                <th>TOTAL GENERAL</th>
                                <th>{{ $detallesSueldo->count() }}</th>
                                <th>${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</th>
                                <th>${{ number_format($detallesSueldo->count() > 0 ? $detallesSueldo->sum('valor') / $detallesSueldo->count() : 0, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Tabla Principal de Detalles --}}
            <div class="table-responsive">
                <table id="detallesSueldoTable" class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>PERÍODO</th>
                            <th>EMPLEADO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>VALOR</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detallesSueldo as $detalle)
                            <tr>
                                <td>{{ str_pad($detalle->mes, 2, '0', STR_PAD_LEFT) }}/{{ $detalle->ano }}</td>
                                <td>{{ $detalle->user ? $detalle->user->name : 'USUARIO DESCONOCIDO' }}</td>
                                <td>{{ $detalle->descripcion }}</td>
                                <td>${{ number_format($detalle->valor, 2, ',', '.') }}</td>
                                <td>{{ $detalle->created_at ? $detalle->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td>
                                    @if(Route::has('detalles-sueldo.show'))
                                    <a href="{{ route('detalles-sueldo.show', $detalle->id) }}"
                                        class="btn btn-xs btn-default text-info mx-1 shadow" title="Ver">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </a>
                                    @endif
                                    @can('admin')
                                    @if(Route::has('detalles-sueldo.edit'))
                                    <a href="{{ route('detalles-sueldo.edit', $detalle->id) }}"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </a>
                                    @endif

                                    @if(Route::has('detalles-sueldo.destroy'))
                                    <a class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        href="#"
                                        data-toggle="modal"
                                        data-target="#confirmarEliminarDetalleModal"
                                        data-id="{{ $detalle->id }}"
                                        data-url="{{ route('detalles-sueldo.destroy', $detalle->id) }}">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </a>
                                    @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary text-white">
                            <th colspan="3">TOTAL DETALLES</th>
                            <th>${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</th>
                            <th colspan="2">{{ $detallesSueldo->count() }} REGISTROS</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> NO SE ENCONTRARON DETALLES DE SUELDO PARA LOS FILTROS SELECCIONADOS
            </div>
            @can('admin')
            <div class="text-center">
                @if(Route::has('detalles-sueldo.create'))
                <a type="button" class="btn btn-success btn-lg" href="{{ route('detalles-sueldo.create') }}">
                    <i class="fas fa-plus"></i> AGREGAR PRIMER DETALLE
                </a>
                @else
                <button type="button" class="btn btn-success btn-lg" disabled title="Ruta no configurada">
                    <i class="fas fa-plus"></i> AGREGAR PRIMER DETALLE
                </button>
                @endif
            </div>
            @endcan
        @endif
    </div>
</div>
