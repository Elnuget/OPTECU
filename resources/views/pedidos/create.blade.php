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

        /* Mejorar la experiencia de los datalist - SIMPLIFICADO */
        input[list]:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
            border-color: #007bff !important;
        }

        /* Estilos para múltiples filtros */
        .filtros-container {
            max-height: 150px;
            overflow-y: auto;
        }
        
        .filtro-item {
            margin-bottom: 0.5rem;
        }
        
        .filtro-item:last-child {
            margin-bottom: 0;
        }
        
        .agregar-filtro, .eliminar-filtro {
            min-width: 35px;
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
                <form action="{{ route('pedidos.store') }}" method="POST" id="pedidoForm" enctype="multipart/form-data">
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
                                    <small class="form-text text-muted">
                                        <i class="fas fa-sync-alt"></i> Se actualiza automáticamente cada segundo
                                    </small>
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
                                    <label for="buscar_historial_clinico" class="form-label">Buscar Historial Clínico</label>
                                    <select class="form-control selectpicker" data-live-search="true" id="buscar_historial_clinico" data-size="10">
                                        <option value="">Seleccione un paciente del historial clínico</option>
                                        {{-- Solo Historial Clínico - Últimos registros únicos --}}
                                        @if(isset($historiales))
                                            @php
                                                $historialesUnicos = collect($historiales)->groupBy(function($historial) {
                                                    return strtolower(trim($historial->nombres . ' ' . $historial->apellidos));
                                                })->map(function($group) {
                                                    return $group->sortByDesc('fecha')->first(); // Último registro por fecha
                                                });
                                            @endphp
                                            
                                            @foreach($historialesUnicos as $historial)
                                                <option value="{{ $historial->nombres }} {{ $historial->apellidos }}" 
                                                        data-cedula="{{ $historial->cedula }}"
                                                        data-celular="{{ $historial->celular }}"
                                                        data-correo="{{ $historial->correo }}"
                                                        data-direccion="{{ $historial->direccion }}"
                                                        data-sucursal="{{ $historial->empresa ? strtoupper($historial->empresa->nombre) : 'SIN EMPRESA' }}"
                                                        data-fecha="{{ $historial->fecha ? $historial->fecha->format('d/m/Y') : 'Sin fecha' }}">
                                                    {{ $historial->nombres }} {{ $historial->apellidos }} 
                                                    ({{ $historial->empresa ? strtoupper($historial->empresa->nombre) : 'SIN EMPRESA' }} - {{ $historial->fecha ? $historial->fecha->format('d/m/Y') : 'Sin fecha' }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="form-text text-muted">
                                        Busque en el historial clínico. Se muestran solo los últimos registros únicos.
                                        <br><strong>Formato:</strong> Nombre (Empresa - Fecha del historial)
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label for="fact" class="form-label">ESTADO</label>
                                    <select class="form-control" id="fact" name="fact">
                                        <option value="Pendiente" selected>Pendiente</option>
                                        <option value="CRISTALERIA">Cristalería</option>
                                        <option value="Separado">Separado</option>
                                        <option value="LISTO EN TALLER">Listo en Taller</option>
                                        <option value="Enviado">Enviado</option>
                                        <option value="ENTREGADO">Entregado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" required>
                                </div>
                            </div>

                            {{-- Nueva fila para cédula --}}
                            <div class="row mb-3">                                <div class="col-md-6">
                                    <label for="cedula" class="form-label">RUT</label>
                                    <input type="text" class="form-control" id="cedula" name="cedula" list="cedulas_existentes" placeholder="Seleccione o escriba un RUT" autocomplete="off">
                                    <datalist id="cedulas_existentes">
                                        @foreach($cedulas as $cedula)
                                            <option value="{{ $cedula }}">
                                        @endforeach
                                    </datalist>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-magic"></i> Se buscará automáticamente información del historial clínico y pedidos anteriores
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label for="paciente" class="form-label">Paciente</label>
                                    <input type="text" class="form-control" id="paciente" name="paciente">
                                </div>
                            </div>

                            {{-- Fila 3 --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="examen_visual" class="form-label">Costo Examen Visual</label>
                                    <input type="number" class="form-control form-control-sm" id="examen_visual" name="examen_visual" step="0.01" oninput="calculateTotal()">
                                </div>                                <div class="col-md-3">
                                    <label for="celular" class="form-label">Celular</label>
                                    <input type="text" class="form-control" id="celular" name="celular" placeholder="Escriba el número de celular" autocomplete="off">
                                </div>                                <div class="col-md-3">
                                    <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" placeholder="Escriba el correo electrónico" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label for="empresa_id" class="form-label">SUCURSAL</label>
                                    <select name="empresa_id" id="empresa_id" class="form-control" {{ !$isUserAdmin && $empresas->count() <= 1 ? 'disabled' : '' }}>
                                        <option value="">Seleccione una empresa...</option>
                                        @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}" {{ ($userEmpresaId == $empresa->id) ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isUserAdmin && $empresas->count() <= 1 && $userEmpresaId)
                                        <input type="hidden" name="empresa_id" value="{{ $userEmpresaId }}">
                                        <small class="form-text text-muted">Solo tiene acceso a esta empresa</small>
                                    @elseif(!$isUserAdmin && $empresas->count() > 1)
                                        <small class="form-text text-muted">Seleccione entre sus empresas asociadas</small>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Escriba la dirección" autocomplete="off">
                                </div>
                            </div>

                            {{-- Nuevos campos: Método de envío y Fecha de entrega --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="metodo_envio" class="form-label">Método de Envío</label>
                                    <select class="form-control" id="metodo_envio" name="metodo_envio">
                                        <option value="">Seleccione método de envío...</option>
                                        <option value="TIENDA">TIENDA</option>
                                        <option value="CORREOS DE CHILE">CORREOS DE CHILE</option>
                                        <option value="CHILEXPRESS">CHILEXPRESS</option>
                                        <option value="STARKEN">STARKEN</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                                    <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega">
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
                                                {{ $armazon->codigo }} - {{ $armazon->lugar }} - {{ $armazon->fecha ? \Carbon\Carbon::parse($armazon->fecha)->format('d/m/Y') : 'Sin fecha' }} - {{ $armazon->empresa ? $armazon->empresa->nombre : 'Sin empresa' }}
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
                                <div class="col-md-6">
                                    <label for="a_foto" class="form-label">Foto Armazón (Opcional)</label>
                                    <input type="file" class="form-control form-control-sm" id="a_foto" name="a_foto[]" accept="image/*">
                                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
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
                            <h3 class="card-title">RECETA</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Fila 6 - Prescripción/Medidas --}}
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Prescripción/Medidas de Lunas</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="10%">Ojo</th>
                                                    <th width="20%">Esfera</th>
                                                    <th width="20%">Cilindro</th>
                                                    <th width="15%">Eje</th>
                                                    <th width="15%">ADD</th>
                                                    <th width="20%">Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="align-middle text-center"><strong>OD</strong></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="od_esfera[]" placeholder="Ej: +2.00"></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="od_cilindro[]" placeholder="Ej: -1.50"></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="od_eje[]" placeholder="Ej: 90°"></td>
                                                    <td rowspan="2" class="align-middle"><input type="text" class="form-control form-control-sm medida-input" name="add[]" placeholder="Ej: +2.00"></td>
                                                    <td rowspan="2" class="align-middle"><textarea class="form-control form-control-sm" name="l_detalle[]" rows="3" placeholder="Detalles adicionales"></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td class="align-middle text-center"><strong>OI</strong></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="oi_esfera[]" placeholder="Ej: +1.75"></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="oi_cilindro[]" placeholder="Ej: -1.25"></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="oi_eje[]" placeholder="Ej: 85°"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center"><strong>DP</strong></td>
                                                    <td><input type="text" class="form-control form-control-sm medida-input" name="dp[]" placeholder="Ej: 62"></td>
                                                    <td colspan="4">
                                                        <input type="hidden" id="l_medida" name="l_medida[]">
                                                        <small class="text-muted">Distancia Pupilar</small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Formato de ejemplo:</strong> OD: +2.00 -1.50 X90° / OI: +1.75 -1.25 X85° ADD: +2.00 DP: 62
                                    </small>
                                </div>
                            </div>
                            {{-- Fila nueva para tipo de lente, material y filtro --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
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
                                <div class="col-md-6">
                                    <label class="form-label">Material</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="material_od" class="form-label text-sm">OD (Ojo Derecho)</label>
                                            <input type="text" class="form-control form-control-sm material-input" id="material_od" name="material_od[]" list="material_options" placeholder="Material OD">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="material_oi" class="form-label text-sm">OI (Ojo Izquierdo)</label>
                                            <input type="text" class="form-control form-control-sm material-input" id="material_oi" name="material_oi[]" list="material_options" placeholder="Material OI">
                                        </div>
                                    </div>
                                    <input type="hidden" id="material" name="material[]">
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
                                <div class="col-md-3">
                                    <label class="form-label">Filtros</label>
                                    <div id="filtros-container-0" class="filtros-container">
                                        <div class="filtro-item mb-2">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control filtro-input" list="filtro_options" placeholder="Filtro 1">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-success btn-sm agregar-filtro" onclick="agregarFiltro(0)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="filtro[]" class="filtros-hidden">
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
                                <div class="col-md-6">
                                    <label class="form-label">Foto Lunas (Opcional)</label>
                                    <input type="file" class="form-control form-control-sm" name="l_foto[]" accept="image/*">
                                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
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
                                                {{ $item->codigo }} - {{ $item->lugar }} - {{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha' }} - {{ $item->empresa ? $item->empresa->nombre : 'Sin empresa' }}
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
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="d_foto[]" class="form-label">Foto Accesorio (Opcional)</label>
                                    <input type="file" class="form-control form-control-sm" id="d_foto[]" name="d_foto[]" accept="image/*">
                                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
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

            // Búsqueda simplificada solo para historial clínico con selectpicker
            $('#buscar_historial_clinico').on('changed.bs.select', function() {
                const valor = $(this).val();
                
                if (!valor) {
                    limpiarCamposAutocompletado();
                    return;
                }
                
                // Obtener datos del option seleccionado
                const selectedOption = $(this).find('option:selected');
                const cedula = selectedOption.data('cedula');
                const celular = selectedOption.data('celular');
                const correo = selectedOption.data('correo');
                const direccion = selectedOption.data('direccion');
                const empresa = selectedOption.data('sucursal');
                const fecha = selectedOption.data('fecha');
                
                // Mostrar información del registro seleccionado
                mostrarInformacionHistorial(empresa, fecha);
                
                // Llenar campos básicos
                $('#cliente').val(extraerNombreLimpio(valor));
                $('#paciente').val(extraerNombreLimpio(valor));
                
                // Llenar campos adicionales si existen
                if (celular) $('#celular').val(celular);
                if (correo) $('#correo_electronico').val(correo);
                if (direccion) $('#direccion').val(direccion);
                
                // Buscar datos completos del historial clínico
                if (cedula) {
                    buscarHistorialClinicoPorCedula(cedula);
                } else {
                    buscarHistorialClinicoPorNombreCompleto(extraerNombreLimpio(valor));
                }
            });

            // Manejar cédula - buscar en historial clínico y pedidos anteriores
            $('#cedula').on('input', function() {
                const valor = this.value.trim();
                if (valor.length >= 3) {
                    setTimeout(() => {
                        const valorActual = $('#cedula').val();
                        if (valorActual === valor) {
                            // Buscar en historial clínico
                            buscarHistorialClinicoPorCedula(valor);
                            // Buscar información de pedidos anteriores
                            buscarPedidoAnteriorPorRut(valor);
                        }
                    }, 300);
                }
            });

            // Agregar event listeners para campos de medidas de la primera sección
            const camposMedidas = document.querySelectorAll('.medida-input');
            camposMedidas.forEach(campo => {
                campo.addEventListener('input', formatearMedidasLunas);
                campo.addEventListener('blur', formatearMedidasLunas);
            });

            // Agregar event listeners para campos de material de la primera sección
            const materialOD = document.querySelector('#material_od');
            const materialOI = document.querySelector('#material_oi');
            
            if (materialOD) {
                materialOD.addEventListener('input', formatearMaterial);
            }
            if (materialOI) {
                materialOI.addEventListener('input', formatearMaterial);
            }

            // Agregar event listeners para campos de filtros de la primera sección
            const filtroInputs = document.querySelectorAll('#filtros-container-0 .filtro-input');
            filtroInputs.forEach(input => {
                input.addEventListener('input', function() {
                    actualizarFiltrosHidden(this.closest('.col-md-3'));
                });
                input.addEventListener('blur', function() {
                    actualizarFiltrosHidden(this.closest('.col-md-3'));
                });
            });

            // Inicializar actualización automática del número de orden
            inicializarActualizacionNumeroOrden();

        });

        // Funciones simplificadas para historial clínico únicamente
        
        // Función para extraer solo el nombre limpio sin información adicional
        window.extraerNombreLimpio = function(valorCompleto) {
            return valorCompleto.replace(/\s*\([^)]*\)\s*/g, '').trim();
        };
        
        // Función para limpiar campos de autocompletado
        window.limpiarCamposAutocompletado = function() {
            const mensajesPrevios = document.querySelectorAll(
                '.loading-indicator-historial, .info-historial, .error-historial, .alert-success, .info-historial-registro, .loading-indicator-pedido, .info-pedido-anterior'
            );
            mensajesPrevios.forEach(msg => msg.remove());
        };
        
        // Función para mostrar información del historial seleccionado
        window.mostrarInformacionHistorial = function(empresa, fecha) {
            limpiarCamposAutocompletado();
            
            const infoMsg = document.createElement('div');
            infoMsg.classList.add('alert', 'alert-info', 'mt-2', 'alert-sm', 'info-historial-registro');
            infoMsg.style.fontSize = '0.875rem';
            infoMsg.style.padding = '0.5rem';
            
            let textoInfo = 'Registro del <strong>HISTORIAL CLÍNICO</strong>';
            if (empresa && empresa !== 'SIN EMPRESA') {
                textoInfo += ` - Empresa: <strong>${empresa}</strong>`;
            }
            if (fecha && fecha !== 'Sin fecha') {
                textoInfo += ` - Fecha: <strong>${fecha}</strong>`;
            }
            
            infoMsg.innerHTML = textoInfo;
            document.getElementById('buscar_historial_clinico').parentNode.appendChild(infoMsg);
            
            setTimeout(() => {
                infoMsg.remove();
            }, 4000);
        };

        // Funciones de búsqueda en historial clínico - SIMPLIFICADAS
        window.buscarHistorialClinico = function(nombreCompleto) {
            if (!nombreCompleto) return;
            buscarHistorialClinicoPorNombreCompleto(nombreCompleto);
        };
        
        window.buscarHistorialClinicoPorCedula = function(cedula) {
            if (!cedula) return;
            buscarHistorialClinicoPorCampo('cedula', cedula);
        };

        window.buscarHistorialClinicoPorNombreCompleto = function(nombreCompleto) {
            if (!nombreCompleto) return;
            
            // Remover indicadores de carga previos
            limpiarCamposAutocompletado();
            
            // Mostrar indicador de carga
            const loadingIndicator = document.createElement('small');
            loadingIndicator.classList.add('loading-indicator-historial', 'text-muted', 'ml-2');
            loadingIndicator.textContent = 'Buscando en historial clínico...';
            document.getElementById('buscar_historial_clinico').parentNode.appendChild(loadingIndicator);
            
            // Petición AJAX
            const url = `/api/historiales-clinicos/buscar-nombre-completo/${encodeURIComponent(nombreCompleto)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Error al obtener datos del historial');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    procesarRespuestaHistorialClinico(data);
                })
                .catch(error => {
                    procesarErrorHistorialClinico(error);
                });
        };
        
        window.buscarHistorialClinicoPorCampo = function(campo, valor) {
            if (!valor) return;
            
            // Remover indicadores de carga previos
            limpiarCamposAutocompletado();
            
            // Mostrar indicador de carga
            const loadingIndicator = document.createElement('small');
            loadingIndicator.classList.add('loading-indicator-historial', 'text-muted', 'ml-2');
            loadingIndicator.textContent = 'Buscando en historial clínico...';
            document.getElementById('buscar_historial_clinico').parentNode.appendChild(loadingIndicator);
            
            // Petición AJAX
            const url = `/api/historiales-clinicos/buscar-por/${campo}/${encodeURIComponent(valor)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Error al obtener datos del historial');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    procesarRespuestaHistorialClinico(data);
                })
                .catch(error => {
                    procesarErrorHistorialClinico(error);
                });
        };

        // Función para procesar respuestas del historial clínico - SIMPLIFICADA
        window.procesarRespuestaHistorialClinico = function(data) {
            // Remover indicador de carga
            limpiarCamposAutocompletado();
            
            if (data.success && data.historial) {
                // Mostrar notificación de éxito
                const successMsg = document.createElement('div');
                successMsg.classList.add('alert', 'alert-success', 'mt-2', 'alert-sm');
                
                let textoExito = 'Historial clínico cargado correctamente';
                if (data.historial.empresa && data.historial.empresa.nombre) {
                    textoExito += ` - Empresa: ${data.historial.empresa.nombre.toUpperCase()}`;
                }
                if (data.historial.created_at) {
                    textoExito += ` - Fecha: ${data.historial.created_at}`;
                }
                
                successMsg.textContent = textoExito;
                successMsg.style.fontSize = '0.875rem';
                successMsg.style.padding = '0.5rem';
                document.getElementById('buscar_historial_clinico').parentNode.appendChild(successMsg);
                
                setTimeout(() => successMsg.remove(), 4000);
                
                // Autocompletar campos (solo si están vacíos)
                if (!$('#cedula').val() && data.historial.cedula) {
                    $('#cedula').val(data.historial.cedula);
                }
                if (!$('#celular').val() && data.historial.celular) {
                    $('#celular').val(data.historial.celular);
                }
                if (!$('#correo_electronico').val() && data.historial.correo) {
                    $('#correo_electronico').val(data.historial.correo);
                }
                if (!$('#direccion').val() && data.historial.direccion) {
                    $('#direccion').val(data.historial.direccion);
                }
                if (!$('#empresa_id').val() && data.historial.empresa_id) {
                    $('#empresa_id').val(data.historial.empresa_id);
                }
                
                // Cargar datos de receta en los campos individuales y el campo unificado
                if (data.historial.od_esfera !== undefined) {
                    // Abrir sección de lunas
                    const lunasHeader = document.querySelector('#lunas-container .card-header');
                    const lunasCollapsed = document.querySelector('#lunas-container').classList.contains('collapsed-card');
                    if (lunasCollapsed && lunasHeader) {
                        lunasHeader.querySelector('.btn-tool').click();
                    }
                    
                    // Llenar campos individuales de la tabla de prescripción
                    const setValueIfEmpty = (selector, value) => {
                        const element = document.querySelector(selector);
                        if (element && !element.value && value !== null && value !== undefined && value !== '') {
                            element.value = value;
                        }
                    };
                    
                    // Formatear valores para mostrar
                    const formatearValorParaCampo = (valor) => {
                        if (valor === null || valor === undefined || valor === '') return '';
                        if (!isNaN(parseFloat(valor))) {
                            const num = parseFloat(valor);
                            return num > 0 ? `+${num.toFixed(2)}` : `${num.toFixed(2)}`;
                        }
                        return valor;
                    };
                    
                    // Llenar campos individuales
                    setValueIfEmpty('[name="od_esfera[]"]', formatearValorParaCampo(data.historial.od_esfera));
                    setValueIfEmpty('[name="od_cilindro[]"]', formatearValorParaCampo(data.historial.od_cilindro));
                    setValueIfEmpty('[name="od_eje[]"]', data.historial.od_eje ? `${data.historial.od_eje}°` : '');
                    setValueIfEmpty('[name="oi_esfera[]"]', formatearValorParaCampo(data.historial.oi_esfera));
                    setValueIfEmpty('[name="oi_cilindro[]"]', formatearValorParaCampo(data.historial.oi_cilindro));
                    setValueIfEmpty('[name="oi_eje[]"]', data.historial.oi_eje ? `${data.historial.oi_eje}°` : '');
                    setValueIfEmpty('[name="add[]"]', formatearValorParaCampo(data.historial.add));
                    setValueIfEmpty('[name="dp[]"]', data.historial.dp || '');
                    
                    // Actualizar automáticamente el campo l_medida después de un breve delay
                    setTimeout(() => {
                        formatearMedidasLunas();
                        formatearMaterial();
                    }, 100);
                }
            } else {
                // No se encontraron datos
                const infoMsg = document.createElement('small');
                infoMsg.classList.add('text-muted', 'ml-2', 'info-historial');
                infoMsg.textContent = 'No se encontraron datos en el historial clínico';
                document.getElementById('buscar_historial_clinico').parentNode.appendChild(infoMsg);
                
                setTimeout(() => infoMsg.remove(), 2000);
            }
        };

        // Función para procesar errores del historial clínico - SIMPLIFICADA
        window.procesarErrorHistorialClinico = function(error) {
            limpiarCamposAutocompletado();
            
            const errorMsg = document.createElement('small');
            errorMsg.classList.add('text-warning', 'ml-2', 'error-historial');
            errorMsg.textContent = 'No se encontraron datos del historial clínico';
            document.getElementById('buscar_historial_clinico').parentNode.appendChild(errorMsg);
            
            setTimeout(() => errorMsg.remove(), 3000);
        };

        // Función para buscar información de pedidos anteriores por RUT
        window.buscarPedidoAnteriorPorRut = function(rut) {
            if (!rut || rut.length < 3) return;
            
            // Mostrar indicador de carga para pedidos anteriores
            const loadingIndicator = document.createElement('small');
            loadingIndicator.classList.add('loading-indicator-pedido', 'text-primary', 'ml-2');
            loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando pedidos anteriores...';
            document.getElementById('cedula').parentNode.appendChild(loadingIndicator);
            
            // Petición AJAX para buscar pedidos anteriores
            const url = `/api/pedidos/buscar-rut/${encodeURIComponent(rut)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 404) {
                            return null; // No hay pedidos anteriores, no es un error
                        }
                        return response.json().then(err => {
                            throw new Error(err.message || 'Error al buscar pedidos anteriores');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Remover indicador de carga
                    document.querySelectorAll('.loading-indicator-pedido').forEach(el => el.remove());
                    
                    if (data && data.success && data.pedido) {
                        procesarRespuestaPedidoAnterior(data);
                    } else {
                        mostrarInfoPedidoAnterior('No se encontraron pedidos anteriores para este RUT');
                    }
                })
                .catch(error => {
                    // Remover indicador de carga
                    document.querySelectorAll('.loading-indicator-pedido').forEach(el => el.remove());
                    
                    // No mostrar error si no hay pedidos anteriores
                    console.log('No se encontraron pedidos anteriores:', error.message);
                });
        };

        // Función para procesar la respuesta de pedidos anteriores
        window.procesarRespuestaPedidoAnterior = function(data) {
            const pedido = data.pedido;
            const receta = data.receta;
            
            // Autocompletar información del cliente si los campos están vacíos
            if (!$('#cliente').val() && pedido.cliente) {
                $('#cliente').val(pedido.cliente);
            }
            if (!$('#paciente').val() && pedido.paciente) {
                $('#paciente').val(pedido.paciente);
            }
            if (!$('#celular').val() && pedido.celular) {
                $('#celular').val(pedido.celular);
            }
            if (!$('#correo_electronico').val() && pedido.correo_electronico) {
                $('#correo_electronico').val(pedido.correo_electronico);
            }
            if (!$('#direccion').val() && pedido.direccion) {
                $('#direccion').val(pedido.direccion);
            }
            if (!$('#empresa_id').val() && pedido.empresa_id) {
                $('#empresa_id').val(pedido.empresa_id);
            }
            if (!$('#metodo_envio').val() && pedido.metodo_envio) {
                $('#metodo_envio').val(pedido.metodo_envio);
            }
            
            // Autocompletar información de la receta si existe
            if (receta && Object.keys(receta).length > 0) {
                // Expandir la sección de lunas si está colapsada
                const lunasContainer = document.querySelector('#lunas-container');
                if (lunasContainer && lunasContainer.classList.contains('collapsed-card')) {
                    const collapseButton = lunasContainer.querySelector('.btn-tool');
                    if (collapseButton) {
                        collapseButton.click();
                    }
                }
                
                // Llenar campos de receta
                setTimeout(() => {
                    if (receta.tipo_lente && !$('[name="tipo_lente[]"]').first().val()) {
                        $('[name="tipo_lente[]"]').first().val(receta.tipo_lente);
                    }
                    if (receta.material && !$('[name="material[]"]').first().val()) {
                        $('[name="material[]"]').first().val(receta.material);
                    }
                    if (receta.filtro && !$('.filtros-hidden').first().val()) {
                        $('.filtros-hidden').first().val(receta.filtro);
                        // Actualizar display de filtros
                        const filtroContainer = document.querySelector('.filtros-container');
                        if (filtroContainer) {
                            filtroContainer.innerHTML = `<div class="filtro-item mb-2">
                                <span class="badge badge-info">${receta.filtro}</span>
                            </div>`;
                        }
                    }
                    if (receta.l_medida && !$('[name="l_medida[]"]').first().val()) {
                        $('[name="l_medida[]"]').first().val(receta.l_medida);
                    }
                    if (receta.l_detalle && !$('[name="l_detalle[]"]').first().val()) {
                        $('[name="l_detalle[]"]').first().val(receta.l_detalle);
                    }
                }, 100);
            }
            
            // Mostrar mensaje de éxito
            mostrarInfoPedidoAnterior(`Información cargada del último pedido (${pedido.cliente})`, 'success');
        };

        // Función para mostrar información sobre pedidos anteriores
        window.mostrarInfoPedidoAnterior = function(mensaje, tipo = 'info') {
            // Remover mensajes previos
            document.querySelectorAll('.info-pedido-anterior').forEach(el => el.remove());
            
            const infoMsg = document.createElement('small');
            infoMsg.classList.add('info-pedido-anterior', 'ml-2');
            
            if (tipo === 'success') {
                infoMsg.classList.add('text-success');
                infoMsg.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
            } else {
                infoMsg.classList.add('text-muted');
                infoMsg.innerHTML = `<i class="fas fa-info-circle"></i> ${mensaje}`;
            }
            
            document.getElementById('cedula').parentNode.appendChild(infoMsg);
            
            setTimeout(() => infoMsg.remove(), 4000);
        };

        // Funciones para actualización automática del número de orden
        let numeroOrdenInterval;
        let actualizacionActiva = true;

        window.inicializarActualizacionNumeroOrden = function() {
            // Actualizar inmediatamente
            actualizarNumeroOrden();
            
            // Configurar intervalo para actualizar cada segundo
            numeroOrdenInterval = setInterval(() => {
                if (actualizacionActiva) {
                    actualizarNumeroOrden();
                }
            }, 1000);
        };

        window.actualizarNumeroOrden = function() {
            if (!actualizacionActiva) return;
            
            fetch('/api/pedidos/next-order-number')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al obtener número de orden');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && actualizacionActiva) {
                        const numeroOrdenField = document.getElementById('numero_orden');
                        if (numeroOrdenField) {
                            // Solo actualizar si el campo está vacío o tiene un valor menor
                            const valorActual = parseInt(numeroOrdenField.value) || 0;
                            if (valorActual < data.next_order_number) {
                                numeroOrdenField.value = data.next_order_number;
                                
                                // Mostrar brevemente que se actualizó (opcional)
                                numeroOrdenField.style.backgroundColor = '#d4edda';
                                setTimeout(() => {
                                    numeroOrdenField.style.backgroundColor = '';
                                }, 500);
                            }
                        }
                    }
                })
                .catch(error => {
                    console.warn('Error al actualizar número de orden:', error);
                    // No mostrar error al usuario para no interferir con la experiencia
                });
        };

        // Detener la actualización automática cuando se envíe el formulario
        window.detenerActualizacionNumeroOrden = function() {
            actualizacionActiva = false;
            if (numeroOrdenInterval) {
                clearInterval(numeroOrdenInterval);
                numeroOrdenInterval = null;
            }
        };

        // Agregar event listener al formulario para detener actualización al enviar
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('pedidoForm');
            if (form) {
                form.addEventListener('submit', detenerActualizacionNumeroOrden);
            }
            
            // Detener actualización automática si el usuario modifica manualmente el número de orden
            const numeroOrdenField = document.getElementById('numero_orden');
            if (numeroOrdenField) {
                numeroOrdenField.addEventListener('input', function() {
                    // Detener actualizaciones automáticas si el usuario modifica el campo
                    detenerActualizacionNumeroOrden();
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

            // Actualizar campos - sin decimales para Chile
            document.getElementById('total').value = Math.round(total);
            document.getElementById('saldo').value = Math.round(total);
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

        // Mostrar todas las opciones del datalist al hacer clic en el input - MÉTODO SIMPLE
        $('input[list]').on('click', function() {
            // Forzar que se muestren todas las opciones
            if (this.value === '') {
                this.value = ' ';
                this.value = '';
            }
        });

        // Aplicar el estilo de mayúsculas como en historial clínico
        $('input[type="text"], input[type="email"], textarea').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Función para formatear material OD/OI en campo unificado
        window.formatearMaterial = function() {
            // Buscar en todas las secciones de lunas
            document.querySelectorAll('.luna-section, .card-body').forEach(seccion => {
                const materialOD = seccion.querySelector('[name="material_od[]"]')?.value?.trim() || '';
                const materialOI = seccion.querySelector('[name="material_oi[]"]')?.value?.trim() || '';
                const materialUnificado = seccion.querySelector('[name="material[]"]');
                
                if (materialUnificado && (materialOD || materialOI)) {
                    let materialTexto = '';
                    const partes = [];
                    if (materialOD) partes.push(`OD: ${materialOD}`);
                    if (materialOI) partes.push(`OI: ${materialOI}`);
                    materialTexto = partes.join(' | ');
                    materialUnificado.value = materialTexto;
                }
            });
        };

        // Event listeners para formateo automático de material
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar listeners para campos de material
            const addMaterialListeners = (container) => {
                const materialOD = container.querySelector('[name="material_od[]"]');
                const materialOI = container.querySelector('[name="material_oi[]"]');
                
                if (materialOD) {
                    materialOD.addEventListener('input', function() {
                        formatearMaterialSeccion(container);
                    });
                }
                if (materialOI) {
                    materialOI.addEventListener('input', function() {
                        formatearMaterialSeccion(container);
                    });
                }
            };

            // Función para formatear material en una sección específica
            window.formatearMaterialSeccion = function(seccion) {
                const materialOD = seccion.querySelector('[name="material_od[]"]')?.value?.trim() || '';
                const materialOI = seccion.querySelector('[name="material_oi[]"]')?.value?.trim() || '';
                const materialUnificado = seccion.querySelector('[name="material[]"]');
                
                if (materialUnificado) {
                    let materialTexto = '';
                    if (materialOD || materialOI) {
                        const partes = [];
                        if (materialOD) partes.push(`OD: ${materialOD}`);
                        if (materialOI) partes.push(`OI: ${materialOI}`);
                        materialTexto = partes.join(' | ');
                    }
                    materialUnificado.value = materialTexto;
                }
            };

            // Agregar a la primera sección
            addMaterialListeners(document);
        });

        function createNewFields(type) {
            let html = '';
            const index = document.querySelectorAll(`[data-${type}-section]`).length;
            
            if (type === 'armazon') {
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
                                    <option value="">Seleccione un armazón</option>
                                    @foreach($armazones as $armazon)
                                        <option value="{{ $armazon->id }}">
                                            {{ $armazon->codigo }} - {{ $armazon->lugar }} - {{ $armazon->fecha ? \Carbon\Carbon::parse($armazon->fecha)->format('d/m/Y') : 'Sin fecha' }}
                                        </option>
                                    @endforeach
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
                            <div class="col-md-6">
                                <label class="form-label">Foto Armazón (Opcional)</label>
                                <input type="file" class="form-control form-control-sm" name="a_foto[]" accept="image/*">
                                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
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
                            <div class="col-md-12">
                                <label class="form-label">Prescripción/Medidas de Lunas</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="10%">Ojo</th>
                                                <th width="20%">Esfera</th>
                                                <th width="20%">Cilindro</th>
                                                <th width="15%">Eje</th>
                                                <th width="15%">ADD</th>
                                                <th width="20%">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle text-center"><strong>OD</strong></td>
                                                <td><input type="text" class="form-control form-control-sm" name="od_esfera[]" placeholder="Ej: +2.00"></td>
                                                <td><input type="text" class="form-control form-control-sm" name="od_cilindro[]" placeholder="Ej: -1.50"></td>
                                                <td><input type="text" class="form-control form-control-sm" name="od_eje[]" placeholder="Ej: 90°"></td>
                                                <td rowspan="2" class="align-middle"><input type="text" class="form-control form-control-sm" name="add[]" placeholder="Ej: +2.00"></td>
                                                <td rowspan="2" class="align-middle"><textarea class="form-control form-control-sm" name="l_detalle[]" rows="3" placeholder="Detalles adicionales"></textarea></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle text-center"><strong>OI</strong></td>
                                                <td><input type="text" class="form-control form-control-sm" name="oi_esfera[]" placeholder="Ej: +1.75"></td>
                                                <td><input type="text" class="form-control form-control-sm" name="oi_cilindro[]" placeholder="Ej: -1.25"></td>
                                                <td><input type="text" class="form-control form-control-sm" name="oi_eje[]" placeholder="Ej: 85°"></td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><strong>DP</strong></td>
                                                <td><input type="text" class="form-control form-control-sm" name="dp[]" placeholder="Ej: 62"></td>
                                                <td colspan="4">
                                                    <input type="hidden" name="l_medida[]">
                                                    <small class="text-muted">Distancia Pupilar</small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Formato de ejemplo:</strong> OD: +2.00 -1.50 X90° / OI: +1.75 -1.25 X85° ADD: +2.00 DP: 62
                                </small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="tipo_lente" class="form-label">Tipo de Lente</label>
                                <input type="text" class="form-control" name="tipo_lente[]" list="tipo_lente_options" 
                                       placeholder="Seleccione o escriba un tipo de lente">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Material</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label text-sm">OD (Ojo Derecho)</label>
                                        <input type="text" class="form-control form-control-sm material-input" name="material_od[]" list="material_options" placeholder="Material OD">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-sm">OI (Ojo Izquierdo)</label>
                                        <input type="text" class="form-control form-control-sm material-input" name="material_oi[]" list="material_options" placeholder="Material OI">
                                    </div>
                                </div>
                                <input type="hidden" name="material[]" class="material-hidden">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filtros</label>
                                <div class="filtros-container" id="filtros-container-${index + 1}">
                                    <div class="filtro-item mb-2">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control filtro-input" list="filtro_options" placeholder="Filtro 1">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success btn-sm agregar-filtro" onclick="agregarFiltroSeccion(this)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="filtro[]" class="filtros-hidden">
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
                            <div class="col-md-6">
                                <label class="form-label">Foto Lunas (Opcional)</label>
                                <input type="file" class="form-control form-control-sm" name="l_foto[]" accept="image/*">
                                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
                            </div>
                        </div>
                    </div>
                `;
            }
            else if (type === 'accesorios') {
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
                                    <option value="" selected>Seleccione un Item del Inventario</option>
                                    @foreach ($accesorios as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->codigo }} - {{ $item->lugar }} - {{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha' }}
                                        </option>
                                    @endforeach
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
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Foto Accesorio (Opcional)</label>
                                <input type="file" class="form-control form-control-sm" name="d_foto[]" accept="image/*">
                                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF</small>
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
            
            // Agregar event listeners para los campos de medidas en secciones duplicadas de lunas
            if (type === 'lunas') {
                const newSection = container.lastElementChild;
                const camposMedidas = [
                    '[name="od_esfera[]"]',
                    '[name="od_cilindro[]"]', 
                    '[name="od_eje[]"]',
                    '[name="oi_esfera[]"]',
                    '[name="oi_cilindro[]"]',
                    '[name="oi_eje[]"]',
                    '[name="add[]"]',
                    '[name="dp[]"]'
                ];
                
                camposMedidas.forEach(selector => {
                    const campo = newSection.querySelector(selector);
                    if (campo) {
                        campo.addEventListener('input', function() {
                            // Formatear medidas para esta sección específica
                            formatearMedidasLunasSeccion(newSection);
                        });
                        campo.addEventListener('blur', function() {
                            formatearMedidasLunasSeccion(newSection);
                        });
                    }
                });

                // Agregar event listeners para los campos de material OD/OI
                const materialOD = newSection.querySelector('[name="material_od[]"]');
                const materialOI = newSection.querySelector('[name="material_oi[]"]');
                
                if (materialOD) {
                    materialOD.addEventListener('input', function() {
                        formatearMaterialSeccion(newSection);
                    });
                }
                if (materialOI) {
                    materialOI.addEventListener('input', function() {
                        formatearMaterialSeccion(newSection);
                    });
                }

                // Agregar event listeners para los campos de filtros
                const filtroInputs = newSection.querySelectorAll('.filtro-input');
                filtroInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        actualizarFiltrosHidden(this.closest('.col-md-3'));
                    });
                    input.addEventListener('blur', function() {
                        actualizarFiltrosHidden(this.closest('.col-md-3'));
                    });
                });
            }
            
            // Aplicar el comportamiento simple de datalist a los nuevos campos también
            $('input[list]').off('click').on('click', function() {
                if (this.value === '') {
                    this.value = ' ';
                    this.value = '';
                }
            });
            
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

        // Función para formatear las medidas de lunas automáticamente
        function formatearMedidasLunas() {
            // Obtener valores de los campos de la primera sección de lunas
            const odEsfera = document.querySelector('[name="od_esfera[]"]')?.value?.trim() || '';
            const odCilindro = document.querySelector('[name="od_cilindro[]"]')?.value?.trim() || '';
            const odEje = document.querySelector('[name="od_eje[]"]')?.value?.trim() || '';
            const oiEsfera = document.querySelector('[name="oi_esfera[]"]')?.value?.trim() || '';
            const oiCilindro = document.querySelector('[name="oi_cilindro[]"]')?.value?.trim() || '';
            const oiEje = document.querySelector('[name="oi_eje[]"]')?.value?.trim() || '';
            const add = document.querySelector('[name="add[]"]')?.value?.trim() || '';
            const dp = document.querySelector('[name="dp[]"]')?.value?.trim() || '';
            
            // Formatear valores con signos apropiados
            const formatearValor = (valor) => {
                if (!valor) return '';
                const num = parseFloat(valor.replace(/[+\-]/g, ''));
                if (isNaN(num)) return valor;
                if (valor.includes('-') || num < 0) return `-${Math.abs(num).toFixed(2)}`;
                return `+${num.toFixed(2)}`;
            };
            
            // Construir la cadena de medidas
            let medidaCompleta = '';
            
            // OD
            if (odEsfera || odCilindro || odEje) {
                medidaCompleta += 'OD: ';
                if (odEsfera) medidaCompleta += formatearValor(odEsfera) + ' ';
                if (odCilindro) medidaCompleta += formatearValor(odCilindro) + ' ';
                if (odEje) medidaCompleta += (odEje.includes('X') ? odEje : `X${odEje}`) + (odEje.includes('°') ? '' : '°') + ' ';
            }
            
            // OI
            if (oiEsfera || oiCilindro || oiEje) {
                if (medidaCompleta) medidaCompleta += '/ ';
                medidaCompleta += 'OI: ';
                if (oiEsfera) medidaCompleta += formatearValor(oiEsfera) + ' ';
                if (oiCilindro) medidaCompleta += formatearValor(oiCilindro) + ' ';
                if (oiEje) medidaCompleta += (oiEje.includes('X') ? oiEje : `X${oiEje}`) + (oiEje.includes('°') ? '' : '°') + ' ';
            }
            
            // ADD
            if (add) {
                if (medidaCompleta) medidaCompleta += ' ';
                medidaCompleta += `ADD: ${formatearValor(add)}`;
            }
            
            // DP
            if (dp) {
                if (medidaCompleta) medidaCompleta += ' ';
                medidaCompleta += `DP: ${dp}`;
            }
            
            // Actualizar el campo oculto
            const campoMedida = document.querySelector('#l_medida');
            if (campoMedida) {
                campoMedida.value = medidaCompleta.trim();
            }
        }

        // Función para formatear las medidas de lunas en una sección específica
        function formatearMedidasLunasSeccion(seccion) {
            // Obtener valores de los campos de esta sección específica
            const odEsfera = seccion.querySelector('[name="od_esfera[]"]')?.value?.trim() || '';
            const odCilindro = seccion.querySelector('[name="od_cilindro[]"]')?.value?.trim() || '';
            const odEje = seccion.querySelector('[name="od_eje[]"]')?.value?.trim() || '';
            const oiEsfera = seccion.querySelector('[name="oi_esfera[]"]')?.value?.trim() || '';
            const oiCilindro = seccion.querySelector('[name="oi_cilindro[]"]')?.value?.trim() || '';
            const oiEje = seccion.querySelector('[name="oi_eje[]"]')?.value?.trim() || '';
            const add = seccion.querySelector('[name="add[]"]')?.value?.trim() || '';
            const dp = seccion.querySelector('[name="dp[]"]')?.value?.trim() || '';
            
            // Formatear valores con signos apropiados
            const formatearValor = (valor) => {
                if (!valor) return '';
                const num = parseFloat(valor.replace(/[+\-]/g, ''));
                if (isNaN(num)) return valor;
                if (valor.includes('-') || num < 0) return `-${Math.abs(num).toFixed(2)}`;
                return `+${num.toFixed(2)}`;
            };
            
            // Construir la cadena de medidas
            let medidaCompleta = '';
            
            // OD
            if (odEsfera || odCilindro || odEje) {
                medidaCompleta += 'OD: ';
                if (odEsfera) medidaCompleta += formatearValor(odEsfera) + ' ';
                if (odCilindro) medidaCompleta += formatearValor(odCilindro) + ' ';
                if (odEje) medidaCompleta += (odEje.includes('X') ? odEje : `X${odEje}`) + (odEje.includes('°') ? '' : '°') + ' ';
            }
            
            // OI
            if (oiEsfera || oiCilindro || oiEje) {
                if (medidaCompleta) medidaCompleta += '/ ';
                medidaCompleta += 'OI: ';
                if (oiEsfera) medidaCompleta += formatearValor(oiEsfera) + ' ';
                if (oiCilindro) medidaCompleta += formatearValor(oiCilindro) + ' ';
                if (oiEje) medidaCompleta += (oiEje.includes('X') ? oiEje : `X${oiEje}`) + (oiEje.includes('°') ? '' : '°') + ' ';
            }
            
            // ADD
            if (add) {
                if (medidaCompleta) medidaCompleta += ' ';
                medidaCompleta += `ADD: ${formatearValor(add)}`;
            }
            
            // DP
            if (dp) {
                if (medidaCompleta) medidaCompleta += ' ';
                medidaCompleta += `DP: ${dp}`;
            }
            
            // Actualizar el campo oculto de esta sección
            const campoMedida = seccion.querySelector('[name="l_medida[]"]');
            if (campoMedida) {
                campoMedida.value = medidaCompleta.trim();
            }
        }

        // Event listeners para los campos de medidas de lunas
        document.addEventListener('DOMContentLoaded', function() {
            // ...existing code...
            
            // Agregar event listeners para formateo automático de medidas
            const camposMedidas = [
                '[name="od_esfera[]"]',
                '[name="od_cilindro[]"]', 
                '[name="od_eje[]"]',
                '[name="oi_esfera[]"]',
                '[name="oi_cilindro[]"]',
                '[name="oi_eje[]"]',
                '[name="add[]"]',
                '[name="dp[]"]'
            ];
            
            camposMedidas.forEach(selector => {
                const campo = document.querySelector(selector);
                if (campo) {
                    campo.addEventListener('input', formatearMedidasLunas);
                    campo.addEventListener('blur', formatearMedidasLunas);
                }
            });
        });

        // Funciones para manejar múltiples filtros
        window.agregarFiltro = function(seccionIndex) {
            const container = document.getElementById(`filtros-container-${seccionIndex}`);
            const filtroCount = container.querySelectorAll('.filtro-item').length + 1;
            
            const newFiltroItem = document.createElement('div');
            newFiltroItem.className = 'filtro-item mb-2';
            newFiltroItem.innerHTML = `
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control filtro-input" list="filtro_options" placeholder="Filtro ${filtroCount}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger btn-sm eliminar-filtro" onclick="eliminarFiltro(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(newFiltroItem);
            
            // Actualizar campo oculto
            actualizarFiltrosHidden(container.closest('.col-md-3'));
            
            // Agregar event listener al nuevo input
            const newInput = newFiltroItem.querySelector('.filtro-input');
            newInput.addEventListener('input', function() {
                actualizarFiltrosHidden(this.closest('.col-md-3'));
            });
            newInput.addEventListener('blur', function() {
                actualizarFiltrosHidden(this.closest('.col-md-3'));
            });
        };

        window.agregarFiltroSeccion = function(button) {
            const container = button.closest('.col-md-3').querySelector('.filtros-container');
            const filtroCount = container.querySelectorAll('.filtro-item').length + 1;
            
            const newFiltroItem = document.createElement('div');
            newFiltroItem.className = 'filtro-item mb-2';
            newFiltroItem.innerHTML = `
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control filtro-input" list="filtro_options" placeholder="Filtro ${filtroCount}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger btn-sm eliminar-filtro" onclick="eliminarFiltro(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(newFiltroItem);
            
            // Actualizar campo oculto
            actualizarFiltrosHidden(container.closest('.col-md-3'));
            
            // Agregar event listener al nuevo input
            const newInput = newFiltroItem.querySelector('.filtro-input');
            newInput.addEventListener('input', function() {
                actualizarFiltrosHidden(this.closest('.col-md-3'));
            });
            newInput.addEventListener('blur', function() {
                actualizarFiltrosHidden(this.closest('.col-md-3'));
            });
        };

        window.eliminarFiltro = function(button) {
            const filtroItem = button.closest('.filtro-item');
            const container = filtroItem.closest('.col-md-3');
            
            // No permitir eliminar si es el único filtro
            const filtrosContainer = container.querySelector('.filtros-container');
            if (filtrosContainer.querySelectorAll('.filtro-item').length <= 1) {
                return;
            }
            
            filtroItem.remove();
            
            // Actualizar numeración de placeholders
            const filtroItems = filtrosContainer.querySelectorAll('.filtro-item');
            filtroItems.forEach((item, index) => {
                const input = item.querySelector('.filtro-input');
                input.placeholder = `Filtro ${index + 1}`;
            });
            
            // Actualizar campo oculto
            actualizarFiltrosHidden(container);
        };

        window.actualizarFiltrosHidden = function(container) {
            const filtroInputs = container.querySelectorAll('.filtro-input');
            const hiddenInput = container.querySelector('.filtros-hidden');
            
            const filtros = [];
            filtroInputs.forEach(input => {
                const valor = input.value.trim();
                if (valor) {
                    filtros.push(valor);
                }
            });
            
            hiddenInput.value = filtros.join(' | ');
        };

        // Debug para múltiples filtros
        window.debugFiltros = function() {
            const hiddenInputs = document.querySelectorAll('.filtros-hidden');
            hiddenInputs.forEach((input, index) => {
                console.log(`Sección ${index + 1} - Filtros:`, input.value);
            });
        };

    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(function() {
            $('.selectpicker').selectpicker();
        });
    </script>
@stop
