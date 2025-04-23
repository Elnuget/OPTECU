<?php
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\mediosdepagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\PagonuevosController; 
use App\Http\Controllers\HistorialClinicoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashHistoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EgresoController;

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
    Route::patch('Configuracion/Usuarios/{id}/toggle-admin', [UsuariosController::class, 'toggleAdmin'])->name('configuracion.usuarios.toggleAdmin');

    // Admin dashboard
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    
    // Only keep admin-specific inventory routes here
    Route::delete('Inventario/eliminar/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');

    Route::get('/admin/puntuaciones', [AdminController::class, 'puntuacionesUsuarios'])
        ->name('admin.puntuaciones');
});

// Keep these routes accessible to all authenticated users
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
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
    Route::post('Pedidos', [PedidosController::class, 'store'])->name('pedidos.store');
    Route::get('Pedidos/{id}', [PedidosController::class, 'show'])->name('pedidos.show');
    Route::get('Pedidos/{id}/editar', [PedidosController::class, 'edit'])->name('pedidos.edit');
    Route::put('Pedidos/{id}', [PedidosController::class, 'update'])->name('pedidos.update');
    Route::delete('Pedidos/{id}', [PedidosController::class, 'destroy'])->name('pedidos.destroy');
    Route::patch('/pedidos/{id}/approve', [PedidosController::class, 'approve'])->name('pedidos.approve');
    Route::put('pedidos/{id}/calificar', [PedidosController::class, 'calificar'])
        ->name('pedidos.calificar');
    Route::post('/pedidos/{id}/enviar-encuesta', [PedidosController::class, 'enviarEncuesta'])
        ->name('pedidos.enviar-encuesta')
        ->middleware('web');
    
    Route::post('/pedidos/{id}/actualizar-estado-encuesta/{estado?}', [PedidosController::class, 'actualizarEstadoEncuesta'])
        ->name('pedidos.actualizar-estado-encuesta')
        ->middleware('web');

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
        
        // Rutas con parámetros después
        Route::get('/{historial}/edit', [HistorialClinicoController::class, 'edit'])
            ->name('historiales_clinicos.edit');
        
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

    Route::resource('caja', 'App\Http\Controllers\CajaController');

    Route::resource('cash-histories', CashHistoryController::class);

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

    // Mover esta ruta específica antes del resource
    Route::get('/egresos/finanzas', [EgresoController::class, 'finanzas'])
        ->name('egresos.finanzas')
        ->middleware(['auth']); // Mantener middleware auth si es necesario dentro del grupo

    Route::resource('egresos', EgresoController::class);
});

// Rutas públicas para calificación
Route::get('/pedidos/{id}/calificar/{token}', [PedidosController::class, 'calificarPublico'])
    ->name('pedidos.calificar-publico');
Route::post('/pedidos/{id}/calificar/{token}', [PedidosController::class, 'guardarCalificacionPublica'])
    ->name('pedidos.guardar-calificacion-publica');