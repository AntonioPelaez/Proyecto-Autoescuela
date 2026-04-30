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
Route::post('/towns/{town}/toggle', [TownsController::class, 'toggleTown'])->middleware('auth:sanctum');

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
Route::middleware('auth:sanctum')->prefix('teachers')->group(function () {
    Route::get('/', [TeacherProfileController::class, 'index']);
    Route::get('/{id}', [TeacherProfileController::class, 'show']);
    Route::put('/{teacher}', [TeacherProfileController::class, 'update']);
    Route::delete('/{teacher}', [TeacherProfileController::class, 'destroy']);
    Route::get('/{teacher}', [TeacherProfileController::class, 'show']);
    Route::post('/{teacher}/toggle', [TeacherProfileController::class, 'toggle']);
    Route::post('/', [TeacherProfileController::class, 'store']);

    // Notas del profesor
    Route::get('/{id}/notes', [TeacherProfileController::class, 'notes']);
    Route::put('/{id}/notes', [TeacherProfileController::class, 'saveNotes']);

    // Vehículos asignados al profesor
    Route::get('/{id}/vehicles', [TeacherProfileController::class, 'vehicles']);
    Route::post('/{id}/vehicles/assign', [TeacherProfileController::class, 'assignVehicle']);
    Route::delete('/{id}/vehicles/{vehicle}/remove', [TeacherProfileController::class, 'removeVehicle']);
});

/*
|--------------------------------------------------------------------------
| Consultar reservas de un profesor a uno o varios alumnos (clases reservadas)
|--------------------------------------------------------------------------
*/
Route::get('/teachers/reservas', [TeacherClassController::class, 'reservasProfesor'])->middleware('auth:sanctum');
/**
 * CRUD DE VEHÍCULOS (API)
 */
Route::middleware('auth:sanctum')->prefix('vehicles')->group(function () {
    Route::get('/', [VehicleController::class, 'index']);
    Route::get('/{id}', [VehicleController::class, 'show']);
    Route::put('/{vehicle}', [VehicleController::class, 'update']);
    Route::delete('/{vehicle}', [VehicleController::class, 'destroy']);
    Route::post('/', [VehicleController::class, 'store']);
});

/** 
 * CRUD DE ESTUDIANTES (API)
 */
Route::middleware('auth:sanctum')->prefix('students')->group(function () {
    Route::get('/', [StudentProfileController::class, 'index']);
    Route::get('/{id}', [StudentProfileController::class, 'show']);
    Route::post('/', [StudentProfileController::class, 'store']);
    Route::put('/{id}', [StudentProfileController::class, 'update']);
    Route::delete('/{id}', [StudentProfileController::class, 'destroy']);

    // Notas del alumno
    Route::get('/{id}/notes', [StudentProfileController::class, 'notes']);
    Route::put('/{id}/notes', [StudentProfileController::class, 'saveNotes']);
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
Route::middleware('auth:sanctum')->get('/availability-slots', [ClassSessionController::class, 'availabilitySlots']);
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

