@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    @push('css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    @endpush

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    @endpush

    <h1>Inventario</h1>
    <p>Administración de Artículos</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong> {{ session('mensaje') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('content')
    <style>
        /* Estilos base y transformación a mayúsculas */
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
        .datalist,
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
        .dropdown-menu,
        .nav-link,
        .menu-item {
            text-transform: uppercase !important;
        }

        /* Estilos responsivos generales */
        .card-body {
            padding: 1rem;
        }

        .form-row {
            margin-right: -5px;
            margin-left: -5px;
        }

        .form-row > [class*='col-'] {
            padding-right: 5px;
            padding-left: 5px;
        }

        /* Ajustes responsivos para la barra de herramientas */
        .btn-toolbar {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn {
            white-space: nowrap;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Ajustes para tablas responsivas */
        .table-responsive {
            margin: 0;
            padding: 0;
            border: none;
            width: 100%;
        }

        .table {
            min-width: 100%;
        }

        .table td, .table th {
            padding: 0.5rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        /* Ajustes responsivos para tarjetas */
        .card-header {
            padding: 0.75rem 1rem;
        }

        .card-title {
            font-size: 1rem;
            margin: 0;
        }

        .badge {
            font-size: 0.75rem;
            white-space: nowrap;
        }

        /* Media queries para dispositivos móviles */
        @media (max-width: 768px) {
            /* Ajustes del formulario en móvil */
            .form-row > [class*='col-'] {
                margin-bottom: 0.5rem;
            }

            /* Ajustes de botones en móvil */
            .btn-toolbar {
                justify-content: center;
            }

            .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }

            /* Ajustes de tarjetas en móvil */
            .card-header {
                padding: 0.5rem;
            }

            .card-title {
                font-size: 0.9rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.25em 0.5em;
            }

            /* Ajustes de tabla en móvil */
            .table td, .table th {
                padding: 0.25rem;
                font-size: 0.8rem;
            }

            /* Ajustes de los iconos en móvil */
            .fas {
                font-size: 0.9rem;
            }

            /* Mejorar visualización de badges en móvil */
            .d-flex.align-items-center {
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .badge {
                margin: 0.1rem !important;
            }
        }

        /* Media queries para tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.85rem;
            }

            .card-title {
                font-size: 0.95rem;
            }

            .table td, .table th {
                padding: 0.4rem;
                font-size: 0.85rem;
            }
        }

        /* Ajustes para la búsqueda y filtros */
        #busquedaGlobal {
            max-width: 100%;
        }

        /* Ajustes para los campos editables */
        .editable .edit-input {
            width: 100%;
            min-width: 50px;
        }

        /* Asegurar que los menús desplegables sean responsivos */
        .dropdown-menu {
            max-width: 100%;
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-row mb-3">
                <div class="col-md-4">
                    <label for="filtroFecha">Seleccionar Fecha:</label>
                    <input type="month" name="fecha" class="form-control"
                           value="{{ request('fecha') ?? now()->format('Y-m') }}" />
                </div>
                <div class="col-md-6">
                    <label for="busqueda">Buscar Artículo:</label>
                    <input type="text" id="busquedaGlobal" class="form-control" placeholder="BUSCAR POR NÚMERO, CÓDIGO O LUGAR...">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary form-control" type="submit">Filtrar</button>
                </div>
            </form>

            <div class="btn-toolbar mb-3" role="toolbar">
                <div class="btn-group">
                    <button class="btn btn-dark" id="toggleAll">
                        <i class="fas fa-expand-arrows-alt"></i> EXPANDIR/CONTRAER TODO
                    </button>
                    <button class="btn btn-success" onclick="crearArticulo()">
                        <i class="fas fa-plus"></i> CREAR ARTÍCULO
                    </button>
                    <button class="btn btn-primary" onclick="actualizarArticulos()">
                        <i class="fas fa-sync"></i> ACTUALIZAR ARTÍCULOS
                    </button>
                    <button class="btn btn-warning" onclick="generarQR()">
                        <i class="fas fa-qrcode"></i> GENERAR QR
                    </button>
                    <button class="btn btn-info" onclick="añadirQR()">
                        <i class="fas fa-plus-circle"></i> AÑADIR CON QR
                    </button>
                    <button class="btn btn-secondary" onclick="historialMovimientos()">
                        <i class="fas fa-history"></i> HISTORIAL DE MOVIMIENTOS
                    </button>
                </div>
            </div>

            <div class="row">
                @php
                    $inventarioPorLugar = $inventario->groupBy('lugar');
                @endphp

                @foreach($inventarioPorLugar as $lugar => $itemsPorLugar)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary cursor-pointer" data-toggle="collapse" 
                                 data-target="#collapse{{ Str::slug($lugar) }}" aria-expanded="false">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title text-white mb-0">
                                        <i class="fas fa-warehouse"></i> {{ $lugar }}
                                    </h3>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $articulosAgotados = $itemsPorLugar->where('cantidad', 0)->count();
                                        @endphp

                                        <span class="badge badge-light mr-3">
                                            {{ $itemsPorLugar->count() }} artículos
                                        </span>
                                        <div class="badge bg-danger text-white mr-3">
                                            {{ $articulosAgotados }} agotados
                                        </div>
                                        <i class="fas fa-chevron-down text-white transition-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div id="collapse{{ Str::slug($lugar) }}" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                            $itemsPorColumna = $itemsPorLugar->groupBy('columna')->sortKeys();
                                        @endphp

                                        @foreach($itemsPorColumna as $columna => $items)
                                            <div class="col-md-6 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-header bg-secondary text-white">
                                                        <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-columns"></i> Columna {{ $columna }}
                                                            </div>
                                                            <div class="d-flex">
                                                                <div class="badge badge-light mr-2">
                                                                    {{ $items->count() }} artículos
                                                                </div>
                                                                <div class="badge badge-success mr-2">
                                                                    Total: {{ $items->sum('cantidad') }}
                                                                </div>
                                                                @php
                                                                    $articulosAgotadosColumna = $items->where('cantidad', 0)->count();
                                                                @endphp
                                                                <div class="badge bg-danger text-white">
                                                                    {{ $articulosAgotadosColumna }} agotados
                                                                </div>
                                                            </div>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <table class="table table-hover mb-0" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 10%">Número</th>
                                                                    <th style="width: 15%">Lugar</th>
                                                                    <th style="width: 10%">Columna</th>
                                                                    <th style="width: 35%">Código</th>
                                                                    <th style="width: 10%">Cantidad</th>
                                                                    @can('admin')
                                                                    <th style="width: 10%">Acciones</th>
                                                                    @endcan
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if(Str::startsWith(strtoupper($lugar), 'SOPORTE'))
                                                                    @for($n = 1; $n <= 14; $n++)
                                                                        @php
                                                                            $item = $items->firstWhere('numero', $n);
                                                                        @endphp
                                                                        <tr @if($item && $item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id ?? '' }}">
                                                                            <td class="editable text-center" data-field="numero">
                                                                                @if($item)
                                                                                    <span class="display-value">{{ $item->numero }}</span>
                                                                                    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->numero }}">
                                                                                @else
                                                                                    <span class="display-value">{{ $n }}</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="editable text-center" data-field="lugar">
                                                                                <span class="display-value">{{ $item->lugar ?? '-' }}</span>
                                                                                @if($item)
                                                                                    <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->lugar }}">
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="editable text-center" data-field="columna">
                                                                                <span class="display-value">{{ $item->columna ?? $columna }}</span>
                                                                                @if($item)
                                                                                    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->columna }}">
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="editable" data-field="codigo">
                                                                                <span class="display-value">{{ $item->codigo ?? '-' }}</span>
                                                                                @if($item)
                                                                                    <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->codigo }}">
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="editable text-center" data-field="cantidad">
                                                                                <span class="display-value">{{ $item->cantidad ?? '-' }}</span>
                                                                                @if($item)
                                                                                    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->cantidad }}">
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            @can('admin')
                                                                            <td class="text-center">
                                                                                @if($item)
                                                                                    <div class="btn-group">
                                                                                        <form action="{{ route('inventario.destroy', $item->id) }}" method="POST" class="d-inline">
                                                                                            @csrf
                                                                                            @method('DELETE')
                                                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                                                                    onclick="return confirm('¿Está seguro de que desea eliminar este artículo?')">
                                                                                                <i class="fa fa-trash"></i>
                                                                                            </button>
                                                                                        </form>
                                                                                    </div>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            @endcan
                                                                        </tr>
                                                                    @endfor
                                                                    {{-- Mostrar artículos con número mayor a 14 --}}
                                                                    @foreach($items->filter(function($itm){ return $itm->numero > 14; })->sortBy('numero') as $item)
                                                                        <tr @if($item->cantidad == 0) class="table-danger" @endif data-id="{{ $item->id }}">
                                                                            <td class="editable text-center" data-field="numero">
                                                                                <span class="display-value">{{ $item->numero }}</span>
                                                                                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->numero }}">
                                                                            </td>
                                                                            <td class="editable text-center" data-field="lugar">
                                                                                <span class="display-value">{{ $item->lugar }}</span>
                                                                                <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->lugar }}">
                                                                            </td>
                                                                            <td class="editable text-center" data-field="columna">
                                                                                <span class="display-value">{{ $item->columna }}</span>
                                                                                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->columna }}">
                                                                            </td>
                                                                            <td class="editable" data-field="codigo">
                                                                                <span class="display-value">{{ $item->codigo }}</span>
                                                                                <input type="text" class="form-control edit-input" style="display: none;" value="{{ $item->codigo }}">
                                                                            </td>
                                                                            <td class="editable text-center" data-field="cantidad">
                                                                                <span class="display-value">{{ $item->cantidad }}</span>
                                                                                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $item->cantidad }}">
                                                                            </td>
                                                                            @can('admin')
                                                                            <td class="text-center">
                                                                                <div class="btn-group">
                                                                                    <form action="{{ route('inventario.destroy', $item->id) }}" method="POST" class="d-inline">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                                                                onclick="return confirm('¿Está seguro de que desea eliminar este artículo?')">
                                                                                            <i class="fa fa-trash"></i>
                                                                                        </button>
                                                                                    </form>
                                                                                </div>
                                                                            </td>
                                                                            @endcan
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    @foreach($items->sortBy('numero') as $i)
                                                                        <tr @if($i->cantidad == 0) class="table-danger" @endif data-id="{{ $i->id }}">
                                                                            <td class="editable text-center" data-field="numero">
                                                                                @if($i)
                                                                                    <span class="display-value">{{ $i->numero }}</span>
                                                                                    <input type="number" class="form-control edit-input" style="display: none;" value="{{ $i->numero }}">
                                                                                @else
                                                                                    <span class="display-value">{{ $i->numero }}</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="editable text-center" data-field="lugar">
                                                                                <span class="display-value">{{ $i->lugar }}</span>
                                                                                <input type="text" class="form-control edit-input" style="display: none;" value="{{ $i->lugar }}">
                                                                            </td>
                                                                            <td class="editable text-center" data-field="columna">
                                                                                <span class="display-value">{{ $i->columna }}</span>
                                                                                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $i->columna }}">
                                                                            </td>
                                                                            <td class="editable" data-field="codigo">
                                                                                <span class="display-value">{{ $i->codigo }}</span>
                                                                                <input type="text" class="form-control edit-input" style="display: none;" value="{{ $i->codigo }}">
                                                                            </td>
                                                                            <td class="editable text-center" data-field="cantidad">
                                                                                <span class="display-value">{{ $i->cantidad }}</span>
                                                                                <input type="number" class="form-control edit-input" style="display: none;" value="{{ $i->cantidad }}">
                                                                            </td>
                                                                            @can('admin')
                                                                            <td class="text-center">
                                                                                <div class="btn-group">
                                                                                    <form action="{{ route('inventario.destroy', $i->id) }}" method="POST" class="d-inline">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                                                                onclick="return confirm('¿Está seguro de que desea eliminar este artículo?')">
                                                                                            <i class="fa fa-trash"></i>
                                                                                        </button>
                                                                                    </form>
                                                                                </div>
                                                                            </td>
                                                                            @endcan
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('js')
    @include('atajos')
    <script>
        $(document).ready(function() {
            // Manejar la rotación del icono en los headers de las tarjetas
            $('.card-header').on('click', function() {
                $(this).find('.transition-icon').toggleClass('fa-rotate-180');
            });

            // Inicializar DataTables con configuración responsiva
            $('.table').DataTable({
                dom: '<"row"<"col-12"f>>' +
                     '<"row"<"col-12"t>>',
                ordering: true,
                searching: true,
                paging: false,
                info: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                autoWidth: false,
                language: {
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    searchPlaceholder: "Buscar en esta columna..."
                },
                columnDefs: [
                    {
                        targets: 0, // Primera columna (NÚMERO)
                        type: 'num',
                    }
                ]
            });

            // Variable para controlar el estado de expansión
            let allExpanded = false;

            // Función para expandir/contraer todas las tarjetas
            $('#toggleAll').on('click', function() {
                allExpanded = !allExpanded;
                if (allExpanded) {
                    $('.collapse').collapse('show');
                    $('.transition-icon').addClass('fa-rotate-180');
                } else {
                    $('.collapse').collapse('hide');
                    $('.transition-icon').removeClass('fa-rotate-180');
                }
            });

            // Búsqueda global
            $('#busquedaGlobal').on('keyup', function() {
                let searchTerm = $(this).val().toLowerCase();
                $('.table').each(function() {
                    let table = $(this).DataTable();
                    table.search(searchTerm).draw();
                });
            });

            // Funciones de navegación
            window.crearArticulo = function() {
                window.location.href = "{{ route('inventario.create') }}";
            }

            window.actualizarArticulos = function() {
                window.location.href = "{{ route('inventario.actualizar') }}";
            }

            window.generarQR = function() {
                if (confirm('¿Está seguro que desea generar nuevos registros?')) {
                    window.location.href = "{{ route('generarQR') }}";
                }
            }

            window.añadirQR = function() {
                window.location.href = "{{ route('leerQR') }}";
            }

            window.historialMovimientos = function() {
                window.location.href = "{{ route('pedidos.inventario-historial') }}";
            }

            // Edición en línea
            $('.editable').on('click', function() {
                let currentValue = $(this).find('.display-value').text().trim();
                let field = $(this).data('field');
                let id = $(this).closest('tr').data('id');
                let input = $(this).find('.edit-input');
                let displayValue = $(this).find('.display-value');
                
                displayValue.hide();
                input.show().focus().val(currentValue);

                input.on('blur keypress', function(e) {
                    if (e.type === 'keypress' && e.which !== 13) return;
                    
                    let newValue = $(this).val();
                    if (newValue === currentValue) {
                        displayValue.show();
                        input.hide();
                        return;
                    }
                    
                    let cell = $(this).closest('.editable');
                    let row = cell.closest('tr');
                    
                    // Obtener los valores actuales de la fila
                    let data = {};
                    
                    try {
                        // Obtener y validar número
                        let numeroText = row.find('[data-field="numero"] .display-value').text().trim();
                        data.numero = parseInt(numeroText);
                        if (isNaN(data.numero)) throw new Error('El número debe ser un valor válido');
                        
                        // Obtener lugar y columna directamente de la fila
                        data.lugar = row.find('[data-field="lugar"] .display-value').text().trim();
                        if (!data.lugar) throw new Error('El lugar no puede estar vacío');
                        
                        data.columna = parseInt(row.find('[data-field="columna"] .display-value').text().trim());
                        if (isNaN(data.columna)) throw new Error('La columna debe ser un número válido');
                        
                        // Obtener y validar código
                        data.codigo = row.find('[data-field="codigo"] .display-value').text().trim();
                        if (!data.codigo) throw new Error('El código no puede estar vacío');
                        
                        // Obtener y validar cantidad
                        let cantidadText = row.find('[data-field="cantidad"] .display-value').text().trim();
                        data.cantidad = parseInt(cantidadText);
                        if (isNaN(data.cantidad)) throw new Error('La cantidad debe ser un número válido');
                        
                        // Actualizar el campo específico con el nuevo valor
                        if (field === 'numero') {
                            let newNum = parseInt(newValue);
                            if (isNaN(newNum) || newNum < 0) throw new Error('El número debe ser un valor válido y no negativo');
                            data.numero = newNum;
                        } else if (field === 'cantidad') {
                            let newCant = parseInt(newValue);
                            if (isNaN(newCant) || newCant < 0) throw new Error('La cantidad debe ser un valor válido y no negativo');
                            data.cantidad = newCant;
                        } else if (field === 'codigo') {
                            if (!newValue.trim()) throw new Error('El código no puede estar vacío');
                            data.codigo = newValue.trim();
                        } else if (field === 'lugar') {
                            if (!newValue.trim()) throw new Error('El lugar no puede estar vacío');
                            data.lugar = newValue.trim();
                        } else if (field === 'columna') {
                            let newCol = parseInt(newValue);
                            if (isNaN(newCol) || newCol < 0) throw new Error('La columna debe ser un valor válido y no negativo');
                            data.columna = newCol;
                        }
                        
                        // Log para debug
                        console.log('Datos a enviar:', data);
                        
                        // Mostrar indicador de carga
                        cell.addClass('bg-light');
                        
                        // Realizar la petición AJAX
                        $.ajax({
                            url: `/inventario/${id}/update-inline`,
                            method: 'POST',
                            data: data,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    displayValue.text(newValue).show();
                                    input.hide();
                                    
                                    // Actualizar el valor en la fila
                                    row.find(`[data-field="${field}"] .display-value`).text(newValue);
                                    
                                    // Actualizar clase de fila si la cantidad es 0
                                    if (field === 'cantidad') {
                                        if (parseInt(newValue) === 0) {
                                            row.addClass('table-danger');
                                        } else {
                                            row.removeClass('table-danger');
                                        }
                                    }
                                    
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Actualizado',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    displayValue.show();
                                    input.hide();
                                    
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                displayValue.show();
                                input.hide();
                                
                                let errorMsg = 'No se pudo actualizar el registro';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg
                                });
                            },
                            complete: function() {
                                cell.removeClass('bg-light');
                            }
                        });
                    } catch (error) {
                        displayValue.show();
                        input.hide();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: error.message
                        });
                    }
                });

                // Cancelar edición con Escape
                input.on('keyup', function(e) {
                    if (e.key === 'Escape') {
                        displayValue.show();
                        input.hide();
                    }
                });
            });
        });
    </script>
