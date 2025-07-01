@php
    $currentUser = Auth::user();
    $lastCashHistory = null;
    $previousCashHistory = null;
    $isClosed = true;
    
    if ($currentUser) {
        // Obtener el último registro de caja para la empresa del usuario
        if ($currentUser->empresa_id) {
            $lastCashHistory = \App\Models\CashHistory::where('empresa_id', $currentUser->empresa_id)
                                                     ->latest()
                                                     ->first();
            
            $previousCashHistory = \App\Models\CashHistory::where('empresa_id', $currentUser->empresa_id)
                                                         ->where('estado', 'Cierre')
                                                         ->latest()
                                                         ->first();
            
            // Verificar si la caja está cerrada para esta empresa
            $isClosed = !$lastCashHistory || $lastCashHistory->estado !== 'Apertura';
        }
    }
    
    $showClosingCard = session('showClosingCard', false);
    
    // Updated: Get sum from Caja model
    $sumCaja = \App\Models\Caja::sum('valor');
@endphp

{{-- Tarjeta de Apertura de Caja (solo para usuarios no administradores) --}}
@if($currentUser && !$currentUser->is_admin && $isClosed && $currentUser->empresa_id)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white" style="max-width: 500px;">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cash-register fa-3x mb-3"></i></h1>
            <h2>Apertura de Caja</h2>
            
            @if($previousCashHistory)
                <div class="alert alert-info">
                    <p class="mb-1"><strong>Último Cierre:</strong></p>
                    <p class="mb-1">Usuario: {{ $previousCashHistory->user->name }}</p>
                    <p class="mb-1">Fecha: {{ $previousCashHistory->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-0">Monto: ${{ number_format($previousCashHistory->monto, 2) }}</p>
                </div>
            @endif
        </div>

        <div class="card shadow">
            <div class="card-body bg-light">
                <form action="{{ route('cash-histories.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="monto">Monto Inicial</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control form-control-lg" 
                                   name="monto" id="monto" value="{{ $sumCaja }}" readonly>
                        </div>
                    </div>
                    <input type="hidden" name="estado" value="Apertura">
                    <input type="hidden" name="empresa_id" value="{{ $currentUser->empresa_id }}">
                    
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
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Tarjeta de Cierre de Caja --}}
@if($showClosingCard && $currentUser && $currentUser->empresa_id && !$isClosed)
<div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background-color: rgba(0,0,0,0.9) !important; z-index: 9999; top: 0; left: 0;">
    <div class="text-white" style="max-width: 500px;">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cash-register fa-3x mb-3 text-danger"></i></h1>
            <h2>Cierre de Caja</h2>
            <p>Usuario actual: {{ auth()->user()->name }}</p>
        </div>

        <div class="card shadow">
            <div class="card-body bg-light">
                <form id="closeCashForm" action="{{ route('cash-histories.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="monto_cierre">Monto Final</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="0.01" class="form-control form-control-lg" 
                                   id="monto_cierre" name="monto" value="{{ $sumCaja }}" readonly>
                        </div>
                    </div>
                    <input type="hidden" name="estado" value="Cierre">
                    <input type="hidden" name="empresa_id" value="{{ $currentUser->empresa_id }}">
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('cancel-closing-card') }}" class="btn btn-secondary btn-lg flex-grow-1 mr-2">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger btn-lg flex-grow-1">
                            <i class="fas fa-door-closed mr-2"></i>Confirmar Cierre
                        </button>
                    </div>
                </form>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <script>
                    document.getElementById('closeCashForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Mostrar indicador de carga
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';

                        // Obtener el token CSRF
                        const token = document.querySelector('input[name="_token"]').value;

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
                                monto: document.getElementById('monto_cierre').value,
                                estado: 'Cierre',
                                empresa_id: {{ $currentUser->empresa_id ?? 'null' }},
                                _token: token
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Primero cerrar caja
                                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Caja Cerrada';
                                
                                // Esperar 1 segundo y luego cerrar sesión
                                setTimeout(() => {
                                    submitBtn.innerHTML = '<i class="fas fa-sign-out-alt mr-2"></i>Cerrando Sesión...';
                                    document.getElementById('logout-form').submit();
                                }, 1000);
                            } else {
                                throw new Error(data.message || 'Error al cerrar la caja');
                            }
                        })
                        .catch(error => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            alert('Error: ' + error.message);
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
     @if(($currentUser && !$currentUser->is_admin && $isClosed && $currentUser->empresa_id) || $showClosingCard) style="filter: blur(5px);" @endif>
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

