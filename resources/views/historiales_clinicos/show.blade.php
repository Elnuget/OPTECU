@extends('adminlte::page')

@section('title', 'DETALLE DEL HISTORIAL CLÍNICO')

@section('content_header')
    <h1 class="mb-3">DETALLE DEL HISTORIAL CLÍNICO</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- FECHA DE REGISTRO --}}
        <div class="card mb-4">
            <div class="card-header" data-toggle="collapse" data-target="#fechaRegistro" style="cursor: pointer">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt mr-2"></i> Fecha de Registro
                </h5>
            </div>
            <div id="fechaRegistro" class="collapse show">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">FECHA CONSULTA:</dt>
                        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($historialClinico->fecha)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-3">PRÓXIMA CONSULTA:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->proxima_consulta ? \Carbon\Carbon::parse($historialClinico->proxima_consulta)->format('d/m/Y') : 'NO PROGRAMADA' }}</dd>
                        <dt class="col-sm-3">USUARIO REGISTRO:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->usuario->name ?? 'N/A') }}</dd>
                    </dl>
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
                    <dl class="row">
                        <dt class="col-sm-3">NOMBRES:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->nombres) }}</dd>
                        <dt class="col-sm-3">APELLIDOS:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->apellidos) }}</dd>
                        <dt class="col-sm-3">EMPRESA:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->empresa ? $historialClinico->empresa->nombre : 'SIN EMPRESA ASIGNADA') }}</dd>
                        <dt class="col-sm-3">EDAD:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->edad }} AÑOS</dd>
                        <dt class="col-sm-3">FECHA NACIMIENTO:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->fecha_nacimiento ? \Carbon\Carbon::parse($historialClinico->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</dd>
                        <dt class="col-sm-3">CÉDULA:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->cedula ?? 'N/A' }}</dd>
                        <dt class="col-sm-3">CELULAR:</dt>
                        <dd class="col-sm-9">{{ $historialClinico->celular }}</dd>
                        <dt class="col-sm-3">OCUPACIÓN:</dt>
                        <dd class="col-sm-9">{{ strtoupper($historialClinico->ocupacion) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- MOTIVO DE CONSULTA Y ENFERMEDAD ACTUAL --}}
        <div class="card mb-4">
            <div class="card-header" data-toggle="collapse" data-target="#motivoConsulta" style="cursor: pointer">
                <h5 class="mb-0">
                    <i class="fas fa-notes-medical mr-2"></i> Motivo de Consulta y Enfermedad Actual
                </h5>
            </div>
            <div id="motivoConsulta" class="collapse show">
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
            <div id="antecedentes" class="collapse show">
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
            <div id="agudezaVisual" class="collapse show">
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
            <div id="lensometria" class="collapse show">
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
            <div id="rxFinal" class="collapse show">
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
            <div id="diagnostico" class="collapse show">
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

        {{-- BOTONES DE ACCIÓN --}}
        <div class="d-flex justify-content-end">
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
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inicialmente mostrar todas las secciones
        $('.collapse').addClass('show');
        
        // Permitir colapsar/expandir secciones
        $('.card-header').click(function() {
            $(this).next('.collapse').collapse('toggle');
        });
    });
</script>
@stop
