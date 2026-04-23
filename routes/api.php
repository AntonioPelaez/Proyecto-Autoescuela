<?php
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TownsController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Todas estas rutas serán /api/auth/...
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

Route::get('/towns', [TownsController::class, 'index'])->middleware('auth:sanctum');
Route::get('/towns/{id}', [TownsController::class, 'show'])->middleware('auth:sanctum');
Route::post('/towns', [TownsController::class, 'store'])->middleware('auth:sanctum');
Route::put('/towns/{id}', [TownsController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/towns/{id}', [TownsController::class, 'destroy'])->middleware('auth:sanctum');
