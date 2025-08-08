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
        
        if ($userEmpresas->count() > 0) {
            // Preparar información de caja para cada empresa
            foreach ($userEmpresas as $empresa) {
                $lastHistory = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                                     ->latest()
                                                     ->first();
                
                $previousHistory = \App\Models\CashHistory::where('empresa_id', $empresa->id)
                                                         ->where('estado', 'Cierre')
                                                         ->latest()
                                                         ->first();
                
                $empresasCaja[] = [
                    'empresa' => $empresa,
                    'lastHistory' => $lastHistory,
                    'previousHistory' => $previousHistory,
                    'isClosed' => !$lastHistory || $lastHistory->estado !== 'Apertura',
                    'sumCaja' => \App\Models\Caja::where('empresa_id', $empresa->id)->sum('valor')
                ];
            }
            
            // Verificar si hay alguna caja que necesite apertura
            $isClosed = collect($empresasCaja)->contains('isClosed', true);
            
            // Si solo hay una empresa, usar la lógica anterior
            if ($userEmpresas->count() == 1) {
                $userEmpresa = $userEmpresas->first();
                $lastCashHistory = $empresasCaja[0]['lastHistory'];
                $previousCashHistory = $empresasCaja[0]['previousHistory'];
                $isClosed = $empresasCaja[0]['isClosed'];
                $sumCaja = $empresasCaja[0]['sumCaja'];
            }
        }
    }
    
    $showClosingCard = session('showClosingCard', false);
@endphp

{{-- Notificación de apertura automática (solo para usuarios no administradores) --}}
@if($aperturaAutomatica && !$currentUser->is_admin)
<div class="alert alert-success alert-dismissible position-fixed" 
     style="top: 20px; right: 20px; z-index: 9998; min-width: 300px;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-check"></i> Apertura Automática Exitosa</h5>
    @if($cajasAbiertas == 1)
        Se ha abierto automáticamente 1 caja al iniciar sesión.
    @else
        Se han abierto automáticamente {{ $cajasAbiertas }} cajas al iniciar sesión.
    @endif
</div>

<script>
    // Auto-dismiss notification after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
</script>
@endif

