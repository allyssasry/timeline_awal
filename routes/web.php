<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressController;

Route::get('/', function () {
    return view('landing');
});


Route::get('/register', [AuthController::class, 'registerForm'])
    ->name('Auth.register');    // nama rute huruf kecil
Route::post('/register',[AuthController::class,'register']);
Route::get('/login',[AuthController::class,'loginForm'])->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::get('/logout',[AuthController::class,'logout']);

// Dashboard
Route::get('/dig/dashboard', function () {
    // Contoh data (ganti dengan query Project sesungguhnya)
    $projects = \App\Models\Project::with('updates')->where('created_by', auth()->id())->latest()->get();
    return view('dig.dashboard', compact('projects'));
})->name('dashboard.digital');Route::get('/it/dashboard', function(){ return view('it.dashboard'); });
Route::get('/supervisor/dashboard', function(){ return view('supervisor.dashboard'); });

// Project & Progress
Route::resource('projects', ProjectController::class);
Route::post('/projects/{id}/progress',[ProgressController::class,'store'])->name('progress.store');
Route::get('/progress/{progress}/edit',[ProgressController::class,'edit'])->name('progress.edit');
Route::put('/progress/{progress}',[ProgressController::class,'update'])->name('progress.update');

