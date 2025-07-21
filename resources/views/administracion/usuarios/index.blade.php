@extends('adminlte::page')

@section('title', 'Usuarios')



@section('content_header')
    <h1>Usuarios</h1>
    <p>Administracion de usuarios</p>
    @if(session('error'))
<div class="alert {{session('tipo')}} alert-dismissible fade show" role="alert">
    <strong>{{session('error')}}</strong> {{session('mensaje')}}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@stop

@section('content')

<div class="card">
    <div class="card-body">
            <div class="btn-group">
            <a type="button" class="btn btn-success" href="{{route('configuracion.usuarios.create')}}">Crear usuario</a>
            
            </div>
        </div>
        <table id="example"  class="table table-striped table-bordered">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Nombre</td>
                    <td>Usuario</td>
                    <td>Mail</td>
                    <td>Sucursal</td>
                    <td>Activo</td>
                    <td>Administrador</td>
                    <td>Acciones</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $index => $u)
                <tr>
                        <td>{{$index +1 }}</td>
                        <td>{{$u->name}}</td>
                        <td>{{$u->user}}</td>
                        <td>{{$u->email}}</td>
                        <td>
                        @if ($u->empresa)
                           {{$u->empresa->nombre}} 
                        @else
                           <span class="text-muted">Sin Sucursal</span>
                        @endif       
                        </td>
                        <td>
                        @if ($u->active)
                           <span class="badge badge-success">Activo</span>
                        @else
                           <span class="badge badge-secondary">Inactivo</span>
                        @endif       
                        </td>
                        <td>
                        @if ($u->is_admin)
                           <span class="badge badge-primary">Sí</span>
                        @else
                           <span class="badge badge-light">No</span>
                        @endif       
                        </td>
                        <td><div class="btn-group">
                            <a type="button" class="btn btn-primary btn-sm" href="{{route('configuracion.usuarios.editar', $u->id)}}">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar({{$u->id}}, '{{$u->name}}')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                          </div></td>
                    </tr>
                   
                @endforeach
            </tbody>
        </table>
        
</div>
           
    
@stop

@section('js')
@include('atajos')
    <script> 
        $(document).ready(function() {
            $('#example').DataTable({
                "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            }
        }
            );
        } );
        
        function confirmarEliminar(id, nombre) {
            if (confirm('¿Está seguro que desea eliminar al usuario "' + nombre + '"?')) {
                // Crear un formulario dinámico para enviar la petición DELETE
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("configuracion.usuarios.destroy", ":id") }}'.replace(':id', id);
                
                // Agregar token CSRF
                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Agregar método DELETE
                var methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@stop

@section('footer')
   <div class="float-right d-none d-sm-block">
        <b>Version</b> @version('compact')       
    </div>
@stop