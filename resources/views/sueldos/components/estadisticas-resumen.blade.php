<!-- Resumen de Estadísticas -->
<div class="row mt-3">
    <!-- Total de Ventas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>${{ number_format($pedidos->sum('total'), 2, ',', '.') }}</h3>
                <p>TOTAL DE VENTAS</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    
    <!-- Total de Saldo -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>${{ number_format($pedidos->sum('saldo'), 2, ',', '.') }}</h3>
                <p>SALDO PENDIENTE</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Retiros de Caja -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>$-{{ number_format(isset($retirosCaja) ? abs($retirosCaja->sum('valor')) : 0, 2, ',', '.') }}</h3>
                <p>RETIROS DE CAJA</p>
            </div>
            <div class="icon">
                <i class="fas fa-cash-register"></i>
            </div>
        </div>
    </div>
    
    <!-- Cantidad de Pedidos -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $pedidos->count() }}</h3>
                <p>PEDIDOS REALIZADOS</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    
    <!-- Balance Neto (Ventas - Retiros) -->
    <div class="col-lg-3 col-6">
        <div class="small-box" style="background-color: #6f42c1; color: white;">
            <div class="inner">
                <h3>${{ number_format($pedidos->sum('total') + (isset($retirosCaja) ? $retirosCaja->sum('valor') : 0), 2, ',', '.') }}</h3>
                <p>BALANCE NETO</p>
            </div>
            <div class="icon">
                <i class="fas fa-balance-scale"></i>
            </div>
        </div>
    </div>
</div>
