@extends('adminlte::page')

@section('title', 'CREAR USUARIO')

@section('content_header')
    <h1>Crear Usuario</h1>
    <p>Administración de usuarios</p>
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> {{session('error')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Éxito:</strong> {{session('success')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Errores de validación:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
@stop

@section('content')
  
<div class="card">
        <div class="card-header">
          <h3 class="card-title">CREAR USUARIO</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="COLLAPSE">
              <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="REMOVE">
              <i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="card-body">
        <div class="col-md-6">
                <form role="form" action="{{route('configuracion.usuarios.store')}}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                                <label>NOMBRE <span class="text-danger">*</span></label>
                                <input name="nombre" required type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       value="{{old('nombre')}}" placeholder="Ingrese el nombre completo">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        <div class="form-group">
                                <label>USUARIO <span class="text-danger">*</span></label>
                                <input name="user" required type="text" class="form-control @error('user') is-invalid @enderror" 
                                       value="{{old('user')}}" placeholder="Ingrese el nombre de usuario">
                                @error('user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">El usuario debe ser único</small>
                        </div>
                        <div class="form-group">
                            <label>CONTRASEÑA <span class="text-danger">*</span></label>
                            <input name="password" required type="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Mínimo 8 caracteres">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Debe contener al menos 8 caracteres</small>
                        </div>
                        <div class="form-group">
                            <label>CONFIRMAR CONTRASEÑA <span class="text-danger">*</span></label>
                            <input name="password_confirmation" required type="password" class="form-control" 
                                   placeholder="Confirme la contraseña">
                        </div>
                        <div class="form-group">
                                <label>E-MAIL <span class="text-danger">*</span></label>
                                <input name="email" required type="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{old('email')}}" placeholder="ejemplo@correo.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">El email debe ser único</small>
                        </div>
                        <div class="form-group">
                                <label>Sucursal</label>
                                <select id="empresa_id" name="empresa_id" class="form-control @error('empresa_id') is-invalid @enderror">
                                <option value="">Sin Sucursal</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{$empresa->id}}" {{old('empresa_id') == $empresa->id ? 'selected' : ''}}>
                                        {{$empresa->nombre}}
                                    </option>
                                @endforeach
                                </select>
                                @error('empresa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        <div class="form-group">
                                <label>ACTIVO</label>
                                <select id="activo" name="activo" class="form-control @error('activo') is-invalid @enderror">
                                <option value="1" {{old('activo', '1') == '1' ? 'selected' : ''}}>ACTIVO</option>
                                <option value="0" {{old('activo') == '0' ? 'selected' : ''}}>INACTIVO</option>
                                </select>
                                @error('activo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        <div class="form-group">
                                <label>ADMINISTRADOR</label>
                                <select id="is_admin" name="is_admin" class="form-control @error('is_admin') is-invalid @enderror">
                                <option value="0" {{old('is_admin', '0') == '0' ? 'selected' : ''}}>NO</option>
                                <option value="1" {{old('is_admin') == '1' ? 'selected' : ''}}>SÍ</option>
                                </select>
                                @error('is_admin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                <button type="button" class="btn btn-primary pull-left" data-toggle="modal" data-target="#modal">CREAR USUARIO</button>
  <div class="modal fade" id="modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          
          <h4 class="modal-title">CREAR USUARIO</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p>¿ESTÁ SEGURO QUE QUIERE GUARDAR LOS CAMBIOS?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">CANCELAR</button>
          <button type="submit" class="btn btn-primary">GUARDAR CAMBIOS</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
        </form>  
        </div>
              
              
          <!-- Fin contenido -->
        </div>
        <!-- /.card-body -->
        <div class="card-footer">
        CREAR USUARIO
        </div>
        <!-- /.card-footer-->
      </div>
    
@stop

@section('js')
<script>
$(document).ready(function() {
    // Validación en tiempo real del email
    $('input[name="email"]').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">El formato del email no es válido</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Validación de contraseñas coincidentes
    $('input[name="password_confirmation"]').on('input', function() {
        const password = $('input[name="password"]').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Las contraseñas no coinciden</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Validación de longitud de contraseña
    $('input[name="password"]').on('input', function() {
        const password = $(this).val();
        
        if (password && password.length < 8) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            
            // Revalidar confirmación si existe
            const confirmPassword = $('input[name="password_confirmation"]').val();
            if (confirmPassword) {
                $('input[name="password_confirmation"]').trigger('input');
            }
        }
    });

    // Validación antes de enviar el formulario
    $('form').on('submit', function(e) {
        let hasErrors = false;
        
        // Validar campos requeridos
        $('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                hasErrors = true;
            }
        });
        
        // Validar email
        const email = $('input[name="email"]').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('input[name="email"]').addClass('is-invalid');
            hasErrors = true;
        }
        
        // Validar contraseñas
        const password = $('input[name="password"]').val();
        const confirmPassword = $('input[name="password_confirmation"]').val();
        
        if (password.length < 8) {
            $('input[name="password"]').addClass('is-invalid');
            hasErrors = true;
        }
        
        if (password !== confirmPassword) {
            $('input[name="password_confirmation"]').addClass('is-invalid');
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.preventDefault();
            alert('Por favor, corrija los errores en el formulario antes de continuar.');
            return false;
        }
    });
});
</script>
@stop

@section('footer')
   <div class="float-right d-none d-sm-block">
        <b>VERSIÓN</b> @version('compact')       
    </div>
@stop