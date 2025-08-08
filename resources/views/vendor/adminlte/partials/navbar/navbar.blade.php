@php
    // Consulta para el cierre de caja
    $lastCashHistory = \App\Models\CashHistory::with('user')->where('estado', 'Apertura')->latest()->first();

    // Fecha actual y mes actual
    $hoy = now();
    $mesActual = $hoy->format('m');
    $mesAnoActual = $hoy->format('Y-m');
    
    // Consulta para cumpleaños sin mensajes enviados en el mes actual
    $cumpleañerosPendientes = \App\Models\HistorialClinico::whereRaw('MONTH(fecha_nacimiento) = ?', [$mesActual])
        ->whereNotNull('celular')
        ->whereRaw('CONCAT(nombres, " ", apellidos) NOT IN (
            SELECT CONCAT(hc.nombres, " ", hc.apellidos) 
            FROM historiales_clinicos hc
            INNER JOIN mensajes_enviados me ON hc.id = me.historial_id
            WHERE me.tipo = "cumpleanos"
            AND DATE_FORMAT(me.fecha_envio, "%Y-%m") = ?
        )', [$mesAnoActual])
        ->count();

    // Consulta para recordatorios de consulta sin mensajes enviados en el mes actual
    $inicioMes = $hoy->copy()->startOfMonth();
    $finMes = $hoy->copy()->endOfMonth();
    
    $consultasPendientes = \App\Models\HistorialClinico::whereNotNull('proxima_consulta')
        ->whereNotNull('celular')
        ->whereDate('proxima_consulta', '>=', $inicioMes)
        ->whereDate('proxima_consulta', '<=', $finMes)
        ->whereRaw('CONCAT(nombres, " ", apellidos) NOT IN (
            SELECT CONCAT(hc.nombres, " ", hc.apellidos)
            FROM historiales_clinicos hc
            INNER JOIN mensajes_enviados me ON hc.id = me.historial_id
            WHERE me.tipo = "consulta"
            AND DATE_FORMAT(me.fecha_envio, "%Y-%m") = ?
        )', [$mesAnoActual])
        ->count();
