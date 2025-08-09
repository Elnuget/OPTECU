@extends('adminlte::page')

@section('title', 'MEDIOS DE PAGO')

@section('content_header')
    <h1>CONFIGURACIÓN</h1>
    <p>ADMINISTRACIÓN DE MEDIOS DE PAGO</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong>{{ strtoupper(session('mensaje')) }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>NOMBRE</td>
                            <td>ACCIONES</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($medio as $m)
                            <tr>
                                <td>{{ $m->id }}</td>
                                <td>{{ strtoupper($m->medio_de_pago) }}</td>

                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success dropdown-toggle"
                                            data-toggle="dropdown">ACCIONES</button>

                                        <div class="dropdown-menu" role="menu">
                                            <a class="dropdown-item"
                                                href="{{ route('configuracion.mediosdepago.editar', $m->id) }}">EDITAR</a>

                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#confirmarEliminarModal" data-id="{{ $m->id }}"
                                                data-url="{{ route('configuracion.mediosdepago.destroy', $m->id) }}">ELIMINAR</a>
                                        </div>
                                        <!-- Confirmar Eliminar Modal -->
                                        <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">CONFIRMAR ELIMINACIÓN
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿ESTÁS SEGURO DE QUE DESEAS ELIMINAR ESTE ELEMENTO?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">CANCELAR</button>
                                                        <form id="eliminarForm" method="post" action="">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">ELIMINAR</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <br>
            <div class="btn-group">
                <a type="button" class="btn btn-success" href="{{ route('configuracion.mediosdepago.create') }}">CREAR
                    MEDIO DE PAGO</a>
            </div>
        </div>
    </div>

@stop

@section('js')
@include('atajos')
    <script>
        $(document).ready(function() {
            // Configurar el modal antes de mostrarse
            $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var url = button.data('url');
                var modal = $(this);
                modal.find('#eliminarForm').attr('action', url);
            });

            // Inicializar DataTable
            $('#example').DataTable({
                "language": window.DataTablesSpanishConfig || {
                    "url": "{{ asset('js/datatables/Spanish.json') }}"
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
