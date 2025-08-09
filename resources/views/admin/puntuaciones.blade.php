@extends('adminlte::page')

@section('title', 'Puntuaciones de Usuarios')

@section('content_header')
    <h1>Puntuaciones de Usuarios</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="puntuacionesTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Total Calificaciones</th>
                        <th>Promedio</th>
                        <th>Calificación Mínima</th>
                        <th>Calificación Máxima</th>
                        <th>Visualización</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($puntuaciones as $puntuacion)
                    <tr>
                        <td>{{ $puntuacion->usuario }}</td>
                        <td>{{ $puntuacion->total_calificaciones }}</td>
                        <td>{{ $puntuacion->promedio }}</td>
                        <td>{{ $puntuacion->minima }}</td>
                        <td>{{ $puntuacion->maxima }}</td>
                        <td>
                            <div class="rating-display">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($puntuacion->promedio))
                                        <span class="star filled">★</span>
                                    @else
                                        <span class="star">☆</span>
                                    @endif
                                @endfor
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .rating-display {
        color: #ffd700;
        font-size: 20px;
    }
    .star.filled {
        color: #ffd700;
    }
    .star {
        color: #ddd;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#puntuacionesTable').DataTable({
        "language": {
            "url": "{{ asset('js/datatables/Spanish.json') }}"
        },
        "order": [[2, "desc"]],
        "pageLength": 25,
        "dom": 'Bfrtip',
        "buttons": [
            'excel', 'pdf', 'print'
        ]
    });
});
</script>
@stop