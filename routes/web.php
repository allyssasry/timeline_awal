<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProgressUpdateController;
// routes/web.php
use App\Http\Controllers\DashboardController;




Route::get('/', function () {
    return view('landing');
});


Route::get('/register', [AuthController::class, 'registerForm'])
    ->name('Auth.register');    // nama rute huruf kecil
Route::post('/register',[AuthController::class,'register']);
Route::get('/login',[AuthController::class,'loginForm'])->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::get('/logout',[AuthController::class,'logout']);


Route::middleware('auth')->group(function () {
    // Dashboard DIG
    Route::get('/dig/dashboard', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

    // Tambah progress ke project yang sudah ada
    Route::post('/projects/{project}/progresses', [ProgressController::class, 'store'])
        ->name('projects.progresses.store');

    // Update harian progress (percent + note + date)
    Route::post('/progresses/{progress}/updates', [ProgressUpdateController::class, 'store'])
        ->name('progresses.updates.store');
});

// Project & Progress
Route::resource('projects', ProjectController::class);
Route::post('/projects/{id}/progress',[ProgressController::class,'store'])->name('progress.store');
Route::get('/progress/{progress}/edit',[ProgressController::class,'edit'])->name('progress.edit');
Route::put('/progress/{progress}',[ProgressController::class,'update'])->name('progress.update');

Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::middleware(['auth'])->group(function () {
    // Tambah progress ke project
    Route::post('/projects/{project}/progresses', [ProgressController::class, 'store'])
        ->name('projects.progresses.store');

    // Update harian progress
    Route::post('/progresses/{progress}/updates', [ProgressUpdateController::class, 'store'])
        ->name('progresses.updates.store');

    // (opsional) edit & update
    Route::get('/progress-updates/{progressUpdate}/edit', [ProgressUpdateController::class, 'edit'])
        ->name('progress-updates.edit');
    Route::match(['put','patch'], '/progress-updates/{progressUpdate}', [ProgressUpdateController::class, 'update'])
        ->name('progress-updates.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/it/dashboard',  [DashboardController::class, 'itDashboard'])->name('it.dashboard');
    Route::get('/dig/dashboard', [DashboardController::class, 'digDashboard'])->name('dig.dashboard');
});

