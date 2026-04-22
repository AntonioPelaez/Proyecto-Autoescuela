<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;




/*
|--------------------------------------------------------------------------
| Ruta principal de la aplicación
|--------------------------------------------------------------------------
| Cuando el usuario entra a la raíz del proyecto ("/"), se muestra la vista
| "welcome.blade.php". Esta es la pantalla inicial por defecto de Laravel.
*/
Route::get('/', function () {
    return view('welcome');
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
| Route::resource genera automáticamente todas las rutas necesarias para un
| CRUD completo:
|
| GET      /users            -> index   (listar usuarios)
| GET      /users/create     -> create  (formulario de creación)
| POST     /users            -> store   (guardar nuevo usuario)
| GET      /users/{id}/edit  -> edit    (formulario de edición)
| PUT      /users/{id}       -> update  (actualizar usuario)
| DELETE   /users/{id}       -> destroy (eliminar usuario)
|
| Estas rutas están conectadas al UserController.
*/
Route::resource('users', UserController::class);





