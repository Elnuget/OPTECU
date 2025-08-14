<?php

namespace App\Http\Controllers;

use App\Models\Sueldo;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SueldoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sueldos = Sueldo::with(['user', 'empresa'])->orderBy('fecha', 'desc')->paginate(10);
        return view('sueldos.index', compact('sueldos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $usuarios = User::all();
        $empresas = Empresa::all();
        return view('sueldos.create', compact('usuarios', 'empresas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:191',
            'valor' => 'required|numeric|min:0',
        ]);

        Sueldo::create($request->all());

        return redirect()->route('sueldos.index')
            ->with('success', 'Sueldo registrado correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\View\View
     */
    public function show(Sueldo $sueldo)
    {
        return view('sueldos.show', compact('sueldo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\View\View
     */
    public function edit(Sueldo $sueldo)
    {
        $usuarios = User::all();
        $empresas = Empresa::all();
        return view('sueldos.edit', compact('sueldo', 'usuarios', 'empresas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Sueldo $sueldo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:191',
            'valor' => 'required|numeric|min:0',
        ]);

        $sueldo->update($request->all());

        return redirect()->route('sueldos.index')
            ->with('success', 'Sueldo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Sueldo $sueldo)
    {
        $sueldo->delete();

        return redirect()->route('sueldos.index')
            ->with('success', 'Sueldo eliminado correctamente');
    }
}
