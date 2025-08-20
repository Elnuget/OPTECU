@php
    $currentUser = Auth::user();
    $lastCashHistory = null;
    $previousCashHistory = null;
    $isClosed = true;
    $userEmpresa = null;
    $userEmpresas = collect();
    $sumCaja = 0;
    $empresasCaja = [];
    $aperturaAutomatica = false;
    $cajasAbiertas = 0;
    $ultimaCajaAbierta = null; // Para preseleccionar la última caja abierta
    
    if ($currentUser) {
        // Obtener empresas según el tipo de usuario
        if ($currentUser->is_admin) {
            // Para administradores, obtener todas las empresas del sistema
            $userEmpresas = \App\Models\Empresa::all();
            $aperturaAutomatica = false; // Los admins no tienen apertura automática
        } else {
            // Para usuarios no admin, intentar apertura automática
            $controller = new \App\Http\Controllers\CashHistoryController();
            $cajasAbiertas = $controller->autoOpenCashForUser($currentUser);
            $aperturaAutomatica = $cajasAbiertas > 0;
            
            // Obtener todas sus empresas asignadas
            $userEmpresas = $currentUser->todasLasEmpresas();
        }
        
        // Obtener la última caja abierta para preseleccionar
        if ($currentUser->is_admin) {
            $ultimaCajaAbierta = \App\Models\CashHistory::with('empresa')
                                                       ->where('estado', 'Apertura')
                                                       ->latest()
                                                       ->first();
        } else {
            $ultimaCajaAbierta = \App\Models\CashHistory::with('empresa')
                                                       ->whereIn('empresa_id', $userEmpresas->pluck('id'))
                                                       ->where('estado', 'Apertura')
                                                       ->latest()
                                                       ->first();
        }
        
        if ($userEmpresas->count() > 0) {
            $today = now()->format('Y-m-d');
            
            // Preparar información de caja para cada empresa
            foreach ($userEmpresas as $empresa) {
                $lastHistory = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                                     ->latest()
                                                     ->first();
                
                $previousHistory = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                                         ->where('estado', 'Cierre')
                                                         ->latest()
                                                         ->first();
                
                // Verificar si hay apertura de hoy (solo para información)
                $aperturaHoy = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                                     ->where('estado', 'Apertura')
                                                     ->whereDate('created_at', $today)
                                                     ->first();
                
                // Verificar si hay cajas abiertas de días anteriores (sin cerrar)
                $cajaAbiertaAnterior = null;
                // Lógica de cajas pendientes eliminada - ya no se verifican cajas del día anterior
                
                $needsClosure = false; // Siempre false - no forzar cierre de cajas anteriores
                
                $empresasCaja[] = [
                    'empresa' => $empresa,
                    'lastHistory' => $lastHistory,
                    'previousHistory' => $previousHistory,
                    'aperturaHoy' => $aperturaHoy,
                    'cajaAbiertaAnterior' => $cajaAbiertaAnterior,
                    'isClosed' => !$lastHistory || $lastHistory->estado !== 'Apertura', // Simple: cerrada si no hay historial o último estado es Cierre
                    'needsClosure' => $needsClosure,
                    'sumCaja' => \App\Models\Caja::where('empresa_id', $empresa->id)->sum('valor'),
                    'isUltimaCajaAbierta' => $ultimaCajaAbierta && $ultimaCajaAbierta->empresa_id == $empresa->id
                ];
            }
            
            // Lógica para determinar qué mostrar:
            // 1. Si hay cajas abiertas actualmente pero no hay sucursal en cache, permitir selección
            // 2. Si hay sucursal en cache y caja abierta, no mostrar apertura
            // 3. Si NO hay cajas abiertas, mostrar apertura (sin importar historial del día)
            
            // Verificar si hay alguna caja ACTUALMENTE abierta (estado = 'Apertura')
            $hayAperturasActivas = collect($empresasCaja)->contains(function ($empresaData) {
                return $empresaData['lastHistory'] && $empresaData['lastHistory']->estado === 'Apertura';
            });
            
            if ($hayAperturasActivas) {
                // Hay cajas abiertas - la decisión final se hará en JavaScript verificando el cache
                $isClosed = 'check_cache'; // Valor especial para verificar en JS
            } else {
                $isClosed = true; // No hay cajas abiertas, mostrar apertura
            }
            
            // Si solo hay una empresa, usar la lógica anterior
            if ($userEmpresas->count() == 1) {
                $userEmpresa = $userEmpresas->first();
                $lastCashHistory = $empresasCaja[0]['lastHistory'];
                $previousCashHistory = $empresasCaja[0]['previousHistory'];
                $sumCaja = $empresasCaja[0]['sumCaja'];
                
                // Para una empresa, verificar si la caja está actualmente abierta
                $cajaActualmenteAbierta = $lastCashHistory && $lastCashHistory->estado === 'Apertura';
                
                if ($cajaActualmenteAbierta) {
                    // Hay caja abierta - la decisión final se hará en JavaScript verificando el cache
                    $isClosed = 'check_cache'; // Valor especial para verificar en JS
                } else {
                    $isClosed = true; // No hay caja abierta, mostrar apertura
                }
            }
        }
    }
    
    $showClosingCard = session('showClosingCard', false);
@endphp

{{-- Notificación de apertura automática (solo para usuarios no administradores) --}}
@if($aperturaAutomatica && !$currentUser->is_admin)
<div class="alert alert-success alert-dismissible position-fixed" 
     style="top: 20px; right: 20px; z-index: 9998; min-width: 280px; max-width: 90vw;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h6 class="mb-1"><i class="icon fas fa-check"></i> Apertura Automática Exitosa</h6>
    @if($cajasAbiertas == 1)
        <small>Se ha abierto automáticamente 1 caja al iniciar sesión.</small>
    @else
        <small>Se han abierto automáticamente {{ $cajasAbiertas }} cajas al iniciar sesión.</small>
    @endif
</div>

<script>
    // Auto-dismiss notification after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
</script>
@endif

