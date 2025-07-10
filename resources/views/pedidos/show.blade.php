@extends('adminlte::page')

@section('title', 'Ver Venta')

@section('content_header')
<h2>Ver Venta</h2>
@stop

@section('content')
<style>
    /* Convertir todo el texto a mayúsculas */
    .card-title,
    .list-group-item,
    .table th,
    .table td,
                      <li class="list-group-item"><strong>Total:</strong> ${{ number_format($pedido->total, 0, ',', '.') }}</li>
                    <li class="list-group-item"><strong>Saldo:</strong> ${{ number_format($pedido->saldo, 0, ',', '.') }}</li>.text-muted,
    h2,
    h3,
    strong {
        text-transform: uppercase !important;
    }

    /* Estilos para hacer clickeable el header completo */
    .card-header {
        cursor: pointer;
    }
    .card-header:hover {
        background-color: rgba(0,0,0,.03);
    }

    /* Estilos para la presentación de material separado por ojos */
    .material-separado {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    
    .material-separado span {
        padding: 3px 8px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .material-separado span strong {
        color: #495057;
    }
</style>
<br>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Venta {{ $pedido->id }}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="card-body">
        {{-- Información Básica --}}
        <div class="card collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Información Básica</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Fecha:</strong> {{ date('d-m-Y', strtotime($pedido->fecha)) }}</li>
                    <li class="list-group-item"><strong>Número de Orden:</strong> {{ $pedido->numero_orden }}</li>
                    <li class="list-group-item"><strong>Factura:</strong> {{ $pedido->fact }}</li>
                </ul>
            </div>
        </div>

        {{-- Datos Personales --}}
        <div class="card collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Datos Personales</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Cliente:</strong> {{ $pedido->cliente }}</li>
                    <li class="list-group-item"><strong>Cédula:</strong> {{ $pedido->cedula ?? 'No registrada' }}</li>
                    <li class="list-group-item"><strong>Paciente:</strong> {{ $pedido->paciente }}</li>
                    <li class="list-group-item"><strong>Celular:</strong> {{ $pedido->celular }}</li>
                    <li class="list-group-item"><strong>Correo Electrónico:</strong> {{ $pedido->correo_electronico }}</li>
                    <li class="list-group-item"><strong>Dirección:</strong> {{ $pedido->direccion }}</li>
                    <li class="list-group-item"><strong>Empresa:</strong> {{ $pedido->empresa ? $pedido->empresa->nombre : 'No asignada' }}</li>
                    <li class="list-group-item"><strong>Examen Visual:</strong> ${{ number_format($pedido->examen_visual, 0, ',', '.') }}</li>
                </ul>
            </div>
        </div>

        {{-- Armazón y Accesorios --}}
        <div class="card collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Armazón y Accesorios</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if ($pedido->inventarios->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Precio Base</th>
                                    <th>Descuento</th>
                                    <th>Precio Final</th>
                                    <th>Base</th>
                                    <th>IVA</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedido->inventarios as $inventario)
                                    @php
                                        $precioConDescuento = $inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100));
                                        $base = round($precioConDescuento / 1.19, 0);
                                        $iva = round($precioConDescuento - $base, 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $inventario->codigo }}</td>
                                        <td>${{ number_format($inventario->pivot->precio, 0, ',', '.') }}</td>
                                        <td>{{ $inventario->pivot->descuento }}%</td>
                                        <td>${{ number_format($precioConDescuento, 0, ',', '.') }}</td>
                                        <td>${{ number_format($base, 0, ',', '.') }}</td>
                                        <td>${{ number_format($iva, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if(isset($inventario->pivot->foto) && $inventario->pivot->foto)
                                                <img src="{{ asset($inventario->pivot->foto) }}" 
                                                     alt="Foto Armazón" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px; cursor: pointer;"
                                                     data-toggle="modal" 
                                                     data-target="#armazonModal{{ $loop->index }}"
                                                     title="Click para ampliar">
                                                
                                                <!-- Modal para ampliar imagen -->
                                                <div class="modal fade" id="armazonModal{{ $loop->index }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Foto Armazón - {{ $inventario->codigo }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ asset($inventario->pivot->foto) }}" 
                                                                     alt="Foto Armazón" 
                                                                     class="img-fluid">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <small class="text-muted">Sin foto</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No hay armazones asignados</p>
                @endif
            </div>
        </div>

        {{-- Lunas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lunas</h3>
            </div>
            <div class="card-body">
                @if ($pedido->lunas->count() > 0)
                    @foreach ($pedido->lunas as $luna)
                        <div class="luna-info {{ $loop->index > 0 ? 'mt-4 pt-4 border-top' : '' }}">
                            @if ($loop->index > 0)
                                <h5 class="text-muted mb-3">Luna {{ $loop->index + 1 }}</h5>
                            @endif
                            
                            {{-- Tabla de Prescripción/Medidas --}}
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <h6 class="mb-2">Prescripción/Medidas:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="15%">Ojo</th>
                                                    <th width="20%">Esfera</th>
                                                    <th width="20%">Cilindro</th>
                                                    <th width="20%">Eje</th>
                                                    <th width="25%">Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    // Parseamos los datos de l_medida si existen
                                                    $medidaData = [];
                                                    $addValue = '';
                                                    $dpValue = '';
                                                    
                                                    if ($luna->l_medida) {
                                                        // Intentar extraer los valores de la cadena existente
                                                        preg_match('/OD:\s*([+\-]?\d*\.?\d*)\s*([+\-]?\d*\.?\d*)\s*X?(\d*)°?/', $luna->l_medida, $odMatches);
                                                        preg_match('/OI:\s*([+\-]?\d*\.?\d*)\s*([+\-]?\d*\.?\d*)\s*X?(\d*)°?/', $luna->l_medida, $oiMatches);
                                                        preg_match('/ADD:\s*([+\-]?\d*\.?\d*)/', $luna->l_medida, $addMatches);
                                                        preg_match('/DP:\s*(\d+)/', $luna->l_medida, $dpMatches);
                                                        
                                                        $medidaData = [
                                                            'od_esfera' => $odMatches[1] ?? '',
                                                            'od_cilindro' => $odMatches[2] ?? '',
                                                            'od_eje' => $odMatches[3] ?? '',
                                                            'oi_esfera' => $oiMatches[1] ?? '',
                                                            'oi_cilindro' => $oiMatches[2] ?? '',
                                                            'oi_eje' => $oiMatches[3] ?? ''
                                                        ];
                                                        
                                                        $addValue = $addMatches[1] ?? '';
                                                        $dpValue = $dpMatches[1] ?? '';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="align-middle text-center font-weight-bold">OD</td>
                                                    <td class="text-center">{{ $medidaData['od_esfera'] ?: '-' }}</td>
                                                    <td class="text-center">{{ $medidaData['od_cilindro'] ?: '-' }}</td>
                                                    <td class="text-center">{{ $medidaData['od_eje'] ? $medidaData['od_eje'] . '°' : '-' }}</td>
                                                    <td rowspan="3" class="align-middle">
                                                        @if($luna->l_detalle)
                                                            <strong>Detalles:</strong><br>
                                                            {{ $luna->l_detalle }}
                                                        @else
                                                            <span class="text-muted">Sin observaciones</span>
                                                        @endif
                                                        
                                                        @if($addValue || $dpValue)
                                                            <hr class="my-2">
                                                        @endif
                                                        
                                                        @if($addValue)
                                                            <strong>ADD:</strong> {{ $addValue }}<br>
                                                        @endif
                                                        
                                                        @if($dpValue)
                                                            <strong>DP:</strong> {{ $dpValue }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="align-middle text-center font-weight-bold">OI</td>
                                                    <td class="text-center">{{ $medidaData['oi_esfera'] ?: '-' }}</td>
                                                    <td class="text-center">{{ $medidaData['oi_cilindro'] ?: '-' }}</td>
                                                    <td class="text-center">{{ $medidaData['oi_eje'] ? $medidaData['oi_eje'] . '°' : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        <small><strong>Medida original:</strong> {{ $luna->l_medida ?: 'No especificada' }}</small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    {{-- Información adicional y foto --}}
                                    <h6 class="mb-2">Información Adicional:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Tipo de Lente:</strong></td>
                                                    <td>{{ $luna->tipo_lente ?: 'No especificado' }}</td>
                                                </tr>
                                <tr>
                                                    <td><strong>Material:</strong></td>
                                                    <td>
                                                        @php
                                                            // Parsear los datos de material si están en formato "OD: valor | OI: valor"
                                                            $materialDisplay = '';
                                                            if ($luna->material && strpos($luna->material, 'OD:') !== false) {
                                                                preg_match('/OD:\s*([^|]+)/', $luna->material, $odMatches);
                                                                preg_match('/OI:\s*(.+)/', $luna->material, $oiMatches);
                                                                
                                                                $materialOD = trim($odMatches[1] ?? '');
                                                                $materialOI = trim($oiMatches[1] ?? '');
                                                                
                                                                if ($materialOD || $materialOI) {
                                                                    $materialDisplay = '<div class="material-separado">';
                                                                    if ($materialOD) {
                                                                        $materialDisplay .= '<span><strong>OD:</strong> ' . $materialOD . '</span>';
                                                                    }
                                                                    if ($materialOI) {
                                                                        $materialDisplay .= '<span><strong>OI:</strong> ' . $materialOI . '</span>';
                                                                    }
                                                                    $materialDisplay .= '</div>';
                                                                } else {
                                                                    $materialDisplay = '<span class="text-muted">No especificado</span>';
                                                                }
                                                            } else {
                                                                // Formato antiguo o valor único
                                                                if ($luna->material) {
                                                                    $materialDisplay = '<span class="badge badge-light">' . $luna->material . '</span>';
                                                                } else {
                                                                    $materialDisplay = '<span class="text-muted">No especificado</span>';
                                                                }
                                                            }
                                                        @endphp
                                                        {!! $materialDisplay !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Filtro:</strong></td>
                                                    <td>{{ $luna->filtro ?: 'No especificado' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Precio:</strong></td>
                                                    <td>${{ number_format($luna->l_precio, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Descuento:</strong></td>
                                                    <td>{{ $luna->l_precio_descuento }}%</td>
                                                </tr>
                                                @php
                                                    $precioConDescuento = $luna->l_precio * (1 - ($luna->l_precio_descuento / 100));
                                                    $base = round($precioConDescuento / 1.19, 0);
                                                    $iva = round($precioConDescuento - $base, 0);
                                                @endphp
                                                <tr class="table-info">
                                                    <td><strong>Precio Final:</strong></td>
                                                    <td><strong>${{ number_format($precioConDescuento, 0, ',', '.') }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Base:</strong></td>
                                                    <td>${{ number_format($base, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>IVA:</strong></td>
                                                    <td>${{ number_format($iva, 0, ',', '.') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    {{-- Foto --}}
                                    <div class="text-center mt-3">
                                        @if(isset($luna->foto) && $luna->foto)
                                            <h6 class="mb-2">Foto:</h6>
                                            <img src="{{ asset($luna->foto) }}" 
                                                 alt="Foto Luna" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 150px; max-height: 150px; cursor: pointer;"
                                                 data-toggle="modal" 
                                                 data-target="#lunaModal{{ $loop->index }}"
                                                 title="Click para ampliar">
                                            
                                            <!-- Modal para ampliar imagen -->
                                            <div class="modal fade" id="lunaModal{{ $loop->index }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Foto Luna - {{ $luna->l_medida }}</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <img src="{{ asset($luna->foto) }}" 
                                                                 alt="Foto Luna" 
                                                                 class="img-fluid">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-light">
                                                <small class="text-muted">Sin foto disponible</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No hay lunas asignadas a este pedido.
                    </div>
                @endif
            </div>
        </div>

        {{-- Compra Rápida --}}
        <div class="card collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Compra Rápida</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Valor de Compra:</strong> ${{ number_format($pedido->valor_compra, 0, ',', '.') }}</li>
                    <li class="list-group-item"><strong>Motivo de Compra:</strong> {{ $pedido->motivo_compra }}</li>
                </ul>
            </div>
        </div>

        {{-- Historial de Pagos --}}
        <div class="card collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Historial de Pagos</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if ($pedido->pagos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPagado = 0; @endphp
                                @foreach ($pedido->pagos as $pago)
                                    @php $totalPagado += $pago->pago; @endphp
                                    <tr>
                                        <td>{{ $pago->created_at ? $pago->created_at->format('d-m-Y H:i') : 'Sin fecha' }}</td>
                                        <td>{{ $pago->mediodepago ? $pago->mediodepago->medio_de_pago : 'No especificado' }}</td>
                                        <td>${{ number_format($pago->pago, 0, ',', '.') }}</td>
                                        <td class="text-center">
                            @if(isset($pago->foto) && $pago->foto)
                                <img src="{{ asset('uploads/pagos/' . $pago->foto) }}" 
                                     alt="Comprobante de Pago" 
                                     class="img-thumbnail" 
                                     style="max-width: 60px; max-height: 60px; cursor: pointer;"
                                     data-toggle="modal" 
                                     data-target="#pagoModal{{ $loop->index }}"
                                     title="Click para ampliar comprobante">
                                
                                <!-- Modal para ampliar imagen del comprobante -->
                                <div class="modal fade" id="pagoModal{{ $loop->index }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Comprobante de Pago - ${{ number_format($pago->pago, 0, ',', '.') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset('uploads/pagos/' . $pago->foto) }}" 
                                                     alt="Comprobante de Pago" 
                                                     class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <small class="text-muted">Sin comprobante</small>
                            @endif
                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th colspan="2">Total Pagado:</th>
                                    <th>${{ number_format($totalPagado, 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No se han registrado pagos para este pedido.
                    </div>
                @endif
            </div>
        </div>

        {{-- Totales --}}
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Total:</strong> ${{ number_format($pedido->total, 0, ',', '.') }}</li>
                    <li class="list-group-item"><strong>Saldo:</strong> ${{ number_format($pedido->saldo, 0, ',', '.') }}</li>
                    @if ($pedido->pagos->count() > 0)
                        <li class="list-group-item"><strong>Total Pagado:</strong> 
                            <span class="text-success">${{ number_format($pedido->pagos->sum('pago'), 0, ',', '.') }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    // Hacer que todo el header sea clickeable
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.card-header').forEach(header => {
            header.addEventListener('click', function(e) {
                // Si el clic no fue en un botón dentro del header
                if (!e.target.closest('.btn-tool')) {
                    const collapseButton = this.querySelector('.btn-tool');
                    if (collapseButton) {
                        collapseButton.click();
                    }
                }
            });
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === "Home") {
            window.location.href = '/dashboard';
        }
    });
</script>
@stop