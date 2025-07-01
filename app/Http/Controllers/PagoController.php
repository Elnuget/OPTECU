<?php

namespace App\Http\Controllers;

use App\Models\mediosdepago;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Models\Pago; // Ensure the Pago model is correctly referenced
use App\Models\Caja;
use App\Models\Empresa;
use Illuminate\Support\Facades\Mail;
use App\Mail\PagoNotification;
use Illuminate\Support\Facades\Log;

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
        // Si no hay parámetros de fecha, redirigir al mes actual
        if (!$request->filled('ano') || !$request->filled('mes')) {
            $currentDate = now()->setTimezone('America/Guayaquil');
            return redirect()->route('pagos.index', [
                'ano' => $currentDate->format('Y'),
                'mes' => $currentDate->format('m')
            ]);
        }

        $mediosdepago = mediosdepago::all();
        $query = Pago::with(['pedido', 'mediodepago']);

        // Aplicar filtros de fecha (ahora siempre se aplicarán)
        $query->whereYear('created_at', '=', $request->ano)
              ->whereHas('pedido', function($q) use ($request) {
                  $q->whereYear('fecha', '=', $request->ano);
              });

        $query->whereMonth('created_at', '=', (int)$request->mes)
              ->whereHas('pedido', function($q) use ($request) {
                  $q->whereMonth('fecha', '=', (int)$request->mes);
              });

        if ($request->filled('metodo_pago')) {
            $query->where('mediodepago_id', '=', $request->metodo_pago);
        }

        // Nuevo filtro por estado TC
        if ($request->filled('tc_status')) {
            // Filtrar siempre por Tarjeta de Crédito (ID 4) cuando se usa el filtro TC
            $query->where('mediodepago_id', 4);

            if ($request->tc_status === 'pendientes') {
                $query->where(function ($q) {
                    $q->where('TC', false)->orWhereNull('TC');
                });
            } elseif ($request->tc_status === 'pagados') {
                $query->where('TC', true);
            }
        }

        // Solo incluir pagos que tienen pedidos asociados y válidos
        $query->whereHas('pedido', function($q) {
            $q->whereNotNull('id');
        });

        $pagos = $query->get();
        
        // Calcular el total solo de pagos con pedidos válidos
        $totalPagos = $pagos->sum('pago');

        // Pasar el estado actual del filtro TC a la vista
        $tcStatus = $request->input('tc_status');

        return view('pagos.index', compact('pagos', 'mediosdepago', 'totalPagos', 'tcStatus'));
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
        // Validate data
        $validatedData = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id', // Hacer pedido_id requerido
            'mediodepago_id' => 'required|exists:mediosdepagos,id',
            'pago' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'created_at' => 'sometimes|nullable|date',
            'TC' => 'sometimes|nullable|boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Verificar que el pedido existe y es del mes actual
            $pedido = Pedido::findOrFail($validatedData['pedido_id']);
            
            if (!$pedido) {
                throw new \Exception('El pedido no existe');
            }

            // Format pago to ensure exact decimal
            $validatedData['pago'] = number_format((float)$validatedData['pago'], 2, '.', '');

            // Handle photo upload
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $nombreFoto = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads/pagos'), $nombreFoto);
                $validatedData['foto'] = $nombreFoto;
            }

            // Create a new pago
            $nuevoPago = Pago::create($validatedData);

            // Update the pedido's saldo
            $pedido->saldo -= $validatedData['pago'];
            $pedido->save();

            // Si el método de pago es Efectivo (asumiendo que el ID es 1)
            if ($validatedData['mediodepago_id'] == 1) {
                // Crear entrada en caja
                Caja::create([
                    'valor' => $validatedData['pago'],
                    'motivo' => 'Abono ' . $pedido->cliente,
                    'user_id' => auth()->id()
                ]);
            }

            // Send email notification
            try {
                $empresas = Empresa::all();
                if($empresas->isNotEmpty()) {
                    foreach($empresas as $empresa) {
                        Mail::to($empresa->correo)->send(new PagoNotification($nuevoPago));
                        Log::info('Email sent successfully to ' . $empresa->correo . ' for payment ID: ' . $nuevoPago->id);
                    }
                } else {
                    Log::info('No registered companies found to send email notifications');
                }
            } catch (\Exception $e) {
                Log::error('Failed to send email for payment ID: ' . $nuevoPago->id . '. Error: ' . $e->getMessage());
            }

            // Obtener el año y mes actual
            $currentYear = date('Y');
            $currentMonth = date('m');

            return redirect("/Pagos?ano={$currentYear}&mes={$currentMonth}")->with([
                'error' => 'Exito',
                'mensaje' => 'Pago creado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            if (isset($nuevoPago)) {
                $nuevoPago->delete();
            }

            return redirect()->route('pagos.index')->with([
                'error' => 'Error',
                'mensaje' => 'El pago no se ha creado. Error: ' . $e->getMessage(),
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
        $validatedData = $request->validate([
            'pedido_id' => 'nullable|exists:pedidos,id',
            'mediodepago_id' => 'nullable|exists:mediosdepagos,id',
            'pago' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'created_at' => 'sometimes|nullable|date',
            'TC' => 'sometimes|nullable|boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Format pago to ensure exact decimal
        $validatedData['pago'] = number_format((float)$validatedData['pago'], 2, '.', '');

        try {
            $pago = Pago::findOrFail($id);
            $oldPagoAmount = $pago->pago;

            // Handle photo upload
            if ($request->hasFile('foto')) {
                // Delete old photo if exists
                if ($pago->foto && file_exists(public_path('uploads/pagos/' . $pago->foto))) {
                    unlink(public_path('uploads/pagos/' . $pago->foto));
                }
                
                $foto = $request->file('foto');
                $nombreFoto = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads/pagos'), $nombreFoto);
                $validatedData['foto'] = $nombreFoto;
            }

            // Si se proporciona una nueva fecha de creación, actualizarla
            if (isset($validatedData['created_at'])) {
                $pago->created_at = $validatedData['created_at'];
            }

            $pago->update($validatedData);
            
            // Actualizar saldo del pedido si se proporciona pedido_id
            if (isset($validatedData['pedido_id'])) {
                $pedido = Pedido::find($validatedData['pedido_id']);
                if ($pedido) {
                    $pedido->saldo += $oldPagoAmount; // Revert the old payment amount
                    $pedido->saldo -= $validatedData['pago']; // Apply the new payment amount
                    $pedido->save();
                }
            } else {
                // If pedido_id is not provided, update the saldo of the existing pedido
                $pedido = $pago->pedido;
                if ($pedido) {
                    $pedido->saldo += $oldPagoAmount; // Revert the old payment amount
                    $pedido->saldo -= $validatedData['pago']; // Apply the new payment amount
                    $pedido->save();
                }
            }

            return redirect()->route('pagos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Pago actualizado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pagos.index')->with([
                'error' => 'Error',
                'mensaje' => 'Pago no se ha actualizado',
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
            $pago = Pago::findOrFail($id);
            $pedido = $pago->pedido;

            if ($pedido) {
                $pedido->saldo += $pago->pago; // Add the payment amount back to the order's balance
                $pedido->save();
            }

            // Si el pago es en efectivo (ID 1), eliminar la entrada correspondiente en caja
            if ($pago->mediodepago_id == 1) {
                $cajaEntry = Caja::where([
                    ['valor', '=', $pago->pago],
                    ['motivo', '=', 'Abono ' . $pedido->cliente]
                ])->first();

                if ($cajaEntry) {
                    $cajaEntry->delete();
                }
            }

            // Delete photo if exists
            if ($pago->foto && file_exists(public_path('uploads/pagos/' . $pago->foto))) {
                unlink(public_path('uploads/pagos/' . $pago->foto));
            }

            $pago->delete(); // Deletes from the 'pagos' table

            return redirect()->route('pagos.index')->with([
                'error' => 'Exito',
                'mensaje' => 'Pago eliminado exitosamente',
                'tipo' => 'alert-success'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pagos.index')->with([
                'error' => 'Error',
                'mensaje' => 'Pago no se ha eliminado',
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
            $pago = Pago::findOrFail($id);
            $pago->TC = true;
            $pago->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado TC actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado TC'
            ], 500);
        }
    }
}
