<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Root route - redirect to tasks
Route::get('/', function () {
    return redirect()->route('tasks.index');
});

Route::get('/welcome', function () {
    return view('welcome');
});

// Task Resource Routes (CRUD)
Route::resource('tasks', TaskController::class);

// Soft Delete & Trash Routes
Route::get('/tasks-trash/trash', [TaskController::class, 'trash'])->name('tasks.trash');
Route::get('/tasks-trash/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
Route::delete('/tasks-trash/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');
