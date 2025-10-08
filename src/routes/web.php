<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard / file list
    Route::get('/dashboard', [FileController::class, 'index'])->name('dashboard');
    Route::post('/upload', [FileController::class, 'upload'])->name('upload');
    Route::get('/download/{filename}', [FileController::class, 'download'])->name('download');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