@endphp

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Lista de Empresas en fila --}}
        <li class="nav-item d-flex align-items-center">
            <i class="fas fa-building ml-2 mr-1 text-secondary d-none d-md-inline"></i>
            <i class="fas fa-building ml-1 mr-1 text-secondary d-md-none" style="font-size: 0.8rem;"></i>
            @if(Auth::user() && Auth::user()->is_admin)
                {{-- Si es admin, mostrar máximo 4 sucursales y botón "Ver todas" si hay más --}}
                @php
                    $todasEmpresas = \App\Models\Empresa::all();
                    $empresasLimitadas = $todasEmpresas->take(4);
                @endphp
                
                @foreach($empresasLimitadas as $index => $empresa)
                    <span class="badge badge-info mr-1 mr-md-2" style="font-size: 0.8rem; padding: 6px 8px;" title="{{ $empresa->nombre }}">
                        <span class="d-none d-md-inline" style="font-size: 0.9rem; padding: 8px 12px;">{{ $empresa->nombre }}</span>
                        <span class="d-md-none">{{ $index + 1 }}</span>
                    </span>
                @endforeach
                
                @if($todasEmpresas->count() > 4)
                    <button class="badge badge-secondary mr-1 mr-md-2" style="font-size: 0.8rem; padding: 6px 8px; border: none; cursor: pointer;" 
                            data-toggle="modal" data-target="#modalTodasEmpresas" title="Ver todas las empresas ({{ $todasEmpresas->count() }})">
                        <span class="d-none d-md-inline" style="font-size: 0.9rem; padding: 8px 12px;">Ver todas ({{ $todasEmpresas->count() }})</span>
                        <span class="d-md-none">+{{ $todasEmpresas->count() - 4 }}</span>
                    </button>
                @endif
            @elseif(Auth::user())
                {{-- Si no es admin, mostrar su empresa principal y empresas adicionales --}}
                @php
                    $todasEmpresasUsuario = Auth::user()->todasLasEmpresas();
                    $empresasLimitadas = $todasEmpresasUsuario->take(3); // Limitamos a 3 para no sobrecargar el navbar
                @endphp
                
                @if($todasEmpresasUsuario->count() > 0)
                    {{-- Mostrar hasta 3 empresas --}}
                    @foreach($empresasLimitadas as $index => $empresa)
                        <span class="badge badge-info mr-1 mr-md-2" style="font-size: 0.8rem; padding: 6px 8px;" title="{{ $empresa->nombre }}">
                            <span class="d-none d-md-inline" style="font-size: 0.9rem; padding: 8px 12px;">{{ $empresa->nombre }}</span>
                            <span class="d-md-none">{{ $index + 1 }}</span>
                        </span>
                    @endforeach
                    
                    {{-- Si tiene más de 3 empresas, mostrar botón "Ver todas" --}}
                    @if($todasEmpresasUsuario->count() > 3)
                        <button class="badge badge-secondary mr-1 mr-md-2" style="font-size: 0.8rem; padding: 6px 8px; border: none; cursor: pointer;" 
                                data-toggle="modal" data-target="#modalEmpresasUsuario" title="Ver todas mis empresas ({{ $todasEmpresasUsuario->count() }})">
                            <span class="d-none d-md-inline" style="font-size: 0.9rem; padding: 8px 12px;">Ver todas ({{ $todasEmpresasUsuario->count() }})</span>
                            <span class="d-md-none">+{{ $todasEmpresasUsuario->count() - 3 }}</span>
                        </button>
                    @endif
                @else
                    <span class="badge badge-secondary mr-2" style="font-size: 0.9rem; padding: 8px 12px;">
                        SIN EMPRESA ASIGNADA
                    </span>
                @endif
            @endif
        </li>

        {{-- Mensaje de advertencia de cierre de caja --}}
        @if(Auth::user())
            @php
                $cajaAbiertaAdvertencia = null;
                if(Auth::user()->is_admin) {
                    // Para administradores, mostrar advertencia si hay alguna caja abierta
                    $cajaAbiertaAdvertencia = \App\Models\CashHistory::with('empresa')
                                                                   ->where('estado', 'Apertura')
                                                                   ->latest()
                                                                   ->first();
                } else if(Auth::user()->empresa_id) {
                    // Para usuarios normales, verificar su empresa
                    $cajaAbiertaAdvertencia = \App\Models\CashHistory::where('empresa_id', Auth::user()->empresa_id)
                                                                    ->where('estado', 'Apertura')
                                                                    ->latest()
                                                                    ->first();
                }
            @endphp
            @if($cajaAbiertaAdvertencia)
                <li class="nav-item d-none d-md-block">
                    <div class="alert alert-danger py-1 px-3 mb-0 ml-3 d-flex align-items-center" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        @if(Auth::user()->is_admin)
                            Hay cajas abiertas - {{ $cajaAbiertaAdvertencia->empresa ? $cajaAbiertaAdvertencia->empresa->nombre : 'EMPRESA DESCONOCIDA' }} y otras
                        @else
                            Debe cerrar caja de {{ \App\Models\Empresa::find($cajaAbiertaAdvertencia->empresa_id)->nombre ?? 'EMPRESA' }} antes de salir
                        @endif
                    </div>
                </li>
            @endif
        @endif

        {{-- Notificaciones ahora están en el icono de campana en la derecha --}}

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Notificaciones con icono de campana para todos los dispositivos --}}
        @if($cumpleañerosPendientes > 0 || $consultasPendientes > 0)
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-danger navbar-badge">{{ $cumpleañerosPendientes + $consultasPendientes }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
                    <span class="dropdown-item dropdown-header">{{ $cumpleañerosPendientes + $consultasPendientes }} NOTIFICACIONES</span>
                    
                    @if($cumpleañerosPendientes > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('mensajes.cumpleanos') }}" class="dropdown-item">
                            <i class="fas fa-birthday-cake mr-2"></i> {{ $cumpleañerosPendientes }} CUMPLEAÑEROS PENDIENTES
                        </a>
                    @endif
                    
                    @if($consultasPendientes > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('mensajes.recordatorios') }}" class="dropdown-item">
                            <i class="fas fa-calendar-check mr-2"></i> {{ $consultasPendientes }} RECORDATORIOS PENDIENTES
                        </a>
                    @endif
                </div>
            </li>
        @endif

        {{-- Botón de cierre de caja para todos los usuarios --}}
        @auth
            {{-- Verificar si hay caja abierta --}}
            @php
                $cajaAbierta = null;
                if(Auth::user()->is_admin) {
                    // Para administradores, verificar si hay alguna caja abierta en cualquier empresa
                    $cajaAbierta = \App\Models\CashHistory::where('estado', 'Apertura')
                                                        ->latest()
                                                        ->first();
                } else {
                    // Para usuarios normales, verificar su empresa asignada
                    if(Auth::user()->empresa_id) {
                        $cajaAbierta = \App\Models\CashHistory::where('empresa_id', Auth::user()->empresa_id)
                                                            ->where('estado', 'Apertura')
                                                            ->latest()
                                                            ->first();
                    }
                }
            @endphp

            @if($cajaAbierta)
                {{-- Botón de cierre de caja para todos los usuarios --}}
                <li class="nav-item">
                    <form action="{{ route('show-closing-card') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger nav-link d-flex align-items-center" 
                                style="border-radius: 20px; padding: 8px 20px; transition: all 0.3s ease;">
                            <i class="fas fa-cash-register mr-2" style="font-size: 1.1em;"></i>
                            <span class="d-none d-sm-inline" style="font-weight: 500;">CERRAR CAJA</span>
                            <span class="d-inline d-sm-none" style="font-weight: 500;">CERRAR</span>
                        </button>
                    </form>
                </li>
            @else
                {{-- Botón de cierre de sesión cuando no hay caja abierta --}}
                <li class="nav-item">
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form-alt').submit();"
                       class="btn btn-outline-danger nav-link d-flex align-items-center" 
                       style="border-radius: 20px; padding: 8px 20px; transition: all 0.3s ease;">
                        <i class="fas fa-sign-out-alt mr-2" style="font-size: 1.1em;"></i>
                        <span class="d-none d-sm-inline" style="font-weight: 500;">CERRAR SESIÓN</span>
                        <span class="d-inline d-sm-none" style="font-weight: 500;">SALIR</span>
                    </a>
                    <form id="logout-form-alt" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            @endif
        @else
            {{-- Botón para usuarios no autenticados --}}
            <li class="nav-item">
                <a href="{{ route('login') }}" class="btn btn-outline-danger nav-link d-flex align-items-center" 
                   style="border-radius: 20px; padding: 8px 20px; transition: all 0.3s ease;">
                    <i class="fas fa-sign-in-alt mr-2" style="font-size: 1.1em;"></i>
                    <span class="d-none d-sm-inline" style="font-weight: 500;">INICIAR SESIÓN</span>
                    <span class="d-inline d-sm-none" style="font-weight: 500;">LOGIN</span>
                </a>
            </li>
        @endauth

        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- Right sidebar toggler link --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>
