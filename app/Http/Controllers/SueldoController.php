<?php

namespace App\Http\Controllers;

use App\Models\Sueldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SueldoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', date('m'));
        
        $query = Sueldo::query();
        
        // Aplicar filtros si están presentes
        if ($ano) {
            $query->whereYear('fecha', $ano);
        }
        
        if ($mes) {
            $query->whereMonth('fecha', $mes);
        }
        
        $sueldos = $query->with('user')->orderBy('fecha', 'desc')->get();
        $totalSueldos = $sueldos->sum('valor');
        
        return view('sueldos.index', compact('sueldos', 'totalSueldos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sueldos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with([
                    'mensaje' => 'ERROR AL GUARDAR EL SUELDO',
                    'tipo' => 'alert-danger'
                ]);
        }

        try {
            Sueldo::create($request->all());

            return redirect()->route('sueldos.index')
                ->with([
                    'mensaje' => 'SUELDO REGISTRADO CORRECTAMENTE',
                    'tipo' => 'alert-success'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'mensaje' => 'ERROR AL GUARDAR EL SUELDO: ' . $e->getMessage(),
                    'tipo' => 'alert-danger'
                ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function show(Sueldo $sueldo)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $sueldo->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function edit(Sueldo $sueldo)
    {
        return view('sueldos.edit', compact('sueldo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sueldo $sueldo)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with([
                    'mensaje' => 'ERROR AL ACTUALIZAR EL SUELDO',
                    'tipo' => 'alert-danger'
                ]);
        }

        try {
            $sueldo->update($request->all());

            return redirect()->route('sueldos.index')
                ->with([
                    'mensaje' => 'SUELDO ACTUALIZADO CORRECTAMENTE',
                    'tipo' => 'alert-success'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'mensaje' => 'ERROR AL ACTUALIZAR EL SUELDO: ' . $e->getMessage(),
                    'tipo' => 'alert-danger'
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sueldo $sueldo)
    {
        try {
            $sueldo->delete();

            return redirect()->route('sueldos.index')
                ->with([
                    'mensaje' => 'SUELDO ELIMINADO CORRECTAMENTE',
                    'tipo' => 'alert-success'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with([
                    'mensaje' => 'ERROR AL ELIMINAR EL SUELDO: ' . $e->getMessage(),
                    'tipo' => 'alert-danger'
                ]);
        }
    }
} 