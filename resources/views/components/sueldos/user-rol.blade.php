{{-- Rol de pago individual --}}
<div class="rol-usuario" id="rol-usuario-{{ $user->id }}">
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>EMPLEADO: <span class="text-primary">{{ $user->name }}</span></h5>
            <h6>PERÍODO: <span class="text-secondary" id="periodo_{{ $user->id }}"></span></h6>
        </div>
        <div class="col-md-6 text-right">
            <h5>TOTAL DE PEDIDOS: <span class="text-success" id="total_{{ $user->id }}"></span></h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-movimientos">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>MOVIMIENTOS</th>
                    <th>SUCURSAL</th>
                    <th>PEDIDOS</th>
                    <th>RETIROS</th>
                </tr>
            </thead>
            <tbody id="desglose_{{ $user->id }}">
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-right mt-3">
        <button type="button" class="btn btn-primary btn-imprimir" data-user="{{ $user->id }}">
            <i class="fas fa-print"></i> IMPRIMIR
        </button>
    </div>
</div> 