</nav>

{{-- Modal para mostrar todas las empresas --}}
<div class="modal fade" id="modalTodasEmpresas" tabindex="-1" role="dialog" aria-labelledby="modalTodasEmpresasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="modalTodasEmpresasLabel">
                    <i class="fas fa-building mr-2"></i>Todas las Sucursales
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @if(Auth::user() && Auth::user()->is_admin)
                        @foreach(\App\Models\Empresa::all() as $empresa)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-building fa-2x text-info mb-2"></i>
                                        <h6 class="card-title">{{ $empresa->nombre }}</h6>
                                        @if($empresa->direccion)
                                            <p class="card-text text-muted small">
                                                <i class="fas fa-map-marker-alt"></i> {{ $empresa->direccion }}
                                            </p>
                                        @endif
                                        @if($empresa->telefono)
                                            <p class="card-text text-muted small">
                                                <i class="fas fa-phone"></i> {{ $empresa->telefono }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal para mostrar todas las empresas del usuario no admin --}}
<div class="modal fade" id="modalEmpresasUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEmpresasUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="modalEmpresasUsuarioLabel">
                    <i class="fas fa-building mr-2"></i>Mis Sucursales Asignadas
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @if(Auth::user() && !Auth::user()->is_admin)
                        @foreach(Auth::user()->todasLasEmpresas() as $empresa)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-building fa-2x text-info mb-2"></i>
                                        <h6 class="card-title">{{ $empresa->nombre }}
                                            @if(Auth::user()->empresa_id == $empresa->id)
                                                <span class="badge badge-success ml-1" title="Sucursal Principal">Principal</span>
                                            @else
                                                <span class="badge badge-secondary ml-1" title="Sucursal Adicional">Adicional</span>
                                            @endif
                                        </h6>
                                        @if($empresa->direccion)
                                            <p class="card-text text-muted small">
                                                <i class="fas fa-map-marker-alt"></i> {{ $empresa->direccion }}
                                            </p>
                                        @endif
                                        @if($empresa->telefono)
                                            <p class="card-text text-muted small">
                                                <i class="fas fa-phone"></i> {{ $empresa->telefono }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Portapapeles --}}
