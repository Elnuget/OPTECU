@if(isset($calificaciones) && isset($pedidos) && count($pedidos) > 0)
<!-- Calificaciones de Pedidos -->
<div class="card">
    <div class="card-header bg-info">
        <h3 class="card-title">CALIFICACIONES DE PEDIDOS</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Total de Pedidos -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $calificaciones['total'] }}</h3>
                        <p>TOTAL PEDIDOS</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            
            <!-- Pedidos Calificados -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $calificaciones['calificados'] }}</h3>
                        <p>PEDIDOS CALIFICADOS</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            
            <!-- Promedio de Calificación -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($calificaciones['promedio'], 1) }}</h3>
                        <p>PROMEDIO CALIFICACIÓN</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            
            <!-- Porcentaje de Calificados -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ number_format($calificaciones['porcentaje_calificados'], 1) }}%</h3>
                        <p>% PEDIDOS CALIFICADOS</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Distribución de calificaciones -->
        <div class="card mb-3">
            <div class="card-header bg-secondary">
                <h3 class="card-title">DISTRIBUCIÓN DE CALIFICACIONES</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @for($i = 5; $i >= 1; $i--)
                        <div class="col">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $i >= 4 ? 'success' : ($i >= 3 ? 'warning' : 'danger') }}">
                                    <i class="fas fa-star"></i> {{ $i }}
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $calificaciones['distribucion'][$i] }} pedidos</span>
                                    <span class="info-box-number">
                                        @if($calificaciones['calificados'] > 0)
                                            {{ number_format(($calificaciones['distribucion'][$i] / $calificaciones['calificados']) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $i >= 4 ? 'success' : ($i >= 3 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $calificaciones['calificados'] > 0 ? ($calificaciones['distribucion'][$i] / $calificaciones['calificados']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Listado de Pedidos con Calificación -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>PEDIDO #</th>
                        <th>CLIENTE</th>
                        <th>CALIFICACIÓN</th>
                        <th>COMENTARIO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos->whereNotNull('calificacion')->sortByDesc('calificacion') as $pedido)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($pedido->fecha)->format('Y-m-d') }}</td>
                            <td>{{ $pedido->numero_orden }}</td>
                            <td>{{ $pedido->cliente }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-{{ $pedido->calificacion >= 4 ? 'success' : ($pedido->calificacion >= 3 ? 'warning' : 'danger') }} mr-2">
                                        {{ $pedido->calificacion }}
                                    </span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa{{ $i <= $pedido->calificacion ? 's' : 'r' }} fa-star text-warning"></i>
                                    @endfor
                                </div>
                            </td>
                            <td>{{ $pedido->comentario_calificacion ?? 'Sin comentarios' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
