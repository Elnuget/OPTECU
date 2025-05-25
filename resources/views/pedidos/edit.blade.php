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
                <i class="fas fa-info-circle"></i> En el combobox de armazón o accesorio solo se muestran los artículos del mes y año actual ({{ date('F Y') }}), además de los que ya están asignados a este pedido.
            </div>
            
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
                <x-pedidos.informacion-basica :pedido="$pedido" :usuarios="$usuarios" />

                {{-- Datos del Cliente --}}
                <x-pedidos.datos-cliente :pedido="$pedido" />

                {{-- Armazón y Accesorios --}}
                <x-pedidos.armazones :pedido="$pedido" :inventarioItems="$inventarioItems" />

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

<!-- Bootstrap Select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Nuestro script de pedidos -->
<script>
    $(document).ready(function() {
        // Inicializar todos los selectpicker existentes
        try {
            $('.selectpicker').selectpicker('destroy');
            $('.selectpicker').selectpicker({
                noneSelectedText: 'Seleccione un armazón o accesorio',
                noneResultsText: 'No se encontraron resultados para {0}',
                liveSearch: true,
                liveSearchPlaceholder: 'Buscar...',
                style: 'btn-light',
                size: 10,
                width: '100%'
            });
            console.log('Selectpicker inicializado correctamente en edit.blade.php');
        } catch (error) {
            console.error('Error al inicializar selectpicker en edit.blade.php:', error);
        }

        // Refrescar los selectpicker cada vez que se muestre el card
        $('.card').on('shown.bs.collapse', function() {
            try {
                $('.selectpicker').selectpicker('refresh');
                console.log('Selectpicker refrescado después de mostrar card');
            } catch (error) {
                console.error('Error al refrescar selectpicker:', error);
            }
        });
    });
</script>

<script src="{{ asset('js/pedidos.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded en la vista');

        // Hacer que todo el header sea clickeable
        document.querySelectorAll('.card-header').forEach(header => {
            header.addEventListener('click', function(e) {
                if (!e.target.closest('.btn-tool')) {
                    const collapseButton = this.querySelector('.btn-tool[data-card-widget="collapse"]');
                    if (collapseButton) {
                        collapseButton.click();
                    }
                }
            });
        });
    });
</script>
@stop