<div class="modal fade" id="clipboardModal" tabindex="-1" role="dialog" aria-labelledby="clipboardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="position: fixed; bottom: 20px; right: 20px; margin: 0; max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="clipboardModalLabel">
                    <i class="fas fa-clipboard mr-2"></i>Portapapeles
                </h5>
                <button type="button" class="close text-white" id="clipboardClose" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <div class="form-group">
                    <label for="clipboardInput">Agregar nuevo elemento:</label>
                    <div class="input-group">
                        <textarea class="form-control" id="clipboardInput" rows="3" placeholder="Escribe o pega aquí el contenido..."></textarea>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="addClipboard">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Elementos guardados:</label>
                    <div id="clipboardItems" class="border rounded p-2" style="min-height: 200px; background-color: #f8f9fa;">
                        <div class="text-muted text-center py-3" id="emptyClipboard">
                            <i class="fas fa-clipboard fa-2x mb-2"></i><br>
                            No hay elementos guardados
                        </div>
                    </div>
                </div>
                
                <div class="form-group text-center">
                    <button class="btn btn-warning btn-sm" id="clearClipboard">
                        <i class="fas fa-trash"></i> Limpiar todo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Botón flotante del portapapeles --}}
<div id="clipboardFloatingBtn" class="clipboard-floating-btn" title="Portapapeles">
    <i class="fas fa-clipboard"></i>
</div>

