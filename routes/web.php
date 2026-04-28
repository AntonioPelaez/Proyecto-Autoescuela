<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassSessionController;

/*
|--------------------------------------------------------------------------
| Ruta principal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});
