<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\StudentProfileController;

/*
|--------------------------------------------------------------------------
| Ruta principal
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});
Route::prefix('auth')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::view('/reset-password', 'auth.reset-password')->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| CRUD Users
|--------------------------------------------------------------------------
*/
Route::resource('users', UserController::class);

/*
|--------------------------------------------------------------------------
| CRUD Profesores
|--------------------------------------------------------------------------
*/
Route::prefix('teachers')->name('teachers.')->group(function () {

    Route::get('/', [TeacherProfileController::class, 'index'])->name('index');

    Route::get('/create', [TeacherProfileController::class, 'create'])->name('create');
    Route::post('/', [TeacherProfileController::class, 'store'])->name('store');

    Route::get('/{teacher}/edit', [TeacherProfileController::class, 'edit'])->name('edit');
    Route::put('/{teacher}', [TeacherProfileController::class, 'update'])->name('update');

    Route::delete('/{teacher}', [TeacherProfileController::class, 'destroy'])->name('delete');

    // NOTAS
    Route::get('/{teacher}/notes', [TeacherProfileController::class, 'notes'])->name('notes');
    Route::put('/{teacher}/notes', [TeacherProfileController::class, 'saveNotes'])->name('notes.save');

    /*
    |--------------------------------------------------------------------------
    | ASIGNACIÓN DE VEHÍCULOS A PROFESORES
    |--------------------------------------------------------------------------
    */
    Route::get('/{teacher}/vehicles', [TeacherProfileController::class, 'vehicles'])->name('vehicles');
    Route::post('/{teacher}/vehicles/assign', [TeacherProfileController::class, 'assignVehicle'])->name('vehicles.assign');
    Route::delete('/{teacher}/vehicles/{vehicle}/remove', [TeacherProfileController::class, 'removeVehicle'])->name('vehicles.remove');
});

/*
|--------------------------------------------------------------------------
| CRUD Alumnos
|--------------------------------------------------------------------------
*/
Route::prefix('students')->name('students.')->group(function () {

    Route::get('/', [StudentProfileController::class, 'index'])->name('index');

    Route::get('/create', [StudentProfileController::class, 'create'])->name('create');
    Route::post('/', [StudentProfileController::class, 'store'])->name('store');

    Route::get('/{student}/edit', [StudentProfileController::class, 'edit'])->name('edit');
    Route::put('/{student}', [StudentProfileController::class, 'update'])->name('update');

    Route::delete('/{student}', [StudentProfileController::class, 'destroy'])->name('delete');

    Route::get('/{student}/notes', [StudentProfileController::class, 'notes'])->name('notes');
    Route::put('/{student}/notes', [StudentProfileController::class, 'saveNotes'])->name('notes.save');
});




/*
|--------------------------------------------------------------------------
| CRUD Vehículos
|--------------------------------------------------------------------------
*/
Route::prefix('vehicles')->name('vehicles.')->group(function () {

    Route::get('/', [VehicleController::class, 'index'])->name('index');

    Route::get('/create', [VehicleController::class, 'create'])->name('create');
    Route::post('/', [VehicleController::class, 'store'])->name('store');

    Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
    Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');

    Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('delete');
});
