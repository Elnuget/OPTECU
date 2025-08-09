@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    <h1>Inventario</h1>
    <p>Administración de Articulo</p>
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen de Inventario</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip"
                    title="Collapse">
                    <i class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <p><strong>ID inventario:</strong> {{ $inventario->id }}</p>
                    <p><strong>Fecha:</strong> {{ $inventario->fecha }}</p>
                    <p><strong>Lugar:</strong> {{ $inventario->lugar }}</p>
                    <p><strong>Columna:</strong> {{ $inventario->columna }}</p>
                    <p><strong>Número:</strong> {{ $inventario->numero }}</p>
                </div>
                <div class="col-6">
                    <p><strong>Código:</strong> {{ $inventario->codigo }}</p>
                    <p><strong>Valor:</strong> {{ $inventario->valor }}</p>
                    <p><strong>Cantidad:</strong> {{ $inventario->cantidad }}</p>
                    <p><strong>Orden:</strong> {{ $inventario->orden }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table id="example" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>Movimiento</td>
                        <td>Ver</td>
                        <td>Codigo</td>
                        <td>Descripcion</td>
                        <td>Cantidad</td>
                        <td>Fecha</td>
                        <td>Usuario</td>
                    </tr>
                </thead>
                <tbody>
                    {{--  @foreach ($historial as $h) --}}
                    {{-- <tr>
                            <td>{{ $h->id }}</td>
                            <td>{{ $h->Movimiento->tipo_movimiento }}</td>
                            <td>
                                @switch($h->Movimiento->id)
                                    @case(1)
                                        <a type="button" class="btn btn-success"
                                            href="{{ route('recepciones.view', $h->id_movimiento) }}">Recepcion</a>
                                    @break

                                    @case(2)
                                        <a type="button" class="btn btn-success"
                                            href="{{ route('ventas.show', $h->id_movimiento) }}">Venta</a>
                                    @break

                                    @case(4)
                                        <a type="button" class="btn btn-success"
                                            href="{{ route('ajustesdeinventario.view', $h->id_movimiento) }}">Ajuste</a>
                                    @break

                                    @default
                                        <a type="button" class="btn btn-success" href="">Datos</a>
                                @endswitch
                            </td>
                            <td>{{ $h->Articulo->cod_interno }}</td>
                            <td>{{ $h->Articulo->descripcion }}</td>
                            <td>{{ $h->cantidad }}</td>
                            <td> @datetime($h->created_at) </td>
                            <td>{{ $h->User->name }}</td>
                        </tr> --}}
                    {{--  @endforeach --}}
                </tbody>
            </table>
            <br>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $("#example").DataTable({
                order: [
                    [0, "desc"]
                ],
                columnDefs: [{
                    targets: [2],
                    visible: true,
                    searchable: true,
                }, ],
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5',
                    'csvHtml5',
                    {
                        extend: 'print',
                        text: 'Imprimir',
                        autoPrint: true,
                        exportOptions: {
                            columns: [0, 1, 3, 4, 5, 6, 7]
                        },
                        customize: function(win) {
                            $(win.document.body).css('font-size', '16pt');
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        filename: 'historial.pdf',
                        title: 'Historial {{ $inventario->codigo }}',
                        pageSize: 'LETTER',
                        exportOptions: {
                            columns: [0, 1, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                language: {
                    url: "{{ asset('js/datatables/Spanish.json') }}",
                },
            });
        });
        // Agrega un 'event listener' al documento para escuchar eventos de teclado
        document.addEventListener('keydown', function(event) {
            if (event.key === "Home") { // Verifica si la tecla presionada es 'Inicio'
                window.location.href = '/dashboard'; // Redirecciona a '/dashboard'
            }
        });
    </script>
@stop

@section('footer')
    <div class="float-right d-none d-sm-block">
        <b>Version</b> @version('compact')
    </div>
@stop
