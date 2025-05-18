{{-- Tarjetas de Pedidos --}}
<div class="card card-outline card-primary mb-4" id="card-pedidos-total">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-shopping-cart mr-2"></i>
            PEDIDOS TOTALES DE TODAS LAS SUCURSALES: 
            <span id="total-pedidos-global">CARGANDO...</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-pedidos-matriz">
                Matriz: $0
            </div>
            <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="progress-pedidos-rocio">
                Rocío: $0
            </div>
            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="progress-pedidos-norte">
                Norte: $0
            </div>
        </div>
    </div>
</div>

{{-- Tarjeta Plegable Pedidos Matriz --}}
<div class="card card-outline card-success card-widget collapsed-card" id="card-pedidos-matriz">
    <div class="card-header">
        <h3 class="card-title">PEDIDOS SUCURSAL MATRIZ - TOTAL: <span id="total-pedidos-matriz">CARGANDO...</span></h3>
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
                        <th>CLIENTE</th>
                        <th>TOTAL</th>
                        <th>ESTADO</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-pedidos-matriz">
                    <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-pedidos-matriz" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div>

{{-- Tarjeta Plegable Pedidos Rocío --}}
<div class="card card-outline card-info card-widget collapsed-card" id="card-pedidos-rocio">
    <div class="card-header">
        <h3 class="card-title">PEDIDOS SUCURSAL ROCÍO - TOTAL: <span id="total-pedidos-rocio">CARGANDO...</span></h3>
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
                        <th>CLIENTE</th>
                        <th>TOTAL</th>
                        <th>ESTADO</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-pedidos-rocio">
                    <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-pedidos-rocio" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div>

{{-- Tarjeta Plegable Pedidos Norte --}}
<div class="card card-outline card-warning card-widget collapsed-card" id="card-pedidos-norte">
    <div class="card-header">
        <h3 class="card-title">PEDIDOS SUCURSAL NORTE - TOTAL: <span id="total-pedidos-norte">CARGANDO...</span></h3>
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
                        <th>CLIENTE</th>
                        <th>TOTAL</th>
                        <th>ESTADO</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-pedidos-norte">
                    <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-pedidos-norte" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div> 