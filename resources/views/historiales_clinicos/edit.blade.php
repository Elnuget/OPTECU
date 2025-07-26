@extends('adminlte::page')

@section('title', 'Editar Historial Clínico')

@section('content_header')
    <h1 class="mb-3">Editar Historial Clínico</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- Mensajes de éxito --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Mensajes de error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

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

        {{-- Debug de la receta (solo en desarrollo) --}}
        @if (config('app.debug') && isset($receta))
            <div class="alert alert-info">
                <small><strong>Debug - Receta encontrada:</strong> ID: {{ $receta->id ?? 'N/A' }} | 
                OD: {{ $receta->od_esfera ?? 'N/A' }} {{ $receta->od_cilindro ?? 'N/A' }} x {{ $receta->od_eje ?? 'N/A' }} | 
                OI: {{ $receta->oi_esfera ?? 'N/A' }} {{ $receta->oi_cilindro ?? 'N/A' }} x {{ $receta->oi_eje ?? 'N/A' }}</small>
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
                                <label for="empresa_id">SUCURSAL</label>
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

            {{-- PRESCRIPCIÓN / RECETAS --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#prescripcion" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-prescription mr-2"></i> Recetas
                    </h5>
                </div>
                <div id="prescripcion" class="collapse show">
                    <div class="card-body">
                        {{-- Botón para añadir nueva receta --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" id="btnAnadirReceta" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus mr-2"></i>Añadir Receta
                                </button>
                                <hr>
                            </div>
                        </div>

                        {{-- Contenedor para todas las recetas --}}
                        <div id="recetasContainer">
                            @if($recetas && $recetas->count() > 0)
                                {{-- Mostrar recetas existentes --}}
                                @foreach($recetas as $index => $recetaItem)
                                    <div class="receta-item border rounded p-3 mb-3" data-receta-index="{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0 text-primary">
                                                <i class="fas fa-prescription mr-2"></i>Receta #<span class="receta-number">{{ $index + 1 }}</span>
                                            </h6>
                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-receta" style="{{ $recetas->count() <= 1 ? 'display: none;' : '' }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        {{-- Campo Tipo de Receta --}}
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Tipo de Receta</strong></label>
                                                    <select name="recetas[{{ $index }}][tipo]" class="form-control">
                                                        <option value="">Seleccionar...</option>
                                                        <option value="CERCA" {{ old("recetas.{$index}.tipo", $recetaItem->tipo) == 'CERCA' ? 'selected' : '' }}>CERCA</option>
                                                        <option value="LEJOS" {{ old("recetas.{$index}.tipo", $recetaItem->tipo) == 'LEJOS' ? 'selected' : '' }}>LEJOS</option>
                                                        <option value="BIFOCAL" {{ old("recetas.{$index}.tipo", $recetaItem->tipo) == 'BIFOCAL' ? 'selected' : '' }}>BIFOCAL</option>
                                                        <option value="PROGRESIVO" {{ old("recetas.{$index}.tipo", $recetaItem->tipo) == 'PROGRESIVO' ? 'selected' : '' }}>PROGRESIVO</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
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
                                                        <td><input type="text" name="recetas[{{ $index }}][od_esfera]" class="form-control" value="{{ old("recetas.{$index}.od_esfera", $recetaItem->od_esfera) }}"></td>
                                                        <td><input type="text" name="recetas[{{ $index }}][od_cilindro]" class="form-control" value="{{ old("recetas.{$index}.od_cilindro", $recetaItem->od_cilindro) }}"></td>
                                                        <td><input type="text" name="recetas[{{ $index }}][od_eje]" class="form-control" value="{{ old("recetas.{$index}.od_eje", $recetaItem->od_eje) }}"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">OI</td>
                                                        <td><input type="text" name="recetas[{{ $index }}][oi_esfera]" class="form-control" value="{{ old("recetas.{$index}.oi_esfera", $recetaItem->oi_esfera) }}"></td>
                                                        <td><input type="text" name="recetas[{{ $index }}][oi_cilindro]" class="form-control" value="{{ old("recetas.{$index}.oi_cilindro", $recetaItem->oi_cilindro) }}"></td>
                                                        <td><input type="text" name="recetas[{{ $index }}][oi_eje]" class="form-control" value="{{ old("recetas.{$index}.oi_eje", $recetaItem->oi_eje) }}"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>ADD</label>
                                                    <input type="text" name="recetas[{{ $index }}][od_adicion]" class="form-control" value="{{ old("recetas.{$index}.od_adicion", $recetaItem->od_adicion) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>DP pl/pc</label>
                                                    <input type="text" name="recetas[{{ $index }}][dp]" class="form-control" value="{{ old("recetas.{$index}.dp", $recetaItem->dp) }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Observaciones:</label>
                                            <textarea name="recetas[{{ $index }}][observaciones]" class="form-control" rows="3">{{ old("recetas.{$index}.observaciones", $recetaItem->observaciones) }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                {{-- Si no hay recetas, mostrar template vacío --}}
                                <div class="receta-item border rounded p-3 mb-3" data-receta-index="0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary">
                                            <i class="fas fa-prescription mr-2"></i>Receta #<span class="receta-number">1</span>
                                        </h6>
                                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-receta" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    {{-- Campo Tipo de Receta --}}
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Tipo de Receta</strong></label>
                                                <select name="recetas[0][tipo]" class="form-control">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="CERCA">CERCA</option>
                                                    <option value="LEJOS">LEJOS</option>
                                                    <option value="BIFOCAL">BIFOCAL</option>
                                                    <option value="PROGRESIVO">PROGRESIVO</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
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
                                                    <td><input type="text" name="recetas[0][od_esfera]" class="form-control"></td>
                                                    <td><input type="text" name="recetas[0][od_cilindro]" class="form-control"></td>
                                                    <td><input type="text" name="recetas[0][od_eje]" class="form-control"></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">OI</td>
                                                    <td><input type="text" name="recetas[0][oi_esfera]" class="form-control"></td>
                                                    <td><input type="text" name="recetas[0][oi_cilindro]" class="form-control"></td>
                                                    <td><input type="text" name="recetas[0][oi_eje]" class="form-control"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>ADD</label>
                                                <input type="text" name="recetas[0][od_adicion]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>DP pl/pc</label>
                                                <input type="text" name="recetas[0][dp]" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Observaciones:</label>
                                        <textarea name="recetas[0][observaciones]" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            @endif
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

        // Validación en tiempo real para campos de receta
        $('input[name^="od_"], input[name^="oi_"], input[name="add"], input[name="dp"]').on('input', function() {
            const campo = $(this);
            const valor = campo.val().trim();
            
            // Remover estilos de error previos
            campo.removeClass('is-invalid');
            campo.next('.invalid-feedback').remove();
            
            // Validar campos numéricos de la receta
            if (valor && campo.attr('name') !== 'od_eje' && campo.attr('name') !== 'oi_eje') {
                // Para esfera, cilindro, ADD (deben ser números con posibles signos + o -)
                const patronNumerico = /^[+\-]?\d*\.?\d*$/;
                if (!patronNumerico.test(valor)) {
                    campo.addClass('is-invalid');
                    campo.after('<div class="invalid-feedback">Debe ser un valor numérico válido (ej: +2.25, -1.50)</div>');
                }
            } else if (valor && (campo.attr('name') === 'od_eje' || campo.attr('name') === 'oi_eje')) {
                // Para eje (debe ser un número entre 0 y 180)
                const eje = parseInt(valor.replace('°', ''));
                if (isNaN(eje) || eje < 0 || eje > 180) {
                    campo.addClass('is-invalid');
                    campo.after('<div class="invalid-feedback">El eje debe ser un número entre 0 y 180</div>');
                }
            } else if (valor && campo.attr('name') === 'dp') {
                // Para DP (debe ser un número positivo)
                const dp = parseInt(valor);
                if (isNaN(dp) || dp <= 0) {
                    campo.addClass('is-invalid');
                    campo.after('<div class="invalid-feedback">La distancia pupilar debe ser un número positivo</div>');
                }
            }
        });

        // Auto-formatear campos de eje para agregar símbolo de grado
        $('input[name="od_eje"], input[name="oi_eje"]').on('blur', function() {
            let valor = $(this).val().trim();
            if (valor && !valor.includes('°')) {
                const numero = parseInt(valor);
                if (!isNaN(numero) && numero >= 0 && numero <= 180) {
                    $(this).val(numero + '°');
                }
            }
        });

        // Validación antes del envío del formulario
        $('form').on('submit', function(e) {
            const errores = $('.is-invalid').length;
            if (errores > 0) {
                e.preventDefault();
                alert('Por favor corrige los errores en los campos marcados antes de continuar.');
                return false;
            }
        });

        // Llamar a la función de diagnóstico al cargar la página
        actualizarDiagnosticoString();

        // Funcionalidad para múltiples recetas
        let recetaIndex = {{ $recetas && $recetas->count() > 0 ? $recetas->count() - 1 : 0 }};

        // Función para añadir nueva receta
        $('#btnAnadirReceta').on('click', function() {
            recetaIndex++;
            
            const nuevaReceta = `
                <div class="receta-item border rounded p-3 mb-3" data-receta-index="${recetaIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary">
                            <i class="fas fa-prescription mr-2"></i>Receta #<span class="receta-number">${recetaIndex + 1}</span>
                        </h6>
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-receta">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Tipo de Receta</strong></label>
                                <select name="recetas[${recetaIndex}][tipo]" class="form-control">
                                    <option value="">Seleccionar...</option>
                                    <option value="CERCA">CERCA</option>
                                    <option value="LEJOS">LEJOS</option>
                                    <option value="BIFOCAL">BIFOCAL</option>
                                    <option value="PROGRESIVO">PROGRESIVO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
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
                                    <td><input type="text" name="recetas[${recetaIndex}][od_esfera]" class="form-control"></td>
                                    <td><input type="text" name="recetas[${recetaIndex}][od_cilindro]" class="form-control"></td>
                                    <td><input type="text" name="recetas[${recetaIndex}][od_eje]" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">OI</td>
                                    <td><input type="text" name="recetas[${recetaIndex}][oi_esfera]" class="form-control"></td>
                                    <td><input type="text" name="recetas[${recetaIndex}][oi_cilindro]" class="form-control"></td>
                                    <td><input type="text" name="recetas[${recetaIndex}][oi_eje]" class="form-control"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ADD</label>
                                <input type="text" name="recetas[${recetaIndex}][od_adicion]" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>DP pl/pc</label>
                                <input type="text" name="recetas[${recetaIndex}][dp]" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones:</label>
                        <textarea name="recetas[${recetaIndex}][observaciones]" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            `;
            
            $('#recetasContainer').append(nuevaReceta);
            actualizarBotonesEliminar();
            
            // Aplicar eventos a los nuevos campos
            aplicarEventosReceta();
        });

        // Función para eliminar receta
        $(document).on('click', '.btn-eliminar-receta', function() {
            $(this).closest('.receta-item').remove();
            actualizarNumerosRecetas();
            actualizarBotonesEliminar();
        });

        // Función para actualizar números de recetas
        function actualizarNumerosRecetas() {
            $('.receta-item').each(function(index) {
                $(this).find('.receta-number').text(index + 1);
            });
        }

        // Función para mostrar/ocultar botones de eliminar
        function actualizarBotonesEliminar() {
            const totalRecetas = $('.receta-item').length;
            if (totalRecetas > 1) {
                $('.btn-eliminar-receta').show();
            } else {
                $('.btn-eliminar-receta').hide();
            }
        }

        // Función para aplicar eventos a campos de receta
        function aplicarEventosReceta() {
            // Validación en tiempo real para campos de receta
            $('input[name*="[od_esfera]"], input[name*="[od_cilindro]"], input[name*="[oi_esfera]"], input[name*="[oi_cilindro]"], input[name*="[od_adicion]"], input[name*="[dp]"]').off('input').on('input', function() {
                const campo = $(this);
                const valor = campo.val().trim();
                
                // Remover estilos de error previos
                campo.removeClass('is-invalid');
                campo.next('.invalid-feedback').remove();
                
                // Validar campos numéricos de la receta
                if (valor) {
                    // Para esfera, cilindro, ADD (deben ser números con posibles signos + o -)
                    const patronNumerico = /^[+\-]?\d*\.?\d*$/;
                    if (!patronNumerico.test(valor)) {
                        campo.addClass('is-invalid');
                        campo.after('<div class="invalid-feedback">Debe ser un valor numérico válido (ej: +2.25, -1.50)</div>');
                    }
                }
            });

            // Para campos de eje
            $('input[name*="[od_eje]"], input[name*="[oi_eje]"]').off('input').on('input', function() {
                const campo = $(this);
                const valor = campo.val().trim();
                
                // Remover estilos de error previos
                campo.removeClass('is-invalid');
                campo.next('.invalid-feedback').remove();
                
                if (valor) {
                    // Para eje (debe ser un número entre 0 y 180)
                    const eje = parseInt(valor.replace('°', ''));
                    if (isNaN(eje) || eje < 0 || eje > 180) {
                        campo.addClass('is-invalid');
                        campo.after('<div class="invalid-feedback">El eje debe ser un número entre 0 y 180</div>');
                    }
                }
            });

            // Auto-formatear campos de eje para agregar símbolo de grado
            $('input[name*="[od_eje]"], input[name*="[oi_eje]"]').off('blur').on('blur', function() {
                let valor = $(this).val().trim();
                if (valor && !valor.includes('°')) {
                    const numero = parseInt(valor);
                    if (!isNaN(numero) && numero >= 0 && numero <= 180) {
                        $(this).val(numero + '°');
                    }
                }
            });

            // Convertir campos de texto a mayúsculas
            $('input[name*="recetas"], textarea[name*="recetas"]').off('input.uppercase').on('input.uppercase', function() {
                $(this).val($(this).val().toUpperCase());
            });
        }

        // Aplicar eventos iniciales
        aplicarEventosReceta();
        actualizarBotonesEliminar();
    });
</script>
@stop