{{-- Tarjeta de Apertura de Caja (para todos los usuarios con empresas asignadas) --}}
@if($currentUser && $isClosed && $userEmpresas->count() > 0)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cash-register fa-3x mb-3"></i></h1>
            <h2>Apertura de Caja</h2>
            @if($userEmpresas->count() == 1)
                <h3 class="text-warning">{{ strtoupper($userEmpresa->nombre) }}</h3>
            @else
                <h3 class="text-info">SELECCIONE SUCURSAL</h3>
                @if($currentUser->is_admin)
                    <p class="text-muted">Como administrador, puede abrir cualquier caja</p>
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
                                <input type="number" step="1" min="0" class="form-control form-control-lg" 
                                       name="monto" id="monto" value="{{ intval($sumCaja) }}" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Apertura">
                        <input type="hidden" name="empresa_id" value="{{ $userEmpresa->id }}">
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1 mr-2">
                                <i class="fas fa-door-open mr-2"></i>Abrir Caja
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </form>
                @else
                    {{-- Múltiples empresas - selector dinámico --}}
                    <form id="multiEmpresaForm" action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="empresa_select">Seleccionar Sucursal:</label>
                            <select id="empresa_select" name="empresa_id" class="form-control form-control-lg" required>
                                <option value="">-- Seleccione una sucursal --</option>
                                @foreach($empresasCaja as $index => $empresaData)
                                    @if($empresaData['isClosed'])
                                        <option value="{{ $empresaData['empresa']->id }}" 
                                                data-monto="{{ intval($empresaData['sumCaja']) }}"
                                                data-last-close="{{ $empresaData['previousHistory'] ? $empresaData['previousHistory']->created_at->format('d/m/Y H:i') : 'Sin cierres anteriores' }}"
                                                data-last-user="{{ $empresaData['previousHistory'] ? $empresaData['previousHistory']->user->name : 'N/A' }}"
                                                data-last-amount="{{ $empresaData['previousHistory'] ? number_format($empresaData['previousHistory']->monto, 2) : '0.00' }}">
                                            {{ strtoupper($empresaData['empresa']->nombre) }} 
                                            (Caja Cerrada - ${{ number_format($empresaData['sumCaja'], 0, ',', '.') }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
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
                                <input type="number" step="1" min="0" class="form-control form-control-lg" 
                                       name="monto" id="monto_multi" value="0" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Apertura">
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1 mr-2" id="btn_abrir" disabled>
                                <i class="fas fa-door-open mr-2"></i>Abrir Caja
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </form>

                    <script>
                        document.getElementById('empresa_select').addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const montoInput = document.getElementById('monto_multi');
                            const btnAbrir = document.getElementById('btn_abrir');
                            const empresaInfo = document.getElementById('empresa_info');
                            
                            if (selectedOption.value) {
                                // Actualizar monto
                                montoInput.value = selectedOption.getAttribute('data-monto') || 0;
                                
                                // Mostrar información del último cierre
                                document.getElementById('info_user').textContent = selectedOption.getAttribute('data-last-user');
                                document.getElementById('info_date').textContent = selectedOption.getAttribute('data-last-close');
                                document.getElementById('info_amount').textContent = selectedOption.getAttribute('data-last-amount');
                                empresaInfo.style.display = 'block';
                                
                                // Habilitar botón
                                btnAbrir.disabled = false;
                            } else {
                                montoInput.value = 0;
                                empresaInfo.style.display = 'none';
                                btnAbrir.disabled = true;
                            }
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
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cash-register fa-3x mb-3 text-danger"></i></h1>
            <h2>Cierre de Caja</h2>
            @if($userEmpresas->count() == 1)
                <h3 class="text-warning">{{ strtoupper($userEmpresa->nombre) }}</h3>
            @else
                <h3 class="text-info">SELECCIONE SUCURSAL</h3>
                @if($currentUser->is_admin)
                    <p class="text-muted">Como administrador, puede cerrar cualquier caja</p>
                @endif
            @endif
            <p>Usuario actual: {{ auth()->user()->name }}</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Puede cerrar múltiples cajas y luego cerrar sesión, o cancelar para continuar trabajando.
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
                                <input type="number" step="1" min="0" class="form-control form-control-lg" 
                                       id="monto_cierre" name="monto" value="{{ intval($sumCaja) }}" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Cierre">
                        <input type="hidden" name="empresa_id" value="{{ $userEmpresa->id }}">
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('cancel-closing-card') }}" class="btn btn-secondary btn-lg mr-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger btn-lg mr-2">
                                <i class="fas fa-door-closed mr-2"></i>Confirmar Cierre
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-warning btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </form>
                @else
                    {{-- Múltiples empresas - selector dinámico --}}
                    <form id="closeCashFormMulti" action="{{ route('cash-histories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="empresa_select_close">Seleccionar Sucursal:</label>
                            <select id="empresa_select_close" name="empresa_id" class="form-control form-control-lg" required>
                                <option value="">-- Seleccione una sucursal --</option>
                                @foreach($empresasCaja as $index => $empresaData)
                                    @if(!$empresaData['isClosed'])
                                        <option value="{{ $empresaData['empresa']->id }}" 
                                                data-monto="{{ intval($empresaData['sumCaja']) }}">
                                            {{ strtoupper($empresaData['empresa']->nombre) }} 
                                            (Caja Abierta - ${{ number_format($empresaData['sumCaja'], 0, ',', '.') }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="monto_cierre_multi">Monto Final:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="1" min="0" class="form-control form-control-lg" 
                                       id="monto_cierre_multi" name="monto" value="0" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="Cierre">
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('cancel-closing-card') }}" class="btn btn-secondary btn-lg mr-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger btn-lg mr-2" id="btn_cerrar" disabled>
                                <i class="fas fa-door-closed mr-2"></i>Confirmar Cierre
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-warning btn-lg" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </form>

                    <script>
                        document.getElementById('empresa_select_close').addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const montoInput = document.getElementById('monto_cierre_multi');
                            const btnCerrar = document.getElementById('btn_cerrar');
                            
                            if (selectedOption.value) {
                                montoInput.value = selectedOption.getAttribute('data-monto') || 0;
                                btnCerrar.disabled = false;
                            } else {
                                montoInput.value = 0;
                                btnCerrar.disabled = true;
                            }
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
                            empresaId = document.getElementById('empresa_select_close').value;
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
                                // Mostrar éxito
                                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Caja Cerrada';
                                
                                // Mostrar mensaje de éxito
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-success mt-3';
                                alertDiv.innerHTML = '<i class="fas fa-check mr-2"></i>Caja cerrada exitosamente. Puede cerrar otra caja o cerrar sesión.';
                                submitBtn.parentNode.parentNode.appendChild(alertDiv);
                                
                                // Recargar la página después de 3 segundos para actualizar el estado
                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
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
         ($showClosingCard && $currentUser && $userEmpresas->count() > 0)) style="filter: blur(5px);" @endif>
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

