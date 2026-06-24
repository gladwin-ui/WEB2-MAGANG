<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SerialNumberController;

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\BugChatController;

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
    
    // Bug Index
    Route::get('/bugs', [BugController::class, 'index'])->name('bugs.index');


    // Hanya Reporter
    Route::middleware(['role:reporter'])->group(function () {
        Route::get('/bugs/create', [BugController::class, 'create'])->name('bugs.create');
        Route::post('/bugs', [BugController::class, 'store'])->name('bugs.store');
        Route::get('/bugs/my', [BugController::class, 'myBugs'])->name('bugs.my');

    });

    // Hanya Mekanik
    Route::middleware(['role:mekanik'])->group(function () {
        Route::get('/bugs/queue', [BugController::class, 'queue'])->name('bugs.queue');
        Route::get('/bugs/{bug}/close', [BugController::class, 'showClose'])->name('bugs.close.form');
        Route::post('/bugs/{bug}/close', [BugController::class, 'close'])->name('bugs.close');
        Route::post('/bugs/{bug}/assign', [BugController::class, 'assign'])->name('bugs.assign');
    });

    // Hanya Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [DashboardController::class, 'exportCsv'])->name('dashboard.export');
        Route::resource('/master/projects', ProjectController::class)->names('master.projects');
        Route::resource('/master/devices', DeviceController::class)->names('master.devices');
        Route::resource('/master/serial-numbers', SerialNumberController::class)->names('master.serial_numbers');
    });

    // Wildcard Bug Details (must be at the bottom)
    Route::get('/bugs/{bug}', [BugController::class, 'show'])->name('bugs.show');

    // Chat — Reporter dan Mekanik
    Route::get('/chats', [BugChatController::class, 'index'])->name('bugs.chat.index');
    Route::get('/bugs/{bug}/chat', [BugChatController::class, 'show'])->name('bugs.chat.show');
    Route::post('/bugs/{bug}/chat', [BugChatController::class, 'send'])->name('bugs.chat.send');
    Route::get('/bugs/{bug}/chat/poll', [BugChatController::class, 'poll'])->name('bugs.chat.poll');
});
