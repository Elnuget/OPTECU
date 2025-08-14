<!-- Tabla de Sueldos -->
<div class="card">
    <div class="card-body">
        {{-- Botón Añadir Sueldo --}}
        <div class="btn-group mb-3">
            <a type="button" class="btn btn-success" href="{{ route('sueldos.create') }}">REGISTRAR SUELDO</a>
        </div>

        <div class="table-responsive">
            <table id="sueldosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>EMPLEADO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>VALOR</th>
                        <th>SUCURSAL</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sueldos as $sueldo)
                        <tr>
                            <td>{{ $sueldo->fecha->format('Y-m-d') }}</td>
                            <td>{{ $sueldo->user->name }}</td>
                            <td>{{ $sueldo->descripcion }}</td>
                            <td>${{ number_format($sueldo->valor, 2, ',', '.') }}</td>
                            <td>{{ $sueldo->empresa ? $sueldo->empresa->nombre : 'SIN SUCURSAL' }}</td>
                            <td>
                                <a href="{{ route('sueldos.show', $sueldo->id) }}"
                                    class="btn btn-xs btn-default text-info mx-1 shadow" title="Ver">
                                    <i class="fa fa-lg fa-fw fa-eye"></i>
                                </a>
                                @can('admin')
                                <a href="{{ route('sueldos.edit', $sueldo->id) }}"
                                    class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                    <i class="fa fa-lg fa-fw fa-pen"></i>
                                </a>

                                <a class="btn btn-xs btn-default text-danger mx-1 shadow"
                                    href="#"
                                    data-toggle="modal"
                                    data-target="#confirmarEliminarModal"
                                    data-id="{{ $sueldo->id }}"
                                    data-url="{{ route('sueldos.destroy', $sueldo->id) }}">
                                    <i class="fa fa-lg fa-fw fa-trash"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
