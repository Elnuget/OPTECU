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

{{-- Modal para Rol de Pagos --}}
<div class="modal fade" id="modalRolPagos" tabindex="-1" role="dialog" aria-labelledby="modalRolPagosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolPagosLabel">ROL DE PAGOS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>EMPLEADO: <span id="rolEmpleadoNombre"></span></h6>
                    </div>
                    <div class="col-md-6">
                        <h6>PERÍODO: <span id="rolPeriodo"></span></h6>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center bg-info">INGRESOS</th>
                                <th colspan="2" class="text-center bg-danger">EGRESOS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SUELDO BASE</td>
                                <td id="rolSueldoBase" class="text-right"></td>
                                <td>RETIROS</td>
                                <td id="rolRetiros" class="text-right"></td>
                            </tr>
                            <tr>
                                <td>COMISIÓN PEDIDOS</td>
                                <td id="rolComisionPedidos" class="text-right"></td>
                                <td>OTROS DESCUENTOS</td>
                                <td id="rolOtrosDescuentos" class="text-right"></td>
                            </tr>
                            <tr>
                                <td>OTROS INGRESOS</td>
                                <td id="rolOtrosIngresos" class="text-right"></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr class="bg-light">
                                <th>TOTAL INGRESOS</th>
                                <th id="rolTotalIngresos" class="text-right"></th>
                                <th>TOTAL EGRESOS</th>
                                <th id="rolTotalEgresos" class="text-right"></th>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-success">
                                <th colspan="3" class="text-right">TOTAL A RECIBIR</th>
                                <th id="rolTotalRecibir" class="text-right"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    <h6>DESGLOSE DE MOVIMIENTOS</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>FECHA</th>
                                    <th>TIPO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>MONTO</th>
                                </tr>
                            </thead>
                            <tbody id="rolDesglose">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnImprimirRol">
                    <i class="fas fa-print"></i> IMPRIMIR
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
            </div>
        </div>
    </div>
</div> 