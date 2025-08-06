@extends('adminlte::page')

@section('title', 'Dashboard')

@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\CashHistory;
@endphp

@section('content_header')
    <h1 class="text-primary">
        <i class="fas fa-home"></i> Bienvenido al Sistema de Gestión ÓPTICA
    </h1>
@stop

@section('content')
    {{-- Verificar autenticación --}}
    @if(!Auth::check())
        <script>
            window.location.href = "{{ route('login') }}";
        </script>
    @else
    {{-- Sección de Atajos --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-gradient-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-keyboard"></i> Atajos del Sistema</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-plus-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tecla [Inicio]</span>
                                    <span class="info-box-number">Nuevo Pedido</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-medical"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tecla [Fin]</span>
                                    <span class="info-box-number">Nuevo Historial Clínico</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial de Caja --}}
    <div class="row">
        <div class="col-12">
            @php
                $cashHistories = CashHistory::with('user')->latest()->get();
                $lastCashHistory = CashHistory::latest()->first();
            @endphp

            @if($lastCashHistory && $lastCashHistory->estado !== 'Apertura')
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i> Advertencia: Debes abrir la caja antes de continuar.
                    <a href="{{ route('cash-histories.index') }}" class="btn btn-primary ml-3">
                        <i class="fas fa-cash-register"></i> Abrir Caja
                    </a>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title"><i class="fas fa-cash-register"></i> Historial de Caja</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cashHistories as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                                                            <td>${{ number_format($history->monto, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $item->estado === 'Apertura' ? 'success' : 'danger' }}">
                                                {{ $item->estado }}
                                            </span>
                                        </td>
                                        <td>{{ $item->user->name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .info-box {
            border-radius: 10px;
            transition: all 0.3s;
        }
        .info-box:hover {
            transform: scale(1.05);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .alert {
            border-radius: 10px;
        }
        .badge {
            padding: 8px 12px;
            font-size: 0.9em;
        }
    </style>
@stop

@section('js')
    @include('atajos')
    <script>
        $(document).ready(function() {
            // Animación inicial
            $('.info-box').hide().fadeIn(1000);
        });
    </script>
@stop
