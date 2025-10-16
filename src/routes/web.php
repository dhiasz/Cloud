<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Halaman welcome
Route::get('/', function () {
    return view('welcome');
});

// Routes hanya bisa diakses user yang login
Route::middleware(['auth'])->group(function () {
    // Dashboard → menampilkan files/index.blade.php
    Route::get('/dashboard', [FileController::class, 'index'])->name('dashboard');
    Route::post('/upload', [FileController::class, 'upload'])->name('upload');
    Route::get('/download/{filename}', [FileController::class, 'download'])->name('download');
    Route::post('/folder/create', [FileController::class, 'createFolder'])->name('folder.create');
    Route::get('/preview/{filename}', [FileController::class, 'preview'])->name('preview');
    Route::delete('/delete/{filename}', [FileController::class, 'delete'])->name('file.delete');
    Route::get('/files/folder', [FileController::class, 'folder'])->name('files.folder');

    // Profile (default Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/files/{folder?}', [FileController::class, 'index'])
        ->where('folder', '.*')
        ->name('files.index');

    Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::get('/files/download/{filename}', [FileController::class, 'download'])->name('files.download');

});

// Auth routes Breeze
require __DIR__.'/auth.php';
