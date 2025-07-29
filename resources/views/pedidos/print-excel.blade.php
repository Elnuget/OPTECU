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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 5mm;
            break-after: page;
            break-inside: avoid;
        }
        
        .page:last-child {
            break-after: avoid;
        }
        
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8mm;
            border: 3px solid #000;
            height: 75mm;
            break-inside: avoid;
        }
        
        .excel-table td {
            border: 2px solid #000;
            padding: 6px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
            position: relative;
            height: 75mm;
            overflow: hidden;
        }
        
        .company-column {
            width: 12%; /* Aumentar ancho de empresa */
            background-color: #f8f9fa;
            border: 2px solid #000;
        }
        
        .info-column {
            width: 35%; /* Aumentar significativamente el ancho de información del pedido */
            background-color: #ffffff;
            border: 2px solid #000;
        }
        
        .empty-column {
            width: 4%; /* Aumentar ligeramente columna vacía */
            background-color: #f8f9fa;
            border: 2px solid #000;
        }
        
        .method-column {
            width: 12%; /* Aumentar ancho de método de envío */
            background-color: #e7f3ff;
            border: 2px solid #000;
        }
        
        .barbosa-column {
            width: 10%; /* Aumentar columna Barbosa */
            background-color: #ffffe0;
            border: 2px solid #000;
        }
        
        .vertical-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            white-space: nowrap;
            font-weight: bold;
            font-size: 10px; /* Aumentar fuente para empresa */
            line-height: 1.3;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.5px;
        }
        
        .info-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 11px; /* Aumentar fuente para mejor legibilidad en celdas más grandes */
            line-height: 1.3;
            white-space: pre-line;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px; /* Más padding para mejor distribución del texto */
            font-weight: normal;
            word-spacing: 1px;
        }
        
        .method-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 10px; /* Aumentar fuente para método */
            color: #0066cc;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.5px;
        }
        
        .barbosa-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 9px; /* Aumentar fuente para Barbosa */
            color: #333;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.5px;
        }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .page {
                margin: 0;
                padding: 5mm;
                min-height: auto;
                height: auto;
                break-after: page;
                break-inside: avoid;
            }
            
            .page:last-child {
                break-after: avoid;
            }
            
            .excel-table {
                border: 3px solid #000 !important;
                height: 75mm !important;
                break-inside: avoid !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .excel-table td {
                border: 2px solid #000 !important;
                height: 75mm !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .company-column {
                background-color: #f8f9fa !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .info-column {
                background-color: #ffffff !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .empty-column {
                background-color: #f8f9fa !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .method-column {
                background-color: #e7f3ff !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .barbosa-column {
                background-color: #ffffe0 !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
        
        @page {
            size: A4 landscape;
            margin: 5mm;
            orphans: 1;
            widows: 1;
        }
    </style>
</head>
<body>
    @php
        $filasPorPagina = 2; // Cambiar a 2 filas por página
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
            window.print();
        }
        
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
