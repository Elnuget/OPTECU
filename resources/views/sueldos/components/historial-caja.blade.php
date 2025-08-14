{{-- Componente: Historial de Caja (Aperturas y Cierres) --}}
@if(isset($historialCaja) && count($historialCaja) > 0)
<div class="card mt-3">
    <div class="card-header bg-primary">
        <h3 class="card-title">
            <i class="fas fa-cash-register"></i> HISTORIAL DE CAJA - CONTROL DE HORAS
            @if(request('usuario'))
                - {{ strtoupper(request('usuario')) }}
            @endif
            ({{ str_pad(request('mes') ?: date('m'), 2, '0', STR_PAD_LEFT) }}/{{ request('anio') ?: date('Y') }})
        </h3>
        <div class="card-tools">
            <span class="badge badge-primary">{{ $historialCaja->count() }} DÍAS</span>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Resumen de Horas Trabajadas -->
        <div class="row p-3">
            @php
                $totalHorasNumericas = $historialCaja->where('horas_trabajadas', '!=', null)->sum('horas_trabajadas');
                $diasCompletos = $historialCaja->where('estado', 'Completo')->count();
                $diasSoloApertura = $historialCaja->where('estado', 'Solo apertura')->count();
                $promedioHoras = $diasCompletos > 0 ? $totalHorasNumericas / $diasCompletos : 0;
            @endphp
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL HORAS</span>
                        <span class="info-box-number">{{ number_format($totalHorasNumericas, 1) }}H</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">DÍAS COMPLETOS</span>
                        <span class="info-box-number">{{ $diasCompletos }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">PROMEDIO/DÍA</span>
                        <span class="info-box-number">{{ number_format($promedioHoras, 1) }}H</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">DÍAS INCOMPLETOS</span>
                        <span class="info-box-number">{{ $diasSoloApertura }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Historial Detallado -->
        <div class="table-responsive">
            <table class="table table-striped table-sm mb-0">
                <thead class="bg-primary">
                    <tr>
                        <th>FECHA</th>
                        <th>DÍA</th>
                        <th>EMPLEADO</th>
                        <th>APERTURA</th>
                        <th>CIERRE</th>
                        <th>HORAS TRABAJADAS</th>
                        <th>MONTO INICIAL</th>
                        <th>MONTO FINAL</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historialCaja as $registro)
                    <tr class="
                        @if($registro->estado == 'Completo') table-success
                        @elseif($registro->estado == 'Solo apertura') table-warning
                        @elseif($registro->estado == 'Solo cierre') table-info
                        @else table-light
                        @endif
                    ">
                        <td>
                            <strong>{{ $registro->fecha_formateada }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ strtoupper($registro->dia_semana) }}</span>
                        </td>
                        <td>{{ $registro->usuario }}</td>
                        <td>
                            @if($registro->hora_apertura)
                                <i class="fas fa-sign-in-alt text-success"></i> 
                                {{ $registro->hora_apertura }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($registro->hora_cierre)
                                <i class="fas fa-sign-out-alt text-danger"></i> 
                                {{ $registro->hora_cierre }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($registro->estado == 'Completo')
                                <span class="badge badge-success">
                                    <i class="fas fa-clock"></i> {{ $registro->horas_formateadas }}
                                </span>
                            @elseif($registro->estado == 'Solo apertura')
                                <span class="badge badge-warning">
                                    <i class="fas fa-hourglass-half"></i> EN PROGRESO
                                </span>
                            @else
                                <span class="badge badge-secondary">{{ $registro->horas_formateadas }}</span>
                            @endif
                        </td>
                        <td>
                            @if($registro->monto_apertura !== null)
                                ${{ number_format($registro->monto_apertura, 2, ',', '.') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($registro->monto_cierre !== null)
                                ${{ number_format($registro->monto_cierre, 2, ',', '.') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($registro->estado == 'Completo')
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> COMPLETO
                                </span>
                            @elseif($registro->estado == 'Solo apertura')
                                <span class="badge badge-warning">
                                    <i class="fas fa-play-circle"></i> SOLO APERTURA
                                </span>
                            @elseif($registro->estado == 'Solo cierre')
                                <span class="badge badge-info">
                                    <i class="fas fa-stop-circle"></i> SOLO CIERRE
                                </span>
                            @else
                                <span class="badge badge-secondary">SIN REGISTROS</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="5">TOTALES</th>
                        <th>
                            <span class="badge badge-primary">{{ number_format($totalHorasNumericas, 1) }} HORAS</span>
                        </th>
                        <th colspan="2">{{ $historialCaja->count() }} DÍAS REGISTRADOS</th>
                        <th>
                            <small>
                                <i class="fas fa-check text-success"></i> {{ $diasCompletos }} |
                                <i class="fas fa-exclamation-triangle text-warning"></i> {{ $diasSoloApertura }}
                            </small>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="card mt-3">
    <div class="card-header bg-secondary">
        <h3 class="card-title">
            <i class="fas fa-cash-register"></i> HISTORIAL DE CAJA - CONTROL DE HORAS
            @if(request('usuario'))
                - {{ strtoupper(request('usuario')) }}
            @endif
            ({{ str_pad(request('mes') ?: date('m'), 2, '0', STR_PAD_LEFT) }}/{{ request('anio') ?: date('Y') }})
        </h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            NO SE ENCONTRARON REGISTROS DE APERTURA/CIERRE DE CAJA PARA 
            @if(request('usuario'))
                {{ strtoupper(request('usuario')) }} EN
            @else
                LOS FILTROS SELECCIONADOS EN
            @endif
            {{ str_pad(request('mes') ?: date('m'), 2, '0', STR_PAD_LEFT) }}/{{ request('anio') ?: date('Y') }}
        </div>
    </div>
</div>
@endif
