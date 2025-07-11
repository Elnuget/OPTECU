<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Excel - Pedidos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: black;
            font-size: 10px;
        }
        
        .page {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 10mm;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
        
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 15mm;
            border: 2px solid #000;
        }
        
        .excel-table td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
            position: relative;
            height: 120px;
            overflow: hidden;
        }
        
        .company-column {
            width: 12%;
            background-color: #f8f9fa;
            border: 1px solid #000;
        }
        
        .info-column {
            width: 15%;
            background-color: #ffffff;
            border: 1px solid #000;
        }
        
        .empty-column {
            width: 5%;
            background-color: #f8f9fa;
            border: 1px solid #000;
        }
        
        .method-column {
            width: 15%;
            background-color: #e7f3ff;
            border: 1px solid #000;
        }
        
        .barbosa-column {
            width: 8%;
            background-color: #ffffe0;
            border: 1px solid #000;
        }
        
        .vertical-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            white-space: nowrap;
            font-weight: bold;
            font-size: 9px;
            line-height: 1.1;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .info-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 8px;
            line-height: 1.0;
            white-space: pre-line;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
        }
        
        .method-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 9px;
            color: #0066cc;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .barbosa-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 8px;
            color: #333;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media print {
            .page {
                margin: 0;
                padding: 10mm;
                height: 297mm; /* A4 height */
                width: 210mm;  /* A4 width */
            }
            
            .excel-table {
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .excel-table td {
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .company-column {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .info-column {
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .empty-column {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .method-column {
                background-color: #e7f3ff !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .barbosa-column {
                background-color: #ffffe0 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
        
        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>
    @php
        $filasPorPagina = 2;
        $totalFilas = $pedidosAgrupados->count();
        $paginas = ceil($totalFilas / $filasPorPagina);
    @endphp
    
    @for($pagina = 0; $pagina < $paginas; $pagina++)
        <div class="page">
            @php
                $inicioFila = $pagina * $filasPorPagina;
                $finFila = min($inicioFila + $filasPorPagina, $totalFilas);
                $filasPagina = $pedidosAgrupados->slice($inicioFila, $filasPorPagina);
            @endphp
            
            @foreach($filasPagina as $grupo)
            <table class="excel-table">
                <tr>
                    <!-- Columna Barbosa -->
                    <td class="barbosa-column">
                        <div class="barbosa-text">DE: L BARBOSA SPA 77.219.776-4</div>
                    </td>
                    
                    @foreach($grupo as $index => $pedido)
                        @php
                            $empresaNombre = $pedido->empresa ? $pedido->empresa->nombre : 'Sin empresa';
                            $numeroOrden = $pedido->numero_orden;
                            
                            $infoPedido = "CLIENTE: " . strtoupper($pedido->cliente) . "\n";
                            $infoPedido .= "CÉDULA: " . ($pedido->cedula ? $pedido->cedula : 'NO REGISTRADA') . "\n";
                            $infoPedido .= "TELÉFONO: " . $pedido->celular . "\n";
                            $infoPedido .= "DIRECCIÓN: " . ($pedido->direccion ? $pedido->direccion : 'NO REGISTRADA') . "\n";
                            $infoPedido .= "CORREO: " . ($pedido->correo_electronico ? $pedido->correo_electronico : 'NO REGISTRADO') . "\n";
                            $infoPedido .= "FECHA ENTREGA: " . ($pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : 'NO REGISTRADA') . "\n";
                            
                            if ($pedido->inventarios->count() > 0) {
                                $infoPedido .= "ARMAZONES/ACCESORIOS:\n";
                                foreach ($pedido->inventarios as $inventario) {
                                    $infoPedido .= "- " . $inventario->codigo . "\n";
                                }
                            }
                            
                            $metodoEnvio = $pedido->metodo_envio ? strtoupper($pedido->metodo_envio) : 'NO ESPECIFICADO';
                        @endphp
                        
                        <!-- Empresa + Número de Orden -->
                        <td class="company-column">
                            <div class="vertical-text">{{ strtoupper($empresaNombre) }} - {{ $numeroOrden }}</div>
                        </td>
                        
                        <!-- Información del Pedido -->
                        <td class="info-column">
                            <div class="info-text">{{ $infoPedido }}</div>
                        </td>
                        
                        <!-- Columna Vacía -->
                        <td class="empty-column"></td>
                        
                        <!-- Método de Envío -->
                        <td class="method-column">
                            <div class="method-text">{{ $metodoEnvio }}</div>
                        </td>
                    @endforeach
                    
                    @php
                        $pedidosFaltantes = 3 - $grupo->count();
                    @endphp
                    
                    @for($i = 0; $i < $pedidosFaltantes; $i++)
                        <!-- Columnas vacías para completar la fila -->
                        <td class="company-column"></td>
                        <td class="info-column"></td>
                        <td class="empty-column"></td>
                        <td class="method-column"></td>
                    @endfor
                </tr>
            </table>
            @endforeach
        </div>
    @endfor

    <script>
        // Auto-imprimir cuando se carga la página
        window.onload = function() {
            setTimeout(function() {
                window.print();
                // Cerrar la ventana después de imprimir (opcional)
                setTimeout(function() {
                    window.close();
                }, 1000);
            }, 500);
        };
        
        // También permitir impresión manual con Ctrl+P
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
