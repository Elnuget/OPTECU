<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rol de Pago - {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}</title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
        }
        
        .header-empresa {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-radius: 8px;
        }
        
        .header-empresa h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header-empresa p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .periodo-info {
            background: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
        
        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
            font-size: 11px;
        }
        
        .resumen-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .resumen-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px dotted #6c757d;
        }
        
        .resumen-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 14px;
        }
        
        .section-title {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            margin: 20px 0 10px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Botón de impresión -->
    <div class="no-print">
        <button class="btn btn-success btn-print" onclick="window.print();">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="container-fluid">
        <!-- Encabezado de la empresa -->
        <div class="header-empresa">
            <h1>{{ $empresa->nombre ?? 'EMPRESA' }}</h1>
            <p>ROL DE PAGO - PERÍODO {{ strtoupper(date('F', mktime(0, 0, 0, $mes, 1))) }} {{ $anio }}</p>
            @if($usuario)
                <p><strong>EMPLEADO: {{ strtoupper($usuario) }}</strong></p>
            @else
                <p><strong>TODOS LOS EMPLEADOS</strong></p>
            @endif
            <small>Generado el: {{ date('d/m/Y H:i') }}</small>
        </div>

        <!-- Información del período -->
        <div class="periodo-info">
            <div class="row">
                <div class="col-md-4">
                    <strong>Período:</strong> {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}
                </div>
                <div class="col-md-4">
                    <strong>Mes:</strong> {{ strtoupper(date('F', mktime(0, 0, 0, $mes, 1))) }}
                </div>
                <div class="col-md-4">
                    <strong>Año:</strong> {{ $anio }}
                </div>
            </div>
        </div>

        @if(isset($detallesSueldo) && count($detallesSueldo) > 0)
        <!-- Sección de Detalles de Sueldo -->
        <div class="section-title">
            <i class="fas fa-user-tie"></i> DETALLES DE SUELDO
        </div>
        
        <!-- Resumen de detalles -->
        <div class="resumen-box">
            <div class="resumen-item">
                <span>Total de Detalles:</span>
                <span>{{ $detallesSueldo->count() }}</span>
            </div>
            <div class="resumen-item">
                <span>Valor Total:</span>
                <span>${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</span>
            </div>
            <div class="resumen-item">
                <span>Promedio por Detalle:</span>
                <span>${{ number_format($detallesSueldo->count() > 0 ? $detallesSueldo->sum('valor') / $detallesSueldo->count() : 0, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tabla de detalles -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>FECHA</th>
                        <th>EMPLEADO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>VALOR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detallesSueldo as $detalle)
                    <tr>
                        <td>{{ $detalle->created_at ? $detalle->created_at->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $detalle->user ? $detalle->user->name : 'N/A' }}</td>
                        <td>{{ $detalle->descripcion }}</td>
                        <td class="text-right">${{ number_format($detalle->valor, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-success text-white">
                    <tr>
                        <th colspan="3">TOTAL DETALLES</th>
                        <th class="text-right">${{ number_format($detallesSueldo->sum('valor'), 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        @if(isset($retirosCaja) && count($retirosCaja) > 0)
        <!-- Sección de Retiros de Caja -->
        <div class="section-title page-break">
            <i class="fas fa-cash-register"></i> RETIROS DE CAJA
        </div>
        
        <!-- Resumen de retiros -->
        <div class="resumen-box">
            <div class="resumen-item">
                <span>Total de Retiros:</span>
                <span>{{ $retirosCaja->count() }}</span>
            </div>
            <div class="resumen-item">
                <span>Monto Total:</span>
                <span>${{ number_format(abs($retirosCaja->sum('valor')), 2, ',', '.') }}</span>
            </div>
            <div class="resumen-item">
                <span>Promedio por Retiro:</span>
                <span>${{ number_format($retirosCaja->count() > 0 ? abs($retirosCaja->sum('valor')) / $retirosCaja->count() : 0, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tabla de retiros -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>FECHA</th>
                        <th>USUARIO</th>
                        <th>EMPRESA</th>
                        <th>MOTIVO</th>
                        <th>VALOR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($retirosCaja as $retiro)
                    <tr>
                        <td>{{ $retiro->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $retiro->user ? $retiro->user->name : 'N/A' }}</td>
                        <td>{{ $retiro->empresa ? $retiro->empresa->nombre : 'N/A' }}</td>
                        <td>{{ $retiro->motivo }}</td>
                        <td class="text-right">${{ number_format(abs($retiro->valor), 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-danger text-white">
                    <tr>
                        <th colspan="4">TOTAL RETIROS</th>
                        <th class="text-right">${{ number_format(abs($retirosCaja->sum('valor')), 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        @if(isset($historialCaja) && count($historialCaja) > 0)
        <!-- Sección de Historial de Caja (Control de Horas) -->
        <div class="section-title page-break">
            <i class="fas fa-clock"></i> CONTROL DE HORAS TRABAJADAS
        </div>
        
        @php
            // Calcular totales de horas
            $totalMinutosGlobal = $historialCaja->where('total_minutos', '!=', null)->sum('total_minutos');
            $totalHorasCalculadas = intval($totalMinutosGlobal / 60);
            $totalMinutosRestantes = $totalMinutosGlobal % 60;
            $diasCompletos = $historialCaja->where('estado', 'Completo')->count();
            $promedioMinutos = $diasCompletos > 0 ? $totalMinutosGlobal / $diasCompletos : 0;
            $promedioHoras = intval($promedioMinutos / 60);
            $promedioMinutosRestantes = intval($promedioMinutos % 60);
        @endphp

        <!-- Resumen de horas -->
        <div class="resumen-box">
            <div class="resumen-item">
                <span>Total de Días:</span>
                <span>{{ $historialCaja->count() }}</span>
            </div>
            <div class="resumen-item">
                <span>Días Completos:</span>
                <span>{{ $diasCompletos }}</span>
            </div>
            <div class="resumen-item">
                <span>Total Horas Trabajadas:</span>
                <span>{{ $totalHorasCalculadas }}h {{ $totalMinutosRestantes }}m</span>
            </div>
            <div class="resumen-item">
                <span>Promedio por Día:</span>
                <span>{{ $promedioHoras }}h {{ $promedioMinutosRestantes }}m</span>
            </div>
        </div>

        <!-- Tabla de control de horas -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>FECHA</th>
                        <th>DÍA</th>
                        <th>USUARIO</th>
                        <th>EMPRESA</th>
                        <th>APERTURA</th>
                        <th>CIERRE</th>
                        <th>HORAS TRABAJADAS</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historialCaja as $dia)
                    <tr class="{{ $dia->estado == 'Completo' ? 'table-success' : ($dia->estado == 'Solo apertura' ? 'table-warning' : 'table-danger') }}">
                        <td>{{ $dia->fecha_formateada }}</td>
                        <td>{{ ucfirst($dia->dia_semana) }}</td>
                        <td>{{ $dia->usuario }}</td>
                        <td>{{ $dia->empresa }}</td>
                        <td>{{ $dia->hora_apertura ?: '-' }}</td>
                        <td>{{ $dia->hora_cierre ?: '-' }}</td>
                        <td class="text-center">{{ $dia->horas_formateadas }}</td>
                        <td>
                            <span class="badge badge-{{ $dia->estado == 'Completo' ? 'success' : ($dia->estado == 'Solo apertura' ? 'warning' : 'danger') }}">
                                {{ strtoupper($dia->estado) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-primary text-white">
                    <tr>
                        <th colspan="6">TOTALES</th>
                        <th class="text-center">{{ $totalHorasCalculadas }}h {{ $totalMinutosRestantes }}m</th>
                        <th>{{ $diasCompletos }} DÍAS</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Resumen General -->
        <div class="section-title">
            <i class="fas fa-calculator"></i> RESUMEN GENERAL
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="resumen-box">
                    <h5 class="text-center mb-3">INGRESOS</h5>
                    <div class="resumen-item">
                        <span>Detalles de Sueldo:</span>
                        <span>${{ number_format($detallesSueldo->sum('valor') ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="resumen-item">
                        <span><strong>TOTAL INGRESOS:</strong></span>
                        <span><strong>${{ number_format($detallesSueldo->sum('valor') ?? 0, 2, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resumen-box">
                    <h5 class="text-center mb-3">EGRESOS</h5>
                    <div class="resumen-item">
                        <span>Retiros de Caja:</span>
                        <span>${{ number_format(abs($retirosCaja->sum('valor') ?? 0), 2, ',', '.') }}</span>
                    </div>
                    <div class="resumen-item">
                        <span><strong>TOTAL EGRESOS:</strong></span>
                        <span><strong>${{ number_format(abs($retirosCaja->sum('valor') ?? 0), 2, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="resumen-box text-center">
                    @php
                        $totalIngresos = $detallesSueldo->sum('valor') ?? 0;
                        $totalEgresos = abs($retirosCaja->sum('valor') ?? 0);
                        $saldoNeto = $totalIngresos - $totalEgresos;
                    @endphp
                    <h4>SALDO NETO: 
                        <span class="badge badge-{{ $saldoNeto >= 0 ? 'success' : 'danger' }} p-2">
                            ${{ number_format($saldoNeto, 2, ',', '.') }}
                        </span>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Información de generación -->
        <div class="text-center mt-4 text-muted">
            <small>
                Reporte generado automáticamente el {{ date('d/m/Y H:i:s') }} <br>
                Sistema de Gestión {{ $empresa->nombre ?? 'OPTECU' }}
            </small>
        </div>
    </div>

    <!-- Font Awesome para iconos -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
