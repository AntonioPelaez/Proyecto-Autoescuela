<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TownsController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\TeacherAvailabiltyExceptionsController;

// Todas estas rutas serán /api/auth/...
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

Route::get('/towns', [TownsController::class, 'index']);
Route::get('/towns/{id}', [TownsController::class, 'show']);
Route::post('/towns', [TownsController::class, 'store'])->middleware('auth:sanctum');
Route::put('/towns/{id}', [TownsController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/towns/{id}', [TownsController::class, 'destroy'])->middleware('auth:sanctum');
// Esta ruta será /api/me
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

// Endpoint para obtener la disponibilidad de un profesor
Route::get('/teachers/{teacher}/availability', [TeacherAvailabilityController::class, 'getAvailability']);

// Endpoint para ver mis clases
Route::get('/my-classes', [ClassController::class, 'index'])->middleware('auth:sanctum');

// Endpoint para gestionar excepciones de disponibilidad
Route::get('/teachers/availability-exceptions', [TeacherAvailabiltyExceptionsController::class, 'index'])->middleware('auth:sanctum');
Route::get('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'show'])->middleware('auth:sanctum');
Route::post('/teachers/availability-exceptions', [TeacherAvailabiltyExceptionsController::class, 'store'])->middleware('auth:sanctum');
Route::put('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'destroy'])->middleware('auth:sanctum');
