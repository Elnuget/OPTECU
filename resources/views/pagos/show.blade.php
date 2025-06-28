@extends('adminlte::page')

@section('title', 'VER PAGO')

@section('content_header')
<h2>VER PAGO</h2>
@stop

@section('content')
<style>
    /* Convertir todo el texto a mayúsculas */
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
    datalist,
    datalist option,
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
    .modal-content,
    .card-header,
    .card-footer,
    button,
    .close,
    .table thead th,
    .table tbody td,
    li,
    strong {
        text-transform: uppercase !important;
    }
</style>

<br>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            PAGO {{ $pago->id }}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip"
                title="COLLAPSE">
                <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="REMOVE">
                <i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="col-md-6">
            <ul>
                <li><strong>ID:</strong> {{ $pago->id }}</li>
                <li><strong>FECHA DE PAGO:</strong> {{ $pago->created_at->format('d-m-Y') }}</li>
                <li><strong>MÉTODO DE PAGO:</strong> {{ strtoupper($pago->mediodepago->medio_de_pago) }}</li>
                <li><strong>PEDIDO ID:</strong> {{ $pago->pedido->id }}</li>
                <li><strong>SALDO DEL PEDIDO:</strong> {{ $pago->pedido->saldo }}</li>
                <li><strong>PAGO:</strong> {{ $pago->pago }}</li>
                @if($pago->foto)
                    <li><strong>FOTO:</strong></li>
                    <div class="mt-2 mb-2">
                        <img src="{{ asset('uploads/pagos/' . $pago->foto) }}" alt="FOTO DEL PAGO" style="max-width: 300px; max-height: 300px;" class="img-thumbnail">
                    </div>
                @endif
            </ul>
        </div>
    </div>
    <div class="card-footer">
        VER PAGO
    </div>
</div>
<br>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            DETALLE DEL PAGO {{ $pago->id }}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip"
                title="COLLAPSE">
                <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="REMOVE">
                <i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="card-body">
        <table id="example" class="table table-striped table-bordered table-responsive">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>FECHA DE PAGO</th>
                    <th>MÉTODO DE PAGO</th>
                    <th>PEDIDO ID</th>
                    <th>SALDO DEL PEDIDO</th>
                    <th>PAGO</th>
                    <th>FOTO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $pago->id }}</td>
                    <td>{{ $pago->created_at->format('d-m-Y') }}</td>
                    <td>{{ strtoupper($pago->mediodepago->medio_de_pago) }}</td>
                    <td>{{ $pago->pedido->id }}</td>
                    <td>{{ $pago->pedido->saldo }}</td>
                    <td>{{ $pago->pago }}</td>
                    <td>
                        @if($pago->foto)
                            <img src="{{ asset('uploads/pagos/' . $pago->foto) }}" alt="FOTO" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                        @else
                            SIN FOTO
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <br />
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $("#example").DataTable({
            order: [
                [0, "desc"]
            ],
            columnDefs: [{
                targets: [2],
                visible: true,
                searchable: true,
            },],
            dom: 'Bfrtip',
            buttons: [
                'excelHtml5',
                'csvHtml5',
                {
                    extend: 'print',
                    text: 'IMPRIMIR',
                    autoPrint: true,
                    customize: function (win) {
                        $(win.document.body).css('font-size', '16pt');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    filename: 'PAGO.PDF',
                    title: 'PAGO {{ $pago->id }}',
                    pageSize: 'LETTER',
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
            },
        });
    });
    // Agrega un 'event listener' al documento para escuchar eventos de teclado
    document.addEventListener('keydown', function(event) {
        if (event.key === "Home") { // Verifica si la tecla presionada es 'F1'
            window.location.href = '/dashboard'; // Redirecciona a '/dashboard'
        }
    });
</script>
@stop
