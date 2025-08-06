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
            
            <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST">
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
                <x-pedidos.datos-cliente :pedido="$pedido" :empresas="$empresas" />

                {{-- Armazón y Accesorios --}}
                <x-pedidos.armazones :pedido="$pedido" :inventarioItems="$inventarioItems" :filtroMes="$filtroMes ?? null" :filtroAno="$filtroAno ?? null" />

                {{-- Lunas --}}
                <x-pedidos.lunas :pedido="$pedido" />

                {{-- Compra Rápida --}}
                <x-pedidos.compra-rapida :pedido="$pedido" />

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

<!-- Pasar datos de inventario a JavaScript -->
<script>
    // Hacer disponibles los datos de inventario en JavaScript
    window.inventarioItems = @json($inventarioItems ?? []);
    window.filtroMes = @json($filtroMes ?? null);
    window.filtroAno = @json($filtroAno ?? null);
    
    console.log('Datos pasados a JavaScript:');
    console.log('- inventarioItems:', window.inventarioItems.length, 'items');
    console.log('- filtroMes:', window.filtroMes);
    console.log('- filtroAno:', window.filtroAno);
</script>

<!-- Nuestros scripts -->
<script src="{{ asset('js/pedidos.js') }}"></script>
<script src="{{ asset('js/selectpicker-fix.js') }}"></script>

<script>
    $(document).ready(function() {
        console.log('=== Script inline de edit.blade.php iniciado ===');
        
        // Verificar que window.addArmazon esté disponible
        if (typeof window.addArmazon === 'function') {
            console.log('✓ window.addArmazon está disponible');
        } else {
            console.error('✗ window.addArmazon no está disponible');
        }
        
        // Remover event listeners previos del botón add-armazon
        $('#add-armazon').off('click');
        console.log('Event listeners previos removidos del botón #add-armazon');
        
        // Configurar el nuevo event listener para el botón add-armazon
        $('#add-armazon').on('click', function(e) {
            e.preventDefault();
            console.log('=== Botón agregar armazón clickeado (edit.blade.php) ===');
            
            // Verificar nuevamente que la función esté disponible antes de llamarla
            if (typeof window.addArmazon === 'function') {
                console.log('Llamando a window.addArmazon...');
                window.addArmazon();
            } else {
                console.error('window.addArmazon no está definida');
                alert('Error: La función addArmazon no está disponible. Por favor, recarga la página.');
            }
        });
        
        console.log('Event listener configurado para #add-armazon');
        
        // Calcular el total inicial
        if (typeof calculateTotal === 'function') {
            calculateTotal();
            console.log('Total inicial calculado');
        } else {
            console.warn('calculateTotal no está disponible');
        }
        
        console.log('=== Inicialización completada ===');
    });
</script>
@stop