{{-- Modal de CAJAS PENDIENTES DE CIERRE eliminado - causaba problemas --}}

{{-- Tarjeta de Apertura de Caja (para todos los usuarios con empresas asignadas) --}}
@if($currentUser && $isClosed === true && $userEmpresas->count() > 0)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center p-3" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;" 
     id="aperturaModal">
    <div class="w-100" style="max-width: 600px; max-height: 90vh;">
        <div class="card shadow" style="max-height: 90vh;">
            <div class="card-body bg-light" style="max-height: 90vh; overflow-y: auto;">
                <div class="text-center mb-4">
                    <h6 class="text-info"><i class="fas fa-cash-register fa-lg mr-2"></i></h6>
                    <h5 class="mb-2" id="aperturaTitle">Apertura de Caja</h5>
                    @if($userEmpresas->count() == 1)
                        <h6 class="text-warning">{{ strtoupper($userEmpresa->nombre) }}</h6>
                    @else
                        <h6 class="text-info">SELECCIONE SUCURSAL</h6>
                        @if($currentUser->is_admin)
                            <p class="text-muted mb-2"><small>Como administrador, puede abrir cualquier caja</small></p>
                        @endif
                    @endif
                    <div id="seleccionSucursalInfo" class="alert alert-info" style="display: none;">
                        <i class="fas fa-info-circle mr-2"></i>
                        <small>Se detectaron cajas abiertas. Seleccione una sucursal para trabajar en ella o abra una nueva caja.</small>
                    </div>
                </div>
                @if($userEmpresas->count() == 1)
                    {{-- Una sola empresa --}}
                    @if($lastCashHistory && $lastCashHistory->estado === 'Apertura')
                        {{-- Caja ya abierta - permitir selección --}}
                        <div class="alert alert-success mb-3">
                            <h5><i class="fas fa-cash-register mr-2"></i><strong>Caja Abierta Detectada</strong></h5>
                            <p class="mb-1">La caja de <strong>{{ $userEmpresa->nombre }}</strong> ya está abierta.</p>
                            <p class="mb-1">Abierta el: {{ $lastCashHistory->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mb-0">Usuario: {{ $lastCashHistory->user->name }}</p>
                        </div>
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                            <button type="button" class="btn btn-info btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1" 
                                    onclick="trabajarEnSucursal('{{ $userEmpresa->id }}', '{{ $userEmpresa->nombre }}');">
                                <i class="fas fa-building mr-2"></i>Trabajar en esta Sucursal
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <span class="d-none d-md-inline">Salir</span>
                            </a>
                        </div>
                        
                        <script>
                            function trabajarEnSucursal(empresaId, empresaNombre) {
                                console.log('CWrapper - Trabajando en sucursal:', empresaId, empresaNombre);
                                
                                // Guardar en cache
                                SucursalCache.guardar(empresaId, empresaNombre);
                                
                                // Ocultar modal inmediatamente
                                const aperturaModal = document.getElementById('aperturaModal');
                                const contentWrapper = document.getElementById('contentWrapper');
                                
                                if (aperturaModal) {
                                    aperturaModal.style.display = 'none';
                                    aperturaModal.style.visibility = 'hidden';
                                }
                                if (contentWrapper) {
                                    contentWrapper.style.filter = 'none';
                                }
                                
                                // Recargar página después de un pequeño delay
                                setTimeout(function() {
                                    window.location.reload();
                                }, 200);
                            }
                        </script>
                    @else
                        {{-- Caja cerrada - permitir apertura --}}
                        @if($previousCashHistory)
                            <div class="alert alert-info mb-3">
                                <p class="mb-1"><strong>Último Cierre:</strong></p>
                                <p class="mb-1">Usuario: {{ $previousCashHistory->user->name }}</p>
                                <p class="mb-1">Fecha: {{ $previousCashHistory->created_at->format('d/m/Y H:i') }}</p>
                                <p class="mb-0">Monto: ${{ number_format($previousCashHistory->monto, 2) }}</p>
                            </div>
                        @endif

                        <form action="{{ route('cash-histories.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="monto">Monto Inicial ({{ $userEmpresa->nombre }})</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-lg" 
                                           name="monto" id="monto" value="{{ number_format($sumCaja, 2, '.', '') }}" readonly>
                                </div>
                            </div>
                            <input type="hidden" name="estado" value="Apertura">
                            <input type="hidden" name="empresa_id" value="{{ $userEmpresa->id }}">
                            
                            <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                                <button type="submit" class="btn btn-success btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1" onclick="SucursalCache.guardar('{{ $userEmpresa->id }}', '{{ $userEmpresa->nombre }}')">
                                    <i class="fas fa-door-open mr-2"></i>Abrir Caja
                                </button>
                                <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    <span class="d-none d-md-inline">Salir</span>
                                </a>
                            </div>
                        </form>
                    @endif
                @else
                    {{-- Múltiples empresas - selector dinámico --}}
                    <form id="multiEmpresaForm" action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Seleccionar Sucursal:</label>
                            <div class="row">
                                @foreach($empresasCaja as $index => $empresaData)
                                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                        @if($empresaData['isClosed'])
                                            {{-- Caja cerrada - permitir apertura --}}
                                            <button type="button" class="btn btn-outline-primary btn-block empresa-btn h-100 d-flex flex-column justify-content-center align-items-start p-3" 
                                                    data-empresa-id="{{ $empresaData['empresa']->id }}"
                                                    data-monto="{{ number_format($empresaData['sumCaja'], 2, '.', '') }}"
                                                    data-last-close="{{ $empresaData['previousHistory'] ? $empresaData['previousHistory']->created_at->format('d/m/Y H:i') : 'Sin cierres anteriores' }}"
                                                    data-last-user="{{ $empresaData['previousHistory'] ? $empresaData['previousHistory']->user->name : 'N/A' }}"
                                                    data-last-amount="{{ $empresaData['previousHistory'] ? number_format($empresaData['previousHistory']->monto, 2) : '0.00' }}"
                                                    data-action="apertura"
                                                    style="min-height: 80px; border: 2px solid; cursor: pointer; transition: all 0.2s ease;">
                                                <div class="d-flex align-items-center w-100">
                                                    <i class="fas fa-building mr-2 flex-shrink-0" style="font-size: 1.1em;"></i>
                                                    <div class="text-left flex-grow-1">
                                                        <div class="font-weight-bold empresa-nombre" style="font-size: 0.95em; line-height: 1.2;">{{ strtoupper($empresaData['empresa']->nombre) }}</div>
                                                        <small class="text-muted d-block" style="font-size: 0.8em;">Caja Cerrada</small>
                                                        <small class="text-success font-weight-bold" style="font-size: 0.85em;">${{ number_format($empresaData['sumCaja'], 2, '.', ',') }}</small>
                                                    </div>
                                                </div>
                                            </button>
                                        @else
                                            {{-- Caja abierta - permitir selección para trabajar --}}
                                            <button type="button" class="btn btn-outline-success btn-block empresa-select-btn h-100 d-flex flex-column justify-content-center align-items-start p-3" 
                                                    data-empresa-id="{{ $empresaData['empresa']->id }}"
                                                    data-empresa-nombre="{{ $empresaData['empresa']->nombre }}"
                                                    data-action="seleccionar"
                                                    style="min-height: 80px; border: 2px solid #28a745; cursor: pointer; transition: all 0.2s ease;">
                                                <div class="d-flex align-items-center w-100">
                                                    <i class="fas fa-cash-register mr-2 flex-shrink-0 text-success" style="font-size: 1.1em;"></i>
                                                    <div class="text-left flex-grow-1">
                                                        <div class="font-weight-bold empresa-nombre" style="font-size: 0.95em; line-height: 1.2;">{{ strtoupper($empresaData['empresa']->nombre) }}</div>
                                                        <small class="text-success d-block font-weight-bold" style="font-size: 0.8em;">Caja Abierta</small>
                                                        <small class="text-muted" style="font-size: 0.75em;">
                                                            Abierta: {{ $empresaData['lastHistory']->created_at->format('d/m H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" id="empresa_id_hidden" name="empresa_id">
                            <input type="hidden" id="action_type" name="action_type" value="apertura">
                        </div>

                        <div id="empresa_info" class="alert alert-info" style="display: none;">
                            <p class="mb-1"><strong>Último Cierre:</strong></p>
                            <p class="mb-1">Usuario: <span id="info_user">N/A</span></p>
                            <p class="mb-1">Fecha: <span id="info_date">N/A</span></p>
                            <p class="mb-0">Monto: $<span id="info_amount">0.00</span></p>
                        </div>

                        <div class="form-group" id="monto_group">
                            <label for="monto_multi">Monto Inicial:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg" 
                                       name="monto" id="monto_multi" value="{{ number_format($sumCaja, 2, '.', '') }}" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Apertura">
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                            <button type="submit" class="btn btn-success btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1" id="btn_abrir" disabled onclick="guardarSucursalCacheMulti()">
                                <i class="fas fa-door-open mr-2"></i><span id="btn_text">Abrir Caja</span>
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <span class="d-none d-md-inline">Salir</span>
                            </a>
                        </div>
                    </form>

                    <script>
                        // Manejar clics en botones de apertura de caja
                        document.querySelectorAll('.empresa-btn').forEach(button => {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Remover selección anterior
                                document.querySelectorAll('.empresa-btn, .empresa-select-btn').forEach(btn => {
                                    btn.classList.remove('btn-primary', 'btn-success');
                                    if (btn.classList.contains('empresa-btn')) {
                                        btn.classList.add('btn-outline-primary');
                                        btn.style.borderColor = '#007bff';
                                        btn.style.backgroundColor = 'transparent';
                                        btn.style.color = '#007bff';
                                    } else {
                                        btn.classList.add('btn-outline-success');
                                        btn.style.borderColor = '#28a745';
                                        btn.style.backgroundColor = 'transparent';
                                        btn.style.color = '#28a745';
                                    }
                                });
                                
                                // Marcar como seleccionado
                                this.classList.remove('btn-outline-primary');
                                this.classList.add('btn-primary');
                                this.style.borderColor = '#0056b3';
                                this.style.backgroundColor = '#007bff';
                                this.style.color = 'white';
                                
                                // Actualizar campos para apertura
                                const empresaId = this.getAttribute('data-empresa-id');
                                const monto = this.getAttribute('data-monto');
                                const lastUser = this.getAttribute('data-last-user');
                                const lastClose = this.getAttribute('data-last-close');
                                const lastAmount = this.getAttribute('data-last-amount');
                                
                                document.getElementById('empresa_id_hidden').value = empresaId;
                                document.getElementById('monto_multi').value = monto;
                                document.getElementById('action_type').value = 'apertura';
                                
                                // Mostrar información y campos de apertura
                                document.getElementById('info_user').textContent = lastUser;
                                document.getElementById('info_date').textContent = lastClose;
                                document.getElementById('info_amount').textContent = lastAmount;
                                document.getElementById('empresa_info').style.display = 'block';
                                document.getElementById('monto_group').style.display = 'block';
                                
                                // Actualizar botón
                                document.getElementById('btn_abrir').disabled = false;
                                document.getElementById('btn_text').textContent = 'Abrir Caja';
                                document.getElementById('btn_abrir').className = 'btn btn-success btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1';
                            }, { passive: false });
                            
                            // Eventos táctiles
                            button.addEventListener('touchstart', function(e) {
                                this.style.transform = 'scale(0.98)';
                            }, { passive: true });
                            
                            button.addEventListener('touchend', function(e) {
                                this.style.transform = 'scale(1)';
                            }, { passive: true });
                        });

                        // Manejar clics en botones de selección de sucursal (caja ya abierta)
                        document.querySelectorAll('.empresa-select-btn').forEach(button => {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                console.log('CWrapper - Clic en botón de selección de sucursal');
                                
                                // Remover selección anterior
                                document.querySelectorAll('.empresa-btn, .empresa-select-btn').forEach(btn => {
                                    btn.classList.remove('btn-primary', 'btn-success');
                                    if (btn.classList.contains('empresa-btn')) {
                                        btn.classList.add('btn-outline-primary');
                                        btn.style.borderColor = '#007bff';
                                        btn.style.backgroundColor = 'transparent';
                                        btn.style.color = '#007bff';
                                    } else {
                                        btn.classList.add('btn-outline-success');
                                        btn.style.borderColor = '#28a745';
                                        btn.style.backgroundColor = 'transparent';
                                        btn.style.color = '#28a745';
                                    }
                                });
                                
                                // Marcar como seleccionado
                                this.classList.remove('btn-outline-success');
                                this.classList.add('btn-success');
                                this.style.borderColor = '#1e7e34';
                                this.style.backgroundColor = '#28a745';
                                this.style.color = 'white';
                                
                                // Configurar para selección de sucursal
                                const empresaId = this.getAttribute('data-empresa-id');
                                const empresaNombre = this.getAttribute('data-empresa-nombre');
                                
                                console.log('CWrapper - Seleccionando empresa:', empresaId, empresaNombre);
                                
                                document.getElementById('empresa_id_hidden').value = empresaId;
                                document.getElementById('action_type').value = 'seleccionar';
                                
                                // Ocultar campos de apertura
                                document.getElementById('empresa_info').style.display = 'none';
                                document.getElementById('monto_group').style.display = 'none';
                                
                                // Actualizar botón para selección
                                document.getElementById('btn_abrir').disabled = false;
                                document.getElementById('btn_text').textContent = 'Seleccionar Sucursal';
                                document.getElementById('btn_abrir').className = 'btn btn-info btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1';
                                
                                // Configurar evento para solo guardar en cache y ocultar modal inmediatamente
                                document.getElementById('btn_abrir').onclick = function() {
                                    console.log('CWrapper - Guardando sucursal en cache y ocultando modal');
                                    SucursalCache.guardar(empresaId, empresaNombre);
                                    
                                    // Ocultar modal inmediatamente
                                    const aperturaModal = document.getElementById('aperturaModal');
                                    const contentWrapper = document.getElementById('contentWrapper');
                                    
                                    if (aperturaModal) {
                                        aperturaModal.style.display = 'none';
                                        aperturaModal.style.visibility = 'hidden';
                                    }
                                    if (contentWrapper) {
                                        contentWrapper.style.filter = 'none';
                                    }
                                    
                                    // Recargar página después de un pequeño delay
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 200);
                                    
                                    return false;
                                };
                            }, { passive: false });
                            
                            // Eventos táctiles
                            button.addEventListener('touchstart', function(e) {
                                this.style.transform = 'scale(0.98)';
                            }, { passive: true });
                            
                            button.addEventListener('touchend', function(e) {
                                this.style.transform = 'scale(1)';
                            }, { passive: true });
                        });
                    </script>
                @endif
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal dinámico para check_cache - se crea solo si es necesario --}}
@if($currentUser && $isClosed === 'check_cache' && $userEmpresas->count() > 0)
<script>
    console.log('CWrapper - Script de modal dinámico cargado');
    console.log('CWrapper - isClosed:', '{{ $isClosed }}');
    console.log('CWrapper - userEmpresas count:', {{ $userEmpresas->count() }});
    
    // Crear modal dinámicamente solo si no hay sucursal en cache
    document.addEventListener('DOMContentLoaded', function() {
        console.log('CWrapper - DOM cargado, verificando cache...');
        verificarCacheYCrearModal();
    });

    function verificarCacheYCrearModal() {
        console.log('CWrapper - Verificando cache...');
        console.log('CWrapper - SucursalCache disponible:', !!window.SucursalCache);
        
        if (window.SucursalCache) {
            const sucursalEnCache = SucursalCache.obtener();
            console.log('CWrapper - Sucursal en cache:', sucursalEnCache);
            
            if (!sucursalEnCache || !sucursalEnCache.id) {
                console.log('CWrapper - No hay sucursal en cache, creando modal...');
                crearModalApertura();
            } else {
                console.log('CWrapper - Sucursal en cache encontrada, no crear modal');
            }
        } else {
            console.log('CWrapper - SucursalCache no disponible, esperando...');
            setTimeout(verificarCacheYCrearModal, 100);
        }
    }

    function crearModalApertura() {
        console.log('CWrapper - Creando modal dinámico...');
        
        // Crear elementos del modal paso a paso
        const modal = document.createElement('div');
        modal.className = 'position-fixed w-100 h-100 d-flex align-items-center justify-content-center p-3';
        modal.style.cssText = 'background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;';
        modal.id = 'aperturaModalDinamico';
        
        const wrapper = document.createElement('div');
        wrapper.className = 'w-100';
        wrapper.style.cssText = 'max-width: 600px; max-height: 90vh;';
        
        const card = document.createElement('div');
        card.className = 'card shadow';
        card.style.cssText = 'max-height: 90vh;';
        
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body bg-light';
        cardBody.style.cssText = 'max-height: 90vh; overflow-y: auto;';
        
        // Crear header
        const header = document.createElement('div');
        header.className = 'text-center mb-4';
        header.innerHTML = `
            <h6 class="text-info"><i class="fas fa-cash-register fa-lg mr-2"></i></h6>
            <h5 class="mb-2">Seleccionar Sucursal de Trabajo</h5>
            <h6 class="text-info">SELECCIONE SUCURSAL</h6>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <small>Seleccione una sucursal para trabajar. Si la caja está cerrada, podrá abrirla.</small>
            </div>
        `;
        
        // Crear contenido según el tipo de usuario
        const content = document.createElement('div');
        @if($userEmpresas->count() == 1)
            // Una sola empresa
            @if($lastCashHistory && $lastCashHistory->estado === 'Apertura')
                content.innerHTML = `
                    <div class="alert alert-success mb-3">
                        <h5><i class="fas fa-cash-register mr-2"></i><strong>Caja Abierta Detectada</strong></h5>
                        <p class="mb-1">La caja de <strong>{{ $userEmpresa->nombre }}</strong> ya está abierta.</p>
                        <p class="mb-1">Abierta el: {{ $lastCashHistory->created_at->format('d/m/Y H:i') }}</p>
                        <p class="mb-0">Usuario: {{ $lastCashHistory->user->name }}</p>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                        <button type="button" class="btn btn-info btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1" 
                                onclick="trabajarEnSucursalDinamico('{{ $userEmpresa->id }}', '{{ $userEmpresa->nombre }}', 'seleccionar', this);">
                            <i class="fas fa-building mr-2"></i>Trabajar en esta Sucursal
                        </button>
                        <a href="{{ route('logout') }}" class="btn btn-danger btn-lg">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span class="d-none d-md-inline">Salir</span>
                        </a>
                    </div>
                `;
            @endif
        @else
            // Múltiples empresas - mostrar TODAS las empresas disponibles
            let empresasHTML = '';
            @foreach($userEmpresas as $empresa)
                @php
                    $empresaData = null;
                    $tieneHistorial = false;
                    $cajaAbierta = false;
                    $sumCaja = 0;
                    $lastHistory = null;
                    $previousHistory = null;
                    
                    foreach($empresasCaja as $empData) {
                        if($empData['empresa']->id == $empresa->id) {
                            $empresaData = $empData;
                            $tieneHistorial = $empData['lastHistory'] !== null;
                            $cajaAbierta = !$empData['isClosed'];
                            $sumCaja = $empData['sumCaja'];
                            $lastHistory = $empData['lastHistory'];
                            $previousHistory = $empData['previousHistory'];
                            break;
                        }
                    }
                    
                    // Si no está en empresasCaja, obtener datos básicos
                    if (!$empresaData) {
                        // Obtener suma de caja para empresas sin historial
                        $sumCaja = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                    ->where('estado', 'Cierre')
                                    ->sum('monto');
                        $previousHistory = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                         ->where('estado', 'Cierre')
                                         ->latest()
                                         ->first();
                    }
                @endphp
                
                @if($cajaAbierta)
                    // Caja abierta - botón verde
                    empresasHTML += `
                        <div class="col-12 col-sm-6 col-lg-4 mb-3">
                            <button type="button" class="btn btn-outline-success btn-block empresa-select-btn-dinamico h-100 d-flex flex-column justify-content-center align-items-start p-3" 
                                    data-empresa-id="{{ $empresa->id }}"
                                    data-empresa-nombre="{{ $empresa->nombre }}"
                                    data-action="seleccionar"
                                    style="min-height: 90px; border: 2px solid #28a745; cursor: pointer;">
                                <div class="d-flex align-items-center w-100">
                                    <i class="fas fa-cash-register mr-2 flex-shrink-0 text-success"></i>
                                    <div class="text-left flex-grow-1">
                                        <div class="font-weight-bold">{{ strtoupper($empresa->nombre) }}</div>
                                        <small class="text-success d-block font-weight-bold">Caja Abierta</small>
                                        <small class="text-muted">Abierta: {{ $lastHistory ? $lastHistory->created_at->format('d/m H:i') : 'N/A' }}</small>
                                        <small class="text-success font-weight-bold">\${{ number_format($sumCaja, 2, '.', ',') }}</small>
                                    </div>
                                </div>
                            </button>
                        </div>
                    `;
                @else
                    // Caja cerrada - botón azul
                    empresasHTML += `
                        <div class="col-12 col-sm-6 col-lg-4 mb-3">
                            <button type="button" class="btn btn-outline-info btn-block empresa-select-btn-dinamico h-100 d-flex flex-column justify-content-center align-items-start p-3" 
                                    data-empresa-id="{{ $empresa->id }}"
                                    data-empresa-nombre="{{ $empresa->nombre }}"
                                    data-action="apertura"
                                    data-monto="{{ number_format($sumCaja, 2, '.', '') }}"
                                    data-last-close="{{ $previousHistory ? $previousHistory->created_at->format('d/m/Y H:i') : 'Sin cierres anteriores' }}"
                                    data-last-user="{{ $previousHistory ? $previousHistory->user->name : 'N/A' }}"
                                    data-last-amount="{{ $previousHistory ? number_format($previousHistory->monto, 2) : '0.00' }}"
                                    style="min-height: 90px; border: 2px solid #17a2b8; cursor: pointer;">
                                <div class="d-flex align-items-center w-100">
                                    <i class="fas fa-building mr-2 flex-shrink-0 text-info"></i>
                                    <div class="text-left flex-grow-1">
                                        <div class="font-weight-bold">{{ strtoupper($empresa->nombre) }}</div>
                                        <small class="text-info d-block font-weight-bold">Caja Cerrada</small>
                                        <small class="text-muted">Clic para abrir caja</small>
                                        <small class="text-success font-weight-bold">\${{ number_format($sumCaja, 2, '.', ',') }}</small>
                                    </div>
                                </div>
                            </button>
                        </div>
                    `;
                @endif
            @endforeach
            
            content.innerHTML = `
                <div class="form-group">
                    <label>Seleccionar Sucursal:</label>
                    <div class="row">
                        ${empresasHTML}
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('logout') }}" class="btn btn-danger btn-lg">
                        <i class="fas fa-sign-out-alt mr-2"></i>Salir
                    </a>
                </div>
            `;
        @endif
        
        // Ensamblar modal
        cardBody.appendChild(header);
        cardBody.appendChild(content);
        card.appendChild(cardBody);
        wrapper.appendChild(card);
        modal.appendChild(wrapper);
        document.body.appendChild(modal);
        
        // Aplicar blur al content wrapper
        const contentWrapper = document.getElementById('contentWrapper');
        if (contentWrapper) {
            contentWrapper.style.filter = 'blur(5px)';
        }
        
        // Configurar eventos
        configurarEventosBotonesDinamicos();
        
        console.log('CWrapper - Modal dinámico creado y mostrado');
    }

    function trabajarEnSucursalDinamico(empresaId, empresaNombre, action, button) {
        console.log('CWrapper - Trabajando en sucursal dinámica:', empresaId, empresaNombre, 'Acción:', action);
        
        if (action === 'seleccionar') {
            // Caja ya abierta, solo seleccionar sucursal
            SucursalCache.guardar(empresaId, empresaNombre);
            
            // Remover modal inmediatamente
            const modal = document.getElementById('aperturaModalDinamico');
            if (modal) {
                modal.remove();
            }
            
            // Quitar blur
            const contentWrapper = document.getElementById('contentWrapper');
            if (contentWrapper) {
                contentWrapper.style.filter = 'none';
            }
            
            // Recargar página
            setTimeout(() => window.location.reload(), 100);
            
        } else if (action === 'apertura') {
            // Caja cerrada, abrir caja
            const monto = button.getAttribute('data-monto');
            const lastUser = button.getAttribute('data-last-user');
            const lastClose = button.getAttribute('data-last-close');
            const lastAmount = button.getAttribute('data-last-amount');
            
            console.log('CWrapper - Abriendo caja con datos:', {
                empresaId, empresaNombre, monto, lastUser, lastClose, lastAmount
            });
            
            // Crear formulario de apertura dinámicamente
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("cash-histories.store") }}';
            
            // Token CSRF
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Empresa ID
            const empresaInput = document.createElement('input');
            empresaInput.type = 'hidden';
            empresaInput.name = 'empresa_id';
            empresaInput.value = empresaId;
            form.appendChild(empresaInput);
            
            // Monto
            const montoInput = document.createElement('input');
            montoInput.type = 'hidden';
            montoInput.name = 'monto';
            montoInput.value = monto;
            form.appendChild(montoInput);
            
            // Estado
            const estadoInput = document.createElement('input');
            estadoInput.type = 'hidden';
            estadoInput.name = 'estado';
            estadoInput.value = 'Apertura';
            form.appendChild(estadoInput);
            
            // Agregar formulario al DOM y enviarlo
            document.body.appendChild(form);
            
            // Guardar en cache antes del envío
            SucursalCache.guardar(empresaId, empresaNombre);
            
            console.log('CWrapper - Enviando formulario de apertura...');
            form.submit();
        }
    }

    function configurarEventosBotonesDinamicos() {
        console.log('CWrapper - Configurando eventos de botones dinámicos');
        // Eventos para botones de selección de sucursal
        document.querySelectorAll('.empresa-select-btn-dinamico').forEach(button => {
            button.addEventListener('click', function() {
                const empresaId = this.getAttribute('data-empresa-id');
                const empresaNombre = this.getAttribute('data-empresa-nombre');
                const action = this.getAttribute('data-action');
                console.log('CWrapper - Botón clickeado:', empresaId, empresaNombre, 'Acción:', action);
                trabajarEnSucursalDinamico(empresaId, empresaNombre, action, this);
            });
        });
    }
