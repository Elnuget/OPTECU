<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Inventario;
use App\Models\PedidoLuna; // Add this line
use App\Models\Empresa;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
            $query = Pedido::query()
                ->with([
                    'aInventario:id,codigo,cantidad',
                    'dInventario:id,codigo,cantidad',
                    'pagos:id,pedido_id,pago',
                    'empresa:id,nombre'
                ]);

            // Si no se solicitan todos los registros y no hay parámetros de fecha, redirigir al mes actual
            if (!$request->has('todos') && !$request->filled('fecha_especifica') && (!$request->filled('ano') || !$request->filled('mes'))) {
                $currentDate = now()->setTimezone('America/Guayaquil');
                $redirectParams = [
                    'ano' => $currentDate->format('Y'),
                    'mes' => $currentDate->format('m')
                ];
                
                // Mantener el filtro de empresa si se especifica
                if ($request->filled('empresa_id')) {
                    $redirectParams['empresa_id'] = $request->get('empresa_id');
                }
                
                return redirect()->route('pedidos.index', $redirectParams);
            }

            // Aplicar filtros de fecha
            if (!$request->has('todos')) {
                // Si hay una fecha específica, filtrar solo por esa fecha
                if ($request->filled('fecha_especifica')) {
                    $query->whereDate('fecha', $request->fecha_especifica);
                } else {
                    // Usar filtros de año y mes como antes
                    $query->whereYear('fecha', $request->ano)
                          ->whereMonth('fecha', $request->mes);
                }
            }
            
            // Aplicar filtro de empresa si se especifica
            if ($request->filled('empresa_id')) {
                $query->where('empresa_id', $request->get('empresa_id'));
            }
            
            // Verificar si el usuario está asociado a una empresa y no es admin
            $userEmpresaId = null;
            $isUserAdmin = auth()->user()->is_admin;
            $userEmpresas = collect(); // Para almacenar todas las empresas del usuario
            
            if (!$isUserAdmin) {
                // Obtener todas las empresas del usuario (principal + adicionales)
                $userEmpresas = auth()->user()->todasLasEmpresas();
                $userEmpresaId = auth()->user()->empresa_id;
                
                // Si el usuario tiene empresas asignadas y no hay filtro específico
                if ($userEmpresas->count() > 0 && !$request->filled('empresa_id')) {
                    $empresaIds = $userEmpresas->pluck('id')->toArray();
                    $query->whereIn('empresa_id', $empresaIds);
                }
            } else if (!$isUserAdmin && auth()->user()->empresa_id) {
                $userEmpresaId = auth()->user()->empresa_id;
                
                // Si el usuario tiene empresa asignada y no es admin, filtramos por su empresa
                // si no hay filtro de empresa específico en la solicitud
                if (!$request->filled('empresa_id')) {
                    $query->where('empresa_id', $userEmpresaId);
                }
            }

            $pedidos = $query->select([
                'id',
                'empresa_id',
                'numero_orden',
                'fecha',
                'fecha_entrega',
                'cliente',
                'celular',
                'total',
                'saldo',
                'fact',
                'usuario',
                'encuesta', // Asegurarnos de que la columna encuesta se cargue explícitamente
                'metodo_envio',
                'reclamo' // Agregar el campo reclamo
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

            // Obtener empresas para el filtro según el tipo de usuario
            if ($isUserAdmin) {
                $empresas = Empresa::orderBy('nombre')->get();
            } else {
                // Para usuarios no admin, mostrar solo sus empresas asignadas
                $empresas = auth()->user()->todasLasEmpresas()->sortBy('nombre')->values();
            }

            return view('pedidos.index', compact('pedidos', 'totales', 'empresas', 'userEmpresaId', 'isUserAdmin'));
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
        
        // Verificar si el usuario está asociado a empresas y no es admin
        $userEmpresaId = null;
        $userEmpresasIds = collect();
        $isUserAdmin = auth()->user()->is_admin;
        
        if (!$isUserAdmin) {
            // Obtener todas las empresas del usuario (principal + adicionales)
            $todasLasEmpresas = auth()->user()->todasLasEmpresas();
            $userEmpresasIds = $todasLasEmpresas->pluck('id');
            
            // Mantener compatibilidad con empresa_id individual
            $userEmpresaId = auth()->user()->empresa_id;
        }

        // Obtener armazones y accesorios del mes actual (solo con cantidad > 0)
        $inventarioQuery = Inventario::where('cantidad', '>', 0)
            ->whereYear('fecha', $currentYear)
            ->whereMonth('fecha', $currentMonth);
            
        // Si el usuario no es admin, filtrar por todas sus empresas asociadas
        if (!$isUserAdmin && $userEmpresasIds->isNotEmpty()) {
            $inventarioQuery->whereIn('empresa_id', $userEmpresasIds);
        }
        
        $inventario = $inventarioQuery->with('empresa')->get();

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
        
        // Obtener pacientes del historial clínico
        $pacientesHistorial = \App\Models\HistorialClinico::select(\DB::raw("CONCAT(nombres, ' ', apellidos) as nombre_completo"))
            ->whereNotNull('nombres')
            ->whereNotNull('apellidos')
            ->distinct()
            ->pluck('nombre_completo')
            ->toArray();
            
        // Obtener lista de pacientes únicos existentes
        $pacientes = Pedido::select('paciente')
            ->whereNotNull('paciente')
            ->distinct()
            ->pluck('paciente')
            ->toArray();
        
        // Combinar pacientes del historial con pacientes de pedidos
        $pacientes = array_merge($pacientes, $pacientesHistorial);
        $pacientes = array_unique($pacientes);
            
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

        // Obtener empresas para el select según el tipo de usuario
        if ($isUserAdmin) {
            // Administradores pueden ver todas las empresas
            $empresas = Empresa::orderBy('nombre')->get();
        } else {
            // Usuarios no administradores solo ven sus empresas asociadas
            $empresas = auth()->user()->todasLasEmpresas()->sortBy('nombre');
        }
        
        // Obtener historiales clínicos para autocompletado
        $historialesQuery = \App\Models\HistorialClinico::select('nombres', 'apellidos', 'cedula', 'celular', 'correo', 'direccion', 'empresa_id', 'fecha')
            ->with('empresa')
            ->whereNotNull('nombres')
            ->whereNotNull('apellidos');
            
        // Si el usuario no es admin, filtrar por todas sus empresas asociadas
        if (!$isUserAdmin && $userEmpresasIds->isNotEmpty()) {
            $historialesQuery->whereIn('empresa_id', $userEmpresasIds);
        }
        
        $historiales = $historialesQuery->orderBy('fecha', 'desc')->get();
        
        $currentDate = date('Y-m-d');
        $lastOrder = Pedido::orderBy('numero_orden', 'desc')->first();
        $nextOrderNumber = $lastOrder ? $lastOrder->numero_orden + 1 : 1;
        $nextInvoiceNumber = 'Pendiente';

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
            'empresas',
            'historiales',
            'userEmpresaId',
            'isUserAdmin',
            'userEmpresasIds'
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
            $pedido->direccion = $pedidoData['direccion'] ?? null;
            
            $pedido->save();

            // Handle armazones solo si hay datos válidos
            if ($request->has('a_inventario_id') && is_array($request->a_inventario_id)) {
                foreach ($request->a_inventario_id as $index => $inventarioId) {
                    if (!empty($inventarioId)) {
                        $precio = $request->a_precio[$index] ?? 0;
                        $descuento = $request->a_precio_descuento[$index] ?? 0;
                        $foto = null;
                        
                        // Manejar la foto si existe
                        if ($request->hasFile('a_foto') && isset($request->file('a_foto')[$index])) {
                            $fotoFile = $request->file('a_foto')[$index];
                            $fotoName = 'armazon_' . time() . '_' . $index . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                            
                            // Verificar que la carpeta existe
                            $carpetaDestino = public_path('img/armazones');
                            if (!file_exists($carpetaDestino)) {
                                mkdir($carpetaDestino, 0755, true);
                            }
                            
                            $fotoFile->move($carpetaDestino, $fotoName);
                            $foto = 'img/armazones/' . $fotoName;
                            
                            \Log::info('Foto de armazón guardada: ' . $foto);
                        }

                        $pedido->inventarios()->attach($inventarioId, [
                            'precio' => (float) $precio,
                            'descuento' => (float) $descuento,
                            'foto' => $foto,
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
                        $foto = null;
                        
                        // Manejar la foto si existe
                        if ($request->hasFile('l_foto') && isset($request->file('l_foto')[$key])) {
                            $fotoFile = $request->file('l_foto')[$key];
                            $fotoName = time() . '_luna_' . $key . '.' . $fotoFile->getClientOriginalExtension();
                            $fotoFile->move(public_path('img/lunas'), $fotoName);
                            $foto = 'img/lunas/' . $fotoName;
                        }
                        
                        $luna = new PedidoLuna([
                            'l_medida' => $medida,
                            'l_detalle' => $request->l_detalle[$key] ?? null,
                            'l_precio' => (float)($request->l_precio[$key] ?? 0),
                            'tipo_lente' => $request->tipo_lente[$key] ?? null,
                            'material' => $request->material[$key] ?? null,
                            'filtro' => $request->filtro[$key] ?? null,
                            'l_precio_descuento' => (float)($request->l_precio_descuento[$key] ?? 0),
                            'foto' => $foto
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
                    $foto = null;

                    if (!empty($inventarioId)) {
                        // Manejar la foto si existe
                        if ($request->hasFile('d_foto') && isset($request->file('d_foto')[$index])) {
                            $fotoFile = $request->file('d_foto')[$index];
                            $fotoName = 'accesorio_' . time() . '_' . $index . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                            
                            // Verificar que la carpeta existe
                            $carpetaDestino = public_path('img/accesorios');
                            if (!file_exists($carpetaDestino)) {
                                mkdir($carpetaDestino, 0755, true);
                            }
                            
                            $fotoFile->move($carpetaDestino, $fotoName);
                            $foto = 'img/accesorios/' . $fotoName;
                            
                            \Log::info('Foto de accesorio guardada: ' . $foto);
                        }
                        
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
                            'foto' => $foto,
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

            // Redirigir a la vista de creación de pagos con el ID del pedido creado
            return redirect()->route('pagos.create', ['pedido_id' => $pedido->id])->with([
                'error' => 'Exito',
                'mensaje' => 'Pedido creado exitosamente. Ahora puede registrar un pago.',
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
            $pedido = Pedido::with(['inventarios', 'lunas', 'pagos'])->findOrFail($id);
            
            // Obtener el año y mes actual
            $currentYear = date('Y');
            $currentMonth = date('m');
            
            // Primer intento: Filtrar inventario por mes y año actual con cantidad > 0
            $inventarioItems = Inventario::where('cantidad', '>', 0)
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
                    $inventarioItems = Inventario::where('cantidad', '>', 0)
                        ->whereYear('fecha', $lastItemDate->year)
                        ->whereMonth('fecha', $lastItemDate->month)
                        ->get();
                        
                    // Actualizar las variables de año y mes para mostrar en la vista
                    $currentYear = $lastItemDate->year;
                    $currentMonth = $lastItemDate->month;
                } else {
                    // Tercer intento: Si no hay ningún artículo con cantidad > 0, mostrar todos los artículos
                    $inventarioItems = Inventario::all();
                }
            }
                
            // Agregar también los items que ya están en este pedido (para que no desaparezcan al editar)
            $pedidoInventarioIds = $pedido->inventarios->pluck('id')->toArray();
            if (!empty($pedidoInventarioIds)) {
                $inventarioItemsPedido = Inventario::whereIn('id', $pedidoInventarioIds)->get();
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
            
            // Verificar si el usuario es administrador
            $isUserAdmin = auth()->user()->is_admin;
            
            // Obtener empresas para el select según el tipo de usuario
            if ($isUserAdmin) {
                // Administradores pueden ver todas las empresas
                $empresas = Empresa::orderBy('nombre')->get();
            } else {
                // Usuarios no administradores solo ven sus empresas asociadas
                $empresas = auth()->user()->todasLasEmpresas()->sortBy('nombre');
            }
            
            // Verificar si el usuario está asociado a empresas y no es admin
            $userEmpresaId = null;
            $userEmpresasIds = collect();
            
            if (!$isUserAdmin) {
                // Obtener todas las empresas del usuario (principal + adicionales)
                $todasLasEmpresas = auth()->user()->todasLasEmpresas();
                $userEmpresasIds = $todasLasEmpresas->pluck('id');
                
                // Mantener compatibilidad con empresa_id individual
                $userEmpresaId = auth()->user()->empresa_id;
            }
            
            // Pasar el mes y año de filtro a la vista
            $filtroMes = $currentMonth;
            $filtroAno = $currentYear;

            return view('pedidos.edit', compact('pedido', 'inventarioItems', 'totalPagado', 'usuarios', 'filtroMes', 'filtroAno', 'empresas', 'userEmpresaId', 'isUserAdmin', 'userEmpresasIds'));
            
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
        try {
            \DB::beginTransaction();
            
            $pedido = Pedido::findOrFail($id);
            
            // Guardar los IDs de inventario actuales antes de actualizarlos
            $inventariosAnteriores = $pedido->inventarios->pluck('id')->toArray();
            
            // Update basic pedido information including cedula
            $pedido->fill($request->except(['a_inventario_id', 'a_precio', 'a_precio_descuento', 'd_inventario_id', 'd_precio', 'd_precio_descuento']));
            $pedido->save();

            // Update pedido_inventario relationships
            // Primero guardamos las fotos existentes antes de hacer detach
            $fotosExistentes = [];
            foreach ($pedido->inventarios as $inventario) {
                if ($inventario->pivot->foto) {
                    $fotosExistentes[$inventario->id] = $inventario->pivot->foto;
                }
            }
            
            $pedido->inventarios()->detach(); // Remove existing relationships

            // Array para almacenar los nuevos IDs de inventario
            $nuevosInventarioIds = [];

            if ($request->has('a_inventario_id')) {
                foreach ($request->a_inventario_id as $index => $inventarioId) {
                    if (!empty($inventarioId)) {
                        $foto = null;
                        
                        // Manejar la foto si existe
                        if ($request->hasFile('a_foto') && isset($request->file('a_foto')[$index])) {
                            $fotoFile = $request->file('a_foto')[$index];
                            $fotoName = 'armazon_update_' . time() . '_' . $index . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                            
                            // Verificar que la carpeta existe
                            $carpetaDestino = public_path('img/armazones');
                            if (!file_exists($carpetaDestino)) {
                                mkdir($carpetaDestino, 0755, true);
                            }
                            
                            $fotoFile->move($carpetaDestino, $fotoName);
                            $foto = 'img/armazones/' . $fotoName;
                            
                            \Log::info('Foto de armazón actualizada: ' . $foto);
                        } else {
                            // Si no se sube nueva foto, mantener la existente si había una
                            $foto = $fotosExistentes[$inventarioId] ?? null;
                        }
                        
                        $pedido->inventarios()->attach($inventarioId, [
                            'precio' => $request->a_precio[$index] ?? 0,
                            'descuento' => $request->a_precio_descuento[$index] ?? 0,
                            'foto' => $foto,
                        ]);
                        
                        $nuevosInventarioIds[] = $inventarioId;
                    }
                }
            }

            // Update accesorios relationships
            if ($request->has('d_inventario_id')) {
                foreach ($request->d_inventario_id as $index => $accesorioId) {
                    if (!empty($accesorioId)) {
                        $foto = null;
                        
                        // Manejar la foto si existe (los accesorios también pueden tener fotos)
                        if ($request->hasFile('d_foto') && isset($request->file('d_foto')[$index])) {
                            $fotoFile = $request->file('d_foto')[$index];
                            $fotoName = 'accesorio_update_' . time() . '_' . $index . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                            
                            // Verificar que la carpeta existe
                            $carpetaDestino = public_path('img/accesorios');
                            if (!file_exists($carpetaDestino)) {
                                mkdir($carpetaDestino, 0755, true);
                            }
                            
                            $fotoFile->move($carpetaDestino, $fotoName);
                            $foto = 'img/accesorios/' . $fotoName;
                            
                            \Log::info('Foto de accesorio actualizada: ' . $foto);
                        } else {
                            // Si no se sube nueva foto, mantener la existente si había una
                            $foto = $fotosExistentes[$accesorioId] ?? null;
                        }
                        
                        $pedido->inventarios()->attach($accesorioId, [
                            'precio' => $request->d_precio[$index] ?? 0,
                            'descuento' => $request->d_precio_descuento[$index] ?? 0,
                            'foto' => $foto,
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
            // Primero guardamos las fotos existentes antes de hacer delete
            $fotosLunasExistentes = [];
            foreach ($pedido->lunas as $index => $luna) {
                if ($luna->foto) {
                    $fotosLunasExistentes[$index] = $luna->foto;
                }
            }
            
            $pedido->lunas()->delete(); // Remove existing lunas
            if ($request->has('l_medida')) {
                foreach ($request->l_medida as $key => $medida) {
                    if (!empty($medida)) {
                        $foto = null;
                        
                        // Manejar la foto si existe
                        if ($request->hasFile('l_foto') && isset($request->file('l_foto')[$key])) {
                            $fotoFile = $request->file('l_foto')[$key];
                            $fotoName = time() . '_luna_update_' . $key . '.' . $fotoFile->getClientOriginalExtension();
                            $fotoFile->move(public_path('img/lunas'), $fotoName);
                            $foto = 'img/lunas/' . $fotoName;
                        } else {
                            // Si no se sube nueva foto, mantener la existente si había una
                            $foto = $fotosLunasExistentes[$key] ?? null;
                        }
                        
                        $pedido->lunas()->create([
                            'l_medida' => $medida,
                            'l_detalle' => $request->l_detalle[$key] ?? null,
                            'l_precio' => $request->l_precio[$key] ?? 0,
                            'tipo_lente' => $request->tipo_lente[$key] ?? null,
                            'material' => $request->material[$key] ?? null,
                            'filtro' => $request->filtro[$key] ?? null,
                            'l_precio_descuento' => $request->l_precio_descuento[$key] ?? 0,
                            'foto' => $foto
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

    public function updateState($id, $state)
    {
        $pedido = Pedido::findOrFail($id);
        $estadoAnterior = $pedido->fact;
        
        // Actualizar el estado según el parámetro recibido
        switch ($state) {
            case 'cristaleria':
                $pedido->fact = 'CRISTALERIA';
                $mensaje = 'Pedido actualizado a CRISTALERIA';
                break;
            case 'separado':
                $pedido->fact = 'Separado';
                $mensaje = 'Pedido actualizado a Separado';
                break;
            case 'taller':
                $pedido->fact = 'LISTO EN TALLER';
                $mensaje = 'Pedido actualizado a LISTO EN TALLER';
                break;
            case 'enviado':
                $pedido->fact = 'Enviado';
                $mensaje = 'Pedido actualizado a Enviado';
                break;
            case 'entregado':
                $pedido->fact = 'ENTREGADO';
                $mensaje = 'Pedido marcado como ENTREGADO';
                break;
            default:
                return redirect()->route('pedidos.index')->with([
                    'error' => 'Error',
                    'mensaje' => 'Estado no válido',
                    'tipo' => 'alert-danger'
                ]);
        }
        
        $pedido->save();
        
        return redirect()->route('pedidos.index')->with([
            'error' => 'Exito',
            'mensaje' => $mensaje,
            'tipo' => 'alert-success'
        ]);
    }

    // Método original de aprobación - se mantiene para compatibilidad
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
     * Imprimir pedidos seleccionados
     */
    public function print(Request $request)
    {
        // Obtener IDs desde GET o POST
        $ids = $request->input('ids');
        
        // Validar que se reciban IDs
        if (empty($ids)) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se seleccionaron pedidos para imprimir'
            ]);
        }

        // Convertir IDs de string a array si es necesario
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        
        // Formato de impresión (tabla única o individual)
        $format = $request->input('format', 'table');
        
        // Obtener los pedidos con sus relaciones
        $pedidos = Pedido::with(['inventarios', 'lunas', 'empresa'])
            ->whereIn('id', $ids)
            ->orderBy('numero_orden', 'desc')
            ->get();

        if ($pedidos->isEmpty()) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se encontraron pedidos para imprimir'
            ]);
        }

        // Decidir qué vista utilizar basado en el formato solicitado
        if ($format === 'table') {
            return view('pedidos.print_table', compact('pedidos'));
        } else {
            return view('pedidos.print', compact('pedidos'));
        }
    }

    /**
     * Imprimir cristalería de pedidos seleccionados
     */
    public function printCristaleria(Request $request)
    {
        // Obtener IDs desde POST
        $ids = $request->input('ids');
        
        // Validar que se reciban IDs
        if (empty($ids)) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se seleccionaron pedidos para imprimir cristalería'
            ]);
        }

        // Convertir IDs de string a array si es necesario
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        
        // Obtener los pedidos con sus lunas
        $pedidos = Pedido::with(['lunas'])
            ->whereIn('id', $ids)
            ->orderBy('numero_orden', 'desc')
            ->get();

        if ($pedidos->isEmpty()) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se encontraron pedidos para imprimir cristalería'
            ]);
        }

        // Filtrar pedidos que tengan lunas
        $pedidosConLunas = $pedidos->filter(function($pedido) {
            return $pedido->lunas->count() > 0;
        });

        if ($pedidosConLunas->isEmpty()) {
            return redirect()->back()->with([
                'tipo' => 'alert-warning',
                'mensaje' => 'Los pedidos seleccionados no tienen lunas especificadas'
            ]);
        }

        return view('pedidos.print-cristaleria', ['pedidos' => $pedidosConLunas]);
    }

    /**
     * Generar vista de impresión con formato Excel de pedidos seleccionados
     */
    public function printExcel(Request $request)
    {
        // Obtener IDs desde GET o POST
        $ids = $request->input('ids');
        
        // Validar que se reciban IDs
        if (empty($ids)) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se seleccionaron pedidos para generar vista Excel'
            ]);
        }

        // Convertir IDs de string a array si es necesario
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        
        // Obtener los pedidos con sus relaciones
        $pedidos = Pedido::with(['inventarios', 'lunas', 'empresa'])
            ->whereIn('id', $ids)
            ->select([
                'id', 'numero_orden', 'cliente', 'cedula', 'celular', 'direccion', 
                'correo_electronico', 'empresa_id', 'metodo_envio', 'fecha_entrega'
            ])
            ->orderBy('numero_orden', 'desc')
            ->get();

        if ($pedidos->isEmpty()) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se encontraron pedidos para generar vista Excel'
            ]);
        }

        // Organizar los pedidos en filas de 3
        $pedidosAgrupados = $pedidos->chunk(3);

        // Generar la vista de impresión
        return view('pedidos.print-excel', compact('pedidosAgrupados'));
    }

    /**
     * Generar archivo Excel real de pedidos seleccionados (función auxiliar)
     */
    public function downloadExcel(Request $request)
    {
        // Obtener IDs desde GET o POST
        $ids = $request->input('ids');
        
        // Validar que se reciban IDs
        if (empty($ids)) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se seleccionaron pedidos para generar Excel'
            ]);
        }

        // Convertir IDs de string a array si es necesario
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        
        // Obtener los pedidos con sus relaciones
        $pedidos = Pedido::with(['inventarios', 'lunas', 'empresa'])
            ->whereIn('id', $ids)
            ->select([
                'id', 'numero_orden', 'cliente', 'cedula', 'celular', 'direccion', 
                'correo_electronico', 'empresa_id', 'metodo_envio', 'fecha_entrega'
            ])
            ->orderBy('numero_orden', 'desc')
            ->get();

        if ($pedidos->isEmpty()) {
            return redirect()->back()->with([
                'tipo' => 'alert-danger',
                'mensaje' => 'No se encontraron pedidos para generar Excel'
            ]);
        }

        // Generar el archivo Excel
        return $this->generateExcelFile($pedidos);
    }

    /**
     * Generar el archivo Excel con el formato específico
     */
    private function generateExcelFile($pedidos)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configurar la columna A con texto vertical para la primera fila
        $sheet->setCellValue('A1', 'DE: L BARBOSA SPA 77.219.776-4');
        $sheet->getStyle('A1')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        
        // Ajustar ancho de columna A automáticamente y altura para el texto vertical
        $sheet->getColumnDimension('A')->setAutoSize(true);
        // Establecer un ancho mínimo para la columna A
        if ($sheet->getColumnDimension('A')->getWidth() < 8) {
            $sheet->getColumnDimension('A')->setWidth(8);
        }
        $sheet->getRowDimension('1')->setRowHeight(200); // Altura suficiente para el texto vertical
        
        // Configurar las columnas para los pedidos con sus respectivos dropdowns
        // Pedido 1: B (empresa), C (info), D (vacía), E (dropdown)
        // Pedido 2: H (empresa), I (info), J (vacía), K (dropdown)  
        // Pedido 3: N (empresa), O (info), P (vacía), Q (dropdown)
        $columnas = ['B', 'H', 'N']; // 3 pedidos por fila
        $columnasInfo = ['C', 'I', 'O']; // Información del pedido
        $columnasVacias = ['D', 'J', 'P']; // Columnas vacías
        $columnasDropdown = ['E', 'K', 'Q']; // Dropdowns
        $filaActual = 1;
        $pedidoEnFila = 0;
        
        foreach ($pedidos as $index => $pedido) {
            // Determinar posición
            $columnaBase = $columnas[$pedidoEnFila];
            $siguienteColumna = $columnasInfo[$pedidoEnFila];
            $columnaCombo = $columnasDropdown[$pedidoEnFila];
            $fila = $filaActual;
            
            // Colocar el texto "DE: L BARBOSA SPA 77.219.776-4" en la columna A para cada fila de pedidos
            if ($pedidoEnFila == 0) { // Solo en la primera posición de cada fila
                $sheet->setCellValue('A' . $fila, 'DE: L BARBOSA SPA 77.219.776-4');
                $sheet->getStyle('A' . $fila)->getAlignment()->setTextRotation(90);
                $sheet->getStyle('A' . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $fila)->getFont()->setBold(true);
            }
            
            // Información del pedido
            $empresaNombre = $pedido->empresa ? $pedido->empresa->nombre : 'Sin empresa';
            $numeroOrden = $pedido->numero_orden;
            
            // Información del pedido completa
            $infoPedido = "CLIENTE: " . strtoupper($pedido->cliente) . "\n";
            $infoPedido .= "CÉDULA: " . ($pedido->cedula ? $pedido->cedula : 'NO REGISTRADA') . "\n";
            $infoPedido .= "TELÉFONO: " . $pedido->celular . "\n";
            $infoPedido .= "DIRECCIÓN: " . ($pedido->direccion ? $pedido->direccion : 'NO REGISTRADA') . "\n";
            $infoPedido .= "CORREO: " . ($pedido->correo_electronico ? $pedido->correo_electronico : 'NO REGISTRADO') . "\n";
            $infoPedido .= "FECHA ENTREGA: " . ($pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : 'NO REGISTRADA') . "\n";
            
            // Agregar información de armazones/accesorios
            if ($pedido->inventarios->count() > 0) {
                $infoPedido .= "ARMAZONES/ACCESORIOS:\n";
                foreach ($pedido->inventarios as $inventario) {
                    $infoPedido .= "- " . $inventario->codigo . "\n";
                }
            }
            
            // Colocar empresa + número de orden en la primera columna del pedido
            $sheet->setCellValue($columnaBase . $fila, strtoupper($empresaNombre) . " - " . $numeroOrden);
            $sheet->getStyle($columnaBase . $fila)->getFont()->setBold(true);
            $sheet->getStyle($columnaBase . $fila)->getAlignment()->setTextRotation(90);
            $sheet->getStyle($columnaBase . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($columnaBase . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Colocar información del pedido en la siguiente columna
            $sheet->setCellValue($siguienteColumna . $fila, $infoPedido);
            $sheet->getStyle($siguienteColumna . $fila)->getAlignment()->setWrapText(true);
            $sheet->getStyle($siguienteColumna . $fila)->getAlignment()->setTextRotation(90);
            $sheet->getStyle($siguienteColumna . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($siguienteColumna . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Colocar método de envío en lugar del dropdown
            $metodoEnvio = $pedido->metodo_envio ? strtoupper($pedido->metodo_envio) : 'NO ESPECIFICADO';
            $sheet->setCellValue($columnaCombo . $fila, $metodoEnvio);
            
            // Aplicar estilo al método de envío
            $sheet->getStyle($columnaCombo . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($columnaCombo . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnaCombo . $fila)->getAlignment()->setTextRotation(90); // Hacer el texto vertical
            $sheet->getStyle($columnaCombo . $fila)->getFont()->setBold(true);
            
            // Ajustar ancho de columnas automáticamente basado en el contenido
            $sheet->getColumnDimension($columnaBase)->setAutoSize(true);
            $sheet->getColumnDimension($siguienteColumna)->setAutoSize(true);
            $sheet->getColumnDimension($columnaCombo)->setAutoSize(true);
            
            // Establecer un ancho mínimo para las columnas
            if ($sheet->getColumnDimension($columnaBase)->getWidth() < 8) {
                $sheet->getColumnDimension($columnaBase)->setWidth(8);
            }
            if ($sheet->getColumnDimension($siguienteColumna)->getWidth() < 12) {
                $sheet->getColumnDimension($siguienteColumna)->setWidth(12);
            }
            if ($sheet->getColumnDimension($columnaCombo)->getWidth() < 15) {
                $sheet->getColumnDimension($columnaCombo)->setWidth(15);
            }
            
            // Ajustar altura de la fila automáticamente basado en el contenido
            $numeroLineas = substr_count($infoPedido, "\n") + 1;
            $alturaCalculada = max(200, $numeroLineas * 12); // Mínimo 200 para acomodar el texto vertical
            $sheet->getRowDimension($fila)->setRowHeight($alturaCalculada);
            
            // Incrementar contador de pedidos en fila
            $pedidoEnFila++;
            
            // Si ya tenemos 3 pedidos en la fila, pasar a la siguiente fila
            if ($pedidoEnFila >= 3) {
                $pedidoEnFila = 0;
                $filaActual += 1; // Solo aumentar una fila ya que cada fila se ajusta automáticamente
            }
        }
        
        // Aplicar bordes a todo el rango de datos generado
        $ultimaColumna = 'Q'; // Hasta la columna Q
        $ultimaFila = $filaActual;
        $rangoCompleto = 'A1:' . $ultimaColumna . $ultimaFila;
        
        // Configurar el estilo de borde
        $styleBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'], // Negro
                ],
            ],
        ];
        
        // Aplicar bordes al rango completo
        $sheet->getStyle($rangoCompleto)->applyFromArray($styleBorder);
        
        // No necesitamos ajustar altura manualmente ya que se hace automáticamente arriba
        
        // Configurar encabezados para descarga
        $filename = 'Pedidos_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Obtener el próximo número de orden disponible
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextOrderNumber()
    {
        try {
            $lastOrder = Pedido::orderBy('numero_orden', 'desc')->first();
            $nextOrderNumber = $lastOrder ? $lastOrder->numero_orden + 1 : 1;
            
            return response()->json([
                'success' => true,
                'next_order_number' => $nextOrderNumber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el próximo número de orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar un reclamo a un pedido
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function agregarReclamo(Request $request, $id)
    {
        try {
            $request->validate([
                'reclamo' => 'required|string|min:10|max:1000'
            ], [
                'reclamo.required' => 'El reclamo es obligatorio',
                'reclamo.min' => 'El reclamo debe tener al menos 10 caracteres',
                'reclamo.max' => 'El reclamo no puede exceder 1000 caracteres'
            ]);

            $pedido = Pedido::findOrFail($id);
            
            // Verificar si ya tiene un reclamo
            if (!empty($pedido->reclamo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pedido ya tiene un reclamo registrado'
                ], 400);
            }

            $pedido->reclamo = $request->reclamo;
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Reclamo agregado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el reclamo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quitar un reclamo de un pedido
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function quitarReclamo($id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            
            // Verificar si tiene un reclamo
            if (empty($pedido->reclamo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pedido no tiene un reclamo registrado'
                ], 400);
            }

            $pedido->reclamo = null;
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Reclamo eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el reclamo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
