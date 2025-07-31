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
        
        /* Estilos para los combobox personalizados */
        .armazon-dropdown {
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .armazon-dropdown .dropdown-item {
            white-space: normal;
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        
        .armazon-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .armazon-dropdown-btn {
            border-color: #ced4da;
        }
        
        .armazon-search {
            border-right: 0;
        }
        
        /* Mostrar el dropdown por encima de otros elementos */
        .dropdown-menu.show {
            z-index: 1050;
        }
        
        /* Estilos para el reclamo */
        .card-header.bg-danger {
            background-color: #dc3545 !important;
        }
        
        .card-header.bg-danger .card-title {
            color: white !important;
        }
        
        .alert-danger {
            border-color: #dc3545;
        }
        
        .alert-danger .alert-heading {
            color: #721c24;
            font-weight: bold;
        }

        /* Estilos para la edición de recetas en lunas */
        .medida-input {
            font-size: 0.875rem;
        }
        
        .medida-input.is-invalid {
            border-color: #dc3545;
            background-color: #fff5f5;
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #dc3545;
        }
        
        .material-input {
            font-size: 0.875rem;
        }
        
        /* Mejorar la visualización de las tablas de receta */
        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
        }
        
        .table-sm td, .table-sm th {
            padding: 0.5rem;
        }
        
        /* Resaltar campos de receta cuando tienen foco */
        .medida-input:focus, .material-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25);
        }

        /* Estilos para indicar campos obligatorios en receta */
        .receta-required::after {
            content: " *";
            color: #dc3545;
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
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> En el combobox de armazón o accesorio solo se muestran los artículos
                @if(isset($filtroMes) && isset($filtroAno))
                @php
                    $nombresMeses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    $mesTexto = $nombresMeses[(int)$filtroMes] ?? 'del mes actual';
                @endphp
                de <strong>{{ $mesTexto }} {{ $filtroAno }}</strong>
                @else
                del mes y año actual ({{ date('F Y') }})
                @endif
                , además de los que ya están asignados a este pedido.
            </div>
            
            <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Agregar id del pedido como campo oculto --}}
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                
                {{-- Indicar si se debe actualizar el inventario en el backend --}}
                <input type="hidden" name="actualizar_inventario" value="false" id="actualizar_inventario">

                {{-- En la sección de lunas, asegurarnos que los IDs se mantienen --}}
                @foreach($pedido->lunas as $index => $luna)
                    <input type="hidden" name="luna_ids[]" value="{{ $luna->id }}">
                @endforeach

                {{-- En la sección de armazones, asegurarnos que los IDs se mantienen --}}
                @foreach($pedido->inventarios as $index => $inventario)
                    <input type="hidden" name="inventario_ids[]" value="{{ $inventario->id }}">
                @endforeach

                {{-- Información Básica --}}
                <x-pedidos.informacion-basica :pedido="$pedido" :usuarios="$usuarios" />

                {{-- Datos del Cliente --}}
                <x-pedidos.datos-cliente :pedido="$pedido" :empresas="$empresas" :userEmpresaId="$userEmpresaId ?? null" :isUserAdmin="$isUserAdmin ?? false" />

                {{-- Armazón y Accesorios --}}
                <x-pedidos.armazones :pedido="$pedido" :inventarioItems="$inventarioItems" :filtroMes="$filtroMes ?? null" :filtroAno="$filtroAno ?? null" />

                {{-- Lunas --}}
                <x-pedidos.lunas :pedido="$pedido" />

                {{-- Compra Rápida --}}
                <x-pedidos.compra-rapida :pedido="$pedido" />

                {{-- Reclamo --}}
                @if(!empty($pedido->reclamo))
                <div class="card">
                    <div class="card-header bg-danger">
                        <h3 class="card-title text-white">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Reclamo Registrado
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-circle"></i>
                                Descripción del Reclamo:
                            </h5>
                            <hr>
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $pedido->reclamo }}</p>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Los reclamos no se pueden editar desde esta vista por seguridad.
                            </small>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Observación --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Observación</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="observacion" class="form-label">Observación</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="3" 
                                          placeholder="Ingrese cualquier observación adicional sobre el pedido">{{ $pedido->observacion }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Totales --}}
                <x-pedidos.totales :pedido="$pedido" :totalPagado="$totalPagado" />
            </form>
        </div>
    </div>

    <div class="card-footer">
        Editar Pedido
    </div>
