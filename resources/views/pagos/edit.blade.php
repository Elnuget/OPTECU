@extends('adminlte::page')

@section('title', 'EDITAR PAGO')

@section('content_header')
@if(session('error'))
<div class="alert {{session('tipo')}} alert-dismissible fade show" role="alert">
    <strong>{{session('error')}}</strong> {{session('mensaje')}}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
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
    strong,
    .select2-selection__rendered {
        text-transform: uppercase !important;
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">EDITAR PAGO</h3>

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
            <form role="form" action="{{ route('pagos.update', $pago->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                
                <div class="form-group">
                    <label>SELECCIONE UN PEDIDO</label>
                    <select name="pedido_id" id="pedido_id" required class="form-control">
                        <option value="">SELECCIONAR EL PEDIDO</option>
                        @foreach($pedidos as $pedido)
                            <option value="{{ $pedido->id }}" data-saldo="{{ $pedido->saldo }}" {{ $pedido->id == $pago->pedido_id ? 'selected' : '' }}>
                                ORDEN: {{ $pedido->numero_orden }} - CLIENTE: {{ $pedido->cliente }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>SELECCIONE UN MEDIO DE PAGO</label>
                    <select name="mediodepago_id" class="form-control">
                        <option value="">SELECCIONAR EL MÉTODO DE PAGO</option>
                        @foreach($mediosdepago as $medioDePago)
                            <option value="{{ $medioDePago->id }}" @if($medioDePago->id == $pago->mediodepago_id) selected @endif>
                                {{ strtoupper($medioDePago->medio_de_pago) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>SALDO</label>
                    <input name="saldo" id="saldo" type="text" class="form-control" value="${{ number_format($pago->pedido->saldo + $pago->pago, 0, ',', '.') }}" readonly>
                </div>
                
                <div class="form-group">
                    <label>PAGO</label>
                    <input name="pago" 
                           required 
                           type="text" 
                           pattern="^\d+$"
                           class="form-control" 
                           value="{{ number_format($pago->pago, 0, '.', '') }}"
                           onkeypress="return (event.charCode >= 48 && event.charCode <= 57)">
                </div>

                <div class="form-group">
                    <label>FECHA DE CREACIÓN</label>
                    <input name="created_at" 
                           type="datetime-local" 
                           class="form-control" 
                           value="{{ \Carbon\Carbon::parse($pago->created_at)->format('Y-m-d\TH:i') }}">
                </div>

                <div class="form-group">
                    <label>FOTO (OPCIONAL)</label>
                    @if($pago->foto)
                        <div class="mb-2">
                            <img src="{{ asset('uploads/pagos/' . $pago->foto) }}" alt="FOTO DEL PAGO" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                            <p class="text-muted">FOTO ACTUAL</p>
                        </div>
                    @endif
                    <input name="foto" type="file" class="form-control-file" accept="image/*">
                    <small class="form-text text-muted">FORMATOS PERMITIDOS: JPEG, PNG, JPG, GIF. TAMAÑO MÁXIMO: 2MB</small>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ strtoupper($error) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="button" class="btn btn-primary pull-left" data-toggle="modal" data-target="#modal">
                    EDITAR PAGO
                </button>
                <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
                    CANCELAR
                </a>

                <div class="modal fade" id="modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">MODIFICAR PAGO</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>¿ESTÁ SEGURO QUE QUIERE GUARDAR LOS CAMBIOS?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">CANCELAR</button>
                                <button type="submit" class="btn btn-primary">GUARDAR CAMBIOS</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-footer">
        EDITAR PAGO
    </div>
</div>
@stop

@section('js')
<script>
    const csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;
    document.getElementById('region').addEventListener('change', (e) => {
        fetch('../GetComunasPorRegion', {
            method: 'POST',
            body: JSON.stringify({ region: e.target.value }),
            headers: {
                'Content-Type': 'application/json',
                "X-CSRF-Token": csrfToken
            }
        }).then(response => {
            return response.json()
        }).then(data => {
            var opciones = "<option value=''>Elegir</option>";
            for (let i in data.lista) {
                opciones += '<option value="' + data.lista[i].id + '">' + data.lista[i].comuna + '</option>';
            }
            document.getElementById("comuna").innerHTML = opciones;
        }).catch(error => console.error(error));
    })
</script>
@stop

@section('footer')
<div class="float-right d-none d-sm-block">
    <b>VERSIÓN</b> @version('compact')
</div>
@stop