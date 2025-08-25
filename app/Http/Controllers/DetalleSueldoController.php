<?php

namespace App\Http\Controllers;

use App\Models\DetalleSueldo;
use App\Models\User;
use Illuminate\Http\Request;

class DetalleSueldoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Este método no se usa ya que los detalles se muestran en sueldos.index
        return redirect()->route('sueldos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $usuarios = User::whereNull('deleted_at')
                        ->where('name', '!=', '')
                        ->orderBy('name')
                        ->get();
        
        // Obtener datos pre-seleccionados desde la URL
        $preselectedData = [
            'usuario' => $request->get('usuario'),
            'mes' => $request->get('mes', date('m')),
            'anio' => $request->get('anio', date('Y'))
        ];
        
        // Si hay un usuario preseleccionado, encontrar su ID
        $usuarioPreseleccionado = null;
        if ($preselectedData['usuario']) {
            $usuarioPreseleccionado = User::whereNull('deleted_at')
                                        ->where('name', $preselectedData['usuario'])
                                        ->first();
        }
        
        return view('detalles-sueldo.create', compact('usuarios', 'preselectedData', 'usuarioPreseleccionado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|integer|between:1,12',
            'ano' => 'required|integer|min:2020',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'created_at' => 'nullable|date',
        ]);

        // Si no se proporciona una fecha de creación, usamos la fecha actual
        if (!isset($validatedData['created_at']) || empty($validatedData['created_at'])) {
            $validatedData['created_at'] = now();
        }
        
        $detalle = DetalleSueldo::create($validatedData);
        
        // Obtener datos del usuario para redireccionar con filtros
        $usuario = User::find($validatedData['user_id']);
        
        return redirect()->route('sueldos.index', [
            'anio' => $validatedData['ano'],
            'mes' => $validatedData['mes'],
            'usuario' => $usuario->name
        ])->with('success', 'DETALLE DE SUELDO CREADO EXITOSAMENTE');
    }

    /**
     * Display the specified resource.
     */
    public function show(DetalleSueldo $detalleSueldo)
    {
        return view('detalles-sueldo.show', compact('detalleSueldo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DetalleSueldo $detalleSueldo)
    {
        $usuarios = User::whereNull('deleted_at')
                        ->where('name', '!=', '')
                        ->orderBy('name')
                        ->get();
        return view('detalles-sueldo.edit', compact('detalleSueldo', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DetalleSueldo $detalleSueldo)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|integer|between:1,12',
            'ano' => 'required|integer|min:2020',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'created_at' => 'nullable|date',
        ]);
        
        // Si no se proporciona una fecha de creación, mantenemos la existente
        if (!isset($validatedData['created_at']) || empty($validatedData['created_at'])) {
            $validatedData['created_at'] = $detalleSueldo->created_at;
        }

        $detalleSueldo->update($validatedData);
        
        // Obtener datos del usuario para redireccionar con filtros
        $usuario = User::find($validatedData['user_id']);

        return redirect()->route('sueldos.index', [
            'anio' => $validatedData['ano'],
            'mes' => $validatedData['mes'],
            'usuario' => $usuario->name
        ])->with('success', 'DETALLE DE SUELDO ACTUALIZADO EXITOSAMENTE');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            \Log::info('DetalleSueldo destroy - ID recibido:', ['id' => $id]);
            
            // Buscar el detalle de sueldo por ID
            $detalleSueldo = DetalleSueldo::find($id);
            
            if (!$detalleSueldo) {
                \Log::error('DetalleSueldo no encontrado:', ['id' => $id]);
                return redirect()->back()
                                ->with('error', 'El detalle de sueldo no fue encontrado.');
            }
            
            // Guardar datos antes de eliminar
            $usuario = $detalleSueldo->user;
            $mes = $detalleSueldo->mes;
            $ano = $detalleSueldo->ano;
            
            \Log::info('DetalleSueldo ANTES de eliminar:', [
                'id' => $detalleSueldo->id,
                'user_id' => $detalleSueldo->user_id,
                'mes' => $mes,
                'ano' => $ano,
                'usuario_name' => $usuario ? $usuario->name : 'null'
            ]);
            
            // Eliminar el registro (soft delete)
            $deleted = $detalleSueldo->delete();
            
            \Log::info('DetalleSueldo DESPUÉS de eliminar:', [
                'deleted_result' => $deleted,
                'exists_normal' => DetalleSueldo::find($detalleSueldo->id) ? 'yes' : 'no',
                'exists_with_trashed' => DetalleSueldo::withTrashed()->find($detalleSueldo->id) ? 'yes' : 'no'
            ]);
            
            $redirectParams = [
                'anio' => $ano,
                'mes' => str_pad($mes, 2, '0', STR_PAD_LEFT), // Asegurar formato MM
            ];
            
            if ($usuario) {
                $redirectParams['usuario'] = $usuario->name;
            }
            
            $redirectUrl = route('sueldos.index', $redirectParams);
            
            \Log::info('Redirect info:', [
                'params' => $redirectParams,
                'url' => $redirectUrl
            ]);
            
            return redirect($redirectUrl)
                            ->with('success', 'DETALLE DE SUELDO ELIMINADO EXITOSAMENTE');
                            
        } catch (\Exception $e) {
            \Log::error('Error eliminando DetalleSueldo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                            ->with('error', 'Error al eliminar el detalle de sueldo: ' . $e->getMessage());
        }
    }
}
