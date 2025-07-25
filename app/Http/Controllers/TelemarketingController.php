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

        // Unión de clientes y pacientes
        $clientesQuery = DB::table('pedidos')
            ->select(
                'cliente as nombre',
                DB::raw('NULL as apellidos'),
                'celular',
                DB::raw("'cliente' as tipo"),
                'empresa_id',s
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

            // Buscar el último mensaje enviado para este cliente/paciente
            $ultimoMensaje = null;
            if ($item->tipo === 'cliente') {
                // Para clientes, buscar por nombre del cliente en la tabla pedidos
                $ultimoMensaje = DB::table('mensajes_enviados')
                    ->join('pedidos', 'mensajes_enviados.pedido_id', '=', 'pedidos.id')
                    ->where('pedidos.cliente', $item->nombre)
                    ->where('pedidos.celular', $item->celular)
                    ->where('mensajes_enviados.tipo', 'cliente')
                    ->select('mensajes_enviados.*')
                    ->orderBy('mensajes_enviados.fecha_envio', 'desc')
                    ->first();
            } else {
                // Para pacientes, buscar por nombre del paciente en la tabla historiales_clinicos
                $ultimoMensaje = DB::table('mensajes_enviados')
                    ->join('historiales_clinicos', 'mensajes_enviados.historial_id', '=', 'historiales_clinicos.id')
                    ->where('historiales_clinicos.nombres', $item->nombre)
                    ->where('historiales_clinicos.apellidos', $item->apellidos)
                    ->where('historiales_clinicos.celular', $item->celular)
                    ->where('mensajes_enviados.tipo', 'paciente')
                    ->select('mensajes_enviados.*')
                    ->orderBy('mensajes_enviados.fecha_envio', 'desc')
                    ->first();
            }

            return (object) [
                'id' => $item->tipo . '_' . $item->id, // Crear ID único combinando tipo e id
                'nombre' => $item->nombre,
                'apellidos' => $item->apellidos,
                'celular' => $item->celular,
                'tipo' => $item->tipo,
                'empresa' => $empresa,
                'ultimo_pedido' => $item->ultimo_pedido ? Carbon::parse($item->ultimo_pedido) : null,
                'ultimo_mensaje' => $ultimoMensaje ? (object) [
                    'fecha_envio' => Carbon::parse($ultimoMensaje->fecha_envio),
                    'mensaje' => $ultimoMensaje->mensaje
                ] : null
            ];
        })->sortBy('nombre');

        return view('telemarketing.index', compact('clientes'));
    }

    public function enviarMensaje(Request $request, $clienteId)
    {
        try {
            $request->validate([
                'mensaje' => 'required|string|max:1000',
                'tipo' => 'nullable|string'
            ]);

            // Verificar si tenemos un tipo o asignar uno predeterminado
            $tipo = $request->tipo ?? 'telemarketing';
            
            // Log detallado para depuración
            Log::info('Datos recibidos para enviar mensaje:', [
                'request_all' => $request->all(),
                'tipo_from_request' => $request->tipo,
                'tipo_assigned' => $tipo,
                'cliente_id' => $clienteId
            ]);

            // Log para depuración
            Log::info('Enviando mensaje a clienteId: ' . $clienteId);
            Log::info('Datos recibidos: ', $request->all());

            // Extraer tipo e ID real del clienteId compuesto
            if (strpos($clienteId, '_') !== false) {
                $parts = explode('_', $clienteId);
                $tipo = $parts[0];
                $realId = $parts[1];
                Log::info("ID separado: tipo=$tipo, realId=$realId");
            } else {
                // Si no tiene formato tipo_id, asumimos que es un cliente por defecto
                $tipo = 'cliente';
                $realId = $clienteId;
                Log::info("ID sin formato compuesto, usando cliente como tipo por defecto. realId=$realId");
            }

            // Obtener información del cliente/paciente
            $cliente = null;
            if ($tipo === 'cliente') {
                $cliente = DB::table('pedidos')
                    ->where('id', $realId)
                    ->select('cliente as nombre', DB::raw('NULL as apellidos'), 'celular', 'empresa_id')
                    ->first();
                
                Log::info("Buscando cliente en pedidos: ", ['id' => $realId, 'encontrado' => !is_null($cliente)]);
            } else {
                $cliente = DB::table('historiales_clinicos')
                    ->where('id', $realId)
                    ->select('nombres as nombre', 'apellidos', 'celular', 'empresa_id')
                    ->first();
                
                Log::info("Buscando paciente en historiales: ", ['id' => $realId, 'encontrado' => !is_null($cliente)]);
            }

            if (!$cliente) {
                Log::warning("Cliente/paciente no encontrado: tipo=$tipo, id=$realId");
                return response()->json(['error' => 'Cliente no encontrado. Por favor refresque la página e intente nuevamente.'], 404);
            }

            // Registrar el mensaje enviado
            $mensajeData = [
                'tipo' => $tipo, // cliente o paciente
                'mensaje' => $request->mensaje,
                'tipo_mensaje' => 'telemarketing', // Cambiamos esto a telemarketing para diferenciarlo
                'fecha_envio' => now(),
                'usuario_id' => auth()->id(),
                'empresa_id' => $cliente->empresa_id ?? null
            ];

            // Asignar la relación correspondiente según el tipo
            if ($tipo === 'cliente') {
                $mensajeData['pedido_id'] = $realId;
                $mensajeData['historial_id'] = null;
            } else {
                $mensajeData['historial_id'] = $realId;
                $mensajeData['pedido_id'] = null;
            }

            // Comprobar que la tabla y modelo existen
            if (!class_exists(MensajesEnviados::class)) {
                Log::error("La clase MensajesEnviados no existe");
                return response()->json(['error' => 'Error de configuración del sistema.'], 500);
            }

            $mensaje = MensajesEnviados::create($mensajeData);
            Log::info("Mensaje creado: ", ['id' => $mensaje->id]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje registrado correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Error de validación al enviar mensaje: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Datos inválidos: ' . implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje de telemarketing: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
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
                            'id',
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
                                'id' => $pedido->id,
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
                            'historiales_clinicos.id',
                            'historiales_clinicos.fecha',
                            'historiales_clinicos.motivo_consulta',
                            'historiales_clinicos.proxima_consulta',
                            'users.name as usuario'
                        )
                        ->orderBy('fecha', 'desc')
                        ->get()
                        ->map(function ($historial) {
                            return [
                                'id' => $historial->id,
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
                            'historiales_clinicos.id',
                            'historiales_clinicos.fecha',
                            'historiales_clinicos.motivo_consulta',
                            'historiales_clinicos.proxima_consulta',
                            'users.name as usuario'
                        )
                        ->orderBy('fecha', 'desc')
                        ->get()
                        ->map(function ($historial) {
                            return [
                                'id' => $historial->id,
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
                            'id',
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
                                'id' => $pedido->id,
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

    public function obtenerMensajePredeterminado()
    {
        try {
            // Buscar mensaje predeterminado para telemarketing
            $mensajePredeterminado = MensajePredeterminado::where('tipo', 'telemarketing')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($mensajePredeterminado) {
                return response()->json([
                    'success' => true,
                    'mensaje' => $mensajePredeterminado->mensaje
                ]);
            }

            // Si no hay mensaje predeterminado, devolver respuesta vacía
            return response()->json([
                'success' => true,
                'mensaje' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener mensaje predeterminado: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