</script>
@endif

{{-- Tarjeta de Cierre de Caja (para todos los usuarios con empresas asignadas) --}}
@if($showClosingCard && $currentUser && $userEmpresas->count() > 0)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center p-3" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="w-100" style="max-width: 600px; max-height: 90vh;">
        <div class="card shadow" style="max-height: 90vh;">
            <div class="card-body bg-light" style="max-height: 90vh; overflow-y: auto;">
                <div class="text-center mb-4">
                    <h6 class="text-danger"><i class="fas fa-cash-register fa-lg mr-2"></i></h6>
                    <h5 class="mb-2">Cierre de Caja</h5>
                    @if($userEmpresas->count() == 1)
                        <h6 class="text-warning">{{ strtoupper($userEmpresa->nombre) }}</h6>
                    @else
                        <h6 class="text-info">SELECCIONE SUCURSAL</h6>
                        @if($currentUser->is_admin)
                            <p class="text-muted mb-2"><small>Como administrador, puede cerrar cualquier caja</small></p>
                        @endif
                    @endif
                    <p class="mb-2"><small>Usuario actual: {{ auth()->user()->name }}</small></p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <small>Al confirmar el cierre de caja, su sesión se cerrará automáticamente. Use "Cancelar" si desea continuar trabajando.</small>
                    </div>
                </div>
                @if($userEmpresas->count() == 1)
                    {{-- Una sola empresa - formulario directo --}}
                    <form id="closeCashForm" action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="monto_cierre">Monto Final ({{ $userEmpresa->nombre }})</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg" 
                                       id="monto_cierre" name="monto" value="{{ number_format($sumCaja, 2, '.', '') }}" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Cierre">
                        <input type="hidden" name="empresa_id" value="{{ $userEmpresa->id }}">
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                            <a href="{{ route('cancel-closing-card') }}" class="btn btn-secondary btn-lg mb-2 mb-md-0 mr-md-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger btn-lg flex-grow-1" onclick="SucursalCache.limpiar()">
                                <i class="fas fa-door-closed mr-2"></i>
                                <span class="d-none d-md-inline">Confirmar Cierre y Salir</span>
                                <span class="d-md-none">Cerrar y Salir</span>
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Múltiples empresas - selector dinámico --}}
                    <form id="closeCashFormMulti" action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Seleccionar Sucursal:</label>
                            <div class="row">
                                @foreach($empresasCaja as $index => $empresaData)
                                    @if(!$empresaData['isClosed'])
                                        <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                            <button type="button" class="btn {{ $empresaData['isUltimaCajaAbierta'] ? 'btn-danger' : 'btn-outline-danger' }} btn-block empresa-close-btn h-100 d-flex flex-column justify-content-center align-items-start p-3" 
                                                    data-empresa-id="{{ $empresaData['empresa']->id }}"
                                                    data-monto="{{ number_format($empresaData['sumCaja'], 2, '.', '') }}"
                                                    style="min-height: 80px; border: 2px solid; cursor: pointer; transition: all 0.2s ease;">
                                                <div class="d-flex align-items-center w-100">
                                                    <i class="fas fa-cash-register mr-2 flex-shrink-0" style="font-size: 1.1em;"></i>
                                                    <div class="text-left flex-grow-1">
                                                        <div class="font-weight-bold empresa-nombre" style="font-size: 0.95em; line-height: 1.2;">{{ strtoupper($empresaData['empresa']->nombre) }}</div>
                                                        <small class="d-block" style="font-size: 0.8em;">Caja Abierta</small>
                                                        <small class="font-weight-bold d-block" style="font-size: 0.85em;">${{ number_format($empresaData['sumCaja'], 2, '.', ',') }}</small>
                                                        @if($empresaData['isUltimaCajaAbierta'])
                                                            <span class="badge badge-warning badge-sm mt-1" style="font-size: 0.7em;">ÚLTIMA ABIERTA</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <input type="hidden" id="empresa_id_close_hidden" name="empresa_id" required>
                        </div>

                        <div class="form-group">
                            <label for="monto_cierre_multi">Monto Final:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg" 
                                       id="monto_cierre_multi" name="monto" value="0" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Cierre">
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                            <a href="{{ route('cancel-closing-card') }}" class="btn btn-secondary btn-lg mb-2 mb-md-0 mr-md-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger btn-lg flex-grow-1" id="btn_cerrar" disabled onclick="SucursalCache.limpiar()">
                                <i class="fas fa-door-closed mr-2"></i>
                                <span class="d-none d-md-inline">Confirmar Cierre y Salir</span>
                                <span class="d-md-none">Cerrar y Salir</span>
                            </button>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Buscar si hay un botón de última caja abierta y seleccionarlo automáticamente
                            const ultimaCajaBtn = document.querySelector('.empresa-close-btn.btn-danger');
                            if (ultimaCajaBtn) {
                                ultimaCajaBtn.click(); // Simular click para seleccionar automáticamente
                            }
                        });

                        document.querySelectorAll('.empresa-close-btn').forEach(button => {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Remover selección anterior
                                document.querySelectorAll('.empresa-close-btn').forEach(btn => {
                                    if (btn.classList.contains('btn-danger')) {
                                        btn.classList.remove('btn-danger');
                                        btn.classList.add('btn-outline-danger');
                                        btn.style.borderColor = '#dc3545';
                                        btn.style.backgroundColor = 'transparent';
                                        btn.style.color = '#dc3545';
                                    }
                                });
                                
                                // Marcar como seleccionado con estilos más visibles
                                this.classList.remove('btn-outline-danger');
                                this.classList.add('btn-danger');
                                this.style.borderColor = '#c82333';
                                this.style.backgroundColor = '#dc3545';
                                this.style.color = 'white';
                                
                                // Actualizar campos
                                const empresaId = this.getAttribute('data-empresa-id');
                                const monto = this.getAttribute('data-monto');
                                
                                document.getElementById('empresa_id_close_hidden').value = empresaId;
                                document.getElementById('monto_cierre_multi').value = monto;
                                
                                // Habilitar botón
                                document.getElementById('btn_cerrar').disabled = false;
                            }, { passive: false });
                            
                            // Agregar eventos táctiles para dispositivos móviles
                            button.addEventListener('touchstart', function(e) {
                                this.style.transform = 'scale(0.98)';
                            }, { passive: true });
                            
                            button.addEventListener('touchend', function(e) {
                                this.style.transform = 'scale(1)';
                            }, { passive: true });
                        });
                    </script>
                @endif

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <script>
                    // Script para manejar el cierre de caja (tanto una empresa como múltiples)
                    @if($userEmpresas->count() == 1)
                        const formId = 'closeCashForm';
                        const empresaId = {{ $userEmpresa->id ?? 'null' }};
                    @else
                        const formId = 'closeCashFormMulti';
                        let empresaId = null;
                    @endif

                    document.getElementById(formId).addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        @if($userEmpresas->count() > 1)
                            empresaId = document.getElementById('empresa_id_close_hidden').value;
                            if (!empresaId) {
                                alert('Por favor seleccione una sucursal');
                                return;
                            }
                        @endif
                        
                        // Mostrar indicador de carga
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';

                        // Obtener el token CSRF
                        const token = document.querySelector('input[name="_token"]').value;
                        
                        @if($userEmpresas->count() == 1)
                            const monto = document.getElementById('monto_cierre').value;
                        @else
                            const monto = document.getElementById('monto_cierre_multi').value;
                        @endif

                        // Enviar formulario con fetch
                        fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                monto: parseFloat(monto),
                                estado: 'Cierre',
                                empresa_id: parseInt(empresaId),
                                _token: token
                            })
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            if (!response.ok) {
                                return response.json().then(errorData => {
                                    throw new Error(errorData.message || 'Error en la respuesta del servidor');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            if (data.success) {
                                // Mostrar éxito temporalmente
                                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Cerrando sesión...';
                                
                                // Mostrar mensaje de éxito
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-success mt-3';
                                alertDiv.innerHTML = '<i class="fas fa-check mr-2"></i>Caja cerrada exitosamente. Cerrando sesión automáticamente...';
                                submitBtn.parentNode.parentNode.appendChild(alertDiv);
                                
                                // Cerrar sesión automáticamente después de 2 segundos
                                setTimeout(() => {
                                    // Crear y enviar formulario de logout
                                    const logoutForm = document.createElement('form');
                                    logoutForm.method = 'POST';
                                    logoutForm.action = '{{ route("logout") }}';
                                    logoutForm.style.display = 'none';
                                    
                                    const csrfInput = document.createElement('input');
                                    csrfInput.type = 'hidden';
                                    csrfInput.name = '_token';
                                    csrfInput.value = token;
                                    
                                    logoutForm.appendChild(csrfInput);
                                    document.body.appendChild(logoutForm);
                                    logoutForm.submit();
                                }, 2000);
                            } else {
                                throw new Error(data.message || 'Error desconocido');
                            }
                        })
                        .catch(error => {
                            console.error('Error detail:', error);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            
                            // Mostrar error detallado
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-danger mt-3';
                            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Error: ' + error.message;
                            
                            // Remover alertas anteriores
                            const existingAlerts = submitBtn.parentNode.parentNode.querySelectorAll('.alert');
                            existingAlerts.forEach(alert => alert.remove());
                            
                            submitBtn.parentNode.parentNode.appendChild(errorDiv);
                            
                            // Auto-remover el error después de 5 segundos
                            setTimeout(() => {
                                errorDiv.remove();
                            }, 5000);
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Content Wrapper --}}
<div class="content-wrapper {{ config('adminlte.classes_content_wrapper', '') }}" 
     @if(($currentUser && $isClosed === true && $userEmpresas->count() > 0) || 
         ($showClosingCard && $currentUser && $userEmpresas->count() > 0)) style="filter: blur(5px);" @endif
     id="contentWrapper">
    {{-- Content Header --}}
    @hasSection('content_header')
        <div class="content-header">
            <div class="{{ config('adminlte.classes_content_header') ?: config('adminlte.classes_content', 'container-fluid') }}">
                @yield('content_header')
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="content">
        <div class="{{ config('adminlte.classes_content') ?: 'container-fluid' }}">
            @yield('content')
        </div>
    </div>
</div>

{{-- Estilos CSS adicionales para responsividad móvil --}}
<style>
    /* Mejoras de responsividad para dispositivos móviles */
    @media (max-width: 768px) {
        .position-fixed .card {
            max-height: 90vh !important;
            overflow-y: auto;
        }
        
        .card-body {
            max-height: 90vh !important;
            overflow-y: auto !important;
        }
        
        .form-control-lg {
            font-size: 16px !important; /* Evita zoom en iOS */
        }
        
        .btn-lg {
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .alert {
            font-size: 13px;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .position-fixed > div {
            padding: 0.5rem !important;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        .btn-lg {
            padding: 10px 12px;
            font-size: 13px;
        }
        
        .input-group-text {
            padding: 8px 12px;
        }
    }
    
    /* Botones empresas con mejor interacción */
    .empresa-btn,
    .empresa-close-btn {
        border-width: 2px !important;
        font-weight: 500;
        position: relative;
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
    
    .empresa-btn:hover,
    .empresa-close-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
        z-index: 2;
    }
    
    .empresa-btn:active,
    .empresa-close-btn:active {
        transform: translateY(0px) scale(0.98);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.1s ease;
    }
    
    /* Estados específicos para botones seleccionados */
    .empresa-btn.btn-primary {
        background-color: #007bff !important;
        border-color: #0056b3 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    
    .empresa-close-btn.btn-danger {
        background-color: #dc3545 !important;
        border-color: #c82333 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    /* Mejoras para nombres de empresa */
    .empresa-nombre {
        word-break: break-word;
        hyphens: auto;
        line-height: 1.3;
    }
    
    /* Mejora para alertas en móvil */
    .alert-dismissible .close {
        padding: 8px 12px;
        font-size: 18px;
        line-height: 1;
        color: inherit;
        opacity: 0.8;
    }
    
    /* Mejoras para badges */
    .badge-sm {
        font-size: 0.7em !important;
        padding: 0.25em 0.4em;
    }
    
    /* Espaciado mejorado en row */
    .row.empresa-selector {
        margin-left: -8px;
        margin-right: -8px;
    }
    
    .row.empresa-selector > [class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
    }
    
    /* Animaciones suaves para transiciones */
    .empresa-btn,
    .empresa-close-btn {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Mejoras para dispositivos táctiles */
    @media (pointer: coarse) {
        .empresa-btn,
        .empresa-close-btn {
            min-height: 88px; /* Tamaño mínimo recomendado para táctil */
        }
        
        .btn-lg {
            min-height: 48px; /* Altura mínima para botones táctiles */
        }
    }
    
    /* Scrollbar personalizado para contenido del modal */
    .card-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Altura máxima fija para todos los modales */
    .modal-container {
        max-height: 90vh !important;
    }
    
    .modal-container .card {
        max-height: 90vh !important;
    }
    
    .modal-container .card-body {
        max-height: calc(90vh - 60px) !important;
        overflow-y: auto !important;
    }
    
    /* Fix para iOS Safari zoom prevention */
    select,
    input[type="number"],
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        font-size: 16px !important;
    }
</style>

<script>
    // Función para guardar la sucursal en localStorage cuando se abre caja (múltiples empresas)
    function guardarSucursalCacheMulti() {
        const empresaSelect = document.getElementById('empresa_id_hidden');
        const actionType = document.getElementById('action_type');
        
        if (empresaSelect && empresaSelect.value) {
            const empresaId = empresaSelect.value;
            
            if (actionType && actionType.value === 'apertura') {
                // Para apertura, buscar en botones de apertura
                const empresaBtn = document.querySelector(`.empresa-btn[data-empresa-id="${empresaId}"]`);
                if (empresaBtn) {
                    const empresaNombre = empresaBtn.querySelector('.empresa-nombre').textContent.trim();
                    SucursalCache.guardar(empresaId, empresaNombre);
                }
            } else if (actionType && actionType.value === 'seleccionar') {
                // Para selección, buscar en botones de selección
                const empresaBtn = document.querySelector(`.empresa-select-btn[data-empresa-id="${empresaId}"]`);
                if (empresaBtn) {
                    const empresaNombre = empresaBtn.getAttribute('data-empresa-nombre');
                    SucursalCache.guardar(empresaId, empresaNombre);
                }
            } else {
                // Fallback: buscar cualquier botón con el ID
                const empresaBtn = document.querySelector(`.empresa-btn[data-empresa-id="${empresaId}"], .empresa-select-btn[data-empresa-id="${empresaId}"]`);
                if (empresaBtn) {
                    let empresaNombre;
                    if (empresaBtn.classList.contains('empresa-btn')) {
                        empresaNombre = empresaBtn.querySelector('.empresa-nombre').textContent.trim();
                    } else {
                        empresaNombre = empresaBtn.getAttribute('data-empresa-nombre');
                    }
                    SucursalCache.guardar(empresaId, empresaNombre);
                }
            }
        }
    }
</script>

