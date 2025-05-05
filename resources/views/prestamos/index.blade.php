@extends('adminlte::page')

@section('title', 'PRÉSTAMOS')

@section('content_header')
    <h1>PRÉSTAMOS</h1>
    <p>ADMINISTRACIÓN DE PRÉSTAMOS</p>
    @if (session('error'))
        <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
            <strong>{{ session('mensaje') }}</strong>
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
        .close {
            text-transform: uppercase !important;
        }
    </style>

    {{-- Tarjetas de Resumen de Retiros y Egresos --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">RETIROS TOTALES (MES ACTUAL)</span>
                    <span class="info-box-number" id="summary-retiros-mes-actual">CARGANDO...</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">EGRESOS TOTALES (MES ACTUAL)</span>
                    <span class="info-box-number" id="summary-egresos-mes-actual">CARGANDO...</span>
                </div>
            </div>
        </div>
    </div>
    {{-- Fin Tarjetas de Resumen --}}

    <div class="card">
        <div class="card-body">
            {{-- Botón Añadir Préstamo --}}
            <div class="btn-group mb-3">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearPrestamoModal">
                    <i class="fas fa-plus mr-2"></i>AÑADIR PRÉSTAMO
                </button>
            </div>

            <div class="table-responsive">
                <table id="prestamosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>USUARIO</th>
                            <th>MOTIVO</th>
                            <th>VALOR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prestamos as $prestamo)
                            <tr>
                                <td>{{ $prestamo->created_at->format('Y-m-d') }}</td>
                                <td>{{ $prestamo->user->name }}</td>
                                <td>{{ $prestamo->motivo }}</td>
                                <td>${{ number_format($prestamo->valor, 2, ',', '.') }}</td>
                                <td>
                                    <button type="button" 
                                        class="btn btn-xs btn-default text-info mx-1 shadow" 
                                        title="Ver"
                                        onclick="window.location.href='{{ route('prestamos.show', $prestamo->id) }}'">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </button>
                                    @can('admin')
                                    <button type="button"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow"
                                        title="Editar"
                                        onclick="window.location.href='{{ route('prestamos.edit', $prestamo->id) }}'">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-xs btn-default text-danger mx-1 shadow"
                                        onclick="eliminarPrestamo({{ $prestamo->id }})"
                                        title="Eliminar">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear Préstamo -->
    <div class="modal fade" id="crearPrestamoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CREAR PRÉSTAMO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('prestamos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">USUARIO:</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">SELECCIONE UN USUARIO</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="valor">VALOR:</label>
                            <input type="number" class="form-control" id="valor" name="valor" required step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="motivo">MOTIVO:</label>
                            <input type="text" class="form-control" id="motivo" name="motivo" required maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
                        <button type="submit" class="btn btn-primary">GUARDAR</button>
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
                    <p>¿ESTÁ SEGURO QUE DESEA ELIMINAR ESTE PRÉSTAMO?</p>
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
@stop

@section('js')
@include('atajos')
    <script>
        // Función para formatear números como moneda
        function formatCurrency(number) {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
        }

        // Función para obtener y sumar retiros del mes actual
        function fetchAndDisplayRetirosMesActual(ano, mes) {
            const urls = [
                `https://opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`,
                `https://escleroptica2.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`,
                `https://sucursal3.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`
            ];
            const summarySpan = document.getElementById('summary-retiros-mes-actual');
            summarySpan.textContent = 'CARGANDO...';

            Promise.all(urls.map(url => fetch(url).then(resp => resp.ok ? resp.json() : {retiros: []}).catch(() => ({ retiros: [] }))))
                .then(results => {
                    let totalRetiros = 0;
                    results.forEach(data => {
                        if (data.retiros && data.retiros.length > 0) {
                            const retirosFiltrados = data.retiros.filter(retiro => {
                                const motivo = retiro.motivo.toLowerCase();
                                return !motivo.includes('deposito') && !motivo.includes('depósito');
                            });
                            totalRetiros += retirosFiltrados.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                        }
                    });
                    summarySpan.textContent = formatCurrency(totalRetiros);
                })
                .catch(error => {
                    console.error('Error al obtener retiros:', error);
                    summarySpan.textContent = 'ERROR';
                });
        }

        // Función para obtener y sumar egresos del mes actual
        function fetchAndDisplayEgresosMesActual(ano, mes) {
            const urls = [
                `https://opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`,
                `https://escleroptica2.opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`,
                `https://sucursal3.opticas.xyz/api/egresos?ano=${ano}&mes=${mes}`
            ];
            const summarySpan = document.getElementById('summary-egresos-mes-actual');
            summarySpan.textContent = 'CARGANDO...';

            Promise.all(urls.map(url => fetch(url).then(resp => resp.ok ? resp.json() : {total_egresos: 0}).catch(() => ({ total_egresos: 0 }))))
                .then(results => {
                    let totalEgresos = 0;
                    results.forEach(data => {
                        totalEgresos += parseFloat(data.total_egresos) || 0;
                    });
                    summarySpan.textContent = formatCurrency(totalEgresos);
                })
                .catch(error => {
                    console.error('Error al obtener egresos:', error);
                    summarySpan.textContent = 'ERROR';
                });
        }

        $(document).ready(function() {
            // Inicializar DataTable
            var prestamosTable = $('#prestamosTable').DataTable({
                "order": [[0, "desc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });

            // Obtener fecha actual
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;

            // Cargar datos de retiros y egresos del mes actual
            fetchAndDisplayRetirosMesActual(currentYear, currentMonth);
            fetchAndDisplayEgresosMesActual(currentYear, currentMonth);

            // Inicializar select2 para los combobox de usuarios
            $('#user_id').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE UN USUARIO',
                allowClear: true,
                width: '100%'
            });

            // Limpiar los formularios cuando se cierren los modales
            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
                $(this).find('select').val('').trigger('change');
            });
        });

        function eliminarPrestamo(id) {
            if (confirm('¿ESTÁ SEGURO DE ELIMINAR ESTE PRÉSTAMO?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '/prestamos/' + id;
                
                var tokenField = document.createElement('input');
                tokenField.type = 'hidden';
                tokenField.name = '_token';
                tokenField.value = '{{ csrf_token() }}';
                
                var methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(tokenField);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@stop 