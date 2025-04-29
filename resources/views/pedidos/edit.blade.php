@extends('adminlte::page')

@section('title', 'Editar Pedido')

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
            <h3 class="card-title">Editar Pedido #{{ $pedido->numero_orden }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Agregar id del pedido como campo oculto --}}
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

                {{-- En la sección de lunas, asegurarnos que los IDs se mantienen --}}
                @foreach($pedido->lunas as $index => $luna)
                    <input type="hidden" name="luna_ids[]" value="{{ $luna->id }}">
                @endforeach

                {{-- En la sección de armazones, asegurarnos que los IDs se mantienen --}}
                @foreach($pedido->inventarios as $index => $inventario)
                    <input type="hidden" name="inventario_ids[]" value="{{ $inventario->id }}">
                @endforeach

                {{-- Información Básica --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información Básica</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha">Fecha</label>
                                <input type="date" name="fecha" class="form-control" 
                                    value="{{ $pedido->fecha ? $pedido->fecha->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="numero_orden">Número de Orden</label>
                                <input type="number" name="numero_orden" class="form-control" value="{{ $pedido->numero_orden }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="usuario">Usuario</label>
                                @can('admin')
                                    <select name="usuario" class="form-control" id="usuario">
                                        @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->name }}" {{ $pedido->usuario == $usuario->name ? 'selected' : '' }}>
                                                {{ $usuario->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ $pedido->usuario }}" readonly>
                                    <input type="hidden" name="usuario" value="{{ $pedido->usuario }}">
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Datos del Cliente --}}
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
                        {{-- Fila 2 --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fact" class="form-label">Factura</label>
                                <input type="text" class="form-control" id="fact" name="fact"
                                       value="{{ $pedido->fact }}">
                            </div>
                            <div class="col-md-6">
                                <label for="cliente" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="cliente" name="cliente"
                                       value="{{ $pedido->cliente }}">
                            </div>
                        </div>

                        {{-- Nueva fila para cédula y paciente --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cedula" class="form-label">Cédula</label>
                                <input type="text" class="form-control" id="cedula" name="cedula"
                                       value="{{ $pedido->cedula }}">
                            </div>
                            <div class="col-md-6">
                                <label for="paciente" class="form-label">Paciente</label>
                                <input type="text" class="form-control" id="paciente" name="paciente" 
                                       value="{{ $pedido->paciente }}">
                            </div>
                        </div>

                        {{-- Fila 3 --}}
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="examen_visual" class="form-label">Examen Visual</label>
                                <input type="number" class="form-control form-control-sm" id="examen_visual" name="examen_visual"
                                       value="{{ $pedido->examen_visual }}">
                            </div>
                            <div class="col-md-3">
                                <label for="examen_visual_descuento" class="form-label">Descuento Examen (%)</label>
                                <input type="number" class="form-control form-control-sm" id="examen_visual_descuento"
                                       name="examen_visual_descuento" min="0" max="100" value="{{ $pedido->examen_visual_descuento }}">
                            </div>
                            <div class="col-md-3">
                                <label for="celular" class="form-label">Celular</label>
                                <input type="text" class="form-control" id="celular" name="celular"
                                       value="{{ $pedido->celular }}">
                            </div>
                            <div class="col-md-3">
                                <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                                       value="{{ $pedido->correo_electronico }}">
                            </div>
                        </div>

                        {{-- Nueva fila para paciente --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="paciente" class="form-label">Paciente</label>
                                <input type="text" class="form-control" id="paciente" name="paciente" value="{{ $pedido->paciente }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Armazón y Accesorios --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Armazón y Accesorios</h3>
                    </div>
                    <div class="card-body">
                        <div id="armazones-container">
                            @foreach($pedido->inventarios as $index => $inventario)
                            <div class="armazon-section mb-3">
                                @if($index > 0)
                                    <hr>
                                @endif
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Armazón (Inventario)</label>
                                        <select name="a_inventario_id[]" class="form-control selectpicker" data-live-search="true">
                                            <option value="">Seleccione un armazón</option>
                                            @foreach($inventarioItems as $item)
                                                <option value="{{ $item->id }}" 
                                                    {{ $inventario->id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->codigo }} - {{ $item->lugar ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <label>Precio</label>
                                        <input type="number" name="a_precio[]" class="form-control" 
                                            value="{{ $inventario->pivot->precio }}" step="0.01" oninput="calculateTotal()">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Descuento (%)</label>
                                        <input type="number" name="a_precio_descuento[]" class="form-control" 
                                            value="{{ $inventario->pivot->descuento }}" min="0" max="100" oninput="calculateTotal()">
                                    </div>
                                </div>
                                @if($index > 0)
                                    <div class="row mt-2">
                                        <div class="col-12 text-right">
                                            <button type="button" class="btn btn-danger btn-sm remove-armazon">
                                                Eliminar Armazón
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-success" id="add-armazon">
                                    Agregar Armazón
                                </button>
                            </div>
                        </div>
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
                        @foreach($pedido->lunas as $index => $luna)
                            <div class="luna-section {{ $index > 0 ? 'mt-4' : '' }}">
                                @if($index > 0)
                                    <hr>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-luna" onclick="this.closest('.luna-section').remove(); calculateTotal();">
                                            <i class="fas fa-times"></i> Eliminar
                                        </button>
                                    </div>
                                @endif
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Lunas Medidas</label>
                                        <input type="text" class="form-control" name="l_medida[]" value="{{ $luna->l_medida }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Lunas Detalle</label>
                                        <input type="text" class="form-control" name="l_detalle[]" value="{{ $luna->l_detalle }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo de Lente</label>
                                        <input type="text" class="form-control" name="tipo_lente[]" 
                                               list="tipo_lente_options" value="{{ $luna->tipo_lente }}"
                                               placeholder="Seleccione o escriba un tipo de lente">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Material</label>
                                        <input type="text" class="form-control" name="material[]" 
                                               list="material_options" value="{{ $luna->material }}"
                                               placeholder="Seleccione o escriba un material">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Filtro</label>
                                        <input type="text" class="form-control" name="filtro[]" 
                                               list="filtro_options" value="{{ $luna->filtro }}"
                                               placeholder="Seleccione o escriba un filtro">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Precio Lunas</label>
                                        <input type="number" class="form-control input-sm" name="l_precio[]"
                                               value="{{ $luna->l_precio }}" step="0.01" oninput="calculateTotal()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Desc. Lunas (%)</label>
                                        <input type="number" class="form-control input-sm" name="l_precio_descuento[]"
                                               value="{{ $luna->l_precio_descuento }}" min="0" max="100" oninput="calculateTotal()">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-success" onclick="duplicateLunas()">Agregar más Lunas</button>
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="valor_compra" class="form-label">Valor de Compra</label>
                                <input type="number" class="form-control input-sm" id="valor_compra" name="valor_compra" value="{{ $pedido->valor_compra }}" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label for="motivo_compra" class="form-label">Motivo de Compra</label>
                                <input type="text" class="form-control" id="motivo_compra" name="motivo_compra" 
                                       list="motivo_compra_options" placeholder="Seleccione o escriba un motivo" value="{{ $pedido->motivo_compra }}">
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="total" class="form-label" style="color: red;">Total</label>
                                <input type="number" class="form-control input-sm" id="total" name="total"
                                       value="{{ $pedido->total }}" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label for="saldo" class="form-label">Saldo Pendiente</label>
                                <input type="number" class="form-control input-sm" id="saldo" name="saldo"
                                       value="{{ $pedido->saldo }}" step="0.01" readonly>
                            </div>
                        </div>
                        
                        {{-- Campo oculto para el total de pagos --}}
                        <input type="hidden" id="total_pagado" value="{{ $totalPagado }}">

                        {{-- Botones y Modal --}}
                        <div class="d-flex justify-content-start">
                            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#modal">
                                Editar pedido
                            </button>
                            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>

                        <div class="modal fade" id="modal">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Editar pedido</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro que quiere editar el pedido?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default pull-left"
                                                data-dismiss="modal">Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary">Editar pedido</button>
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
        Editar Pedido
    </div>
    <!-- /.card-footer-->

    {{-- Datalists para las opciones --}}
    <datalist id="tipo_lente_options">
        <option value="Monofocal">
        <option value="Bifocal">
        <option value="Progresivo">
        <option value="Ocupacional">
        <option value="Contacto">
    </datalist>
    
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
@stop

@section('js')
<script>
    document.getElementById('add-armazon').addEventListener('click', function() {
        const container = document.getElementById('armazones-container');
        const template = `
            <div class="armazon-section mb-3">
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <label>Armazón (Inventario)</label>
                        <select name="a_inventario_id[]" class="form-control selectpicker" data-live-search="true">
                            <option value="">Seleccione un armazón</option>
                            @foreach($inventarioItems as $item)
                                <option value="{{ $item->id }}">{{ $item->codigo }} - {{ $item->lugar ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label>Precio</label>
                        <input type="number" name="a_precio[]" class="form-control" value="0" step="0.01" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-6">
                        <label>Descuento (%)</label>
                        <input type="number" name="a_precio_descuento[]" class="form-control" value="0" min="0" max="100" oninput="calculateTotal()">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-armazon">
                            Eliminar Armazón
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        $('.selectpicker').selectpicker('refresh'); // Inicializar el nuevo select
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-armazon')) {
            e.target.closest('.armazon-section').remove();
        }
    });

    function calculateTotal() {
        // Obtener el total pagado
        const totalPagado = parseFloat(document.getElementById('total_pagado').value) || 0;

        // Calcular nuevo total
        let newTotal = 0;

        // Sumar examen visual
        const examenVisual = parseFloat(document.getElementById('examen_visual').value) || 0;
        const examenVisualDescuento = parseFloat(document.getElementById('examen_visual_descuento').value) || 0;
        const examenVisualTotal = examenVisual * (1 - (examenVisualDescuento / 100));
        newTotal += examenVisualTotal;

        // Sumar armazones
        document.querySelectorAll('.armazon-section').forEach(section => {
            const precio = parseFloat(section.querySelector('[name="a_precio[]"]').value) || 0;
            const descuento = parseFloat(section.querySelector('[name="a_precio_descuento[]"]').value) || 0;
            const precioFinal = precio * (1 - (descuento / 100));
            newTotal += precioFinal;
        });

        // Sumar lunas
        document.querySelectorAll('.luna-section').forEach(section => {
            const precio = parseFloat(section.querySelector('[name="l_precio[]"]').value) || 0;
            const descuento = parseFloat(section.querySelector('[name="l_precio_descuento[]"]').value) || 0;
            const precioFinal = precio * (1 - (descuento / 100));
            newTotal += precioFinal;
        });

        // Sumar compra rápida
        const valorCompra = parseFloat(document.getElementById('valor_compra').value) || 0;
        newTotal += valorCompra;

        // Redondear a 2 decimales
        newTotal = Math.round(newTotal * 100) / 100;

        // Calcular saldo pendiente (nuevo total menos pagos realizados)
        const newSaldo = Math.max(0, newTotal - totalPagado);

        // Actualizar los campos
        document.getElementById('total').value = newTotal.toFixed(2);
        document.getElementById('saldo').value = newSaldo.toFixed(2);
    }

    // Agregar listeners para todos los campos que afectan al total
    document.addEventListener('DOMContentLoaded', function() {
        // Campos que afectan al total
        const fields = [
            'examen_visual',
            'examen_visual_descuento',
            'valor_compra',
            'total'  // Agregamos el campo total para permitir edición manual
        ];
        
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.addEventListener('input', function() {
                    if (field === 'total') {
                        // Si se modifica el total manualmente, recalcular solo el saldo
                        const total = parseFloat(this.value) || 0;
                        const totalPagado = parseFloat(document.getElementById('total_pagado').value) || 0;
                        const newSaldo = Math.max(0, total - totalPagado);
                        document.getElementById('saldo').value = newSaldo.toFixed(2);
                    } else {
                        // Para otros campos, calcular todo
                        calculateTotal();
                    }
                });
            }
        });

        // Event delegation para precios y descuentos de armazones
        document.getElementById('armazones-container').addEventListener('input', function(e) {
            if (e.target.matches('[name="a_precio[]"], [name="a_precio_descuento[]"]')) {
                calculateTotal();
            }
        });

        // Event delegation para precios y descuentos de lunas
        document.getElementById('lunas-container').addEventListener('input', function(e) {
            if (e.target.matches('[name="l_precio[]"], [name="l_precio_descuento[]"]')) {
                calculateTotal();
            }
        });

        // Calcular total inicial
        calculateTotal();

        // Hacer que todo el header sea clickeable
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

        // Inicializar selectpicker
        $('.selectpicker').selectpicker();
    });

    // Mostrar todas las opciones del datalist al hacer clic en el input
    document.querySelectorAll('input[list]').forEach(input => {
        input.addEventListener('click', function() {
            this.setAttribute('list', this.getAttribute('list'));
        });
    });

    function duplicateAccesorios() {
        const container = document.getElementById('accesorios-container').querySelector('.card-body');
        const newItem = document.querySelector('.accesorio-item').cloneNode(true);
        // Clear the values in the cloned inputs
        newItem.querySelectorAll('input').forEach(input => input.value = '');
        newItem.querySelector('select').selectedIndex = 0;
        container.appendChild(newItem);
    }

    function duplicateLunas() {
        const container = document.querySelector('#lunas-container .card-body');
        const template = `
            <div class="luna-section mt-4">
                <hr>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm remove-luna" onclick="this.closest('.luna-section').remove(); calculateTotal();">
                        <i class="fas fa-times"></i> Eliminar
                    </button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Lunas Medidas</label>
                        <input type="text" class="form-control" name="l_medida[]" value="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lunas Detalle</label>
                        <input type="text" class="form-control" name="l_detalle[]" value="">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Lente</label>
                        <input type="text" class="form-control" name="tipo_lente[]" 
                               list="tipo_lente_options" value=""
                               placeholder="Seleccione o escriba un tipo de lente">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Material</label>
                        <input type="text" class="form-control" name="material[]" 
                               list="material_options" value=""
                               placeholder="Seleccione o escriba un material">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtro</label>
                        <input type="text" class="form-control" name="filtro[]" 
                               list="filtro_options" value=""
                               placeholder="Seleccione o escriba un filtro">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Precio Lunas</label>
                        <input type="number" class="form-control input-sm" name="l_precio[]"
                               value="0" step="0.01" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desc. Lunas (%)</label>
                        <input type="number" class="form-control input-sm" name="l_precio_descuento[]"
                               value="0" min="0" max="100" oninput="calculateTotal()">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar campos requeridos
        const required = ['fecha', 'numero_orden'];
        let isValid = true;
        
        required.forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        // Validar que el total sea mayor a 0
        const total = parseFloat(document.getElementById('total').value);
        if (total <= 0) {
            isValid = false;
            alert('El total debe ser mayor a 0');
        }
        
        if (isValid) {
            this.submit();
        }
    });
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/bootstrap-select.min.js"></script>
@stop
