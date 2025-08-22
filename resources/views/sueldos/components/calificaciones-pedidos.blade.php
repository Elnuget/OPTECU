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
