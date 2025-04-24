@extends('adminlte::page')

@section('title', 'EDITAR USUARIO')

@section('content_header')
   
@stop

@section('content')
  
<div class="card">
        <div class="card-header">
          <h3 class="card-title">EDITAR USUARIO</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="COLLAPSE">
              <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="REMOVE">
              <i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="card-body">
        <div class="col-md-6">
                <form role="form" action="{{route('configuracion.usuarios.update', $usuario)}}" method="POST">
                        @csrf
                        @method('put')
                        <div class ="form-group">
                                <label>NOMBRE</label>
                                <input name="nombre" required type="text" class="form-control" value = "{{$usuario->name}}">
                        </div>
                        <div class ="form-group">
                                <label>USUARIO</label>
                                <input name="user" required type="text" class="form-control" value = "{{$usuario->user}}">
                        </div>
                        <div class ="form-group">
                                <label>E-MAIL</label>
                                <input name="email" required type="text" class="form-control" value = "{{$usuario->email}}">
                        </div>
                        <div class ="form-group">
                                <label>CONTRASEÑA</label>
                                <input name="password" type="password" class="form-control" placeholder="Dejar vacío para mantener la contraseña actual">
                                <small class="text-muted">Ingrese una nueva contraseña solo si desea cambiarla</small>
                        </div>
                        <div class ="form-group">
                                <label>ACTIVO</label>
                                <select id="activo" name="activo" class="form-control">
                                @if ($usuario->active === 1)
                                <option value="1">ACTIVO</option>
                                <option value="0">INACTIVO</option>
                                @else
                                <option value="1">ACTIVO</option>
                                <option value="0" selected>INACTIVO</option>
                                @endif    
                                </select>
                        </div>
                        <div class ="form-group">
                                <label>ADMINISTRADOR</label>
                                <select id="is_admin" name="is_admin" class="form-control">
                                @if ($usuario->is_admin === 1)
                                <option value="1">SÍ</option>
                                <option value="0">NO</option>
                                @else
                                <option value="1">SÍ</option>
                                <option value="0" selected>NO</option>
                                @endif    
                                </select>
                        </div>
                <button type="button" class="btn btn-primary pull-left" data-toggle="modal" data-target="#modal">EDITAR USUARIO</button>
  <div class="modal fade" id="modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          
          <h4 class="modal-title">MODIFICAR USUARIO</h4>
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
              
      <br>    
          <!-- Fin contenido -->
        </div>
</div> 
        <!-- /.card-body -->
        <div class="card-footer">
        EDITAR USUARIO
        </div>
        <!-- /.card-footer-->
      </div>
    
@stop

@section('js')
   
@stop

@section('footer')
   <div class="float-right d-none d-sm-block">
        <b>VERSIÓN</b> @version('compact')       
    </div>
@stop