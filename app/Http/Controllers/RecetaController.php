<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\HistorialClinico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecetaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $recetas = Receta::with('historialClinico')->get();
        return view('recetas.index', compact('recetas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $historialId
     * @return \Illuminate\Http\Response
     */
    public function create($historialId = null)
    {
        $historialClinico = null;
        if ($historialId) {
            $historialClinico = HistorialClinico::findOrFail($historialId);
        } else {
            $historiales = HistorialClinico::orderBy('fecha', 'desc')->get();
            return view('recetas.seleccionar_historial', compact('historiales'));
        }
        
        return view('recetas.create', compact('historialClinico'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'historial_clinico_id' => 'required|exists:historiales_clinicos,id',
                'od_esfera' => 'nullable|string|max:50',
                'od_cilindro' => 'nullable|string|max:50',
                'od_eje' => 'nullable|string|max:50',
                'od_adicion' => 'nullable|string|max:50',
                'oi_esfera' => 'nullable|string|max:50',
                'oi_cilindro' => 'nullable|string|max:50',
                'oi_eje' => 'nullable|string|max:50',
                'oi_adicion' => 'nullable|string|max:50',
                'dp' => 'nullable|string|max:50',
                'observaciones' => 'nullable|string|max:1000',
                'tipo' => 'nullable|string|max:255',
            ]);

            $receta = Receta::create($validatedData);
            
            return redirect()->route('recetas.show', $receta->id)
                ->with('success', 'Receta creada exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al crear receta: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la receta: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $receta = Receta::with('historialClinico')->findOrFail($id);
        return view('recetas.show', compact('receta'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $receta = Receta::findOrFail($id);
        $historialClinico = $receta->historialClinico;
        return view('recetas.edit', compact('receta', 'historialClinico'));
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
        try {
            $receta = Receta::findOrFail($id);
            
            $validatedData = $request->validate([
                'od_esfera' => 'nullable|string|max:50',
                'od_cilindro' => 'nullable|string|max:50',
                'od_eje' => 'nullable|string|max:50',
                'od_adicion' => 'nullable|string|max:50',
                'oi_esfera' => 'nullable|string|max:50',
                'oi_cilindro' => 'nullable|string|max:50',
                'oi_eje' => 'nullable|string|max:50',
                'oi_adicion' => 'nullable|string|max:50',
                'dp' => 'nullable|string|max:50',
                'observaciones' => 'nullable|string|max:1000',
                'tipo' => 'nullable|string|max:255',
            ]);

            $receta->update($validatedData);
            
            return redirect()->route('recetas.show', $receta->id)
                ->with('success', 'Receta actualizada exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar receta: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la receta: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $receta = Receta::findOrFail($id);
            $receta->delete();
            
            return redirect()->route('recetas.index')
                ->with('success', 'Receta eliminada exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar receta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la receta: ' . $e->getMessage());
        }
    }
}
