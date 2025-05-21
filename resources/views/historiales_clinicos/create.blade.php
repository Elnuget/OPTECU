@extends('adminlte::page')

@section('title', 'Crear Historial Clínico')

@section('content_header')
    <h1 class="mb-3">Crear Historial Clínico</h1>
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

        <form action="{{ route('historiales_clinicos.store') }}" method="POST">
            @csrf

            {{-- FECHA DE REGISTRO --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#fechaRegistro" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt mr-2"></i> Fecha de Registro
                    </h5>
                </div>
                <div id="fechaRegistro" class="collapse">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="fecha">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                <div id="datosPaciente" class="collapse">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" id="nombres" class="form-control" required list="nombres_existentes" placeholder="Seleccione o escriba un nombre">
                                <datalist id="nombres_existentes">
                                    @foreach($nombres as $nombre)
                                        <option value="{{ $nombre }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" required list="apellidos_existentes" placeholder="Seleccione o escriba un apellido">
                                <datalist id="apellidos_existentes">
                                    @foreach($apellidos as $apellido)
                                        <option value="{{ $apellido }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="cedula">Cédula</label> <!-- Removido text-danger -->
                                <input type="text" name="cedula" id="cedula" class="form-control" list="cedulas_existentes" placeholder="Seleccione o escriba una cédula">
                                <datalist id="cedulas_existentes">
                                    @foreach($cedulas as $cedula)
                                        <option value="{{ $cedula }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edad">Edad <span class="text-danger">*</span></label>
                                <input type="number" name="edad" id="edad" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="celular">Celular <span class="text-danger">*</span></label>
                                <input type="text" name="celular" id="celular" class="form-control" required list="celulares_existentes" placeholder="Seleccione o escriba un número de celular">
                                <datalist id="celulares_existentes">
                                    @foreach($celulares as $celular)
                                        <option value="{{ $celular }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ocupacion">Ocupación <span class="text-danger">*</span></label>
                                <input type="text" name="ocupacion" id="ocupacion" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                <label>Motivo de Consulta <span class="text-danger">*</span></label>
                                <input type="text" name="motivo_consulta" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Enfermedad Actual <span class="text-danger">*</span></label>
                                <input type="text" name="enfermedad_actual" class="form-control" required>
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
                                <label>Antecedentes Personales Oculares <span class="text-danger">*</span></label>
                                <input list="antecedentesPersonalesOcularesList" name="antecedentes_personales_oculares" class="form-control" required>
                                <datalist id="antecedentesPersonalesOcularesList">
                                    @foreach($antecedentesPersonalesOculares as $antecedente)
                                        <option value="{{ $antecedente }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Antecedentes Personales Generales <span class="text-danger">*</span></label>
                                <input list="antecedentesPersonalesGeneralesList" name="antecedentes_personales_generales" class="form-control" required>
                                <datalist id="antecedentesPersonalesGeneralesList">
                                    @foreach($antecedentesPersonalesGenerales as $antecedente)
                                        <option value="{{ $antecedente }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Antecedentes Familiares Oculares <span class="text-danger">*</span></label>
                                <input list="antecedentesFamiliaresOcularesList" name="antecedentes_familiares_oculares" class="form-control" required>
                                <datalist id="antecedentesFamiliaresOcularesList">
                                    @foreach($antecedentesFamiliaresOculares as $antecedente)
                                        <option value="{{ $antecedente }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Antecedentes Familiares Generales <span class="text-danger">*</span></label>
                                <input list="antecedentesFamiliaresGeneralesList" name="antecedentes_familiares_generales" class="form-control" required>
                                <datalist id="antecedentesFamiliaresGeneralesList">
                                    @foreach($antecedentesFamiliaresGenerales as $antecedente)
                                        <option value="{{ $antecedente }}">
                                    @endforeach
                                </datalist>
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
                                <h6>Agudeza Visual VL sin Corrección <span class="text-danger">*</span></h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>OD <span class="text-danger">*</span></label>
                                        <input type="text" name="agudeza_visual_vl_sin_correccion_od" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>OI <span class="text-danger">*</span></label>
                                        <input type="text" name="agudeza_visual_vl_sin_correccion_oi" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>AO <span class="text-danger">*</span></label>
                                        <input type="text" name="agudeza_visual_vl_sin_correccion_ao" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Agudeza Visual VP sin Corrección <span class="text-danger">*</span></h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>OD <span class="text-danger">*</span></label>
                                        <input type="text" name="agudeza_visual_vp_sin_correccion_od" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>OI <span class="text-danger">*</span></label>
                                        <input type="text" name="agudeza_visual_vp_sin_correccion_oi" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>AO <span class="text-danger">*</span></label>
                                        <input type="text" name="agudeza_visual_vp_sin_correccion_ao" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Pin Hole (PH) <span class="text-danger">*</span></h6>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>PH OD <span class="text-danger">*</span></label>
                                        <input type="text" name="ph_od" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>PH OI <span class="text-danger">*</span></label>
                                        <input type="text" name="ph_oi" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Optotipo</label> <!-- Removido text-danger -->
                            <textarea name="optotipo" class="form-control" rows="2"></textarea>
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
                                <h6>Lensometría</h6> <!-- Removido text-danger -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>OD</label> <!-- Removido text-danger -->
                                        <input type="text" name="lensometria_od" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>OI</label> <!-- Removido text-danger -->
                                        <input type="text" name="lensometria_oi" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de Lente</label> <!-- Removido text-danger y required -->
                                    <input type="text" name="tipo_lente" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Material</label> <!-- Removido text-danger y required -->
                                    <input type="text" name="material" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Filtro</label> <!-- Removido text-danger y required -->
                                    <input type="text" name="filtro" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tiempo de Uso</label> <!-- Ya estaba como no obligatorio -->
                                    <input type="text" name="tiempo_uso" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RX FINAL --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#rxFinal">
                    <h5 class="mb-0">
                        <i class="fas fa-prescription mr-2"></i> Rx Final
                    </h5>
                </div>
                <div id="rxFinal" class="collapse">
                    <div class="card-body">
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6">
                                <label>Refracción OD <span class="text-danger">*</span></label>
                                <input type="text" name="refraccion_od" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Refracción OI <span class="text-danger">*</span></label>
                                <input type="text" name="refraccion_oi" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>DP OD <span class="text-danger">*</span></label>
                                <input type="text" name="rx_final_dp_od" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>DP OI <span class="text-danger">*</span></label>
                                <input type="text" name="rx_final_dp_oi" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>AV VL OD <span class="text-danger">*</span></label>
                                <input type="text" name="rx_final_av_vl_od" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>AV VL OI <span class="text-danger">*</span></label>
                                <input type="text" name="rx_final_av_vl_oi" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>AV VP OD <span class="text-danger">*</span></label>
                                <input type="text" name="rx_final_av_vp_od" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>AV VP OI <span class="text-danger">*</span></label>
                                <input type="text" name="rx_final_av_vp_oi" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>ADD</label> <!-- Removido text-danger -->
                                <input type="text" name="add" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DIAGNÓSTICO, TRATAMIENTO Y COTIZACIÓN --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#diagnostico">
                    <h5 class="mb-0">
                        <i class="fas fa-file-medical mr-2"></i> Diagnóstico y Cotización
                    </h5>
                </div>
                <div id="diagnostico" class="collapse">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-12">
                                <label>Diagnóstico <span class="text-danger">*</span></label>
                                <textarea name="diagnostico" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group col-12">
                                <label>Tratamiento <span class="text-danger">*</span></label>
                                <textarea name="tratamiento" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Cotización</label>
                                <input type="text" name="cotizacion" class="form-control">
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
                                    <input type="hidden" name="proxima_consulta" id="proxima_consulta">
                                </div>
                            </div>
                            <input type="hidden" name="usuario_id" value="{{ Auth::id() }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTONES DE ACCIÓN --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('historiales_clinicos.index') }}" class="btn btn-secondary mr-2">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Guardar
                </button>
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

        // Función para cargar datos personales desde historiales previos
        function cargarDatosPersonales(campo, valor) {
            if (!valor) return;

            // Mostrar indicador de carga
            const elemento = document.getElementById(campo);
            if (!elemento.nextElementSibling || !elemento.nextElementSibling.classList.contains('loading-indicator')) {
                const loadingIndicator = document.createElement('small');
                loadingIndicator.classList.add('loading-indicator', 'text-muted', 'ml-2');
                loadingIndicator.textContent = 'Cargando datos...';
                elemento.parentNode.appendChild(loadingIndicator);
            }

            // Hacer petición AJAX para obtener datos del último historial
            fetch(`/api/historiales-clinicos/buscar-por/${campo}/${encodeURIComponent(valor)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al obtener datos');
                    }
                    return response.json();
                })
                .then(data => {
                    // Remover indicador de carga
                    const loadingIndicator = elemento.parentNode.querySelector('.loading-indicator');
                    if (loadingIndicator) {
                        loadingIndicator.remove();
                    }

                    if (data.success && data.historial) {
                        // Autocompletar campos excepto el que generó la búsqueda
                        if (campo !== 'nombres') {
                            document.getElementById('nombres').value = data.historial.nombres || '';
                        }
                        if (campo !== 'apellidos') {
                            document.getElementById('apellidos').value = data.historial.apellidos || '';
                        }
                        if (campo !== 'cedula') {
                            document.getElementById('cedula').value = data.historial.cedula || '';
                        }
                        if (campo !== 'celular') {
                            document.getElementById('celular').value = data.historial.celular || '';
                        }
                        // También podríamos completar otros campos como edad y fecha_nacimiento si es necesario
                        if (data.historial.edad) {
                            document.getElementById('edad').value = data.historial.edad;
                        }
                        if (data.historial.fecha_nacimiento) {
                            document.getElementById('fecha_nacimiento').value = data.historial.fecha_nacimiento;
                        }
                        if (data.historial.ocupacion) {
                            document.getElementById('ocupacion').value = data.historial.ocupacion;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Remover indicador de carga en caso de error
                    const loadingIndicator = elemento.parentNode.querySelector('.loading-indicator');
                    if (loadingIndicator) {
                        loadingIndicator.remove();
                    }
                });
        }

        // Añadir eventos para los campos de datos personales
        $('#nombres').on('change', function() {
            if (this.value.trim()) {
                cargarDatosPersonales('nombres', this.value);
            }
        });

        $('#apellidos').on('change', function() {
            if (this.value.trim()) {
                cargarDatosPersonales('apellidos', this.value);
            }
        });

        $('#cedula').on('change', function() {
            if (this.value.trim()) {
                cargarDatosPersonales('cedula', this.value);
            }
        });

        $('#celular').on('change', function() {
            if (this.value.trim()) {
                cargarDatosPersonales('celular', this.value);
            }
        });
    });
</script>
@stop
