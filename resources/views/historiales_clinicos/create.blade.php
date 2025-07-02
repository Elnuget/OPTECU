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
                                <label for="fecha">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="{{ date('Y-m-d') }}">
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
                        <div class="form-row mb-4">
                            <div class="form-group col-md-12">
                                <label for="buscar_paciente">Buscar Paciente Existente</label>
                                <input type="text" id="buscar_paciente" class="form-control" placeholder="Escriba el nombre o apellido del paciente" list="pacientes_existentes">
                                <datalist id="pacientes_existentes">
                                    @foreach($nombresCompletos as $registro)
                                        <option value="{{ $registro['completo'] }}" data-nombre="{{ $registro['nombre'] }}" data-apellido="{{ $registro['apellido'] }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nombres">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control">
                            </div>                            <div class="form-group col-md-4">
                                <label for="cedula">Cédula</label>
                                <input type="text" name="cedula" id="cedula" class="form-control" list="cedulas_existentes" placeholder="Seleccione o escriba una cédula" autocomplete="off">
                                <datalist id="cedulas_existentes">
                                    @foreach($cedulas as $cedula)
                                        <option value="{{ $cedula }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edad">Edad</label>
                                <input type="number" name="edad" id="edad" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                            </div>                            <div class="form-group col-md-4">
                                <label for="celular">Celular</label>
                                <input type="text" name="celular" id="celular" class="form-control" list="celulares_existentes" placeholder="Seleccione o escriba un número de celular" autocomplete="off">
                                <datalist id="celulares_existentes">
                                    @foreach($celulares as $celular)
                                        <option value="{{ $celular }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ocupacion">Ocupación</label>
                                <input type="text" name="ocupacion" id="ocupacion" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="proxima_consulta">Próxima Consulta</label>
                                <input type="date" name="proxima_consulta" id="proxima_consulta" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="empresa_id">Empresa</label>
                                <select name="empresa_id" id="empresa_id" class="form-control">
                                    <option value="">Seleccione una empresa...</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
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
                        <small class="text-muted ml-2">(Al buscar un paciente, se cargarán los datos de su última receta)</small>
                    </h5>
                </div>
                <div id="prescripcion" class="collapse">
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
                                        <td><input type="text" name="od_esfera" class="form-control"></td>
                                        <td><input type="text" name="od_cilindro" class="form-control"></td>
                                        <td><input type="text" name="od_eje" class="form-control"></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">OI</td>
                                        <td><input type="text" name="oi_esfera" class="form-control"></td>
                                        <td><input type="text" name="oi_cilindro" class="form-control"></td>
                                        <td><input type="text" name="oi_eje" class="form-control"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add">ADD</label>
                                    <input type="text" name="add" id="add" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dp">DP pl/pc</label>
                                    <input type="text" name="dp" id="dp" class="form-control">
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
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="diagnostico[]" value="Astigmatismo" id="astigmatismo">
                                            <label class="form-check-label" for="astigmatismo">
                                                Astigmatismo
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="diagnostico[]" value="Hipermetropía" id="hipermetropia">
                                            <label class="form-check-label" for="hipermetropia">
                                                Hipermetropía
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="diagnostico[]" value="Miopía" id="miopia">
                                            <label class="form-check-label" for="miopia">
                                                Miopía
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="diagnostico[]" value="Presbicia" id="presbicia">
                                            <label class="form-check-label" for="presbicia">
                                                Presbicia
                                            </label>
                                        </div>
                                        <!-- Campo oculto para almacenar diagnósticos como string -->
                                        <input type="hidden" name="diagnostico" id="diagnostico_string" value="">
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
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3"></textarea>
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
                                    <input type="text" name="motivo_consulta" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Enfermedad Actual</label>
                                    <input type="text" name="enfermedad_actual" class="form-control">
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
                                    <input list="antecedentesPersonalesOcularesList" name="antecedentes_personales_oculares" class="form-control">
                                    <datalist id="antecedentesPersonalesOcularesList">
                                        @foreach($antecedentesPersonalesOculares as $antecedente)
                                            <option value="{{ $antecedente }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Personales Generales</label>
                                    <input list="antecedentesPersonalesGeneralesList" name="antecedentes_personales_generales" class="form-control">
                                    <datalist id="antecedentesPersonalesGeneralesList">
                                        @foreach($antecedentesPersonalesGenerales as $antecedente)
                                            <option value="{{ $antecedente }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Familiares Oculares</label>
                                    <input list="antecedentesFamiliaresOcularesList" name="antecedentes_familiares_oculares" class="form-control">
                                    <datalist id="antecedentesFamiliaresOcularesList">
                                        @foreach($antecedentesFamiliaresOculares as $antecedente)
                                            <option value="{{ $antecedente }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Antecedentes Familiares Generales</label>
                                    <input list="antecedentesFamiliaresGeneralesList" name="antecedentes_familiares_generales" class="form-control">
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
                                    <h6>Agudeza Visual VL sin Corrección</h6>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>OD</label>
                                            <input type="text" name="agudeza_visual_vl_sin_correccion_od" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>OI</label>
                                            <input type="text" name="agudeza_visual_vl_sin_correccion_oi" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>AO</label>
                                            <input type="text" name="agudeza_visual_vl_sin_correccion_ao" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Agudeza Visual VP sin Corrección</h6>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>OD</label>
                                            <input type="text" name="agudeza_visual_vp_sin_correccion_od" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>OI</label>
                                            <input type="text" name="agudeza_visual_vp_sin_correccion_oi" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>AO</label>
                                            <input type="text" name="agudeza_visual_vp_sin_correccion_ao" class="form-control">
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
                                            <input type="text" name="ph_od" class="form-control">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>PH OI</label>
                                            <input type="text" name="ph_oi" class="form-control">
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
            </div>

            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Guardar Historial Clínico
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
    .text-danger {
        font-weight: bold;
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
    
        // Convertir input a mayúsculas mientras se escribe
        $('input[type="text"], textarea').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Función para actualizar el campo diagnóstico basado en los checkboxes seleccionados
        function actualizarDiagnosticoString() {
            const diagnosticoSeleccionados = $('input[name="diagnostico[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            $('#diagnostico_string').val(diagnosticoSeleccionados.join(', '));
        }
        
        // Inicializar el campo diagnóstico al cargar la página
        actualizarDiagnosticoString();
        
        // Manejar la selección de paciente existente
        $('#buscar_paciente').on('input', function() {
            const selectedOption = $('#pacientes_existentes option[value="' + this.value + '"]');
            if (selectedOption.length) {
                const nombre = selectedOption.data('nombre');
                const apellido = selectedOption.data('apellido');
                
                $('#nombres').val(nombre);
                $('#apellidos').val(apellido);
                
                // Cargar datos adicionales del paciente
                cargarDatosPersonales('nombres', nombre);
            }
        });

        // Abrir automáticamente la sección de receta cuando se carga la página o se selecciona un paciente
        function mostrarSeccionReceta() {
            // Asegurarse de que la sección esté abierta
            if (!$('#prescripcion').hasClass('show')) {
                $('#prescripcion').collapse('show');
            }
        }

        // Convertir checkboxes de diagnóstico a string antes de enviar el formulario
        $('form').on('submit', function(e) {
            actualizarDiagnosticoString();
        });
        
        // Actualizar campo oculto cuando se seleccionen/deseleccionen checkboxes
        $('input[name="diagnostico[]"]').on('change', function() {
            actualizarDiagnosticoString();
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
                loadingIndicator.textContent = 'Cargando datos y última receta...';
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
                    console.log('Respuesta de la API:', data); // Añadir para depuración

                    // Remover indicador de carga
                    const loadingIndicator = elemento.parentNode.querySelector('.loading-indicator');
                    if (loadingIndicator) {
                        loadingIndicator.remove();
                    }

                    if (data.success && data.historial) {
                        // Cargar todos los datos del historial, excepto la fecha
                        const historial = data.historial;
                        
                        // Abrir sección de receta automáticamente cuando se cargan datos
                        mostrarSeccionReceta();
                        
                        // Indicador visual de que se cargó la receta
                        const tieneReceta = historial.od_esfera || historial.oi_esfera;
                        if (tieneReceta) {
                            // Mostrar notificación temporal
                            const notificacion = document.createElement('div');
                            notificacion.classList.add('alert', 'alert-success', 'alert-dismissible', 'fade', 'show', 'mt-2', 'mb-0');
                            notificacion.setAttribute('role', 'alert');
                            notificacion.innerHTML = `
                                <strong><i class="fas fa-check-circle mr-2"></i>Receta cargada:</strong> Se han cargado los datos de la última receta del paciente.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            `;
                            
                            // Agregar notificación después del título de la sección Receta
                            const seccionReceta = document.querySelector('#prescripcion .card-body');
                            seccionReceta.insertBefore(notificacion, seccionReceta.firstChild);
                            
                            // Auto-eliminar después de 5 segundos
                            setTimeout(() => {
                                notificacion.classList.remove('show');
                                setTimeout(() => notificacion.remove(), 150);
                            }, 5000);
                        }
                        
                        // Autocompletar campos excepto el que generó la búsqueda y la fecha
                        // Datos personales
                        if (campo !== 'nombres') document.getElementById('nombres').value = historial.nombres || '';
                        if (campo !== 'apellidos') document.getElementById('apellidos').value = historial.apellidos || '';
                        if (campo !== 'cedula') document.getElementById('cedula').value = historial.cedula || '';
                        if (campo !== 'celular') document.getElementById('celular').value = historial.celular || '';
                        document.getElementById('edad').value = historial.edad || '';
                        
                        // Formatear y establecer la fecha de nacimiento si existe
                        if (historial.fecha_nacimiento) {
                            // Asegurarse de que la fecha esté en formato YYYY-MM-DD
                            const fechaNacimiento = new Date(historial.fecha_nacimiento);
                            if (!isNaN(fechaNacimiento.getTime())) {
                                const fechaFormateada = fechaNacimiento.toISOString().split('T')[0];
                                document.getElementById('fecha_nacimiento').value = fechaFormateada;
                            }
                        } else {
                            document.getElementById('fecha_nacimiento').value = '';
                        }
                        
                        document.getElementById('ocupacion').value = historial.ocupacion || '';
                        
                        // Próxima consulta
                        document.getElementById('proxima_consulta').value = historial.proxima_consulta ? new Date(historial.proxima_consulta).toISOString().split('T')[0] : '';
                        
                        // Empresa
                        if (historial.empresa_id) {
                            document.getElementById('empresa_id').value = historial.empresa_id;
                        } else {
                            document.getElementById('empresa_id').value = '';
                        }
                        
                        // Motivo de consulta y enfermedad actual
                        document.getElementsByName('motivo_consulta')[0].value = historial.motivo_consulta || '';
                        document.getElementsByName('enfermedad_actual')[0].value = historial.enfermedad_actual || '';
                        
                        // Antecedentes
                        document.getElementsByName('antecedentes_personales_oculares')[0].value = historial.antecedentes_personales_oculares || '';
                        document.getElementsByName('antecedentes_personales_generales')[0].value = historial.antecedentes_personales_generales || '';
                        document.getElementsByName('antecedentes_familiares_oculares')[0].value = historial.antecedentes_familiares_oculares || '';
                        document.getElementsByName('antecedentes_familiares_generales')[0].value = historial.antecedentes_familiares_generales || '';
                        
                        // Agudeza visual y PH
                        document.getElementsByName('agudeza_visual_vl_sin_correccion_od')[0].value = historial.agudeza_visual_vl_sin_correccion_od || '';
                        document.getElementsByName('agudeza_visual_vl_sin_correccion_oi')[0].value = historial.agudeza_visual_vl_sin_correccion_oi || '';
                        document.getElementsByName('agudeza_visual_vl_sin_correccion_ao')[0].value = historial.agudeza_visual_vl_sin_correccion_ao || '';
                        document.getElementsByName('agudeza_visual_vp_sin_correccion_od')[0].value = historial.agudeza_visual_vp_sin_correccion_od || '';
                        document.getElementsByName('agudeza_visual_vp_sin_correccion_oi')[0].value = historial.agudeza_visual_vp_sin_correccion_oi || '';
                        document.getElementsByName('agudeza_visual_vp_sin_correccion_ao')[0].value = historial.agudeza_visual_vp_sin_correccion_ao || '';
                        document.getElementsByName('ph_od')[0].value = historial.ph_od || '';
                        document.getElementsByName('ph_oi')[0].value = historial.ph_oi || '';
                        document.getElementsByName('optotipo')[0].value = historial.optotipo || '';
                        
                        // Lensometría
                        document.getElementsByName('lensometria_od')[0].value = historial.lensometria_od || '';
                        document.getElementsByName('lensometria_oi')[0].value = historial.lensometria_oi || '';
                        document.getElementsByName('tipo_lente')[0].value = historial.tipo_lente || '';
                        document.getElementsByName('material')[0].value = historial.material || '';
                        document.getElementsByName('filtro')[0].value = historial.filtro || '';
                        document.getElementsByName('tiempo_uso')[0].value = historial.tiempo_uso || '';
                        
                        // Diagnóstico y tratamiento
                        // El diagnóstico se maneja a través de checkboxes más adelante
                        document.getElementById('proxima_consulta').value = historial.proxima_consulta ? new Date(historial.proxima_consulta).toISOString().split('T')[0] : '';
                        
                        // Receta - Valores OD y OI
                        document.getElementsByName('od_esfera')[0].value = historial.od_esfera || '';
                        document.getElementsByName('od_cilindro')[0].value = historial.od_cilindro || '';
                        document.getElementsByName('od_eje')[0].value = historial.od_eje || '';
                        document.getElementsByName('oi_esfera')[0].value = historial.oi_esfera || '';
                        document.getElementsByName('oi_cilindro')[0].value = historial.oi_cilindro || '';
                        document.getElementsByName('oi_eje')[0].value = historial.oi_eje || '';
                        
                        // Poblar ADD y DP
                        document.getElementById('add').value = historial.add || '';
                        document.getElementById('dp').value = historial.dp || '';
                        
                        // Si hay diagnóstico, marcar los checkboxes correspondientes
                        if (historial.diagnostico) {
                            const diagnosticos = historial.diagnostico.split(',').map(d => d.trim().toUpperCase());
                            
                            // Limpiar selecciones previas
                            $('input[name="diagnostico[]"]').prop('checked', false);
                            
                            // Marcar las casillas correspondientes
                            $('input[name="diagnostico[]"]').each(function() {
                                const valorCheckbox = $(this).val().toUpperCase();
                                if (diagnosticos.some(d => d === valorCheckbox)) {
                                    $(this).prop('checked', true);
                                }
                            });
                            
                            // Actualizar el campo oculto
                            $('#diagnostico_string').val(historial.diagnostico);
                        }
                        
                        // Poblar observaciones
                        document.getElementById('observaciones').value = historial.observaciones || '';
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
        }        // Eventos para autocompletado
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
