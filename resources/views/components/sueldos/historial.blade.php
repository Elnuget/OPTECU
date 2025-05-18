{{-- Tarjetas de Historial de Caja --}}
<div class="card card-outline card-info mb-4" id="card-historial-total">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cash-register mr-2"></i>
            HISTORIAL DE CAJA DE TODAS LAS SUCURSALES
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL INGRESOS</span>
                        <span class="info-box-number" id="total-ingresos-global">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-danger">
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL EGRESOS</span>
                        <span class="info-box-number" id="total-egresos-global">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <div class="info-box-content">
                        <span class="info-box-text">BALANCE</span>
                        <span class="info-box-number" id="total-balance-global">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tarjeta Plegable Historial Matriz --}}
<div class="card card-outline card-success card-widget collapsed-card" id="card-historial-matriz">
    <div class="card-header">
        <h3 class="card-title">HISTORIAL DE CAJA MATRIZ - BALANCE: <span id="total-historial-matriz">CARGANDO...</span></h3>
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
                        <th>TIPO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>MONTO</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-historial-matriz">
                    <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-historial-matriz" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div>

{{-- Tarjeta Plegable Historial Rocío --}}
<div class="card card-outline card-info card-widget collapsed-card" id="card-historial-rocio">
    <div class="card-header">
        <h3 class="card-title">HISTORIAL DE CAJA ROCÍO - BALANCE: <span id="total-historial-rocio">CARGANDO...</span></h3>
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
                        <th>TIPO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>MONTO</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-historial-rocio">
                    <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-historial-rocio" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div>

{{-- Tarjeta Plegable Historial Norte --}}
<div class="card card-outline card-warning card-widget collapsed-card" id="card-historial-norte">
    <div class="card-header">
        <h3 class="card-title">HISTORIAL DE CAJA NORTE - BALANCE: <span id="total-historial-norte">CARGANDO...</span></h3>
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
                        <th>TIPO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>MONTO</th>
                        <th>USUARIO</th>
                    </tr>
                </thead>
                <tbody id="desglose-historial-norte">
                    <tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="overlay dark" id="loading-overlay-historial-norte" style="display: none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
</div> 