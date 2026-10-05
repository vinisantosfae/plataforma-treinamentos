<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TreinamentoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard/colaborador', [DashboardController::class, 'collaborator']);
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('admin');

    Route::get('/treinamentos', [TreinamentoController::class, 'index']);
    Route::get('/treinamentos/{id}', [TreinamentoController::class, 'show']);
    Route::post('/treinamentos', [TreinamentoController::class, 'store'])->middleware('admin');
    Route::patch('/treinamentos/{id}', [TreinamentoController::class, 'update'])->middleware('admin');
    Route::delete('/treinamentos/{id}', [TreinamentoController::class, 'destroy'])->middleware('admin');
});
