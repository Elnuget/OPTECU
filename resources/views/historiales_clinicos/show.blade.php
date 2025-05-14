@extends('adminlte::page')

@section('title', 'DETALLE DEL HISTORIAL CLÍNICO')

@section('content_header')
    <h1>DETALLE DEL HISTORIAL CLÍNICO</h1>
    <p>Información detallada del historial clínico.</p>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">HISTORIAL N°: {{ $historialClinico->id }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>INFORMACIÓN DEL PACIENTE</h4>
                <dl class="row">
                    <dt class="col-sm-4">NOMBRES:</dt>
                    <dd class="col-sm-8">{{ strtoupper($historialClinico->nombres) }}</dd>
                    <dt class="col-sm-4">APELLIDOS:</dt>
                    <dd class="col-sm-8">{{ strtoupper($historialClinico->apellidos) }}</dd>
                    <dt class="col-sm-4">EDAD:</dt>
                    <dd class="col-sm-8">{{ $historialClinico->edad }} AÑOS</dd>
                    <dt class="col-sm-4">FECHA NACIMIENTO:</dt>
                    <dd class="col-sm-8">{{ $historialClinico->fecha_nacimiento ? \Carbon\Carbon::parse($historialClinico->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</dd>
                    <dt class="col-sm-4">CÉDULA:</dt>
                    <dd class="col-sm-8">{{ $historialClinico->cedula ?? 'N/A' }}</dd>
                    <dt class="col-sm-4">CELULAR:</dt>
                    <dd class="col-sm-8">{{ $historialClinico->celular }}</dd>
                    <dt class="col-sm-4">OCUPACIÓN:</dt>
                    <dd class="col-sm-8">{{ strtoupper($historialClinico->ocupacion) }}</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <h4>INFORMACIÓN DE LA CONSULTA</h4>
                <dl class="row">
                    <dt class="col-sm-4">FECHA CONSULTA:</dt>
                    <dd class="col-sm-8">{{ \Carbon\Carbon::parse($historialClinico->fecha)->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">PRÓXIMA CONSULTA:</dt>
                    <dd class="col-sm-8">{{ $historialClinico->proxima_consulta ? \Carbon\Carbon::parse($historialClinico->proxima_consulta)->format('d/m/Y') : 'NO PROGRAMADA' }}</dd>
                    <dt class="col-sm-4">USUARIO REGISTRO:</dt>
                    <dd class="col-sm-8">{{ strtoupper($historialClinico->usuario->name ?? 'N/A') }}</dd>
                </dl>
            </div>
        </div>

        <hr>
        <h4>MOTIVO DE CONSULTA Y ENFERMEDAD ACTUAL</h4>
        <dl class="row">
            <dt class="col-sm-3">MOTIVO CONSULTA:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->motivo_consulta) }}</dd>
            <dt class="col-sm-3">ENFERMEDAD ACTUAL:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->enfermedad_actual) }}</dd>
        </dl>

        <hr>
        <h4>ANTECEDENTES</h4>
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

        <hr>
        <h4>AGUDEZA VISUAL SIN CORRECCIÓN</h4>
        <div class="row">
            <div class="col-md-4">
                <p><strong>OJO DERECHO (OD):</strong> {{ strtoupper($historialClinico->agudeza_visual_vl_sin_correccion_od) }} (VL) / {{ strtoupper($historialClinico->agudeza_visual_vp_sin_correccion_od) }} (VP)</p>
            </div>
            <div class="col-md-4">
                <p><strong>OJO IZQUIERDO (OI):</strong> {{ strtoupper($historialClinico->agudeza_visual_vl_sin_correccion_oi) }} (VL) / {{ strtoupper($historialClinico->agudeza_visual_vp_sin_correccion_oi) }} (VP)</p>
            </div>
            <div class="col-md-4">
                <p><strong>AMBOS OJOS (AO):</strong> {{ strtoupper($historialClinico->agudeza_visual_vl_sin_correccion_ao) }} (VL) / {{ strtoupper($historialClinico->agudeza_visual_vp_sin_correccion_ao) }} (VP)</p>
            </div>
        </div>
        <dl class="row">
            <dt class="col-sm-3">PH OD:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->ph_od) }}</dd>
            <dt class="col-sm-3">PH OI:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->ph_oi) }}</dd>
            <dt class="col-sm-3">OPTOTIPO:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->optotipo ?? 'N/A') }}</dd>
        </dl>

        <hr>
        <h4>LENSOMETRÍA (SI APLICA)</h4>
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

        <hr>
        <h4>REFRACCIÓN Y RX FINAL</h4>
        <div class="row">
            <div class="col-md-6">
                <p><strong>REFRACCIÓN OD:</strong> {{ strtoupper($historialClinico->refraccion_od) }}</p>
                <p><strong>RX FINAL DP OD:</strong> {{ strtoupper($historialClinico->rx_final_dp_od) }}</p>
                <p><strong>RX FINAL AV VL OD:</strong> {{ strtoupper($historialClinico->rx_final_av_vl_od) }}</p>
                <p><strong>RX FINAL AV VP OD:</strong> {{ strtoupper($historialClinico->rx_final_av_vp_od) }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>REFRACCIÓN OI:</strong> {{ strtoupper($historialClinico->refraccion_oi) }}</p>
                <p><strong>RX FINAL DP OI:</strong> {{ strtoupper($historialClinico->rx_final_dp_oi) }}</p>
                <p><strong>RX FINAL AV VL OI:</strong> {{ strtoupper($historialClinico->rx_final_av_vl_oi) }}</p>
                <p><strong>RX FINAL AV VP OI:</strong> {{ strtoupper($historialClinico->rx_final_av_vp_oi) }}</p>
            </div>
        </div>
        <dl class="row">
            <dt class="col-sm-3">ADD (SI APLICA):</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->add ?? 'N/A') }}</dd>
        </dl>
        
        <hr>
        <h4>DIAGNÓSTICO Y TRATAMIENTO</h4>
        <dl class="row">
            <dt class="col-sm-3">DIAGNÓSTICO:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->diagnostico) }}</dd>
            <dt class="col-sm-3">TRATAMIENTO:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->tratamiento) }}</dd>
            <dt class="col-sm-3">COTIZACIÓN:</dt>
            <dd class="col-sm-9">{{ strtoupper($historialClinico->cotizacion ?? 'N/A') }}</dd>
        </dl>

    </div>
    <div class="card-footer">
        <a href="{{ route('historiales_clinicos.index') }}" class="btn btn-secondary">VOLVER AL LISTADO</a>
        @if ($historialClinico && $historialClinico->id)
            <a href="{{ route('historiales_clinicos.edit', ['historial' => $historialClinico->id]) }}" class="btn btn-warning">EDITAR</a>
        @else
            <a href="#" class="btn btn-warning disabled" aria-disabled="true">EDITAR (ID NO DISPONIBLE)</a>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    /* Convertir todo el texto a mayúsculas */
    .card-title,
    .card-header h3,
    dt,
    h4,
    .btn {
        text-transform: uppercase !important;
    }
    dd {
        font-weight: normal; /* Para que el contenido del dd no esté en negrita por defecto */
    }
</style>
@stop

@section('js')
<script>
    // No se necesita JS específico para esta vista por ahora
</script>
@stop
