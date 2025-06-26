<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeteksiController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;

// Auth routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes - require authentication
Route::middleware('auth')->group(function () {
    // Dashboard routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Detection routes
    Route::get('/detect', [DeteksiController::class, 'index'])->name('detect.form');
    Route::post('/detect/process', [DeteksiController::class, 'detect'])->name('detect.process');
    Route::get('/webcam', [DeteksiController::class, 'webcam'])->name('webcam');
    Route::post('/webcam/detect', [DeteksiController::class, 'webcamDetect'])->name('webcam.detect');
    

    // Laporan routes
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/print', [LaporanController::class, 'printReport'])->name('laporan.print');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/manajemen', [UserController::class, 'index'])->name('admin.manajemen');
    Route::get('/create', [UserController::class, 'create'])->name('admin.create');
    Route::post('/store', [UserController::class, 'store'])->name('admin.store');
    Route::get('/edit/{user}', [UserController::class, 'edit'])->name('admin.edit');
    Route::put('/update/{user}', [UserController::class, 'update'])->name('admin.update');
    Route::delete('/destroy/{user}', [UserController::class, 'destroy'])->name('admin.destroy');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.toggle-status');
});