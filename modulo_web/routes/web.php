<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\CattleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VaccineController;
use App\Http\Controllers\Admin\VeterinarianController;
use App\Http\Controllers\Admin\WorkstationController;
use App\Http\Controllers\LoginController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');

    Route::resource('veterinarians', VeterinarianController::class)->except(['destroy']);
    Route::resource('cattle', CattleController::class)->except(['destroy']);
    Route::resource('vaccines', VaccineController::class)->except(['destroy']);
    Route::resource('workstations', WorkstationController::class)->except(['destroy']);
});

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});
