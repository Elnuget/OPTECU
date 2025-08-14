<!-- Tabla de Pedidos -->
<div class="table-responsive mt-3">
    <table class="table table-sm table-bordered table-striped">
        <thead>
            <tr>
                <th>FECHA</th>
                <th>ORDEN</th>
                <th>CLIENTE</th>
                <th>SUCURSAL</th>
                <th>USUARIO</th>
                <th>TOTAL</th>
                <th>SALDO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->fecha->format('Y-m-d') }}</td>
                    <td>{{ $pedido->numero_orden }}</td>
                    <td>{{ $pedido->cliente }}</td>
                    <td>{{ $pedido->empresa ? $pedido->empresa->nombre : 'SIN SUCURSAL' }}</td>
                    <td>{{ $pedido->usuario ?: 'N/A' }}</td>
                    <td>${{ number_format($pedido->total, 2, ',', '.') }}</td>
                    <td>${{ number_format($pedido->saldo, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-secondary">
                <th colspan="5">TOTAL</th>
                <th>${{ isset($pedidos) ? number_format($pedidos->sum('total'), 2, ',', '.') : '0,00' }}</th>
                <th>${{ isset($pedidos) ? number_format($pedidos->sum('saldo'), 2, ',', '.') : '0,00' }}</th>
            </tr>
        </tfoot>
    </table>
</div>
