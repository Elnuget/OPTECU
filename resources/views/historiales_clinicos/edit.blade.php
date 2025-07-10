@extends('adminlte::page')

@section('title', 'Editar Historial Clínico')

@section('content_header')
    <h1 class="mb-3">Editar Historial Clínico</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('historiales_clinicos.update', $historialClinico->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- FECHA DE REGISTRO --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#fechaRegistro" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt mr-2"></i> Fecha de Registro
                    </h5>
                </div>
                <div id="fechaRegistro" class="collapse show">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="fecha">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="{{ old('fecha', \Carbon\Carbon::parse($historialClinico->fecha)->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DATOS DEL PACIENTE --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#datosPaciente" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-user mr-2"></i> Datos del Paciente
                    </h5>
                </div>
                <div id="datosPaciente" class="collapse show">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nombres">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" value="{{ old('nombres', $historialClinico->nombres) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" value="{{ old('apellidos', $historialClinico->apellidos) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="cedula">RUT</label>
                                <input type="text" name="cedula" id="cedula" class="form-control" value="{{ old('cedula', $historialClinico->cedula) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edad">Edad</label>
                                <input type="number" name="edad" id="edad" class="form-control" value="{{ old('edad', $historialClinico->edad) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $historialClinico->fecha_nacimiento ? \Carbon\Carbon::parse($historialClinico->fecha_nacimiento)->format('Y-m-d') : '') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="celular">Celular</label>
                                <input type="text" name="celular" id="celular" class="form-control" value="{{ old('celular', $historialClinico->celular) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" name="correo" id="correo" class="form-control" value="{{ old('correo', $historialClinico->correo) }}" placeholder="ejemplo@correo.com">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="direccion">Dirección</label>
                                <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion', $historialClinico->direccion) }}" placeholder="Ingrese la dirección">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ocupacion">Ocupación</label>
                                <input type="text" name="ocupacion" id="ocupacion" class="form-control" value="{{ old('ocupacion', $historialClinico->ocupacion) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="proxima_consulta">Próxima Consulta</label>
                                <input type="date" name="proxima_consulta" id="proxima_consulta" class="form-control" value="{{ old('proxima_consulta', $historialClinico->proxima_consulta ? \Carbon\Carbon::parse($historialClinico->proxima_consulta)->format('Y-m-d') : '') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="empresa_id">Empresa</label>
                                @if (!$isUserAdmin && $userEmpresaId)
                                    <select name="empresa_id" id="empresa_id" class="form-control" readonly disabled>
                                        @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}" {{ $userEmpresaId == $empresa->id ? 'selected' : '' }}>
                                                {{ $empresa->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="empresa_id" value="{{ $userEmpresaId }}">
                                    <small class="text-muted">Tu usuario está asociado a esta empresa y no puede ser cambiada.</small>
                                @else
                                    <select name="empresa_id" id="empresa_id" class="form-control">
                                        <option value="">Seleccione una empresa...</option>
                                        @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}" {{ old('empresa_id', $historialClinico->empresa_id) == $empresa->id ? 'selected' : '' }}>
                                                {{ $empresa->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PRESCRIPCIÓN / RECETA --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#prescripcion" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-prescription mr-2"></i> Receta
                    </h5>
                </div>
                <div id="prescripcion" class="collapse show">
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th></th>
                                        <th class="text-center">Esfera</th>
                                        <th class="text-center">Cilindro</th>
                                        <th class="text-center">Eje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold">OD</td>
                                        <td><input type="text" name="od_esfera" class="form-control" value="{{ old('od_esfera', $receta->od_esfera ?? '') }}"></td>
                                        <td><input type="text" name="od_cilindro" class="form-control" value="{{ old('od_cilindro', $receta->od_cilindro ?? '') }}"></td>
                                        <td><input type="text" name="od_eje" class="form-control" value="{{ old('od_eje', $receta->od_eje ?? '') }}"></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">OI</td>
                                        <td><input type="text" name="oi_esfera" class="form-control" value="{{ old('oi_esfera', $receta->oi_esfera ?? '') }}"></td>
                                        <td><input type="text" name="oi_cilindro" class="form-control" value="{{ old('oi_cilindro', $receta->oi_cilindro ?? '') }}"></td>
                                        <td><input type="text" name="oi_eje" class="form-control" value="{{ old('oi_eje', $receta->oi_eje ?? '') }}"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add">ADD</label>
                                    <input type="text" name="add" id="add" class="form-control" value="{{ old('add', $historialClinico->add) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dp">DP pl/pc</label>
                                    <input type="text" name="dp" id="dp" class="form-control" value="{{ old('dp', $receta->dp ?? '') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Diagnóstico:</h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $diagnosticosGuardados = explode(',', strtoupper($historialClinico->diagnostico ?? ''));
                                            $diagnosticosGuardados = array_map('trim', $diagnosticosGuardados);
                                        @endphp
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Astigmatismo" id="astigmatismo" {{ in_array('ASTIGMATISMO', $diagnosticosGuardados) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="astigmatismo">Astigmatismo</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Hipermetropía" id="hipermetropia" {{ in_array('HIPERMETROPÍA', $diagnosticosGuardados) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="hipermetropia">Hipermetropía</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Miopía" id="miopia" {{ in_array('MIOPÍA', $diagnosticosGuardados) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="miopia">Miopía</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Presbicia" id="presbicia" {{ in_array('PRESBICIA', $diagnosticosGuardados) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="presbicia">Presbicia</label>
                                        </div>
                                        <input type="hidden" name="diagnostico" id="diagnostico_string" value="{{ old('diagnostico', $historialClinico->diagnostico) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">NOTA IMPORTANTE:</h6>
                                        <p class="card-text">
                                            El período de adaptación del lente óptico varía de 2 a 3 semanas, puede tener molestias como:
                                            mareos, dolor de cabeza, visión a desnivel.
                                            Estas desaparecerán a medida que se adapte al lente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <label for="observaciones">Observaciones:</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones', $receta->observaciones ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTÓN PARA MOSTRAR/OCULTAR SECCIONES OPCIONALES --}}
            <div class="text-center mb-4">
                <button type="button" id="btnMostrarOpcionales" class="btn btn-outline-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Mostrar información adicional
                </button>
            </div>

            {{-- SECCIONES OPCIONALES --}}
            <div id="seccionesOpcionales" style="display: none;">
                {{-- MOTIVO DE CONSULTA Y ENFERMEDAD ACTUAL --}}
                <div class="card mb-4">
                    <div class="card-header" data-toggle="collapse" data-target="#motivoConsulta">
                        <h5 class="mb-0">
                            <i class="fas fa-notes-medical mr-2"></i> Motivo de Consulta y Enfermedad Actual
                        </h5>
                    </div>
                    <div id="motivoConsulta" class="collapse">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Motivo de Consulta</label>
                                    <input type="text" name="motivo_consulta" class="form-control" value="{{ old('motivo_consulta', $historialClinico->motivo_consulta) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Enfermedad Actual</label>
                                    <input type="text" name="enfermedad_actual" class="form-control" value="{{ old('enfermedad_actual', $historialClinico->enfermedad_actual) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ANTECEDENTES --}}
                <div class="card mb-4">
                    <div class="card-header" data-toggle="collapse" data-target="#antecedentes">
                        <h5 class="mb-0">
                            <i class="fas fa-history mr-2"></i> Antecedentes
                        </h5>
                    </div>
                    <div id="antecedentes" class="collapse">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Personales Oculares</label>
                                    <input name="antecedentes_personales_oculares" class="form-control" value="{{ old('antecedentes_personales_oculares', $historialClinico->antecedentes_personales_oculares) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Personales Generales</label>
                                    <input name="antecedentes_personales_generales" class="form-control" value="{{ old('antecedentes_personales_generales', $historialClinico->antecedentes_personales_generales) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Familiares Oculares</label>
                                    <input name="antecedentes_familiares_oculares" class="form-control" value="{{ old('antecedentes_familiares_oculares', $historialClinico->antecedentes_familiares_oculares) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Familiares Generales</label>
                                    <input name="antecedentes_familiares_generales" class="form-control" value="{{ old('antecedentes_familiares_generales', $historialClinico->antecedentes_familiares_generales) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AGUDEZA VISUAL Y PH --}}
                <div class="card mb-4">
                    <div class="card-header" data-toggle="collapse" data-target="#agudezaVisual">
                        <h5 class="mb-0">
                            <i class="fas fa-eye mr-2"></i> Agudeza Visual y PH
                        </h5>
                    </div>
                    <div id="agudezaVisual" class="collapse">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Agudeza Visual VL sin Corrección</h6>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>OD</label>
                                            <input type="text" name="agudeza_visual_vl_sin_correccion_od" class="form-control" value="{{ old('agudeza_visual_vl_sin_correccion_od', $historialClinico->agudeza_visual_vl_sin_correccion_od) }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>OI</label>
                                            <input type="text" name="agudeza_visual_vl_sin_correccion_oi" class="form-control" value="{{ old('agudeza_visual_vl_sin_correccion_oi', $historialClinico->agudeza_visual_vl_sin_correccion_oi) }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>AO</label>
                                            <input type="text" name="agudeza_visual_vl_sin_correccion_ao" class="form-control" value="{{ old('agudeza_visual_vl_sin_correccion_ao', $historialClinico->agudeza_visual_vl_sin_correccion_ao) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Agudeza Visual VP sin Corrección</h6>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>OD</label>
                                            <input type="text" name="agudeza_visual_vp_sin_correccion_od" class="form-control" value="{{ old('agudeza_visual_vp_sin_correccion_od', $historialClinico->agudeza_visual_vp_sin_correccion_od) }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>OI</label>
                                            <input type="text" name="agudeza_visual_vp_sin_correccion_oi" class="form-control" value="{{ old('agudeza_visual_vp_sin_correccion_oi', $historialClinico->agudeza_visual_vp_sin_correccion_oi) }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>AO</label>
                                            <input type="text" name="agudeza_visual_vp_sin_correccion_ao" class="form-control" value="{{ old('agudeza_visual_vp_sin_correccion_ao', $historialClinico->agudeza_visual_vp_sin_correccion_ao) }}">
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
                                            <input type="text" name="ph_od" class="form-control" value="{{ old('ph_od', $historialClinico->ph_od) }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>PH OI</label>
                                            <input type="text" name="ph_oi" class="form-control" value="{{ old('ph_oi', $historialClinico->ph_oi) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Optotipo</label>
                                <textarea name="optotipo" class="form-control" rows="2">{{ old('optotipo', $historialClinico->optotipo) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LENSOMETRÍA --}}
                <div class="card mb-4">
                    <div class="card-header" data-toggle="collapse" data-target="#lensometria">
                        <h5 class="mb-0">
                            <i class="fas fa-glasses mr-2"></i> Lensometría
                        </h5>
                    </div>
                    <div id="lensometria" class="collapse">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Lensometría</h6>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>OD</label>
                                            <input type="text" name="lensometria_od" class="form-control" value="{{ old('lensometria_od', $historialClinico->lensometria_od) }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>OI</label>
                                            <input type="text" name="lensometria_oi" class="form-control" value="{{ old('lensometria_oi', $historialClinico->lensometria_oi) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tipo de Lente</label>
                                        <input type="text" name="tipo_lente" class="form-control" value="{{ old('tipo_lente', $historialClinico->tipo_lente) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Material</label>
                                        <input type="text" name="material" class="form-control" value="{{ old('material', $historialClinico->material) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Filtro</label>
                                        <input type="text" name="filtro" class="form-control" value="{{ old('filtro', $historialClinico->filtro) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tiempo de Uso</label>
                                        <input type="text" name="tiempo_uso" class="form-control" value="{{ old('tiempo_uso', $historialClinico->tiempo_uso) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DIAGNÓSTICO Y TRATAMIENTO ADICIONAL --}}
                <div class="card mb-4">
                    <div class="card-header" data-toggle="collapse" data-target="#diagnosticoAdicional">
                        <h5 class="mb-0">
                            <i class="fas fa-file-medical mr-2"></i> Información Adicional
                        </h5>
                    </div>
                    <div id="diagnosticoAdicional" class="collapse">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tratamiento</label>
                                <textarea name="tratamiento" class="form-control" rows="3">{{ old('tratamiento', $historialClinico->tratamiento) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Cotización</label>
                                <textarea name="cotizacion" class="form-control" rows="3">{{ old('cotizacion', $historialClinico->cotizacion) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
                <a href="{{ route('historiales_clinicos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .card-header {
        background-color: #f8f9fa;
        transition: background-color 0.3s ease;
    }
    .card-header:hover {
        background-color: #e9ecef;
    }
    .form-group label {
        font-weight: 600;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Botón para mostrar/ocultar secciones opcionales
        $('#btnMostrarOpcionales').click(function() {
            const $seccionesOpcionales = $('#seccionesOpcionales');
            const $boton = $(this);
            
            if ($seccionesOpcionales.is(':visible')) {
                $seccionesOpcionales.slideUp();
                $boton.html('<i class="fas fa-plus-circle mr-2"></i>Mostrar información adicional');
            } else {
                $seccionesOpcionales.slideDown();
                $boton.html('<i class="fas fa-minus-circle mr-2"></i>Ocultar información adicional');
            }
        });

        // Función para actualizar el campo diagnóstico basado en los checkboxes seleccionados
        function actualizarDiagnosticoString() {
            const diagnosticoSeleccionados = $('input[type="checkbox"]:checked').map(function() {
                return this.value;
            }).get();
            
            $('#diagnostico_string').val(diagnosticoSeleccionados.join(', '));
        }
        
        // Manejar cambio en checkboxes de diagnóstico
        $('input[type="checkbox"]').on('change', function() {
            actualizarDiagnosticoString();
        });

        // Convertir campos de texto a mayúsculas
        $('input[type="text"], textarea').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Calcular edad desde fecha de nacimiento
        $('#fecha_nacimiento').on('change', function() {
            let fechaNacimiento = new Date($(this).val());
            if (!isNaN(fechaNacimiento.getTime())) {
                let hoy = new Date();
                let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                let m = hoy.getMonth() - fechaNacimiento.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                    edad--;
                }
                $('#edad').val(edad);
            }
        });
    });
</script>
@stop
