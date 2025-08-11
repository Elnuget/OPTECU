@extends('adminlte::page')

@section('title', 'Agregar venta')

@section('content_header')
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong>{{ session('error') }}</strong> {{ session('mensaje') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('content')
    {{-- Meta tag para CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Convertir todo el texto a mayúsculas */
        .card-title,
        .card-header h3,
        .form-label,
        label,
        .list-group-item,
        .input-group-text,
        .custom-select option,
        .btn,
        input::placeholder,
        select option,
        .text-muted,
        strong,
        p,
        h1, h2, h3, h4, h5, h6 {
            text-transform: uppercase !important;
        }

        /* Estilos para hacer clickeable el header completo */
        .card-header {
            cursor: pointer;
        }
        .card-header:hover {
            background-color: rgba(0,0,0,.03);
        }

        /* Convertir inputs a mayúsculas */
        input[type="text"],
        input[type="email"],
        input[type="number"],
        textarea,
        select,
        .form-control {
            text-transform: uppercase !important;
        }

        /* Asegurar que los placeholders también estén en mayúsculas */
        input::placeholder,
        textarea::placeholder {
            text-transform: uppercase !important;
        }

        /* Asegurar que las opciones de datalist estén en mayúsculas */
        datalist option {
            text-transform: uppercase !important;
        }

        /* Asegurar que las opciones del selectpicker estén en mayúsculas */
        .bootstrap-select .dropdown-menu li a {
            text-transform: uppercase !important;
        }

        /* Mejorar el estilo de los datalist */
        input[list]::-webkit-calendar-picker-indicator {
            display: none !important;
        }
        
        input[list] {
            position: relative;
        }
        
        /* Agregar un indicador visual para campos con autocompletado */
        input[list]::after {
            content: '▼';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
        }
        
        /* Estilos para el foco en inputs con datalist */
        input[list]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>

    {{-- Mostrar mensajes de error --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mostrar mensajes de error específicos de la base de datos --}}
    @if (session('db_error'))
        <div class="alert alert-danger">
            {{ session('db_error') }}
        </div>
    @endif

    <br>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Añadir Pedido</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip"
                    title="Ocultar/Mostrar">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="col-md-12">
                <form action="{{ route('pedidos.store') }}" method="POST" id="pedidoForm">
                    @csrf

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
                            {{-- Fila 1 --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"
                                           value="{{ old('fecha', $currentDate) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="numero_orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="numero_orden" name="numero_orden"
                                           value="{{ old('numero_orden', $nextOrderNumber) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Datos Personales --}}
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Datos Personales</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Fila 2 --}}
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label for="buscar_cliente_paciente" class="form-label">Buscar Cliente/Paciente</label>
                                    <input type="text" class="form-control" id="buscar_cliente_paciente" 
                                           placeholder="Escriba para buscar un cliente o paciente existente" 
                                           list="clientes_pacientes_list">
                                    <datalist id="clientes_pacientes_list">
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente }}" data-tipo="cliente">
                                        @endforeach
                                        @foreach($pacientes as $paciente)
                                            <option value="{{ $paciente }}" data-tipo="paciente">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label for="fact" class="form-label">Factura</label>
                                    <input type="text" class="form-control" id="fact" name="fact"
                                           value="Pendiente">
                                </div>
                                <div class="col-md-6">
                                    <label for="empresa_id" class="form-label">Empresa *</label>
                                    <select class="form-control" id="empresa_id" name="empresa_id" required>
                                        <option value="">Seleccione una empresa</option>
                                        @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Fila para Cliente --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" required>
                                </div>
                            </div>

                            {{-- Nueva fila para cédula --}}
                            <div class="row mb-3">                                <div class="col-md-6">
                                    <label for="cedula" class="form-label">Cédula</label>
                                    <input type="text" class="form-control" id="cedula" name="cedula" list="cedulas_existentes" placeholder="Seleccione o escriba una cédula" autocomplete="off">
                                    <datalist id="cedulas_existentes">
                                        @foreach($cedulas as $cedula)
                                            <option value="{{ $cedula }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label for="paciente" class="form-label">Paciente</label>
                                    <input type="text" class="form-control" id="paciente" name="paciente">
                                </div>
                            </div>

                            {{-- Fila 3 --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="examen_visual" class="form-label">Examen Visual</label>
                                    <input type="number" class="form-control form-control-sm" id="examen_visual" name="examen_visual" step="0.01" oninput="calculateTotal()">
                                </div>                                <div class="col-md-3">
                                    <label for="celular" class="form-label">Celular</label>
                                    <input type="text" class="form-control" id="celular" name="celular" placeholder="Escriba el número de celular" autocomplete="off">
                                </div>                                <div class="col-md-6">
                                    <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" placeholder="Escriba el correo electrónico" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Armazón --}}
                    <div id="armazon-container" class="card collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">Armazón</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label>Armazón (Inventario)</label>
                                    <select class="form-control selectpicker" data-live-search="true" name="a_inventario_id[]">
                                        <option value="">Seleccione un armazón</option>
                                        @foreach($armazones as $armazon)
                                            <option value="{{ $armazon->id }}">
                                                {{ $armazon->codigo }} - {{ $armazon->lugar }} - N°{{ $armazon->numero }} - {{ $armazon->fecha ? \Carbon\Carbon::parse($armazon->fecha)->format('d/m/Y') : 'Sin fecha' }} - {{ $armazon->empresa->nombre ?? 'Sin empresa' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Fila 5 --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="a_precio" class="form-label">Precio Armazón</label>
                                    <input type="number" class="form-control form-control-sm precio-armazon" id="a_precio" name="a_precio[]" step="0.01" oninput="calculateTotal()">
                                </div>
                                <div class="col-md-3">
                                    <label for="a_precio_descuento" class="form-label">Desc. Armazón (%)</label>
                                    <input type="number" class="form-control form-control-sm descuento-armazon" id="a_precio_descuento"
                                           name="a_precio_descuento[]" min="0" max="100" value="0" oninput="calculateTotal()">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-success" onclick="duplicateArmazon()">Agregar más Armazón</button>
                        </div>
                    </div>

                    {{-- Lunas --}}
                    <div id="lunas-container" class="card collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">Lunas</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Fila 6 --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="l_medida" class="form-label">Lunas Medidas</label>
                                    <input type="text" class="form-control" id="l_medida" name="l_medida[]">
                                </div>
                                <div class="col-md-6">
                                    <label for="l_detalle" class="form-label">Lunas Detalle</label>
                                    <input type="text" class="form-control" id="l_detalle" name="l_detalle[]">
                                </div>
                            </div>
                            {{-- Fila nueva para tipo de lente, material y filtro --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="tipo_lente" class="form-label">Tipo de Lente</label>
                                    <input type="text" class="form-control" id="tipo_lente" name="tipo_lente[]" list="tipo_lente_options" placeholder="Seleccione o escriba un tipo de lente">
                                    <datalist id="tipo_lente_options">
                                        <option value="Monofocal">
                                        <option value="Bifocal">
                                        <option value="Progresivo">
                                        <option value="Ocupacional">
                                        <option value="Contacto">
                                    </datalist>
                                </div>
                                <div class="col-md-4">
                                    <label for="material" class="form-label">Material</label>
                                    <input type="text" class="form-control" id="material" name="material[]" list="material_options" placeholder="Seleccione o escriba un material">
                                    <datalist id="material_options">
                                        <option value="Policarbonato">
                                        <option value="CR-39">
                                        <option value="Cristal">
                                        <option value="1.56">
                                        <option value="1.61">
                                        <option value="1.67">
                                        <option value="1.74">
                                        <option value="GX7">
                                        <option value="Crizal">
                                    </datalist>
                                </div>
                                <div class="col-md-4">
                                    <label for="filtro" class="form-label">Filtro</label>
                                    <input type="text" class="form-control" id="filtro" name="filtro[]" list="filtro_options" placeholder="Seleccione o escriba un filtro">
                                    <datalist id="filtro_options">
                                        <option value="Antireflejo">
                                        <option value="UV">
                                        <option value="Filtro azul AR verde">
                                        <option value="Filtro azul AR azul">
                                        <option value="Fotocromatico">
                                        <option value="Blancas">
                                        <option value="Fotocromatico AR">
                                        <option value="Fotocromatico filtro azul">
                                        <option value="Fotocromatico a colores">
                                        <option value="Tinturado">
                                        <option value="Polarizado">
                                        <option value="Transitions">
                                    </datalist>
                                </div>
                            </div>
                            {{-- Fila nueva para precio y descuento de lunas --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Precio Lunas</label>
                                    <input type="number" class="form-control input-sm" name="l_precio[]" step="0.01" oninput="calculateTotal()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Desc. Lunas (%)</label>
                                    <input type="number" class="form-control input-sm" name="l_precio_descuento[]" min="0" max="100" value="0" oninput="calculateTotal()">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-success" onclick="duplicateLunas()">Agregar más Lunas</button>
                        </div>
                    </div>

                    {{-- Accesorios --}}
                    <div id="accesorios-container" class="card collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">Accesorios</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Fila 7 --}}
                            <div class="row mb-3 accesorio-item">
                                <div class="col-md-6">
                                    <label for="d_inventario_id[]" class="form-label">Accesorio (Inventario)</label>
                                    <select class="form-control selectpicker" data-live-search="true" id="d_inventario_id[]" name="d_inventario_id[]">
                                        <option value="">Seleccione un Item del Inventario</option>
                                        @foreach ($accesorios as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->codigo }} - {{ $item->lugar }} - N°{{ $item->numero }} - {{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha' }} - {{ $item->empresa->nombre ?? 'Sin empresa' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="d_precio[]" class="form-label">Precio Accesorio</label>
                                    <input type="number" class="form-control input-sm" id="d_precio[]" name="d_precio[]" step="0.01" oninput="calculateTotal()">
                                </div>
                                <div class="col-md-3">
                                    <label for="d_precio_descuento[]" class="form-label">Desc. Accesorio (%)</label>
                                    <input type="number" class="form-control input-sm" id="d_precio_descuento[]" name="d_precio_descuento[]" min="0" max="100" value="0" oninput="calculateTotal()">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-success" onclick="duplicateAccesorios()">Agregar más Accesorios</button>
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
                            {{-- Nueva fila para compra rápida --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="valor_compra" class="form-label">Valor de Compra</label>
                                    <input type="number" class="form-control input-sm" id="valor_compra" name="valor_compra" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label for="motivo_compra" class="form-label">Motivo de Compra</label>
                                    <input type="text" class="form-control" id="motivo_compra" name="motivo_compra" 
                                           list="motivo_compra_options" placeholder="Seleccione o escriba un motivo">
                                    <datalist id="motivo_compra_options">
                                        <option value="Líquidos">
                                        <option value="Accesorios">
                                        <option value="Estuches">
                                        <option value="Otros">
                                    </datalist>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total y Botones --}}
                    <div class="card">
                        <div class="card-body">
                            {{-- Fila Total --}}
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="total" class="form-label" style="color: red;">Total</label>
                                    <input type="number" class="form-control input-sm" id="total" name="total" step="0.01" readonly>
                                </div>
                            </div>

                            {{-- Fila oculta (Saldo) --}}
                            <div class="row mb-3" style="display: none;">
                                <div class="col-md-12">
                                    <label for="saldo" class="form-label">Saldo</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="saldo" 
                                           name="saldo" 
                                           value="0"
                                           step="0.01"
                                           required>
                                </div>
                            </div>

                            {{-- Botones y Modal --}}
                            <div class="d-flex justify-content-start">
                                <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#modal">
                                    Crear pedido
                                </button>
                                <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">
                                    Cancelar
                                </a>
                            </div>

                            <div class="modal fade" id="modal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Crear pedido</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro que desea crear el pedido?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default pull-left"
                                                    data-dismiss="modal">Cancelar
                                            </button>
                                            <button type="submit" class="btn btn-primary">Crear pedido</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /.card-body -->

        <div class="card-footer">
            Añadir Pedido
        </div>
        <!-- /.card-footer-->
    </div>

@stop

@section('js')
    <script>
        // Datos de inventario para uso en JavaScript
        window.inventarioData = {
            armazones: [
                @foreach($armazones as $item)
                {
                    id: {{ $item->id }},
                    empresa_id: {{ $item->empresa_id ?? 'null' }},
                    display: "{!! addslashes($item->codigo . ' - ' . $item->lugar . ' - N°' . $item->numero . ' - ' . ($item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha') . ' - ' . ($item->empresa->nombre ?? 'Sin empresa')) !!}"
                }@if(!$loop->last),@endif
                @endforeach
            ],
            accesorios: [
                @foreach($accesorios as $item)
                {
                    id: {{ $item->id }},
                    empresa_id: {{ $item->empresa_id ?? 'null' }},
                    display: "{!! addslashes($item->codigo . ' - ' . $item->lugar . ' - N°' . $item->numero . ' - ' . ($item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha') . ' - ' . ($item->empresa->nombre ?? 'Sin empresa')) !!}"
                }@if(!$loop->last),@endif
                @endforeach
            ]
        };
        
        // Datos de empresas para filtrado
        window.empresasData = @json($empresas);
    </script>
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

            // Preseleccionar la sucursal activa desde localStorage
            if (typeof SucursalCache !== 'undefined') {
                SucursalCache.preseleccionarEnSelect('empresa_id', false);
                
                // Después de preseleccionar, obtener el valor y actualizar número de orden
                setTimeout(() => {
                    const empresaPreseleccionadaCache = document.getElementById('empresa_id').value;
                    if (empresaPreseleccionadaCache) {
                        actualizarNumeroOrden(empresaPreseleccionadaCache);
                        filtrarInventarioPorEmpresa(empresaPreseleccionadaCache);
                    }
                }, 200);
            }

            // Manejar búsqueda en selectpicker para respetar filtro por empresa
            $(document).on('shown.bs.select', '.selectpicker', function(e) {
                const selectElement = e.target;
                if (selectElement.name.includes('inventario_id')) {
                    aplicarFiltroEmpresaEnSelectpicker(selectElement);
                }
            });

            // Manejar cambio de empresa para filtrar inventario y actualizar número de orden
            document.getElementById('empresa_id').addEventListener('change', function() {
                const empresaId = this.value;
                
                // Filtrar inventario
                filtrarInventarioPorEmpresa(empresaId);
                
                // Actualizar número de orden si hay empresa seleccionada
                if (empresaId) {
                    actualizarNumeroOrden(empresaId);
                }
            });

            // Inicializar filtro si hay empresa preseleccionada
            const empresaPreseleccionada = document.getElementById('empresa_id').value;
            if (empresaPreseleccionada) {
                setTimeout(() => {
                    filtrarInventarioPorEmpresa(empresaPreseleccionada);
                    actualizarNumeroOrden(empresaPreseleccionada);
                }, 100);
            }

            // Manejar la búsqueda unificada de cliente/paciente
            document.getElementById('buscar_cliente_paciente').addEventListener('change', function() {
                const selectedOption = document.querySelector(`#clientes_pacientes_list option[value="${this.value}"]`);
                if (selectedOption) {
                    const tipo = selectedOption.dataset.tipo;
                    const valor = this.value;

                    if (tipo === 'cliente') {
                        document.getElementById('cliente').value = valor;
                        cargarDatosPersonales('cliente', valor);
                    } else if (tipo === 'paciente') {
                        document.getElementById('paciente').value = valor;
                        cargarDatosPersonales('paciente', valor);
                    }
                }
            });            // Autocompletado eliminado para cédula, celular y correo_electronico
            // document.getElementById('cedula').addEventListener('change', function() {
            //     if (this.value.trim()) {
            //         cargarDatosPersonales('cedula', this.value);
            //     }
            // });

            // Los campos celular y correo_electronico ya NO tendrán autocompletado automático
            // document.getElementById('celular').addEventListener('change', function() {
            //     if (this.value.trim()) {
            //         cargarDatosPersonales('celular', this.value);
            //     }
            // });

            // document.getElementById('correo_electronico').addEventListener('change', function() {
            //     if (this.value.trim()) {
            //         cargarDatosPersonales('correo', this.value);
            //     }
            // });

            // Función para cargar datos personales según el campo proporcionado
            function cargarDatosPersonales(campo, valor) {
                if (!valor) return;

                // Mostrar indicador de carga
                const elemento = document.getElementById(campo === 'correo' ? 'correo_electronico' : campo);
                if (!elemento.nextElementSibling || !elemento.nextElementSibling.classList.contains('loading-indicator')) {
                    const loadingIndicator = document.createElement('small');
                    loadingIndicator.classList.add('loading-indicator', 'text-muted', 'ml-2');
                    loadingIndicator.textContent = 'Cargando datos...';
                    elemento.parentNode.appendChild(loadingIndicator);
                }

                // Hacer petición AJAX para obtener datos del último pedido
                fetch(`/api/pedidos/buscar-por/${campo}/${encodeURIComponent(valor)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al obtener datos');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Remover indicador de carga
                        const loadingIndicator = elemento.parentNode.querySelector('.loading-indicator');
                        if (loadingIndicator) {
                            loadingIndicator.remove();
                        }

                        if (data.success && data.pedido) {
                            // Autocompletar campos excepto el que generó la búsqueda
                            if (campo !== 'cliente') {
                                document.getElementById('cliente').value = data.pedido.cliente || '';
                            }
                            if (campo !== 'cedula') {
                                document.getElementById('cedula').value = data.pedido.cedula || '';
                            }
                            if (campo !== 'paciente') {
                                document.getElementById('paciente').value = data.pedido.paciente || '';
                            }
                            if (campo !== 'celular') {
                                document.getElementById('celular').value = data.pedido.celular || '';
                            }
                            if (campo !== 'correo') {
                                document.getElementById('correo_electronico').value = data.pedido.correo_electronico || '';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Remover indicador de carga en caso de error
                        const loadingIndicator = elemento.parentNode.querySelector('.loading-indicator');
                        if (loadingIndicator) {
                            loadingIndicator.remove();
                        }
                    });
            }
        });

        function calculateTotal() {
            let total = 0;

            // Examen visual
            const examenVisual = parseFloat(document.getElementById('examen_visual').value) || 0;
            total += examenVisual;

            // Armazones - incluir tanto el original como los campos añadidos
            const armazonPrecios = document.querySelectorAll('[name="a_precio"], [name="a_precio[]"]');
            const armazonDescuentos = document.querySelectorAll('[name="a_precio_descuento"], [name="a_precio_descuento[]"]');
            armazonPrecios.forEach((precio, index) => {
                const precioValue = parseFloat(precio.value) || 0;
                const descuento = parseFloat(armazonDescuentos[index]?.value) || 0;
                total += precioValue * (1 - (descuento / 100));
            });

            // Lunas - incluir tanto el original como los campos añadidos
            const lunasPrecios = document.querySelectorAll('[name="l_precio"], [name="l_precio[]"]');
            const lunasDescuentos = document.querySelectorAll('[name="l_precio_descuento"], [name="l_precio_descuento[]"]');
            lunasPrecios.forEach((precio, index) => {
                const precioValue = parseFloat(precio.value) || 0;
                const descuento = parseFloat(lunasDescuentos[index]?.value) || 0;
                total += precioValue * (1 - (descuento / 100));
            });

            // Accesorios - incluir tanto el original como los campos añadidos
            const accesoriosPrecios = document.querySelectorAll('[name="d_precio"], [name="d_precio[]"]');
            const accesoriosDescuentos = document.querySelectorAll('[name="d_precio_descuento"], [name="d_precio_descuento[]"]');
            accesoriosPrecios.forEach((precio, index) => {
                const precioValue = parseFloat(precio.value) || 0;
                const descuento = parseFloat(accesoriosDescuentos[index]?.value) || 0;
                total += precioValue * (1 - (descuento / 100));
            });

            // Valor compra
            const valorCompra = parseFloat(document.getElementById('valor_compra').value) || 0;
            total += valorCompra;

            // Actualizar campos
            document.getElementById('total').value = total.toFixed(2);
            document.getElementById('saldo').value = total.toFixed(2);
        }

        // Event listeners para precios
        ['examen_visual', 'a_precio', 'l_precio', 'd_precio', 'valor_compra'].forEach(id => { // Añadir valor_compra
            const element = document.getElementById(id);
            if(element){
                element.addEventListener('input', calculateTotal);
            }
        });

        // Event listeners para descuentos
        ['a_precio_descuento', 'l_precio_descuento', 'd_precio_descuento'].forEach(id => {
            const element = document.getElementById(id);
            if(element){
                element.addEventListener('input', calculateTotal);
            }
        });

        // Mejorar el comportamiento de los inputs con datalist
        document.querySelectorAll('input[list]').forEach(input => {
            // Mostrar opciones al hacer clic
            input.addEventListener('click', function() {
                this.value = '';
                this.focus();
                // Simular tecla hacia abajo para abrir el datalist
                const event = new KeyboardEvent('keydown', {
                    key: 'ArrowDown',
                    code: 'ArrowDown',
                    keyCode: 40,
                    which: 40
                });
                this.dispatchEvent(event);
            });
            
            // Mantener el foco por más tiempo
            input.addEventListener('focus', function() {
                setTimeout(() => {
                    if (this.value === '') {
                        const event = new KeyboardEvent('keydown', {
                            key: 'ArrowDown',
                            code: 'ArrowDown',
                            keyCode: 40,
                            which: 40
                        });
                        this.dispatchEvent(event);
                    }
                }, 100);
            });
            
            // Filtrar opciones mientras se escribe
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    // El datalist se filtrará automáticamente
                    setTimeout(() => {
                        const event = new KeyboardEvent('keydown', {
                            key: 'ArrowDown',
                            code: 'ArrowDown',
                            keyCode: 40,
                            which: 40
                        });
                        this.dispatchEvent(event);
                    }, 50);
                }
            });
        });

        function createNewFields(type) {
            let html = '';
            const index = document.querySelectorAll(`[data-${type}-section]`).length;
            
            if (type === 'armazon') {
                // Usar datos filtrados si están disponibles
                const datosArmazones = window.inventarioData.armazonesFiltrados || window.inventarioData.armazones;
                
                // Generar opciones desde los datos de JavaScript
                let armazonOptions = '<option value="">Seleccione un armazón</option>';
                datosArmazones.forEach(item => {
                    armazonOptions += `<option value="${item.id}">${item.display}</option>`;
                });
                
                html = `
                    <div data-armazon-section class="mt-4">
                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove(); calculateTotal();">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Armazón (Inventario)</label>
                                <select class="form-control selectpicker" data-live-search="true" name="a_inventario_id[]">
                                    ${armazonOptions}
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Precio Armazón</label>
                                <input type="number" class="form-control form-control-sm precio-armazon" name="a_precio[]" step="0.01" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Desc. Armazón (%)</label>
                                <input type="number" class="form-control form-control-sm descuento-armazon" name="a_precio_descuento[]" min="0" max="100" value="0" oninput="calculateTotal()">
                            </div>
                        </div>
                    </div>
                `;
            }
            else if (type === 'lunas') {
                html = `
                    <div data-lunas-section class="mt-4">
                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove(); calculateTotal();">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Lunas Medidas</label>
                                <input type="text" class="form-control" name="l_medida[]" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lunas Detalle</label>
                                <input type="text" class="form-control" name="l_detalle[]" oninput="calculateTotal()">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Lente</label>
                                <input type="text" class="form-control" name="tipo_lente[]" list="tipo_lente_options" 
                                       placeholder="Seleccione o escriba un tipo de lente" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Material</label>
                                <input type="text" class="form-control" name="material[]" list="material_options"
                                       placeholder="Seleccione o escriba un material" oninput="calculateTotal()">
                            </div>
                            <div class="col-md.4">
                                <label class="form-label">Filtro</label>
                                <input type="text" class="form-control" name="filtro[]" list="filtro_options"
                                       placeholder="Seleccione o escriba un filtro" oninput="calculateTotal()">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Precio Lunas</label>
                                <input type="number" class="form-control input-sm" name="l_precio[]" step="0.01" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Desc. Lunas (%)</label>
                                <input type="number" class="form-control input-sm" name="l_precio_descuento[]" 
                                       min="0" max="100" value="0" oninput="calculateTotal()">
                            </div>
                        </div>
                    </div>
                `;
            }
            else if (type === 'accesorios') {
                // Usar datos filtrados si están disponibles
                const datosAccesorios = window.inventarioData.accesoriosFiltrados || window.inventarioData.accesorios;
                
                // Generar opciones desde los datos de JavaScript
                let accesorioOptions = '<option value="" selected>Seleccione un Item del Inventario</option>';
                datosAccesorios.forEach(item => {
                    accesorioOptions += `<option value="${item.id}">${item.display}</option>`;
                });
                
                html = `
                    <div data-accesorios-section class="mt-4">
                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove(); calculateTotal();">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Accesorio (Inventario)</label>
                                <select class="form-control selectpicker" data-live-search="true" name="d_inventario_id[]">
                                    ${accesorioOptions}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Precio Accesorio</label>
                                <input type="number" class="form-control input-sm" name="d_precio[]" step="0.01" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Desc. Accesorio (%)</label>
                                <input type="number" class="form-control input-sm" name="d_precio_descuento[]" min="0" max="100" value="0" oninput="calculateTotal()">
                            </div>
                        </div>
                    </div>
                `;
            }

            const container = document.querySelector(`#${type}-container .card-body`);
            container.insertAdjacentHTML('beforeend', html);

            // Agregar event listeners a los nuevos campos
            if (type === 'armazon') {
                const newSection = container.lastElementChild;
                const newPrecioInput = newSection.querySelector('.precio-armazon');
                const newDescuentoInput = newSection.querySelector('.descuento-armazon');
                
                newPrecioInput.addEventListener('input', calculateTotal);
                newDescuentoInput.addEventListener('input', calculateTotal);
            }
            $('.selectpicker').selectpicker('refresh'); // Reevaluar el nuevo select
        }

        function duplicateArmazon() {
            createNewFields('armazon');
        }

        function duplicateLunas() {
            createNewFields('lunas');
            calculateTotal(); // recalcular total al agregar más lunas
        }

        function duplicateAccesorios() {
            createNewFields('accesorios');
            calculateTotal(); // recalcular total con el nuevo accesorio
        }

        // Función para filtrar inventario por empresa
        function filtrarInventarioPorEmpresa(empresaId) {
            console.log('Filtrando inventario por empresa:', empresaId);
            
            // Filtrar armazones
            const armazonesFiltrados = window.inventarioData.armazones.filter(item => {
                if (!empresaId) return true; // Si no hay empresa seleccionada, mostrar todos
                
                // Extraer el nombre de la empresa del display
                const partes = item.display.split(' - ');
                const empresaItem = partes[partes.length - 1]; // La empresa es la última parte
                
                // Buscar la empresa por ID en la lista de empresas
                const empresaSeleccionada = @json($empresas).find(emp => emp.id == empresaId);
                const nombreEmpresaSeleccionada = empresaSeleccionada ? empresaSeleccionada.nombre : '';
                
                return empresaItem === nombreEmpresaSeleccionada;
            });
            
            // Filtrar accesorios
            const accesoriosFiltrados = window.inventarioData.accesorios.filter(item => {
                if (!empresaId) return true; // Si no hay empresa seleccionada, mostrar todos
                
                // Extraer el nombre de la empresa del display
                const partes = item.display.split(' - ');
                const empresaItem = partes[partes.length - 1]; // La empresa es la última parte
                
                // Buscar la empresa por ID en la lista de empresas
                const empresaSeleccionada = @json($empresas).find(emp => emp.id == empresaId);
                const nombreEmpresaSeleccionada = empresaSeleccionada ? empresaSeleccionada.nombre : '';
                
                return empresaItem === nombreEmpresaSeleccionada;
            });
            
            console.log('Armazones filtrados:', armazonesFiltrados.length);
            console.log('Accesorios filtrados:', accesoriosFiltrados.length);
            
            // Actualizar selects de armazones existentes
            document.querySelectorAll('select[name="a_inventario_id[]"]').forEach(select => {
                actualizarSelectConDatos(select, armazonesFiltrados, 'Seleccione un armazón');
            });
            
            // Actualizar selects de accesorios existentes
            document.querySelectorAll('select[name="d_inventario_id[]"]').forEach(select => {
                actualizarSelectConDatos(select, accesoriosFiltrados, 'Seleccione un Item del Inventario');
            });
            
            // Actualizar los datos globales para futuras adiciones
            window.inventarioData.armazonesFiltrados = armazonesFiltrados;
            window.inventarioData.accesoriosFiltrados = accesoriosFiltrados;
        }
        
        // Función auxiliar para actualizar un select con datos filtrados
        function actualizarSelectConDatos(select, datos, placeholder) {
            const valorActual = select.value;
            
            // Limpiar opciones actuales
            select.innerHTML = `<option value="">${placeholder}</option>`;
            
            // Agregar opciones filtradas
            datos.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.display;
                if (item.id == valorActual) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Refrescar selectpicker si existe
            if ($(select).hasClass('selectpicker')) {
                $(select).selectpicker('refresh');
            }
        }

        // Función para actualizar el número de orden basado en la empresa seleccionada
        async function actualizarNumeroOrden(empresaId) {
            const numeroOrdenInput = document.getElementById('numero_orden');
            if (!numeroOrdenInput) {
                console.error('Campo numero_orden no encontrado');
                return;
            }

            try {
                // Mostrar indicador de carga
                numeroOrdenInput.style.backgroundColor = '#fff3cd';
                numeroOrdenInput.style.border = '1px solid #ffeaa7';
                numeroOrdenInput.disabled = true;

                // Realizar petición AJAX
                const response = await fetch(`/api/pedidos/siguiente-numero-orden/${empresaId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Actualizar el campo con el siguiente número de orden
                    numeroOrdenInput.value = data.data.siguiente_numero_orden;
                    
                    // Mostrar indicador de éxito
                    numeroOrdenInput.style.backgroundColor = '#d4edda';
                    numeroOrdenInput.style.border = '1px solid #c3e6cb';
                    
                    console.log(`Número de orden actualizado: ${data.data.siguiente_numero_orden} (empresa: ${empresaId})`);
                    
                    // Remover indicadores después de 2 segundos
                    setTimeout(() => {
                        numeroOrdenInput.style.backgroundColor = '';
                        numeroOrdenInput.style.border = '';
                        numeroOrdenInput.disabled = false;
                    }, 2000);
                    
                } else {
                    throw new Error(data.message || 'Error al obtener número de orden');
                }

            } catch (error) {
                console.error('Error al actualizar número de orden:', error);
                
                // Mostrar indicador de error
                numeroOrdenInput.style.backgroundColor = '#f8d7da';
                numeroOrdenInput.style.border = '1px solid #f5c6cb';
                numeroOrdenInput.disabled = false;
                
                // Mostrar mensaje de error al usuario
                alert(`Error al obtener el número de orden: ${error.message}`);
                
                // Remover indicador de error después de 3 segundos
                setTimeout(() => {
                    numeroOrdenInput.style.backgroundColor = '';
                    numeroOrdenInput.style.border = '';
                }, 3000);
            }
        }

        // Función para aplicar filtro de empresa en selectpicker
        function aplicarFiltroEmpresaEnSelectpicker(selectElement) {
            const empresaId = document.getElementById('empresa_id').value;
            if (!empresaId) return;
            
            // Obtener la empresa seleccionada
            const empresaSeleccionada = @json($empresas).find(emp => emp.id == empresaId);
            if (!empresaSeleccionada) return;
            
            const nombreEmpresaSeleccionada = empresaSeleccionada.nombre;
            
            // Obtener el dropdown del selectpicker
            const $selectpicker = $(selectElement);
            const dropdownMenu = $selectpicker.next('.bootstrap-select').find('.dropdown-menu');
            
            // Filtrar opciones visualmente en el dropdown
            dropdownMenu.find('li').each(function() {
                const texto = $(this).find('a .text').text() || $(this).find('a').text();
                
                if (texto.includes(nombreEmpresaSeleccionada) || texto === 'Seleccione una opción' || texto === 'Seleccione un armazón' || texto === 'Seleccione un Item del Inventario') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(function() {
            $('.selectpicker').selectpicker();
        });
    </script>
@stop
