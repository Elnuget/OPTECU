{{-- Tarjetas de Retiros --}}
<div class="card card-outline card-danger mb-4" id="card-retiros-total">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-money-bill-wave mr-2"></i>
            RETIROS TOTALES DE TODAS LAS SUCURSALES: 
            <span id="total-retiros-global">CARGANDO...</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-retiros-matriz">
                Matriz: $0
            </div>
            <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-retiros-rocio">
                Rocío: $0
            </div>
            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-retiros-norte">
                Norte: $0
            </div>
        </div>
    </div>
</div>

{{-- Tarjeta Plegable Retiros Matriz --}}
<div class="card card-outline card-success card-widget collapsed-card" id="card-retiros-matriz">
    <div class="card-header">
        <h3 class="card-title">RETIROS SUCURSAL MATRIZ - TOTAL: <span id="total-retiros-matriz">CARGANDO...</span></h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body" style="display: none;">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>MOTIVO</th>
                        <th>VALOR</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-retiros-matriz">
                    <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-retiros-matriz" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div>

{{-- Tarjeta Plegable Retiros Rocío --}}
<div class="card card-outline card-info card-widget collapsed-card" id="card-retiros-rocio">
    <div class="card-header">
        <h3 class="card-title">RETIROS SUCURSAL ROCÍO - TOTAL: <span id="total-retiros-rocio">CARGANDO...</span></h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body" style="display: none;">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>MOTIVO</th>
                        <th>VALOR</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-retiros-rocio">
                    <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-retiros-rocio" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div>

{{-- Tarjeta Plegable Retiros Norte --}}
<div class="card card-outline card-warning card-widget collapsed-card" id="card-retiros-norte">
    <div class="card-header">
        <h3 class="card-title">RETIROS SUCURSAL NORTE - TOTAL: <span id="total-retiros-norte">CARGANDO...</span></h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body" style="display: none;">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>MOTIVO</th>
                        <th>VALOR</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-retiros-norte">
                    <tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-retiros-norte" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div> 