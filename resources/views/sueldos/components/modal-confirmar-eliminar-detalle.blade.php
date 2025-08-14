<!-- Modal Confirmar Eliminar Detalle de Sueldo -->
<div class="modal fade" id="confirmarEliminarDetalleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE DETALLE DE SUELDO?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> ESTA ACCIÓN NO SE PUEDE DESHACER</p>
            </div>
            <div class="modal-footer">
                <form id="eliminarDetalleForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-danger">ELIMINAR</button>
                </form>
            </div>
        </div>
    </div>
</div>
