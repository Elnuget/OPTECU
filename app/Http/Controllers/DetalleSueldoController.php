<?php

namespace App\Http\Controllers;

use App\Models\DetalleSueldo;
use App\Models\User;
use Illuminate\Http\Request;

class DetalleSueldoController extends Controller
{
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
        ]);

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
        ]);

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
    public function destroy(DetalleSueldo $detalleSueldo)
    {
        // Guardar datos antes de eliminar
        $usuario = $detalleSueldo->user;
        $mes = $detalleSueldo->mes;
        $ano = $detalleSueldo->ano;
        
        $detalleSueldo->delete();
        
        $redirectParams = [
            'anio' => $ano,
            'mes' => $mes,
        ];
        
        if ($usuario) {
            $redirectParams['usuario'] = $usuario->name;
        }
        
        return redirect()->route('sueldos.index', $redirectParams)
                        ->with('success', 'DETALLE DE SUELDO ELIMINADO EXITOSAMENTE');
    }
}
