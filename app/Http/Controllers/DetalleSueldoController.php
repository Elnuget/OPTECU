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
    public function create()
    {
        $usuarios = User::orderBy('name')->get();
        return view('detalles-sueldo.create', compact('usuarios'));
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
            'valor' => 'required|numeric|min:0',
        ]);

        DetalleSueldo::create($validatedData);

        return redirect()->route('sueldos.index')
                        ->with('success', 'DETALLE DE SUELDO CREADO EXITOSAMENTE');
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
        $usuarios = User::orderBy('name')->get();
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
            'valor' => 'required|numeric|min:0',
        ]);

        $detalleSueldo->update($validatedData);

        return redirect()->route('sueldos.index')
                        ->with('success', 'DETALLE DE SUELDO ACTUALIZADO EXITOSAMENTE');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleSueldo $detalleSueldo)
    {
        $detalleSueldo->delete();
        
        return redirect()->route('sueldos.index')
                        ->with('success', 'DETALLE DE SUELDO ELIMINADO EXITOSAMENTE');
    }
}
