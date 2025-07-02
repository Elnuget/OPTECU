@extends('adminlte::page')

@section('title', 'Editar Articulo')

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
    <br>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Articulo</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip"
                    title="Collapse">
                    <i class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="col-md-10">
                <form role="form" action="{{ route('inventario.update', $inventario->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group row">
                        <div class="col-12">
                            <label>Fecha</label>
                            <input name="fecha" required type="date" class="form-control" value="{{ $inventario->fecha }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-12">
                            <label>Empresa</label>
                            <select name="empresa_id" class="form-control">
                                <option value="">Seleccione una Empresa</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ $inventario->empresa_id == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-6">
                            <label>Lugar</label>
                            <input list="lugares" name="lugar" class="form-control" required value="{{ $inventario->lugar }}">
                            <datalist id="lugares">
                                <option value="Soporte">
                                <option value="Vitrina">
                                <option value="Estuches">
                                <option value="Cosas Extras">
                                <option value="Armazones Extras">
                                <option value="Líquidos">
                                <option value="Goteros">
                            </datalist>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-4">
                            <label>Columna</label>
                            <input name="columna" required type="text" class="form-control" value="{{ $inventario->columna }}">
                        </div>
                        <div class="col-4">
                            <label>Número</label>
                            <input name="numero" id="numero" class="form-control" required type="number" value="{{ $inventario->numero }}">
                        </div>
                        <div class="col-4">
                            <label>Código</label>
                            <input name="codigo" id="codigo" required type="text" class="form-control" value="{{ $inventario->codigo }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-4">
                            <label>Valor</label>
                            <input name="valor" id="valor"  type="number" class="form-control" value="{{ $inventario->valor }}">
                        </div>
                        <div class="col-4">
                            <label>Cantidad</label>
                            <input name="cantidad" id="cantidad" required type="number" class="form-control" value="{{ $inventario->cantidad }}">
                        </div>
                        <div class="col-4">
                            <label>Orden</label>
                            <input name="orden"  type="text" class="form-control" value="{{ $inventario->orden }}">
                        </div>
                    </div>

                    <br>

                    <button type="button" class="btn btn-primary pull-left" data-toggle="modal" data-target="#modal">Editar
                        Articulo</button>
                        <a href="{{ route('inventario.index') }}" class="btn btn-secondary ">
                            Cancelar
                        </a>
                    <div class="modal fade" id="modal">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">

                                    <h4 class="modal-title">Modificar inventario</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Estás seguro que quiere guardar los cambios?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default pull-left"
                                        data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </div>
                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                </form>
            </div>

            <br>
            <!-- Fin contenido -->
        </div>
    </div>
    <!-- /.card-body -->
    <div class="card-footer">
        Editar Articulo
    </div>
    <!-- /.card-footer-->
    </div>

@stop

@section('js')
<script></script>
    // Agrega un 'event listener' al documento para escuchar eventos de teclado
document.addEventListener('keydown', function(event) {
    if (event.key === "Home") { // Verifica si la tecla presionada es 'Inicio'
        window.location.href = '/dashboard'; // Redirecciona a '/dashboard'
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const lugarSelect = document.getElementById('lugar');
    const newLugarInput = document.getElementById('newLugar');

    lugarSelect.addEventListener('change', function() {
        if (this.value === 'new') {
            newLugarInput.style.display = 'block';
        } else {
            newLugarInput.style.display = 'none';
        }
    });
});
</script>

@stop

@section('footer')
    <div class="float-right d-none d-sm-block">
        <b>Version</b> @version('compact')
    </div>
@stop