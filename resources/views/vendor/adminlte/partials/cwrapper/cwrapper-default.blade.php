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
    $hayCajasPendientesCierre = false;
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
                if ($lastHistory && $lastHistory->estado === 'Apertura' && 
                    $lastHistory->created_at->format('Y-m-d') < $today) {
                    // La última acción fue apertura y fue en un día anterior
                    $cajaAbiertaAnterior = $lastHistory;
                }
                
                $needsClosure = $cajaAbiertaAnterior !== null;
                
                if ($needsClosure) {
                    $hayCajasPendientesCierre = true;
                }
                
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
            // 1. Si hay cajas pendientes de cierre del día anterior, mostrar cierre forzoso
            // 2. Si hay cajas abiertas actualmente, no mostrar apertura
            // 3. Si NO hay cajas abiertas, mostrar apertura (sin importar historial del día)
            
            if ($hayCajasPendientesCierre) {
                $isClosed = false; // No mostrar apertura, mostrar cierre pendiente
            } else {
                // Verificar si hay alguna caja ACTUALMENTE abierta (estado = 'Apertura')
                $hayAperturasActivas = collect($empresasCaja)->contains(function ($empresaData) {
                    return $empresaData['lastHistory'] && $empresaData['lastHistory']->estado === 'Apertura';
                });
                
                if ($hayAperturasActivas) {
                    $isClosed = false; // Hay cajas abiertas, no mostrar apertura
                } else {
                    $isClosed = true; // No hay cajas abiertas, mostrar apertura
                }
            }
            
            // Si solo hay una empresa, usar la lógica anterior
            if ($userEmpresas->count() == 1) {
                $userEmpresa = $userEmpresas->first();
                $lastCashHistory = $empresasCaja[0]['lastHistory'];
                $previousCashHistory = $empresasCaja[0]['previousHistory'];
                $sumCaja = $empresasCaja[0]['sumCaja'];
                
                // Para una empresa, ajustar la lógica de $isClosed
                if ($empresasCaja[0]['needsClosure']) {
                    $isClosed = false; // Mostrar cierre pendiente
                } else {
                    // Verificar si la caja está actualmente abierta
                    $cajaActualmenteAbierta = $lastCashHistory && $lastCashHistory->estado === 'Apertura';
                    $isClosed = !$cajaActualmenteAbierta; // Mostrar apertura solo si la caja NO está abierta
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

{{-- Tarjeta de Cierre Pendiente (cajas del día anterior sin cerrar) --}}
@if($currentUser && $hayCajasPendientesCierre && $userEmpresas->count() > 0)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center p-3" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white w-100" style="max-width: 700px;">
        <div class="text-center mb-4">
            <h1 class="d-none d-md-block"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i></h1>
            <h3 class="d-md-none"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i></h3>
            <h3 class="text-danger d-none d-md-block">CAJAS PENDIENTES DE CIERRE</h3>
            <h5 class="text-danger d-md-none">CAJAS PENDIENTES DE CIERRE</h5>
            <h4 class="text-warning d-none d-md-block">Debe cerrar las cajas del día anterior</h4>
            <h6 class="text-warning d-md-none">Debe cerrar las cajas del día anterior</h6>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle mr-2"></i>
                <small class="d-md-none">Se detectaron cajas abiertas de días anteriores que no fueron cerradas. 
                Debe cerrarlas antes de poder trabajar normalmente.</small>
                <span class="d-none d-md-inline">Se detectaron cajas abiertas de días anteriores que no fueron cerradas. 
                Debe cerrarlas antes de poder trabajar normalmente.</span>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body bg-light">
                @if($userEmpresas->count() == 1 && $empresasCaja[0]['needsClosure'])
                    {{-- Una sola empresa con caja pendiente --}}
                    <div class="alert alert-info mb-3">
                        <h5><strong>Caja pendiente de cierre:</strong></h5>
                        <p class="mb-1">Empresa: {{ $userEmpresa->nombre }}</p>
                        <p class="mb-1">Fecha de apertura: {{ $empresasCaja[0]['cajaAbiertaAnterior']->created_at->format('d/m/Y H:i') }}</p>
                        <p class="mb-0">Usuario que abrió: {{ $empresasCaja[0]['cajaAbiertaAnterior']->user->name }}</p>
                    </div>

                    <form action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="monto_cierre_pendiente">Monto de Cierre ({{ $userEmpresa->nombre }})</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg" 
                                       name="monto" id="monto_cierre_pendiente" value="{{ number_format($sumCaja, 2, '.', '') }}" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Cierre">
                        <input type="hidden" name="empresa_id" value="{{ $userEmpresa->id }}">
                        <input type="hidden" name="cierre_pendiente" value="1">
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between mt-4">
                            <button type="submit" class="btn btn-warning btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1">
                                <i class="fas fa-door-closed mr-2"></i>Cerrar Caja Pendiente
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form-pendiente').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <span class="d-none d-md-inline">Salir</span>
                            </a>
                        </div>
                    </form>
                @else
                    {{-- Múltiples empresas con cajas pendientes --}}
                    <form action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="empresa_select_pendiente">Seleccionar Sucursal con Caja Pendiente:</label>
                            <select id="empresa_select_pendiente" name="empresa_id" class="form-control form-control-lg" required>
                                <option value="">-- Seleccione una sucursal --</option>
                                @foreach($empresasCaja as $empresaData)
                                    @if($empresaData['needsClosure'])
                                        <option value="{{ $empresaData['empresa']->id }}" 
                                                data-monto="{{ intval($empresaData['sumCaja']) }}"
                                                data-fecha-apertura="{{ $empresaData['cajaAbiertaAnterior']->created_at->format('d/m/Y H:i') }}"
                                                data-usuario-apertura="{{ $empresaData['cajaAbiertaAnterior']->user->name }}">
                                            {{ strtoupper($empresaData['empresa']->nombre) }} 
                                            (Pendiente desde {{ $empresaData['cajaAbiertaAnterior']->created_at->format('d/m/Y') }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div id="info_caja_pendiente" class="alert alert-info" style="display: none;">
                            <h6><strong>Información de la caja pendiente:</strong></h6>
                            <p class="mb-1">Fecha de apertura: <span id="info_fecha_apertura">N/A</span></p>
                            <p class="mb-0">Usuario que abrió: <span id="info_usuario_apertura">N/A</span></p>
                        </div>

                        <div class="form-group">
                            <label for="monto_pendiente_multi">Monto de Cierre:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg" 
                                       name="monto" id="monto_pendiente_multi" value="0" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Cierre">
                        <input type="hidden" name="cierre_pendiente" value="1">
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-warning btn-lg flex-grow-1 mr-2" id="btn_cerrar_pendiente" disabled>
                                <i class="fas fa-door-closed mr-2"></i>Cerrar Caja Pendiente
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form-pendiente').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </form>

                    <script>
                        document.getElementById('empresa_select_pendiente').addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const montoInput = document.getElementById('monto_pendiente_multi');
                            const btnCerrar = document.getElementById('btn_cerrar_pendiente');
                            const infoCaja = document.getElementById('info_caja_pendiente');
                            
                            if (selectedOption.value) {
                                // Actualizar monto
                                montoInput.value = selectedOption.getAttribute('data-monto') || 0;
                                
                                // Mostrar información de la caja pendiente
                                document.getElementById('info_fecha_apertura').textContent = selectedOption.getAttribute('data-fecha-apertura');
                                document.getElementById('info_usuario_apertura').textContent = selectedOption.getAttribute('data-usuario-apertura');
                                infoCaja.style.display = 'block';
                                
                                // Habilitar botón
                                btnCerrar.disabled = false;
                            } else {
                                montoInput.value = 0;
                                infoCaja.style.display = 'none';
                                btnCerrar.disabled = true;
                            }
                        });
                    </script>
                @endif

                <form id="logout-form-pendiente" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Tarjeta de Apertura de Caja (para todos los usuarios con empresas asignadas) --}}
