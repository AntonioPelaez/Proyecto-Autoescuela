<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\StudentProfileController;




/*
|--------------------------------------------------------------------------
| Ruta principal de la aplicación
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
| Rutas de autenticación
|--------------------------------------------------------------------------
| Estas rutas son generadas automáticamente por Laravel Breeze para manejar
| el registro, inicio de sesión, recuperación de contraseña, etc.
*/
Route::prefix('auth')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::view('/reset-password', 'auth.reset-password')->name('password.reset');
});
/*
|--------------------------------------------------------------------------
| Rutas CRUD para la tabla "users"
|--------------------------------------------------------------------------
*/
Route::resource('users', UserController::class);
/*
|--------------------------------------------------------------------------
| Rutas CRUD para profesores (TeacherProfile)
|--------------------------------------------------------------------------
*/
Route::prefix('teachers')->name('teachers.')->group(function () {

    // Listado de profesores
    Route::get('/', [TeacherProfileController::class, 'index'])->name('index');

    // Crear profesor
    Route::get('/create', [TeacherProfileController::class, 'create'])->name('create');
    Route::post('/store', [TeacherProfileController::class, 'store'])->name('store');

    // Editar profesor
    Route::get('/{teacher}/edit', [TeacherProfileController::class, 'edit'])->name('edit');
    Route::put('/{teacher}/update', [TeacherProfileController::class, 'update'])->name('update');

    // Eliminar profesor
    Route::delete('/{teacher}/delete', [TeacherProfileController::class, 'destroy'])->name('delete');

    // Notas del profesor
    Route::get('/{teacher}/notes', [TeacherProfileController::class, 'notes'])->name('notes');
    Route::put('/{teacher}/notes/save', [TeacherProfileController::class, 'saveNotes'])->name('notes.save');

    // Asociar alumnos
    Route::get('/{teacher}/students', [TeacherProfileController::class, 'students'])->name('students');
    Route::post('/{teacher}/students/attach', [TeacherProfileController::class, 'attachStudent'])->name('students.attach');
    Route::delete('/{teacher}/students/{student}/detach', [TeacherProfileController::class, 'detachStudent'])->name('students.detach');

});

Route::prefix('students')->name('students.')->group(function () {

    Route::get('/', [StudentProfileController::class, 'index'])->name('index');
    Route::get('/create', [StudentProfileController::class, 'create'])->name('create');
    Route::post('/store', [StudentProfileController::class, 'store'])->name('store');

    Route::get('/{student}/edit', [StudentProfileController::class, 'edit'])->name('edit');
    Route::put('/{student}/update', [StudentProfileController::class, 'update'])->name('update');

    Route::delete('/{student}/delete', [StudentProfileController::class, 'destroy'])->name('delete');

    Route::get('/{student}/notes', [StudentProfileController::class, 'notes'])->name('notes');
    Route::put('/{student}/notes/save', [StudentProfileController::class, 'saveNotes'])->name('notes.save');
});





