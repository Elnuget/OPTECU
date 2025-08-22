<?php
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\mediosdepagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\PagonuevosController; 
use App\Http\Controllers\HistorialClinicoController;
use App\Http\Controllers\DeclaranteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashHistoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\PagoPrestamoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\TelemarketingController;
use App\Http\Controllers\SueldoController;
use App\Http\Controllers\DetalleSueldoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Wrap admin-only routes in admin middleware group
Route::middleware(['auth:sanctum', 'verified', 'admin'])->group(function () {
    // Configuracion
    Route::get('Configuracion/Usuarios', [UsuariosController::class, 'index'])->name('configuracion.usuarios.index');
    Route::get('Configuracion/Usuarios/Crear', [UsuariosController::class, 'create'])->name('configuracion.usuarios.create');
    Route::post('Configuracion/Usuarios', [UsuariosController::class, 'store'])->name('configuracion.usuarios.store');
    Route::get('Configuracion/Usuarios/{id}', [UsuariosController::class, 'show'])->name('configuracion.usuarios.editar');
    Route::put('Configuracion/Usuarios/{usuario}', [UsuariosController::class, 'update'])->name('configuracion.usuarios.update');
    Route::delete('Configuracion/Usuarios/{id}', [UsuariosController::class, 'destroy'])->name('configuracion.usuarios.destroy');
    Route::patch('Configuracion/Usuarios/{id}/toggle-admin', [UsuariosController::class, 'toggleAdmin'])->name('configuracion.usuarios.toggleAdmin');

    // Admin dashboard
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    
    // Only keep admin-specific inventory routes here
    Route::delete('Inventario/eliminar/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');

    Route::get('/admin/puntuaciones', [AdminController::class, 'puntuacionesUsuarios'])
        ->name('admin.puntuaciones');

    // Asistencias - Solo para administradores
    Route::get('asistencias/reporte', [AsistenciaController::class, 'reporte'])->name('asistencias.reporte');
    Route::get('asistencias/scan', [AsistenciaController::class, 'scan'])->name('asistencias.scan');
    Route::post('asistencias/procesar-qr', [AsistenciaController::class, 'procesarQr'])->name('asistencias.procesar-qr');
    Route::post('asistencias/marcar-entrada', [AsistenciaController::class, 'marcarEntrada'])->name('asistencias.marcar-entrada');
    Route::post('asistencias/marcar-salida', [AsistenciaController::class, 'marcarSalida'])->name('asistencias.marcar-salida');
    Route::resource('asistencias', AsistenciaController::class);

    // Rutas financieras - Solo para administradores
    Route::get('/egresos/finanzas', [EgresoController::class, 'finanzas'])
        ->name('egresos.finanzas');
    Route::get('/egresos/datos-financieros', [EgresoController::class, 'getDatosFinancieros'])
        ->name('egresos.datos-financieros');
    Route::get('/egresos/graficos-financieros', [EgresoController::class, 'getGraficosFinancieros'])
        ->name('egresos.graficos-financieros');
    Route::get('/egresos/movimientos-recientes', [EgresoController::class, 'getMovimientosRecientes'])
        ->name('egresos.movimientos-recientes');
    Route::get('/egresos/pedidos-usuario', [EgresoController::class, 'getPedidosPorUsuario'])
        ->name('egresos.pedidos-usuario');
    Route::get('/egresos/ultimo-sueldo-usuario', [EgresoController::class, 'getUltimoSueldoUsuario'])
        ->name('egresos.ultimo-sueldo-usuario');

    // Horarios - Solo para administradores
    Route::get('horarios/activos', [HorarioController::class, 'activos'])->name('horarios.activos');
    Route::get('horarios/empresa/{empresaId}', [HorarioController::class, 'getByEmpresa'])->name('horarios.por-empresa');
    Route::resource('horarios', HorarioController::class);
});

