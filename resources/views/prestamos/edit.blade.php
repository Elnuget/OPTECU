@extends('adminlte::page')

@section('title', 'EDITAR PRÉSTAMO')

@section('content_header')
    <h1>EDITAR PRÉSTAMO</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('prestamos.update', $prestamo->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="user_id">USUARIO:</label>
                    <select name="user_id" id="user_id" class="form-control select2" required>
                        <option value="">SELECCIONE UN USUARIO</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ $prestamo->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="valor">VALOR ORIGINAL:</label>
                    <input type="number" class="form-control" id="valor" name="valor" 
                           value="{{ $prestamo->valor }}" required step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="valor_neto">VALOR NETO:</label>
                    <input type="number" class="form-control" id="valor_neto" name="valor_neto" 
                           value="{{ $prestamo->valor_neto }}" required step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="cuotas">CUOTAS:</label>
                    <input type="number" class="form-control" id="cuotas" name="cuotas" 
                           value="{{ $prestamo->cuotas }}" required min="1">
                </div>

                <div class="form-group">
                    <label for="motivo">MOTIVO:</label>
                    <input type="text" class="form-control" id="motivo" name="motivo" 
                           value="{{ $prestamo->motivo }}" required maxlength="255">
                </div>

                <div class="form-group">
                    <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">CANCELAR</a>
                    <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#user_id').select2({
                theme: 'bootstrap4',
                placeholder: 'SELECCIONE UN USUARIO',
                allowClear: true,
                width: '100%'
            });

            // Calcular valor neto al cambiar el valor original
            $('#valor').on('input', function() {
                const valorOriginal = parseFloat($(this).val()) || 0;
                $('#valor_neto').val(valorOriginal);
            });
        });
    </script>
@stop 