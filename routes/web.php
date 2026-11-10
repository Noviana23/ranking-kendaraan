<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MooraController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => session()->has('user_id') ? redirect()->route('dashboard') : redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles');
    Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
    Route::get('/costs', [ReportController::class, 'costs'])->name('costs');
    Route::get('/ranking', [MooraController::class, 'index'])->name('ranking');
    Route::get('/ranking/export', [MooraController::class, 'export'])->name('ranking.export');
    Route::get('/criteria', [CriteriaController::class, 'index'])->name('criteria');

    Route::middleware('admin.custom')->group(function () {
        Route::get('/import', [VehicleController::class, 'importPage'])->name('import');
        Route::post('/import', [VehicleController::class, 'import'])->name('import.submit');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::delete('/vehicles', [VehicleController::class, 'destroyAll'])->name('vehicles.destroyAll');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
        Route::patch('/vehicles/{vehicle}/availability', [VehicleController::class, 'availability'])->name('vehicles.availability');
        Route::put('/criteria', [CriteriaController::class, 'update'])->name('criteria.update');
        Route::post('/ranking/calculate', [MooraController::class, 'calculate'])->name('ranking.calculate');
    });
});
