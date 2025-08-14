<!-- Pedidos por Sucursal -->
<div class="card mt-3">
    <div class="card-header bg-secondary">
        <h3 class="card-title">PEDIDOS POR SUCURSAL</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>SUCURSAL</th>
                    <th>PEDIDOS</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pedidosPorEmpresa = $pedidos->groupBy('empresa_id');
                @endphp
                
                @foreach($pedidosPorEmpresa as $empresaId => $pedidosEmpresa)
                    @php
                        $nombreEmpresa = 'SIN SUCURSAL';
                        if ($empresaId && $pedidosEmpresa->first()->empresa) {
                            $nombreEmpresa = $pedidosEmpresa->first()->empresa->nombre;
                        }
                    @endphp
                    <tr>
                        <td>{{ $nombreEmpresa }}</td>
                        <td>{{ $pedidosEmpresa->count() }}</td>
                        <td>${{ number_format($pedidosEmpresa->sum('total'), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
