@extends('adminlte::page')

@section('title', 'Actualizar Artículos')

@section('content_header')
    <h1>ACTUALIZAR ARTÍCULOS</h1>
@stop

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Convertir todo el texto a mayúsculas */
        body, 
        .content-wrapper, 
        .main-header, 
        .main-sidebar, 
        .card-title,
        .info-box-text,
        .info-box-number,
        .custom-select,
        .btn,
        label,
        input,
        select,
        option,
        datalist,
        datalist option,
        .form-control,
        p,
        h1, h2, h3, h4, h5, h6,
        th,
        td,
        span,
        a,
        .dropdown-item,
        .alert,
        .modal-title,
        .modal-body p,
        .modal-content,
        .card-header,
        .card-footer,
        button,
        .close,
        .table thead th,
        .table tbody td,
        .dataTables_filter,
        .dataTables_info,
        .paginate_button,
        .alert-info {
            text-transform: uppercase !important;
        }

        /* Asegurar que el placeholder también esté en mayúsculas */
        input::placeholder,
        .dataTables_filter input::placeholder {
            text-transform: uppercase !important;
        }

        /* Asegurar que las opciones del datalist estén en mayúsculas */
        datalist option {
            text-transform: uppercase !important;
        }
    </style>

    <div class="row">
        {{-- Tarjeta de Artículos sin Orden --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ARTÍCULOS SIN ORDEN ASIGNADA</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="mes_filtro">FILTRAR POR MES:</label>
                            <input type="month" class="form-control" id="mes_filtro" name="mes_filtro">
                        </div>
                        <div class="col-md-4">
                            <label for="lugar_filtro">FILTRAR POR LUGAR:</label>
                            <select class="form-control" id="lugar_filtro" name="lugar_filtro">
                                <option value="">TODOS</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="columna_filtro">FILTRAR POR COLUMNA:</label>
                            <input type="number" class="form-control" id="columna_filtro" name="columna_filtro" placeholder="INGRESE COLUMNA">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <button id="actualizarFechasBtn" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> ACTUALIZAR AL MES SIGUIENTE DEL ORIGINAL
                            </button>
                        </div>
                    </div>
                    
                    @if($inventario->isEmpty())
                        <div class="alert alert-info">
                            NO HAY ARTÍCULOS SIN ORDEN ASIGNADA.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="actualizarTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                                <label class="custom-control-label" for="checkAll"></label>
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>CÓDIGO</th>
                                        <th>CANTIDAD</th>
                                        <th>LUGAR</th>
                                        <th>COLUMNA</th>
                                        <th>NUMERO</th>
                                        <th>FECHA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventario as $item)
                                        <tr data-fecha="{{ $item->fecha }}" 
                                            data-lugar="{{ $item->lugar }}" 
                                            data-columna="{{ $item->columna }}"
                                            data-id="{{ $item->id }}" 
                                            class="fila-inventario">
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input fila-checkbox" id="check{{ $item->id }}">
                                                    <label class="custom-control-label" for="check{{ $item->id }}"></label>
                                                </div>
                                            </td>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->codigo }}</td>
                                            <td>{{ $item->cantidad }}</td>
                                            <td>{{ $item->lugar }}</td>
                                            <td>{{ $item->columna }}</td>
                                            <td>{{ $item->numero ?? 1 }}</td>
                                            <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar el filtro de mes para mostrar el mes anterior por defecto
            const today = new Date();
            const mesAnterior = new Date(today.getFullYear(), today.getMonth() - 1);
            const mesAnteriorStr = mesAnterior.toISOString().slice(0, 7);
            document.getElementById('mes_filtro').value = mesAnteriorStr;

            // Checkbox "Seleccionar todos"
            document.getElementById('checkAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.fila-checkbox');
                checkboxes.forEach(checkbox => {
                    const fila = checkbox.closest('tr');
                    if (fila.style.display !== 'none') { // Solo afecta a las filas visibles
                        checkbox.checked = this.checked;
                    }
                });
            });

            // Botón de actualizar fechas
            document.getElementById('actualizarFechasBtn').addEventListener('click', async function() {
                const filasSeleccionadas = document.querySelectorAll('.fila-checkbox:checked');
                
                if (filasSeleccionadas.length === 0) {
                    alert('Por favor, seleccione al menos un artículo para actualizar.');
                    return;
                }

                if (!confirm('¿Está seguro de crear nuevos registros con la fecha del MES SIGUIENTE AL MES ORIGINAL de los artículos seleccionados?')) {
                    return;
                }

                const articulos = Array.from(filasSeleccionadas).map(checkbox => {
                    const fila = checkbox.closest('tr');
                    return {
                        id: fila.querySelector('td:nth-child(2)').textContent,
                        codigo: fila.querySelector('td:nth-child(3)').textContent,
                        cantidad: parseInt(fila.querySelector('td:nth-child(4)').textContent),
                        lugar: fila.querySelector('td:nth-child(5)').textContent,
                        columna: fila.querySelector('td:nth-child(6)').textContent,
                        numero: parseInt(fila.querySelector('td:nth-child(7)').textContent || '1'),
                        fecha_original: fila.getAttribute('data-fecha')
                    };
                });

                try {
                    console.log('Enviando datos:', articulos);
                    const response = await fetch('{{ route("inventario.crear-nuevos-registros") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ articulos: articulos })
                    });

                    console.log('Status:', response.status);
                    console.log('Status Text:', response.statusText);

                    // Verificar el tipo de contenido de la respuesta
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Tipo de contenido no válido:', contentType);
                        throw new Error('La respuesta del servidor no es JSON. Verifica que la ruta esté correctamente definida.');
                    }

                    const result = await response.json();
                    console.log('Respuesta:', result);

                    if (!response.ok) {
                        throw new Error(result.error || `Error del servidor: ${response.status} - ${response.statusText}`);
                    }

                    if (result.success) {
                        alert('Registros creados correctamente.');
                        window.location.reload();
                    } else {
                        throw new Error(result.error || 'Error al crear los nuevos registros');
                    }
                } catch (error) {
                    console.error('Error detallado:', error);
                    alert('Error: ' + error.message);
                }
            });

            // Función para actualizar los lugares disponibles según el mes seleccionado
            function actualizarLugares(mes) {
                const filas = document.querySelectorAll('.fila-inventario');
                const lugaresSet = new Set();
                
                console.log('Mes seleccionado:', mes);
                
                // Recolectar lugares únicos que tienen stock en el mes seleccionado
                filas.forEach(fila => {
                    const fechaFila = fila.getAttribute('data-fecha');
                    const mesFila = fechaFila.slice(0, 7);
                    const cantidad = parseInt(fila.querySelector('td:nth-child(4)').textContent);
                    const lugar = fila.querySelector('td:nth-child(5)').textContent.trim();
                    
                    console.log('Revisando fila:', {
                        lugar: lugar,
                        cantidad: cantidad,
                        mesFila: mesFila,
                        fechaFila: fechaFila
                    });

                    // Eliminar la condición del mes para ver todos los lugares
                    if (cantidad !== 0) {
                        lugaresSet.add(lugar);
                        console.log('Agregando lugar:', lugar);
                    }
                });

                console.log('Lugares encontrados:', Array.from(lugaresSet));

                // Actualizar el combobox de lugares
                const lugarSelect = document.getElementById('lugar_filtro');
                const valorActual = lugarSelect.value;
                
                // Limpiar opciones actuales
                lugarSelect.innerHTML = '<option value="">TODOS</option>';
                
                // Agregar nuevas opciones ordenadas alfabéticamente
                Array.from(lugaresSet)
                    .sort()
                    .forEach(lugar => {
                        const option = document.createElement('option');
                        option.value = lugar;
                        option.textContent = lugar;
                        lugarSelect.appendChild(option);
                    });

                // Restaurar selección si aún existe en las nuevas opciones
                if (valorActual && lugaresSet.has(valorActual)) {
                    lugarSelect.value = valorActual;
                }
            }

            // Función para filtrar la tabla
            function filtrarTabla() {
                const mes = document.getElementById('mes_filtro').value;
                const lugar = document.getElementById('lugar_filtro').value;
                const columna = document.getElementById('columna_filtro').value;

                console.log('Filtrando con:', {
                    mes: mes,
                    lugar: lugar,
                    columna: columna
                });

                const filas = document.querySelectorAll('.fila-inventario');
                
                // Crear un mapa para agrupar filas por código
                const filasPorCodigo = new Map();
                
                filas.forEach(fila => {
                    const codigo = fila.querySelector('td:nth-child(3)').textContent;
                    const fechaFila = fila.getAttribute('data-fecha');
                    if (!filasPorCodigo.has(codigo)) {
                        filasPorCodigo.set(codigo, []);
                    }
                    filasPorCodigo.get(codigo).push({
                        fila: fila,
                        fecha: fechaFila
                    });
                });

                let filasVisibles = 0;
                filas.forEach(fila => {
                    const fechaFila = fila.getAttribute('data-fecha');
                    const lugarFila = fila.querySelector('td:nth-child(5)').textContent.trim();
                    const columnaFila = fila.querySelector('td:nth-child(6)').textContent.trim();
                    const cantidad = parseInt(fila.querySelector('td:nth-child(4)').textContent);
                    const codigo = fila.querySelector('td:nth-child(3)').textContent;
                    
                    const mesFila = fechaFila.slice(0, 7);
                    const cumpleMes = mesFila === mes;
                    const cumpleLugar = !lugar || lugarFila.toUpperCase() === lugar.toUpperCase();
                    const cumpleColumna = !columna || columnaFila === columna;
                    const tieneStock = cantidad !== 0;

                    // Verificar si existe el mismo código en un mes posterior
                    const registrosDelCodigo = filasPorCodigo.get(codigo) || [];
                    const existeEnMesSiguiente = registrosDelCodigo.some(registro => {
                        const mesFecha = registro.fecha.slice(0, 7);
                        return mesFecha > mes;
                    });

                    const debeMostrar = cumpleMes && cumpleLugar && cumpleColumna && tieneStock && !existeEnMesSiguiente;
                    
                    if (lugarFila.toUpperCase().includes('GOTERO')) {
                        console.log('Fila de GOTERO:', {
                            lugar: lugarFila,
                            cantidad: cantidad,
                            cumpleMes: cumpleMes,
                            cumpleLugar: cumpleLugar,
                            cumpleColumna: cumpleColumna,
                            tieneStock: tieneStock,
                            existeEnMesSiguiente: existeEnMesSiguiente,
                            debeMostrar: debeMostrar
                        });
                    }

                    if (debeMostrar) {
                        fila.style.display = '';
                        filasVisibles++;
                    } else {
                        fila.style.display = 'none';
                    }
                });

                console.log('Total de filas visibles:', filasVisibles);
            }

            // Eventos para los filtros
            document.getElementById('mes_filtro').addEventListener('change', function() {
                actualizarLugares(this.value);
                filtrarTabla();
            });
            document.getElementById('lugar_filtro').addEventListener('change', filtrarTabla);
            document.getElementById('columna_filtro').addEventListener('input', filtrarTabla);

            // Inicialización
            actualizarLugares(mesAnteriorStr);
            filtrarTabla();

            // Inicializar DataTable
            $('#actualizarTable').DataTable({
                "scrollX": true,
                "order": [[0, "desc"]],
                "language": {
                    "url": "{{ asset('js/datatables/Spanish.json') }}",
                    "search": "BUSCAR:",
                    "info": "",
                    "infoEmpty": "",
                    "infoFiltered": "",
                    "paginate": {
                        "next": "",
                        "previous": ""
                    }
                },
                "paging": false,
                "info": false,
                "searching": true,
                "stateSave": true,
                "stateDuration": 60 * 60 * 24, // 24 horas
                "initComplete": function() {
                    actualizarLugares(mesAnteriorStr);
                    filtrarTabla();
                }
            });
        });
    </script>
@stop