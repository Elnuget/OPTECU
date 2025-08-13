<?php

namespace App\Http\Controllers;

use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoPrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;
        $pagosPrestamos = PagoPrestamo::with(['prestamo', 'user'])
            ->when(!Auth::user()->hasRole('admin'), function ($query) use ($empresaId) {
                return $query->porEmpresa($empresaId);
            })
            ->latest()
            ->get();
            
        $empresas = Empresa::orderBy('nombre')->get();
        
        return view('pago-prestamos.index', compact('pagosPrestamos', 'empresas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'prestamo_id' => 'required|exists:prestamos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'valor' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:pagado,pendiente',
        ]);
        
        // Verificar que el préstamo exista y tenga saldo pendiente
        $prestamo = Prestamo::findOrFail($request->prestamo_id);
        
        // Verificar que el valor no supere el saldo pendiente
        if ($request->valor > $prestamo->saldo_pendiente) {
            return redirect()->back()
                ->with('error', true)
                ->with('tipo', 'alert-danger')
                ->with('mensaje', 'El valor del pago no puede superar el saldo pendiente del préstamo');
        }
        
        // Crear el pago
        $pago = PagoPrestamo::create([
            'prestamo_id' => $request->prestamo_id,
            'empresa_id' => $request->empresa_id,
            'user_id' => Auth::id(),
            'valor' => $request->valor,
            'fecha_pago' => $request->fecha_pago,
            'motivo' => $request->motivo,
            'observaciones' => $request->observaciones,
            'estado' => $request->estado,
        ]);
        
        return redirect()->back()
            ->with('error', true)
            ->with('tipo', 'alert-success')
            ->with('mensaje', 'Pago registrado correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pago = PagoPrestamo::with(['prestamo', 'prestamo.user', 'empresa'])->findOrFail($id);
        
        return view('pago-prestamos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pago = PagoPrestamo::with(['prestamo', 'prestamo.user'])->findOrFail($id);
        $empresas = Empresa::orderBy('nombre')->get();
        
        if (request()->ajax()) {
            return response()->json([
                'id' => $pago->id,
                'prestamo_id' => $pago->prestamo_id,
                'empresa_id' => $pago->empresa_id,
                'valor' => $pago->valor,
                'fecha_pago' => $pago->fecha_pago->format('Y-m-d'),
                'motivo' => $pago->motivo,
                'observaciones' => $pago->observaciones,
                'estado' => $pago->estado,
                'usuario' => $pago->prestamo->user->name ?? 'N/A'
            ]);
        }
        
        return view('pago-prestamos.edit', compact('pago', 'empresas'));
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
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'valor' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:pagado,pendiente',
        ]);
        
        $pago = PagoPrestamo::findOrFail($id);
        
        // Verificar que el préstamo exista
        $prestamo = Prestamo::findOrFail($pago->prestamo_id);
        
        // Actualizar el pago
        $pago->update([
            'empresa_id' => $request->empresa_id,
            'valor' => $request->valor,
            'fecha_pago' => $request->fecha_pago,
            'motivo' => $request->motivo,
            'observaciones' => $request->observaciones,
            'estado' => $request->estado,
        ]);
        
        // Si es una solicitud AJAX o proviene de la vista de préstamos, redirigir de vuelta
        if (request()->ajax() || request()->header('referer') && strpos(request()->header('referer'), 'prestamos') !== false) {
            return redirect()->back()
                ->with('error', true)
                ->with('tipo', 'alert-success')
                ->with('mensaje', 'Pago actualizado correctamente');
        }
        
        return redirect()->route('pago-prestamos.index')
            ->with('error', true)
            ->with('tipo', 'alert-success')
            ->with('mensaje', 'Pago actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pago = PagoPrestamo::findOrFail($id);
        $pago->delete();
        
        // Si es una solicitud AJAX o proviene de la vista de préstamos, redirigir de vuelta
        if (request()->ajax() || request()->header('referer') && strpos(request()->header('referer'), 'prestamos') !== false) {
            return redirect()->back()
                ->with('error', true)
                ->with('tipo', 'alert-success')
                ->with('mensaje', 'Pago eliminado correctamente');
        }
        
        return redirect()->route('pago-prestamos.index')
            ->with('error', true)
            ->with('tipo', 'alert-success')
            ->with('mensaje', 'Pago eliminado correctamente');
    }
}