@if($currentUser && $isClosed && $userEmpresas->count() > 0)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center p-3" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white w-100" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h1 class="d-none d-md-block"><i class="fas fa-cash-register fa-3x mb-3"></i></h1>
            <h3 class="d-md-none"><i class="fas fa-cash-register fa-2x mb-2"></i></h3>
            <h3 class="d-none d-md-block">Apertura de Caja</h3>
            <h5 class="d-md-none">Apertura de Caja</h5>
            @if($userEmpresas->count() == 1)
                <h4 class="text-warning d-none d-md-block">{{ strtoupper($userEmpresa->nombre) }}</h4>
                <h6 class="text-warning d-md-none">{{ strtoupper($userEmpresa->nombre) }}</h6>
            @else
                <h4 class="text-info d-none d-md-block">SELECCIONE SUCURSAL</h4>
                <h6 class="text-info d-md-none">SELECCIONE SUCURSAL</h6>
                @if($currentUser->is_admin)
                    <p class="text-muted"><small>Como administrador, puede abrir cualquier caja</small></p>
                @endif
            @endif
        </div>

        <div class="card shadow">
            <div class="card-body bg-light">
                @if($userEmpresas->count() == 1)
                    {{-- Una sola empresa - formulario directo --}}
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
                            <button type="submit" class="btn btn-success btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1">
                                <i class="fas fa-door-open mr-2"></i>Abrir Caja
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <span class="d-none d-md-inline">Salir</span>
                            </a>
                        </div>
                    </form>
                @else
                    {{-- Múltiples empresas - selector dinámico --}}
                    <form id="multiEmpresaForm" action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Seleccionar Sucursal:</label>
                            <div class="row">
                                @foreach($empresasCaja as $index => $empresaData)
                                    @if($empresaData['isClosed'])
                                        <div class="col-12 col-md-6 mb-2">
                                            <button type="button" class="btn btn-outline-primary btn-block empresa-btn py-3" 
                                                    data-empresa-id="{{ $empresaData['empresa']->id }}"
                                                    data-monto="{{ number_format($empresaData['sumCaja'], 2, '.', '') }}"
                                                    data-last-close="{{ $empresaData['previousHistory'] ? $empresaData['previousHistory']->created_at->format('d/m/Y H:i') : 'Sin cierres anteriores' }}"
                                                    data-last-user="{{ $empresaData['previousHistory'] ? $empresaData['previousHistory']->user->name : 'N/A' }}"
                                                    data-last-amount="{{ $empresaData['previousHistory'] ? number_format($empresaData['previousHistory']->monto, 2) : '0.00' }}">
                                                <i class="fas fa-building mr-2"></i>
                                                <div class="text-left">
                                                    <div class="font-weight-bold">{{ strtoupper($empresaData['empresa']->nombre) }}</div>
                                                    <small class="text-muted">Caja Cerrada - ${{ number_format($empresaData['sumCaja'], 2, '.', ',') }}</small>
                                                </div>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <input type="hidden" id="empresa_id_hidden" name="empresa_id" required>
                        </div>

                        <div id="empresa_info" class="alert alert-info" style="display: none;">
                            <p class="mb-1"><strong>Último Cierre:</strong></p>
                            <p class="mb-1">Usuario: <span id="info_user">N/A</span></p>
                            <p class="mb-1">Fecha: <span id="info_date">N/A</span></p>
                            <p class="mb-0">Monto: $<span id="info_amount">0.00</span></p>
                        </div>

                        <div class="form-group">
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
                            <button type="submit" class="btn btn-success btn-lg mb-2 mb-md-0 mr-md-2 flex-grow-1" id="btn_abrir" disabled>
                                <i class="fas fa-door-open mr-2"></i>Abrir Caja
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <span class="d-none d-md-inline">Salir</span>
                            </a>
                        </div>
                    </form>

                    <script>
                        document.querySelectorAll('.empresa-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                // Remover selección anterior
                                document.querySelectorAll('.empresa-btn').forEach(btn => {
                                    btn.classList.remove('btn-primary');
                                    btn.classList.add('btn-outline-primary');
                                });
                                
                                // Marcar como seleccionado
                                this.classList.remove('btn-outline-primary');
                                this.classList.add('btn-primary');
                                
                                // Actualizar campos
                                const empresaId = this.getAttribute('data-empresa-id');
                                const monto = this.getAttribute('data-monto');
                                const lastUser = this.getAttribute('data-last-user');
                                const lastClose = this.getAttribute('data-last-close');
                                const lastAmount = this.getAttribute('data-last-amount');
                                
                                document.getElementById('empresa_id_hidden').value = empresaId;
                                document.getElementById('monto_multi').value = monto;
                                
                                // Mostrar información del último cierre
                                document.getElementById('info_user').textContent = lastUser;
                                document.getElementById('info_date').textContent = lastClose;
                                document.getElementById('info_amount').textContent = lastAmount;
                                document.getElementById('empresa_info').style.display = 'block';
                                
                                // Habilitar botón
                                document.getElementById('btn_abrir').disabled = false;
                            });
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

