<!-- Sección de calificaciones para incluir en imprimir-rol-pago.blade.php -->
@if(isset($calificaciones) && isset($pedidos) && count($pedidos->whereNotNull('calificacion')) > 0)
    <div class="section">
        <h2 class="section-title">DETALLE DE CALIFICACIONES</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>CALIFICACIÓN</th>
                    <th>CANTIDAD</th>
                    <th>PORCENTAJE</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 5; $i >= 1; $i--)
                <tr>
                    <td>{{ $i }} {{ $i === 5 ? '(Excelente)' : ($i === 4 ? '(Bueno)' : ($i === 3 ? '(Regular)' : ($i === 2 ? '(Malo)' : '(Pésimo)'))) }}</td>
                    <td class="text-center">{{ $calificaciones['distribucion'][$i] }}</td>
                    <td class="text-center">
                        @if($calificaciones['calificados'] > 0)
                            {{ number_format(($calificaciones['distribucion'][$i] / $calificaciones['calificados']) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <th>TOTAL</th>
                    <th class="text-center">{{ $calificaciones['calificados'] }}</th>
                    <th class="text-center">100%</th>
                </tr>
            </tfoot>
        </table>
        
        @if(count($pedidos->whereNotNull('calificacion')->whereNotNull('comentario_calificacion')) > 0)
            <h3 class="subsection-title">COMENTARIOS DE CLIENTES</h3>
            
            <table class="data-table">
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
                    @foreach($pedidos->whereNotNull('calificacion')->whereNotNull('comentario_calificacion')->sortByDesc('calificacion')->take(10) as $pedido)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $pedido->numero_orden }}</td>
                        <td>{{ $pedido->cliente }}</td>
                        <td class="text-center">{{ $pedido->calificacion }}/5</td>
                        <td>{{ $pedido->comentario_calificacion }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif
