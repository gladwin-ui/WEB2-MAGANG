<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SerialNumberController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\ImportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/users/{user}/profile-photo', [UserSettingsController::class, 'photo'])->name('users.profile-photo');

    Route::get('/bugs', [BugController::class, 'index'])->name('bugs.index');
    Route::get('/bugs/{bug}', [BugController::class, 'show'])->name('bugs.show');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'exportExcel'])->name('dashboard.export');
    Route::resource('/master/projects', ProjectController::class)->names('master.projects');
    Route::resource('/master/devices', DeviceController::class)->names('master.devices');
    Route::resource('/master/serial-numbers', SerialNumberController::class)->names('master.serial_numbers');

    // SQL Import routes
    Route::get('/import',                [ImportController::class, 'showUploadForm'])->name('import.upload');
    Route::post('/import',               [ImportController::class, 'upload'])->name('import.process');
    Route::get('/import/history',        [ImportController::class, 'history'])->name('import.history');
    Route::get('/import/{id}/progress',  [ImportController::class, 'progress'])->name('import.progress');
    Route::get('/import/{id}/status',    [ImportController::class, 'status'])->name('import.status');

    // Dashboard Reset & Trash Recovery routes
    Route::post('/import/reset',         [ImportController::class, 'reset'])->name('import.reset');
    Route::post('/import/trash/restore-all', [ImportController::class, 'restoreAll'])->name('import.restore_all');
    Route::post('/import/trash/{id}/restore', [ImportController::class, 'restore'])->name('import.restore');
    Route::post('/import/trash/delete-selected', [ImportController::class, 'forceDeleteSelected'])->name('import.force_delete_selected');
    Route::delete('/import/trash/{id}/force-delete', [ImportController::class, 'forceDelete'])->name('import.force_delete');
    // Trash page removed — redirect to history
    Route::get('/import/trash', fn() => redirect()->route('import.history'))->name('import.trash');
    Route::post('/import/reanalyze',     [ImportController::class, 'reanalyze'])->name('import.reanalyze');
    Route::post('/bugs/{bug}/reprocess', [BugController::class, 'reprocess'])->name('bugs.reprocess');
    Route::delete('/import/history/{id}', [ImportController::class, 'deleteFromHistory'])->name('import.history.delete');
});
