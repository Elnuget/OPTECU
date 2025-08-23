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
    .text-muted,
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
                    <li class="list-group-item"><strong>Empresa:</strong> {{ $pedido->empresa ? $pedido->empresa->nombre : 'Sin empresa asignada' }}</li>
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
                    <li class="list-group-item"><strong>Examen Visual:</strong> ${{ number_format($pedido->examen_visual, 2, ',', '.') }}</li>
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedido->inventarios as $inventario)
                                    @php
                                        $precioConDescuento = $inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100));
                                        $base = round($precioConDescuento / 1.15, 2);
                                        $iva = round($precioConDescuento - $base, 2);
                                    @endphp
                                    <tr>
                                        <td>{{ $inventario->codigo }}</td>
                                        <td>${{ number_format($inventario->pivot->precio, 2, ',', '.') }}</td>
                                        <td>{{ $inventario->pivot->descuento }}%</td>
                                        <td>${{ number_format($precioConDescuento, 2, ',', '.') }}</td>
                                        <td>${{ number_format($base, 2, ',', '.') }}</td>
                                        <td>${{ number_format($iva, 2, ',', '.') }}</td>
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
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Medida</th>
                                    <th>Detalle</th>
                                    <th>Tipo de Lente</th>
                                    <th>Material</th>
                                    <th>Filtro</th>
                                    <th>Precio</th>
                                    <th>Desc. (%)</th>
                                    <th>Base</th>
                                    <th>IVA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedido->lunas as $luna)
                                    @php
                                        $precioConDescuento = $luna->l_precio * (1 - ($luna->l_precio_descuento / 100));
                                        $base = round($precioConDescuento / 1.15, 2);
                                        $iva = round($precioConDescuento - $base, 2);
                                    @endphp
                                    <tr>
                                        <td>{{ $luna->l_medida }}</td>
                                        <td>{{ $luna->l_detalle }}</td>
                                        <td>{{ $luna->tipo_lente }}</td>
                                        <td>{{ $luna->material }}</td>
                                        <td>{{ $luna->filtro }}</td>
                                        <td>${{ number_format($luna->l_precio, 2, ',', '.') }}</td>
                                        <td>{{ $luna->l_precio_descuento }}%</td>
                                        <td>${{ number_format($base, 2, ',', '.') }}</td>
                                        <td>${{ number_format($iva, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No hay lunas asignadas</p>
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
                    <li class="list-group-item"><strong>Valor de Compra:</strong> ${{ number_format($pedido->valor_compra, 2, ',', '.') }}</li>
                    <li class="list-group-item"><strong>Motivo de Compra:</strong> {{ $pedido->motivo_compra }}</li>
                </ul>
            </div>
        </div>

        {{-- Totales --}}
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Total:</strong> ${{ number_format($pedido->total, 2, ',', '.') }}</li>
                    <li class="list-group-item"><strong>Saldo:</strong> ${{ number_format($pedido->saldo, 2, ',', '.') }}</li>
                </ul>
            </div>
        </div>

            {{-- Pagos Asociados --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Pagos Asociados</h3>
                </div>
                <div class="card-body">
                    @if ($pedido->pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Método de Pago</th>
                                        <th>Monto</th>
                                        <th>TC</th>
                                        <th>Fecha</th>
                                        <th>Foto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pedido->pagos as $pago)
                                        <tr>
                                            <td>{{ $pago->id }}</td>
                                            <td>{{ $pago->mediodepago ? $pago->mediodepago->medio_de_pago : 'N/A' }}</td>
                                            <td>${{ number_format($pago->pago, 2, ',', '.') }}</td>
                                            <td>{{ $pago->TC ? 'Sí' : 'No' }}</td>
                                            <td>{{ $pago->created_at ? $pago->created_at->format('d-m-Y H:i') : '' }}</td>
                                            <td>
                                                @if ($pago->foto)
                                                    <a href="{{ asset('uploads/' . $pago->foto) }}" target="_blank">Ver</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay pagos registrados para este pedido.</p>
                    @endif
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