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

@section('css')
<style>
.empresas-column {
    min-width: 200px;
}
.badge-empresas {
    display: inline-block;
    margin: 2px;
    font-size: 0.75em;
}
.empresa-principal {
    border: 2px solid #007bff;
    background-color: #007bff !important;
}
.empresa-adicional {
    background-color: #6c757d !important;
}
.multiple-empresas-indicator {
    font-size: 0.7em;
    color: #28a745;
    font-weight: bold;
}
</style>
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
                    <td>Sucursales</td>
                    <td>Activo</td>
                    <td>Administrador</td>
                    <td>Acciones</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $index => $u)
                @php
                    $totalEmpresas = 0;
                    if ($u->empresa) $totalEmpresas++;
                    $totalEmpresas += $u->empresas->count();
                @endphp
                <tr class="{{ $totalEmpresas > 1 ? 'table-info' : '' }}" 
                    @if($totalEmpresas > 1) 
                        title="Usuario con acceso múltiple a {{ $totalEmpresas }} empresas"
                    @endif>
                        <td>
                            {{$index +1 }}
                            @if($totalEmpresas > 1)
                                <span class="badge badge-info badge-sm ml-1">
                                    <i class="fas fa-building"></i>
                                </span>
                            @endif
                        </td>
                        <td>{{$u->name}}</td>
                        <td>{{$u->user}}</td>
                        <td>{{$u->email}}</td>
                        <td class="empresas-column">
                        @php
                            $todasLasEmpresas = collect();
                            
                            // Agregar empresa principal si existe
                            if ($u->empresa) {
                                $todasLasEmpresas->push($u->empresa);
                            }
                            
                            // Agregar empresas adicionales
                            $empresasAdicionales = $u->empresas;
                            $todasLasEmpresas = $todasLasEmpresas->merge($empresasAdicionales)->unique('id');
                        @endphp
                        
                        @if ($todasLasEmpresas->count() > 0)
                            <div class="d-flex flex-column" 
                                 @if($todasLasEmpresas->count() > 1) 
                                     data-toggle="tooltip" 
                                     data-html="true"
                                     title="<strong>Empresas asignadas:</strong><br>
                                            @foreach ($todasLasEmpresas as $empresa)
                                                • {{ $empresa->nombre }}{{ $u->empresa_id == $empresa->id ? ' (Principal)' : ' (Adicional)' }}<br>
                                            @endforeach"
                                 @endif>
                                @foreach ($todasLasEmpresas as $empresa)
                                    <span class="badge badge-empresas {{ $u->empresa_id == $empresa->id ? 'empresa-principal' : 'empresa-adicional' }} mb-1">
                                        {{ $empresa->nombre }}
                                        @if ($u->empresa_id == $empresa->id)
                                            <small>(Principal)</small>
                                        @endif
                                    </span>
                                @endforeach
                                @if ($todasLasEmpresas->count() > 1)
                                    <small class="multiple-empresas-indicator mt-1">
                                        <i class="fas fa-building"></i> {{ $todasLasEmpresas->count() }} empresas
                                    </small>
                                @endif
                            </div>
                        @else
                           <span class="text-muted">
                               <i class="fas fa-building-slash"></i> Sin Sucursal
                           </span>
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
            // Inicializar DataTable
            $('#example').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                },
                "columnDefs": [
                    {
                        "targets": 4, // Columna de Sucursales
                        "orderable": false // Desactivar ordenamiento en esta columna
                    }
                ]
            });

            // Inicializar tooltips para usuarios con múltiples empresas
            $('[data-toggle="tooltip"]').tooltip({
                placement: 'top',
                html: true,
                container: 'body'
            });
        });
        
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