{{-- Tarjeta de Cierre de Caja (para todos los usuarios con empresas asignadas) --}}
@if($showClosingCard && $currentUser && $userEmpresas->count() > 0)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center p-3" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white w-100" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h1 class="d-none d-md-block"><i class="fas fa-cash-register fa-3x mb-3 text-danger"></i></h1>
            <h3 class="d-md-none"><i class="fas fa-cash-register fa-2x mb-2 text-danger"></i></h3>
            <h3 class="d-none d-md-block">Cierre de Caja</h3>
            <h5 class="d-md-none">Cierre de Caja</h5>
            @if($userEmpresas->count() == 1)
                <h4 class="text-warning d-none d-md-block">{{ strtoupper($userEmpresa->nombre) }}</h4>
                <h6 class="text-warning d-md-none">{{ strtoupper($userEmpresa->nombre) }}</h6>
            @else
                <h4 class="text-info d-none d-md-block">SELECCIONE SUCURSAL</h4>
                <h6 class="text-info d-md-none">SELECCIONE SUCURSAL</h6>
                @if($currentUser->is_admin)
                    <p class="text-muted"><small>Como administrador, puede cerrar cualquier caja</small></p>
                @endif
            @endif
            <p class="mb-2"><small>Usuario actual: {{ auth()->user()->name }}</small></p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <small class="d-md-none">Al confirmar el cierre de caja, su sesión se cerrará automáticamente. Use "Cancelar" si desea continuar trabajando.</small>
                <span class="d-none d-md-inline">Al confirmar el cierre de caja, su sesión se cerrará automáticamente. Use "Cancelar" si desea continuar trabajando.</span>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body bg-light">
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
                            <button type="submit" class="btn btn-danger btn-lg flex-grow-1">
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
                                        <div class="col-12 col-md-6 mb-2">
                                            <button type="button" class="btn {{ $empresaData['isUltimaCajaAbierta'] ? 'btn-danger' : 'btn-outline-danger' }} btn-block empresa-close-btn py-3" 
                                                    data-empresa-id="{{ $empresaData['empresa']->id }}"
                                                    data-monto="{{ number_format($empresaData['sumCaja'], 2, '.', '') }}">
                                                <i class="fas fa-cash-register mr-2"></i>
                                                <div class="text-left">
                                                    <div class="font-weight-bold">{{ strtoupper($empresaData['empresa']->nombre) }}</div>
                                                    <small class="text-muted">
                                                        Caja Abierta - ${{ number_format($empresaData['sumCaja'], 2, '.', ',') }}
                                                        @if($empresaData['isUltimaCajaAbierta'])
                                                            <br><span class="badge badge-warning">ÚLTIMA ABIERTA</span>
                                                        @endif
                                                    </small>
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
                            <button type="submit" class="btn btn-danger btn-lg flex-grow-1" id="btn_cerrar" disabled>
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
                            button.addEventListener('click', function() {
                                // Remover selección anterior
                                document.querySelectorAll('.empresa-close-btn').forEach(btn => {
                                    if (btn.classList.contains('btn-danger')) {
                                        btn.classList.remove('btn-danger');
                                        btn.classList.add('btn-outline-danger');
                                    }
                                });
                                
                                // Marcar como seleccionado
                                this.classList.remove('btn-outline-danger');
                                this.classList.add('btn-danger');
                                
                                // Actualizar campos
                                const empresaId = this.getAttribute('data-empresa-id');
                                const monto = this.getAttribute('data-monto');
                                
                                document.getElementById('empresa_id_close_hidden').value = empresaId;
                                document.getElementById('monto_cierre_multi').value = monto;
                                
                                // Habilitar botón
                                document.getElementById('btn_cerrar').disabled = false;
                            });
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
     @if(($currentUser && $isClosed && $userEmpresas->count() > 0) || 
         ($showClosingCard && $currentUser && $userEmpresas->count() > 0) ||
         ($hayCajasPendientesCierre && $currentUser && $userEmpresas->count() > 0)) style="filter: blur(5px);" @endif>
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
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .empresa-btn, .empresa-close-btn {
            font-size: 14px !important;
            padding: 12px 8px !important;
        }
        
        .empresa-btn .text-left div,
        .empresa-close-btn .text-left div {
            font-size: 13px;
        }
        
        .empresa-btn small,
        .empresa-close-btn small {
            font-size: 11px;
        }
        
        .form-control-lg {
            font-size: 16px; /* Evita zoom en iOS */
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
    
    /* Botones empresas con mejor espaciado en móvil */
    .empresa-btn:hover,
    .empresa-close-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        transition: all 0.2s ease;
    }
    
    /* Mejora para alertas en móvil */
    .alert-dismissible .close {
        padding: 8px 12px;
    }
</style>