@stop

@section('css')
    <style>
        /* Estilos base */
        .table {
            width: 100% !important;
            margin-bottom: 0;
        }

        .table th,
        .table td {
            padding: 8px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-align: center;
        }

        /* Ajustes para las celdas editables */
        .editable {
            cursor: pointer;
            padding: 4px 8px;
        }

        .editable:hover {
            background-color: rgba(0,0,0,.075);
        }

        .editable .display-value {
            display: block;
            min-height: 24px;
            line-height: 24px;
        }

        .editable .edit-input {
            width: 100%;
            padding: 4px;
            height: 32px;
        }

        /* Ajustes para los botones */
        .btn-group {
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 14px;
        }

        /* Ajustes para el contenedor de la tabla */
        .card-body.p-0 {
            overflow-x: auto;
        }

        /* Ajustes para DataTables */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_filter {
            margin: 8px;
        }

        .table td, .table th {
            padding: 0.75rem;
            font-size: 0.95rem;
            vertical-align: middle;
        }
        
        .btn-xs {
            padding: 0.2rem 0.4rem;
        }
        
        .small-box {
            border-radius: 4px;
            position: relative;
            display: block;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        .small-box > .inner {
            padding: 10px;
        }

        .small-box h3 {
            font-size: 38px;
            font-weight: bold;
            margin: 0 0 10px 0;
            white-space: nowrap;
            padding: 0;
        }

        .small-box p {
            font-size: 15px;
            margin-bottom: 10px;
        }

        .small-box small {
            font-size: 12px;
            display: block;
            margin-top: 5px;
            color: rgba(0,0,0,0.7);
        }

        .small-box .icon {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 70px;
            color: rgba(0,0,0,0.15);
        }

        .editable {
            cursor: pointer;
        }
        
        .editable:hover {
            background-color: #f8f9fa;
        }
        
        .editable input {
            width: 100%;
            padding: 2px 5px;
        }
        
        .table-danger {
            background-color: #f8d7da;
        }
        
        .badge {
            font-size: 90%;
            padding: 0.5em 1em;
        }

        .card {
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header {
            padding: 1rem 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        .cursor-pointer {
            cursor: pointer;
        }
        
        .transition-icon {
            transition: transform 0.3s ease;
        }
        
        .collapse.show + .card-header .transition-icon {
            transform: rotate(180deg);
        }
        
        .card-header:hover {
            background-color: #0056b3 !important;
        }

        /* Estilos adicionales para las tablas */
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-sm td, .table-sm th {
            padding: 0.75rem;
        }

        /* Centrar contenido de celdas específicas */
        .table td.editable[data-field="numero"],
        .table td.editable[data-field="cantidad"] {
            text-align: center;
        }

        /* Mejorar visualización de botones de acción */
        .btn-group .btn-xs {
            margin: 0 2px;
        }

        /* Ajustar el badge en el encabezado de columna */
        .card-header .badge {
            background-color: white;
            color: #6c757d;
            font-weight: 600;
            min-width: 100px;
            text-align: center;
        }

        /* Ajustar el ancho de las columnas de la tabla */
        .inventario-table {
            width: 100%;
            table-layout: fixed;
        }

        /* Mejorar la visualización de las celdas editables */
        .editable .display-value {
            display: block;
            min-height: 24px;
            line-height: 24px;
        }

        /* Ajustar el padding del contenido de la tarjeta */
        .card-body {
            padding: 1rem;
        }

        /* Mejorar la visualización de las tablas en dispositivos móviles */
        @media (max-width: 768px) {
            .col-md-6 {
                padding: 0 5px;
            }
            
            .table td, .table th {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }

        /* Ajustes para la tabla */
        .table-responsive {
            overflow-x: auto;
            margin: 0;
            padding: 0;
            border: none;
        }

        .inventario-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .inventario-table th,
        .inventario-table td {
            padding: 8px 12px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        .inventario-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
        }

        .inventario-table td.editable[data-field="numero"] {
            width: 100px;
            text-align: center;
        }

        .inventario-table td.editable[data-field="codigo"] {
            min-width: 250px;
        }

        .inventario-table td.editable[data-field="cantidad"] {
            width: 100px;
            text-align: center;
        }

        /* Ajustes para las acciones */
        .inventario-table td:last-child {
            width: 120px;
            text-align: center;
            white-space: nowrap;
        }

        /* Ajustes para el contenedor de la tabla */
        .card-body.p-0 {
            padding: 0 !important;
            margin: 0;
        }

        /* Ajustes para las celdas editables */
        .editable .display-value {
            display: block;
            width: 100%;
            text-align: inherit;
        }

        .editable .edit-input {
            width: 100%;
            padding: 4px 8px;
            text-align: inherit;
        }

        /* Ajustes para los botones de acción */
        .btn-group {
            display: inline-flex;
            gap: 4px;
        }

        .btn-xs {
            padding: 2px 6px;
            font-size: 0.875rem;
        }
    </style>
@stop

@section('footer')
    
@stop
