<?php

use App\Http\Controllers\TeacherProfileController;

Route::prefix('teachers')->name('teachers.')->group(function () {

    Route::get('/', [TeacherProfileController::class, 'index'])->name('index');

    Route::get('/create', [TeacherProfileController::class, 'create'])->name('create');
    Route::post('/store', [TeacherProfileController::class, 'store'])->name('store');

    Route::get('/{teacher}/edit', [TeacherProfileController::class, 'edit'])->name('edit');
    Route::put('/{teacher}/update', [TeacherProfileController::class, 'update'])->name('update');

    Route::delete('/{teacher}/delete', [TeacherProfileController::class, 'destroy'])->name('delete');

    Route::get('/{teacher}/notes', [TeacherProfileController::class, 'notes'])->name('notes');
    Route::put('/{teacher}/notes/save', [TeacherProfileController::class, 'saveNotes'])->name('notes.save');
});