@stop

@section('js')
<!-- jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Nuestros scripts -->
<script src="{{ asset('js/pedidos.js') }}"></script>
<script src="{{ asset('js/selectpicker-fix.js') }}"></script>

<script>
    // Pasar datos del inventario a JavaScript
    window.inventarioItems = @json($inventarioItems);
    window.filtroMes = @json($filtroMes ?? null);
    window.filtroAno = @json($filtroAno ?? null);
    
    $(document).ready(function() {
        console.log('Script inline iniciado - editMode:', window.editMode);
        console.log('Inventario items disponibles:', window.inventarioItems.length);
        
        // No es necesario inicializar nada aquí, ya que todo se maneja en selectpicker-fix.js
        
        // Inicializar event listeners para material después de que se cargue la página
        setTimeout(() => {
            if (typeof window.agregarEventListenersMaterial === 'function') {
                window.agregarEventListenersMaterial();
            }
        }, 500);
        
        // Remover cualquier event listener anterior del botón add-armazon
        const addButton = document.getElementById('add-armazon');
        if (addButton) {
            console.log('Botón add-armazon encontrado, configurando event listener');
            
            // Clonar el botón para remover todos los event listeners
            const newButton = addButton.cloneNode(true);
            addButton.parentNode.replaceChild(newButton, addButton);
            
            // Agregar el nuevo event listener
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Botón agregar armazón clickeado - llamando window.addArmazon()');
                
                // Verificar que la función existe
                if (typeof window.addArmazon === 'function') {
                    window.addArmazon();
                } else {
                    console.error('window.addArmazon no está definida');
                }
            });
        } else {
            console.error('No se encontró el botón add-armazon');
        }
        
        // El resto de la lógica ahora se maneja en selectpicker-fix.js
        
        // Calcular el total inicial
        calculateTotal();
        
        // Inicializar funcionalidades de receta/lunas
        inicializarRecetaFunctionality();
    });

    // Función para inicializar la funcionalidad de recetas en lunas
    function inicializarRecetaFunctionality() {
        // Agregar event listeners para campos de medidas de lunas
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('medida-input')) {
                formatearMedidasLunasSeccion(e.target.closest('.luna-section'));
            }
            
            if (e.target.classList.contains('material-input')) {
                formatearMaterialSeccion(e.target.closest('.luna-section'));
            }
        });

        // Validación en tiempo real para campos de receta en lunas
        document.addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('od_') || 
                e.target.name && e.target.name.includes('oi_') || 
                e.target.name && e.target.name.includes('add') || 
                e.target.name && e.target.name.includes('dp')) {
                
                validarCampoReceta(e.target);
            }
        });

        // Auto-formatear campos de eje para agregar símbolo de grado
        document.addEventListener('blur', function(e) {
            if (e.target.name && (e.target.name.includes('od_eje') || e.target.name.includes('oi_eje'))) {
                let valor = e.target.value.trim();
                if (valor && !valor.includes('°')) {
                    const numero = parseInt(valor);
                    if (!isNaN(numero) && numero >= 0 && numero <= 180) {
                        e.target.value = numero + '°';
                        // Actualizar también el campo hidden de medida
                        formatearMedidasLunasSeccion(e.target.closest('.luna-section'));
                    }
                }
            }
        });
    }

    // Función para validar campos de receta
    function validarCampoReceta(campo) {
        const valor = campo.value.trim();
        
        // Remover estilos de error previos
        campo.classList.remove('is-invalid');
        const errorMsg = campo.parentNode.querySelector('.invalid-feedback');
        if (errorMsg) errorMsg.remove();
        
        if (valor) {
            if (campo.name.includes('eje')) {
                // Para eje (debe ser un número entre 0 y 180)
                const eje = parseInt(valor.replace('°', ''));
                if (isNaN(eje) || eje < 0 || eje > 180) {
                    mostrarErrorCampo(campo, 'El eje debe ser un número entre 0 y 180');
                }
            } else if (campo.name.includes('dp')) {
                // Para DP (debe ser un número positivo)
                const dp = parseInt(valor);
                if (isNaN(dp) || dp <= 0) {
                    mostrarErrorCampo(campo, 'La distancia pupilar debe ser un número positivo');
                }
            } else {
                // Para esfera, cilindro, ADD (deben ser números con posibles signos + o -)
                const patronNumerico = /^[+\-]?\d*\.?\d*$/;
                if (!patronNumerico.test(valor)) {
                    mostrarErrorCampo(campo, 'Debe ser un valor numérico válido (ej: +2.25, -1.50)');
                }
            }
        }
    }

    // Función para mostrar error en campo
    function mostrarErrorCampo(campo, mensaje) {
        campo.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = mensaje;
        campo.parentNode.appendChild(errorDiv);
    }

    // Función mejorada para formatear las medidas de lunas en una sección específica
    function formatearMedidasLunasSeccion(seccion) {
        if (!seccion) return;
        
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
            const num = parseFloat(valor.replace(/[^\d.-]/g, ''));
            return isNaN(num) ? valor : (num > 0 ? '+' + num.toFixed(2) : num.toFixed(2));
        };
        
        // Construir la cadena de medidas
        let medidaCompleta = '';
        
        // OD
        if (odEsfera || odCilindro || odEje) {
            medidaCompleta += 'OD: ';
            if (odEsfera) medidaCompleta += formatearValor(odEsfera) + ' ';
            if (odCilindro) medidaCompleta += formatearValor(odCilindro) + ' ';
            if (odEje) medidaCompleta += 'X' + odEje.replace('°', '') + '° ';
            medidaCompleta = medidaCompleta.trim() + ' / ';
        }
        
        // OI
        if (oiEsfera || oiCilindro || oiEje) {
            medidaCompleta += 'OI: ';
            if (oiEsfera) medidaCompleta += formatearValor(oiEsfera) + ' ';
            if (oiCilindro) medidaCompleta += formatearValor(oiCilindro) + ' ';
            if (oiEje) medidaCompleta += 'X' + oiEje.replace('°', '') + '° ';
            medidaCompleta = medidaCompleta.trim() + ' ';
        }
        
        // ADD
        if (add) {
            medidaCompleta += 'ADD: ' + formatearValor(add) + ' ';
        }
        
        // DP
        if (dp) {
            medidaCompleta += 'DP: ' + dp;
        }
        
        // Actualizar el campo oculto
        const campoMedida = seccion.querySelector('.l-medida-hidden');
        if (campoMedida) {
            campoMedida.value = medidaCompleta.trim();
        }
    }

    // Función para formatear material en una sección específica
    function formatearMaterialSeccion(seccion) {
        if (!seccion) return;
        
        const materialOD = seccion.querySelector('[name="material_od[]"]')?.value?.trim() || '';
        const materialOI = seccion.querySelector('[name="material_oi[]"]')?.value?.trim() || '';
        
        let materialCompleto = '';
        if (materialOD || materialOI) {
            if (materialOD === materialOI && materialOD) {
                materialCompleto = materialOD;
            } else {
                if (materialOD) materialCompleto += 'OD: ' + materialOD;
                if (materialOI) {
                    if (materialCompleto) materialCompleto += ' | ';
                    materialCompleto += 'OI: ' + materialOI;
                }
            }
        }
        
        const campoMaterial = seccion.querySelector('.material-hidden');
        if (campoMaterial) {
            campoMaterial.value = materialCompleto;
        }
    }

    // Validación antes del envío del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const errores = document.querySelectorAll('.is-invalid').length;
                if (errores > 0) {
                    e.preventDefault();
                    alert('Por favor corrige los errores en los campos de receta marcados antes de continuar.');
                    return false;
                }
            });
        }
    });
</script>
@stop
