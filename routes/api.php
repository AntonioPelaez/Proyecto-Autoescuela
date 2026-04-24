<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TownsController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\TeacherAvailabiltyExceptionsController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\ClassSessionQueryController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\AdminClassController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (/api/auth/...)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Rutas de Pueblos
|--------------------------------------------------------------------------
*/
Route::get('/towns', [TownsController::class, 'index']);
Route::get('/towns/{id}', [TownsController::class, 'show']);
Route::post('/towns', [TownsController::class, 'store'])->middleware('auth:sanctum');
Route::put('/towns/{id}', [TownsController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/towns/{id}', [TownsController::class, 'destroy'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Usuario autenticado (/api/me)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

/*
|--------------------------------------------------------------------------
| Disponibilidad de profesores
|--------------------------------------------------------------------------
*/

// Endpoint para ver mis clases
Route::get('/my-classes', [ClassController::class, 'index'])->middleware('auth:sanctum');

// Endpoint para gestionar excepciones de disponibilidad

Route::get('/teachers/availability-exceptions', [TeacherAvailabiltyExceptionsController::class, 'index'])->middleware('auth:sanctum');
Route::get('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'show'])->middleware('auth:sanctum');
Route::put('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'update'])->middleware('auth:sanctum');
Route::post('/teachers/availability-exceptions', [TeacherAvailabiltyExceptionsController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'destroy'])->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Horas disponibles para reservar
|--------------------------------------------------------------------------
*/
Route::get('/availability-slots', [ClassSessionController::class, 'hours']);



/*
| Consultar clases del día (confirmadas + pendientes)
|--------------------------------------------------------------------------
*/
Route::get('/class-sessions/day', [ClassSessionController::class, 'daySessions']);

/*
|--------------------------------------------------------------------------
| Crear reserva (pendiente)
|--------------------------------------------------------------------------
*/
Route::post('/class-sessions', [ClassSessionController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Cancelar reserva (marca status = cancelled)
|--------------------------------------------------------------------------
*/
Route::post('/class-sessions/cancel', [ClassSessionController::class, 'cancel']);

/*
|--------------------------------------------------------------------------
| Confirmar reserva (pending → confirmed)
|--------------------------------------------------------------------------
*/
Route::post('/class-sessions/confirm', [ClassSessionController::class, 'confirm']);

// Endpoint para que el admin pueda consultar todas las clases con filtros
Route::get('/admin/classes', [AdminClassController::class, 'index'])->middleware('auth:sanctum');

