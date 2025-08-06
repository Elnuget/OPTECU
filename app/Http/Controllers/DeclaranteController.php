<?php

namespace App\Http\Controllers;

use App\Models\Declarante;
use Illuminate\Http\Request;

class DeclaranteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $declarantes = Declarante::all();
        return view('declarantes.index', compact('declarantes'));
    }

    /**
     * Lista los declarantes para AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listar()
    {
        try {
            $declarantes = Declarante::select('id', 'nombre', 'ruc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $declarantes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar declarantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('declarantes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|unique:declarante,ruc|max:255',
            'firma' => 'nullable|string'
        ]);

        $declarante = Declarante::create([
            'nombre' => $request->nombre,
            'ruc' => $request->ruc,
            'firma' => $request->firma
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $declarante,
                'message' => 'Declarante creado exitosamente'
            ]);
        }

        return redirect()->route('declarantes.index')
            ->with('success', 'Declarante creado exitosamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $declarante = Declarante::findOrFail($id);
        return view('declarantes.show', compact('declarante'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $declarante = Declarante::findOrFail($id);
        return view('declarantes.edit', compact('declarante'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $declarante = Declarante::findOrFail($id);
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|max:255|unique:declarante,ruc,' . $id,
            'firma' => 'nullable|string'
        ]);

        $declarante->update([
            'nombre' => $request->nombre,
            'ruc' => $request->ruc,
            'firma' => $request->firma
        ]);

        return redirect()->route('declarantes.index')
            ->with('success', 'Declarante actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $declarante = Declarante::findOrFail($id);
        $declarante->delete();

        return redirect()->route('declarantes.index')
            ->with('success', 'Declarante eliminado exitosamente');
    }
}