// Keep these routes accessible to all authenticated users
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    
    // IMPORTANTE: Ruta directa para MI CÓDIGO QR - más simple y sin conflictos
    Route::get('mi-qr', [AsistenciaController::class, 'miQr'])->name('mi-qr');
    
    // También mantener la ruta original por compatibilidad
    Route::get('asistencias/mi-qr', [AsistenciaController::class, 'miQr'])->name('asistencias.mi-qr');
    
    // Medios de Pago
    Route::get('Configuración/MediosDePago', [mediosdepagoController::class, 'index'])->name('configuracion.mediosdepago.index');
    Route::get('Configuración/MediosDePago/Crear', [mediosdepagoController::class, 'create'])->name('configuracion.mediosdepago.create'); 
    Route::get('Configuración/MediosDePago/{id}', [mediosdepagoController::class, 'editar'])->name('configuracion.mediosdepago.editar');
    Route::delete('Configuración/MediosDePago/eliminar/{id}', [mediosdepagoController::class, 'destroy'])->name('configuracion.mediosdepago.destroy');
    Route::get('Configuración/MediosDePago/{id}/ver', [mediosdepagoController::class, 'show'])->name('configuracion.mediosdepago.show');
    Route::put('Configuración/MediosDePago/{id}', [mediosdepagoController::class, 'update'])->name('configuracion.mediosdepago.update');
    Route::post('Configuración/MediosDePago', [mediosdepagoController::class, 'store'])->name('configuracion.mediosdepago.store');

    // Inventory routes that all users can access
    Route::put('/inventario/{id}', [InventarioController::class, 'update'])
        ->name('inventario.update');
    
    Route::get('/Inventario/Actualizar', [InventarioController::class, 'actualizar'])
        ->name('inventario.actualizar');
    
    Route::post('inventario/crear-nuevos-registros', [InventarioController::class, 'crearNuevosRegistros'])
        ->name('inventario.crear-nuevos-registros')
        ->middleware('web');

    Route::post('/inventario/actualizar-fechas', [InventarioController::class, 'actualizarFechas'])
        ->name('inventario.actualizar-fechas');

    Route::get('Inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('Inventario/Crear', [InventarioController::class, 'create'])
        ->name('inventario.create');
    Route::post('Inventario', [InventarioController::class, 'store'])->name('inventario.store');
    Route::get('Inventario/{id}', [InventarioController::class, 'edit'])->name('inventario.edit');
    Route::get('Inventario/{id}/ver', [InventarioController::class, 'show'])->name('inventario.show');
    Route::get('/inventario/lugares/{lugar}', [InventarioController::class, 'getNumerosLugar'])->name('inventario.getNumerosLugar');

    // Venta nuevo
    Route::get('Venta', [InventarioController::class, 'index'])->name('venta.index');
    Route::get('Venta/Crear', [InventarioController::class, 'create'])->name('venta.create'); 
    Route::get('Venta/{id}', [InventarioController::class, 'edit'])->name('venta.edit');
    Route::delete('Venta/eliminar/{id}', [InventarioController::class, 'destroy'])->name('invenventatarios.destroy');
    Route::get('Venta/{id}/ver', [InventarioController::class, 'show'])->name('venta.show');
    Route::put('Venta/{articulo}', [InventarioController::class, 'update'])->name('venta.update');
    Route::post('Venta', [InventarioController::class, 'store'])->name('venta.store');

    // Pedidos
    Route::get('Pedidos', [PedidosController::class, 'index'])->name('pedidos.index');
    Route::get('Pedidos/Crear', [PedidosController::class, 'create'])->name('pedidos.create');
    // API para obtener próximo número de orden
    Route::get('api/pedidos/next-order-number', [PedidosController::class, 'getNextOrderNumber'])->name('api.pedidos.next-order-number');
    Route::get('Pedidos/Imprimir', [PedidosController::class, 'print'])->name('pedidos.print');
    Route::post('Pedidos/Imprimir', [PedidosController::class, 'print'])->name('pedidos.print.post');
    Route::post('Pedidos/Excel', [PedidosController::class, 'printExcel'])->name('pedidos.print.excel');
    Route::post('Pedidos/Etiquetas', [PedidosController::class, 'printExcel'])->name('pedidos.print.etiquetas');
    Route::post('Pedidos/DescargarExcel', [PedidosController::class, 'downloadExcel'])->name('pedidos.download.excel');
    Route::post('Pedidos/Cristaleria', [PedidosController::class, 'printCristaleria'])->name('pedidos.print.cristaleria');
    Route::post('Pedidos/ExportarCristalariaExcel', [PedidosController::class, 'exportCristalariaExcel'])->name('pedidos.export.cristaleria');
    Route::post('Pedidos', [PedidosController::class, 'store'])->name('pedidos.store');
    Route::get('Pedidos/{id}', [PedidosController::class, 'show'])->name('pedidos.show');
    Route::get('Pedidos/{id}/editar', [PedidosController::class, 'edit'])->name('pedidos.edit');
    Route::put('Pedidos/{id}', [PedidosController::class, 'update'])->name('pedidos.update');
    Route::delete('Pedidos/{id}', [PedidosController::class, 'destroy'])->name('pedidos.destroy');
    Route::patch('/pedidos/{id}/update-state/{state}', [PedidosController::class, 'updateState'])->name('pedidos.update-state');
    Route::post('/pedidos/bulk-update-state', [PedidosController::class, 'bulkUpdateState'])->name('pedidos.bulk-update-state');
    Route::patch('/pedidos/{id}/approve', [PedidosController::class, 'approve'])->name('pedidos.approve');
    Route::put('pedidos/{id}/calificar', [PedidosController::class, 'calificar'])
        ->name('pedidos.calificar');
    Route::post('/pedidos/crear-factura', [PedidosController::class, 'crearFactura'])->name('pedidos.crear-factura');
    Route::post('/pedidos/{id}/enviar-encuesta', [PedidosController::class, 'enviarEncuesta'])
        ->name('pedidos.enviar-encuesta')
        ->middleware('web');
    
    Route::post('/pedidos/{id}/actualizar-estado-encuesta/{estado?}', [PedidosController::class, 'actualizarEstadoEncuesta'])
        ->name('pedidos.actualizar-estado-encuesta')
        ->middleware('web');
    
    // Rutas para reclamos
    Route::post('/pedidos/{id}/agregar-reclamo', [PedidosController::class, 'agregarReclamo'])->name('pedidos.agregar-reclamo');
    Route::delete('/pedidos/{id}/quitar-reclamo', [PedidosController::class, 'quitarReclamo'])->name('pedidos.quitar-reclamo');

    // Rutas para marcar/desmarcar urgente
    Route::post('/pedidos/{id}/marcar-urgente', [PedidosController::class, 'marcarUrgente'])->name('pedidos.marcar-urgente');
    Route::delete('/pedidos/{id}/desmarcar-urgente', [PedidosController::class, 'desmarcarUrgente'])->name('pedidos.desmarcar-urgente');

    // Rutas para Declarantes
    Route::get('/pedidos/declarantes/listar', [DeclaranteController::class, 'listar'])->name('pedidos.declarantes.listar');
    Route::post('/pedidos/declarantes', [DeclaranteController::class, 'store'])->name('pedidos.declarantes.store');
    Route::put('/pedidos/declarantes/{id}', [DeclaranteController::class, 'update'])->name('pedidos.declarantes.update');
    Route::delete('/pedidos/declarantes/{id}', [DeclaranteController::class, 'destroy'])->name('pedidos.declarantes.destroy');
    
    // Rutas para el recurso completo de declarantes (estas son las rutas estándar)
    Route::resource('declarantes', DeclaranteController::class);
    Route::get('/declarantes/{id}/facturas', [DeclaranteController::class, 'facturas'])->name('declarantes.facturas');

    // Historiales Clinicos
    Route::prefix('historiales_clinicos')->group(function () {
        // Rutas sin parámetros primero
        Route::get('/', [HistorialClinicoController::class, 'index'])
            ->name('historiales_clinicos.index');
        
        Route::get('/create', [HistorialClinicoController::class, 'create'])
            ->name('historiales_clinicos.create');
        
        Route::post('/', [HistorialClinicoController::class, 'store'])
            ->name('historiales_clinicos.store');
        
        // Ruta de cumpleaños (debe ir antes de las rutas con parámetros)
        Route::get('/cumpleanos', [HistorialClinicoController::class, 'cumpleanos'])
            ->name('historiales_clinicos.cumpleanos');
        
        // Ruta para obtener historiales relacionados por nombre y apellido
        Route::get('/relacionados', [HistorialClinicoController::class, 'historialesRelacionados'])
            ->name('historiales_clinicos.relacionados');
        
        // Ruta para impresión múltiple de recetas
        Route::get('/multipleprint', [HistorialClinicoController::class, 'multiplePrint'])
            ->name('historiales_clinicos.multipleprint');
        
        // Rutas con parámetros después
        Route::get('/{historial}/edit', [HistorialClinicoController::class, 'edit'])
            ->name('historiales_clinicos.edit');
        
        Route::get('/{historial}/print', [HistorialClinicoController::class, 'print'])
            ->name('historiales_clinicos.print');
        
        Route::put('/{historial}', [HistorialClinicoController::class, 'update'])
            ->name('historiales_clinicos.update');
        
        Route::get('/{historial}', [HistorialClinicoController::class, 'show'])
            ->name('historiales_clinicos.show');
        
        Route::delete('/{historial}', [HistorialClinicoController::class, 'destroy'])
            ->name('historiales_clinicos.destroy');
        
        Route::get('/{historial}/whatsapp', [HistorialClinicoController::class, 'enviarWhatsapp'])
            ->name('historiales_clinicos.whatsapp');

        Route::get('/lista-cumpleanos', [HistorialClinicoController::class, 'listaCumpleanos'])
            ->name('historiales_clinicos.lista_cumpleanos');

        Route::get('/proximas-consultas', [HistorialClinicoController::class, 'proximasConsultas'])
            ->name('historiales_clinicos.proximas_consultas');

        Route::post('/{id}/enviar-mensaje', [HistorialClinicoController::class, 'enviarMensaje'])
            ->name('historiales_clinicos.enviar-mensaje');
    });

    // Pagos
    Route::get('Pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('Pagos/Crear', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('Pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('Pagos/{id}', [PagoController::class, 'show'])->name('pagos.show');
    Route::get('Pagos/{id}/editar', [PagoController::class, 'edit'])->name('pagos.edit');
    Route::put('Pagos/{id}', [PagoController::class, 'update'])->name('pagos.update');
    Route::delete('Pagos/{id}', [PagoController::class, 'destroy'])->name('pagos.destroy');
    Route::post('/pagos/{id}/update-tc', [PagoController::class, 'updateTC'])->name('pagos.updateTC');

    Route::resource('caja', 'App\Http\Controllers\CajaController');

    Route::resource('cash-histories', CashHistoryController::class);
    
    // Ruta para verificar el estado de la caja (para administradores)
    Route::get('/cash-histories-check-status', [CashHistoryController::class, 'checkStatus'])
        ->name('cash-histories.checkStatus')
        ->middleware('admin');

    Route::get('/generar-qr', function () {
        return view('inventario.generarQR');
    })->name('generarQR');

    Route::get('/leer-qr', function () {
        return view('inventario.leerQR');
    })->name('leerQR');

    Route::post('/show-closing-card', [CashHistoryController::class, 'showClosingCard'])->name('show-closing-card');
    Route::get('/cancel-closing-card', [CashHistoryController::class, 'cancelClosingCard'])->name('cancel-closing-card');

    Route::get('/pedidos/inventario-historial', [PedidosController::class, 'inventarioHistorial'])
        ->name('pedidos.inventario-historial');
    // Asegúrate de que esta ruta esté antes de otras rutas que puedan interferir
    Route::post('/inventario/{id}/update-inline', [InventarioController::class, 'updateInline'])
        ->name('inventario.update-inline');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('empresas', EmpresaController::class);

    // Nuevas rutas para mensajes
    Route::prefix('mensajes')->group(function () {
        Route::get('/cumpleanos', [HistorialClinicoController::class, 'cumpleanos'])
            ->name('mensajes.cumpleanos');
        Route::get('/recordatorios', [HistorialClinicoController::class, 'recordatoriosConsulta'])
            ->name('mensajes.recordatorios');
    });
    
    // Rutas para mensajes predeterminados
    Route::post('/configuraciones/mensajes-predeterminados', [App\Http\Controllers\ConfiguracionController::class, 'guardarMensajePredeterminado'])->name('configuraciones.mensajes-predeterminados');
    Route::get('/configuraciones/mensajes-predeterminados/{tipo}', [TelemarketingController::class, 'obtenerMensajePredeterminado'])->name('configuraciones.mensajes-predeterminados.tipo');

    Route::resource('prestamos', PrestamoController::class);
    Route::resource('pago-prestamos', PagoPrestamoController::class);

    // Rutas para recetas
    Route::resource('recetas', \App\Http\Controllers\RecetaController::class);
    Route::get('/recetas/create/{historialId}', [\App\Http\Controllers\RecetaController::class, 'create'])->name('recetas.create.from.historial');

    // Rutas para Telemarketing
    Route::prefix('telemarketing')->group(function () {
        Route::get('/', [TelemarketingController::class, 'index'])
            ->name('telemarketing.index');
        
        Route::post('/{clienteId}/enviar-mensaje', [TelemarketingController::class, 'enviarMensaje'])
            ->name('telemarketing.enviar-mensaje');
        
        Route::get('/{clienteId}/historial', [TelemarketingController::class, 'obtenerHistorial'])
            ->name('telemarketing.historial');
            
        // Ruta para obtener mensaje predeterminado de telemarketing
        Route::get('/configuraciones/mensaje-predeterminado', [TelemarketingController::class, 'obtenerMensajePredeterminado'])
            ->name('telemarketing.mensaje-predeterminado');
    });
    
});

