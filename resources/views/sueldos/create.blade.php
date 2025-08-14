@extends('adminlte::page')

@section('title', 'CREAR SUELDO')

@section('adminlte_css')
    {{-- Add Select2 CSS from CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('content_header')
    <h1>CREAR SUELDO</h1>
@stop

@section('content')
    <style>
        /* Convertir todo el texto a mayúsculas */
        body, 
        .content-wrapper, 
        .main-header, 
        .main-sidebar, 
        .card-title,
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
        .alert,
        .modal-title,
        .modal-body p,
        .card-header,
        .card-footer,
        button {
            text-transform: uppercase !important;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">NUEVO REGISTRO DE SUELDO</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('sueldos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id">EMPLEADO <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required>
                                <option value="">SELECCIONAR EMPLEADO</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" 
                                        @if(isset($preselectedData['usuario']) && $preselectedData['usuario'] && $preselectedData['usuario']->id == $usuario->id) selected 
                                        @elseif(old('user_id') == $usuario->id) selected @endif>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            {{-- Debug info --}}
                            @if(config('app.debug') && isset($preselectedData['usuario']))
                                <small class="text-muted">
                                    DEBUG: Usuario preseleccionado: {{ $preselectedData['usuario']->name ?? 'NULL' }}
                                </small>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="empresa_id">EMPRESA</label>
                            <select class="form-control select2 @error('empresa_id') is-invalid @enderror" 
                                    id="empresa_id" name="empresa_id">
                                <option value="">SELECCIONAR EMPRESA</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" 
                                        @if(isset($preselectedData['empresa_matriz']) && $preselectedData['empresa_matriz'] && $preselectedData['empresa_matriz']->id == $empresa->id) selected 
                                        @elseif(old('empresa_id') == $empresa->id) selected @endif>
                                        {{ $empresa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha">FECHA <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('fecha') is-invalid @enderror" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="{{ old('fecha', date('Y-m-d')) }}" 
                                   required>
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="valor">VALOR <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('valor') is-invalid @enderror" 
                                   id="valor" 
                                   name="valor" 
                                   value="{{ old('valor') }}" 
                                   step="0.01" 
                                   min="0" 
                                   placeholder="0.00"
                                   required>
                            @error('valor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descripcion">DESCRIPCIÓN <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="INGRESE LA DESCRIPCIÓN DEL SUELDO"
                                      required>{{ old('descripcion', isset($preselectedData['mes']) && isset($preselectedData['anio']) ? 'SUELDO ' . strtoupper(date('F', mktime(0, 0, 0, $preselectedData['mes'], 1))) . ' ' . $preselectedData['anio'] : '') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="documento">DOCUMENTO ESCANEADO</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input @error('documento') is-invalid @enderror" 
                                           id="documento" 
                                           name="documento"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <label class="custom-file-label" for="documento">SELECCIONAR ARCHIVO...</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                FORMATOS PERMITIDOS: PDF, JPG, JPEG, PNG. TAMAÑO MÁXIMO: 2MB
                            </small>
                            @error('documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> GUARDAR SUELDO
                    </button>
                    <a href="{{ route('sueldos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> CANCELAR
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
{{-- Add Select2 JS from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        placeholder: 'SELECCIONAR...',
        allowClear: true,
        theme: 'bootstrap4'
    });
    
    console.log('Select2 inicializado correctamente');
    
    // Mostrar nombre del archivo seleccionado
    $('#documento').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'SELECCIONAR ARCHIVO...');
    });
    
    // Obtener parámetros URL para autocompletar descripción
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };
    
    // Si hay parámetros de mes y año en la URL, actualizar descripción automáticamente
    var mesUrl = getUrlParameter('mes');
    var anioUrl = getUrlParameter('anio');
    var usuarioUrl = getUrlParameter('usuario');
    
    if (mesUrl && anioUrl && $('#descripcion').val().trim() === '') {
        var meses = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        var descripcionAuto = 'SUELDO ' + meses[parseInt(mesUrl)] + ' ' + anioUrl;
        $('#descripcion').val(descripcionAuto);
    }
    
    // Verificar que la preselección de usuario funcione
    var selectedUserId = $('#user_id').val();
    console.log('Usuario preseleccionado ID:', selectedUserId);
    console.log('Usuario desde URL:', usuarioUrl);
    
    // Si no hay usuario preseleccionado pero viene en URL, buscar por nombre
    if (!selectedUserId && usuarioUrl) {
        $('#user_id option').each(function() {
            if ($(this).text().toUpperCase().includes(usuarioUrl.toUpperCase())) {
                $(this).prop('selected', true);
                $('#user_id').trigger('change');
                console.log('Usuario seleccionado por coincidencia de nombre:', $(this).text());
                return false; // break del each
            }
        });
    }
    
    // Debug: mostrar parámetros URL
    console.log('Parámetros URL:', {
        usuario: usuarioUrl,
        mes: mesUrl,
        anio: anioUrl
    });
});
</script>
@stop
