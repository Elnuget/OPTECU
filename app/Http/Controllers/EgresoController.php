<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EgresoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin')->only(['edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        try {
            $query = Egreso::with('user');

            // Obtener año y mes actual como valores por defecto
            $ano = $request->get('ano', date('Y'));
            $mes = $request->get('mes', date('n'));

            // Aplicar filtros usando los valores por defecto o los proporcionados
            $query->whereYear('created_at', $ano)
                  ->whereMonth('created_at', $mes);

            $egresos = $query->orderBy('created_at', 'desc')->get();

            // Calcular totales
            $totales = [
                'egresos' => $egresos->sum('valor')
            ];

            return view('egresos.index', compact('egresos', 'totales'));
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@index: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al cargar los egresos: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function create()
    {
        return view('egresos.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric|min:0',
                'motivo' => 'required|string|max:255',
                'usuario' => 'required_if:motivo,PAGO DE SUELDO|exists:users,id'
            ]);

            $egreso = new Egreso();
            $egreso->user_id = $request->motivo === 'PAGO DE SUELDO' ? $request->usuario : auth()->id();
            $egreso->valor = $request->valor;
            
            // Si es pago de sueldo, generar motivo con mes y año
            if ($request->motivo === 'PAGO DE SUELDO') {
                $mes = $request->get('mes_pedidos', date('n'));
                $ano = $request->get('ano_pedidos', date('Y'));
                
                $meses = [
                    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
                ];
                
                $nombreMes = $meses[$mes] ?? 'MES_DESCONOCIDO';
                $egreso->motivo = "SUELDO ({$nombreMes} {$ano})";
            } else {
                $egreso->motivo = strtoupper($request->motivo);
            }
            
            $egreso->save();

            return redirect()->route('egresos.index')->with([
                'error' => 'Exito',
                'mensaje' => $request->motivo === 'PAGO DE SUELDO' ? 'Sueldo pagado exitosamente' : 'Egreso registrado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@store: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al registrar el egreso: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function show(Egreso $egreso)
    {
        return view('egresos.show', compact('egreso'));
    }

    public function edit(Egreso $egreso)
    {
        return view('egresos.edit', compact('egreso'));
    }

    public function update(Request $request, Egreso $egreso)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric|min:0',
                'motivo' => 'required|string|max:255'
            ]);

            $egreso->valor = $request->valor;
            $egreso->motivo = strtoupper($request->motivo);
            $egreso->save();

            return redirect()->route('egresos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Egreso actualizado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@update: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al actualizar el egreso: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function destroy(Egreso $egreso)
    {
        try {
            $egreso->delete();

            return redirect()->route('egresos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Egreso eliminado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@destroy: ' . $e->getMessage());
            return back()->with([
                'error' => 'Error',
                'mensaje' => 'Error al eliminar el egreso: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function finanzas()
    {
        return view('egresos.finanzas');
    }

    public function getPedidosPorUsuario(Request $request)
    {
        try {
            $usuarioId = $request->get('usuario_id');
            $mes = $request->get('mes');
            $ano = $request->get('ano');

            if (!$usuarioId || !$mes || !$ano) {
                return response()->json([
                    'total_pedidos' => 0,
                    'total_valor' => 0,
                    'mensaje' => 'Parámetros incompletos'
                ]);
            }

            // Obtener el nombre del usuario
            $usuario = \App\Models\User::find($usuarioId);
            if (!$usuario) {
                return response()->json([
                    'total_pedidos' => 0,
                    'total_valor' => 0,
                    'mensaje' => 'Usuario no encontrado'
                ]);
            }

            // Buscar pedidos por el nombre del usuario
            $query = Pedido::where('usuario', $usuario->name)
                           ->whereYear('fecha', $ano)
                           ->whereMonth('fecha', $mes);

            $pedidos = $query->get();
            $totalPedidos = $pedidos->count();
            $totalValor = $pedidos->sum('total');

            return response()->json([
                'total_pedidos' => $totalPedidos,
                'total_valor' => $totalValor,
                'mensaje' => 'Datos obtenidos correctamente',
                'usuario_nombre' => $usuario->name
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@getPedidosPorUsuario: ' . $e->getMessage());
            return response()->json([
                'total_pedidos' => 0,
                'total_valor' => 0,
                'mensaje' => 'Error al obtener los datos: ' . $e->getMessage()
            ]);
        }
    }

    public function getUltimoSueldoUsuario(Request $request)
    {
        try {
            $usuarioId = $request->get('usuario_id');
            
            if (!$usuarioId) {
                return response()->json([
                    'ultimo_sueldo' => null,
                    'mensaje' => 'Usuario no especificado'
                ]);
            }

            // Obtener el nombre del usuario
            $usuario = \App\Models\User::find($usuarioId);
            if (!$usuario) {
                return response()->json([
                    'ultimo_sueldo' => null,
                    'mensaje' => 'Usuario no encontrado'
                ]);
            }

            // Buscar el último egreso de sueldo para este usuario
            $ultimoSueldo = Egreso::where('user_id', $usuarioId)
                                  ->where('motivo', 'LIKE', 'SUELDO%')
                                  ->orderBy('created_at', 'desc')
                                  ->first();

            return response()->json([
                'ultimo_sueldo' => $ultimoSueldo ? $ultimoSueldo->valor : null,
                'mensaje' => $ultimoSueldo ? 'Último sueldo encontrado' : 'No hay sueldos anteriores'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en EgresoController@getUltimoSueldoUsuario: ' . $e->getMessage());
            return response()->json([
                'ultimo_sueldo' => null,
                'mensaje' => 'Error al obtener el último sueldo'
            ]);
        }
    }
}