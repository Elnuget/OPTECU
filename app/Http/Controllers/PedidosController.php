<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Inventario;
use App\Models\PedidoLuna; // Add this line
use App\Models\Declarante;
use App\Models\Factura;
use App\Models\Empresa;
use App\Models\HistorialClinico;

class PedidosController extends Controller
{    public function __construct()
    {
        // Aplicar middleware de autenticación a todas las rutas excepto las públicas
        $this->middleware('auth')->except(['calificarPublico', 'guardarCalificacionPublica']);
        
        // Aplicar middleware de administrador solo a estas rutas
        $this->middleware('can:admin')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Si se solicita ver todos los registros, no aplicar filtros de fecha
            $mostrarTodos = $request->has('todos') && $request->todos == '1';
            
            // Si no hay fecha seleccionada y no se solicita ver todos, redirigir al mes actual
            if (!$mostrarTodos && (!$request->filled('ano') || !$request->filled('mes'))) {
                return redirect()->route('pedidos.index', [
                    'ano' => now()->setTimezone('America/Guayaquil')->format('Y'),
                    'mes' => now()->setTimezone('America/Guayaquil')->format('m')
                ]);
            }

            $query = Pedido::query()
                ->with([
                    'aInventario:id,codigo,cantidad',
                    'dInventario:id,codigo,cantidad',
                    'pagos:id,pedido_id,pago',
                    'empresa:id,nombre'
                ]);

            // Aplicar filtros de año y mes solo si no se solicita ver todos
            if (!$mostrarTodos) {
                $query->whereYear('fecha', $request->ano)
                      ->whereMonth('fecha', $request->mes);
            }

            // Aplicar filtro por empresa si se selecciona
            if ($request->filled('empresa_id') && $request->empresa_id != '') {
                $query->where('empresa_id', $request->empresa_id);
            }

            $pedidos = $query->select([
                'id',
                'numero_orden',
                'fecha',
                'cliente',
                'celular',
                'paciente',
                'total',
                'saldo',
                'fact',
                'usuario',
                'empresa_id',
                'encuesta' // Asegurarnos de que la columna encuesta se cargue explícitamente
            ])
            ->orderBy('numero_orden', 'desc')
            ->get();

            // Log para debugging
            \Log::info('Pedidos cargados:', [
                'count' => $pedidos->count(),
                'muestra' => $pedidos->take(3)->map(function($p) {
                    return [
                        'id' => $p->id,
                        'cliente' => $p->cliente,
                        'encuesta' => $p->encuesta
                    ];
                })
            ]);

            // Calcular totales de los pedidos filtrados
            $totales = [
                'ventas' => $pedidos->sum('total'),
                'saldos' => $pedidos->sum('saldo'),
                'cobrado' => $pedidos->sum(function($pedido) {
                    return $pedido->pagos->sum('pago');
                })
            ];

            // Obtener lista de empresas para el filtro
            $empresas = Empresa::orderBy('nombre')->get();

            return view('pedidos.index', compact('pedidos', 'totales', 'empresas'));
        } catch (\Exception $e) {
            \Log::error('Error en PedidosController@index: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los pedidos: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener el año y mes actual
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Obtener armazones y accesorios del mes actual (solo con cantidad > 0) con información de empresa
        $inventario = Inventario::with('empresa:id,nombre')
            ->where('cantidad', '>', 0)
            ->whereYear('fecha', $currentYear)
            ->whereMonth('fecha', $currentMonth)
            ->get();

        // Separar el inventario en armazones y accesorios
        $armazones = $inventario;
        $accesorios = $inventario;

        // Obtener lista de clientes únicos existentes
        $clientes = Pedido::select('cliente')
            ->whereNotNull('cliente')
            ->distinct()
            ->pluck('cliente')
            ->toArray();
            
        // Obtener lista de cédulas únicas existentes
        $cedulas = Pedido::select('cedula')
            ->whereNotNull('cedula')
            ->distinct()
            ->pluck('cedula')
            ->toArray();
            
        // Obtener lista de pacientes únicos existentes de pedidos
        $pacientesPedidos = Pedido::select('paciente')
            ->whereNotNull('paciente')
            ->distinct()
            ->pluck('paciente')
            ->filter()
            ->map(function($nombre) {
                return [
                    'nombre' => $nombre,
                    'tipo' => 'pedido'
                ];
            })->values()->toArray();
            
        // Obtener lista de pacientes únicos de historiales clínicos
        $pacientesHistoriales = HistorialClinico::select('nombres', 'apellidos')
            ->whereNotNull('nombres')
            ->whereNotNull('apellidos')
            ->distinct()
            ->get()
            ->map(function($historial) {
                $nombreCompleto = trim($historial->nombres . ' ' . $historial->apellidos);
                return [
                    'nombre' => $nombreCompleto,
                    'tipo' => 'historial_clinico'
                ];
            })
            ->unique('nombre')
            ->values()
            ->toArray();
            
        // Combinar pacientes de pedidos e historiales
        $pacientes = array_merge($pacientesPedidos, $pacientesHistoriales);
            
        // Obtener lista de celulares únicos existentes
        $celulares = Pedido::select('celular')
            ->whereNotNull('celular')
            ->distinct()
            ->pluck('celular')
            ->toArray();
            
        // Obtener lista de correos electrónicos únicos existentes
        $correos = Pedido::select('correo_electronico')
            ->whereNotNull('correo_electronico')
            ->distinct()
            ->pluck('correo_electronico')
            ->toArray();

        $currentDate = date('Y-m-d');
        $lastOrder = Pedido::orderBy('numero_orden', 'desc')->first();
        $nextOrderNumber = $lastOrder ? $lastOrder->numero_orden + 1 : 1;
        $nextInvoiceNumber = 'Pendiente';

        // Obtener lista de empresas
        $empresas = Empresa::orderBy('nombre')->get();

        return view('pedidos.create', compact(
            'armazones', 
            'accesorios', 
            'currentDate', 
            'nextOrderNumber', 
            'nextInvoiceNumber',
            'clientes',
            'cedulas',
            'pacientes',
            'celulares',
            'correos',
            'empresas'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar que la empresa sea obligatoria
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'cliente' => 'required|string|max:255',
        ]);

        try {
            \DB::beginTransaction();

            // Filtrar los arrays vacíos antes de crear el pedido
            $pedidoData = collect($request->all())
                ->reject(function ($value, $key) {
                    // Quitar campos que son arreglos, por ejemplo a_inventario_id, l_medida, etc.
                    return is_array($value);
                })
                ->toArray();

            // Create basic pedido
            $pedido = new Pedido();
            $pedido->fill($pedidoData);
            $pedido->usuario = auth()->user()->name;

            // Asegurar que los campos tengan valores por defecto si están vacíos
            $pedido->total = $pedidoData['total'] ?? 0;
            $pedido->saldo = $pedidoData['saldo'] ?? 0;
            $pedido->examen_visual = $pedidoData['examen_visual'] ?? 0;
            $pedido->valor_compra = $pedidoData['valor_compra'] ?? 0;
            $pedido->cedula = $pedidoData['cedula'] ?? null;
            
            $pedido->save();

            // Handle armazones solo si hay datos válidos
            if ($request->has('a_inventario_id') && is_array($request->a_inventario_id)) {
                foreach ($request->a_inventario_id as $index => $inventarioId) {
                    if (!empty($inventarioId)) {
                        $precio = $request->a_precio[$index] ?? 0;
                        $descuento = $request->a_precio_descuento[$index] ?? 0;

                        $pedido->inventarios()->attach($inventarioId, [
                            'precio' => (float) $precio,
                            'descuento' => (float) $descuento,
                        ]);

                        $inventarioItem = Inventario::find($inventarioId);
                        if ($inventarioItem) {
                            $inventarioItem->orden = $pedido->numero_orden;
                            $inventarioItem->valor = (float) $precio;
                            $inventarioItem->cantidad -= 1;
                            $inventarioItem->save();
                        }
                    }
                }
            }

            // Handle lunas solo si hay datos válidos
            if ($request->has('l_medida') && is_array($request->l_medida)) {
                foreach ($request->l_medida as $key => $medida) {
                    if (!empty($medida)) {
                        $luna = new PedidoLuna([
                            'l_medida' => $medida,
                            'l_detalle' => $request->l_detalle[$key] ?? null,
                            'l_precio' => (float)($request->l_precio[$key] ?? 0),
                            'tipo_lente' => $request->tipo_lente[$key] ?? null,
                            'material' => $request->material[$key] ?? null,
                            'filtro' => $request->filtro[$key] ?? null,
                            'l_precio_descuento' => (float)($request->l_precio_descuento[$key] ?? 0)
                        ]);
                        $pedido->lunas()->save($luna);
                    }
                }
            }

            // Handle accesorios
            if ($request->has('d_inventario_id') && is_array($request->d_inventario_id)) {
                foreach ($request->d_inventario_id as $index => $inventarioId) {
                    $precio = $request->d_precio[$index] ?? 0;
                    $descuento = $request->d_precio_descuento[$index] ?? 0;

                    if (!empty($inventarioId)) {
                        if (!is_numeric($inventarioId)) {
                            // Crear nuevo registro en inventario
                            $inventarioItem = new Inventario();
                            $inventarioItem->codigo = $inventarioId;
                            $inventarioItem->cantidad = 1;
                            // ...asignar otras propiedades si es necesario...
                            $inventarioItem->save();
                            $inventarioId = $inventarioItem->id;
                        }

                        $pedido->inventarios()->attach($inventarioId, [
                            'precio' => (float) $precio,
                            'descuento' => (float) $descuento,
                        ]);

                        $inventarioItem = Inventario::find($inventarioId);
                        if ($inventarioItem) {
                            $inventarioItem->orden = $pedido->numero_orden;
                            $inventarioItem->valor = (float) $precio;
                            $inventarioItem->cantidad -= 1;
                            $inventarioItem->save();
                        }
                    }
                }
            }

            \DB::commit();

            // Eliminar el envío de correo electrónico
            // if ($pedido->correo_electronico) {
            //     \Mail::to($pedido->correo_electronico)->send(new \App\Mail\CalificacionPedido($pedido));
            // }

            // Redirigir a la creación de pago con el pedido recién creado preseleccionado
            return redirect()->route('pagos.create', ['pedido_id' => $pedido->id])->with([
                'error' => 'Exito',
                'mensaje' => 'Pedido creado exitosamente. Ahora puede añadir un pago.',
                'tipo' => 'alert-success'
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error en PedidosController@store: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
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
        $pedido = Pedido::with([
            'aInventario',
            'dInventario',
            'inventarios',
            'lunas',  // Add this line to eager load lunas
            'empresa'
        ])->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $pedido = Pedido::with(['inventarios.empresa', 'lunas', 'pagos'])->findOrFail($id);
            
            // Obtener el año y mes actual
            $currentYear = date('Y');
            $currentMonth = date('m');
            
            // Primer intento: Filtrar inventario por mes y año actual con cantidad > 0
            $inventarioItems = Inventario::with('empresa:id,nombre')
                ->where('cantidad', '>', 0)
                ->whereYear('fecha', $currentYear)
                ->whereMonth('fecha', $currentMonth)
                ->get();
                
            // Verificar si hay resultados
            if ($inventarioItems->isEmpty()) {
                // Segundo intento: Si no hay del mes actual, buscar el último mes con artículos
                $ultimoArticulo = Inventario::where('cantidad', '>', 0)
                    ->orderBy('fecha', 'desc')
                    ->first();
                    
                if ($ultimoArticulo) {
                    $lastItemDate = \Carbon\Carbon::parse($ultimoArticulo->fecha);
                    $inventarioItems = Inventario::with('empresa:id,nombre')
                        ->where('cantidad', '>', 0)
                        ->whereYear('fecha', $lastItemDate->year)
                        ->whereMonth('fecha', $lastItemDate->month)
                        ->get();
                        
                    // Actualizar las variables de año y mes para mostrar en la vista
                    $currentYear = $lastItemDate->year;
                    $currentMonth = $lastItemDate->month;
                } else {
                    // Tercer intento: Si no hay ningún artículo con cantidad > 0, mostrar todos los artículos
                    $inventarioItems = Inventario::with('empresa:id,nombre')->get();
                }
            }
                
            // Agregar también los items que ya están en este pedido (para que no desaparezcan al editar)
            $pedidoInventarioIds = $pedido->inventarios->pluck('id')->toArray();
            if (!empty($pedidoInventarioIds)) {
                $inventarioItemsPedido = Inventario::with('empresa:id,nombre')
                    ->whereIn('id', $pedidoInventarioIds)->get();
                // Combinar las colecciones y eliminar duplicados
                $inventarioItems = $inventarioItems->concat($inventarioItemsPedido)->unique('id');
            }
            
            // Log para debugging
            \Log::info('Inventario filtrado:', [
                'total_items' => $inventarioItems->count(),
                'mes_filtro' => $currentMonth,
                'año_filtro' => $currentYear,
                'muestra' => $inventarioItems->take(5)->map(function($item) {
                    return [
                        'id' => $item->id,
                        'codigo' => $item->codigo,
                        'fecha' => $item->fecha,
                        'cantidad' => $item->cantidad
                    ];
                })
            ]);
            
            $totalPagado = $pedido->pagos->sum('pago'); // Suma todos los pagos realizados
            $usuarios = \App\Models\User::all(); // Obtener todos los usuarios
            
            // Pasar el mes y año de filtro a la vista
            $filtroMes = $currentMonth;
            $filtroAno = $currentYear;

            // Obtener lista de empresas
            $empresas = Empresa::orderBy('nombre')->get();

            return view('pedidos.edit', compact('pedido', 'inventarioItems', 'totalPagado', 'usuarios', 'filtroMes', 'filtroAno', 'empresas'));
            
        } catch (\Exception $e) {
            \Log::error('Error en PedidosController@edit: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el pedido: ' . $e->getMessage());
        }
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
        // Validar que la empresa sea obligatoria
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        try {
            \DB::beginTransaction();
            
            $pedido = Pedido::findOrFail($id);
            
            // Guardar los IDs de inventario actuales antes de actualizarlos
            $inventariosAnteriores = $pedido->inventarios->pluck('id')->toArray();
            
            // Update basic pedido information including cedula
            $pedido->fill($request->except(['a_inventario_id', 'a_precio', 'a_precio_descuento', 'd_inventario_id', 'd_precio', 'd_precio_descuento']));
            $pedido->save();

            // Update pedido_inventario relationships
            $pedido->inventarios()->detach(); // Remove existing relationships

            // Array para almacenar los nuevos IDs de inventario
            $nuevosInventarioIds = [];

            if ($request->has('a_inventario_id')) {
                foreach ($request->a_inventario_id as $index => $inventarioId) {
                    if (!empty($inventarioId)) {
                        $pedido->inventarios()->attach($inventarioId, [
                            'precio' => $request->a_precio[$index] ?? 0,
                            'descuento' => $request->a_precio_descuento[$index] ?? 0,
                        ]);
                        
                        $nuevosInventarioIds[] = $inventarioId;
                    }
                }
            }

            // Update accesorios relationships
            if ($request->has('d_inventario_id')) {
                foreach ($request->d_inventario_id as $index => $accesorioId) {
                    if (!empty($accesorioId)) {
                        $pedido->inventarios()->attach($accesorioId, [
                            'precio' => $request->d_precio[$index] ?? 0,
                            'descuento' => $request->d_precio_descuento[$index] ?? 0,
                        ]);
                        
                        $nuevosInventarioIds[] = $accesorioId;
                    }
                }
            }

            // Verificar si se debe actualizar el inventario o ya se hizo en el frontend
            $actualizarInventario = $request->input('actualizar_inventario', 'true') === 'true';
            
            if ($actualizarInventario) {
                \Log::info('Actualizando inventario desde el backend para pedido #' . $pedido->id);
                
                // Actualizar el estado del inventario
                // 1. Restaurar la cantidad para artículos eliminados del pedido
                $inventariosEliminados = array_diff($inventariosAnteriores, $nuevosInventarioIds);
                foreach ($inventariosEliminados as $inventarioId) {
                    $inventario = Inventario::find($inventarioId);
                    if ($inventario) {
                        $inventario->cantidad += 1; // Aumentar la cantidad al quitar del pedido
                        $inventario->orden = null; // Quitar la referencia al pedido
                        $inventario->save();
                    }
                }
                
                // 2. Actualizar o reducir cantidad para nuevos artículos añadidos
                $inventariosNuevos = array_diff($nuevosInventarioIds, $inventariosAnteriores);
                foreach ($inventariosNuevos as $inventarioId) {
                    $inventario = Inventario::find($inventarioId);
                    if ($inventario) {
                        $inventario->cantidad -= 1; // Disminuir la cantidad al añadir al pedido
                        $inventario->orden = $pedido->numero_orden; // Asignar referencia al pedido
                        $inventario->save();
                    }
                }
            } else {
                \Log::info('Omitiendo actualización de inventario en backend para pedido #' . $pedido->id . ' (ya manejado en frontend)');
            }

            // Update lunas
            $pedido->lunas()->delete(); // Remove existing lunas
            if ($request->has('l_medida')) {
                foreach ($request->l_medida as $key => $medida) {
                    if (!empty($medida)) {
                        $pedido->lunas()->create([
                            'l_medida' => $medida,
                            'l_detalle' => $request->l_detalle[$key] ?? null,
                            'l_precio' => $request->l_precio[$key] ?? 0,
                            'tipo_lente' => $request->tipo_lente[$key] ?? null,
                            'material' => $request->material[$key] ?? null,
                            'filtro' => $request->filtro[$key] ?? null,
                            'l_precio_descuento' => $request->l_precio_descuento[$key] ?? 0
                        ]);
                    }
                }
            }

            \DB::commit();

            return redirect('/Pedidos')->with([
                'error' => 'Exito',
                'mensaje' => 'Pedido actualizado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect('/Pedidos')->with([
                'error' => 'Error',
                'mensaje' => 'Pedido no se ha actualizado: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
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
            \DB::beginTransaction();
            
            $pedido = Pedido::findOrFail($id);
            
            // Eliminar registros de caja relacionados con los pagos
            foreach ($pedido->pagos as $pago) {
                if ($pago->mediodepago_id == 1) { // Si es pago en efectivo
                    \App\Models\Caja::where([
                        ['valor', '=', $pago->pago],
                        ['motivo', 'like', 'Abono ' . $pedido->cliente . '%']
                    ])->delete();
                }
            }

            // La eliminación de pagos se maneja automáticamente por el modelo
            $pedido->delete();

            \DB::commit();

            return redirect('/Pedidos')->with([
                'error' => 'Exito',
                'mensaje' => 'Pedido y sus pagos asociados eliminados exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error eliminando pedido: ' . $e->getMessage());
            
            return redirect('/Pedidos')->with([
                'error' => 'Error',
                'mensaje' => 'Error al eliminar el pedido: ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function approve($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->fact = 'APROBADO';
        $pedido->save();

        return redirect()->route('pedidos.index')->with([
            'error' => 'Exito',
            'mensaje' => 'Factura aprobada exitosamente',
            'tipo' => 'alert-success'
        ]);
    }

    public function inventarioHistorial()
    {
        $inventario = Inventario::with('pedidos')->get();
        return view('pedidos.inventario-historial', compact('inventario'));
    }

    public function enviarEncuesta($id)
    {
        try {
            // Log del inicio del proceso
            \Log::info('Iniciando generación de encuesta para pedido ID: ' . $id);

            $pedido = Pedido::findOrFail($id);
            \Log::info('Pedido encontrado:', [
                'id' => $pedido->id,
                'created_at' => $pedido->created_at,
                'cliente' => $pedido->cliente,
                'estado_encuesta' => $pedido->encuesta
            ]);
            
            // Verificar si el pedido tiene los datos necesarios
            if (!$pedido->created_at) {
                \Log::error('El pedido no tiene fecha de creación');
                throw new \Exception('El pedido no tiene fecha de creación');
            }

            if (!$pedido->id) {
                \Log::error('El pedido no tiene ID válido');
                throw new \Exception('El pedido no tiene ID válido');
            }
            
            // Generar el token usando una cadena más simple
            try {
                $token = hash('sha256', $pedido->id . $pedido->created_at->timestamp);
                \Log::info('Token generado exitosamente');
            } catch (\Exception $e) {
                \Log::error('Error al generar token: ' . $e->getMessage());
                throw new \Exception('Error al generar el token de la encuesta: ' . $e->getMessage());
            }
            
            // Verificar la configuración de la URL
            $baseUrl = config('app.url');
            \Log::info('URL base de la aplicación: ' . $baseUrl);
            
            if (!$baseUrl) {
                $baseUrl = request()->getSchemeAndHttpHost();
                \Log::info('Usando URL alternativa: ' . $baseUrl);
            }
            
            try {
                // Construir la URL de la encuesta
                $urlEncuesta = route('pedidos.calificar-publico', [
                    'id' => $pedido->id, 
                    'token' => $token
                ], true);
                
                // Crear un texto amigable para el enlace
                $textoAmigable = "➡️ *CLICK AQUÍ PARA COMPLETAR LA ENCUESTA* ⬅️";
                
                \Log::info('URL de encuesta generada:', ['url' => $urlEncuesta]);
            } catch (\Exception $e) {
                \Log::error('Error al generar URL de encuesta: ' . $e->getMessage());
                throw new \Exception('Error al generar la URL de la encuesta: ' . $e->getMessage());
            }
            
            try {
                // Actualizar el estado de la encuesta usando el valor correcto del enum
                $pedido->encuesta = 'enviado';
                $pedido->save();
                \Log::info('Estado de encuesta actualizado a enviado');
            } catch (\Exception $e) {
                \Log::error('Error al actualizar estado de encuesta: ' . $e->getMessage());
                throw new \Exception('Error al actualizar el estado de la encuesta: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'url' => $urlEncuesta,
                'texto_amigable' => $textoAmigable,
                'estado' => 'enviado',
                'mensaje' => 'Encuesta enviada exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error completo al generar enlace de encuesta: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el enlace de la encuesta: ' . $e->getMessage(),
                'details' => [
                    'pedido_id' => $id,
                    'error_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    public function calificarPublico($id, $token)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            
            // Generar el token de la misma manera que en enviarEncuesta
            $tokenEsperado = hash('sha256', $pedido->id . $pedido->created_at->timestamp);
            
            // Verificar que el token sea válido
            if ($token !== $tokenEsperado) {
                abort(403, 'Token inválido');
            }

            // Si ya está calificado, mostrar mensaje
            if ($pedido->calificacion) {
                return view('pedidos.calificacion-completa');
            }

            return view('pedidos.calificar-publico', compact('pedido', 'token'));
        } catch (\Exception $e) {
            \Log::error('Error en calificarPublico: ' . $e->getMessage());
            abort(500, 'Error al procesar la encuesta');
        }
    }

    public function guardarCalificacionPublica(Request $request, $id, $token)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            
            // Generar el token de la misma manera que en enviarEncuesta
            $tokenEsperado = hash('sha256', $pedido->id . $pedido->created_at->timestamp);
            
            // Verificar que el token sea válido
            if ($token !== $tokenEsperado) {
                abort(403, 'Token inválido');
            }

            // Si ya está calificado, mostrar error
            if ($pedido->calificacion) {
                return redirect()->back()->with('error', 'Este pedido ya ha sido calificado');
            }

            $request->validate([
                'calificacion' => 'required|integer|min:1|max:5',
                'comentario' => 'nullable|string|max:1000'
            ]);

            $comentarioFinal = $request->comentario 
                ? $pedido->cliente . ': ' . $request->comentario
                : $pedido->cliente;

            $pedido->update([
                'calificacion' => $request->calificacion,
                'comentario_calificacion' => $comentarioFinal,
                'fecha_calificacion' => now()
            ]);

            return view('pedidos.gracias-calificacion');
        } catch (\Exception $e) {
            \Log::error('Error en guardarCalificacionPublica: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar la calificación');
        }
    }

    public function actualizarEstadoEncuesta($id, $estado = 'enviado')
    {
        try {
            $pedido = Pedido::findOrFail($id);
            
            // Actualizar estado
            $pedido->encuesta = $estado;
            $pedido->save();
            
            // Verificar que se haya guardado correctamente
            $pedidoActualizado = Pedido::find($id);
            
            return response()->json([
                'success' => true,
                'estadoOriginal' => $estado,
                'estadoActual' => $pedidoActualizado->encuesta,
                'mensaje' => 'Estado actualizado a ' . $estado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los declarantes para mostrar en modal
     */
    public function getDeclarantes()
    {
        try {
            $declarantes = Declarante::with('facturas')->orderBy('nombre', 'asc')->get();
            
            // Calcular los totales fiscales para cada declarante
            $declarantes->each(function ($declarante) {
                $totalMonto = $declarante->facturas->sum('monto'); // Base gravable
                $totalIva = $declarante->facturas->sum('iva'); // IVA Débito Fiscal
                $totalFacturado = $totalMonto + $totalIva; // Total facturado (Base + IVA)
                
                $declarante->total_base = $totalMonto; // Base gravable
                $declarante->total_iva = $totalIva; // IVA Débito Fiscal (solo el IVA)
                $declarante->total_facturado = $totalFacturado; // Total facturado completo
                $declarante->cantidad_facturas = $declarante->facturas->count();
            });
            
            return response()->json([
                'success' => true,
                'data' => $declarantes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los declarantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo declarante
     */
    public function storeDeclarante(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'ruc' => 'required|string|max:20',
                'firma' => 'nullable|file|mimes:p12,pem|max:5120' // 5MB máximo
            ]);

            // Manejar el archivo de firma si se envió
            $firmaPath = null;
            if ($request->hasFile('firma')) {
                $archivo = $request->file('firma');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $firmaPath = $archivo->storeAs('certificados', $nombreArchivo, 'public');
                $validatedData['firma'] = basename($firmaPath);
            }

            $declarante = Declarante::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Declarante creado exitosamente',
                'data' => $declarante
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear declarante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el declarante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un declarante existente
     */
    public function updateDeclarante(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'ruc' => 'required|string|max:20',
                'firma' => 'nullable|file|mimes:p12,pem|max:5120' // 5MB máximo
            ]);

            $declarante = Declarante::findOrFail($id);

            // Manejar el archivo de firma si se envió uno nuevo
            if ($request->hasFile('firma')) {
                // Eliminar el archivo anterior si existe
                if ($declarante->firma) {
                    $rutaAnterior = storage_path('app/public/certificados/' . $declarante->firma);
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }

                // Guardar el nuevo archivo
                $archivo = $request->file('firma');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $firmaPath = $archivo->storeAs('certificados', $nombreArchivo, 'public');
                $validatedData['firma'] = basename($firmaPath);
            } else {
                // Si no se envió archivo, mantener el actual
                unset($validatedData['firma']);
            }

            $declarante->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Declarante actualizado exitosamente',
                'data' => $declarante
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar declarante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el declarante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un declarante
     */
    public function destroyDeclarante($id)
    {
        try {
            $declarante = Declarante::findOrFail($id);
            $declarante->delete();

            return response()->json([
                'success' => true,
                'message' => 'Declarante eliminado exitosamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Declarante no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar declarante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el declarante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle de facturas de un declarante
     */
    public function getDeclaranteFacturas($id)
    {
        try {
            $declarante = Declarante::with(['facturas.pedido'])->findOrFail($id);
            
            $facturas = $declarante->facturas->map(function ($factura) {
                return [
                    'id' => $factura->id,
                    'pedido_id' => $factura->pedido_id,
                    'numero_orden' => $factura->pedido ? $factura->pedido->numero_orden : 'N/A',
                    'cliente' => $factura->pedido ? $factura->pedido->cliente : 'N/A',
                    'fecha' => $factura->created_at ? $factura->created_at->format('d/m/Y') : 'N/A',
                    'tipo' => ucfirst($factura->tipo),
                    'monto' => $factura->monto,
                    'iva' => $factura->iva,
                    'total' => $factura->monto + $factura->iva,
                    'xml' => $factura->xml
                ];
            });

            $totales = [
                'total_base' => $facturas->sum('monto'),
                'total_iva' => $facturas->sum('iva'),
                'total_facturado' => $facturas->sum('total'),
                'cantidad_facturas' => $facturas->count()
            ];

            return response()->json([
                'success' => true,
                'declarante' => [
                    'id' => $declarante->id,
                    'nombre' => $declarante->nombre,
                    'ruc' => $declarante->ruc
                ],
                'facturas' => $facturas,
                'totales' => $totales
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Declarante no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles del pedido para la factura
     */
    public function obtenerDetallesPedido($id)
    {
        try {
            $pedido = Pedido::with(['inventarios', 'lunas'])->findOrFail($id);
            
            $detalles = [
                'inventarios' => [],
                'lunas' => [],
                'totales' => [
                    'base_total' => 0,
                    'iva_total' => 0,
                    'monto_total' => 0
                ]
            ];

            $totalBase = 0;
            $totalIva = 0;
            $totalMonto = 0;

            // Procesar inventarios (accesorios/armazones)
            foreach ($pedido->inventarios as $inventario) {
                $precioConDescuento = $inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100));
                $base = round($precioConDescuento / 1.15, 2);
                $iva = round($precioConDescuento - $base, 2);
                
                $detalles['inventarios'][] = [
                    'codigo' => $inventario->codigo,
                    'precio_original' => $inventario->pivot->precio,
                    'descuento' => $inventario->pivot->descuento,
                    'precio_con_descuento' => $precioConDescuento,
                    'base' => $base,
                    'iva' => $iva
                ];
                
                $totalBase += $base;
                $totalIva += $iva;
                $totalMonto += $precioConDescuento;
            }

            // Procesar lunas
            foreach ($pedido->lunas as $luna) {
                $precioConDescuento = $luna->l_precio * (1 - ($luna->l_precio_descuento / 100));
                $base = round($precioConDescuento / 1.15, 2);
                $iva = round($precioConDescuento - $base, 2);
                
                $detalles['lunas'][] = [
                    'medida' => $luna->l_medida,
                    'detalle' => $luna->l_detalle,
                    'tipo_lente' => $luna->tipo_lente,
                    'material' => $luna->material,
                    'filtro' => $luna->filtro,
                    'precio_original' => $luna->l_precio,
                    'descuento' => $luna->l_precio_descuento,
                    'precio_con_descuento' => $precioConDescuento,
                    'base' => $base,
                    'iva' => $iva
                ];
                
                $totalBase += $base;
                $totalIva += $iva;
                $totalMonto += $precioConDescuento;
            }

            // Redondear totales
            $detalles['totales']['base_total'] = round($totalBase, 2);
            $detalles['totales']['iva_total'] = round($totalIva, 2);
            $detalles['totales']['monto_total'] = round($totalMonto, 2);

            return response()->json([
                'success' => true,
                'pedido' => [
                    'id' => $pedido->id,
                    'cliente' => $pedido->cliente,
                    'total_original' => $pedido->total
                ],
                'detalles' => $detalles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles del pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear factura para un pedido
     */
    public function crearFactura(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'pedido_id' => 'required|integer|exists:pedidos,id',
                'declarante_id' => 'required|integer|exists:declarante,id',
                'tipo' => 'required|string|in:factura,nota_venta',
                'monto' => 'required|numeric|min:0',
                'iva' => 'required|numeric|min:0',
                'xml' => 'nullable|string|max:255'
            ]);

            // Buscar el pedido con sus relaciones
            $pedido = Pedido::with(['inventarios', 'lunas'])->findOrFail($request->pedido_id);
            
            // Verificar que el pedido esté pendiente
            if (strtoupper($pedido->fact) !== 'PENDIENTE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pedido ya ha sido procesado'
                ], 400);
            }

            // Calcular monto total e IVA basado en inventarios y lunas
            $totalBase = 0;
            $totalIva = 0;
            $totalMonto = 0;

            // Calcular base e IVA de inventarios (accesorios)
            foreach ($pedido->inventarios as $inventario) {
                $precioConDescuento = $inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100));
                $base = round($precioConDescuento / 1.15, 2);
                $iva = round($precioConDescuento - $base, 2);
                
                $totalBase += $base;
                $totalIva += $iva;
                $totalMonto += $precioConDescuento;
            }

            // Calcular base e IVA de lunas
            foreach ($pedido->lunas as $luna) {
                $precioConDescuento = $luna->l_precio * (1 - ($luna->l_precio_descuento / 100));
                $base = round($precioConDescuento / 1.15, 2);
                $iva = round($precioConDescuento - $base, 2);
                
                $totalBase += $base;
                $totalIva += $iva;
                $totalMonto += $precioConDescuento;
            }

            // Redondear los totales finales
            $totalBase = round($totalBase, 2);
            $totalIva = round($totalIva, 2);
            $totalMonto = round($totalMonto, 2);

            // El monto final será la suma de la base + IVA (que debería ser igual al total calculado)
            $montoFinal = $totalBase + $totalIva;
            $ivaFinal = $totalIva;

            // Generar nombre del archivo XML
            $xmlPath = $request->xml;
            if (empty($xmlPath)) {
                $xmlPath = 'facturas/' . $request->tipo . '_' . $pedido->id . '_' . date('YmdHis') . '.xml';
            }

            // Crear la factura
            $factura = Factura::create([
                'pedido_id' => $request->pedido_id,
                'declarante_id' => $request->declarante_id,
                'tipo' => $request->tipo,
                'monto' => $montoFinal,
                'iva' => $ivaFinal,
                'xml' => $xmlPath
            ]);

            // Generar el XML de la factura
            $this->generarXMLFactura($factura, $pedido);

            // Actualizar el estado del pedido a 'Aprobado'
            $pedido->update(['fact' => 'Aprobado']);

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => [
                    'factura_id' => $factura->id,
                    'xml_path' => $xmlPath,
                    'tipo' => $request->tipo,
                    'monto' => $montoFinal,
                    'iva' => $ivaFinal
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear factura: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar XML de la factura
     */
    private function generarXMLFactura($factura, $pedido)
    {
        try {
            // Obtener datos del declarante
            $declarante = $factura->declarante;
            
            // Crear estructura XML
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><factura></factura>');
            
            // Información del declarante
            $emisor = $xml->addChild('emisor');
            $emisor->addChild('nombre', htmlspecialchars($declarante->nombre));
            $emisor->addChild('ruc', htmlspecialchars($declarante->ruc));
            
            // Información del cliente
            $receptor = $xml->addChild('receptor');
            $receptor->addChild('nombre', htmlspecialchars($pedido->cliente));
            $receptor->addChild('cedula', htmlspecialchars($pedido->cedula ?? 'N/A'));
            
            // Información de la factura
            $infoFactura = $xml->addChild('infoFactura');
            $infoFactura->addChild('tipo', htmlspecialchars($factura->tipo));
            $infoFactura->addChild('numero', $pedido->numero_orden);
            $infoFactura->addChild('fecha', date('d/m/Y'));
            
            // Detalles de productos/servicios
            $detalles = $xml->addChild('detalles');
            
            // Añadir inventarios
            foreach ($pedido->inventarios as $inventario) {
                $detalle = $detalles->addChild('detalle');
                $detalle->addChild('codigo', htmlspecialchars($inventario->codigo));
                $detalle->addChild('descripcion', htmlspecialchars($inventario->codigo));
                $detalle->addChild('cantidad', 1);
                $detalle->addChild('precio', number_format($inventario->pivot->precio, 2));
                $detalle->addChild('descuento', $inventario->pivot->descuento);
                
                $precioConDescuento = $inventario->pivot->precio * (1 - ($inventario->pivot->descuento / 100));
                $detalle->addChild('total', number_format($precioConDescuento, 2));
            }
            
            // Añadir lunas
            foreach ($pedido->lunas as $luna) {
                $detalle = $detalles->addChild('detalle');
                $detalle->addChild('codigo', 'LUNA');
                $detalle->addChild('descripcion', htmlspecialchars($luna->l_detalle . ' - ' . $luna->tipo_lente));
                $detalle->addChild('cantidad', 1);
                $detalle->addChild('precio', number_format($luna->l_precio, 2));
                $detalle->addChild('descuento', $luna->l_precio_descuento);
                
                $precioConDescuento = $luna->l_precio * (1 - ($luna->l_precio_descuento / 100));
                $detalle->addChild('total', number_format($precioConDescuento, 2));
            }
            
            // Totales
            $totales = $xml->addChild('totales');
            $totales->addChild('subtotal', number_format($factura->monto - $factura->iva, 2));
            $totales->addChild('iva', number_format($factura->iva, 2));
            $totales->addChild('total', number_format($factura->monto, 2));
            
            // Crear directorio si no existe
            $xmlFullPath = storage_path('app/public/' . $factura->xml);
            $xmlDirectory = dirname($xmlFullPath);
            
            if (!file_exists($xmlDirectory)) {
                mkdir($xmlDirectory, 0755, true);
            }
            
            // Guardar el archivo XML
            $xml->asXML($xmlFullPath);
            
            \Log::info('XML generado exitosamente: ' . $xmlFullPath);
            
        } catch (\Exception $e) {
            \Log::error('Error al generar XML: ' . $e->getMessage());
            // No lanzar excepción para no interrumpir el proceso de creación de factura
        }
    }

}
