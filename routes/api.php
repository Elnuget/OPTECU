<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\CashHistoryController;
use App\Http\Controllers\Api\HistorialClinicoController;
use App\Http\Controllers\SueldoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrestamoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/pagos/totales', [PagoController::class, 'getTotalesPorMes']);
Route::get('/caja/retiros', [PagoController::class, 'getRetirosCaja']);
Route::get('/egresos', [PagoController::class, 'getEgresosPorMes']);
Route::get('/pedidos', [PedidoController::class, 'getPedidosPorMes']);
Route::get('/caja/historial', [CashHistoryController::class, 'getHistorialCaja']);

// Ruta para obtener último pedido de un cliente
Route::get('/clientes/{cliente}/ultimo-pedido', [PedidoController::class, 'getUltimoPedidoCliente']);

// Ruta para buscar información por RUT/Cédula
Route::get('/pedidos/buscar-rut/{cedula}', [PedidoController::class, 'buscarPorRut']);

// Ruta para buscar pedidos por cualquier campo de datos personales
Route::get('/pedidos/buscar-por/{campo}/{valor}', [PedidoController::class, 'buscarPedidoPorCampo']);

// Ruta para obtener el siguiente número de orden por empresa
Route::get('/pedidos/siguiente-numero-orden/{empresa_id}', [PedidoController::class, 'getSiguienteNumeroOrden']);

// Ruta para generar Excel de pedidos
Route::post('/pedidos/generar-excel', [PedidoController::class, 'generarExcel']);

// Ruta para buscar historiales clínicos por cualquier campo de datos personales
Route::get('/historiales-clinicos/buscar-por/{campo}/{valor}', [HistorialClinicoController::class, 'buscarPorCampo']);

// Ruta para buscar historiales clínicos por nombre completo
Route::get('/historiales-clinicos/buscar-nombre-completo/{nombreCompleto}', [HistorialClinicoController::class, 'buscarPorNombreCompleto']);

// Ruta para buscar historiales clínicos por nombres y apellidos separados
Route::get('/historiales-clinicos/buscar-por-nombre/{nombres}/{apellidos}', [HistorialClinicoController::class, 'buscarPorNombresApellidos']);

Route::get('/sueldos/registros-cobro', [SueldoController::class, 'getRegistrosCobro']);
Route::get('/sueldos/total-registros-cobro', [SueldoController::class, 'getTotalRegistrosCobro']);
Route::post('/sueldos/datos-rol-pagos', [SueldoController::class, 'getDatosRolPagos']);

Route::get('/prestamos/pagos-locales', [PrestamoController::class, 'getPagosLocales']);
Route::post('/prestamos/pagos', [PrestamoController::class, 'storePago']);
Route::get('/prestamos/{prestamo}/resumen-pagos', [PrestamoController::class, 'getResumenPagos']);

Route::post('/inventario/restaurar/{id}', [InventarioController::class, 'restaurar']);
Route::post('/inventario/restar/{id}', [InventarioController::class, 'restar']);