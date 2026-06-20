<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('recibir-datos', [PagesController::class, 'recibirDatos'])->name('recibir-datos');

// ── API Asistencia Lima Norte ────────────────────────────────────────────────
use App\Http\Controllers\Api\AsistenciaSyncController;

Route::prefix('asistencia')->name('api.asistencia.')->group(function () {
    // Sin autenticación — health check
    Route::get('/ping', [AsistenciaSyncController::class, 'ping'])->name('ping');

    // Con token Bearer
    Route::middleware('api.token')->group(function () {
        Route::post('/sync',        [AsistenciaSyncController::class, 'sync'])->name('sync');
        Route::get('/ultimo-item',  [AsistenciaSyncController::class, 'ultimoItem'])->name('ultimo-item');
    });
});