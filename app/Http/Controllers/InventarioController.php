<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventario; // Asegúrate de importar el modelo Inventario
use App\Models\Pedido; // Asegúrate de importar el modelo Pedido
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{    public function __construct()
    {
        $this->middleware('can:admin')->only(['destroy']);
    }

    /**
     * Muestra una lista del recurso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Si no hay fecha seleccionada, redirigir al mes actual
            if (!$request->filled('fecha')) {
                return redirect()->route('inventario.index', [
                    'fecha' => now()->format('Y-m')
                ]);
            }

            // Verificar si la tabla existe antes de hacer consultas
            if (!Schema::hasTable('inventarios')) {
                return view('inventario.index', [
                    'inventario' => collect(),
                    'totalCantidad' => 0
                ]);
            }

            // Obtener el inventario completo
            $query = Inventario::query();
            
            // Restricción por empresa para usuarios no administradores
            $user = auth()->user();
            $userEmpresaId = $user->empresa_id;
            $isUserAdmin = $user->is_admin;
            
            // Si el usuario no es admin, manejar sus empresas asignadas
            if (!$isUserAdmin) {
                $userEmpresas = $user->todasLasEmpresas();
                
                if ($userEmpresas->count() > 0) {
                    // Si no hay filtro específico, mostrar inventario de todas sus empresas
                    if (!$request->filled('empresa_id')) {
                        $empresaIds = $userEmpresas->pluck('id')->toArray();
                        $query->whereIn('empresa_id', $empresaIds);
                    } else {
                        // Si hay filtro específico, verificar que tenga acceso a esa empresa
                        $empresaId = $request->empresa_id;
                        if ($userEmpresas->where('id', $empresaId)->count() > 0) {
                            $query->where('empresa_id', $empresaId);
                        } else {
                            // Si no tiene acceso, mostrar sus empresas por defecto
                            $empresaIds = $userEmpresas->pluck('id')->toArray();
                            $query->whereIn('empresa_id', $empresaIds);
                        }
                    }
                }
            } else if ($request->filled('empresa_id')) {
                // Si es admin, aplicar el filtro seleccionado
                $query->where('empresa_id', $request->empresa_id);
            }
            
            // Aplicar el filtro de fecha
            $query->where('fecha', 'like', $request->fecha . '%');
            
            // Incluir relación con empresa
            $query->with('empresa');
            
            // Obtener todos los datos ordenados por lugar y columna
            $inventario = $query->orderBy('lugar')
                               ->orderBy('columna')
                               ->orderBy('numero')
                               ->get();
            
            // Calcular el total de cantidad
            $totalCantidad = $inventario->sum('cantidad');

            // Obtener las empresas para el filtro según el tipo de usuario
            if ($isUserAdmin) {
                $empresas = \App\Models\Empresa::orderBy('nombre')->get();
            } else {
                // Para usuarios no admin, mostrar solo sus empresas asignadas
                $empresas = $user->todasLasEmpresas()->sortBy('nombre')->values();
            }

            return view('inventario.index', compact('inventario', 'totalCantidad', 'empresas', 'userEmpresaId', 'isUserAdmin'));
            
        } catch (\Exception $e) {
            \Log::error('Error en InventarioController@index: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al cargar el inventario: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Muestra el formulario para crear un nuevo recurso.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();
        
        // Si el usuario no es admin, mostrar sus empresas asignadas
        if (!$user->is_admin) {
            $empresas = $user->todasLasEmpresas()->sortBy('nombre')->values();
            $userEmpresaId = $user->empresa_id;
        } else {
            // Si es admin, mostrar TODAS LAS SUCURSALES
            $empresas = \App\Models\Empresa::orderBy('nombre')->get();
            $userEmpresaId = null;
        }
        
        return view('inventario.create', compact('empresas', 'userEmpresaId'));
    }

    /**
     * Almacena un recurso recién creado en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'columna' => 'required|integer',
            'numero' => 'required|integer',
            'codigo' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'empresa_id' => 'nullable|exists:empresas,id',
        ]);

        if ($request->input('lugar') === 'new') {
            $validatedData['lugar'] = $request->input('new_lugar');
        }
        
        // Restricción para usuarios no administradores
        $user = auth()->user();
        if (!$user->is_admin) {
            // Si el usuario no es admin, validar que puede usar la empresa seleccionada
            if ($validatedData['empresa_id']) {
                $userEmpresas = $user->todasLasEmpresas();
                $empresaSeleccionada = $userEmpresas->where('id', $validatedData['empresa_id'])->first();
                if (!$empresaSeleccionada) {
                    if (request()->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tiene permisos para asignar este artículo a la empresa seleccionada'
                        ], 403);
                    }
                    return redirect()->back()->withErrors(['empresa_id' => 'No tiene permisos para asignar este artículo a la empresa seleccionada']);
                }
            }
        }

        // Convertir código a mayúsculas
        $validatedData['codigo'] = strtoupper($validatedData['codigo']);

        try {
            $inventario = Inventario::create($validatedData);

            // Si es una petición AJAX, devolver JSON con el ID
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Artículo creado exitosamente',
                    'id' => $inventario->id
                ]);
            }

            return redirect()->back()->with([
                'error' => 'Exito',
                'mensaje' => 'Artículo creado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            // Si es una petición AJAX, devolver JSON
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el artículo: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with([
                'error' => 'Error',
                'mensaje' => 'Artículo no se ha creado. Detalle: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Muestra un recurso específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $inventario = Inventario::findOrFail($id);
        return view('inventario.show', compact('inventario'));
    }

    /**
     * Muestra el formulario para editar un recurso específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $inventario = Inventario::findOrFail($id);
        
        // Obtener el usuario autenticado
        $user = auth()->user();
        
        // Verificar si el usuario no admin está intentando editar un inventario que no pertenece a sus empresas
        if (!$user->is_admin) {
            $userEmpresas = $user->todasLasEmpresas();
            $tieneAcceso = $userEmpresas->where('id', $inventario->empresa_id)->count() > 0;
            
            if (!$tieneAcceso) {
                return back()->with([
                    'error' => 'Error',
                    'mensaje' => 'No tiene permisos para editar este artículo',
                    'tipo' => 'alert-danger'
                ]);
            }
        }
        
        // Si el usuario no es admin, mostrar sus empresas asignadas
        if (!$user->is_admin) {
            $empresas = $user->todasLasEmpresas()->sortBy('nombre')->values();
            $userEmpresaId = $user->empresa_id;
        } else {
            // Si es admin, mostrar TODAS LAS SUCURSALES
            $empresas = \App\Models\Empresa::orderBy('nombre')->get();
            $userEmpresaId = null;
        }
        
        return view('inventario.edit', compact('inventario', 'empresas', 'userEmpresaId'));
    }

    /**
     * Actualiza un recurso específico en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'columna' => 'required|integer',
            'numero' => 'required|integer',
            'codigo' => 'required|string|max:255',
            'valor' => 'nullable|numeric',
            'cantidad' => 'required|integer',
            'empresa_id' => 'nullable|exists:empresas,id',
        ]);

        try {
            $inventario = Inventario::findOrFail($id);
            
            // Restricción para usuarios no administradores
            $user = auth()->user();
            
            // Verificar si el usuario no admin está intentando editar un inventario que no pertenece a sus empresas
            if (!$user->is_admin) {
                $userEmpresas = $user->todasLasEmpresas();
                $tieneAcceso = $userEmpresas->where('id', $inventario->empresa_id)->count() > 0;
                
                if (!$tieneAcceso) {
                    return back()->with([
                        'error' => 'Error',
                        'mensaje' => 'No tiene permisos para editar este artículo',
                        'tipo' => 'alert-danger'
                    ]);
                }
                
                // Verificar que la empresa seleccionada esté entre las empresas asignadas al usuario
                if ($request->filled('empresa_id')) {
                    $empresaSeleccionada = $userEmpresas->where('id', $request->empresa_id)->first();
                    if (!$empresaSeleccionada) {
                        return back()->with([
                            'error' => 'Error',
                            'mensaje' => 'No tiene permisos para asignar este artículo a la empresa seleccionada',
                            'tipo' => 'alert-danger'
                        ]);
                    }
                }
            }
            $inventario->update($validatedData);

            return redirect()->route('inventario.actualizar')->with([
                'error' => 'Éxito',
                'mensaje' => 'Artículo actualizado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('inventario.actualizar')->with([
                'error' => 'Error',
                'mensaje' => 'Artículo no se ha actualizado: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Elimina un recurso específico de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $inventario = Inventario::findOrFail($id);
            $inventario->delete();

            // Mantener el parámetro de fecha si existe
            $redirectParams = [];
            if ($request->has('fecha')) {
                $redirectParams['fecha'] = $request->input('fecha');
            }

            return redirect()->route('inventario.index', $redirectParams)->with([
                'error' => 'Exito',
                'mensaje' => 'Artículo eliminado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            // Mantener el parámetro de fecha si existe
            $redirectParams = [];
            if ($request->has('fecha')) {
                $redirectParams['fecha'] = $request->input('fecha');
            }

            return redirect()->route('inventario.index', $redirectParams)->with([
                'error' => 'Error',
                'mensaje' => 'No se puede eliminar el artículo del inventario porque está asociado a pedidos existentes. Por favor, elimine los pedidos que contienen este artículo antes de intentar eliminarlo.',
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Restaura una unidad al inventario cuando se elimina de un pedido.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restaurar($id)
    {
        try {
            $inventario = Inventario::findOrFail($id);
            $inventario->increment('cantidad');

            return response()->json([
                'success' => true,
                'message' => 'Unidad restaurada exitosamente al inventario'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la unidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNumerosLugar($lugar)
    {
        // removed: pluck('numero_lugar')
        return response()->json([]);
    }

    public function leerQR()
    {
        \Log::info('Accediendo a la vista de lector QR');
        return view('inventario.leerQR');
    }

    public function actualizar()
    {
        try {
            // Obtener artículos cuya cantidad es distinta de 0
            $inventario = Inventario::where('cantidad', '!=', 0)
                ->orderBy('fecha', 'desc')
                ->get();
            
            // Obtener pedidos para el select
            $pedidos = Pedido::orderBy('numero_orden', 'desc')
                ->where('saldo', '>', 0)
                ->get();
            
            return view('inventario.actualizar', compact('inventario', 'pedidos'));
        } catch (\Exception $e) {
            \Log::error('Error en actualizar', ['error' => $e->getMessage()]);
            return redirect()->route('inventario.index')->with([
                'error' => 'Error',
                'mensaje' => 'Error al cargar los artículos: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Actualiza un registro en línea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateInline(Request $request, $id)
    {
        try {
            \Log::info('Actualizando inventario inline', [
                'id' => $id,
                'data' => $request->all()
            ]);
            
            $inventario = Inventario::findOrFail($id);
            
            // Verificar permisos de empresa para usuarios no administradores
            $user = auth()->user();
            if (!$user->is_admin) {
                $userEmpresas = $user->todasLasEmpresas();
                $tieneAcceso = $userEmpresas->where('id', $inventario->empresa_id)->count() > 0;
                
                if (!$tieneAcceso) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tiene permisos para editar este artículo'
                    ], 403);
                }
            }
            
            // Obtener solo el campo y valor que se está actualizando
            $field = $request->input('field');
            $value = $request->input('value');
            
            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo no especificado'
                ], 400);
            }

            // Validar según el campo específico
            $rules = [];
            $messages = [];
            
            switch ($field) {
                case 'numero':
                    $rules['value'] = 'required|integer';
                    $messages['value.required'] = 'El número es requerido';
                    $messages['value.integer'] = 'El número debe ser un valor entero';
                    break;
                    
                case 'lugar':
                    $rules['value'] = 'required|string|max:255';
                    $messages['value.required'] = 'El lugar es requerido';
                    $messages['value.string'] = 'El lugar debe ser texto';
                    $messages['value.max'] = 'El lugar no puede tener más de 255 caracteres';
                    break;
                    
                case 'columna':
                    $rules['value'] = 'required|integer';
                    $messages['value.required'] = 'La columna es requerida';
                    $messages['value.integer'] = 'La columna debe ser un valor entero';
                    break;
                    
                case 'codigo':
                    $rules['value'] = 'required|string|max:255';
                    $messages['value.required'] = 'El código es requerido';
                    $messages['value.string'] = 'El código debe ser texto';
                    $messages['value.max'] = 'El código no puede tener más de 255 caracteres';
                    break;
                    
                case 'cantidad':
                    $rules['value'] = 'required|integer|min:0';
                    $messages['value.required'] = 'La cantidad es requerida';
                    $messages['value.integer'] = 'La cantidad debe ser un valor entero';
                    $messages['value.min'] = 'La cantidad no puede ser menor a 0';
                    break;
                    
                case 'empresa_id':
                    $rules['value'] = 'nullable|exists:empresas,id';
                    $messages['value.exists'] = 'La empresa seleccionada no es válida';
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Campo no válido: ' . $field
                    ], 400);
            }

            try {
                $validatedData = $request->validate($rules, $messages);
                $validatedValue = $validatedData['value'];

            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Error de validación', [
                    'field' => $field,
                    'value' => $value,
                    'errors' => $e->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }

            // Convertir código a mayúsculas
            if ($field === 'codigo') {
                $validatedValue = strtoupper($validatedValue);
            }

            // Validar permisos para cambio de empresa
            if ($field === 'empresa_id' && !$user->is_admin && $validatedValue) {
                $userEmpresas = $user->todasLasEmpresas();
                $empresaSeleccionada = $userEmpresas->where('id', $validatedValue)->first();
                if (!$empresaSeleccionada) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tiene permisos para asignar este artículo a la empresa seleccionada'
                    ], 403);
                }
            }

            // Actualizar solo el campo específico
            $inventario->update([$field => $validatedValue]);

            // Preparar respuesta con parámetros actuales preservados
            $currentParams = [];
            if (request()->filled('fecha')) {
                $currentParams['fecha'] = request('fecha');
            }
            if (request()->filled('empresa_id')) {
                $currentParams['empresa_id'] = request('empresa_id');
            }

            return response()->json([
                'success' => true,
                'message' => 'Artículo actualizado correctamente',
                'redirect_params' => $currentParams
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar inventario inline', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el artículo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza la fecha de múltiples artículos a la fecha actual
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function actualizarFechas(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer|exists:inventarios,id'
            ]);

            $fechaActual = now()->format('Y-m-d');
            
            Inventario::whereIn('id', $request->ids)
                ->update(['fecha' => $fechaActual]);

            return response()->json([
                'success' => true,
                'message' => 'Fechas actualizadas correctamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar fechas de inventario', [
                'error' => $e->getMessage(),
                'ids' => $request->ids ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las fechas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea nuevos registros de inventario con la fecha actual
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function crearNuevosRegistros(Request $request)
    {
        try {
            $request->validate([
                'articulos' => 'required|array',
                'articulos.*.codigo' => 'required|string',
                'articulos.*.cantidad' => 'required|integer',
                'articulos.*.lugar' => 'required|string',
                'articulos.*.columna' => 'required|string',
                'articulos.*.numero' => 'nullable|integer',
                'articulos.*.fecha_original' => 'required|string'
            ]);

            DB::beginTransaction();
            
            $articulos = $request->input('articulos');
            $creados = [];
            
            foreach ($articulos as $articulo) {
                // Calcular fecha del siguiente mes basado en la fecha original
                $fechaOriginal = \Carbon\Carbon::parse($articulo['fecha_original']);
                $fechaSiguienteMes = $fechaOriginal->copy()->addMonth()->startOfMonth();
                
                $creado = Inventario::create([
                    'codigo' => $articulo['codigo'],
                    'cantidad' => $articulo['cantidad'],
                    'lugar' => $articulo['lugar'],
                    'columna' => $articulo['columna'],
                    'fecha' => $fechaSiguienteMes,
                    'numero' => $articulo['numero'] ?? 1
                ]);
                $creados[] = $creado->id;
            }

            DB::commit();
            
            \Log::info('Registros creados correctamente', ['ids' => $creados]);
            
            return response()->json([
                'success' => true,
                'message' => 'Registros creados correctamente',
                'ids' => $creados
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Error de validación al crear registros: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error de validación: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear nuevos registros: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al crear los registros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resta una unidad del inventario cuando se selecciona en un pedido.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restar($id)
    {
        try {
            $inventario = Inventario::findOrFail($id);
            
            // Verificar que haya suficiente cantidad disponible
            if ($inventario->cantidad <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay unidades disponibles para restar'
                ], 400);
            }
            
            // Restar una unidad
            $inventario->decrement('cantidad');
            
            // Actualizar el número de orden (opcional)
            $pedidoActual = Pedido::orderBy('numero_orden', 'desc')->first();
            if ($pedidoActual) {
                $inventario->orden = $pedidoActual->numero_orden;
                $inventario->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Unidad restada exitosamente del inventario',
                'cantidad_restante' => $inventario->cantidad
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al restar unidad de inventario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al restar la unidad: ' . $e->getMessage()
            ], 500);
        }
    }
}