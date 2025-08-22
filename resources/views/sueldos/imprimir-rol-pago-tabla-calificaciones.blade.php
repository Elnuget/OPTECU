<!-- Tabla simplificada de calificaciones para incluir en imprimir-rol-pago.blade.php -->
@if(isset($calificaciones) && isset($pedidos) && count($pedidos->whereNotNull('calificacion')) > 0)
    <div class="section">
        <h2 class="section-title">CALIFICACIONES DE CLIENTES</h2>
        
        <table>
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>PEDIDO</th>
                    <th>CLIENTE</th>
                    <th>CALIFICACIÓN</th>
                    <th>COMENTARIO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos->whereNotNull('calificacion')->sortByDesc('calificacion') as $pedido)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $pedido->numero_orden }}</td>
                    <td>{{ $pedido->cliente }}</td>
                    <td class="text-center">{{ $pedido->calificacion }}/5</td>
                    <td>{{ $pedido->comentario_calificacion ?? 'Sin comentarios' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
