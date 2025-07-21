<?php

namespace App\Http\Controllers;

use App\Models\mediosdepago;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Models\Pago; // Ensure the Pago model is correctly referenced
use App\Models\Caja;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin')->only(['edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $mediosdepago = mediosdepago::all();
        $query = Pago::with(['pedido', 'mediodepago']);
        $user = auth()->user();
        $userEmpresaId = $user->empresa_id;
        $isAdmin = $user->is_admin;

        // Si no se solicitan todos los registros y no hay parámetros de fecha, redirigir al mes actual
        if (!$request->has('todos') && !$request->filled('fecha_especifica') && (!$request->filled('ano') || !$request->filled('mes'))) {
            $currentDate = now()->setTimezone('America/Guayaquil');
            $redirectParams = [
                'ano' => $currentDate->format('Y'),
                'mes' => $currentDate->format('m')
            ];
            
            // Mantener el filtro de empresa si se especifica
            if ($request->filled('empresa')) {
                $redirectParams['empresa'] = $request->get('empresa');
            }
            
            return redirect()->route('pagos.index', $redirectParams);
        }

        // Aplicar filtros de fecha
        if (!$request->has('todos')) {
            // Si hay una fecha específica, filtrar solo por esa fecha
            if ($request->filled('fecha_especifica')) {
                $query->whereDate('created_at', $request->fecha_especifica);
            } else {
                // Usar filtros de año y mes como antes
                $query->whereYear('created_at', '=', $request->get('ano'))
                      ->whereMonth('created_at', '=', (int)$request->get('mes'));
            }
        }

        // Aplicar filtro por método de pago si está seleccionado
        if ($request->filled('metodo_pago')) {
            $query->where('mediodepago_id', $request->get('metodo_pago'));
        }

        // Obtener los pagos
        $pagos = $query->orderBy('created_at', 'desc')->get();
        
        // Manejar filtrado por empresa según tipo de usuario
        if (!$isAdmin) {
            // Para usuarios no admin, obtener todas sus empresas asignadas
            $userEmpresas = $user->todasLasEmpresas();
            
            if ($userEmpresas->count() > 0) {
                $empresaIds = $userEmpresas->pluck('id')->toArray();
                
                if ($request->filled('empresa')) {
                    // Si hay filtro específico, verificar que tenga acceso a esa empresa
                    if (in_array($request->get('empresa'), $empresaIds)) {
                        $pagos = $pagos->filter(function($pago) use ($request) {
                            return $pago->pedido->empresa_id == $request->get('empresa');
                        });
                    } else {
                        // Si no tiene acceso, mostrar pagos de todas sus empresas
                        $pagos = $pagos->filter(function($pago) use ($empresaIds) {
                            return in_array($pago->pedido->empresa_id, $empresaIds);
                        });
                    }
                } else {
                    // Si no hay filtro específico, mostrar pagos de todas sus empresas
                    $pagos = $pagos->filter(function($pago) use ($empresaIds) {
                        return in_array($pago->pedido->empresa_id, $empresaIds);
                    });
                }
            }
        } else if ($request->filled('empresa')) {
            // Para admins, aplicar el filtro normalmente
            $pagos = $pagos->filter(function($pago) use ($request) {
                return $pago->pedido->empresa_id == $request->empresa;
            });
        }
        
        // Calcular el total de pagos
        $totalPagos = $pagos->sum('pago');

        // Obtener empresas para el filtro según el tipo de usuario
        if ($isAdmin) {
            $empresas = Empresa::orderBy('nombre')->get();
        } else {
            // Para usuarios no admin, mostrar solo sus empresas asignadas
            $empresas = $user->todasLasEmpresas()->sortBy('nombre')->values();
        }

        return view('pagos.index', compact('pagos', 'mediosdepago', 'totalPagos', 'empresas', 'isAdmin', 'userEmpresaId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $mediosdepago = mediosdepago::all();
        $pedidos = Pedido::select('id', 'numero_orden', 'saldo', 'cliente')->get(); // Seleccionar solo id, numero_orden, saldo y cliente
        $selectedPedidoId = $request->get('pedido_id'); // Obtener el pedido seleccionado si existe
        return view('pagos.create', compact('mediosdepago', 'pedidos', 'selectedPedidoId')); // Pasar pedidos a la vista
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
            // Validate data
            $validatedData = $request->validate([
                'pedido_id' => 'required|exists:pedidos,id', // Hacer pedido_id requerido
                'mediodepago_id' => 'required|exists:mediosdepagos,id',
                'pago' => 'required|regex:/^\d+(\.\d{1,2})?$/',
                'created_at' => 'sometimes|nullable|date',
                'TC' => 'sometimes|nullable|boolean',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Verificar que el pedido existe
            $pedido = Pedido::findOrFail($validatedData['pedido_id']);
            
            if (!$pedido) {
                throw new \Exception('El pedido seleccionado no existe');
            }

            // Verificar que el pago no sea mayor al saldo
            $pagoAmount = (float)$validatedData['pago'];
            if ($pagoAmount > $pedido->saldo) {
                throw new \Exception('El monto del pago no puede ser mayor al saldo pendiente del pedido');
            }

            if ($pagoAmount <= 0) {
                throw new \Exception('El monto del pago debe ser mayor a cero');
            }

            // Format pago to ensure exact decimal
            $validatedData['pago'] = number_format($pagoAmount, 2, '.', '');

            // Create uploads directory if it doesn't exist
            $uploadsPath = public_path('uploads/pagos');
            if (!file_exists($uploadsPath)) {
                mkdir($uploadsPath, 0755, true);
            }

            // Handle photo upload with better error handling
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                
                // Verificar que el archivo sea válido
                if (!$foto->isValid()) {
                    throw new \Exception('El archivo de foto no es válido');
                }
                
                // Generar nombre único para evitar conflictos
                $extension = $foto->getClientOriginalExtension();
                $nombreFoto = 'pago_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Mover archivo con verificación
                if (!$foto->move($uploadsPath, $nombreFoto)) {
                    throw new \Exception('Error al guardar la foto del pago');
                }
                
                $validatedData['foto'] = $nombreFoto;
            }

            // Comenzar transacción de base de datos
            \DB::beginTransaction();

            // Create a new pago
            $nuevoPago = Pago::create($validatedData);
            
            if (!$nuevoPago) {
                throw new \Exception('Error al crear el registro de pago en la base de datos');
            }

            // Update the pedido's saldo
            $pedido->saldo -= $validatedData['pago'];
            
            if (!$pedido->save()) {
                throw new \Exception('Error al actualizar el saldo del pedido');
            }

            // Si el método de pago es Efectivo (asumiendo que el ID es 1)
            if ($validatedData['mediodepago_id'] == 1) {
                // Crear entrada en caja con la empresa del pedido
                $cajaEntry = Caja::create([
                    'valor' => $validatedData['pago'],
                    'motivo' => 'Abono ' . $pedido->cliente,
                    'user_id' => auth()->id(),
                    'empresa_id' => $pedido->empresa_id
                ]);
                
                if (!$cajaEntry) {
                    throw new \Exception('Error al registrar el movimiento en caja');
                }
            }

            // Confirmar transacción
            \DB::commit();

            // Redirigir al index de pedidos en lugar de pagos
            return redirect()->route('pedidos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Pago creado exitosamente. El saldo del pedido ha sido actualizado.',
                'tipo' => 'alert-success'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Rollback en caso de error de validación
            \DB::rollback();
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'error' => 'Error de Validación',
                    'mensaje' => 'Por favor, revise los datos ingresados.',
                    'tipo' => 'alert-danger'
                ]);

        } catch (\Exception $e) {
            // Rollback en caso de cualquier error
            \DB::rollback();
            
            // Eliminar foto si se subió pero falló la creación
            if (isset($validatedData['foto']) && file_exists($uploadsPath . '/' . $validatedData['foto'])) {
                unlink($uploadsPath . '/' . $validatedData['foto']);
            }

            Log::error('Error creating payment: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => 'Error',
                    'mensaje' => 'El pago no se ha creado. ' . $e->getMessage(),
                    'tipo' => 'alert-danger'
                ]);
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
        $pago = Pago::findOrFail($id); // Encontrar pago por ID
        return view('pagos.show', compact('pago')); // Retornar vista para mostrar el pago
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $mediosdepago = mediosdepago::all();
        $pedidos = Pedido::select('id', 'numero_orden', 'saldo', 'cliente')->get(); // Agregado 'cliente' a la consulta
        $pago = Pago::findOrFail($id);
        return view('pagos.edit', compact('pago', 'mediosdepago', 'pedidos'));
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
            $validatedData = $request->validate([
                'pedido_id' => 'nullable|exists:pedidos,id',
                'mediodepago_id' => 'nullable|exists:mediosdepagos,id',
                'pago' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
                'created_at' => 'sometimes|nullable|date',
                'TC' => 'sometimes|nullable|boolean',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Format pago to ensure exact decimal
            if (isset($validatedData['pago'])) {
                $validatedData['pago'] = number_format((float)$validatedData['pago'], 2, '.', '');
            }

            $pago = Pago::findOrFail($id);
            $oldPagoAmount = $pago->pago;

            // Create uploads directory if it doesn't exist
            $uploadsPath = public_path('uploads/pagos');
            if (!file_exists($uploadsPath)) {
                mkdir($uploadsPath, 0755, true);
            }

            // Handle photo upload with better error handling
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                
                // Verificar que el archivo sea válido
                if (!$foto->isValid()) {
                    throw new \Exception('El archivo de foto no es válido');
                }
                
                // Delete old photo if exists
                if ($pago->foto && file_exists($uploadsPath . '/' . $pago->foto)) {
                    unlink($uploadsPath . '/' . $pago->foto);
                }
                
                // Generar nombre único para evitar conflictos
                $extension = $foto->getClientOriginalExtension();
                $nombreFoto = 'pago_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Mover archivo con verificación
                if (!$foto->move($uploadsPath, $nombreFoto)) {
                    throw new \Exception('Error al guardar la nueva foto del pago');
                }
                
                $validatedData['foto'] = $nombreFoto;
            }

            // Si se proporciona una nueva fecha de creación, actualizarla
            if (isset($validatedData['created_at'])) {
                $pago->created_at = $validatedData['created_at'];
            }

            // Comenzar transacción
            DB::beginTransaction();

            $pago->update($validatedData);
            
            // Actualizar saldo del pedido si se proporciona pedido_id
            if (isset($validatedData['pedido_id'])) {
                $pedido = Pedido::find($validatedData['pedido_id']);
                if ($pedido) {
                    $pedido->saldo += $oldPagoAmount; // Revert the old payment amount
                    $pedido->saldo -= $validatedData['pago']; // Apply the new payment amount
                    
                    if (!$pedido->save()) {
                        throw new \Exception('Error al actualizar el saldo del pedido');
                    }
                }
            } else {
                // If pedido_id is not provided, update the saldo of the existing pedido
                $pedido = $pago->pedido;
                if ($pedido) {
                    $pedido->saldo += $oldPagoAmount; // Revert the old payment amount
                    $pedido->saldo -= $validatedData['pago']; // Apply the new payment amount
                    
                    if (!$pedido->save()) {
                        throw new \Exception('Error al actualizar el saldo del pedido');
                    }
                }
            }

            // Confirmar transacción
            DB::commit();

            return redirect()->route('pagos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Pago actualizado exitosamente',
                'tipo' => 'alert-success'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'error' => 'Error de Validación',
                    'mensaje' => 'Por favor, revise los datos ingresados.',
                    'tipo' => 'alert-danger'
                ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error updating payment: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => 'Error',
                    'mensaje' => 'El pago no se ha actualizado. ' . $e->getMessage(),
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
            // Comenzar transacción
            DB::beginTransaction();
            
            $pago = Pago::findOrFail($id);
            $pedido = $pago->pedido;

            if (!$pedido) {
                throw new \Exception('No se encontró el pedido asociado al pago');
            }

            // Add the payment amount back to the order's balance
            $pedido->saldo += $pago->pago;
            
            if (!$pedido->save()) {
                throw new \Exception('Error al actualizar el saldo del pedido');
            }

            // Si el pago es en efectivo (ID 1), eliminar la entrada correspondiente en caja
            if ($pago->mediodepago_id == 1) {
                $cajaEntry = Caja::where([
                    ['valor', '=', $pago->pago],
                    ['motivo', '=', 'Abono ' . $pedido->cliente],
                    ['empresa_id', '=', $pedido->empresa_id]
                ])->first();

                if ($cajaEntry) {
                    if (!$cajaEntry->delete()) {
                        throw new \Exception('Error al eliminar el movimiento de caja');
                    }
                }
            }

            // Delete photo if exists
            if ($pago->foto && file_exists(public_path('uploads/pagos/' . $pago->foto))) {
                unlink(public_path('uploads/pagos/' . $pago->foto));
            }

            // Delete the payment record
            if (!$pago->delete()) {
                throw new \Exception('Error al eliminar el registro de pago');
            }

            // Confirmar transacción
            DB::commit();

            return redirect()->route('pagos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Pago eliminado exitosamente',
                'tipo' => 'alert-success'
            ]);
            
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollback();
            
            Log::error('Error eliminating payment: ' . $e->getMessage());
            
            return redirect()->route('pagos.index')->with([
                'error' => 'Error',
                'mensaje' => 'El pago no se ha eliminado. ' . $e->getMessage(),
                'tipo' => 'alert-danger'
            ]);
        }
    }

    /**
     * Update TC status for a payment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTC($id)
    {
        try {
            // Comenzar transacción
            DB::beginTransaction();
            
            $pago = Pago::findOrFail($id);
            $pago->TC = true;
            
            if (!$pago->save()) {
                throw new \Exception('Error al actualizar el estado TC');
            }
            
            // Confirmar transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado TC actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollback();
            
            Log::error('Error updating TC status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado TC: ' . $e->getMessage()
            ], 500);
        }
    }
}
