<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use App\Models\Pedido;
use App\Models\Empresa;
use App\Models\MensajesEnviados;
use App\Models\MensajePredeterminado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelemarketingController extends Controller
{
    public function index(Request $request)
    {
        // Verificar si el usuario está asociado a una empresa y no es admin
        $userEmpresaId = null;
        $isUserAdmin = auth()->user()->is_admin ?? false;
        
        if (!$isUserAdmin && auth()->user()->empresa_id) {
            $userEmpresaId = auth()->user()->empresa_id;
        }

        // Obtener empresas para el filtro
        $empresas = Empresa::all();

        // Unión de clientes y pacientes
        $clientesQuery = DB::table('pedidos')
            ->select(
                'cliente as nombre',
                DB::raw('NULL as apellidos'),
                'celular',
                DB::raw("'cliente' as tipo"),
                'empresa_id',
                DB::raw('MAX(fecha) as ultimo_pedido'),
                'id'
            )
            ->whereNotNull('cliente')
            ->where('cliente', '!=', '')
            ->whereNotNull('celular')
            ->where('celular', '!=', '')
            ->groupBy('cliente', 'celular', 'empresa_id', 'id');

        $pacientesQuery = DB::table('historiales_clinicos')
            ->select(
                'nombres as nombre',
                'apellidos',
                'celular',
                DB::raw("'paciente' as tipo"),
                'empresa_id',
                DB::raw('MAX(fecha) as ultimo_pedido'),
                'id'
            )
            ->whereNotNull('nombres')
            ->where('nombres', '!=', '')
            ->whereNotNull('celular')
            ->where('celular', '!=', '')
            ->groupBy('nombres', 'apellidos', 'celular', 'empresa_id', 'id');

        // Obtener clientes únicos de pedidos
        $clientes = DB::table('pedidos')
            ->leftJoin('empresas', 'pedidos.empresa_id', '=', 'empresas.id')
            ->select(
                'pedidos.cliente as nombre',
                DB::raw('NULL as apellidos'),
                'pedidos.celular',
                DB::raw("'cliente' as tipo"),
                'pedidos.empresa_id',
                'empresas.nombre as empresa_nombre',
                DB::raw('MAX(pedidos.fecha) as ultimo_pedido'),
                DB::raw('MIN(pedidos.id) as id')
            )
            ->whereNotNull('pedidos.cliente')
            ->where('pedidos.cliente', '!=', '')
            ->whereNotNull('pedidos.celular')
            ->where('pedidos.celular', '!=', '');

        // Aplicar filtros de empresa para clientes
        if ($request->filled('empresa_id')) {
            $clientes->where('pedidos.empresa_id', $request->get('empresa_id'));
        }
        if (!$isUserAdmin && $userEmpresaId) {
            $clientes->where('pedidos.empresa_id', $userEmpresaId);
        }

        $clientes = $clientes->groupBy('pedidos.cliente', 'pedidos.celular', 'pedidos.empresa_id', 'empresas.nombre');

        // Obtener pacientes únicos de historiales clínicos
        $pacientes = DB::table('historiales_clinicos')
            ->leftJoin('empresas', 'historiales_clinicos.empresa_id', '=', 'empresas.id')
            ->select(
                'historiales_clinicos.nombres as nombre',
                'historiales_clinicos.apellidos',
                'historiales_clinicos.celular',
                DB::raw("'paciente' as tipo"),
                'historiales_clinicos.empresa_id',
                'empresas.nombre as empresa_nombre',
                DB::raw('MAX(historiales_clinicos.fecha) as ultimo_pedido'),
                DB::raw('MIN(historiales_clinicos.id) as id')
            )
            ->whereNotNull('historiales_clinicos.nombres')
            ->where('historiales_clinicos.nombres', '!=', '')
            ->whereNotNull('historiales_clinicos.celular')
            ->where('historiales_clinicos.celular', '!=', '');

        // Aplicar filtros de empresa para pacientes
        if ($request->filled('empresa_id')) {
            $pacientes->where('historiales_clinicos.empresa_id', $request->get('empresa_id'));
        }
        if (!$isUserAdmin && $userEmpresaId) {
            $pacientes->where('historiales_clinicos.empresa_id', $userEmpresaId);
        }

        $pacientes = $pacientes->groupBy('historiales_clinicos.nombres', 'historiales_clinicos.apellidos', 'historiales_clinicos.celular', 'historiales_clinicos.empresa_id', 'empresas.nombre');

        // Combinar resultados según el filtro de tipo
        if ($request->filled('tipo_cliente')) {
            if ($request->get('tipo_cliente') === 'cliente') {
                $clientesData = $clientes->get();
            } else {
                $clientesData = $pacientes->get();
            }
        } else {
            // Unir ambos conjuntos de datos
            $clientesData = $clientes->get()->merge($pacientes->get());
        }

        // Transformar los datos para la vista
        $clientes = $clientesData->map(function ($item) {
            $empresa = null;
            if ($item->empresa_id && $item->empresa_nombre) {
                $empresa = (object) ['nombre' => $item->empresa_nombre];
            }

            return (object) [
                'id' => $item->tipo . '_' . $item->id, // Crear ID único combinando tipo e id
                'nombre' => $item->nombre,
                'apellidos' => $item->apellidos,
                'celular' => $item->celular,
                'tipo' => $item->tipo,
                'empresa' => $empresa,
                'ultimo_pedido' => $item->ultimo_pedido ? Carbon::parse($item->ultimo_pedido) : null
            ];
        })->sortBy('nombre');

        return view('telemarketing.index', compact('clientes', 'empresas', 'isUserAdmin', 'userEmpresaId'));
    }

    public function enviarMensaje(Request $request, $clienteId)
    {
        try {
            $request->validate([
                'mensaje' => 'required|string|max:1000',
                'tipo' => 'required|string'
            ]);

            // Extraer tipo e ID real del clienteId compuesto
            $parts = explode('_', $clienteId);
            $tipo = $parts[0];
            $realId = $parts[1];

            // Obtener información del cliente/paciente
            if ($tipo === 'cliente') {
                $cliente = DB::table('pedidos')
                    ->where('id', $realId)
                    ->select('cliente as nombre', DB::raw('NULL as apellidos'), 'celular', 'empresa_id')
                    ->first();
            } else {
                $cliente = DB::table('historiales_clinicos')
                    ->where('id', $realId)
                    ->select('nombres as nombre', 'apellidos', 'celular', 'empresa_id')
                    ->first();
            }

            if (!$cliente) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            // Registrar el mensaje enviado
            MensajesEnviados::create([
                'cliente_id' => $realId,
                'tipo_cliente' => $tipo,
                'nombres' => $cliente->nombre,
                'apellidos' => $cliente->apellidos ?? '',
                'celular' => $cliente->celular,
                'mensaje' => $request->mensaje,
                'tipo_mensaje' => $request->tipo,
                'fecha_envio' => now(),
                'usuario_id' => auth()->id(),
                'empresa_id' => $cliente->empresa_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje registrado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje de telemarketing: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function obtenerHistorial(Request $request, $clienteId)
    {
        try {
            // Extraer tipo e ID real del clienteId compuesto
            $parts = explode('_', $clienteId);
            $tipo = $parts[0];
            $realId = $parts[1];

            $pedidos = [];
            $historiales = [];

            if ($tipo === 'cliente') {
                // Obtener información del cliente desde pedidos
                $clienteInfo = DB::table('pedidos')
                    ->where('id', $realId)
                    ->select('cliente as nombre', DB::raw('NULL as apellidos'))
                    ->first();

                if ($clienteInfo) {
                    // Buscar todos los pedidos de este cliente
                    $pedidos = DB::table('pedidos')
                        ->where('cliente', $clienteInfo->nombre)
                        ->select(
                            'fecha',
                            'numero_orden',
                            'fact',
                            'total',
                            'saldo'
                        )
                        ->orderBy('fecha', 'desc')
                        ->get()
                        ->map(function ($pedido) {
                            return [
                                'fecha' => Carbon::parse($pedido->fecha)->format('d/m/Y'),
                                'numero_orden' => $pedido->numero_orden,
                                'fact' => $pedido->fact,
                                'total_formatted' => number_format($pedido->total, 0, ',', '.'),
                                'saldo' => $pedido->saldo,
                                'saldo_formatted' => number_format($pedido->saldo, 0, ',', '.')
                            ];
                        });

                    // Buscar historiales clínicos por nombre (si existen)
                    $historiales = DB::table('historiales_clinicos')
                        ->leftJoin('users', 'historiales_clinicos.usuario_id', '=', 'users.id')
                        ->where('nombres', $clienteInfo->nombre)
                        ->select(
                            'historiales_clinicos.fecha',
                            'historiales_clinicos.motivo_consulta',
                            'historiales_clinicos.proxima_consulta',
                            'users.name as usuario'
                        )
                        ->orderBy('fecha', 'desc')
                        ->get()
                        ->map(function ($historial) {
                            return [
                                'fecha' => Carbon::parse($historial->fecha)->format('d/m/Y'),
                                'motivo_consulta' => $historial->motivo_consulta,
                                'proxima_consulta' => $historial->proxima_consulta,
                                'usuario' => $historial->usuario ?? 'N/A'
                            ];
                        });
                }
            } else {
                // Obtener información del paciente desde historiales clínicos
                $pacienteInfo = DB::table('historiales_clinicos')
                    ->where('id', $realId)
                    ->select('nombres', 'apellidos')
                    ->first();

                if ($pacienteInfo) {
                    // Buscar todos los historiales de este paciente
                    $historiales = DB::table('historiales_clinicos')
                        ->leftJoin('users', 'historiales_clinicos.usuario_id', '=', 'users.id')
                        ->where('nombres', $pacienteInfo->nombres)
                        ->where('apellidos', $pacienteInfo->apellidos)
                        ->select(
                            'historiales_clinicos.fecha',
                            'historiales_clinicos.motivo_consulta',
                            'historiales_clinicos.proxima_consulta',
                            'users.name as usuario'
                        )
                        ->orderBy('fecha', 'desc')
                        ->get()
                        ->map(function ($historial) {
                            return [
                                'fecha' => Carbon::parse($historial->fecha)->format('d/m/Y'),
                                'motivo_consulta' => $historial->motivo_consulta,
                                'proxima_consulta' => $historial->proxima_consulta,
                                'usuario' => $historial->usuario ?? 'N/A'
                            ];
                        });

                    // Buscar pedidos por nombre (si existen)
                    $nombreCompleto = trim($pacienteInfo->nombres . ' ' . $pacienteInfo->apellidos);
                    $pedidos = DB::table('pedidos')
                        ->where(function($query) use ($pacienteInfo, $nombreCompleto) {
                            $query->where('cliente', $pacienteInfo->nombres)
                                  ->orWhere('cliente', $nombreCompleto)
                                  ->orWhere('paciente', $pacienteInfo->nombres)
                                  ->orWhere('paciente', $nombreCompleto);
                        })
                        ->select(
                            'fecha',
                            'numero_orden',
                            'fact',
                            'total',
                            'saldo'
                        )
                        ->orderBy('fecha', 'desc')
                        ->get()
                        ->map(function ($pedido) {
                            return [
                                'fecha' => Carbon::parse($pedido->fecha)->format('d/m/Y'),
                                'numero_orden' => $pedido->numero_orden,
                                'fact' => $pedido->fact,
                                'total_formatted' => number_format($pedido->total, 0, ',', '.'),
                                'saldo' => $pedido->saldo,
                                'saldo_formatted' => number_format($pedido->saldo, 0, ',', '.')
                            ];
                        });
                }
            }

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos,
                'historiales' => $historiales
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener historial de telemarketing: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
