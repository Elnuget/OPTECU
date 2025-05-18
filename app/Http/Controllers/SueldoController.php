<?php

namespace App\Http\Controllers;

use App\Models\Sueldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        
        $sueldos = $query->orderBy('fecha', 'desc')->get();
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
            'valor' => 'required|numeric'
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

        $sueldo = Sueldo::create($request->all());

        return redirect()->route('sueldos.index')
            ->with([
                'mensaje' => 'SUELDO REGISTRADO CORRECTAMENTE',
                'tipo' => 'alert-success'
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function show(Sueldo $sueldo)
    {
        return view('sueldos.show', compact('sueldo'));
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
            'valor' => 'required|numeric'
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

        $sueldo->update($request->all());

        return redirect()->route('sueldos.index')
            ->with([
                'mensaje' => 'SUELDO ACTUALIZADO CORRECTAMENTE',
                'tipo' => 'alert-success'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sueldo $sueldo)
    {
        $sueldo->delete();

        return redirect()->route('sueldos.index')
            ->with([
                'mensaje' => 'SUELDO ELIMINADO CORRECTAMENTE',
                'tipo' => 'alert-success'
            ]);
    }
} 