<style>
    .btn-outline-danger {
        border: 2px solid #dc3545;
        background-color: transparent;
        color: #dc3545;
        text-transform: uppercase;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2);
    }

    .btn-outline-danger:active {
        transform: translateY(0);
    }

    .btn-outline-danger i {
        transition: transform 0.3s ease;
    }

    .btn-outline-danger:hover i {
        transform: rotate(-15deg);
    }
    
    /* Estilos para las alertas de notificación */
    .alert-info, .alert-warning {
        transition: all 0.3s ease;
    }
    
    .alert-info:hover, .alert-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Ajustes responsivos para el botón */
    @media (max-width: 576px) {
        .btn-outline-danger {
            padding: 8px 15px !important;
        }
        
        /* Ajustes para badges de empresa en móviles */
        .badge {
            font-size: 0.75rem !important;
            padding: 4px 6px !important;
            margin-right: 0.25rem !important;
        }
        
        /* Reducir espaciado en navbar para móviles */
        .navbar-nav .nav-item {
            margin-right: 0.25rem;
        }
        
        /* Ocultar algunos elementos en pantallas muy pequeñas */
        .d-xs-none {
            display: none !important;
        }
    }

    /* Estilos para el botón "Ver todas" */
    .badge.badge-secondary[data-toggle="modal"] {
        transition: all 0.3s ease;
        background-color: #6c757d;
    }

    .badge.badge-secondary[data-toggle="modal"]:hover {
        background-color: #5a6268;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Estilos para las tarjetas del modal */
    .modal-body .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e3e6f0;
    }

    .modal-body .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .modal-body .card-title {
        color: #2c3e50;
        font-weight: 600;
    }

    /* Estilos para el portapapeles */
    #clipboardModal .modal-dialog {
        transition: all 0.3s ease;
    }

    /* Botón flotante del portapapeles */
    .clipboard-floating-btn {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #007bff, #0056b3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
        transition: all 0.3s ease;
        z-index: 1050;
        color: white;
        font-size: 1.5rem;
    }

    .clipboard-floating-btn:hover {
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6);
        background: linear-gradient(135deg, #0056b3, #004085);
    }

    .clipboard-floating-btn:active {
        transform: translateY(-1px) scale(1.05);
    }

    .clipboard-floating-btn.active {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    }

    .clipboard-floating-btn.active:hover {
        background: linear-gradient(135deg, #1e7e34, #155724);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6);
    }

    /* Animación de pulso cuando hay elementos guardados */
    .clipboard-floating-btn.has-items {
        animation: clipboardPulse 2s infinite;
    }

    @keyframes clipboardPulse {
        0% {
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
        }
        50% {
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.8);
        }
        100% {
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
        }
    }

    /* Badge de contador en el botón flotante */
    .clipboard-floating-btn::after {
        content: attr(data-count);
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s ease;
    }

    .clipboard-floating-btn.has-items::after {
        opacity: 1;
        transform: scale(1);
    }

    /* Ajuste del modal para que no tape el botón */
    #clipboardModal .modal-dialog {
        position: fixed;
        bottom: 100px;
        right: 20px;
        margin: 0;
        max-width: 400px;
    }

    /* Responsivo para móviles */
    @media (max-width: 768px) {
        .clipboard-floating-btn {
            width: 50px;
            height: 50px;
            bottom: 20px;
            right: 20px;
            font-size: 1.2rem;
        }
        
        #clipboardModal .modal-dialog {
            position: fixed;
            bottom: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
            margin: 0;
        }
    }

    .clipboard-item {
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
        position: relative;
        transition: all 0.2s ease;
    }

    .clipboard-item:hover {
        border-color: #007bff;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .clipboard-item .btn-group {
        position: absolute;
        top: 5px;
        right: 5px;
    }

    .clipboard-content {
        margin-right: 80px;
        word-wrap: break-word;
        white-space: pre-wrap;
        font-family: monospace;
        font-size: 0.9em;
        max-height: 100px;
        overflow-y: auto;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clipboardToggle = document.getElementById('clipboardFloatingBtn');
    const clipboardModal = document.getElementById('clipboardModal');
    const clipboardClose = document.getElementById('clipboardClose');
    const clipboardInput = document.getElementById('clipboardInput');
    const addClipboard = document.getElementById('addClipboard');
    const clipboardItems = document.getElementById('clipboardItems');
    const emptyClipboard = document.getElementById('emptyClipboard');
    const clearClipboard = document.getElementById('clearClipboard');
    
    let isModalOpen = false;

    // Cargar elementos del localStorage al iniciar
    loadClipboardItems();
    updateFloatingButtonState();

    // Toggle del modal
    clipboardToggle.addEventListener('click', function(e) {
        e.preventDefault();
        if (isModalOpen) {
            hideModal();
        } else {
            showModal();
        }
    });

    // Cerrar modal
    clipboardClose.addEventListener('click', function() {
        hideModal();
    });

    // Cerrar modal al hacer clic fuera
    clipboardModal.addEventListener('click', function(e) {
        if (e.target === clipboardModal) {
            hideModal();
        }
    });

    // Agregar elemento
    addClipboard.addEventListener('click', function() {
        addClipboardItem();
    });

    // Agregar con Enter
    clipboardInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            addClipboardItem();
        }
    });

    // Limpiar todo
    clearClipboard.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que deseas eliminar todos los elementos del portapapeles?')) {
            localStorage.removeItem('clipboardItems');
            loadClipboardItems();
            updateFloatingButtonState();
        }
    });

    function showModal() {
        clipboardModal.style.display = 'block';
        setTimeout(() => {
            clipboardModal.classList.add('show');
            clipboardModal.style.opacity = '1';
        }, 10);
        isModalOpen = true;
        clipboardToggle.classList.add('active');
    }

    function hideModal() {
        clipboardModal.classList.remove('show');
        clipboardModal.style.opacity = '0';
        setTimeout(() => {
            clipboardModal.style.display = 'none';
        }, 300);
        isModalOpen = false;
        clipboardToggle.classList.remove('active');
    }

    function addClipboardItem() {
        const content = clipboardInput.value.trim();
        if (content === '') return;

        const items = getClipboardItems();
        const newItem = {
            id: Date.now(),
            content: content,
            timestamp: new Date().toLocaleString('es-ES')
        };

        items.unshift(newItem);
        saveClipboardItems(items);
        loadClipboardItems();
        updateFloatingButtonState();
        clipboardInput.value = '';
    }

    function getClipboardItems() {
        const stored = localStorage.getItem('clipboardItems');
        return stored ? JSON.parse(stored) : [];
    }

    function saveClipboardItems(items) {
        localStorage.setItem('clipboardItems', JSON.stringify(items));
    }

    function updateFloatingButtonState() {
        const items = getClipboardItems();
        const count = items.length;
        
        if (count > 0) {
            clipboardToggle.classList.add('has-items');
            clipboardToggle.setAttribute('data-count', count > 99 ? '99+' : count);
        } else {
            clipboardToggle.classList.remove('has-items');
            clipboardToggle.removeAttribute('data-count');
        }
    }

    function loadClipboardItems() {
        const items = getClipboardItems();
        
        if (items.length === 0) {
            clipboardItems.innerHTML = '<div class="text-muted text-center py-3" id="emptyClipboard"><i class="fas fa-clipboard fa-2x mb-2"></i><br>No hay elementos guardados</div>';
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="clipboard-item" data-id="${item.id}">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary copy-btn" title="Copiar">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-btn" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="clipboard-content">${escapeHtml(item.content)}</div>
                    <small class="text-muted">Guardado: ${item.timestamp}</small>
                </div>
            `;
        });

        clipboardItems.innerHTML = html;

        // Agregar eventos a los botones
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.closest('.clipboard-item');
                const content = item.querySelector('.clipboard-content').textContent;
                copyToClipboard(content);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.closest('.clipboard-item');
                const itemId = parseInt(item.dataset.id);
                deleteClipboardItem(itemId);
            });
        });
    }

    function deleteClipboardItem(id) {
        const items = getClipboardItems();
        const filteredItems = items.filter(item => item.id !== id);
        saveClipboardItems(filteredItems);
        loadClipboardItems();
        updateFloatingButtonState();
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Mostrar feedback visual
            const btn = event.target.closest('button');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalIcon;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 1000);
        }).catch(err => {
            console.error('Error al copiar:', err);
            alert('Error al copiar al portapapeles');
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
