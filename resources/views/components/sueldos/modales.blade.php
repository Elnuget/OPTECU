<!-- Modal Crear Sueldo -->
<div class="modal fade" id="crearSueldoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">REGISTRAR NUEVO SUELDO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sueldos.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fecha">FECHA:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="descripcion">DESCRIPCIÓN:</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="valor">VALOR:</label>
                        <input type="number" class="form-control" id="valor" name="valor" required step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="user_id">USUARIO:</label>
                        <select class="form-control" id="user_id" name="user_id" required>
                            <option value="">SELECCIONE USUARIO</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-success">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Sueldo -->
<div class="modal fade" id="verSueldoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">DETALLES DEL SUELDO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>FECHA:</label>
                    <p id="verFecha" class="form-control-static"></p>
                </div>
                <div class="form-group">
                    <label>DESCRIPCIÓN:</label>
                    <p id="verDescripcion" class="form-control-static"></p>
                </div>
                <div class="form-group">
                    <label>VALOR:</label>
                    <p id="verValor" class="form-control-static"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Sueldo -->
<div class="modal fade" id="editarSueldoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">EDITAR SUELDO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarSueldo" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editFecha">FECHA:</label>
                        <input type="date" class="form-control" id="editFecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="editDescripcion">DESCRIPCIÓN:</label>
                        <input type="text" class="form-control" id="editDescripcion" name="descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="editValor">VALOR:</label>
                        <input type="number" class="form-control" id="editValor" name="valor" required step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE REGISTRO DE SUELDO?</p>
            </div>
            <div class="modal-footer">
                <form id="eliminarForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-danger">ELIMINAR</button>
                </form>
            </div>
        </div>
    </div>
</div> 