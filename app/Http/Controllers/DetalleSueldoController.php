<?php

namespace App\Http\Controllers;

use App\Models\DetalleSueldo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetalleSueldoController extends Controller
{
    /**
     * Muestra una lista de los detalles de sueldos.
     */
    public function index()
    {
        $detalles = DetalleSueldo::with('user')->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->get();
        
        return view('detalles_sueldos.index', compact('detalles'));
    }

    /**
     * Muestra el formulario para crear un nuevo detalle.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('detalles_sueldos.create', compact('users'));
    }

    /**
     * Almacena un nuevo detalle de sueldo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|string|size:2',
            'ano' => 'required|integer|min:2000|max:2100',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();
            
            $detalle = DetalleSueldo::create($validated);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'DETALLE DE SUELDO CREADO CORRECTAMENTE',
                'data' => $detalle
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ERROR AL CREAR EL DETALLE DE SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un detalle de sueldo específico.
     */
    public function show(DetalleSueldo $detalleSueldo)
    {
        return response()->json([
            'success' => true,
            'data' => $detalleSueldo->load('user')
        ]);
    }

    /**
     * Muestra el formulario para editar un detalle.
     */
    public function edit(DetalleSueldo $detalleSueldo)
    {
        $users = User::orderBy('name')->get();
        return view('detalles_sueldos.edit', compact('detalleSueldo', 'users'));
    }

    /**
     * Actualiza un detalle de sueldo específico.
     */
    public function update(Request $request, DetalleSueldo $detalleSueldo)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|string|size:2',
            'ano' => 'required|integer|min:2000|max:2100',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();
            
            $detalleSueldo->update($validated);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'DETALLE DE SUELDO ACTUALIZADO CORRECTAMENTE',
                'data' => $detalleSueldo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ERROR AL ACTUALIZAR EL DETALLE DE SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un detalle de sueldo específico.
     */
    public function destroy(DetalleSueldo $detalleSueldo)
    {
        try {
            DB::beginTransaction();
            
            $detalleSueldo->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'DETALLE DE SUELDO ELIMINADO CORRECTAMENTE'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ERROR AL ELIMINAR EL DETALLE DE SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los detalles de sueldo por usuario y período
     */
    public function getDetallesPorPeriodo(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|string|size:2',
            'ano' => 'required|integer|min:2000|max:2100'
        ]);

        $detalles = DetalleSueldo::with('user')
            ->where('user_id', $validated['user_id'])
            ->where('mes', $validated['mes'])
            ->where('ano', $validated['ano'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $detalles
        ]);
    }
} 