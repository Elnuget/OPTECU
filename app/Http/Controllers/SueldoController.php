<?php

namespace App\Http\Controllers;

use App\Models\Sueldo;
use App\Models\Egreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            DB::beginTransaction();

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'fecha' => 'required|date',
                'descripcion' => 'required|string',
                'valor' => 'required|numeric'
            ]);

            $sueldo = Sueldo::create([
                'user_id' => $request->user_id,
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'valor' => $request->valor
            ]);

            // Crear el registro de egreso correspondiente
            $egreso = new Egreso();
            $egreso->user_id = $request->user_id;
            $egreso->valor = $request->valor;
            $egreso->motivo = $request->descripcion;
            $egreso->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'SUELDO REGISTRADO CORRECTAMENTE',
                'data' => $sueldo
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar sueldo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL REGISTRAR EL SUELDO: ' . $e->getMessage()
            ], 500);
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
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'fecha' => 'required|date',
                'descripcion' => 'required|string',
                'valor' => 'required|numeric'
            ]);

            $sueldo = Sueldo::findOrFail($id);
            
            $sueldo->update([
                'user_id' => $request->user_id,
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'valor' => $request->valor
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'SUELDO ACTUALIZADO CORRECTAMENTE',
                'data' => $sueldo
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar sueldo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL ACTUALIZAR EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sueldo  $sueldo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $sueldo = Sueldo::findOrFail($id);
            $sueldo->delete();

            return response()->json([
                'success' => true,
                'mensaje' => 'SUELDO ELIMINADO CORRECTAMENTE'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar sueldo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL ELIMINAR EL SUELDO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guarda un valor de sueldo vía AJAX
     */
    public function guardarValor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha' => 'required|date',
                'valor' => 'required|numeric|min:0',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'DATOS INVÁLIDOS',
                    'errores' => $validator->errors()
                ], 422);
            }

            // Buscar si existe un registro para esta fecha y usuario
            $sueldo = Sueldo::where('user_id', $request->user_id)
                           ->where('fecha', $request->fecha)
                           ->where('descripcion', 'REGISTROCOBRO')
                           ->first();

            if ($sueldo) {
                // Si existe, actualizar el valor
                $sueldo->valor = $request->valor;
                $sueldo->save();
            } else {
                // Si no existe, crear nuevo registro
                $sueldo = Sueldo::create([
                    'fecha' => $request->fecha,
                    'descripcion' => 'REGISTROCOBRO',
                    'valor' => $request->valor,
                    'user_id' => $request->user_id
                ]);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'VALOR GUARDADO CORRECTAMENTE',
                'data' => $sueldo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL GUARDAR EL VALOR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los sueldos con descripción REGISTROCOBRO
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegistrosCobro(Request $request)
    {
        try {
            $query = Sueldo::where('descripcion', 'REGISTROCOBRO');
            
            // Filtros por año y mes
            if ($request->has('ano')) {
                $query->whereYear('fecha', $request->ano);
            }
            
            if ($request->has('mes')) {
                $query->whereMonth('fecha', $request->mes);
            }
            
            // Filtros opcionales
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            if ($request->has('fecha_inicio')) {
                $query->where('fecha', '>=', $request->fecha_inicio);
            }
            
            if ($request->has('fecha_fin')) {
                $query->where('fecha', '<=', $request->fecha_fin);
            }
            
            $registros = $query->with('user')
                             ->orderBy('fecha', 'desc')
                             ->get();
            
            return response()->json([
                'success' => true,
                'data' => $registros,
                'total' => $registros->sum('valor')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER LOS REGISTROS DE COBRO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el total de registros de cobro para un usuario en un período específico
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotalRegistrosCobro(Request $request)
    {
        try {
            $query = Sueldo::where('descripcion', 'REGISTROCOBRO');
            
            // Filtros por año y mes
            if ($request->has('ano')) {
                $query->whereYear('fecha', $request->ano);
            }
            
            if ($request->has('mes')) {
                $query->whereMonth('fecha', $request->mes);
            }
            
            // Filtro por usuario
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            $total = $query->sum('valor');
            
            return response()->json([
                'success' => true,
                'total' => $total
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'ERROR AL OBTENER EL TOTAL DE REGISTROS DE COBRO: ' . $e->getMessage()
            ], 500);
        }
    }
} 