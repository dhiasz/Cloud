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
    Route::get('/keepcloud/home', [FileController::class, 'index'])->name('dashboard');
    Route::post('/keepcloud/upload', [FileController::class, 'upload'])->name('upload');
    Route::post('/keepcloud/folder/create', [FileController::class, 'createFolder'])->name('folder.create');
    
    
   

        
    Route::get('/keepcloud/files/{folder?}', [FileController::class, 'index'])
    ->where('folder', '.*')
    ->name('files.index');
    
Route::get('/keepcloud/folder', [FileController::class, 'folder'])
    ->middleware('auth')
    ->name('files.folder');

    
    Route::post('/keepcloud/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('/keepcloud/folder/create', [FileController::class, 'createFolder'])->name('folder.create');
    Route::get('/keepcloud/download/{filename}', [FileController::class, 'download'])->name('download');
    Route::get('/keepcloud/preview/{filename}', [FileController::class, 'preview'])->name('preview');
    Route::delete('/keepcloud/delete/{filename}', [FileController::class, 'delete'])->name('file.delete');
    Route::delete('/keepcloud/files/delete/{filename}', [FileController::class, 'delete'])->name('file.delete');
    
    
    // Profile (default Breeze)
        Route::get('/keepcloud/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/keepcloud/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/keepcloud/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
});

// Auth routes Breeze
require __DIR__.'/auth.php';
