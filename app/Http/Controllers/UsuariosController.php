<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    public function index()
    {
        $usuarios = User::with('empresa')->get();
        return view('administracion.usuarios.index', compact('usuarios'));
    }
    public function create()
    {
        $empresas = \App\Models\Empresa::all();
        return view('administracion.usuarios.crear', compact('empresas'));
    }
    public function store(Request $request)
    {
        try {
            // Validaciones específicas
            $request->validate([
                'nombre' => 'required|string|max:255|min:2',
                'user' => 'required|string|max:50|min:3|unique:users,user|alpha_num',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
                'activo' => 'required|boolean',
                'is_admin' => 'required|boolean',
                'empresa_id' => 'nullable|exists:empresas,id'
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.min' => 'El nombre debe tener al menos 2 caracteres',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres',
                'user.required' => 'El nombre de usuario es obligatorio',
                'user.min' => 'El nombre de usuario debe tener al menos 3 caracteres',
                'user.max' => 'El nombre de usuario no puede exceder 50 caracteres',
                'user.unique' => 'Este nombre de usuario ya existe',
                'user.alpha_num' => 'El nombre de usuario solo puede contener letras y números',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El formato del email no es válido',
                'email.unique' => 'Este email ya está registrado',
                'email.max' => 'El email no puede exceder 255 caracteres',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.confirmed' => 'Las contraseñas no coinciden',
                'password_confirmation.required' => 'La confirmación de contraseña es obligatoria',
                'activo.required' => 'Debe especificar si el usuario está activo',
                'activo.boolean' => 'El campo activo debe ser verdadero o falso',
                'is_admin.required' => 'Debe especificar si el usuario es administrador',
                'is_admin.boolean' => 'El campo administrador debe ser verdadero o falso',
                'empresa_id.exists' => 'La sucursal seleccionada no existe'
            ]);

            $usuario = new User();
            $usuario->name = $request->nombre;
            $usuario->user = $request->user;
            $usuario->email = $request->email;
            $usuario->active = $request->activo;
            $usuario->is_admin = $request->is_admin;
            $usuario->empresa_id = $request->empresa_id ?: null;
            $usuario->password = Hash::make($request->password);
            
            $usuario->save();
            
            return redirect()->route('configuracion.usuarios.index')->with([
                'error' => 'Éxito',
                'mensaje' => 'Usuario creado exitosamente',
                'tipo' => 'alert-success'
            ]);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar errores específicos de base de datos
            $errorCode = $e->errorInfo[1];
            $errorMessage = 'Error al crear el usuario';
            
            if ($errorCode == 1062) { // Duplicate entry
                if (strpos($e->getMessage(), 'users_user_unique') !== false) {
                    $errorMessage = 'El nombre de usuario ya existe';
                } elseif (strpos($e->getMessage(), 'users_email_unique') !== false) {
                    $errorMessage = 'El email ya está registrado';
                } else {
                    $errorMessage = 'Ya existe un usuario con estos datos';
                }
            }
            
            return redirect()->back()->withInput()->with([
                'error' => 'Error de Base de Datos',
                'mensaje' => $errorMessage,
                'tipo' => 'alert-danger'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Los errores de validación se manejan automáticamente
            return redirect()->back()->withErrors($e->validator)->withInput();
            
        } catch (\Exception $e) {
            // Otros errores generales
            return redirect()->back()->withInput()->with([
                'error' => 'Error Inesperado',
                'mensaje' => 'Ocurrió un error inesperado. Por favor, inténtelo de nuevo.',
                'tipo' => 'alert-danger'
            ]);
        }
    }
    public function show($id)
    {
        $usuario = User::with('empresa')->find($id);
        $empresas = \App\Models\Empresa::all();
        return view('administracion.usuarios.editar', compact('usuario', 'empresas'));
    }

    public function update(Request $request, User $usuario)
    {
        try {
            // Validaciones específicas para actualización
            $request->validate([
                'nombre' => 'required|string|max:255|min:2',
                'user' => 'required|string|max:50|min:3|alpha_num|unique:users,user,' . $usuario->id,
                'email' => 'required|email|max:255|unique:users,email,' . $usuario->id,
                'password' => 'nullable|string|min:8|confirmed',
                'activo' => 'required|boolean',
                'is_admin' => 'required|boolean',
                'empresa_id' => 'nullable|exists:empresas,id'
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.min' => 'El nombre debe tener al menos 2 caracteres',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres',
                'user.required' => 'El nombre de usuario es obligatorio',
                'user.min' => 'El nombre de usuario debe tener al menos 3 caracteres',
                'user.max' => 'El nombre de usuario no puede exceder 50 caracteres',
                'user.unique' => 'Este nombre de usuario ya existe',
                'user.alpha_num' => 'El nombre de usuario solo puede contener letras y números',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El formato del email no es válido',
                'email.unique' => 'Este email ya está registrado',
                'email.max' => 'El email no puede exceder 255 caracteres',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.confirmed' => 'Las contraseñas no coinciden',
                'activo.required' => 'Debe especificar si el usuario está activo',
                'activo.boolean' => 'El campo activo debe ser verdadero o falso',
                'is_admin.required' => 'Debe especificar si el usuario es administrador',
                'is_admin.boolean' => 'El campo administrador debe ser verdadero o falso',
                'empresa_id.exists' => 'La sucursal seleccionada no existe'
            ]);

            $usuario->name = $request->nombre;
            $usuario->user = $request->user;
            $usuario->email = $request->email;
            $usuario->active = $request->activo;
            $usuario->is_admin = $request->is_admin;
            $usuario->empresa_id = $request->empresa_id ?: null;
            
            // Actualizar la contraseña solo si se proporciona una nueva
            if ($request->filled('password')) {
                $usuario->password = Hash::make($request->password);
            }
            
            $usuario->save();
            
            return redirect()->route('configuracion.usuarios.index')->with([
                'error' => 'Éxito',
                'mensaje' => 'Usuario modificado exitosamente',
                'tipo' => 'alert-success'
            ]);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar errores específicos de base de datos
            $errorCode = $e->errorInfo[1];
            $errorMessage = 'Error al actualizar el usuario';
            
            if ($errorCode == 1062) { // Duplicate entry
                if (strpos($e->getMessage(), 'users_user_unique') !== false) {
                    $errorMessage = 'El nombre de usuario ya existe';
                } elseif (strpos($e->getMessage(), 'users_email_unique') !== false) {
                    $errorMessage = 'El email ya está registrado';
                } else {
                    $errorMessage = 'Ya existe un usuario con estos datos';
                }
            }
            
            return redirect()->back()->withInput()->with([
                'error' => 'Error de Base de Datos',
                'mensaje' => $errorMessage,
                'tipo' => 'alert-danger'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Los errores de validación se manejan automáticamente
            return redirect()->back()->withErrors($e->validator)->withInput();
            
        } catch (\Exception $e) {
            // Otros errores generales
            return redirect()->back()->withInput()->with([
                'error' => 'Error Inesperado',
                'mensaje' => 'Ocurrió un error inesperado. Por favor, inténtelo de nuevo.',
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function toggleAdmin($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->is_admin = !$usuario->is_admin;
        $usuario->save();

        return redirect()->back()->with([
            'error' => 'Éxito',
            'mensaje' => 'Estado de administrador actualizado correctamente',
            'tipo' => 'alert-success'
        ]);
    }

    public function destroy($id)
    {
        try {
            $usuario = User::findOrFail($id);
            
            // Verificar que no se esté intentando eliminar al usuario actual
            if (auth()->id() == $usuario->id) {
                return redirect()->route('configuracion.usuarios.index')->with([
                    'error' => 'Error',
                    'mensaje' => 'No puedes eliminar tu propio usuario',
                    'tipo' => 'alert-danger'
                ]);
            }

            $nombreUsuario = $usuario->name;
            $usuario->delete();

            return redirect()->route('configuracion.usuarios.index')->with([
                'error' => 'Éxito',
                'mensaje' => "Usuario '{$nombreUsuario}' eliminado correctamente",
                'tipo' => 'alert-success'
            ]);

        } catch (\Exception $e) {
            return redirect()->route('configuracion.usuarios.index')->with([
                'error' => 'Error',
                'mensaje' => 'No se pudo eliminar el usuario',
                'tipo' => 'alert-danger'
            ]);
        }
    }
}
