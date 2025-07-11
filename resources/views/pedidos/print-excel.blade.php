<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Excel - Pedidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            table-layout: fixed;
        }
        
        .excel-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
            position: relative;
            height: 150px;
        }
        
        .company-column {
            width: 12%;
            background-color: #f8f9fa;
        }
        
        .info-column {
            width: 15%;
            background-color: #ffffff;
        }
        
        .empty-column {
            width: 5%;
            background-color: #f8f9fa;
        }
        
        .method-column {
            width: 15%;
            background-color: #e7f3ff;
        }
        
        .vertical-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            white-space: nowrap;
            font-weight: bold;
            font-size: 12px;
            line-height: 1.2;
        }
        
        .info-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 10px;
            line-height: 1.1;
            white-space: pre-line;
            padding: 5px;
        }
        
        .method-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 12px;
            color: #0066cc;
        }
        
        .barbosa-column {
            width: 8%;
            background-color: #ffffe0;
        }
        
        .barbosa-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 11px;
            color: #333;
        }
        
        .print-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        @media print {
            .print-buttons {
                display: none;
            }
            
            body {
                background-color: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
            
            .excel-table {
                page-break-inside: avoid;
            }
        }
        
        .footer-info {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Vista de Impresión - Formato Excel</h1>
            <p>Pedidos Seleccionados: {{ $pedidosAgrupados->flatten()->count() }}</p>
        </div>
        
        <div class="print-buttons">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
            <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
            <a href="{{ url()->previous() }}" class="btn btn-success">⬅️ Volver</a>
        </div>
        
        @foreach($pedidosAgrupados as $grupo)
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
        
        <div class="footer-info">
            <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            <p><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
        </div>
    </div>

    <script>
        // Auto-focus para mejor experiencia
        window.onload = function() {
            document.body.focus();
        };
        
        // Atajos de teclado
        document.addEventListener('keydown', function(e) {
            // Ctrl+P para imprimir
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Escape para cerrar
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
