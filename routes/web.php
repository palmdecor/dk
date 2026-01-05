<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/preview', [SettingsController::class, 'preview'])->name('settings.preview');

    Route::get('/upload', [UploadController::class, 'index'])->name('upload');
    Route::post('/upload', [UploadController::class, 'store']);

    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::get('/jobs/{job}/poll', [JobController::class, 'poll'])->name('jobs.poll');

    Route::get('/download/{file}', [DownloadController::class, 'downloadFile'])->name('download.file');
    Route::get('/download-job/{job}/zip', [DownloadController::class, 'downloadZip'])->name('download.zip');
});
