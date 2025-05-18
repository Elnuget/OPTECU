{{-- Tabla de Sueldos --}}
<div class="table-responsive">
    <table id="sueldosTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>FECHA</th>
                <th>DESCRIPCIÓN</th>
                <th>VALOR</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sueldos as $sueldo)
                <tr>
                    <td>{{ $sueldo->fecha->format('Y-m-d') }}</td>
                    <td>{{ $sueldo->descripcion }}</td>
                    <td>${{ number_format($sueldo->valor, 2, ',', '.') }}</td>
                    <td>
                        <button type="button" 
                            class="btn btn-xs btn-default text-info mx-1 shadow" 
                            title="Ver"
                            data-toggle="modal" 
                            data-target="#verSueldoModal" 
                            data-id="{{ $sueldo->id }}"
                            data-fecha="{{ $sueldo->fecha->format('Y-m-d') }}"
                            data-descripcion="{{ $sueldo->descripcion }}"
                            data-valor="{{ $sueldo->valor }}">
                            <i class="fa fa-lg fa-fw fa-eye"></i>
                        </button>
                        
                        <button type="button" 
                            class="btn btn-xs btn-default text-primary mx-1 shadow" 
                            title="Editar"
                            data-toggle="modal" 
                            data-target="#editarSueldoModal" 
                            data-id="{{ $sueldo->id }}"
                            data-fecha="{{ $sueldo->fecha->format('Y-m-d') }}"
                            data-descripcion="{{ $sueldo->descripcion }}"
                            data-valor="{{ $sueldo->valor }}">
                            <i class="fa fa-lg fa-fw fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-xs btn-default text-danger mx-1 shadow"
                            title="Eliminar"
                            data-toggle="modal"
                            data-target="#confirmarEliminarModal"
                            data-id="{{ $sueldo->id }}"
                            data-url="{{ route('sueldos.destroy', $sueldo->id) }}">
                            <i class="fa fa-lg fa-fw fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div> 