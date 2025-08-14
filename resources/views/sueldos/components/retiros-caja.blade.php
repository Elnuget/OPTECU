<!-- Retiros de Caja -->
@if(isset($retirosCaja) && count($retirosCaja) > 0)
<div class="card mt-3">
    <div class="card-header bg-danger">
        <h3 class="card-title">RETIROS DE CAJA</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>SUCURSAL</th>
                    <th>USUARIO</th>
                    <th></th>MOTIVO</th>
                    <th>VALOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($retirosCaja as $retiro)
                <tr>
                    <td>{{ $retiro->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $retiro->empresa ? $retiro->empresa->nombre : 'SIN SUCURSAL' }}</td>
                    <td>{{ $retiro->user ? $retiro->user->name : 'N/A' }}</td>
                    <td>{{ $retiro->motivo }}</td>
                    <td>$-{{ number_format(abs($retiro->valor), 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-secondary">
                    <th colspan="4">TOTAL RETIROS</th>
                    <th>$-{{ number_format(abs($retirosCaja->sum('valor')), 2, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
