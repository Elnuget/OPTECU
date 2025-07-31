@extends('adminlte::page')

@section('title', 'DETALLE DEL HISTORIAL CLÍNICO')

@section('content_header')
    <h1 class="mb-3">DETALLE DEL HISTORIAL CLÍNICO</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- DATOS DEL PACIENTE --}}
        <div class="card mb-4">
            <div class="card-header" data-toggle="collapse" data-target="#datosPaciente" style="cursor: pointer">
                <h5 class="mb-0">
                    <i class="fas fa-user mr-2"></i> Datos del Paciente
                </h5>
            </div>
            <div id="datosPaciente" class="collapse show">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">FECHA CONSULTA:</dt>
                        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($historialClinico->fecha)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-3">NOMBRES:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->nombres) }}</dd>
                        <dt class="col-sm-3">APELLIDOS:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->apellidos) }}</dd>
                        <dt class="col-sm-3">EDAD:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->edad }} AÑOS</dd>
                        <dt class="col-sm-3">FECHA NACIMIENTO:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->fecha_nacimiento ? \Carbon\Carbon::parse($historialClinico->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</dd>
                        <dt class="col-sm-3">RUT :</dt>
                        <dd class="col-sm-9">{{ $historialClinico->cedula ?? 'N/A' }}</dd>
                        <dt class="col-sm-3">CELULAR:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->celular }}</dd>
                        <dt class="col-sm-3">CORREO:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->correo ?? 'N/A' }}</dd>
                        <dt class="col-sm-3">DIRECCIÓN:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->direccion ?? 'N/A') }}</dd>
                        <dt class="col-sm-3">OCUPACIÓN:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->ocupacion) }}</dd>
                        <dt class="col-sm-3">EMPRESA:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->empresa ? strtoupper($historialClinico->empresa->nombre) : 'N/A' }}</dd>
                        <dt class="col-sm-3">PRÓXIMA CONSULTA:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->proxima_consulta ? \Carbon\Carbon::parse($historialClinico->proxima_consulta)->format('d/m/Y') : 'NO PROGRAMADA' }}</dd>
                        <dt class="col-sm-3">USUARIO REGISTRO:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->usuario->name ?? 'N/A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- PRESCRIPCIÓN / RECETAS --}}
        @if($historialClinico->recetas && $historialClinico->recetas->count() > 0)
        <div class="card mb-4">
            <div class="card-header" data-toggle="collapse" data-target="#prescripcion" style="cursor: pointer">
                <h5 class="mb-0">
                    <i class="fas fa-prescription mr-2"></i> Recetas
                    <span class="badge badge-secondary ml-2">{{ $historialClinico->recetas->count() }}</span>
                </h5>
            </div>
            <div id="prescripcion" class="collapse show">
                <div class="card-body">
                    @foreach($historialClinico->recetas as $index => $receta)
                        <div class="receta-container border rounded p-3 mb-4 {{ $loop->last ? 'mb-0' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-prescription mr-2"></i>Receta #{{ $index + 1 }}
                                    @if($receta->tipo)
                                        <span class="badge badge-primary ml-2">{{ $receta->tipo }}</span>
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ $receta->created_at ? $receta->created_at->format('d/m/Y H:i') : 'Fecha no disponible' }}
                                </small>
                            </div>
                            
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered text-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th></th>
                                            <th>ESFERA</th>
                                            <th>CILINDRO</th>
                                            <th>EJE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>OD</strong></td>
                                            <td>{{ $receta->od_esfera ?? 'N/A' }}</td>
                                            <td>{{ $receta->od_cilindro ?? 'N/A' }}</td>
                                            <td>{{ $receta->od_eje ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>OI</strong></td>
                                            <td>{{ $receta->oi_esfera ?? 'N/A' }}</td>
                                            <td>{{ $receta->oi_cilindro ?? 'N/A' }}</td>
                                            <td>{{ $receta->oi_eje ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-6">ADD OD:</dt>
                                        <dd class="col-sm-6">{{ strtoupper($receta->od_adicion ?? 'N/A') }}</dd>
                                        <dt class="col-sm-6">ADD OI:</dt>
                                        <dd class="col-sm-6">{{ strtoupper($receta->oi_adicion ?? 'N/A') }}</dd>
                                        <dt class="col-sm-6">DP:</dt>
                                        <dd class="col-sm-6">{{ strtoupper($receta->dp ?? 'N/A') }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-12">OBSERVACIONES:</dt>
                                        <dd class="col-12">
                                            @if($receta->observaciones)
                                                <div class="p-2 bg-light rounded">
                                                    {{ strtoupper($receta->observaciones) }}
                                                </div>
                                            @else
                                                <span class="text-muted">Sin observaciones</span>
                                            @endif
                                        </dd>
                                        @if($receta->foto)
                                            <dt class="col-12 mt-2">FOTO:</dt>
                                            <dd class="col-12">
                                                <div class="p-2 bg-light rounded">
                                                    <img src="{{ $receta->foto }}" alt="Foto de la receta" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                                </div>
                                            </dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$loop->last)
                            <hr class="my-4">
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-prescription mr-2"></i> Recetas
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay recetas registradas para este historial clínico.
                </div>
            </div>
        </div>
        @endif

        {{-- DIAGNÓSTICO --}}
        <div class="card mb-4">
            <div class="card-header" data-toggle="collapse" data-target="#diagnostico" style="cursor: pointer">
                <h5 class="mb-0">
                    <i class="fas fa-stethoscope mr-2"></i> Diagnóstico
                </h5>
            </div>
            <div id="diagnostico" class="collapse show">
                <div class="card-body">
                    @if($historialClinico->diagnostico)
                        @php
                            $diagnosticos = explode(',', $historialClinico->diagnostico);
                            $diagnosticos = array_map('trim', $diagnosticos);
                        @endphp
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled {{ in_array('Astigmatismo', $diagnosticos) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Astigmatismo
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled {{ in_array('Miopía', $diagnosticos) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Miopía
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled {{ in_array('Hipermetropía', $diagnosticos) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Hipermetropía
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled {{ in_array('Presbicia', $diagnosticos) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Presbicia
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">DIAGNÓSTICO COMPLETO:</dt>
                                <dd class="col-sm-9">{{ strtoupper($historialClinico->diagnostico) }}</dd>
                            </dl>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            No hay diagnóstico registrado para este historial clínico.
                        </div>
                    @endif
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
                <div class="card-header" data-toggle="collapse" data-target="#motivoConsulta" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-notes-medical mr-2"></i> Motivo de Consulta y Enfermedad Actual
                    </h5>
                </div>
                <div id="motivoConsulta" class="collapse">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">MOTIVO CONSULTA:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->motivo_consulta) }}</dd>
                            <dt class="col-sm-3">ENFERMEDAD ACTUAL:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->enfermedad_actual) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- ANTECEDENTES --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#antecedentes" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-history mr-2"></i> Antecedentes
                    </h5>
                </div>
                <div id="antecedentes" class="collapse">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">PERSONALES OCULARES:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->antecedentes_personales_oculares) }}</dd>
                            <dt class="col-sm-3">PERSONALES GENERALES:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->antecedentes_personales_generales) }}</dd>
                            <dt class="col-sm-3">FAMILIARES OCULARES:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->antecedentes_familiares_oculares) }}</dd>
                            <dt class="col-sm-3">FAMILIARES GENERALES:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->antecedentes_familiares_generales) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- AGUDEZA VISUAL Y PH --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#agudezaVisual" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-eye mr-2"></i> Agudeza Visual y PH
                    </h5>
                </div>
                <div id="agudezaVisual" class="collapse">
                    <div class="card-body">
                        <h6>Agudeza Visual VL sin Corrección</h6>
                        <dl class="row">
                            <dt class="col-sm-3">OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->agudeza_visual_vl_sin_correccion_od) }}</dd>
                            <dt class="col-sm-3">OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->agudeza_visual_vl_sin_correccion_oi) }}</dd>
                            <dt class="col-sm-3">AO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->agudeza_visual_vl_sin_correccion_ao) }}</dd>
                        </dl>

                        <h6 class="mt-4">Agudeza Visual VP sin Corrección</h6>
                        <dl class="row">
                            <dt class="col-sm-3">OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->agudeza_visual_vp_sin_correccion_od) }}</dd>
                            <dt class="col-sm-3">OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->agudeza_visual_vp_sin_correccion_oi) }}</dd>
                            <dt class="col-sm-3">AO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->agudeza_visual_vp_sin_correccion_ao) }}</dd>
                        </dl>

                        <h6 class="mt-4">Pin Hole (PH)</h6>
                        <dl class="row">
                            <dt class="col-sm-3">PH OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->ph_od) }}</dd>
                            <dt class="col-sm-3">PH OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->ph_oi) }}</dd>
                            <dt class="col-sm-3">OPTOTIPO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->optotipo ?? 'N/A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- LENSOMETRÍA --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#lensometria" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-glasses mr-2"></i> Lensometría
                    </h5>
                </div>
                <div id="lensometria" class="collapse">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">LENSOMETRÍA OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->lensometria_od ?? 'N/A') }}</dd>
                            <dt class="col-sm-3">LENSOMETRÍA OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->lensometria_oi ?? 'N/A') }}</dd>
                            <dt class="col-sm-3">TIPO DE LENTE:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->tipo_lente ?? 'N/A') }}</dd>
                            <dt class="col-sm-3">MATERIAL:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->material ?? 'N/A') }}</dd>
                            <dt class="col-sm-3">FILTRO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->filtro ?? 'N/A') }}</dd>
                            <dt class="col-sm-3">TIEMPO DE USO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->tiempo_uso ?? 'N/A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- RX FINAL --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#rxFinal" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-prescription mr-2"></i> Rx Final
                    </h5>
                </div>
                <div id="rxFinal" class="collapse">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">REFRACCIÓN OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->refraccion_od) }}</dd>
                            <dt class="col-sm-3">REFRACCIÓN OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->refraccion_oi) }}</dd>
                            <dt class="col-sm-3">DP OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->rx_final_dp_od) }}</dd>
                            <dt class="col-sm-3">DP OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->rx_final_dp_oi) }}</dd>
                            <dt class="col-sm-3">AV VL OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->rx_final_av_vl_od) }}</dd>
                            <dt class="col-sm-3">AV VL OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->rx_final_av_vl_oi) }}</dd>
                            <dt class="col-sm-3">AV VP OD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->rx_final_av_vp_od) }}</dd>
                            <dt class="col-sm-3">AV VP OI:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->rx_final_av_vp_oi) }}</dd>
                            <dt class="col-sm-3">ADD:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->add ?? 'N/A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- DIAGNÓSTICO Y TRATAMIENTO --}}
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#diagnostico" style="cursor: pointer">
                    <h5 class="mb-0">
                        <i class="fas fa-file-medical mr-2"></i> Diagnóstico y Tratamiento
                    </h5>
                </div>
                <div id="diagnostico" class="collapse">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">DIAGNÓSTICO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->diagnostico) }}</dd>
                            <dt class="col-sm-3">TRATAMIENTO:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->tratamiento) }}</dd>
                            <dt class="col-sm-3">COTIZACIÓN:</dt>
                            <dd class="col-sm-9">{{ strtoupper($historialClinico->cotizacion ?? 'N/A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTONES DE ACCIÓN --}}
        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('historiales_clinicos.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-2"></i>VOLVER AL LISTADO
            </a>
            @if ($historialClinico && $historialClinico->id)
                <a href="{{ route('historiales_clinicos.edit', ['historial' => $historialClinico->id]) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-2"></i>EDITAR
                </a>
            @endif
        </div>
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
    .card-header h5 {
        margin: 0;
        font-weight: 600;
    }
    dt {
        font-weight: 600;
    }
    dd {
        margin-bottom: 0.5rem;
    }
    h6 {
        font-weight: 600;
        color: #495057;
    }
    .btn {
        text-transform: uppercase;
    }
    
    /* Estilos para múltiples recetas */
    .receta-container {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6 !important;
        transition: all 0.3s ease;
    }
    .receta-container:hover {
        background-color: #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .receta-container .table {
        background-color: white;
        margin-bottom: 0;
    }
    .receta-container .table thead {
        background-color: #007bff !important;
        color: white !important;
    }
    .badge-secondary {
        background-color: #6c757d;
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
                // Opcional: colapsar todas las tarjetas internas al ocultar el contenedor
                $seccionesOpcionales.find('.collapse').collapse('hide');
            } else {
                $seccionesOpcionales.slideDown();
                $boton.html('<i class="fas fa-minus-circle mr-2"></i>Ocultar información adicional');
                // Opcional: expandir todas las tarjetas internas al mostrar el contenedor
                $seccionesOpcionales.find('.collapse').collapse('show');
            }
        });

        // Permitir colapsar/expandir secciones individuales
        $('.card-header[data-toggle="collapse"]').click(function() {
            const target = $(this).data('target');
            $(target).collapse('toggle');
        });
    });
</script>
@stop
