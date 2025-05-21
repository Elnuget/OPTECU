<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\CashHistoryController;

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

// Ruta para buscar pedidos por cualquier campo de datos personales
Route::get('/pedidos/buscar-por/{campo}/{valor}', [PedidoController::class, 'buscarPedidoPorCampo']);