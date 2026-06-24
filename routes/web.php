<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SerialNumberController;
use App\Http\Controllers\BugFeedbackController;
use App\Http\Controllers\DeviceController;

// Redirect home to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Bug Index & Details
    Route::get('/bugs', [BugController::class, 'index'])->name('bugs.index');
    Route::get('/bugs/create', [BugController::class, 'create'])->name('bugs.create');
    Route::post('/bugs', [BugController::class, 'store'])->name('bugs.store');
    Route::get('/bugs/{bug}', [BugController::class, 'show'])->name('bugs.show');
    
    // Bug Feedback
    Route::post('/bugs/{bug}/feedback', [BugFeedbackController::class, 'store'])->name('bugs.feedback.store');
    Route::post('/bugs/{bug}/feedback/read', [BugFeedbackController::class, 'markAsRead'])->name('bugs.feedback.read');

    // Mechanics & Admin Close Bug Route
    Route::middleware(['role:mekanik,admin'])->group(function () {
        Route::get('/bugs/{bug}/close', [BugController::class, 'showCloseForm'])->name('bugs.close.form');
        Route::post('/bugs/{bug}/close', [BugController::class, 'close'])->name('bugs.close');
    });

    // Admin Only Routes
    Route::middleware(['role:admin'])->group(function () {
        // Analytics Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [DashboardController::class, 'exportCsv'])->name('dashboard.export');

        // Master Projects
        Route::get('/master/projects', [ProjectController::class, 'index'])->name('master.projects.index');
        Route::post('/master/projects', [ProjectController::class, 'store'])->name('master.projects.store');
        Route::put('/master/projects/{project}', [ProjectController::class, 'update'])->name('master.projects.update');
        Route::delete('/master/projects/{project}', [ProjectController::class, 'destroy'])->name('master.projects.destroy');

        // Master Serial Numbers
        Route::get('/master/serial-numbers', [SerialNumberController::class, 'index'])->name('master.serial_numbers.index');
        Route::post('/master/serial-numbers', [SerialNumberController::class, 'store'])->name('master.serial_numbers.store');
        Route::put('/master/serial-numbers/{serialNumber}', [SerialNumberController::class, 'update'])->name('master.serial_numbers.update');
        Route::delete('/master/serial-numbers/{serialNumber}', [SerialNumberController::class, 'destroy'])->name('master.serial_numbers.destroy');

        // Master Devices
        Route::get('/master/devices', [DeviceController::class, 'index'])->name('master.devices.index');
        Route::post('/master/devices', [DeviceController::class, 'store'])->name('master.devices.store');
        Route::put('/master/devices/{device}', [DeviceController::class, 'update'])->name('master.devices.update');
        Route::delete('/master/devices/{device}', [DeviceController::class, 'destroy'])->name('master.devices.destroy');
    });
});
