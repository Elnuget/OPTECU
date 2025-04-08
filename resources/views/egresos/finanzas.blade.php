@extends('adminlte::page')

@section('title', 'FINANZAS')

@section('content_header')
    <h1>FINANZAS</h1>
    <p>ADMINISTRACIÓN DE FINANZAS</p>
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
        datalist,
        datalist option,
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
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-dollar-sign mr-2"></i>
                INFORMACIÓN FINANCIERA
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                AQUÍ SE MOSTRARÁN LAS FINANZAS Y ESTADÍSTICAS FINANCIERAS DE LA EMPRESA
            </div>
        </div>
    </div>
@stop

@section('js')
@include('atajos')
@stop 