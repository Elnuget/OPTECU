@extends('adminlte::page')

@section('title', 'CREAR HORARIO')

@section('content_header')
    <h1>CREAR HORARIO</h1>
    <p>REGISTRAR NUEVO HORARIO DE TRABAJO</p>
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">FORMULARIO DE HORARIO</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ strtoupper($error) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('horarios.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="empresa_id">EMPRESA <span class="text-danger">*</span></label>
                            <select name="empresa_id" id="empresa_id" class="form-control @error('empresa_id') is-invalid @enderror" required>
                                <option value="">SELECCIONAR EMPRESA</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ strtoupper($empresa->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                                <div class="invalid-feedback">{{ strtoupper($message) }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora_entrada">HORA DE ENTRADA <span class="text-danger">*</span></label>
                            <input type="time" 
                                   name="hora_entrada" 
                                   id="hora_entrada" 
                                   class="form-control @error('hora_entrada') is-invalid @enderror" 
                                   value="{{ old('hora_entrada') }}" 
                                   required>
                            @error('hora_entrada')
                                <div class="invalid-feedback">{{ strtoupper($message) }}</div>
                            @enderror
                            <small class="form-text text-muted">FORMATO: HH:MM (24 HORAS)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora_salida">HORA DE SALIDA <span class="text-danger">*</span></label>
                            <input type="time" 
                                   name="hora_salida" 
                                   id="hora_salida" 
                                   class="form-control @error('hora_salida') is-invalid @enderror" 
                                   value="{{ old('hora_salida') }}" 
                                   required>
                            @error('hora_salida')
                                <div class="invalid-feedback">{{ strtoupper($message) }}</div>
                            @enderror
                            <small class="form-text text-muted">FORMATO: HH:MM (24 HORAS)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>INFORMACIÓN:</strong>
                            <ul class="mb-0 mt-2">
                                <li>LA HORA DE SALIDA DEBE SER POSTERIOR A LA HORA DE ENTRADA</li>
                                <li>USE EL FORMATO DE 24 HORAS (00:00 - 23:59)</li>
                                <li>EJEMPLO: 08:00 PARA LAS 8:00 AM, 20:00 PARA LAS 8:00 PM</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div id="duracion-preview" class="alert alert-light" style="display: none;">
                            <i class="fas fa-clock"></i> <strong>DURACIÓN ESTIMADA:</strong> <span id="duracion-text">-</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('horarios.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> CANCELAR
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> GUARDAR HORARIO
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    function calcularDuracion() {
        const horaEntrada = document.getElementById('hora_entrada').value;
        const horaSalida = document.getElementById('hora_salida').value;
        
        if (horaEntrada && horaSalida) {
            const entrada = new Date(`2000-01-01 ${horaEntrada}:00`);
            const salida = new Date(`2000-01-01 ${horaSalida}:00`);
            
            if (salida > entrada) {
                const diferencia = (salida - entrada) / (1000 * 60 * 60); // en horas
                const horas = Math.floor(diferencia);
                const minutos = Math.round((diferencia - horas) * 60);
                
                let texto = '';
                if (horas > 0) {
                    texto += `${horas} HORA${horas !== 1 ? 'S' : ''}`;
                }
                if (minutos > 0) {
                    if (texto) texto += ' Y ';
                    texto += `${minutos} MINUTO${minutos !== 1 ? 'S' : ''}`;
                }
                
                document.getElementById('duracion-text').textContent = texto || '0 MINUTOS';
                document.getElementById('duracion-preview').style.display = 'block';
            } else {
                document.getElementById('duracion-preview').style.display = 'none';
            }
        } else {
            document.getElementById('duracion-preview').style.display = 'none';
        }
    }

    document.getElementById('hora_entrada').addEventListener('change', calcularDuracion);
    document.getElementById('hora_salida').addEventListener('change', calcularDuracion);

    // Validación en tiempo real
    document.getElementById('hora_salida').addEventListener('change', function() {
        const horaEntrada = document.getElementById('hora_entrada').value;
        const horaSalida = this.value;
        
        if (horaEntrada && horaSalida && horaSalida <= horaEntrada) {
            this.setCustomValidity('LA HORA DE SALIDA DEBE SER POSTERIOR A LA HORA DE ENTRADA');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
</script>
@stop
