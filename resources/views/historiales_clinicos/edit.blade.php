@extends('adminlte::page')

@section('title', 'EDITAR HISTORIAL CLÍNICO')

@section('content_header')
    <h1>EDITAR HISTORIAL CLÍNICO</h1>
@stop

@section('content')
    {{-- Mensajes de error y éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('historiales_clinicos.update', $historialClinico->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Fecha de Registro --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar"></i> FECHA DE REGISTRO</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="fecha">FECHA</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ \Carbon\Carbon::parse($historialClinico->fecha)->format('Y-m-d') }}">
                    </div>
                </div>
            </div>

            {{-- Datos del Paciente --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> DATOS DEL PACIENTE</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nombres">NOMBRES</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" value="{{ $historialClinico->nombres }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apellidos">APELLIDOS</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" value="{{ $historialClinico->apellidos }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cedula">CÉDULA</label>
                                <input type="text" name="cedula" id="cedula" class="form-control" value="{{ $historialClinico->cedula }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edad">EDAD</label>
                                <input type="number" name="edad" id="edad" class="form-control" value="{{ $historialClinico->edad }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha_nacimiento">FECHA DE NACIMIENTO</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="{{ $historialClinico->fecha_nacimiento ? \Carbon\Carbon::parse($historialClinico->fecha_nacimiento)->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="celular">CELULAR</label>
                                <input type="text" name="celular" id="celular" class="form-control" value="{{ $historialClinico->celular }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ocupacion">OCUPACIÓN</label>
                                <input type="text" name="ocupacion" id="ocupacion" class="form-control" value="{{ $historialClinico->ocupacion }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="empresa_id">EMPRESA</label>
                                <select name="empresa_id" id="empresa_id" class="form-control">
                                    <option value="">Seleccione una empresa...</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ $historialClinico->empresa_id == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Motivo de Consulta y Enfermedad Actual --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-notes-medical"></i> MOTIVO DE CONSULTA Y ENFERMEDAD ACTUAL</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="motivo_consulta">MOTIVO DE CONSULTA</label>
                        <input type="text" name="motivo_consulta" id="motivo_consulta" class="form-control" value="{{ $historialClinico->motivo_consulta }}">
                    </div>
                    <div class="form-group">
                        <label for="enfermedad_actual">ENFERMEDAD ACTUAL</label>
                        <textarea name="enfermedad_actual" id="enfermedad_actual" class="form-control" rows="3">{{ $historialClinico->enfermedad_actual }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Antecedentes --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#antecedentes">
                    <h5 class="mb-0">
                        <i class="fas fa-history mr-2"></i> Antecedentes
                    </h5>
                </div>
                <div id="antecedentes" class="collapse show">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Antecedentes Personales Oculares</label>
                                <textarea name="antecedentes_personales_oculares" class="form-control" rows="3">{{ old('antecedentes_personales_oculares', $historialClinico->antecedentes_personales_oculares) }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Antecedentes Personales Generales</label>
                                <textarea name="antecedentes_personales_generales" class="form-control" rows="3">{{ old('antecedentes_personales_generales', $historialClinico->antecedentes_personales_generales) }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Antecedentes Familiares Oculares</label>
                                <textarea name="antecedentes_familiares_oculares" class="form-control" rows="3">{{ old('antecedentes_familiares_oculares', $historialClinico->antecedentes_familiares_oculares) }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Antecedentes Familiares Generales</label>
                                <textarea name="antecedentes_familiares_generales" class="form-control" rows="3">{{ old('antecedentes_familiares_generales', $historialClinico->antecedentes_familiares_generales) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Agudeza Visual y PH --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#agudezaVisual">
                    <h5 class="mb-0">
                        <i class="fas fa-eye mr-2"></i> Agudeza Visual y PH
                    </h5>
                </div>
                <div id="agudezaVisual" class="collapse show">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Agudeza Visual VL sin Corrección</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>OD</label>
                                        <input type="text" name="agudeza_visual_vl_sin_correccion_od" class="form-control"
                                            value="{{ old('agudeza_visual_vl_sin_correccion_od', $historialClinico->agudeza_visual_vl_sin_correccion_od) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>OI</label>
                                        <input type="text" name="agudeza_visual_vl_sin_correccion_oi" class="form-control"
                                            value="{{ old('agudeza_visual_vl_sin_correccion_oi', $historialClinico->agudeza_visual_vl_sin_correccion_oi) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>AO</label>
                                        <input type="text" name="agudeza_visual_vl_sin_correccion_ao" class="form-control"
                                            value="{{ old('agudeza_visual_vl_sin_correccion_ao', $historialClinico->agudeza_visual_vl_sin_correccion_ao) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Agudeza Visual VP sin Corrección</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>OD</label>
                                        <input type="text" name="agudeza_visual_vp_sin_correccion_od" class="form-control"
                                            value="{{ old('agudeza_visual_vp_sin_correccion_od', $historialClinico->agudeza_visual_vp_sin_correccion_od) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>OI</label>
                                        <input type="text" name="agudeza_visual_vp_sin_correccion_oi" class="form-control"
                                            value="{{ old('agudeza_visual_vp_sin_correccion_oi', $historialClinico->agudeza_visual_vp_sin_correccion_oi) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>AO</label>
                                        <input type="text" name="agudeza_visual_vp_sin_correccion_ao" class="form-control"
                                            value="{{ old('agudeza_visual_vp_sin_correccion_ao', $historialClinico->agudeza_visual_vp_sin_correccion_ao) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Pin Hole (PH)</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>PH OD</label>
                                        <input type="text" name="ph_od" class="form-control"
                                            value="{{ old('ph_od', $historialClinico->ph_od) }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>PH OI</label>
                                            <input type="text" name="ph_oi" class="form-control"
                                                value="{{ old('ph_oi', $historialClinico->ph_oi) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Se eliminó el campo ADD de esta sección --}}
                            <input type="hidden" name="usuario_id" value="{{ old('usuario_id', $historialClinico->usuario_id) }}">
                        <div class="form-group">
                            <label>Optotipo</label>
                            <textarea name="optotipo" class="form-control" rows="2">{{ old('optotipo', $historialClinico->optotipo) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lensometría --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#lensometria">
                    <h5 class="mb-0">
                        <i class="fas fa-glasses mr-2"></i> Lensometría
                    </h5>
                </div>
                <div id="lensometria" class="collapse show">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Lensometría</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>OD</label>
                                        <input type="text" name="lensometria_od" class="form-control" 
                                            value="{{ old('lensometria_od', $historialClinico->lensometria_od) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>OI</label>
                                        <input type="text" name="lensometria_oi" class="form-control" 
                                            value="{{ old('lensometria_oi', $historialClinico->lensometria_oi) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de Lente</label>
                                    <input type="text" name="tipo_lente" class="form-control" 
                                        value="{{ old('tipo_lente', $historialClinico->tipo_lente) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Material</label>
                                    <input type="text" name="material" class="form-control" 
                                        value="{{ old('material', $historialClinico->material) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Filtro</label>
                                    <input type="text" name="filtro" class="form-control" 
                                        value="{{ old('filtro', $historialClinico->filtro) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tiempo de Uso</label>
                                    <input type="text" name="tiempo_uso" class="form-control" 
                                        value="{{ old('tiempo_uso', $historialClinico->tiempo_uso) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rx Final --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#rxFinal">
                    <h5 class="mb-0">
                        <i class="fas fa-prescription mr-2"></i> Rx Final
                    </h5>
                </div>
                <div id="rxFinal" class="collapse show">
                    <div class="card-body">
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6">
                                <label>Refracción OD</label>
                                <input type="text" name="refraccion_od" class="form-control"
                                    value="{{ old('refraccion_od', $historialClinico->refraccion_od) }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Refracción OI</label>
                                <input type="text" name="refraccion_oi" class="form-control"
                                    value="{{ old('refraccion_oi', $historialClinico->refraccion_oi) }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>DP OD</label>
                                <input type="text" name="rx_final_dp_od" class="form-control"
                                    value="{{ old('rx_final_dp_od', $historialClinico->rx_final_dp_od) }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>DP OI</label>
                                <input type="text" name="rx_final_dp_oi" class="form-control"
                                    value="{{ old('rx_final_dp_oi', $historialClinico->rx_final_dp_oi) }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>AV VL OD</label>
                                <input type="text" name="rx_final_av_vl_od" class="form-control"
                                    value="{{ old('rx_final_av_vl_od', $historialClinico->rx_final_av_vl_od) }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>AV VL OI</label>
                                <input type="text" name="rx_final_av_vl_oi" class="form-control"
                                    value="{{ old('rx_final_av_vl_oi', $historialClinico->rx_final_av_vl_oi) }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>AV VP OD</label>
                                <input type="text" name="rx_final_av_vp_od" class="form-control"
                                    value="{{ old('rx_final_av_vp_od', $historialClinico->rx_final_av_vp_od) }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>AV VP OI</label>
                                <input type="text" name="rx_final_av_vp_oi" class="form-control"
                                    value="{{ old('rx_final_av_vp_oi', $historialClinico->rx_final_av_vp_oi) }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>ADD</label>
                                <input type="text" name="add" class="form-control" 
                                    value="{{ old('add', $historialClinico->add) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Diagnóstico, Tratamiento y Cotización --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#diagnostico">
                    <h5 class="mb-0">
                        <i class="fas fa-file-medical mr-2"></i> Diagnóstico y Cotización
                    </h5>
                </div>
                <div id="diagnostico" class="collapse show">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-12">
                                <label>Diagnóstico</label>
                                <textarea name="diagnostico" class="form-control" rows="3">{{ old('diagnostico', $historialClinico->diagnostico) }}</textarea>
                            </div>
                            <div class="form-group col-12">
                                <label>Tratamiento</label>
                                <textarea name="tratamiento" class="form-control" rows="3">{{ old('tratamiento', $historialClinico->tratamiento) }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Cotización</label>
                                <input type="text" name="cotizacion" class="form-control" 
                                    value="{{ old('cotizacion', $historialClinico->cotizacion) }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Próxima Consulta (en meses)</label>
                                <div class="input-group">
                                    <input type="number" id="meses_proxima_consulta" class="form-control" min="1" step="1">
                                    <select id="meses_predefinidos" class="form-control">
                                        <option value="">Seleccione meses</option>
                                        <option value="3">3 meses</option>
                                        <option value="6">6 meses</option>
                                        <option value="9">9 meses</option>
                                    </select>
                                    <input type="hidden" name="proxima_consulta" id="proxima_consulta" value="{{ old('proxima_consulta', $historialClinico->proxima_consulta) }}">
                                </div>
                            </div>
                            <input type="hidden" name="usuario_id" value="{{ old('usuario_id', $historialClinico->usuario_id) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="text-right">
                <a href="{{ route('historiales_clinicos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> CANCELAR
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> GUARDAR CAMBIOS
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    /* Convertir todo el texto a mayúsculas */
    .card-title,
    .card-header,
    label,
    input,
    textarea,
    select,
    .btn,
    h1, h2, h3,
    .form-control {
        text-transform: uppercase !important;
    }

    .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .text-danger {
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Convertir input a mayúsculas mientras se escribe
        $('input[type="text"], textarea').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Actualizar edad automáticamente cuando cambia la fecha de nacimiento
        $('#fecha_nacimiento').on('change', function() {
            let fechaNacimiento = new Date($(this).val());
            let hoy = new Date();
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            let mes = hoy.getMonth() - fechaNacimiento.getMonth();
            
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }
            
            $('#edad').val(edad);
        });

        // Función para calcular la próxima consulta basada en la fecha de registro
        function calcularProximaConsulta(meses) {
            if (!meses) return;
            let fechaRegistro = new Date($('#fecha').val());
            if (isNaN(fechaRegistro.getTime())) {
                alert('Por favor, primero seleccione una fecha de registro válida');
                return;
            }
            fechaRegistro.setMonth(fechaRegistro.getMonth() + parseInt(meses));
            return fechaRegistro.toISOString().split('T')[0];
        }

        // Manejar cambios en el input de meses
        $('#meses_proxima_consulta').on('input', function() {
            let meses = $(this).val();
            $('#meses_predefinidos').val(''); // Limpiar el select
            $('#proxima_consulta').val(calcularProximaConsulta(meses));
        });

        // Manejar cambios en el select de meses predefinidos
        $('#meses_predefinidos').on('change', function() {
            let meses = $(this).val();
            if (meses) {
                $('#meses_proxima_consulta').val(meses);
                $('#proxima_consulta').val(calcularProximaConsulta(meses));
            }
        });

        // Recalcular próxima consulta cuando cambie la fecha de registro
        $('#fecha').on('change', function() {
            let meses = $('#meses_proxima_consulta').val();
            if (meses) {
                $('#proxima_consulta').val(calcularProximaConsulta(meses));
            }
        });

        // Si hay una próxima consulta ya establecida, calcular los meses desde la fecha de registro
        let proximaConsulta = $('#proxima_consulta').val();
        let fechaRegistro = new Date($('#fecha').val());
        if (proximaConsulta && !isNaN(fechaRegistro.getTime())) {
            let fechaProxima = new Date(proximaConsulta);
            let meses = (fechaProxima.getFullYear() - fechaRegistro.getFullYear()) * 12 + 
                       (fechaProxima.getMonth() - fechaRegistro.getMonth());
            $('#meses_proxima_consulta').val(meses);
        }
    });
</script>
@stop