// Rutas públicas para calificación
Route::get('/pedidos/{id}/calificar/{token}', [PedidosController::class, 'calificarPublico'])
    ->name('pedidos.calificar-publico')
    ->middleware('web');
Route::post('/pedidos/{id}/calificar/{token}', [PedidosController::class, 'guardarCalificacionPublica'])
    ->name('pedidos.guardar-calificacion-publica');

    // Rutas para Facturas
    Route::get('facturas', [App\Http\Controllers\FacturaController::class, 'index'])->name('facturas.index');
    Route::get('facturas/create', [App\Http\Controllers\FacturaController::class, 'create'])->name('facturas.create');
    Route::get('facturas/listar', [App\Http\Controllers\FacturaController::class, 'listar'])->name('facturas.listar');
    Route::post('facturas', [App\Http\Controllers\FacturaController::class, 'store'])->name('facturas.store');
    Route::get('facturas/{id}', [App\Http\Controllers\FacturaController::class, 'show'])->name('facturas.show');
    Route::put('facturas/{id}', [App\Http\Controllers\FacturaController::class, 'update'])->name('facturas.update');
    Route::delete('facturas/{id}', [App\Http\Controllers\FacturaController::class, 'destroy'])->name('facturas.destroy');
    
    // Rutas para el controlador de Sueldos
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Ruta para imprimir rol de pago (DEBE ir antes del resource)
    Route::get('sueldos/imprimir-rol-pago', [SueldoController::class, 'imprimirRolPago'])
        ->name('sueldos.imprimir-rol-pago');
    
    Route::resource('sueldos', SueldoController::class);
    
    // Ruta de Egresos accesible para todos los usuarios autenticados
    Route::resource('egresos', EgresoController::class);
    
    // Rutas para Detalles de Sueldo
    Route::resource('detalles-sueldo', DetalleSueldoController::class)->names([
        'create' => 'detalles-sueldo.create',
        'store' => 'detalles-sueldo.store',
        'show' => 'detalles-sueldo.show',
        'edit' => 'detalles-sueldo.edit',
        'update' => 'detalles-sueldo.update',
        'destroy' => 'detalles-sueldo.destroy',
    ]);
});