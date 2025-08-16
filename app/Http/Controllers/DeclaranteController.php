<?php

namespace App\Http\Controllers;

use App\Models\Declarante;
use Illuminate\Http\Request;

class DeclaranteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
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
            // Seleccionar todos los campos del declarante
            $declarantes = Declarante::all();
            
            // Para cada declarante, calcular métricas de facturas
            foreach ($declarantes as $declarante) {
                // Aquí puedes agregar la lógica para calcular base_gravable, iva_debito, total_facturado y cantidad_facturas
                // Si tienes una relación con facturas, sería algo así:
                $facturas = \App\Models\Factura::where('declarante_id', $declarante->id)->get();
                
                $declarante->base_gravable = $facturas->sum('monto') ?? 0;
                $declarante->iva_debito = $facturas->sum('iva') ?? 0;
                $declarante->total_facturado = $declarante->base_gravable + $declarante->iva_debito;
                $declarante->cantidad_facturas = $facturas->count();
            }
            
            return response()->json([
                'success' => true,
                'declarantes' => $declarantes
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
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('declarantes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $declarante = Declarante::findOrFail($id);
        
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'declarante' => $declarante
            ]);
        }
        
        return view('declarantes.show', compact('declarante'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $declarante = Declarante::findOrFail($id);
        $declarante->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Declarante eliminado exitosamente'
            ]);
        }

        return redirect()->route('declarantes.index')
            ->with('success', 'Declarante eliminado exitosamente');
    }

    /**
     * Obtener las facturas de un declarante
     *
     * @param int $id ID del declarante
     * @return \Illuminate\Http\JsonResponse
     */
    public function facturas($id)
    {
        try {
            $declarante = Declarante::findOrFail($id);
            
            // Aquí debes cargar las facturas relacionadas con el declarante
            // Ajusta esto según tu estructura de base de datos
            $facturas = \App\Models\Factura::where('declarante_id', $id)->get();
            
            return response()->json([
                'success' => true,
                'declarante' => $declarante,
                'facturas' => $facturas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar facturas: ' . $e->getMessage()
            ], 500);
        }
    }
}
