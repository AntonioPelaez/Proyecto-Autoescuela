<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TownsController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeacherAvailabiltyExceptionsController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\ClassSessionQueryController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\StudentProfileController; 
use App\Http\Controllers\TeacherClassController;

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

/**
 * Usuarios CRUD
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

/**
 * CRUD DE PROFESORES
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/teacher', [TeacherProfileController::class, 'index']);
    Route::get('/teacher/{id}', [TeacherProfileController::class, 'show']);
    Route::post('/teacher', [TeacherProfileController::class, 'store']);
    Route::put('/teacher/{id}', [TeacherProfileController::class, 'update']);
    Route::delete('/teacher/{id}', [TeacherProfileController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Consultar reservas de un profesor a uno o varios alumnos (clases reservadas)
|--------------------------------------------------------------------------
*/
Route::get('/teachers/reservas', [TeacherClassController::class, 'reservasProfesor'])->middleware('auth:sanctum');
/**
 * CRUD DE VEHÍCULOS
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
});
/**
 * CRUD DE ESTUDIANTES
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/students', [StudentProfileController::class, 'index']);
    Route::get('/students/{id}', [StudentProfileController::class, 'show']);
    Route::post('/students', [StudentProfileController::class, 'store']);
    Route::put('/students/{id}', [StudentProfileController::class, 'update']);
    Route::delete('/students/{id}', [StudentProfileController::class, 'destroy']);
});
/**
 * Ruta que permite a un alumno autenticado consultar sus clases reservadas, con detalles del profesor y pueblo.
 */
Route::get('my-classes', [ClassController::class, 'index'])->middleware('auth:sanctum');
/*
|--------------------------------------------------------------------------
| Excepciones de disponibilidad
|--------------------------------------------------------------------------
*/
Route::get('/teachers/availability-exceptions', [TeacherAvailabiltyExceptionsController::class, 'index'])->middleware('auth:sanctum');
Route::get('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'show'])->middleware('auth:sanctum');
Route::put('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'update'])->middleware('auth:sanctum');
Route::post('/teachers/availability-exceptions', [TeacherAvailabiltyExceptionsController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/teachers/availability-exceptions/{id}', [TeacherAvailabiltyExceptionsController::class, 'destroy'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Horas disponibles (profesor individual)
|--------------------------------------------------------------------------
*/
Route::get('/availability-hours', [ClassSessionController::class, 'hours'])
    ->name('api.availability-hours')->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Slots disponibles (pueblo → profesores → slots)
|--------------------------------------------------------------------------
*/
Route::get('/availability-slots', [ClassSessionController::class, 'availabilitySlots'])
    ->name('api.availability-slots')->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Consultar clases del día
|--------------------------------------------------------------------------
*/
Route::get('/class-sessions/day', [ClassSessionController::class, 'daySessions'])
    ->name('api.day-sessions')->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Crear reserva
|--------------------------------------------------------------------------
*/
Route::post('/class-sessions', [ClassSessionController::class, 'store'])
    ->name('api.class-sessions.store')->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Cancelar reserva
|--------------------------------------------------------------------------
*/
Route::post('/class-sessions/cancel', [ClassSessionController::class, 'cancel'])
    ->name('api.class-sessions.cancel')->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Confirmar reserva
|--------------------------------------------------------------------------
*/
Route::post('/class-sessions/confirm', [ClassSessionController::class, 'confirm'])
    ->name('api.class-sessions.confirm')->middleware('auth:sanctum');

// Endpoint para que el admin pueda consultar todas las clases con filtros
Route::get('/admin/classes', [AdminClassController::class, 'index'])->middleware('auth:sanctum');

