@props(['pedido', 'totalPagado'])

<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Totales</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
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
            </div>
        </div>
    </div>
</div> 